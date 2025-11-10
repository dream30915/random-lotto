# download_php.ps1
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $MyInvocation.MyCommand.Definition
$phpDir = Join-Path $root "php"
# ตรวจทั้งสองกรณี: php.exe อยู่ในโฟลเดอร์ php หรืออยู่ตรง root (เคยย้ายผิดตำแหน่ง)
$phpExeInDir = Join-Path $phpDir "php.exe"
$phpExeAtRoot = Join-Path $root "php.exe"
if (Test-Path $phpExeInDir) {
    Write-Output "PHP already exists in $phpDir"
    exit 0
} elseif (Test-Path $phpExeAtRoot) {
    Write-Warning "พบ php.exe ที่ root จะใช้โครงสร้างนี้ชั่วคราว (ข้ามการดาวน์โหลด)"
    exit 0
}
Add-Type -AssemblyName System.IO.Compression.FileSystem
$candidateVersions = @(
    @{ v = "8.2.25"; path = "php-8.2.25-nts-Win32-vs16-x64.zip" },
    @{ v = "8.2.24"; path = "php-8.2.24-nts-Win32-vs16-x64.zip" },
    @{ v = "8.2.23"; path = "php-8.2.23-nts-Win32-vs16-x64.zip" },
    @{ v = "8.2.22"; path = "php-8.2.22-nts-Win32-vs16-x64.zip" },
    @{ v = "8.2.21"; path = "php-8.2.21-nts-Win32-vs16-x64.zip" },
    @{ v = "8.2.20"; path = "php-8.2.20-nts-Win32-vs16-x64.zip" },
    @{ v = "8.2.19"; path = "php-8.2.19-nts-Win32-vs16-x64.zip" },
    @{ v = "8.2.18"; path = "php-8.2.18-nts-Win32-vs16-x64.zip" },
    @{ v = "8.2.17"; path = "php-8.2.17-nts-Win32-vs16-x64.zip" },
    @{ v = "8.2.16"; path = "php-8.2.16-nts-Win32-vs16-x64.zip" },
    @{ v = "8.2.15"; path = "php-8.2.15-nts-Win32-vs16-x64.zip" },
    @{ v = "8.2.14"; path = "php-8.2.14-nts-Win32-vs16-x64.zip" },
    @{ v = "8.2.13"; path = "php-8.2.13-nts-Win32-vs16-x64.zip" },
    @{ v = "8.2.12"; path = "php-8.2.12-nts-Win32-vs16-x64.zip" },
    @{ v = "8.2.11"; path = "php-8.2.11-nts-Win32-vs16-x64.zip" },
    @{ v = "8.2.10"; path = "php-8.2.10-nts-Win32-vs16-x64.zip" },
    # fall back to 8.1.x if needed (VS16 toolset, NTS x64)
    @{ v = "8.1.27"; path = "php-8.1.27-nts-Win32-vs16-x64.zip" },
    @{ v = "8.1.26"; path = "php-8.1.26-nts-Win32-vs16-x64.zip" },
    @{ v = "8.1.25"; path = "php-8.1.25-nts-Win32-vs16-x64.zip" },
    @{ v = "8.1.24"; path = "php-8.1.24-nts-Win32-vs16-x64.zip" },
    @{ v = "8.1.23"; path = "php-8.1.23-nts-Win32-vs16-x64.zip" },
    @{ v = "8.1.22"; path = "php-8.1.22-nts-Win32-vs16-x64.zip" }
)
$phpVersion = $null
$phpZipFile = $null
$phpZipUrl = $null
$cacheDir = Join-Path $root "cache"
if (!(Test-Path $cacheDir)) {
    New-Item -ItemType Directory -Path $cacheDir | Out-Null
}
$selected = $null
$phpZip = $null

# หมายเหตุ: จะตรวจและลบโฟลเดอร์ที่ทับ path ZIP หลังจากเลือกเวอร์ชันเสร็จด้านล่าง

