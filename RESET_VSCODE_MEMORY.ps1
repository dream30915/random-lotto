# RESET_VSCODE_MEMORY.ps1
Write-Host "🔧 กำลังรีเซ็ต VSCode memory limit..." -ForegroundColor Cyan

Get-Process Code -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Sleep -Seconds 2

$settingsPath = "$env:APPDATA\Code\User\settings.json"
if (-not (Test-Path $settingsPath)) {
    New-Item -ItemType File -Path $settingsPath -Force | Out-Null
    Set-Content -Path $settingsPath -Value "{}"
}

$json = Get-Content $settingsPath -Raw | ConvertFrom-Json
$json."typescript.tsserver.maxTsServerMemory" = 4096
$json | ConvertTo-Json -Depth 10 | Set-Content -Path $settingsPath -Encoding UTF8

$codePath = "C:\Users\$env:USERNAME\AppData\Local\Programs\Microsoft VS Code\Code.exe"
if (Test-Path $codePath) {
    Write-Host "🚀 เปิด VSCode ใหม่ด้วย memory สูงสุด 4GB..." -ForegroundColor Cyan
    Start-Process -FilePath $codePath -ArgumentList "--max-old-space-size=4096"
} else {
    Write-Host "⚠️ ไม่พบ Code.exe ตรวจสอบ path ติดตั้ง VSCode" -ForegroundColor Yellow
}
