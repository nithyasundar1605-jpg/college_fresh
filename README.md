# College Event Management System - Setup Instructions

## Prerequisites
- XAMPP (Apache + MySQL)
- Node.js (v16 or higher)
- npm or yarn

## Installation Steps

### 1. Database Setup
1. Start XAMPP and ensure Apache and MySQL are running
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Import the `database_schema.sql` file
4. This will create the database and tables with a default admin user

### 2. Backend Setup
The backend files are already in place in the `/backend` directory.
Ensure your XAMPP is configured to serve files from the project directory.

### 3. Frontend Setup
1. Open terminal in the project root directory
2. Install dependencies:
   ```bash
   npm install
   ```
3. Start the development server:
   ```bash
   npm start
   ```

## Default Admin Credentials
- Email: admin@college.edu
- Password: Admin@123

## Project Structure
```
college_fresh/
├── backend/
│   ├── auth/
│   │   ├── login.php
│   │   ├── register.php
│   │   └── logout.php
│   └── config/
│       └── db.php
├── src/
│   ├── components/
│   ├── pages/
│   │   ├── Login.jsx
│   │   └── Register.jsx
│   ├── services/
│   │   └── api.js
│   ├── App.js
│   └── index.js
├── database_schema.sql
└── package.json
```

## Features Implemented
✅ Professional login/register UI
✅ User authentication with session management
✅ Password encryption using password_hash()
✅ Role-based access control (Admin/Student)
✅ Input validation and error handling
✅ Responsive design
✅ Clean, modern admin-panel style

## Testing
1. Visit http://localhost:3000
2. Test registration with a new student account
3. Login with the registered student account
4. Login with admin credentials to test admin access

## Troubleshooting
- Ensure XAMPP Apache and MySQL are running
- Check that the database is imported correctly
- Verify CORS settings in backend/config/db.php
- Make sure all npm dependencies are installed