@echo off
echo ========================================
echo Auto-starting XAMPP Services
echo ========================================

echo.
echo Checking XAMPP installation...
IF NOT EXIST "C:\xampp\xampp-control.exe" (
    echo ❌ XAMPP not found at C:\xampp
    echo Please install XAMPP first!
    pause
    exit /b 1
)

echo ✓ XAMPP found

echo.
echo Stopping existing services...
taskkill /F /IM httpd.exe >nul 2>&1
taskkill /F /IM mysqld.exe >nul 2>&1

echo.
echo Starting Apache service...
start "" "C:\xampp\apache_start.bat"
timeout /t 3 /nobreak >nul

echo Starting MySQL service...
start "" "C:\xampp\mysql_start.bat"
timeout /t 3 /nobreak >nul

echo.
echo Waiting for services to start...
timeout /t 5 /nobreak >nul

echo.
echo Testing services...

echo Testing Apache...
curl -s http://localhost >nul 2>&1
IF %ERRORLEVEL% EQU 0 (
    echo ✓ Apache is running
) ELSE (
    echo ⚠ Apache may still be starting
)

echo Testing MySQL...
"C:\xampp\mysql\bin\mysql.exe" -u root -e "SELECT 1;" >nul 2>&1
IF %ERRORLEVEL% EQU 0 (
    echo ✓ MySQL is running
) ELSE (
    echo ⚠ MySQL may still be starting
)

echo.
echo ========================================
echo Services startup completed!
echo ========================================

echo.
echo Next steps:
echo 1. Open your browser
echo 2. Go to: http://localhost/college_fresh/auto_setup.php
echo 3. This will automatically create the database

echo.
echo Quick links:
echo - Main App: http://localhost/college_fresh/
echo - Auto Setup: http://localhost/college_fresh/auto_setup.php
echo - phpMyAdmin: http://localhost/phpmyadmin

pause