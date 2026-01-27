-- Add bosun assessment and part tracking fields to reports table with proper ENUM types
ALTER TABLE reports 
ADD COLUMN bosun_assessment ENUM('No Fault Found', 'Damage/Impact', 'Wear and Tear', 'Consumable') DEFAULT NULL,
ADD COLUMN part_required VARCHAR(255) DEFAULT NULL,
ADD COLUMN part_status ENUM('Not in the Store', 'In the Store', 'On Order') DEFAULT NULL,
ADD COLUMN completion_date DATETIME DEFAULT NULL;
