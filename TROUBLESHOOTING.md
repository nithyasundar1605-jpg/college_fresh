# 🛠️ Registration Issue Troubleshooting Guide

## Current Status
- ✅ Database created and working
- ✅ Backend PHP files in correct location  
- ✅ All code logic verified correct
- ⚠️ Apache may not be serving PHP files properly

## Quick Diagnosis Steps

### 1. Test Apache/PHP
Visit: `http://localhost/college_fresh/test_apache.php`
- If you see "Apache is working! PHP version: ..." → Apache is working
- If you get 404 or download prompt → Apache isn't configured properly

### 2. Test Database Connection
Visit: `http://localhost/college_fresh/db_test.php`
- This will show detailed database status

### 3. Run Full Debug
Visit: `http://localhost/college_fresh/debug_registration.php`
- This tests every step of registration process

## Common Solutions

### Solution A: Restart XAMPP Services
1. Open XAMPP Control Panel
2. Stop Apache and MySQL
3. Start Apache and MySQL again
4. Wait for green "Running" status

### Solution B: Check Port Conflicts
Sometimes other programs use port 80:
1. Open Command Prompt as Administrator
2. Run: `netstat -ano | findstr :80`
3. Note the PID numbers
4. Run: `taskkill /F /PID [number]` for conflicting processes
5. Restart Apache

### Solution C: Use Different Port
1. Edit `C:\xampp\apache\conf\httpd.conf`
2. Find `Listen 80` and change to `Listen 8080`
3. Find `ServerName localhost:80` and change to `ServerName localhost:8080`
4. Restart Apache
5. Access via `http://localhost:8080/college_fresh/`

### Solution D: Check Windows Firewall
1. Open Windows Defender Firewall
2. Allow Apache through firewall
3. Or temporarily disable firewall for testing

## Manual Registration Test

If the web interface continues to fail, you can register directly via database:

1. Visit `http://localhost/phpmyadmin`
2. Select `college_events_db` database
3. Go to `users` table
4. Click "Insert" tab
5. Fill in:
   - name: Your Name
   - email: your@email.com
   - password: (use password_hash generator)
   - role: student
6. Click "Go"

## Password Hash Generator
Use this to create hashed passwords:
```php
<?php
echo password_hash('your_password_here', PASSWORD_DEFAULT);
?>
```

## Emergency Contact
If none of these work:
1. Share the exact error message from debug_registration.php
2. Include Apache error logs from `C:\xampp\apache\logs\error.log`
3. Include MySQL error logs from `C:\xampp\mysql\data\mysql_error.log`

## Quick Fix Commands
```batch
# Stop all services
taskkill /F /IM httpd.exe
taskkill /F /IM mysqld.exe

# Start XAMPP services
start "" "C:\xampp\apache_start.bat"
start "" "C:\xampp\mysql_start.bat"

# Test connection
curl http://localhost/college_fresh/test_apache.php
```