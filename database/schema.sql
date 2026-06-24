-- Enhanced Database Schema for Silver Axis Pay Gateway
-- Compatible with portal rotations and standalone testing
-- 1. Admins Table
CREATE TABLE IF NOT EXISTS pay_admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. UPI Accounts Table (with default flags and rotation purpose)
CREATE TABLE IF NOT EXISTS pay_upi_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    upi_id VARCHAR(100) NOT NULL UNIQUE,
    payee_name VARCHAR(100) NOT NULL,
    active TINYINT(1) DEFAULT 1,
    is_default TINYINT(1) DEFAULT 0,
    purpose ENUM('all', 'intern', 'client') DEFAULT 'all',
    last_used_at TIMESTAMP NULL DEFAULT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active_default (active, is_default)
) ENGINE=InnoDB;

-- 3. Orders / Transactions Table (supporting customer inputs and portal links)
CREATE TABLE IF NOT EXISTS pay_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_no VARCHAR(50) NOT NULL UNIQUE,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'INR',
    upi_account_id INT NOT NULL,
    status ENUM('pending', 'under_review', 'approved', 'rejected', 'on_hold', 'expired') DEFAULT 'pending',
    utr_ref VARCHAR(100) DEFAULT NULL,
    payer_name VARCHAR(100) DEFAULT NULL,
    payer_notes TEXT DEFAULT NULL,
    admin_note TEXT DEFAULT NULL,
    payer_type VARCHAR(20) DEFAULT 'intern', -- 'intern', 'cert_user', 'client'
    payer_id INT DEFAULT NULL,              -- local user/intern reference ID
    reference_id VARCHAR(100) DEFAULT NULL,  -- certificate request ID or invoice ID
    user_ip VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (upi_account_id) REFERENCES pay_upi_accounts(id) ON DELETE RESTRICT,
    INDEX idx_order_no (order_no),
    INDEX idx_status_created (status, created_at),
    INDEX idx_utr (utr_ref)
) ENGINE=InnoDB;

-- 4. Activity Logs Table (Timeline)
CREATE TABLE IF NOT EXISTS pay_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT DEFAULT NULL,
    action_type VARCHAR(50) NOT NULL, -- 'login', 'approve_payment', 'reject_payment', 'hold_payment', 'add_upi', 'edit_upi', 'toggle_upi', 'delete_upi', 'submit_utr'
    order_no VARCHAR(50) DEFAULT NULL,
    details TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES pay_admins(id) ON DELETE SET NULL,
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Default Admin User (username: admin, password: admin123)
INSERT INTO pay_admins (username, password_hash)
VALUES ('admin', '$2y$10$gB3Pi0l.2db6e/mvwTbbJOQkQy16G9zJUh/WfR6QvfZcgyPAH9WCi')
ON DUPLICATE KEY UPDATE id=id;
