@echo off
chcp 65001 >nul
title HUAYKINMAIMOD PORTABLE AUTO RUN (FINAL FIX)
color 0A

setlocal EnableDelayedExpansion

echo ===============================================
echo    HUAYKINMAIMOD PORTABLE AUTO RUN (FINAL FIX)
echo ===============================================
echo.

set "root=%~dp0"
set "phpdir=%root%php"
set "phpexe=%phpdir%\php.exe"
if not exist "%phpexe%" if exist "%root%php.exe" (
    echo [INFO] Using root-level php.exe
    set "phpexe=%root%php.exe"
)
set "zip=%root%HuayKinMaiMod_Full_Build_Final.zip"
set "extract=%root%HuayKinMaiMod_Extracted"

:: ตรวจไฟล์ ZIP
if not exist "%zip%" (
    echo [ERROR] Missing file: HuayKinMaiMod_Full_Build_Final.zip
    pause
    exit /b
)

:: ตรวจ PHP
if not exist "%phpexe%" (
    echo [INFO] PHP not found under expected locations, running download_php.ps1 ...
    powershell -ExecutionPolicy Bypass -File "%root%download_php.ps1"
    if not exist "%phpexe%" if exist "%root%php.exe" (
        echo [INFO] Detected php.exe at root after download
        set "phpexe=%root%php.exe"
    )
)

if not exist "%phpexe%" (
    echo [ERROR] PHP installation failed.
    pause
    exit /b
)

:: ลบของเก่าก่อน
if exist "%extract%" (
    echo Removing old extracted folder...
    rmdir /s /q "%extract%"
)

:: แตกไฟล์ ZIP
echo Extracting ZIP...
powershell -Command "Expand-Archive -Path '%zip%' -DestinationPath '%extract%' -Force"

if not exist "%extract%\index.php" (
    echo [ERROR] index.php not found after extraction.
    pause
    exit /b
)

:: ใส่ไฟล์เดโม (ถ้ามี) ให้ใช้งานได้แม้จะลบ/แตกไฟล์ใหม่ทุกครั้ง
if exist "%root%demo_all.php" (
    copy /Y "%root%demo_all.php" "%extract%\demo_all.php" >nul
) else if exist "%root%examples.php" (
    copy /Y "%root%examples.php" "%extract%\demo_all.php" >nul
)

:: วางหน้าแรกแบบรายการ (index_list.php -> index.php) และหน้ารายละเอียด (detail.php)
if exist "%root%index_list.php" (
    copy /Y "%root%index_list.php" "%extract%\index.php" >nul
)
if exist "%root%detail.php" (
    copy /Y "%root%detail.php" "%extract%\detail.php" >nul
)

cd /d "%extract%"

:: ค้นหาพอร์ตว่าง 8080-8083
set "port="
for %%P in (8080 8081 8082 8083) do (
    netstat -ano | find "LISTENING" | find "%%P" >nul
    if errorlevel 1 (
        set "port=%%P"
        goto foundport
    )
)

echo [ERROR] No available port (8080–8083)
pause
exit /b

:foundport
echo Using port !port!
echo.

:: รัน PHP server (bind to all interfaces so LAN devices such as an iPhone can reach it)
start "" "%phpexe%" -S 0.0.0.0:!port! -t "%extract%" -c "%root%php.ini"
timeout /t 2 >nul

:: เปิดเว็บ
echo Opening browser...
start "" "http://localhost:!port!/index.php"

echo Optional demo page (if copied): http://localhost:!port!/demo_all.php

echo.
echo ===============================================
echo 🚀 Website started successfully on port !port!
echo URL: http://localhost:!port!/index.php
echo ===============================================
echo.
pause
exit /b
