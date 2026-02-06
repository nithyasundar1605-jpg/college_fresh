@echo off
echo ========================================
echo Quick XAMPP Services Fix
echo ========================================

echo.
echo Stopping all XAMPP services...
taskkill /F /IM httpd.exe >nul 2>&1
taskkill /F /IM mysqld.exe >nul 2>&1
echo ✓ Services stopped

echo.
echo Fixing common issues...

echo 1. Cleaning up PID files...
if exist "C:\xampp\mysql\data\mysql.pid" (
    del "C:\xampp\mysql\data\mysql.pid"
    echo ✓ Removed MySQL PID file
)

echo 2. Checking/resetting ports...
echo Testing port 80...
netstat -ano | findstr :80 >nul
if %errorlevel% equ 0 (
    echo ⚠ Port 80 still in use
    echo Suggesting port change to 8080
    REM Apply port 8080 configuration
    powershell -Command "(Get-Content 'C:\xampp\apache\conf\httpd.conf') -replace 'Listen 80', 'Listen 8080' | Set-Content 'C:\xampp\apache\conf\httpd.conf'"
    powershell -Command "(Get-Content 'C:\xampp\apache\conf\httpd.conf') -replace 'ServerName localhost:80', 'ServerName localhost:8080' | Set-Content 'C:\xampp\apache\conf\httpd.conf'"
    echo ✓ Changed Apache to port 8080
) else (
    echo ✓ Port 80 is free
)

echo.
echo 3. Testing MySQL data directory...
if not exist "C:\xampp\mysql\data" (
    echo ⚠ MySQL data directory missing
    echo Initializing MySQL data...
    "C:\xampp\mysql\bin\mysqld.exe" --initialize-insecure --datadir="C:\xampp\mysql\data"
    echo ✓ MySQL data initialized
)

echo.
echo 4. Starting services...
echo Starting Apache...
start "" "C:\xampp\apache_start.bat"
timeout /t 3 /nobreak >nul

echo Starting MySQL...
start "" "C:\xampp\mysql_start.bat"
timeout /t 3 /nobreak >nul

echo.
echo 5. Verifying services...
echo Apache processes:
tasklist | findstr /i httpd
echo MySQL processes:
tasklist | findstr /i mysql

echo.
echo 6. Testing connectivity...
echo Testing Apache (port 8080)...
powershell -Command "try { $r = Invoke-WebRequest -Uri 'http://localhost:8080/' -TimeoutSec 5; Write-Host '✓ Apache responding on port 8080' } catch { Write-Host '✗ Apache not responding' }"

echo Testing MySQL...
"C:\xampp\mysql\bin\mysql.exe" -u root -e "SELECT 1;" >nul 2>&1
if %errorlevel% equ 0 (
    echo ✓ MySQL responding
) else (
    echo ✗ MySQL not responding
)

echo.
echo ========================================
echo Quick Fix Complete
echo ========================================
echo.
echo Access your application at:
echo - http://localhost:8080/college_fresh/
echo - http://localhost:8080/phpmyadmin
echo.
echo If services are still not working:
echo 1. Run xampp_diagnostics.bat for detailed analysis
echo 2. Check Windows Event Viewer for error details
echo 3. Try running XAMPP Control Panel as Administrator
echo 4. Consider reinstalling XAMPP as last resort

echo.
echo Press any key to exit...
pause >nul