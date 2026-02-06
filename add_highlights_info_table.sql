USE college_event_management;

CREATE TABLE IF NOT EXISTS event_highlights_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    summary TEXT,
    winners JSON DEFAULT NULL,
    guests JSON DEFAULT NULL,
    statistics JSON DEFAULT NULL,
    sponsors JSON DEFAULT NULL,
    resources JSON DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    UNIQUE(event_id)
);
