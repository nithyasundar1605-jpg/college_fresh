@echo off
echo ========================================
echo XAMPP Services Diagnostics and Repair
echo ========================================

echo.
echo 1. Checking current service status...
echo Apache:
tasklist | findstr /i httpd
echo MySQL:
tasklist | findstr /i mysql

echo.
echo 2. Checking port availability...
echo Port 80 (Apache original):
netstat -ano | findstr :80
echo Port 8080 (Apache new):
netstat -ano | findstr :8080
echo Port 3306 (MySQL):
netstat -ano | findstr :3306

echo.
echo 3. Checking Apache configuration...
echo Testing httpd.conf syntax:
"C:\xampp\apache\bin\httpd.exe" -t
if %errorlevel% equ 0 (
    echo ✓ Apache configuration OK
) else (
    echo ✗ Apache configuration has errors
)

echo.
echo 4. Checking MySQL configuration...
echo Testing MySQL startup:
"C:\xampp\mysql\bin\mysqld.exe" --help >nul 2>&1
if %errorlevel% equ 0 (
    echo ✓ MySQL binary accessible
) else (
    echo ✗ MySQL binary issues
)

echo.
echo 5. Checking error logs...
echo Apache error log:
if exist "C:\xampp\apache\logs\error.log" (
    echo Last 5 lines:
    for /f "delims=" %%i in ('findstr /n "^" "C:\xampp\apache\logs\error.log" ^| findstr /r "[0-9]*:" ^| tail -n 5') do echo %%i
) else (
    echo Apache error log not found
)

echo.
echo MySQL error log:
if exist "C:\xampp\mysql\data\mysql_error.log" (
    echo Last 5 lines:
    type "C:\xampp\mysql\data\mysql_error.log" | findstr /c:"ERROR" /c:"Warning" | tail -n 5
) else (
    echo MySQL error log not found
)

echo.
echo 6. Repair options:
echo.
echo Option A: Restore original Apache configuration (port 80)
echo Option B: Fix MySQL data directory issues
echo Option C: Complete XAMPP reset
echo Option D: Manual troubleshooting
echo.

choice /c ABCD /m "Choose repair option (A/B/C/D): "

if errorlevel 4 goto :manual_troubleshoot
if errorlevel 3 goto :complete_reset
if errorlevel 2 goto :fix_mysql
if errorlevel 1 goto :restore_apache

:restore_apache
echo.
echo Restoring Apache to original port 80 configuration...
echo Stopping services first...
taskkill /F /IM httpd.exe >nul 2>&1
taskkill /F /IM mysqld.exe >nul 2>&1

echo Restoring httpd.conf...
if exist "C:\xampp\apache\conf\httpd.conf.backup" (
    copy "C:\xampp\apache\conf\httpd.conf.backup" "C:\xampp\apache\conf\httpd.conf"
    echo ✓ Apache configuration restored
) else (
    echo ✗ Backup file not found
)

echo Restoring SSL configuration...
if exist "C:\xampp\apache\conf\extra\httpd-ssl.conf.backup" (
    copy "C:\xampp\apache\conf\extra\httpd-ssl.conf.backup" "C:\xampp\apache\conf\extra\httpd-ssl.conf"
    echo ✓ SSL configuration restored
)

echo Starting services...
"C:\xampp\apache_start.bat"
"C:\xampp\mysql_start.bat"
echo Services restarted. Check XAMPP Control Panel.
goto :complete

:fix_mysql
echo.
echo Attempting MySQL repair...
echo Stopping MySQL...
taskkill /F /IM mysqld.exe >nul 2>&1

echo Checking MySQL data directory...
if exist "C:\xampp\mysql\data" (
    echo MySQL data directory exists
    echo Contents:
    dir "C:\xampp\mysql\data" | findstr /i "\.err"
) else (
    echo ✗ MySQL data directory missing
    echo Initializing new data directory...
    "C:\xampp\mysql\bin\mysqld.exe" --initialize-insecure --datadir="C:\xampp\mysql\data"
)

echo Starting MySQL...
net start mysql
if %errorlevel% equ 0 (
    echo ✓ MySQL started successfully
    echo Recreating database...
    "C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS college_events_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    "C:\xampp\mysql\bin\mysql.exe" -u root college_events_db < "C:\xampp\htdocs\college_fresh\database_schema.sql"
    echo ✓ Database recreated
) else (
    echo ✗ MySQL failed to start
)
goto :complete

:complete_reset
echo.
echo Performing complete XAMPP reset...
echo WARNING: This will reset all configurations and databases!
choice /c YN /m "Continue with complete reset? (Y/N): "
if errorlevel 2 goto :complete

echo Stopping all services...
taskkill /F /IM httpd.exe >nul 2>&1
taskkill /F /IM mysqld.exe >nul 2>&1

echo Backing up current configuration...
xcopy "C:\xampp" "C:\xampp_backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%" /E /I /H

echo Restoring original XAMPP files...
REM This would require XAMPP reinstall - showing manual steps instead
echo Manual steps required:
echo 1. Uninstall current XAMPP
echo 2. Download fresh XAMPP installer
echo 3. Install to same location
echo 4. Re-import your database
goto :complete

:manual_troubleshoot
echo.
echo Manual troubleshooting steps:
echo.
echo For Apache issues:
echo 1. Run as Administrator: XAMPP Control Panel
echo 2. Check Windows Defender Firewall settings
echo 3. Disable conflicting antivirus software temporarily
echo 4. Check Event Viewer for application errors
echo.
echo For MySQL issues:
echo 1. Delete mysql.pid file in mysql/data directory
echo 2. Check if mysql/data directory has proper permissions
echo 3. Run mysql_upgrade.exe
echo 4. Check if port 3306 is blocked by other software
echo.
echo General:
echo 1. Restart computer
echo 2. Temporarily disable Windows Firewall
echo 3. Run XAMPP setup as Administrator
goto :complete

:complete
echo.
echo ========================================
echo Diagnostics Complete
echo ========================================
echo.
echo Next steps:
echo 1. Check XAMPP Control Panel status
echo 2. Test services:
echo    - Apache: http://localhost/
echo    - MySQL: Use MySQL command line or phpMyAdmin
echo 3. If working, test your application:
echo    - http://localhost/college_fresh/
echo.
echo If issues persist:
echo 1. Check error logs in XAMPP Control Panel
echo 2. Try running individual service start scripts
echo 3. Consider reinstalling XAMPP as last resort

echo.
echo Press any key to exit...
pause >nul