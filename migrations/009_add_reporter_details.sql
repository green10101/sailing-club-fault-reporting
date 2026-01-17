-- Add reporter name and email to reports table
ALTER TABLE reports 
ADD COLUMN reporter_name VARCHAR(255) NOT NULL DEFAULT '',
ADD COLUMN reporter_email VARCHAR(255) NOT NULL DEFAULT '';
