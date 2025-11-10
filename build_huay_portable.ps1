# ==============================
# HUAYKINMAIMOD PORTABLE BUILDER
# Created by ChatGPT (for Woravej)
# ==============================

$ErrorActionPreference = "Stop"
$root = "C:\HuayKinMaiMod_portable"
$phpUrl = "https://windows.php.net/downloads/releases/archives/php-8.2.25-nts-Win32-vs16-x64.zip"
$phpZip = "$root\php.zip"
$phpDir = "$root\php"
$mockZip = "$root\HuayKinMaiMod_Full_Build_Final.zip"
$outputZip = "$root\HuayKinMaiMod_Portable_8.2_AllInOne_REAL.zip"

Write-Host "========================================"
Write-Host " HUAYKINMAIMOD PORTABLE BUILDER START"
Write-Host "========================================`n"

# Step 1 - Download PHP
if (!(Test-Path "$phpDir\php.exe")) {
    Write-Host "Downloading PHP 8.2.25 NTS x64..."
    Invoke-WebRequest -Uri $phpUrl -OutFile $phpZip
    Write-Host "Extracting PHP..."
    Expand-Archive -Path $phpZip -DestinationPath $root -Force
    $phpFolder = Get-ChildItem -Directory -Path $root | Where-Object { $_.Name -like "php-8.2.*" } | Select-Object -First 1
    if ($phpFolder) {
        Rename-Item $phpFolder.FullName $phpDir -Force
    }
    Remove-Item $phpZip -Force
    Write-Host "✅ PHP installed at $phpDir"
} else {
    Write-Host "✅ PHP already exists at $phpDir"
}

# Step 2 - Check mock system
if (!(Test-Path $mockZip)) {
    Write-Host "[ERROR] HuayKinMaiMod_Full_Build_Final.zip not found in $root"
    exit 1
}

# Step 3 - Create final All-In-One ZIP
Write-Host "Creating All-In-One ZIP..."
if (Test-Path $outputZip) { Remove-Item $outputZip -Force }
Compress-Archive -Path "$phpDir","$mockZip","$root\AUTO_RUN_HUAYKINMAIMOD_Portable.bat","$root\README_ENGLISH.txt" -DestinationPath $outputZip
Write-Host "✅ Created: $outputZip"

# Step 4 - Test run
Write-Host "Launching HuayKinMaiMod Portable..."
Start-Process -FilePath "cmd.exe" -ArgumentList "/c `"$root\AUTO_RUN_HUAYKINMAIMOD_Portable.bat`""

Write-Host "`n✅ Build complete!"
Write-Host "ZIP location: $outputZip"
Write-Host "========================================"
