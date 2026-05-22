param(
    [string]$Author = $env:HF_USERNAME,
    [string]$ApiUrl = $(if ($env:HF_ENDPOINT) { $env:HF_ENDPOINT } else { "https://huggingface.co" }),
    [string]$TokenEnv = "HF_TOKEN"
)

$ErrorActionPreference = "Stop"

function Write-Section {
    param([string]$Name)
    Write-Host ""
    Write-Host "== $Name =="
}

function Get-Token {
    param([string]$Name)
    return [Environment]::GetEnvironmentVariable($Name)
}

function Invoke-HfApi {
    param(
        [string]$Path,
        [hashtable]$Query = @{},
        [string]$Token = ""
    )

    $builder = [System.UriBuilder]::new(($ApiUrl.TrimEnd("/") + $Path))
    if ($Query.Count -gt 0) {
        $pairs = foreach ($key in $Query.Keys) {
            "{0}={1}" -f [System.Uri]::EscapeDataString([string]$key), [System.Uri]::EscapeDataString([string]$Query[$key])
        }
        $builder.Query = ($pairs -join "&")
    }

    $headers = @{}
    if ($Token) {
        $headers["Authorization"] = "Bearer $Token"
    }

    try {
        return Invoke-RestMethod -Uri $builder.Uri.AbsoluteUri -Headers $headers -TimeoutSec 30
    } catch {
        return [pscustomobject]@{
            error = $_.Exception.Message
            path = $Path
        }
    }
}

function Write-RepoList {
    param(
        [object]$Data,
        [string[]]$Fields
    )

    if ($null -eq $Data) {
        Write-Output "[]"
        return
    }

    $props = $Data.PSObject.Properties.Name
    if ($props -contains "error") {
        $Data | ConvertTo-Json -Depth 5
        return
    }

    $items = @($Data)
    if ($items.Count -eq 0) {
        Write-Output "[]"
        return
    }

    $items | Select-Object -Property $Fields | ConvertTo-Json -Depth 5
}

$token = Get-Token -Name $TokenEnv

Write-Section "Local CLI"
$hf = Get-Command hf -ErrorAction SilentlyContinue
if ($hf) {
    [pscustomobject]@{
        command = "hf"
        found = $true
        source = $hf.Source
    } | ConvertTo-Json -Depth 3
} else {
    [pscustomobject]@{
        command = "hf"
        found = $false
    } | ConvertTo-Json -Depth 3
}

Write-Section "Auth Visibility"
[pscustomobject]@{
    token_env = $TokenEnv
    token_present = [bool]$token
    author = $Author
    api_url = $ApiUrl
} | ConvertTo-Json -Depth 3

if (-not $Author) {
    Write-Host "No author supplied. Re-run with -Author <huggingface-namespace> or set HF_USERNAME."
    exit 2
}

Write-Section "Models"
$models = Invoke-HfApi -Path "/api/models" -Query @{ author = $Author; limit = 50 } -Token $token
Write-RepoList -Data $models -Fields @("id", "author", "private", "gated", "lastModified", "likes", "downloads")

Write-Section "Datasets"
$datasets = Invoke-HfApi -Path "/api/datasets" -Query @{ author = $Author; limit = 50 } -Token $token
Write-RepoList -Data $datasets -Fields @("id", "author", "private", "gated", "lastModified", "likes", "downloads")

Write-Section "Spaces"
$spaces = Invoke-HfApi -Path "/api/spaces" -Query @{ author = $Author; limit = 50 } -Token $token
Write-RepoList -Data $spaces -Fields @("id", "author", "private", "sdk", "lastModified", "likes")

Write-Section "Jobs"
if ($hf) {
    $jobOutput = & hf jobs ps --all 2>&1
    $jobText = ($jobOutput -join "`n")
    if ($LASTEXITCODE -eq 0 -and $jobText -notmatch "LocalTokenNotFoundError|Unexpected error") {
        $jobOutput
    } else {
        [pscustomobject]@{
            error = $jobText
            exit_code = $LASTEXITCODE
            note = "Hugging Face Jobs may require local CLI login even when Codex connector auth exists."
        } | ConvertTo-Json -Depth 3
    }
} else {
    [pscustomobject]@{
        skipped = $true
        reason = "hf CLI not found"
    } | ConvertTo-Json -Depth 3
}
