# Apache Port Conflict Resolver - PowerShell Version
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Apache Port Conflict Resolver" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

Write-Host "`n1. Checking which process is using port 80..." -ForegroundColor Yellow
$port80Processes = netstat -ano | Select-String ":80" | Select-String "LISTENING"

if ($port80Processes) {
    Write-Host "Found processes using port 80:" -ForegroundColor Red
    $port80Processes | ForEach-Object {
        Write-Host "  $_" -ForegroundColor Red
    }
    
    # Extract PID
    $pidMatch = [regex]::Match($port80Processes, '\s+(\d+)$')
    if ($pidMatch.Success) {
        $blockingPID = $pidMatch.Groups[1].Value
        Write-Host "`nBlocking process PID: $blockingPID" -ForegroundColor Yellow
        
        # Get process name
        try {
            $process = Get-Process -Id $blockingPID -ErrorAction Stop
            Write-Host "Process name: $($process.ProcessName)" -ForegroundColor Yellow
            Write-Host "Process path: $($process.Path)" -ForegroundColor Yellow
        } catch {
            Write-Host "Could not get process details" -ForegroundColor Yellow
        }
        
        Write-Host "`nOptions to resolve:" -ForegroundColor Cyan
        Write-Host "1. Kill blocking process (Recommended for development)" -ForegroundColor White
        Write-Host "2. Change Apache to use port 8080" -ForegroundColor White
        Write-Host "3. Exit" -ForegroundColor White
        
        $choice = Read-Host "`nEnter your choice (1-3)"
        
        switch ($choice) {
            "1" {
                Write-Host "`nKilling process with PID $blockingPID..." -ForegroundColor Yellow
                try {
                    Stop-Process -Id $blockingPID -Force -ErrorAction Stop
                    Write-Host "✓ Process killed successfully" -ForegroundColor Green
                    
                    Write-Host "Restarting Apache..." -ForegroundColor Yellow
                    Start-Process -FilePath "C:\xampp\apache_start.bat" -WindowStyle Hidden
                    Start-Sleep -Seconds 3
                    
                    Write-Host "Apache should now be running on port 80" -ForegroundColor Green
                } catch {
                    Write-Host "✗ Failed to kill process: $($_.Exception.Message)" -ForegroundColor Red
                    Write-Host "You may need to run this script as Administrator" -ForegroundColor Yellow
                }
            }
            
            "2" {
                Write-Host "`nChanging Apache to port 8080..." -ForegroundColor Yellow
                Write-Host "Manual steps required:" -ForegroundColor Cyan
                Write-Host "1. Open C:\xampp\apache\conf\httpd.conf" -ForegroundColor White
                Write-Host "2. Find 'Listen 80' and change to 'Listen 8080'" -ForegroundColor White
                Write-Host "3. Find 'ServerName localhost:80' and change to 'ServerName localhost:8080'" -ForegroundColor White
                Write-Host "4. Save the file" -ForegroundColor White
                Write-Host "5. Restart Apache from XAMPP Control Panel" -ForegroundColor White
                Write-Host "`nAfter this change, access your site at: http://localhost:8080/college_fresh/" -ForegroundColor Green
            }
            
            "3" {
                Write-Host "Exiting..." -ForegroundColor Yellow
            }
            
            default {
                Write-Host "Invalid choice. Exiting." -ForegroundColor Red
            }
        }
    }
} else {
    Write-Host "No processes found using port 80" -ForegroundColor Green
    Write-Host "Apache should be able to start normally" -ForegroundColor Green
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "Setup Complete!" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

Write-Host "`nTest your application:" -ForegroundColor Cyan
Write-Host "- Main Site: http://localhost/college_fresh/" -ForegroundColor White
Write-Host "- Database Test: http://localhost/college_fresh/db_test.php" -ForegroundColor White
Write-Host "- Registration Test: Try registering a new student account" -ForegroundColor White

Write-Host "`nDefault Admin Login:" -ForegroundColor Cyan
Write-Host "- Email: admin@college.edu" -ForegroundColor White
Write-Host "- Password: Admin@123" -ForegroundColor White

Write-Host "`nPress any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")