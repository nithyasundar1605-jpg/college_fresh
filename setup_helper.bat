@echo off
echo ========================================
echo College Event Management - Setup Helper
echo ========================================

echo.
echo 1. Checking XAMPP installation...
IF EXIST "C:\xampp\xampp-control.exe" (
    echo ✓ XAMPP found at C:\xampp\
) ELSE (
    echo ✗ XAMPP not found. Please install XAMPP first.
    pause
    exit /b
)

echo.
echo 2. Copying project files to XAMPP htdocs...
xcopy "%~dp0*" "C:\xampp\htdocs\college_fresh\" /E /I /Y /exclude:setup_helper.bat

IF %ERRORLEVEL% EQU 0 (
    echo ✓ Files copied successfully to C:\xampp\htdocs\college_fresh\
) ELSE (
    echo ✗ Failed to copy files
    pause
    exit /b
)

echo.
echo 3. Starting XAMPP services...
echo Please manually start Apache and MySQL in XAMPP Control Panel
echo.
echo Instructions:
echo 1. Open XAMPP Control Panel
echo 2. Click "Start" for Apache service  
echo 3. Click "Start" for MySQL service
echo 4. Wait until both show green "Running" status
echo.
echo Then access:
echo - Main App: http://localhost/college_fresh/
echo - Database Test: http://localhost/college_fresh/db_test.php
echo - phpMyAdmin: http://localhost/phpmyadmin

echo.
echo Press any key to open XAMPP Control Panel...
pause >nul
start "" "C:\xampp\xampp-control.exe"

echo.
echo Setup complete! Please follow the instructions above.
pause