@echo off
echo Starting Apache on port 8080 to avoid conflicts with system process on port 80...
echo This batch file starts Apache with the correct configuration for the College Event Management System.
echo.

REM Kill any existing Apache processes
taskkill /f /im httpd.exe 2>nul

REM Wait a moment
timeout /t 2 /nobreak >nul

REM Start Apache with the correct configuration
"C:\xampp\apache\bin\httpd.exe" -f "C:\xampp\apache\conf\httpd.conf"

echo.
echo Apache has been started on port 8080.
echo Your College Event Management System is now accessible at:
echo.
echo Frontend: http://localhost:3000
echo Backend:  http://localhost:8080/college_fresh/
echo.
echo Press any key to exit...
pause >nul