USE sailing_club;

-- Migration to add bosun_notes column to reports table
ALTER TABLE reports ADD COLUMN bosun_notes TEXT AFTER status;
-- Also update the status enum to include 'Waiting parts'
ALTER TABLE reports MODIFY COLUMN status ENUM('New', 'In progress', 'Waiting parts', 'Complete') DEFAULT 'New';