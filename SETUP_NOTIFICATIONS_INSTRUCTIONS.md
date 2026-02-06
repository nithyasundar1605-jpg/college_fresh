# 🔔 Notification System Setup Required

## Problem
The notifications are not working because the `notifications` table doesn't exist in your database yet.

## Solution
Run the setup script to create the notifications table:

### Option 1: Using Browser (Recommended)
1. Open your browser
2. Navigate to: `http://localhost/college_fresh/setup_notifications.php`
3. The script will automatically create the notifications table
4. You'll see a success message when it's done

### Option 2: Using phpMyAdmin
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select the `college_event_management` database
3. Click on the "SQL" tab
4. Copy and paste this SQL query:

```sql
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'general',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

5. Click "Go" to execute the query

## After Setup
Once the table is created, notifications will work automatically:
- ✅ New event creation → All students notified
- ✅ Registration approval/rejection → Student notified
- ✅ Certificate generation → Student notified
- ✅ Notifications appear within 5 seconds

## Verify It's Working
After running the setup:
1. Login as admin
2. Create a new event
3. Login as a student (different browser/incognito)
4. Check the bell icon - you should see a notification within 5 seconds!
