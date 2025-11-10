# HUAYKINMAIMOD PORTABLE AUTO INSTALLER (FIXED & ROBUST)
# Save as: C:\HuayKinMaiMod_portable\HuayKinMaiMod_AutoInstaller.ps1
# NOTE: Save file as UTF-8 (no BOM) or ANSI.

$ErrorActionPreference = "Stop"

$root = "C:\HuayKinMaiMod_portable"
$phpZipUrl = "https://windows.php.net/downloads/releases/archives/php-8.2.25-nts-Win32-vs16-x64.zip"
$phpZip = Join-Path $root "php_temp.zip"
$phpTarget = Join-Path $root "php"
$mockZip = Join-Path $root "HuayKinMaiMod_Full_Build_Final.zip"
$batFile = Join-Path $root "AUTO_RUN_HUAYKINMAIMOD_Portable.bat"
$outputZip = Join-Path $root "HuayKinMaiMod_Portable_8.2_AllInOne_REAL.zip"

function Write-Ok($msg) { Write-Host "✅ $msg" -ForegroundColor Green }
function Write-Warn($msg) { Write-Host "⚠️  $msg" -ForegroundColor Yellow }
function Write-Err($msg) { Write-Host "❌ $msg" -ForegroundColor Red }

try {
    Write-Host "`n=============================="
    Write-Host " HUAYKINMAIMOD AUTO INSTALLER"
    Write-Host "==============================`n" -ForegroundColor Cyan

    # Create root folder if missing
    if (!(Test-Path $root)) {
        New-Item -Path $root -ItemType Directory | Out-Null
        Write-Host "Created folder: $root"
    }

    # Step 0: Basic checks
    if (!(Test-Path $mockZip)) {
        Write-Warn "Mock package not found: $mockZip"
        Write-Host "If you already have HuayKinMaiMod_Full_Build_Final.zip, place it in $root and re-run."
        Read-Host "Press Enter to exit"
        exit 1
    }

    # Step 1: Download & extract PHP if needed
    if (!(Test-Path (Join-Path $phpTarget "php.exe"))) {
        Write-Host "📦 Downloading PHP 8.2.25 NTS x64..."
        Invoke-WebRequest -Uri $phpZipUrl -OutFile $phpZip -UseBasicParsing
        Write-Host "✅ Download complete. Extracting..."
        Expand-Archive -Path $phpZip -DestinationPath $root -Force
        Remove-Item -Path $phpZip -Force -ErrorAction SilentlyContinue

        # Find extracted folder name (php-8.2.*)
        $phpFolder = Get-ChildItem -Directory -Path $root | Where-Object { $_.Name -match "^php-8\.2" } | Select-Object -First 1
        if ($phpFolder -ne $null) {
            if (Test-Path $phpTarget) {
                Remove-Item -Path $phpTarget -Recurse -Force -ErrorAction SilentlyContinue
            }
            Write-Host "🔧 Moving PHP files to: $phpTarget"
            Move-Item -Path (Join-Path $phpFolder.FullName "*") -Destination $phpTarget -Force
            Remove-Item -Path $phpFolder.FullName -Recurse -Force -ErrorAction SilentlyContinue
            Write-Ok "PHP installed at $phpTarget"
        } else {
            Write-Err "Extraction failed: could not find extracted php folder under $root"
            Read-Host "Press Enter to exit"
            exit 1
        }
    } else {
        Write-Ok "PHP already present at $phpTarget"
    }

    # Step 2: Ensure basic php structure exists
    if (!(Test-Path (Join-Path $phpTarget "php.exe"))) {
        Write-Err "php.exe not found at $phpTarget\php.exe"
        Read-Host "Press Enter to exit"
        exit 1
    }

    if (!(Test-Path (Join-Path $phpTarget "ext"))) {
        Write-Warn "ext folder missing; creating ext folder..."
        New-Item -Path (Join-Path $phpTarget "ext") -ItemType Directory | Out-Null
    }

    if (!(Test-Path (Join-Path $phpTarget "php.ini-development"))) {
        Write-Warn "php.ini-development missing; downloading sample..."
        Invoke-WebRequest -Uri "https://raw.githubusercontent.com/php/php-src/master/php.ini-development" -OutFile (Join-Path $phpTarget "php.ini-development")
    }

    Write-Ok "Verified PHP folder structure:"
    Write-Host " - " (Join-Path $phpTarget "php.exe")
    Write-Host " - " (Join-Path $phpTarget "ext\")
    Write-Host " - " (Join-Path $phpTarget "php.ini-development")
    Write-Host ""

    # Step 3: Create final All-In-One ZIP (optional)
    if (Test-Path $outputZip) {
        Write-Host "Removing old All-In-One ZIP..."
        Remove-Item -Path $outputZip -Force -ErrorAction SilentlyContinue
    }

    Write-Host "Packaging All-In-One ZIP..."
    # Use Compress-Archive; include php folder, mock zip, bat and readme if exist
    $itemsToZip = @()
    if (Test-Path $phpTarget) { $itemsToZip += $phpTarget }
    if (Test-Path $mockZip) { $itemsToZip += $mockZip }
    if (Test-Path $batFile) { $itemsToZip += $batFile }
    $readme = Join-Path $root "README_ENGLISH.txt"
    if (Test-Path $readme) { $itemsToZip += $readme }

    if ($itemsToZip.Count -gt 0) {
        Compress-Archive -Path $itemsToZip -DestinationPath $outputZip -Force
        Write-Ok "Created All-In-One ZIP: $outputZip"
    } else {
        Write-Warn "No files to add to ZIP (unexpected)."
    }

    # Step 4: Launch the runner .bat (if exists)
    if (Test-Path $batFile) {
        Write-Host "`n🚀 Launching HuayKinMaiMod Portable..."
        Start-Process -FilePath "cmd.exe" -ArgumentList "/c `"$batFile`""
        Write-Ok "Launched. Browser should open to http://localhost:8080 shortly."
    } else {
        Write-Warn "Runner .bat not found: $batFile"
    }

    Write-Host "`nAll done. Press Enter to close this window."
    Read-Host
    exit 0
}
catch {
    Write-Err "An error occurred: $($_.Exception.Message)"
    Write-Host ""
    Write-Host "Full error details:"
    Write-Host $_.Exception.ToString()
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}
