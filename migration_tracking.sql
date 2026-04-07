-- ============================================================
-- Migration: Add Order Tracking Tables
-- Run this in phpMyAdmin if you already have the database
-- ============================================================

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_code VARCHAR(30) UNIQUE NOT NULL,
    client_name VARCHAR(255) NOT NULL,
    client_email VARCHAR(255),
    client_phone VARCHAR(50),
    client_institution VARCHAR(255),
    service_type VARCHAR(100) DEFAULT 'setup_ojs',
    package_tier VARCHAR(50) DEFAULT '',
    description TEXT,
    status ENUM('pending','in_progress','completed','cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_milestones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending','in_progress','completed') DEFAULT 'pending',
    completed_at DATETIME NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Index for fast tracking code lookups
CREATE INDEX idx_orders_tracking ON orders(tracking_code);
CREATE INDEX idx_milestones_order ON order_milestones(order_id, sort_order);
