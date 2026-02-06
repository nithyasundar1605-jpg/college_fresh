@echo off
echo ========================================
echo Apache Port Conflict Resolver
echo ========================================

echo.
echo 1. Checking which process is using port 80...
echo Running: netstat -ano ^| findstr :80
netstat -ano | findstr :80

echo.
echo 2. Identifying the blocking process...
for /f "tokens=5" %%a in ('netstat -ano ^| findstr :80 ^| findstr LISTENING') do (
    set PID=%%a
    goto :found
)
:found

echo Blocking process PID: %PID%

echo.
echo 3. Getting process name...
tasklist /fi "PID eq %PID%" /fo csv | findstr /v "Image Name"

echo.
echo 4. Options to resolve:

echo.
echo Option A: Kill the blocking process (Recommended for development)
echo Option B: Change Apache to use port 8080
echo Option C: Manually configure port forwarding

echo.
echo What would you like to do?
echo 1. Kill blocking process (press 1)
echo 2. Change Apache to port 8080 (press 2) 
echo 3. Exit (press 3)

choice /c 123 /m "Enter your choice: "

if errorlevel 3 goto :exit
if errorlevel 2 goto :change_port
if errorlevel 1 goto :kill_process

:kill_process
echo.
echo Killing process with PID %PID%...
taskkill /F /PID %PID%
if %errorlevel% equ 0 (
    echo ✓ Process killed successfully
    echo Restarting Apache...
    net start Apache
    echo Apache should now be running on port 80
) else (
    echo ✗ Failed to kill process. You may need administrator privileges.
)
goto :complete

:change_port
echo.
echo Changing Apache to port 8080...
echo This requires editing Apache configuration files.
echo Please follow these manual steps:
echo.
echo 1. Open C:\xampp\apache\conf\httpd.conf
echo 2. Find "Listen 80" and change to "Listen 8080"
echo 3. Find "ServerName localhost:80" and change to "ServerName localhost:8080"
echo 4. Save the file
echo 5. Restart Apache from XAMPP Control Panel
echo.
echo After this change, access your site at: http://localhost:8080/college_fresh/
goto :complete

:complete
echo.
echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Test your application:
echo - Main Site: http://localhost/college_fresh/ (or :8080 if you changed ports)
echo - Database Test: http://localhost/college_fresh/db_test.php
echo - Registration Test: Try registering a new student account
echo.
echo Default Admin Login:
echo - Email: admin@college.edu
echo - Password: Admin@123

:exit
echo.
echo Press any key to exit...
pause >nul