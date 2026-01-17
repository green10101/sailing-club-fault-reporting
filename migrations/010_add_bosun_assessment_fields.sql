-- Add bosun assessment and part tracking fields to reports table
ALTER TABLE reports 
ADD COLUMN bosun_assessment VARCHAR(50) DEFAULT NULL,
ADD COLUMN part_required VARCHAR(255) DEFAULT NULL,
ADD COLUMN part_status VARCHAR(50) DEFAULT NULL,
ADD COLUMN completion_date DATETIME DEFAULT NULL;
