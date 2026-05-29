param(
    [string]$Author = "justsayit",
    [switch]$Apply,
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
    "[$(Get-Date -Format o)] START action=$ActionName script=$PSCommandPath apply=$Apply author=$Author" |
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
        [string[]]$Command
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
    if ($exitCode -ne 0) {
        throw "Command failed at step '$Step' with exit code $exitCode"
    }
}

function New-WorkspaceSpec {
    # Authoritative 6-repo plan per docs/ai/HUGGINGFACE_WORKSPACE_TOUCHPOINT.md.
    # Locked 2026-05-29 (operator decision via Sub-batch C of WardenClyffe-latest absorption).
    # Earlier 3-repo plan (clyffy-master, wardenclyffe-clyffy, wardenclyffe-evals) was superseded.
    Write-ActionLog -Step "workspace_spec" -Message "building target workspace list (6 repos per touchpoint)"
    return @(
        [pscustomobject]@{
            Name = "clyffy-ai-lab"
            Type = "space"
            SpaceSdk = "gradio"
            Purpose = "Operator-facing demo surface for Clyffy AI features."
        },
        [pscustomobject]@{
            Name = "clyffy-rro-lab"
            Type = "space"
            SpaceSdk = "gradio"
            Purpose = "Reason-ready object pipeline demo and inspection UI."
        },
        [pscustomobject]@{
            Name = "wardenclyffe-evals"
            Type = "dataset"
            SpaceSdk = ""
            Purpose = "Prompt/eval cases, redacted traces, and golden outputs."
        },
        [pscustomobject]@{
            Name = "clyffy-kb-seed"
            Type = "dataset"
            SpaceSdk = ""
            Purpose = "Sanitized knowledge-base seed corpus."
        },
        [pscustomobject]@{
            Name = "clyffy-embedder-bakeoff"
            Type = "dataset"
            SpaceSdk = ""
            Purpose = "Embedding/reranking benchmark inputs and results."
        },
        [pscustomobject]@{
            Name = "clyffy-runtime-notebooks"
            Type = "space"
            SpaceSdk = "gradio"
            Purpose = "Runtime experiments. Switch Type to 'model' when artifacts become model-shaped."
        }
    )
}

function New-HfRepoCommand {
    param([pscustomobject]$Workspace)

    $repoId = "$Author/$($Workspace.Name)"
    $args = @("repo", "create", $repoId, "--private", "--exist-ok")

    if ($Workspace.Type -eq "dataset") {
        $args += @("--repo-type", "dataset")
    } elseif ($Workspace.Type -eq "space") {
        $args += @("--repo-type", "space", "--space_sdk", $Workspace.SpaceSdk)
    }

    Write-ActionLog -Step "repo_command" -Message "repo=$repoId command=hf $($args -join ' ')"
    return $args
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

try {
    Initialize-ActionLog -ActionName "huggingface-bootstrap-workspaces"
    Write-ActionLog -Step "plan" -Message "1=find hf CLI; 2=build workspace spec; 3=emit repo create commands; 4=execute only when -Apply is set"
    $hfPath = Find-HfCli
    $workspaces = New-WorkspaceSpec
    foreach ($workspace in $workspaces) {
        $repoId = "$Author/$($workspace.Name)"
        $args = New-HfRepoCommand -Workspace $workspace
        $summary = [pscustomobject]@{
            repo = $repoId
            type = $workspace.Type
            private = $true
            purpose = $workspace.Purpose
            mode = $(if ($Apply) { "apply" } else { "dry-run" })
            command = "hf " + ($args -join " ")
        }
        $json = $summary | ConvertTo-Json -Depth 4
        Add-Content -Path $script:LogFile -Value $json -Encoding UTF8
        Write-Host $json

        if ($Apply) {
            Invoke-LoggedCommand -Step "create_repo:$repoId" -Command @($hfPath) + $args
        } else {
            Write-ActionLog -Step "dry_run:$repoId" -Message "not executing remote create"
        }
    }
    Write-ActionLog -Step "complete" -Message "ok=true"
} catch {
    Write-ActionLog -Step "failed" -Level "ERROR" -Message ("error=" + $_.Exception.Message)
    throw
}
