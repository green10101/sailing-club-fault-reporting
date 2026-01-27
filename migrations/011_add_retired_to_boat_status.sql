-- Migration to add 'Retired' status to boats table
-- This adds the 'Retired' option to the status enum

ALTER TABLE boats MODIFY COLUMN status ENUM('OK', 'Minor Faults', 'Out of Operation', 'Retired') DEFAULT 'OK';