function Download-WithProgress {
    param(
        [string]$Uri,
        [string]$Destination,
        [string]$Label
    )

    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    try {
        if (Get-Command Start-BitsTransfer -ErrorAction SilentlyContinue) {
            Start-BitsTransfer -Source $Uri -Destination $Destination -DisplayName $Label -Description $Label -TransferType Download -ErrorAction Stop
        }
        else {
            Write-Output "(BITS not available, falling back to Invoke-WebRequest)"
            Invoke-WebRequest -Uri $Uri -OutFile $Destination -UseBasicParsing -ErrorAction Stop
        }
    }
    catch {
        if (Test-Path $Destination) { Remove-Item $Destination -Force }
        throw
    }
}

function Test-ZipArchive {
    param([string]$ZipPath)
    try {
        if (-not (Test-Path $ZipPath)) { return $false }
        $zip = [System.IO.Compression.ZipFile]::OpenRead($ZipPath)
        try {
            return ($zip.Entries.Count -gt 0)
        }
        finally {
            $zip.Dispose()
        }
    }
    catch {
        return $false
    }
}

function Get-PhpArchive {
    param([string]$Primary, [string]$Mirror, [string]$Dest, [int]$MinMB)
    $attempts = 0
    while ($attempts -lt 3) {
        $attempts++
        if (Test-Path $Dest) {
            $sizeMB = (Get-Item $Dest).Length / 1MB
            if ($sizeMB -ge $MinMB) {
                $msg = ("Archive valid (size {0:N1} MB)" -f $sizeMB)
                Write-Output $msg
                return $true
            }
            else {
                $warn = ("Cached file too small ({0:N1} MB < {1} MB). Deleting and re-downloading." -f $sizeMB, $MinMB)
                Write-Warning $warn
                Remove-Item $Dest -Force
            }
        }
        $usePrimary = ($attempts -eq 1 -or $attempts -eq 2)
        if ($usePrimary) { $source = $Primary } else { $source = $Mirror }
        Write-Output "Downloading (attempt $attempts) from $source ..."
        try {
            Download-WithProgress -Uri $source -Destination $Dest -Label "PHP $phpVersion attempt $attempts"
        }
        catch {
            Write-Warning "Download error: $($_.Exception.Message)"
            continue
        }
    }
    return $false
}

Write-Output "Selecting a PHP version to download..."
$minSizeMB = 70
foreach ($cand in $candidateVersions) {
    $tryFile = $cand.path
    $tryVer  = $cand.v
    $tryPrimary = "https://windows.php.net/downloads/releases/archives/$tryFile"
    $tryMirror  = "https://museum.php.net/php8/$tryFile"
    $tryZipPath = Join-Path $cacheDir $tryFile

    # if cached and large enough, pick immediately
    if (Test-Path $tryZipPath) {
        $sizeMB = (Get-Item $tryZipPath).Length / 1MB
        if ($sizeMB -ge $minSizeMB) {
            Write-Output ("Using cached archive for PHP {0} ({1:N1} MB)" -f $tryVer, $sizeMB)
            $phpVersion = $tryVer; $phpZipFile = $tryFile; $phpZipUrl = $tryPrimary; $selected = @{primary=$tryPrimary; mirror=$tryMirror}
            $phpZip = $tryZipPath
            break
        }
    }

    # otherwise choose this candidate as target, and attempt download now
    $phpVersion = $tryVer; $phpZipFile = $tryFile; $phpZipUrl = $tryPrimary; $selected = @{primary=$tryPrimary; mirror=$tryMirror}
    $phpZip = $tryZipPath
    Write-Output ("Chosen candidate PHP {0}" -f $phpVersion)
    # Try immediate fetch; loop below will validate size and download
    if (-not (Test-Path $phpZip)) { New-Item -ItemType File -Path $phpZip -Force | Out-Null; Remove-Item $phpZip -Force }
    # attempt fetch for this candidate; if later steps fail, loop continues to next candidate
    if (Get-PhpArchive -Primary $tryPrimary -Mirror $tryMirror -Dest $phpZip -MinMB $minSizeMB) { break } else { continue }
}
if ($null -eq $phpVersion) { Write-Error "No PHP candidate selected"; exit 1 }

