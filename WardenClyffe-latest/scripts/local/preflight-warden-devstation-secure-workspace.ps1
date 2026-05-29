[CmdletBinding()]
param(
    [string]$SshHost = "devstation.clyffy.ai",
    [string]$AdminHost = "hades@devstation.clyffy.ai",
    [string]$ClaudeSshConfig = "$env:LOCALAPPDATA\Claude\.ssh\config",
    [switch]$SkipClaudeConfig,
    [switch]$Strict
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Invoke-SshCheck {
    param(
        [string]$Label,
        [string[]]$SshArgs
    )

    Write-Host "[devstation-preflight] $Label"
    $previousErrorActionPreference = $ErrorActionPreference
    $ErrorActionPreference = "Continue"
    try {
        $output = & ssh.exe @SshArgs 2>&1
        $exitCode = $LASTEXITCODE
    } finally {
        $ErrorActionPreference = $previousErrorActionPreference
    }
    foreach ($line in $output) {
        Write-Host $line
    }
    return [pscustomobject]@{
        Label = $Label
        ExitCode = $exitCode
        Output = ($output -join "`n")
    }
}

$checks = New-Object System.Collections.Generic.List[object]

$checks.Add((Invoke-SshCheck `
    -Label "default SSH alias reaches devstation" `
    -SshArgs @("-o", "BatchMode=yes", "-o", "ConnectTimeout=10", $SshHost, "whoami; hostname; id -nG")))

$checks.Add((Invoke-SshCheck `
    -Label "temporary admin user reaches devstation" `
    -SshArgs @("-o", "BatchMode=yes", "-o", "ConnectTimeout=10", $AdminHost, "whoami; hostname; id -nG")))

if (-not $SkipClaudeConfig) {
    if (Test-Path -LiteralPath $ClaudeSshConfig) {
        $checks.Add((Invoke-SshCheck `
            -Label "Claude AppData SSH config reaches devstation" `
            -SshArgs @("-F", $ClaudeSshConfig, "-o", "BatchMode=yes", "-o", "ConnectTimeout=10", $SshHost, "whoami; hostname")))
    } else {
        Write-Host "[devstation-preflight] Claude SSH config missing: $ClaudeSshConfig"
        $checks.Add([pscustomobject]@{
            Label = "Claude AppData SSH config exists"
            ExitCode = 1
            Output = "missing: $ClaudeSshConfig"
        })
    }
}

$remoteCommand = "cd /workspace/warden-storage/projects/WardenClyffe-latest && bash scripts/agents/warden-devstation-secure-preflight.sh"
$checks.Add((Invoke-SshCheck `
    -Label "remote secure workspace preflight" `
    -SshArgs @("-o", "BatchMode=yes", "-o", "ConnectTimeout=10", $SshHost, $remoteCommand)))

$failed = @($checks | Where-Object { $_.ExitCode -ne 0 })

[pscustomobject]@{
    target = "warden-devstation-secure-workspace"
    ssh_host = $SshHost
    admin_host = $AdminHost
    claude_ssh_config = if ($SkipClaudeConfig) { "(skipped)" } else { $ClaudeSshConfig }
    checks = $checks.Count
    failed = $failed.Count
    result = if ($failed.Count -eq 0) { "pass" } else { "fail" }
} | Format-List

if ($failed.Count -gt 0 -and $Strict) {
    exit 1
}
