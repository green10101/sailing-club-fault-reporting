USE sailing_club;

-- Migration to add bosun_notes column to reports table
ALTER TABLE reports ADD COLUMN bosun_notes TEXT AFTER status;
-- Also update the status enum to include 'waiting_parts'
ALTER TABLE reports MODIFY COLUMN status ENUM('pending', 'in_progress', 'waiting_parts', 'completed') DEFAULT 'pending';