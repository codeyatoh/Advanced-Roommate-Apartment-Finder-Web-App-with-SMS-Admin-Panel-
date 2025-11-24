-- =============================================================================
-- Create notifications table
-- Run this if you already have an existing database
-- =============================================================================

CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('match', 'appointment', 'message') NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    related_id INT,  -- ID of related entity (match_id, appointment_id, etc.)
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, is_read),
    INDEX idx_created (created_at)
);
