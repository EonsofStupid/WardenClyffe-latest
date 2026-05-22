param(
    [string]$ZoneId = "40bb8e4477b430c77dbb6c81b3fb6e5f",
    [string]$RecordName = "master.clyffy.ai",
    [string]$TargetIp = "104.176.44.101",
    [string]$TokenEnv = "CLOUDFLARE_API_TOKEN",
    [switch]$Proxied,
    [switch]$Apply
)

$ErrorActionPreference = "Stop"

function Get-Token {
    param([string]$Name)

    $value = [Environment]::GetEnvironmentVariable($Name)
    if ([string]::IsNullOrWhiteSpace($value)) {
        return $null
    }
    return $value
}

$token = Get-Token -Name $TokenEnv
$payload = [ordered]@{
    type = "A"
    name = $RecordName
    content = $TargetIp
    ttl = 1
    proxied = [bool]$Proxied
    comment = "WardenClyffe master Clyffy route via homebase edge"
}

[pscustomobject]@{
    Action = if ($Apply) { "apply" } else { "dry-run" }
    ZoneId = $ZoneId
    RecordName = $RecordName
    TargetIp = $TargetIp
    Proxied = [bool]$Proxied
    TokenEnv = $TokenEnv
    TokenPresent = -not [string]::IsNullOrWhiteSpace($token)
} | Format-List

if (-not $Apply) {
    Write-Host "Dry run only. Re-run with -Apply after LXC 120, edge routing, and TLS are ready."
    exit 0
}

if (-not $token) {
    throw "Missing Cloudflare token. Set $TokenEnv or pass -TokenEnv with the env var name."
}

$headers = @{
    Authorization = "Bearer $token"
    "Content-Type" = "application/json"
}

$encodedName = [System.Net.WebUtility]::UrlEncode($RecordName)
$listUri = "https://api.cloudflare.com/client/v4/zones/$ZoneId/dns_records?type=A&name=$encodedName"
$existing = Invoke-RestMethod -Method GET -Uri $listUri -Headers $headers -TimeoutSec 30

if (-not $existing.success) {
    throw "Cloudflare record lookup failed."
}

$body = $payload | ConvertTo-Json -Depth 5

if ($existing.result.Count -gt 0) {
    $recordId = $existing.result[0].id
    $uri = "https://api.cloudflare.com/client/v4/zones/$ZoneId/dns_records/$recordId"
    $result = Invoke-RestMethod -Method PUT -Uri $uri -Headers $headers -Body $body -TimeoutSec 30
    $verb = "updated"
} else {
    $uri = "https://api.cloudflare.com/client/v4/zones/$ZoneId/dns_records"
    $result = Invoke-RestMethod -Method POST -Uri $uri -Headers $headers -Body $body -TimeoutSec 30
    $verb = "created"
}

if (-not $result.success) {
    throw "Cloudflare DNS upsert failed."
}

[pscustomobject]@{
    Result = $verb
    RecordName = $result.result.name
    Type = $result.result.type
    TargetIp = $result.result.content
    Proxied = $result.result.proxied
    RecordId = $result.result.id
} | Format-List
