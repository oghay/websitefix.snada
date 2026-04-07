-- ============================================================
-- Migration: Add Notification Logs Table
-- Run this in phpMyAdmin after migration_tracking.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS notification_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    channel VARCHAR(30) NOT NULL DEFAULT 'whatsapp',
    provider VARCHAR(50) NOT NULL DEFAULT 'fonnte',
    recipient VARCHAR(50) NOT NULL,
    message TEXT,
    status ENUM('sent','failed') DEFAULT 'failed',
    response VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_notif_logs_created ON notification_logs(created_at);
CREATE INDEX idx_notif_logs_status ON notification_logs(status);
