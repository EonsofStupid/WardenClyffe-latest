param(
    [string]$EnvFile = "",
    [switch]$Probe
)

$ErrorActionPreference = "Stop"

function Import-EnvFile {
    param([string]$Path)

    if (-not $Path) {
        return
    }
    if (-not (Test-Path -LiteralPath $Path)) {
        throw "Env file not found: $Path"
    }

    Get-Content -LiteralPath $Path | ForEach-Object {
        $line = $_.Trim()
        if (-not $line -or $line.StartsWith("#")) {
            return
        }
        $idx = $line.IndexOf("=")
        if ($idx -lt 1) {
            return
        }
        $name = $line.Substring(0, $idx).Trim()
        $value = $line.Substring($idx + 1).Trim().Trim('"').Trim("'")
        if ($name) {
            Set-Item -Path "Env:$name" -Value $value
        }
    }
}

function Get-RequiredEnv {
    param([string]$Name)
    $value = [Environment]::GetEnvironmentVariable($Name)
    if ([string]::IsNullOrWhiteSpace($value)) {
        throw "Missing required environment variable: $Name"
    }
    return $value
}

Import-EnvFile -Path $EnvFile

$required = @(
    "PROXMOX_HOST",
    "PROXMOX_TOKEN_ID",
    "PROXMOX_TOKEN_SECRET"
)

$missing = @()
foreach ($name in $required) {
    if ([string]::IsNullOrWhiteSpace([Environment]::GetEnvironmentVariable($name))) {
        $missing += $name
    }
}

$hostName = [Environment]::GetEnvironmentVariable("PROXMOX_HOST")
$port = [Environment]::GetEnvironmentVariable("PROXMOX_PORT")
$node = [Environment]::GetEnvironmentVariable("PROXMOX_NODE")
$verifyTls = [Environment]::GetEnvironmentVariable("PROXMOX_VERIFY_TLS")

if (-not $port) {
    $port = "8006"
}
if (-not $node) {
    $node = "(not set)"
}
if (-not $verifyTls) {
    $verifyTls = "false"
}

[pscustomobject]@{
    ProxmoxHost = if ($hostName) { $hostName } else { "(missing)" }
    ProxmoxPort = $port
    ProxmoxNode = $node
    VerifyTls = $verifyTls
    TokenIdPresent = -not [string]::IsNullOrWhiteSpace([Environment]::GetEnvironmentVariable("PROXMOX_TOKEN_ID"))
    TokenSecretPresent = -not [string]::IsNullOrWhiteSpace([Environment]::GetEnvironmentVariable("PROXMOX_TOKEN_SECRET"))
    Ready = ($missing.Count -eq 0)
} | Format-List

if ($missing.Count -gt 0) {
    Write-Host "Missing: $($missing -join ', ')"
    exit 2
}

if (-not $Probe) {
    Write-Host "Access variables are present. Re-run with -Probe for read-only /version check."
    exit 0
}

$tokenId = Get-RequiredEnv "PROXMOX_TOKEN_ID"
$tokenSecret = Get-RequiredEnv "PROXMOX_TOKEN_SECRET"
$uri = "https://${hostName}:${port}/api2/json/version"
$headers = @{
    Authorization = "PVEAPIToken=${tokenId}=${tokenSecret}"
}

$curl = Get-Command curl.exe -ErrorAction SilentlyContinue
if ($curl) {
    $curlArgs = @("-fsS", "--max-time", "20")
    if ($verifyTls -ne "true") {
        $curlArgs += "-k"
    }
    $curlArgs += @("-H", $headers.Authorization.Insert(0, "Authorization: "), $uri)

    try {
        $raw = & curl.exe @curlArgs
        if ($LASTEXITCODE -eq 0 -and $raw) {
            $response = $raw | ConvertFrom-Json
            $data = $response.data
            [pscustomobject]@{
                Probe = "version"
                Transport = "curl.exe"
                Success = $true
                Version = $data.version
                Release = $data.release
            } | Format-List
            exit 0
        }
    } catch {
        [pscustomobject]@{
            Probe = "version"
            Transport = "curl.exe"
            Success = $false
            Error = $_.Exception.Message
        } | Format-List
    }
}

$invokeParams = @{
    Uri = $uri
    Headers = $headers
    Method = "GET"
    TimeoutSec = 15
}

if ($verifyTls -ne "true" -and $PSVersionTable.PSEdition -eq "Core") {
    $invokeParams["SkipCertificateCheck"] = $true
}

$previousCertificateCallback = $null
if ($verifyTls -ne "true" -and $PSVersionTable.PSEdition -ne "Core") {
    $previousCertificateCallback = [System.Net.ServicePointManager]::ServerCertificateValidationCallback
    [System.Net.ServicePointManager]::ServerCertificateValidationCallback = { $true }
}

try {
    $response = Invoke-RestMethod @invokeParams
    $data = $response.data
    [pscustomobject]@{
        Probe = "version"
        Success = $true
        Version = $data.version
        Release = $data.release
    } | Format-List
} catch {
    [pscustomobject]@{
        Probe = "version"
        Success = $false
        Error = $_.Exception.Message
    } | Format-List
    exit 1
} finally {
    if ($null -ne $previousCertificateCallback) {
        [System.Net.ServicePointManager]::ServerCertificateValidationCallback = $previousCertificateCallback
    }
}
