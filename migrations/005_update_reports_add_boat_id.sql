ALTER TABLE reports ADD COLUMN boat_id INT;
ALTER TABLE reports ADD CONSTRAINT fk_boat_id FOREIGN KEY (boat_id) REFERENCES boats(id);
-- Optionally, you can drop boat_name later after migrating data
-- ALTER TABLE reports DROP COLUMN boat_name;