-- Add per-user notification preference for new fault reports.
-- Safe to run once on upgraded installations.

ALTER TABLE users
ADD COLUMN notify_new_reports TINYINT(1) NOT NULL DEFAULT 0;

-- Optional: enable notifications for current admins/bosuns by default.
-- Uncomment if desired.
-- UPDATE users SET notify_new_reports = 1 WHERE role IN ('admin', 'bosun');
