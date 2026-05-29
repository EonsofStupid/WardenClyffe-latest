param(
    [switch]$AddToGitCredential,
    [switch]$DryRun,
    [switch]$ShowOnly,
    [string]$LogDir = "logs/huggingface"
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

$script:RepoRoot = Resolve-Path (Join-Path $PSScriptRoot "..")
$script:ResolvedLogDir = Join-Path $script:RepoRoot $LogDir
$script:LogFile = $null

function Initialize-ActionLog {
    param([string]$ActionName)

    New-Item -ItemType Directory -Force -Path $script:ResolvedLogDir | Out-Null
    $stamp = Get-Date -Format "yyyyMMdd-HHmmss"
    $script:LogFile = Join-Path $script:ResolvedLogDir "$ActionName-$stamp.log"
    "[$(Get-Date -Format o)] START action=$ActionName script=$PSCommandPath dry_run=$DryRun show_only=$ShowOnly" |
        Set-Content -Path $script:LogFile -Encoding UTF8
    Write-Host "Log: $script:LogFile"
}

function Write-ActionLog {
    param(
        [string]$Step,
        [string]$Message,
        [string]$Level = "INFO"
    )

    $line = "[$(Get-Date -Format o)] $Level step=$Step $Message"
    Add-Content -Path $script:LogFile -Value $line -Encoding UTF8
    Write-Host $line
}

function Invoke-LoggedCommand {
    param(
        [string]$Step,
        [string[]]$Command,
        [switch]$AllowFailure
    )

    Write-ActionLog -Step $Step -Message ("command=" + ($Command -join " "))
    $oldErrorActionPreference = $ErrorActionPreference
    $ErrorActionPreference = "Continue"
    try {
        $output = & $Command[0] @($Command[1..($Command.Count - 1)]) 2>&1
        $exitCode = $LASTEXITCODE
    } finally {
        $ErrorActionPreference = $oldErrorActionPreference
    }
    foreach ($line in $output) {
        Add-Content -Path $script:LogFile -Value "[$(Get-Date -Format o)] OUTPUT step=$Step $line" -Encoding UTF8
        Write-Host $line
    }
    Write-ActionLog -Step $Step -Message "exit_code=$exitCode"
    if ($exitCode -ne 0 -and -not $AllowFailure) {
        throw "Command failed at step '$Step' with exit code $exitCode"
    }
    return $output
}

function Find-HfCli {
    Write-ActionLog -Step "find_hf_cli" -Message "looking up hf on PATH"
    $hf = Get-Command hf -ErrorAction SilentlyContinue
    if (-not $hf) {
        Write-ActionLog -Step "find_hf_cli" -Level "ERROR" -Message "hf CLI not found on PATH"
        throw "hf CLI not found on PATH."
    }
    Write-ActionLog -Step "find_hf_cli" -Message "source=$($hf.Source)"
    return $hf.Source
}

function Report-HfEnvironment {
    param([string]$HfPath)

    Write-ActionLog -Step "hf_env" -Message "collecting Hugging Face token/cache metadata"
    $envOutput = Invoke-LoggedCommand -Step "hf_env" -Command @($HfPath, "env") -AllowFailure
    $interesting = $envOutput | Select-String -Pattern "Token path|Has saved token|HF_TOKEN_PATH|HF_STORED_TOKENS_PATH|HF_HUB_CACHE|HF_ASSETS_CACHE"
    foreach ($line in $interesting) {
        Write-ActionLog -Step "hf_env_summary" -Message ($line.Line.Trim())
    }
}

function Report-HfAuth {
    param([string]$HfPath)

    Write-ActionLog -Step "hf_auth_list" -Message "checking current local auth state"
    Invoke-LoggedCommand -Step "hf_auth_list" -Command @($HfPath, "auth", "list") -AllowFailure | Out-Null
}

function Invoke-HfLogin {
    param([string]$HfPath)

    $loginArgs = @($HfPath, "auth", "login")
    if ($AddToGitCredential) {
        $loginArgs += "--add-to-git-credential"
    }

    if ($DryRun -or $ShowOnly) {
        Write-ActionLog -Step "hf_auth_login" -Message ("dry_run=true would_run=" + ($loginArgs -join " "))
        return
    }

    Write-ActionLog -Step "hf_auth_login" -Message "starting official interactive hf auth login; token value will not be logged"
    & $HfPath @($loginArgs[1..($loginArgs.Count - 1)])
    $exitCode = $LASTEXITCODE
    Write-ActionLog -Step "hf_auth_login" -Message "exit_code=$exitCode"
    if ($exitCode -ne 0) {
        throw "hf auth login failed with exit code $exitCode."
    }
}

try {
    if ($ShowOnly) {
        $DryRun = $true
    }

    Initialize-ActionLog -ActionName "setup-huggingface-cli-auth"
    Write-ActionLog -Step "plan" -Message "1=find hf CLI; 2=record hf env paths; 3=record current auth; 4=run official hf auth login unless dry-run; 5=record auth after login"
    $hfPath = Find-HfCli
    Report-HfEnvironment -HfPath $hfPath
    Report-HfAuth -HfPath $hfPath
    Invoke-HfLogin -HfPath $hfPath
    if (-not $DryRun) {
        Report-HfAuth -HfPath $hfPath
    }
    Write-ActionLog -Step "complete" -Message "ok=true"
} catch {
    Write-ActionLog -Step "failed" -Level "ERROR" -Message ("error=" + $_.Exception.Message)
    throw
}
