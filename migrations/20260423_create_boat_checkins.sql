CREATE TABLE IF NOT EXISTS boat_checkins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    boat_id INT NOT NULL,
    user_name VARCHAR(120) NOT NULL,
    user_email VARCHAR(190) NOT NULL,
    checked_in_at DATETIME NOT NULL,
    put_away_ok TINYINT(1) NOT NULL,
    safe_for_next_user TINYINT(1) NOT NULL,
    has_faults_to_rectify TINYINT(1) NOT NULL,
    damage_during_checkout TINYINT(1) NULL,
    checkin_notes TEXT NULL,
    fault_report_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_boat_checkins_boat FOREIGN KEY (boat_id) REFERENCES boats(id),
    CONSTRAINT fk_boat_checkins_report FOREIGN KEY (fault_report_id) REFERENCES reports(id),
    INDEX idx_boat_checkins_boat_date (boat_id, checked_in_at),
    INDEX idx_boat_checkins_date (checked_in_at)
);

-- Optional provenance columns for reports, used only when present by the code.
-- Run these optional statements only once if you want explicit provenance on report rows.
-- ALTER TABLE reports
--     ADD COLUMN source ENUM('fault_form', 'boat_checkin') NOT NULL DEFAULT 'fault_form';
--
-- ALTER TABLE reports
--     ADD COLUMN boat_checkin_id INT NULL,
--     ADD CONSTRAINT fk_reports_boat_checkin FOREIGN KEY (boat_checkin_id) REFERENCES boat_checkins(id);
--
-- CREATE INDEX idx_reports_source ON reports(source);
