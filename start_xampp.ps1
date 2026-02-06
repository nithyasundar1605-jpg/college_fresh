# Auto-start XAMPP Services Script
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Auto-starting XAMPP Services" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

# Check if XAMPP is installed
$xamppPath = "C:\xampp"
if (-not (Test-Path $xamppPath)) {
    Write-Host "❌ XAMPP not found at $xamppPath" -ForegroundColor Red
    Write-Host "Please install XAMPP first!" -ForegroundColor Yellow
    pause
    exit 1
}

Write-Host "✓ XAMPP found at $xamppPath" -ForegroundColor Green

# Stop any existing Apache/MySQL processes
Write-Host "`nStopping existing services..." -ForegroundColor Yellow
Stop-Process -Name "httpd" -Force -ErrorAction SilentlyContinue
Stop-Process -Name "mysqld" -Force -ErrorAction SilentlyContinue

# Start Apache
Write-Host "`nStarting Apache service..." -ForegroundColor Yellow
try {
    Start-Process -FilePath "$xamppPath\apache_start.bat" -WindowStyle Hidden
    Start-Sleep -Seconds 3
    
    # Check if Apache is running
    $apacheTest = Test-NetConnection -ComputerName localhost -Port 80 -WarningAction SilentlyContinue
    if ($apacheTest.TcpTestSucceeded) {
        Write-Host "✓ Apache started successfully (port 80)" -ForegroundColor Green
    } else {
        Write-Host "⚠ Apache may still be starting..." -ForegroundColor Yellow
    }
} catch {
    Write-Host "❌ Failed to start Apache: $($_.Exception.Message)" -ForegroundColor Red
}

# Start MySQL
Write-Host "`nStarting MySQL service..." -ForegroundColor Yellow
try {
    Start-Process -FilePath "$xamppPath\mysql_start.bat" -WindowStyle Hidden
    Start-Sleep -Seconds 3
    
    # Check if MySQL is running
    try {
        $mysqlTest = Test-NetConnection -ComputerName localhost -Port 3306 -WarningAction SilentlyContinue
        if ($mysqlTest.TcpTestSucceeded) {
            Write-Host "✓ MySQL started successfully (port 3306)" -ForegroundColor Green
        } else {
            Write-Host "⚠ MySQL may still be starting..." -ForegroundColor Yellow
        }
    } catch {
        Write-Host "⚠ Could not verify MySQL port - this is normal for first-time setup" -ForegroundColor Yellow
    }
} catch {
    Write-Host "❌ Failed to start MySQL: $($_.Exception.Message)" -ForegroundColor Red
}

# Wait a bit more for services to stabilize
Write-Host "`nWaiting for services to stabilize..." -ForegroundColor Yellow
Start-Sleep -Seconds 5

# Test connections
Write-Host "`nTesting service connectivity..." -ForegroundColor Yellow

# Test Apache
try {
    $webResponse = Invoke-WebRequest -Uri "http://localhost" -TimeoutSec 5 -ErrorAction Stop
    Write-Host "✓ Apache is responding (Status: $($webResponse.StatusCode))" -ForegroundColor Green
} catch {
    Write-Host "⚠ Apache test failed - may still be starting" -ForegroundColor Yellow
}

# Test MySQL
try {
    $connectionString = "server=localhost;uid=root;pwd=;"
    Add-Type -AssemblyName System.Data
    $connection = New-Object System.Data.MySqlClient.MySqlConnection($connectionString)
    $connection.Open()
    $connection.Close()
    Write-Host "✓ MySQL connection successful" -ForegroundColor Green
} catch {
    Write-Host "⚠ MySQL test failed - may still be starting" -ForegroundColor Yellow
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "Services startup process completed!" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

# Copy project files to htdocs if not already there
$htdocsPath = "$xamppPath\htdocs\college_fresh"
$currentPath = Get-Location

if (-not (Test-Path $htdocsPath)) {
    Write-Host "`nCopying project files to XAMPP htdocs..." -ForegroundColor Yellow
    robocopy "$currentPath" "$htdocsPath" /E /XD node_modules /XF *.bat *.ps1 *.md
    Write-Host "✓ Files copied to $htdocsPath" -ForegroundColor Green
}

Write-Host "`n📋 Next Steps:" -ForegroundColor Cyan
Write-Host "1. Open browser and go to: http://localhost/college_fresh/auto_setup.php" -ForegroundColor White
Write-Host "2. This will automatically create the database and import schema" -ForegroundColor White
Write-Host "3. Or manually visit: http://localhost/phpmyadmin to create database" -ForegroundColor White

Write-Host "`n🔗 Quick Access Links:" -ForegroundColor Cyan
Write-Host "- Main App: http://localhost/college_fresh/" -ForegroundColor White
Write-Host "- Auto Setup: http://localhost/college_fresh/auto_setup.php" -ForegroundColor White
Write-Host "- phpMyAdmin: http://localhost/phpmyadmin" -ForegroundColor White

pause