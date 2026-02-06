@echo off
echo ========================================
echo Apache Configuration Repair
echo ========================================

echo.
echo 1. Restoring original Apache configuration...
if exist "C:\xampp\apache\conf\httpd.conf.backup" (
    echo Restoring httpd.conf from backup...
    copy "C:\xampp\apache\conf\httpd.conf.backup" "C:\xampp\apache\conf\httpd.conf"
    echo ✓ httpd.conf restored
) else (
    echo ✗ Backup not found. You may need to reinstall XAMPP.
    goto :error_exit
)

if exist "C:\xampp\apache\conf\extra\httpd-ssl.conf.backup" (
    echo Restoring SSL configuration...
    copy "C:\xampp\apache\conf\extra\httpd-ssl.conf.backup" "C:\xampp\apache\conf\extra\httpd-ssl.conf"
    echo ✓ SSL configuration restored
)

echo.
echo 2. Applying correct port 8080 configuration...
echo Making backup of restored files first...
copy "C:\xampp\apache\conf\httpd.conf" "C:\xampp\apache\conf\httpd_fixed.conf"

echo Updating Listen directive...
powershell -Command "(Get-Content 'C:\xampp\apache\conf\httpd.conf') -replace 'Listen 80$', 'Listen 8080' | Set-Content 'C:\xampp\apache\conf\httpd.conf'"

echo Updating ServerName directive...
powershell -Command "(Get-Content 'C:\xampp\apache\conf\httpd.conf') -replace 'ServerName localhost:80$', 'ServerName localhost:8080' | Set-Content 'C:\xampp\apache\conf\httpd.conf'"

echo.
echo 3. Testing configuration syntax...
"C:\xampp\apache\bin\httpd.exe" -t
if %errorlevel% equ 0 (
    echo ✓ Apache configuration syntax OK
) else (
    echo ✗ Apache configuration still has errors
    echo Restoring original backup...
    copy "C:\xampp\apache\conf\httpd.conf.backup" "C:\xampp\apache\conf\httpd.conf"
    goto :error_exit
)

echo.
echo 4. Fixing MySQL issues...
echo Cleaning up MySQL PID file...
if exist "C:\xampp\mysql\data\mysql.pid" (
    del "C:\xampp\mysql\data\mysql.pid"
    echo ✓ Removed MySQL PID file
)

echo.
echo 5. Starting services...
echo Stopping any existing processes...
taskkill /F /IM httpd.exe >nul 2>&1
taskkill /F /IM mysqld.exe >nul 2>&1

echo Starting Apache...
start "" "C:\xampp\apache_start.bat"
timeout /t 5 /nobreak >nul

echo Starting MySQL...
start "" "C:\xampp\mysql_start.bat"  
timeout /t 5 /nobreak >nul

echo.
echo 6. Verifying services are running...
timeout /t 3 /nobreak >nul
echo Apache processes:
tasklist | findstr /i httpd
echo MySQL processes:  
tasklist | findstr /i mysql

echo.
echo 7. Testing service connectivity...
echo Testing Apache on port 8080...
powershell -Command "try { $r = Invoke-WebRequest -Uri 'http://localhost:8080/' -TimeoutSec 10; Write-Host '✓ Apache is responding on port 8080' } catch { Write-Host '✗ Apache test failed' }"

echo Testing MySQL connection...
"C:\xampp\mysql\bin\mysql.exe" -u root -e "SELECT VERSION();" >nul 2>&1
if %errorlevel% equ 0 (
    echo ✓ MySQL is responding
) else (
    echo ✗ MySQL test failed
)

echo.
echo ========================================
echo Configuration Repair Complete
echo ========================================
echo.
echo Test URLs:
echo - Main site: http://localhost:8080/
echo - Your app: http://localhost:8080/college_fresh/
echo - phpMyAdmin: http://localhost:8080/phpmyadmin
echo.
echo If services are still not working:
echo 1. Check Windows Event Viewer for detailed errors
echo 2. Try running XAMPP Control Panel as Administrator
echo 3. Check Windows Defender/Antivirus interference
echo 4. Consider reinstalling XAMPP as last resort

goto :exit

:error_exit
echo.
echo ========================================
echo REPAIR FAILED
echo ========================================
echo.
echo Critical issues found:
echo 1. Apache configuration backup missing
echo 2. Unable to fix configuration automatically
echo.
echo Recommended solution:
echo 1. Uninstall current XAMPP
echo 2. Download fresh XAMPP installer from apachefriends.org
echo 3. Install to C:\xampp\
echo 4. Re-import your database using database_schema.sql
echo.

:exit
echo.
echo Press any key to exit...
pause >nul