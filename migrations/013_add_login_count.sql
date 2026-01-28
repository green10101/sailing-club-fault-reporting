-- Add login_count column to users table to track number of logins
ALTER TABLE users 
ADD COLUMN login_count INT DEFAULT 0;
