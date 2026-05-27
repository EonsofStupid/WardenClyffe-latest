param(
  [string]$DriveLetter = "W",
  [string]$RemotePath = "\\10.0.0.117\warden-storage",
  [string]$StorageUser = "warden-share",
  [string]$BrokerHost = "warden-capsule",
  [string]$DevstationHost = "devstation",
  [string]$SshIdentityFile = "~/.ssh/warden_foundation_01_ed25519",
  [string]$ProjectRelativePath = "projects\WardenClyffe-latest",
  [switch]$NoSshConfig,
  [switch]$NoOpen,
  [switch]$Force
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

$ProjectId = "4a897376-3cbd-4aeb-8550-c7d3ed7ad113"
$InfisicalEnv = "dev"
$InfisicalPath = "/warden/shared-storage/01"
$SecretName = "WARDEN_SHARED_STORAGE_01_SMB_PASSWORD"
$ManagedBlockStart = "# BEGIN WardenClyffe Windows SSOT"
$ManagedBlockEnd = "# END WardenClyffe Windows SSOT"

function Write-Step {
  param([string]$Message)
  Write-Host "[warden-ssot] $Message"
}

function Require-Command {
  param([string]$Name)
  $cmd = Get-Command $Name -ErrorAction SilentlyContinue
  if (-not $cmd) {
    throw "Required command not found: $Name"
  }
  return $cmd.Source
}

function Install-WardenSshConfig {
  $sshDir = Join-Path $HOME ".ssh"
  $configPath = Join-Path $sshDir "config"
  New-Item -ItemType Directory -Path $sshDir -Force | Out-Null

  $existing = ""
  if (Test-Path -LiteralPath $configPath) {
    $existing = Get-Content -LiteralPath $configPath -Raw
    $pattern = "(?s)\r?\n?$([regex]::Escape($ManagedBlockStart)).*?$([regex]::Escape($ManagedBlockEnd))\r?\n?"
    $existing = [regex]::Replace($existing, $pattern, "`r`n")
  }

  $block = @"
$ManagedBlockStart
Host ssh.clyffy.ai
  HostName ssh.clyffy.ai
  User root
  Port 22
  IdentityFile $SshIdentityFile
  IdentitiesOnly yes
  StrictHostKeyChecking accept-new

Host devstation warden-devstation devstation.clyffy.ai warden-devstation.clyffy.ai
  HostName 10.0.0.116
  User wardenop
  IdentityFile $SshIdentityFile
  IdentitiesOnly yes
  ProxyCommand "C:\Windows\System32\OpenSSH\ssh.exe" -W %h:%p ssh.clyffy.ai
  StrictHostKeyChecking accept-new
$ManagedBlockEnd

"@

  Set-Content -LiteralPath $configPath -Value ($block + $existing.TrimStart()) -Encoding ascii
  Write-Step "installed SSH aliases in $configPath"
}

function Get-WardenStoragePassword {
  $ssh = Require-Command "ssh.exe"
  $jsonLines = & $ssh -T $BrokerHost infisical secrets get $SecretName --projectId $ProjectId --env $InfisicalEnv --path $InfisicalPath --output json
  if ($LASTEXITCODE -ne 0) {
    throw "Could not broker storage secret through $BrokerHost. Check SSH alias/key access first."
  }

  $json = ($jsonLines -join "`n").Trim()
  if ([string]::IsNullOrWhiteSpace($json)) {
    throw "Storage secret broker returned no data."
  }

  $parsed = $json | ConvertFrom-Json
  $item = if ($parsed -is [System.Array]) { $parsed[0] } else { $parsed }
  $value = ""
  if ($item.PSObject.Properties.Name -contains "secretValue") {
    $value = [string]$item.secretValue
  } elseif ($item.PSObject.Properties.Name -contains "value") {
    $value = [string]$item.value
  }

  if ([string]::IsNullOrWhiteSpace($value)) {
    throw "Storage secret broker returned an empty password."
  }
  return $value
}

function Mount-WardenStorage {
  param([string]$Password)

  $localPath = "$DriveLetter`:"
  $uncTarget = ($RemotePath -replace '^\\\\', '').Split('\')[0]

  $existing = Get-SmbMapping -LocalPath $localPath -ErrorAction SilentlyContinue
  if ($existing -and $existing.RemotePath -ne $RemotePath) {
    if (-not $Force) {
      throw "$localPath is already mapped to $($existing.RemotePath). Re-run with -Force only if you want Warden to replace it."
    }
    Remove-SmbMapping -LocalPath $localPath -Force -UpdateProfile -ErrorAction SilentlyContinue
  } elseif ($existing) {
    Remove-SmbMapping -LocalPath $localPath -Force -UpdateProfile -ErrorAction SilentlyContinue
  }

  cmdkey.exe "/add:$uncTarget" "/user:$StorageUser" "/pass:$Password" | Out-Null
  New-SmbMapping -LocalPath $localPath -RemotePath $RemotePath -UserName $StorageUser -Password $Password -Persistent $true -ErrorAction Stop | Out-Null

  $root = "$localPath\"
  if (-not (Test-Path -LiteralPath $root)) {
    throw "$root is not visible after mapping."
  }

  $testDir = Join-Path $root ".warden-map-test"
  $testFile = Join-Path $testDir "windows-endpoint.txt"
  New-Item -ItemType Directory -Path $testDir -Force | Out-Null
  Set-Content -LiteralPath $testFile -Value "warden windows endpoint test $(Get-Date -Format o)" -NoNewline -Encoding ascii
  $readBack = Get-Content -LiteralPath $testFile -Raw
  Remove-Item -LiteralPath $testFile -Force
  Remove-Item -LiteralPath $testDir -Force
  if ($readBack -notlike "warden windows endpoint test*") {
    throw "Mapped drive write/read/delete test failed."
  }

  return $root
}

function Test-DevstationSsh {
  $ssh = Require-Command "ssh.exe"
  $hostName = (& $ssh -o BatchMode=yes -T $DevstationHost hostname 2>$null)
  if ($LASTEXITCODE -ne 0 -or [string]::IsNullOrWhiteSpace(($hostName -join ""))) {
    return "failed"
  }
  return (($hostName -join "`n").Trim())
}

if (-not $NoSshConfig) {
  Install-WardenSshConfig
}

$shareHost = ($RemotePath -replace '^\\\\', '').Split('\')[0]
$smbCheck = Test-NetConnection -ComputerName $shareHost -Port 445 -InformationLevel Quiet
if (-not $smbCheck) {
  throw "Cannot reach SMB on $shareHost:445. Connect to Warden private network/Tailscale/Headscale first."
}

Write-Step "brokering SMB credential without printing it"
$password = Get-WardenStoragePassword
try {
  Write-Step "mapping $DriveLetter`: to $RemotePath"
  $root = Mount-WardenStorage -Password $password
} finally {
  $password = $null
}

$projectPath = Join-Path $root $ProjectRelativePath
if (-not (Test-Path -LiteralPath $projectPath)) {
  throw "Mapped drive is live, but project path is missing: $projectPath"
}

$drive = Get-PSDrive -Name $DriveLetter -PSProvider FileSystem
$mapping = Get-SmbMapping -LocalPath "$DriveLetter`:"
$devstationResult = Test-DevstationSsh

if (-not $NoOpen) {
  Start-Process explorer.exe $projectPath
  Start-Process explorer.exe "shell:MyComputerFolder"
}

[pscustomobject]@{
  storage_mapping = "ok"
  local_path = "$DriveLetter`:"
  remote_path = $mapping.RemotePath
  project_path = $projectPath
  free_gib = [math]::Round($drive.Free / 1GB, 1)
  used_gib = [math]::Round($drive.Used / 1GB, 1)
  devstation_ssh = $devstationResult
}
