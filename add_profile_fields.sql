-- Migration script to add profile fields to users table
USE college_event_management;

ALTER TABLE users
ADD COLUMN college_name VARCHAR(150),
ADD COLUMN address TEXT,
ADD COLUMN phone_number VARCHAR(20),
ADD COLUMN course_name VARCHAR(100),
ADD COLUMN year_of_study INT;

-- Verify columns
SHOW COLUMNS FROM users;
