-- Create notifications table without foreign key constraints first
-- Run this in phpMyAdmin SQL tab

DROP TABLE IF EXISTS notifications;

CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('match', 'message', 'appointment', 'inquiry', 'system') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    related_id INT NULL,
    related_user_id INT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    
    INDEX idx_user_id (user_id),
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert test data
INSERT INTO notifications (user_id, type, title, message, related_user_id, is_read) VALUES
(1, 'match', 'New Match!', 'You have a new mutual match with Carelene Canque', 2, 0),
(1, 'message', 'New Message', 'Carelene Canque sent you a message', 2, 0),
(1, 'appointment', 'Appointment Confirmed', 'Your viewing appointment has been confirmed', NULL, 1);
