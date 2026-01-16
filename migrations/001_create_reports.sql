CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    boat_name VARCHAR(255) NOT NULL,
    fault_description TEXT NOT NULL,
    reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('New', 'In progress', 'Complete') DEFAULT 'New',
    bosun_id INT,
    FOREIGN KEY (bosun_id) REFERENCES users(id) ON DELETE SET NULL
);