@echo off
echo ========================================
echo MySQL Specific Repair
echo ========================================

echo.
echo 1. Stopping MySQL service...
taskkill /F /IM mysqld.exe >nul 2>&1
net stop mysql >nul 2>&1
echo ✓ MySQL stopped

echo.
echo 2. Checking MySQL data directory...
if not exist "C:\xampp\mysql\data" (
    echo Creating MySQL data directory...
    mkdir "C:\xampp\mysql\data"
)

echo Checking for PID file...
if exist "C:\xampp\mysql\data\mysql.pid" (
    del "C:\xampp\mysql\data\mysql.pid"
    echo ✓ Removed stale PID file
)

echo.
echo 3. Testing MySQL startup manually...
echo Initializing MySQL (if needed)...
"C:\xampp\mysql\bin\mysqld.exe" --initialize-insecure --datadir="C:\xampp\mysql\data" >nul 2>&1

echo Starting MySQL daemon...
start "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone --console

echo Waiting for MySQL to start...
timeout /t 10 /nobreak >nul

echo.
echo 4. Testing MySQL connection...
"C:\xampp\mysql\bin\mysql.exe" -u root -e "SELECT VERSION(); SELECT NOW();" > mysql_test_result.txt 2>&1

if %errorlevel% equ 0 (
    echo ✓ MySQL is responding!
    type mysql_test_result.txt
    del mysql_test_result.txt
) else (
    echo ✗ MySQL connection failed
    echo Checking error output:
    type mysql_test_result.txt
    del mysql_test_result.txt
    goto :mysql_troubleshoot
)

echo.
echo 5. Recreating database if needed...
echo Checking if college_events_db exists...
"C:\xampp\mysql\bin\mysql.exe" -u root -e "SHOW DATABASES;" | findstr /i "college_events_db" >nul
if %errorlevel% equ 0 (
    echo ✓ Database already exists
) else (
    echo Creating college_events_db...
    "C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE college_events_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    echo Importing schema...
    "C:\xampp\mysql\bin\mysql.exe" -u root college_events_db < "C:\xampp\htdocs\college_fresh\database_schema.sql"
    echo ✓ Database recreated
)

echo.
echo ========================================
echo MySQL Repair Complete
echo ========================================
echo.
echo Test MySQL connection:
echo Command line: "C:\xampp\mysql\bin\mysql.exe" -u root
echo phpMyAdmin: http://localhost:8080/phpmyadmin
echo Your app: http://localhost:8080/college_fresh/
goto :exit

:mysql_troubleshoot
echo.
echo ========================================
echo MySQL Troubleshooting
echo ========================================
echo.
echo Common solutions:
echo 1. Run XAMPP Control Panel as Administrator
echo 2. Check Windows Defender Firewall settings
echo 3. Temporarily disable antivirus software
echo 4. Check if port 3306 is blocked
echo 5. Reinstall XAMPP MySQL component
echo.
echo Manual steps:
echo 1. Open Command Prompt as Administrator
echo 2. Navigate to: cd C:\xampp\mysql\bin
echo 3. Run: mysqld --install MySQL
echo 4. Run: net start mysql
echo.

:exit
echo Press any key to exit...
pause >nul