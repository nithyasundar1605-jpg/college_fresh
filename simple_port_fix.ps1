# Simple Apache Port Fix
Write-Host "=== Apache Port Conflict Fix ===" -ForegroundColor Cyan

# Check what's using port 80
Write-Host "Checking port 80 usage..." -ForegroundColor Yellow
$netstatOutput = netstat -ano | Select-String ":80.*LISTENING"

if ($netstatOutput) {
    Write-Host "Found process using port 80:" -ForegroundColor Red
    Write-Host $netstatOutput -ForegroundColor Red
    
    # Extract PID
    $pidPattern = '\s+(\d+)$'
    $matches = [regex]::Matches($netstatOutput, $pidPattern)
    
    if ($matches.Count -gt 0) {
        $pid = $matches[0].Groups[1].Value
        Write-Host "Blocking PID: $pid" -ForegroundColor Yellow
        
        # Try to get process info
        try {
            $process = Get-Process -Id $pid -ErrorAction Stop
            Write-Host "Process: $($process.ProcessName)" -ForegroundColor Yellow
        } catch {
            Write-Host "Could not identify process" -ForegroundColor Yellow
        }
        
        Write-Host "`nOptions:" -ForegroundColor Cyan
        Write-Host "1. Kill process (requires admin)" -ForegroundColor White
        Write-Host "2. Manual fix instructions" -ForegroundColor White
        Write-Host "3. Exit" -ForegroundColor White
        
        $choice = Read-Host "Choose option (1-3)"
        
        if ($choice -eq "1") {
            Write-Host "Attempting to kill process $pid..." -ForegroundColor Yellow
            try {
                Stop-Process -Id $pid -Force
                Write-Host "Process killed successfully" -ForegroundColor Green
                Write-Host "Try starting Apache again" -ForegroundColor Green
            } catch {
                Write-Host "Failed to kill process. Try running as Administrator" -ForegroundColor Red
                Write-Host "Or use option 2 for manual fix" -ForegroundColor Yellow
            }
        }
        elseif ($choice -eq "2") {
            Write-Host "`n=== MANUAL FIX INSTRUCTIONS ===" -ForegroundColor Cyan
            Write-Host "1. Open Task Manager (Ctrl+Shift+Esc)" -ForegroundColor White
            Write-Host "2. Find process with PID $pid" -ForegroundColor White
            Write-Host "3. End the process" -ForegroundColor White
            Write-Host "4. Start Apache from XAMPP Control Panel" -ForegroundColor White
            Write-Host "`nAlternative: Change Apache port to 8080:" -ForegroundColor Yellow
            Write-Host "- Edit C:\xampp\apache\conf\httpd.conf" -ForegroundColor White
            Write-Host "- Change 'Listen 80' to 'Listen 8080'" -ForegroundColor White
            Write-Host "- Change 'ServerName localhost:80' to 'ServerName localhost:8080'" -ForegroundColor White
        }
    }
} else {
    Write-Host "Port 80 is free. Apache should start normally." -ForegroundColor Green
}

Write-Host "`n=== NEXT STEPS ===" -ForegroundColor Cyan
Write-Host "After fixing port conflict:" -ForegroundColor White
Write-Host "1. Start Apache and MySQL in XAMPP Control Panel" -ForegroundColor White
Write-Host "2. Visit: http://localhost/college_fresh/" -ForegroundColor White
Write-Host "3. Test registration with any student details" -ForegroundColor White
Write-Host "4. Login as admin: admin@college.edu / Admin@123" -ForegroundColor White

Write-Host "`nPress Enter to exit..."
Read-Host