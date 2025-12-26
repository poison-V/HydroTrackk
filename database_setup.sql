-- ============================================
-- HydroTrack Database Setup Script
-- ============================================
-- This script creates the database and all necessary tables
-- Run this in phpMyAdmin or MySQL command line

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `hydrotrack_db` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Use the database
USE `hydrotrack_db`;

-- ============================================
-- Table: users
-- Stores user accounts for the POS system
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'cashier', 'manager') DEFAULT 'cashier',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: customers
-- Stores customer information
-- ============================================
CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20),
  `email` VARCHAR(255),
  `address` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_phone` (`phone`),
  INDEX `idx_name` (`full_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: products
-- Stores gallon sizes and pricing
-- ============================================
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `size` VARCHAR(50) NOT NULL,
  `size_key` VARCHAR(50) NOT NULL UNIQUE,
  `price_refill` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `price_borrow` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_size_key` (`size_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: inventory
-- Tracks gallon inventory
-- ============================================
CREATE TABLE IF NOT EXISTS `inventory` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `serial_number` VARCHAR(100) UNIQUE,
  `status` ENUM('in', 'out', 'borrowed', 'damaged') DEFAULT 'in',
  `customer_id` INT UNSIGNED,
  `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
  INDEX `idx_serial` (`serial_number`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: transactions
-- Stores all sales transactions
-- ============================================
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_number` VARCHAR(50) UNIQUE,
  `transaction_type` ENUM('Refill', 'Borrow', 'Returned') NOT NULL,
  `customer_id` INT UNSIGNED,
  `customer_name` VARCHAR(255),
  `customer_phone` VARCHAR(20),
  `customer_address` TEXT,
  `product_id` INT UNSIGNED,
  `product_size` VARCHAR(50),
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_method` ENUM('Cash', 'GCash', 'Card') DEFAULT 'Cash',
  `amount_paid` DECIMAL(10,2),
  `change_amount` DECIMAL(10,2),
  `delivery_type` ENUM('Delivery', 'Pickup') DEFAULT 'Pickup',
  `cashier_id` INT UNSIGNED,
  `cashier_name` VARCHAR(255),
  `status` ENUM('pending', 'completed', 'cancelled', 'returned') DEFAULT 'completed',
  `notes` TEXT,
  `transaction_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`cashier_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_invoice` (`invoice_number`),
  INDEX `idx_type` (`transaction_type`),
  INDEX `idx_date` (`transaction_date`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: stock_history
-- Tracks stock movements
-- ============================================
CREATE TABLE IF NOT EXISTS `stock_history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `action` ENUM('restock', 'sale', 'return', 'adjustment', 'damage') NOT NULL,
  `quantity` INT NOT NULL,
  `previous_stock` INT,
  `new_stock` INT,
  `user_id` INT UNSIGNED,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_product` (`product_id`),
  INDEX `idx_action` (`action`),
  INDEX `idx_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: expenses
-- Tracks business expenses
-- ============================================
CREATE TABLE IF NOT EXISTS `expenses` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` VARCHAR(255) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `category` VARCHAR(100),
  `user_id` INT UNSIGNED,
  `expense_date` DATE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_date` (`expense_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT SAMPLE DATA
-- ============================================

-- Sample Users (password for all: 'password123')
INSERT INTO `users` (`full_name`, `email`, `password_hash`, `role`, `is_active`) VALUES
('Admin User', 'admin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1),
('John Cashier', 'cashier@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cashier', 1),
('Maria Manager', 'manager@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 1);

-- Sample Customers
INSERT INTO `customers` (`full_name`, `phone`, `email`, `address`) VALUES
('Juan Dela Cruz', '09171234567', 'juan@gmail.com', '123 Main St, Manila'),
('Maria Santos', '09181234567', 'maria@gmail.com', '456 Rizal Ave, Quezon City'),
('Pedro Reyes', '09191234567', 'pedro@gmail.com', '789 Bonifacio St, Makati'),
('Ana Garcia', '09201234567', 'ana@gmail.com', '321 Luna St, Pasig'),
('Carlos Mendoza', '09211234567', 'carlos@gmail.com', '654 Mabini St, Mandaluyong');

-- Sample Products (Gallon Sizes)
INSERT INTO `products` (`name`, `size`, `size_key`, `price_refill`, `price_borrow`) VALUES
('20 Liter Slim Gallon', '20L Slim', '20LiterSlim', 25.00, 150.00),
('20 Liter Round Gallon', '20L Round', '20LiterRound', 25.00, 150.00),
('10 Liter Gallon', '10L', '10Liter', 15.00, 80.00),
('5 Liter Gallon', '5L', '5Liter', 10.00, 50.00);

-- Sample Transactions
INSERT INTO `transactions` 
(`invoice_number`, `transaction_type`, `customer_id`, `customer_name`, `customer_phone`, `customer_address`, 
 `product_id`, `product_size`, `quantity`, `unit_price`, `total_amount`, `payment_method`, 
 `amount_paid`, `change_amount`, `delivery_type`, `cashier_id`, `cashier_name`, `status`, `transaction_date`) 
VALUES
('INV-2025-001', 'Refill', 1, 'Juan Dela Cruz', '09171234567', '123 Main St, Manila', 
 1, '20L Slim', 2, 25.00, 50.00, 'Cash', 100.00, 50.00, 'Delivery', 2, 'John Cashier', 'completed', '2025-12-01 08:30:00'),
 
('INV-2025-002', 'Borrow', 2, 'Maria Santos', '09181234567', '456 Rizal Ave, Quezon City', 
 1, '20L Slim', 1, 150.00, 150.00, 'GCash', 150.00, 0.00, 'Pickup', 2, 'John Cashier', 'completed', '2025-12-01 09:15:00'),
 
('INV-2025-003', 'Refill', 3, 'Pedro Reyes', '09191234567', '789 Bonifacio St, Makati', 
 2, '20L Round', 3, 25.00, 75.00, 'Cash', 100.00, 25.00, 'Delivery', 2, 'John Cashier', 'completed', '2025-12-01 10:00:00'),
 
('INV-2025-004', 'Refill', 4, 'Ana Garcia', '09201234567', '321 Luna St, Pasig', 
 3, '10L', 2, 15.00, 30.00, 'Cash', 50.00, 20.00, 'Pickup', 2, 'John Cashier', 'completed', '2025-12-01 11:30:00'),
 
('INV-2025-005', 'Borrow', 5, 'Carlos Mendoza', '09211234567', '654 Mabini St, Mandaluyong', 
 4, '5L', 2, 50.00, 100.00, 'Card', 100.00, 0.00, 'Delivery', 2, 'John Cashier', 'completed', '2025-12-01 13:00:00');

-- Sample Inventory
INSERT INTO `inventory` (`product_id`, `serial_number`, `status`, `customer_id`) VALUES
(1, 'SLM-20-001', 'in', NULL),
(1, 'SLM-20-002', 'in', NULL),
(1, 'SLM-20-003', 'out', 1),
(1, 'SLM-20-004', 'borrowed', 2),
(2, 'RND-20-001', 'in', NULL),
(2, 'RND-20-002', 'in', NULL),
(2, 'RND-20-003', 'out', 3),
(3, '10L-001', 'in', NULL),
(3, '10L-002', 'in', NULL),
(4, '5L-001', 'in', NULL),
(4, '5L-002', 'borrowed', 5);

-- Sample Stock History
INSERT INTO `stock_history` (`product_id`, `action`, `quantity`, `previous_stock`, `new_stock`, `user_id`, `notes`) VALUES
(1, 'restock', 50, 0, 50, 1, 'Initial stock'),
(2, 'restock', 50, 0, 50, 1, 'Initial stock'),
(3, 'restock', 30, 0, 30, 1, 'Initial stock'),
(4, 'restock', 20, 0, 20, 1, 'Initial stock'),
(1, 'sale', -2, 50, 48, 2, 'Transaction INV-2025-001'),
(1, 'sale', -1, 48, 47, 2, 'Transaction INV-2025-002'),
(2, 'sale', -3, 50, 47, 2, 'Transaction INV-2025-003');

-- Sample Expenses
INSERT INTO `expenses` (`description`, `amount`, `category`, `user_id`, `expense_date`) VALUES
('Electricity Bill - November', 2500.00, 'Utilities', 1, '2025-11-30'),
('Water Bill - November', 800.00, 'Utilities', 1, '2025-11-30'),
('Gallon Cleaning Supplies', 1200.00, 'Supplies', 2, '2025-11-28'),
('Delivery Gas', 500.00, 'Transportation', 2, '2025-11-29'),
('Office Supplies', 350.00, 'Supplies', 1, '2025-11-27');

-- ============================================
-- VIEWS (Optional - for easier reporting)
-- ============================================

-- View: Daily Sales Summary
CREATE OR REPLACE VIEW `view_daily_sales` AS
SELECT 
    DATE(transaction_date) as sale_date,
    transaction_type,
    COUNT(*) as transaction_count,
    SUM(quantity) as total_quantity,
    SUM(total_amount) as total_sales
FROM transactions
WHERE status = 'completed'
GROUP BY DATE(transaction_date), transaction_type
ORDER BY sale_date DESC, transaction_type;

-- View: Customer Transaction History
CREATE OR REPLACE VIEW `view_customer_history` AS
SELECT 
    c.id as customer_id,
    c.full_name,
    c.phone,
    COUNT(t.id) as total_transactions,
    SUM(t.total_amount) as total_spent,
    MAX(t.transaction_date) as last_transaction
FROM customers c
LEFT JOIN transactions t ON c.id = t.customer_id
GROUP BY c.id, c.full_name, c.phone;

-- View: Current Stock Levels
CREATE OR REPLACE VIEW `view_stock_levels` AS
SELECT 
    p.id,
    p.name,
    p.size,
    p.size_key,
    COUNT(CASE WHEN i.status = 'in' THEN 1 END) as in_stock,
    COUNT(CASE WHEN i.status = 'out' THEN 1 END) as out_stock,
    COUNT(CASE WHEN i.status = 'borrowed' THEN 1 END) as borrowed,
    COUNT(CASE WHEN i.status = 'damaged' THEN 1 END) as damaged,
    COUNT(*) as total_gallons
FROM products p
LEFT JOIN inventory i ON p.id = i.product_id
GROUP BY p.id, p.name, p.size, p.size_key;

-- ============================================
-- COMPLETION MESSAGE
-- ============================================
SELECT 'Database setup completed successfully!' as message;
SELECT 'Default login credentials:' as info;
SELECT 'Email: admin@gmail.com, Password: password123' as admin_account;
SELECT 'Email: cashier@gmail.com, Password: password123' as cashier_account;
