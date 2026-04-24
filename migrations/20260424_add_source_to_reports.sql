-- Add provenance columns to reports table.
-- Run this migration once after 20260423_create_boat_checkins.sql has been applied.

ALTER TABLE reports
    ADD COLUMN source ENUM('fault_form', 'boat_checkin') NOT NULL DEFAULT 'fault_form';

ALTER TABLE reports
    ADD COLUMN boat_checkin_id INT NULL,
    ADD CONSTRAINT fk_reports_boat_checkin FOREIGN KEY (boat_checkin_id) REFERENCES boat_checkins(id);

CREATE INDEX idx_reports_source ON reports(source);
