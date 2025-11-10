# ==============================================
# HUAYKINMAIMOD PORTABLE AUTO INSTALLER (SAFE)
# ==============================================

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $MyInvocation.MyCommand.Definition
$phpDir = Join-Path $root "php"
$batFile = Join-Path $root "AUTO_RUN_HUAYKINMAIMOD_Portable.bat"

Write-Host ""
Write-Host "=============================================="
Write-Host " HUAYKINMAIMOD PORTABLE AUTO INSTALLER (SAFE)"
Write-Host "==============================================" -ForegroundColor Cyan
Write-Host ""

try {

    # Create main folder if missing
    if (!(Test-Path $root)) {
        New-Item -ItemType Directory -Path $root | Out-Null
        Write-Host "📁 Created: $root"
    }

    # Download PHP if not found (use robust downloader)
    if (!(Test-Path (Join-Path $phpDir "php.exe"))) {
        Write-Host "📦 Downloading PHP runtime (robust mode)..."
        $dlScript = Join-Path $root "download_php.ps1"
        if (Test-Path $dlScript) {
            Start-Process -FilePath "powershell.exe" -ArgumentList "-NoProfile","-ExecutionPolicy","Bypass","-File","`"$dlScript`"" -Wait
        } else {
            Write-Host "⚠️  download_php.ps1 not found. Attempting simple direct download (8.2.x)"
            $phpZipUrl = "https://windows.php.net/downloads/releases/archives/php-8.2.25-nts-Win32-vs16-x64.zip"
            $phpZip = Join-Path $root "php_temp.zip"
            Invoke-WebRequest -Uri $phpZipUrl -OutFile $phpZip -UseBasicParsing
            Write-Host "✅ Download complete. Extracting..."
            Expand-Archive -Path $phpZip -DestinationPath $root -Force
            Remove-Item $phpZip -Force
            # Find extracted folder (any php-8.*)
            $phpFolder = Get-ChildItem -Directory $root | Where-Object { $_.Name -like "php-8.*" } | Select-Object -First 1
            if ($phpFolder) {
                if (!(Test-Path $phpDir)) { New-Item -Path $phpDir -ItemType Directory | Out-Null }
                Move-Item -Path (Join-Path $phpFolder.FullName "*") -Destination $phpDir -Force
                Remove-Item $phpFolder.FullName -Recurse -Force
                Write-Host "✅ PHP installed at $phpDir"
            } else {
                Write-Host "❌ Extraction failed. No php-8.* folder found."
                Pause
                exit
            }
        }
    } else {
        Write-Host "✅ PHP already exists at $phpDir"
    }

    # Verify php presence after download
    if (!(Test-Path (Join-Path $phpDir "php.exe")) -and (Test-Path (Join-Path $root "php.exe"))) {
        Write-Host "⚠️  Using root-level php.exe (fallback)"
    } elseif (!(Test-Path (Join-Path $phpDir "php.exe"))) {
        Write-Host "❌ PHP not installed. Please run download_php.ps1 manually or place a valid archive in cache."
        Pause
        exit
    }

    # Verify structure
    if (!(Test-Path (Join-Path $phpDir "ext"))) {
        New-Item -Path (Join-Path $phpDir "ext") -ItemType Directory | Out-Null
        Write-Host "⚠️  Created missing ext folder"
    }
    if (!(Test-Path (Join-Path $phpDir "php.ini-development"))) {
        Invoke-WebRequest -Uri "https://raw.githubusercontent.com/php/php-src/master/php.ini-development" -OutFile (Join-Path $phpDir "php.ini-development")
        Write-Host "⚠️  Downloaded php.ini-development"
    }

    # Launch main web if BAT exists
    if (Test-Path $batFile) {
        Write-Host ""
        Write-Host "🚀 Launching HuayKinMaiMod Portable..."
        Start-Process -FilePath "cmd.exe" -ArgumentList "/c `"$batFile`""
        Write-Host "✅ Done! Web should open in browser."
    } else {
        Write-Host "⚠️  Cannot find $batFile"
        Write-Host "Please ensure AUTO_RUN_HUAYKINMAIMOD_Portable.bat is in $root"
    }

}
catch {
    Write-Host ""
    Write-Host "❌ ERROR: $($_.Exception.Message)"
}

Write-Host ""
Write-Host "Press any key to close..."
Pause
exit
