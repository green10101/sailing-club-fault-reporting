USE sailing_club;

-- Migration to update status enum values from old to new format
-- This will update existing records to use the new status values

-- Update status values to new format
UPDATE reports SET status = 'New' WHERE status = 'pending';
UPDATE reports SET status = 'In progress' WHERE status = 'in_progress';
UPDATE reports SET status = 'Waiting parts' WHERE status = 'waiting_parts';
UPDATE reports SET status = 'Complete' WHERE status = 'completed';

-- Update the enum definition to use the new values
ALTER TABLE reports MODIFY COLUMN status ENUM('New', 'In progress', 'Waiting parts', 'Complete') DEFAULT 'New';