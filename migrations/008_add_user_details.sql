-- Add name and email to users table
ALTER TABLE users 
ADD COLUMN name VARCHAR(255) NOT NULL DEFAULT '',
ADD COLUMN email VARCHAR(255) NOT NULL DEFAULT '';