if (Test-Path $phpZip) { Write-Output "Found cached PHP archive at $phpZip (will validate size)" } else { Write-Output "No cached archive. Will download fresh copy (selected PHP $phpVersion)." }

$mirrorUrl = $selected.mirror

# ถ้ามีโฟลเดอร์ชื่อเดียวกับไฟล์ ZIP ให้ลบทิ้ง (ตอนนี้เรามีค่า $phpZip แล้ว)
if ($phpZip -and (Test-Path $phpZip)) {
    $item = Get-Item $phpZip -ErrorAction SilentlyContinue
    if ($null -ne $item -and $item.PSIsContainer) {
        Write-Warning "Found a folder at ZIP path ($phpZip). Removing it so we can download the file."
        Remove-Item -LiteralPath $phpZip -Recurse -Force
    }
}


# หากยังไม่มีไฟล์หลังจบรอบเลือกทั้งหมด แปลว่าทุกเวอร์ชันโหลดไม่สำเร็จ
if (-not (Test-Path $phpZip)) {
    Write-Error "Failed to obtain a valid PHP archive for all candidates."
    exit 1
}

# ตรวจสอบซ้ำก่อนแตกไฟล์ (กันกรณีไฟล์ถูกลบระหว่างทาง)
if (-not (Test-Path $phpZip)) {
    Write-Error "Archive not found at $phpZip"
    exit 1
}
$finalSizeMB = (Get-Item $phpZip).Length / 1MB
if ($finalSizeMB -lt $minSizeMB) {
    $msg = ("Archive too small after download ({0:N1} MB < {1} MB). Aborting." -f $finalSizeMB, $minSizeMB)
    Write-Error $msg
    exit 1
}

Write-Output "Extracting to $phpDir ..."
try {
    Get-ChildItem -Directory -Path $root | Where-Object { $_.Name -like "php-8.*" } | ForEach-Object { Remove-Item -LiteralPath $_.FullName -Recurse -Force }
    Expand-Archive -Path $phpZip -DestinationPath $root -Force
}
catch {
    Write-Error "Extraction failed: $($_.Exception.Message)"
    exit 1
}
$extractedFolder = Get-ChildItem -Directory -Path $root | Where-Object { $_.Name -like "php-8.*" } | Select-Object -First 1
if ($null -eq $extractedFolder) {
    Write-Error "Extraction failed: php folder not found"
    exit 1
}
if (Test-Path $phpDir) { Remove-Item -Recurse -Force $phpDir }
New-Item -ItemType Directory -Path $phpDir | Out-Null
Move-Item -Path (Join-Path $extractedFolder.FullName "*") -Destination $phpDir -Force
if (Test-Path $extractedFolder.FullName) {
    Remove-Item -Path $extractedFolder.FullName -Recurse -Force
}
$phpIni = Join-Path $phpDir "php.ini"
$phpIniProd = Join-Path $phpDir "php.ini-production"
if (Test-Path $phpIniProd) {
    Copy-Item $phpIniProd $phpIni -Force
    $lines = Get-Content $phpIni
    $extensionDirSet = $false
    $gdSet = $false
    $updatedLines = @()
    foreach ($line in $lines) {
        if (-not $extensionDirSet -and $line -match '^\s*;?\s*extension_dir\s*=') {
            $updatedLines += 'extension_dir = "ext"'
            $extensionDirSet = $true
            continue
        }
        if (-not $gdSet -and $line -match '^\s*;extension\s*=\s*gd\b') {
            $updatedLines += 'extension=gd'
            $gdSet = $true
            continue
        }
        $updatedLines += $line
    }
    if (-not $extensionDirSet) {
        $updatedLines = @('extension_dir = "ext"') + $updatedLines
    }
    if (-not $gdSet) {
        $updatedLines += 'extension=gd'
    }
    Set-Content -Path $phpIni -Value $updatedLines -Encoding ASCII
    Write-Output "Configured php.ini for GD extension"
} else {
    Write-Warning "php.ini-production not found in $phpDir"
}
Write-Output "PHP downloaded and ready in $phpDir"
