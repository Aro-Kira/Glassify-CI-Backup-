-- =====================================================
-- GlassWorth BUILDERS Database Schema
-- =====================================================
-- This SQL file creates the complete database structure
-- for the GlassWorth BUILDERS inventory management system
-- =====================================================

-- Create database
CREATE DATABASE IF NOT EXISTS glassworth_builders CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE glassworth_builders;

-- =====================================================
-- Table: users
-- Stores user account information
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    member_since DATE DEFAULT (CURRENT_DATE),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: items
-- Stores inventory items
-- =====================================================
CREATE TABLE IF NOT EXISTS items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    item_code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    unit VARCHAR(50) NOT NULL,
    min_threshold INT DEFAULT 15,
    is_new_item BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_item_code (item_code),
    INDEX idx_category (category),
    INDEX idx_stock_quantity (stock_quantity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: products
-- Stores product catalog information
-- =====================================================
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    image VARCHAR(255) DEFAULT NULL,
    added_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_added_date (added_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: reports
-- Stores various system reports
-- =====================================================
CREATE TABLE IF NOT EXISTS reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    type ENUM('sales', 'inventory', 'purchases', 'products') NOT NULL,
    description TEXT NOT NULL,
    amount DECIMAL(12, 2) DEFAULT 0.00,
    status ENUM('completed', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_date (date),
    INDEX idx_type (type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: activities
-- Stores activity log for system events
-- =====================================================
CREATE TABLE IF NOT EXISTS activities (
    activity_id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(100) NOT NULL,
    item_name VARCHAR(255) DEFAULT NULL,
    change_description VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id INT DEFAULT NULL,
    item_id INT DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (item_id) REFERENCES items(item_id) ON DELETE SET NULL,
    INDEX idx_timestamp (timestamp),
    INDEX idx_action (action),
    INDEX idx_item_id (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: stock_transactions
-- Stores stock movement history
-- =====================================================
CREATE TABLE IF NOT EXISTS stock_transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    transaction_type ENUM('add', 'remove', 'adjust') NOT NULL,
    quantity INT NOT NULL,
    reason TEXT DEFAULT NULL,
    previous_stock INT DEFAULT NULL,
    new_stock INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(item_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_item_id (item_id),
    INDEX idx_timestamp (timestamp),
    INDEX idx_transaction_type (transaction_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Insert Sample Data
-- =====================================================

-- Insert default admin user
-- Password: admin123 (hashed with password_hash PHP function)
-- In production, use: password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (name, email, password, role, member_since) VALUES
('Admin User', 'admin@glassworth.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '2024-01-01');

-- Insert sample inventory items
INSERT INTO items (item_code, name, category, stock_quantity, unit, min_threshold, is_new_item) VALUES
('ITM001', 'Cement', 'Building Materials', 100, 'Bags', 20, FALSE),
('ITM002', 'Steel Bars', 'Building Materials', 75, 'Pieces', 15, FALSE),
('ITM003', 'Paint', 'Finishing', 10, 'Cans', 15, FALSE),
('ITM004', 'Wood Planks', 'Lumber', 0, 'Pieces', 15, FALSE),
('ITM005', 'Nails', 'Hardware', 200, 'Boxes', 20, FALSE),
('ITM006', 'Screws', 'Hardware', 150, 'Boxes', 15, TRUE),
('ITM007', 'Tempered Glass 6mm', 'Glass', 3, 'Sheets', 25, FALSE),
('ITM008', 'Aluminum Frame', 'Aluminum', 50, 'Pieces', 15, FALSE),
('ITM009', 'Mirror Tiles', 'Glass', 25, 'Sheets', 20, FALSE),
('ITM010', 'Handle Set', 'Hardware', 2, 'Sets', 10, FALSE);

-- Insert sample products
INSERT INTO products (name, category, price, added_date) VALUES
('Tempered Glass 6mm', 'Glass', 1250.00, '2025-01-15'),
('Aluminum Window Frame', 'Aluminum', 3200.00, '2025-01-14'),
('Premium Paint White', 'Finishing', 850.00, '2025-01-13'),
('Steel Rebar 10mm', 'Building Materials', 450.00, '2025-01-12'),
('Plywood 1/2 inch', 'Lumber', 680.00, '2025-01-11'),
('Door Hinge Set', 'Hardware', 350.00, '2025-01-10'),
('Clear Glass 4mm', 'Glass', 980.00, '2025-01-09'),
('Aluminum Door Frame', 'Aluminum', 2800.00, '2025-01-08'),
('Interior Paint Beige', 'Finishing', 920.00, '2025-01-07'),
('Cement Bag 40kg', 'Building Materials', 280.00, '2025-01-06'),
('Hardwood Plank', 'Lumber', 1200.00, '2025-01-05'),
('Door Lock Set', 'Hardware', 650.00, '2025-01-04'),
('Frosted Glass 6mm', 'Glass', 1350.00, '2025-01-03'),
('Aluminum Panel', 'Aluminum', 1950.00, '2025-01-02'),
('Varnish Clear', 'Finishing', 580.00, '2025-01-01'),
('Sand Gravel Mix', 'Building Materials', 320.00, '2024-12-30'),
('Pine Wood Board', 'Lumber', 450.00, '2024-12-29'),
('Cabinet Handle', 'Hardware', 180.00, '2024-12-28'),
('Safety Glass 8mm', 'Glass', 1650.00, '2024-12-27'),
('Aluminum Extrusion', 'Aluminum', 2200.00, '2024-12-26');

-- Insert sample reports
INSERT INTO reports (date, type, description, amount, status) VALUES
('2025-01-15', 'sales', 'Monthly Sales Report - January 2025', 245680.00, 'completed'),
('2025-01-14', 'inventory', 'Inventory Stock Report', 0.00, 'completed'),
('2025-01-13', 'purchases', 'Purchase Order Summary', 125000.00, 'pending'),
('2025-01-12', 'products', 'Product Performance Report', 0.00, 'completed'),
('2025-01-11', 'sales', 'Weekly Sales Report', 58750.00, 'completed'),
('2025-01-10', 'inventory', 'Low Stock Alert Report', 0.00, 'completed'),
('2025-01-09', 'purchases', 'Vendor Payment Report', 89000.00, 'completed'),
('2025-01-08', 'products', 'Top Selling Products', 0.00, 'completed'),
('2025-01-07', 'sales', 'Daily Sales Summary', 12350.00, 'completed'),
('2025-01-06', 'inventory', 'Stock Movement Report', 0.00, 'pending'),
('2025-01-05', 'purchases', 'Material Purchase Report', 156000.00, 'completed'),
('2025-01-04', 'sales', 'Customer Sales Analysis', 189500.00, 'completed'),
('2025-01-03', 'products', 'Product Category Report', 0.00, 'completed'),
('2025-01-02', 'inventory', 'Year-End Inventory Count', 0.00, 'completed'),
('2025-01-01', 'sales', 'New Year Sales Report', 45000.00, 'completed'),
('2024-12-30', 'purchases', 'Monthly Purchase Summary', 234000.00, 'completed'),
('2024-12-29', 'sales', 'December Sales Report', 312000.00, 'completed'),
('2024-12-28', 'products', 'Product Return Report', 0.00, 'completed'),
('2024-12-27', 'inventory', 'Warehouse Stock Report', 0.00, 'completed'),
('2024-12-26', 'sales', 'Holiday Sales Report', 98750.00, 'completed');

-- Insert sample activities
INSERT INTO activities (action, item_name, change_description, description, timestamp) VALUES
('Threshold updated', 'Tempered Glass 6mm', 'Min: 15 → 25', 'Increased due to demand', '2025-05-28 09:45:00'),
('Stock reduced', 'Mirror Tiles', '-5 sheets', 'Damaged during handling', '2025-05-28 08:30:00'),
('Stock added', 'Aluminum Frame', '+20 pieces', 'New delivery from supplier', '2025-05-27 17:12:00'),
('Item created', 'Aluminum Frame', '50 pieces initial', 'System', '2025-05-27 14:15:00'),
('Item created', 'Handle Set', '2 sets initial', 'System', '2025-05-27 14:15:00'),
('Stock added', 'Cement', '+50 bags', 'New delivery', '2025-05-26 10:00:00'),
('Stock reduced', 'Paint', '-5 cans', 'Used for project', '2025-05-25 15:30:00'),
('Item created', 'Screws', '150 boxes initial', 'System', '2025-05-24 11:20:00');

-- Insert sample stock transactions
INSERT INTO stock_transactions (item_id, transaction_type, quantity, reason, previous_stock, new_stock, user_id) VALUES
(7, 'remove', 5, 'Damaged during handling', 8, 3, 1),
(8, 'add', 20, 'New delivery from supplier', 30, 50, 1),
(1, 'add', 50, 'New delivery', 50, 100, 1),
(3, 'remove', 5, 'Used for project', 15, 10, 1);

-- =====================================================
-- Views for easier data access
-- =====================================================

-- View: Low Stock Items
CREATE OR REPLACE VIEW v_low_stock_items AS
SELECT 
    item_id,
    item_code,
    name,
    category,
    stock_quantity,
    unit,
    min_threshold,
    (min_threshold - stock_quantity) AS shortage
FROM items
WHERE stock_quantity < min_threshold AND stock_quantity > 0
ORDER BY shortage DESC;

-- View: Out of Stock Items
CREATE OR REPLACE VIEW v_out_of_stock_items AS
SELECT 
    item_id,
    item_code,
    name,
    category,
    stock_quantity,
    unit,
    min_threshold
FROM items
WHERE stock_quantity = 0
ORDER BY name;

-- View: New Items
CREATE OR REPLACE VIEW v_new_items AS
SELECT 
    item_id,
    item_code,
    name,
    category,
    stock_quantity,
    unit,
    created_at
FROM items
WHERE is_new_item = TRUE
ORDER BY created_at DESC;

-- View: Recent Activities Summary
CREATE OR REPLACE VIEW v_recent_activities AS
SELECT 
    activity_id,
    action,
    item_name,
    change_description,
    description,
    timestamp,
    DATE_FORMAT(timestamp, '%m/%d/%Y - %h:%i %p') AS formatted_timestamp
FROM activities
ORDER BY timestamp DESC
LIMIT 50;

-- View: Stock Status Summary
CREATE OR REPLACE VIEW v_stock_status_summary AS
SELECT 
    category,
    COUNT(*) AS total_items,
    SUM(CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END) AS out_of_stock,
    SUM(CASE WHEN stock_quantity > 0 AND stock_quantity < min_threshold THEN 1 ELSE 0 END) AS low_stock,
    SUM(CASE WHEN stock_quantity >= min_threshold THEN 1 ELSE 0 END) AS in_stock,
    SUM(stock_quantity) AS total_stock
FROM items
GROUP BY category;

-- =====================================================
-- Stored Procedures
-- =====================================================

-- Procedure: Add Stock
DELIMITER //
CREATE PROCEDURE sp_add_stock(
    IN p_item_id INT,
    IN p_quantity INT,
    IN p_reason TEXT,
    IN p_user_id INT
)
BEGIN
    DECLARE v_previous_stock INT;
    DECLARE v_new_stock INT;
    
    -- Get current stock
    SELECT stock_quantity INTO v_previous_stock
    FROM items
    WHERE item_id = p_item_id;
    
    -- Calculate new stock
    SET v_new_stock = v_previous_stock + p_quantity;
    
    -- Update item stock
    UPDATE items
    SET stock_quantity = v_new_stock,
        updated_at = CURRENT_TIMESTAMP
    WHERE item_id = p_item_id;
    
    -- Record transaction
    INSERT INTO stock_transactions (item_id, transaction_type, quantity, reason, previous_stock, new_stock, user_id)
    VALUES (p_item_id, 'add', p_quantity, p_reason, v_previous_stock, v_new_stock, p_user_id);
    
    -- Log activity
    INSERT INTO activities (action, item_id, item_name, change_description, description, user_id)
    SELECT 
        'Stock added',
        p_item_id,
        name,
        CONCAT('+', p_quantity, ' ', unit),
        p_reason,
        p_user_id
    FROM items
    WHERE item_id = p_item_id;
    
    SELECT v_new_stock AS new_stock;
END //
DELIMITER ;

-- Procedure: Remove Stock
DELIMITER //
CREATE PROCEDURE sp_remove_stock(
    IN p_item_id INT,
    IN p_quantity INT,
    IN p_reason TEXT,
    IN p_user_id INT
)
BEGIN
    DECLARE v_previous_stock INT;
    DECLARE v_new_stock INT;
    
    -- Get current stock
    SELECT stock_quantity INTO v_previous_stock
    FROM items
    WHERE item_id = p_item_id;
    
    -- Calculate new stock (cannot go below 0)
    SET v_new_stock = GREATEST(0, v_previous_stock - p_quantity);
    
    -- Update item stock
    UPDATE items
    SET stock_quantity = v_new_stock,
        updated_at = CURRENT_TIMESTAMP
    WHERE item_id = p_item_id;
    
    -- Record transaction
    INSERT INTO stock_transactions (item_id, transaction_type, quantity, reason, previous_stock, new_stock, user_id)
    VALUES (p_item_id, 'remove', p_quantity, p_reason, v_previous_stock, v_new_stock, p_user_id);
    
    -- Log activity
    INSERT INTO activities (action, item_id, item_name, change_description, description, user_id)
    SELECT 
        'Stock reduced',
        p_item_id,
        name,
        CONCAT('-', p_quantity, ' ', unit),
        p_reason,
        p_user_id
    FROM items
    WHERE item_id = p_item_id;
    
    SELECT v_new_stock AS new_stock;
END //
DELIMITER ;

-- Procedure: Update Threshold
DELIMITER //
CREATE PROCEDURE sp_update_threshold(
    IN p_item_id INT,
    IN p_new_threshold INT,
    IN p_reason TEXT,
    IN p_user_id INT
)
BEGIN
    DECLARE v_old_threshold INT;
    DECLARE v_item_name VARCHAR(255);
    DECLARE v_unit VARCHAR(50);
    
    -- Get current threshold and item info
    SELECT min_threshold, name, unit 
    INTO v_old_threshold, v_item_name, v_unit
    FROM items
    WHERE item_id = p_item_id;
    
    -- Update threshold
    UPDATE items
    SET min_threshold = p_new_threshold,
        updated_at = CURRENT_TIMESTAMP
    WHERE item_id = p_item_id;
    
    -- Log activity
    INSERT INTO activities (action, item_id, item_name, change_description, description, user_id)
    VALUES (
        'Threshold updated',
        p_item_id,
        v_item_name,
        CONCAT('Min: ', v_old_threshold, ' → ', p_new_threshold),
        p_reason,
        p_user_id
    );
    
    SELECT p_new_threshold AS new_threshold;
END //
DELIMITER ;

-- =====================================================
-- Triggers
-- =====================================================

-- Trigger: Auto-generate item_code on insert
DELIMITER //
CREATE TRIGGER trg_generate_item_code
BEFORE INSERT ON items
FOR EACH ROW
BEGIN
    IF NEW.item_code IS NULL OR NEW.item_code = '' THEN
        SET NEW.item_code = CONCAT('ITM', LPAD((SELECT COALESCE(MAX(CAST(SUBSTRING(item_code, 4) AS UNSIGNED)), 0) + 1 FROM items), 3, '0'));
    END IF;
END //
DELIMITER ;

-- Trigger: Log item creation
DELIMITER //
CREATE TRIGGER trg_log_item_creation
AFTER INSERT ON items
FOR EACH ROW
BEGIN
    INSERT INTO activities (action, item_id, item_name, change_description, description)
    VALUES (
        'Item created',
        NEW.item_id,
        NEW.name,
        CONCAT(NEW.stock_quantity, ' ', NEW.unit, ' initial'),
        'System'
    );
END //
DELIMITER ;

-- =====================================================
-- End of Database Schema
-- =====================================================

