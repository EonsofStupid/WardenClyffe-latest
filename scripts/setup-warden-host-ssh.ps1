param(
    [string]$HostAlias = "server1",
    [string]$HostName = "server1",
    [int]$Port = 22,
    [string]$User = "root",
    [Parameter(Mandatory = $true)]
    [string]$ExpectedFingerprint,
    [string]$IdentityFile = "~/.ssh/warden_foundation_01_ed25519",
    [switch]$Apply,
    [switch]$WriteConfig,
    [switch]$EnsureKey,
    [switch]$TestLogin
)

$ErrorActionPreference = "Stop"

function Resolve-HomePath {
    param([string]$Path)

    if ($Path.StartsWith("~/") -or $Path.StartsWith("~\")) {
        return (Join-Path $env:USERPROFILE $Path.Substring(2))
    }
    return $Path
}

function Get-KeyFingerprint {
    param([string]$KnownHostsPath)

    $raw = & ssh-keygen -lf $KnownHostsPath -E sha256
    if ($LASTEXITCODE -ne 0) {
        throw "ssh-keygen failed to fingerprint $KnownHostsPath"
    }

    $line = @($raw)[0]
    if ($line -notmatch "SHA256:[^\s]+") {
        throw "No SHA256 fingerprint found in ssh-keygen output: $line"
    }
    return $Matches[0]
}

function Get-VerifiedHostKeyLine {
    param(
        [string]$Alias,
        [string]$Name,
        [int]$SshPort,
        [string]$Expected
    )

    $tmp = Join-Path $env:TEMP ("wardenclyffe_{0}_{1}_known_hosts_probe" -f $Alias, [guid]::NewGuid().ToString("N"))
    Remove-Item -LiteralPath $tmp -ErrorAction SilentlyContinue

    $hostKeyLine = $null
    $scanCandidates = @(
        "C:\Program Files\Git\usr\bin\ssh-keyscan.exe",
        "ssh-keyscan.exe",
        "ssh-keyscan"
    )

    foreach ($candidate in $scanCandidates) {
        $scanner = $null
        if (Test-Path -LiteralPath $candidate) {
            $scanner = $candidate
        } else {
            $cmd = Get-Command $candidate -ErrorAction SilentlyContinue
            if ($cmd) {
                $scanner = $cmd.Source
            }
        }

        if (-not $scanner) {
            continue
        }

        $scanOutput = & $scanner -t ed25519 -p $SshPort $Name 2>$null
        $hostKeyLine = $scanOutput | Where-Object { $_ -match " ssh-ed25519 " } | Select-Object -First 1
        if ($hostKeyLine) {
            break
        }
    }

    if ($hostKeyLine) {
        Set-Content -LiteralPath $tmp -Value $hostKeyLine -Encoding ASCII
    } else {
        $previousErrorAction = $ErrorActionPreference
        $ErrorActionPreference = "Continue"
        try {
            & ssh `
                -o StrictHostKeyChecking=accept-new `
                -o UserKnownHostsFile="$tmp" `
                -o BatchMode=yes `
                -o ConnectTimeout=8 `
                -p $SshPort `
                $Name true 2>$null | Out-Null
        } finally {
            $ErrorActionPreference = $previousErrorAction
        }
    }

    if (-not (Test-Path -LiteralPath $tmp)) {
        throw "SSH did not write a temporary host key for $Name."
    }

    $fingerprint = Get-KeyFingerprint -KnownHostsPath $tmp
    if ($fingerprint -ne $Expected) {
        $line = Get-Content -LiteralPath $tmp | Select-Object -First 1
        throw "Host key mismatch for $Name. Expected $Expected, got $fingerprint. Refusing to trust: $line"
    }

    $hostKeyLine = Get-Content -LiteralPath $tmp | Select-Object -First 1
    if ($hostKeyLine -notmatch " ssh-ed25519 ") {
        throw "Expected an ssh-ed25519 host key line, got: $hostKeyLine"
    }

    return [pscustomobject]@{
        Fingerprint = $fingerprint
        HostKeyLine = $hostKeyLine
        ProbePath = $tmp
    }
}

function Upsert-KnownHost {
    param(
        [string]$Alias,
        [string]$HostKeyLine
    )

    $sshDir = Join-Path $env:USERPROFILE ".ssh"
    $knownHosts = Join-Path $sshDir "known_hosts"
    New-Item -ItemType Directory -Force -Path $sshDir | Out-Null

    if (Test-Path -LiteralPath $knownHosts) {
        $backup = "$knownHosts.bak.$(Get-Date -Format yyyyMMddHHmmss)"
        Copy-Item -LiteralPath $knownHosts -Destination $backup
        & ssh-keygen -R $Alias -f $knownHosts | Out-Null
    }

    Add-Content -LiteralPath $knownHosts -Value $HostKeyLine -Encoding ASCII
    return $knownHosts
}

function Upsert-SshConfig {
    param(
        [string]$Alias,
        [string]$Name,
        [int]$SshPort,
        [string]$SshUser,
        [string]$Identity
    )

    $sshDir = Join-Path $env:USERPROFILE ".ssh"
    $configPath = Join-Path $sshDir "config"
    New-Item -ItemType Directory -Force -Path $sshDir | Out-Null

    $block = @(
        "# BEGIN WardenClyffe $Alias",
        "Host $Alias",
        "  HostName $Name",
        "  User $SshUser",
        "  Port $SshPort",
        "  IdentityFile $Identity",
        "  IdentitiesOnly yes",
        "# END WardenClyffe $Alias"
    ) -join [Environment]::NewLine

    $content = ""
    if (Test-Path -LiteralPath $configPath) {
        $content = Get-Content -LiteralPath $configPath -Raw
        $backup = "$configPath.bak.$(Get-Date -Format yyyyMMddHHmmss)"
        Copy-Item -LiteralPath $configPath -Destination $backup
    }

    $pattern = "(?ms)^# BEGIN WardenClyffe $([regex]::Escape($Alias)).*?# END WardenClyffe $([regex]::Escape($Alias))\r?\n?"
    if ($content -match $pattern) {
        $content = [regex]::Replace($content, $pattern, $block + [Environment]::NewLine)
    } else {
        if ($content -and -not $content.EndsWith([Environment]::NewLine)) {
            $content += [Environment]::NewLine
        }
        $content += $block + [Environment]::NewLine
    }

    Set-Content -LiteralPath $configPath -Value $content -Encoding ASCII
    return $configPath
}

function Ensure-IdentityKey {
    param([string]$Identity)

    $path = Resolve-HomePath -Path $Identity
    $pubPath = "$path.pub"

    if (-not (Test-Path -LiteralPath $path)) {
        $cmdPath = $path.Replace('"', '""')
        $cmdComment = ("warden@$HostAlias").Replace('"', '""')
        $keygenCommand = "ssh-keygen -t ed25519 -a 64 -f `"$cmdPath`" -C `"$cmdComment`" -N `"`""
        & cmd.exe /d /c $keygenCommand
        if ($LASTEXITCODE -ne 0) {
            throw "ssh-keygen failed to create $path"
        }
    }

    return [pscustomobject]@{
        PrivateKeyPath = $path
        PublicKeyPath = $pubPath
        PublicKey = Get-Content -LiteralPath $pubPath -Raw
    }
}

$expected = $ExpectedFingerprint.Trim()
if ($expected -notmatch "^SHA256:[A-Za-z0-9+/=]+$") {
    throw "ExpectedFingerprint must look like SHA256:<base64>."
}

$verified = Get-VerifiedHostKeyLine `
    -Alias $HostAlias `
    -Name $HostName `
    -SshPort $Port `
    -Expected $expected

[pscustomobject]@{
    HostAlias = $HostAlias
    HostName = $HostName
    Port = $Port
    User = $User
    Fingerprint = $verified.Fingerprint
    FingerprintMatches = $true
    Apply = [bool]$Apply
    WriteConfig = [bool]$WriteConfig
    EnsureKey = [bool]$EnsureKey
    IdentityFile = $IdentityFile
} | Format-List

if (-not $Apply) {
    Write-Host "Dry run only. Re-run with -Apply after reviewing the host profile."
    exit 0
}

$knownHosts = Upsert-KnownHost -Alias $HostAlias -HostKeyLine $verified.HostKeyLine
Write-Host "Updated known_hosts: $knownHosts"

if ($WriteConfig) {
    $config = Upsert-SshConfig `
        -Alias $HostAlias `
        -Name $HostName `
        -SshPort $Port `
        -SshUser $User `
        -Identity $IdentityFile
    Write-Host "Updated ssh config: $config"
}

if ($EnsureKey) {
    $key = Ensure-IdentityKey -Identity $IdentityFile
    Write-Host "Identity private key path: $($key.PrivateKeyPath)"
    Write-Host "Identity public key path:  $($key.PublicKeyPath)"
    Write-Host ""
    Write-Host "Public key to install on the Proxmox host:"
    Write-Host $key.PublicKey.Trim()
}

if ($TestLogin) {
    & ssh -o BatchMode=yes -o ConnectTimeout=8 $HostAlias "whoami && hostname"
    exit $LASTEXITCODE
}
