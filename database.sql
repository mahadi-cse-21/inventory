-- Inventory Pro - Complete Database Schema
-- This SQL script creates all tables needed for the Inventory Pro system

-- Drop database if it exists and create a new one
DROP DATABASE IF EXISTS inventory_pro_1;
CREATE DATABASE inventory_pro_1;
USE inventory_pro_1;

-- Set character set
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `users`
-- -----------------------------------------------------
CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NULL,
 
  `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATE NOT NULL,

  `updated_at` DATE NULL,

  PRIMARY KEY (`id`),
  UNIQUE INDEX `username_UNIQUE` (`username` ASC),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table `locations`
-- -----------------------------------------------------
CREATE TABLE `locations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `address` VARCHAR(255) NULL,
  `building` VARCHAR(100) NULL,
  `floor` VARCHAR(50) NULL,
  `room` VARCHAR(50) NULL,
  `capacity` INT NULL,
  `manager_id` INT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATE NOT NULL,
  `updated_at` DATE NULL,
  PRIMARY KEY (`id`),
    FOREIGN KEY (`manager_id`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table `categories`
-- -----------------------------------------------------
CREATE TABLE `categories` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `parent_id` INT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
    FOREIGN KEY (`parent_id`)
    REFERENCES `categories` (`id`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table `suppliers`
-- -----------------------------------------------------
CREATE TABLE `suppliers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `contact_person` VARCHAR(100) NULL,
  `email` VARCHAR(100) NULL,
  `phone` VARCHAR(20) NULL,
  `address` TEXT NULL,
  `website` VARCHAR(255) NULL,
  `notes` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table `items`
-- -----------------------------------------------------
CREATE TABLE `items` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `category_id` INT NULL,
  `location_id` INT NULL,
  `supplier_id` INT NULL,
  `brand` VARCHAR(100) NULL,
  `model` VARCHAR(100) NULL,
  `model_number` VARCHAR(100) NULL,
  `serial_number` VARCHAR(100) NULL,
  `barcode` VARCHAR(100) NULL,
  `status` ENUM('available', 'borrowed', 'maintenance', 'reserved', 'unavailable', 'retired') NOT NULL DEFAULT 'available',
  `condition_rating` ENUM('new', 'excellent', 'good', 'fair', 'poor', 'damaged') NOT NULL DEFAULT 'good',
  `description` TEXT NULL,
  `specifications` TEXT NULL,
  `notes` TEXT NULL,
  `purchase_date` DATE NULL,
  `purchase_price` DECIMAL(10,2) NULL,
  `warranty_expiry` DATE NULL,
  `current_value` DECIMAL(10,2) NULL,
  `maintenance_interval` INT NULL,
  `next_maintenance_date` DATE NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  `created_by` INT NULL,
  `updated_at` DATETIME NULL,
  `updated_by` INT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `slug_UNIQUE` (`slug` ASC),
  UNIQUE INDEX `asset_id_UNIQUE` (`asset_id` ASC),
  UNIQUE INDEX `barcode_UNIQUE` (`barcode` ASC),

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- -----------------------------------------------------
-- Table `item_images`
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Table `borrow_requests`
-- -----------------------------------------------------
CREATE TABLE `borrow_requests` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `purpose` VARCHAR(255) NOT NULL,
  `project_name` VARCHAR(100) NULL,
  `borrow_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `pickup_time` TIME NULL,
  `return_time` TIME NULL,
  `return_date` DATE NULL,
  `status` ENUM('pending', 'approved', 'rejected', 'cancelled', 'checked_out', 'overdue', 'partially_returned', 'returned') NOT NULL DEFAULT 'pending',

  `approved_by` INT NULL,


) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table `borrowed_items`
-- -----------------------------------------------------
CREATE TABLE `borrow_items` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `borrow_request_id` INT NOT NULL,
  `item_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `condition_before` ENUM('new', 'excellent', 'good', 'fair', 'poor', 'damaged') NULL,
  `condition_after` ENUM('new', 'excellent', 'good', 'fair', 'poor', 'damaged') NULL,
  `status` ENUM('pending', 'checked_out', 'returned') NOT NULL DEFAULT 'pending',
  `is_returned` TINYINT(1) NOT NULL DEFAULT 0,
 
  `returned_at` DATETIME NULL,
 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table `cart_items`
-- -----------------------------------------------------
CREATE TABLE `cart_items` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `item_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `planned_borrow_date` DATE NULL,
  `planned_return_date` DATE NULL,
  `notes` TEXT NULL,
  `added_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_cart_items_user_id_idx` (`user_id` ASC),
  INDEX `fk_cart_items_item_id_idx` (`item_id` ASC),
  CONSTRAINT `fk_cart_items_user_id`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_cart_items_item_id`
    FOREIGN KEY (`item_id`)
    REFERENCES `items` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table `reservations`
-- -----------------------------------------------------
CREATE TABLE `reservations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `item_id` INT NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `purpose` VARCHAR(255) NULL,
  `status` ENUM('pending', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table `maintenance_records`
-- -----------------------------------------------------
CREATE TABLE `maintenance_records` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `item_id` INT NOT NULL,
  `maintenance_type` ENUM('repair', 'inspection', 'cleaning', 'calibration', 'upgrade', 'other') NOT NULL,
  `description` TEXT NOT NULL,
  `requested_by` INT NULL,
  `assigned_to` INT NULL,
  `status` ENUM('requested', 'scheduled', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'requested',
  `estimated_cost` DECIMAL(10,2) NULL,
  `actual_cost` DECIMAL(10,2) NULL,
  `parts_used` TEXT NULL,
  `start_date` DATE NULL,
  `end_date` DATE NULL,
  `resolution` TEXT NULL,
  `notes` TEXT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_maintenance_records_item_id_idx` (`item_id` ASC),
  INDEX `fk_maintenance_records_requested_by_idx` (`requested_by` ASC),
  INDEX `fk_maintenance_records_assigned_to_idx` (`assigned_to` ASC),
  CONSTRAINT `fk_maintenance_records_item_id`
    FOREIGN KEY (`item_id`)
    REFERENCES `items` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_maintenance_records_requested_by`
    FOREIGN KEY (`requested_by`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_maintenance_records_assigned_to`
    FOREIGN KEY (`assigned_to`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table `damage_reports`
-- -----------------------------------------------------
CREATE TABLE `damage_reports` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `item_id` INT NOT NULL,
  `reported_by` INT NOT NULL,
  `damage_date` DATE NOT NULL,
  `severity` ENUM('low', 'medium', 'high', 'critical') NOT NULL,
  `description` TEXT NOT NULL,
  `status` ENUM('open', 'in_progress', 'pending_maintenance', 'resolved') NOT NULL DEFAULT 'open',
  `resolution` TEXT NULL,
  `resolution_date` DATE NULL,
  `maintenance_record_id` INT NULL,
  `notes` TEXT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_damage_reports_item_id_idx` (`item_id` ASC),
  INDEX `fk_damage_reports_reported_by_idx` (`reported_by` ASC),
  INDEX `fk_damage_reports_maintenance_record_id_idx` (`maintenance_record_id` ASC),
  CONSTRAINT `fk_damage_reports_item_id`
    FOREIGN KEY (`item_id`)
    REFERENCES `items` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_damage_reports_reported_by`
    FOREIGN KEY (`reported_by`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_damage_reports_maintenance_record_id`
    FOREIGN KEY (`maintenance_record_id`)
    REFERENCES `maintenance_records` (`id`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table `damage_images`
-- -----------------------------------------------------
CREATE TABLE `damage_images` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `damage_report_id` INT NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_size` INT NULL,
  `file_type` VARCHAR(50) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_damage_images_damage_report_id_idx` (`damage_report_id` ASC),
  CONSTRAINT `fk_damage_images_damage_report_id`
    FOREIGN KEY (`damage_report_id`)
    REFERENCES `damage_reports` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table `inventory_transactions`
-- -----------------------------------------------------
CREATE TABLE `inventory_transactions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `item_id` INT NOT NULL,
  `transaction_type` ENUM('purchase', 'check_out', 'check_in', 'transfer', 'maintenance', 'retire', 'adjust') NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `from_location_id` INT NULL,
  `to_location_id` INT NULL,
  `related_record_type` VARCHAR(50) NULL,
  `related_record_id` INT NULL,
  `performed_by` INT NULL,
  `notes` TEXT NULL,
  `transaction_date` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_inventory_transactions_item_id_idx` (`item_id` ASC),
  INDEX `fk_inventory_transactions_from_location_id_idx` (`from_location_id` ASC),
  INDEX `fk_inventory_transactions_to_location_id_idx` (`to_location_id` ASC),
  INDEX `fk_inventory_transactions_performed_by_idx` (`performed_by` ASC),
  CONSTRAINT `fk_inventory_transactions_item_id`
    FOREIGN KEY (`item_id`)
    REFERENCES `items` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_inventory_transactions_from_location_id`
    FOREIGN KEY (`from_location_id`)
    REFERENCES `locations` (`id`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_inventory_transactions_to_location_id`
    FOREIGN KEY (`to_location_id`)
    REFERENCES `locations` (`id`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_inventory_transactions_performed_by`
    FOREIGN KEY (`performed_by`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;






-- -----------------------------------------------------
-- Table `settings`
-- -----------------------------------------------------
-- Create the settings table
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `default_value` text DEFAULT NULL,
  `setting_group` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `field_type` varchar(20) NOT NULL DEFAULT 'text',
  `options` text DEFAULT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `validation_rules` varchar(255) DEFAULT NULL,
  `order_index` int(11) NOT NULL DEFAULT 0,
  `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Insert Default Data
-- -----------------------------------------------------

-- Insert admin user (password: password)
INSERT INTO `users` (`username`, `password`, `email`, `full_name`, `role`, `is_active`, `created_at`) VALUES
('admin', '$2y$12$8qVK4325MLOAQV0eYO/z4O7lYMCh.s2WaWfxEkzdT3.R4TpNiMF3W', 'admin@example.com', 'System Administrator', 'admin', 1, NOW());

-- Insert default departments
INSERT INTO `departments` (`name`, `description`, `is_active`, `created_at`) VALUES
('IT Department', 'Information Technology Department', 1, NOW()),
('Marketing', 'Marketing and Communications Department', 1, NOW()),
('Sales', 'Sales and Business Development Department', 1, NOW()),
('Human Resources', 'Human Resources Department', 1, NOW()),
('Finance', 'Finance and Accounting Department', 1, NOW()),
('Operations', 'Operations and Logistics Department', 1, NOW());

-- Insert default locations
INSERT INTO `locations` (`name`, `description`, `address`, `building`, `floor`, `capacity`, `is_active`, `created_at`) VALUES
('Main Warehouse', 'Primary storage location for inventory', '123 Warehouse Blvd, Suite 100', 'Building A', '1', 1000, 1, NOW()),
('IT Department', 'IT department storage area', '456 Corporate Drive', 'Headquarters', '3', 200, 1, NOW()),
('Marketing Department', 'Marketing department storage area', '456 Corporate Drive', 'Headquarters', '2', 100, 1, NOW()),
('Office Supplies', 'Office supplies storage room', '456 Corporate Drive', 'Headquarters', '1', 300, 1, NOW()),
('Conference Room A', 'Main conference room', '456 Corporate Drive', 'Headquarters', '1', 50, 1, NOW()),
('Design Lab', 'Design and creative workspace', '456 Corporate Drive', 'Headquarters', '2', 75, 1, NOW());

-- Insert default categories
INSERT INTO `categories` (`name`, `description`, `is_active`, `created_at`) VALUES
('Electronics', 'Electronic devices and equipment', 1, NOW()),
('Computers', 'Computers, laptops, and accessories', 1, NOW()),
('Audio Equipment', 'Audio recording and playback devices', 1, NOW()),
('Video Equipment', 'Video recording and display devices', 1, NOW()),
('Office Equipment', 'General office equipment', 1, NOW()),
('Furniture', 'Office furniture and fixtures', 1, NOW()),
('Photography', 'Photography equipment and accessories', 1, NOW());

-- Update parent_id for sub-categories
UPDATE `categories` SET `parent_id` = (SELECT `id` FROM (SELECT * FROM `categories`) AS temp WHERE `name` = 'Electronics') WHERE `name` = 'Computers';
UPDATE `categories` SET `parent_id` = (SELECT `id` FROM (SELECT * FROM `categories`) AS temp WHERE `name` = 'Electronics') WHERE `name` = 'Audio Equipment';
UPDATE `categories` SET `parent_id` = (SELECT `id` FROM (SELECT * FROM `categories`) AS temp WHERE `name` = 'Electronics') WHERE `name` = 'Video Equipment';

-- Insert default suppliers
INSERT INTO `suppliers` (`name`, `contact_person`, `email`, `phone`, `address`, `website`, `is_active`, `created_at`) VALUES
('TechSupplies Inc.', 'John Doe', 'contact@techsupplies.com', '555-123-4567', '789 Supplier Street, Tech City', 'www.techsupplies.com', 1, NOW()),
('Office Depot', 'Jane Smith', 'sales@officedepot.com', '555-987-6543', '456 Office Lane, Business Park', 'www.officedepot.com', 1, NOW()),
('Electronics Warehouse', 'Bob Johnson', 'info@electronicswarehouse.com', '555-555-1234', '123 Electronic Avenue, Tech City', 'www.electronicswarehouse.com', 1, NOW());

-- Insert default permissions
INSERT INTO `permissions` (`name`, `description`, `category`) VALUES
('view_items', 'View inventory items', 'Inventory'),
('add_items', 'Add new inventory items', 'Inventory'),
('edit_items', 'Edit existing inventory items', 'Inventory'),
('delete_items', 'Delete inventory items', 'Inventory'),
('view_categories', 'View item categories', 'Inventory'),
('manage_categories', 'Manage item categories', 'Inventory'),
('view_users', 'View user accounts', 'Users'),
('add_users', 'Add new user accounts', 'Users'),
('edit_users', 'Edit existing user accounts', 'Users'),
('delete_users', 'Delete user accounts', 'Users'),
('view_borrow_requests', 'View borrow requests', 'Borrowing'),
('create_borrow_requests', 'Create borrow requests', 'Borrowing'),
('approve_borrow_requests', 'Approve borrow requests', 'Borrowing'),
('reject_borrow_requests', 'Reject borrow requests', 'Borrowing'),
('checkout_items', 'Check out items to users', 'Borrowing'),
('return_items', 'Process item returns', 'Borrowing'),
('view_locations', 'View storage locations', 'Locations'),
('manage_locations', 'Manage storage locations', 'Locations'),
('view_maintenance', 'View maintenance records', 'Maintenance'),
('manage_maintenance', 'Manage maintenance records', 'Maintenance'),
('view_reports', 'View reports and analytics', 'Reports'),
('export_data', 'Export data from the system', 'Reports'),
('manage_settings', 'Manage system settings', 'System');

-- Set default role permissions
-- Admin role permissions (all permissions)
INSERT INTO `role_permissions` (`role`, `permission_id`)
SELECT 'admin', `id` FROM `permissions`;

-- Manager role permissions
INSERT INTO `role_permissions` (`role`, `permission_id`)
SELECT 'manager', `id` FROM `permissions` WHERE `name` IN (
    'view_items', 'add_items', 'edit_items', 'view_categories', 'manage_categories',
    'view_users', 'view_borrow_requests', 'create_borrow_requests', 'approve_borrow_requests',
    'reject_borrow_requests', 'checkout_items', 'return_items', 'view_locations',
    'manage_locations', 'view_maintenance', 'manage_maintenance', 'view_reports', 'export_data'
);

-- User role permissions
INSERT INTO `role_permissions` (`role`, `permission_id`)
SELECT 'user', `id` FROM `permissions` WHERE `name` IN (
    'view_items', 'view_categories', 'create_borrow_requests', 'view_locations'
);

-- Insert sample items
INSERT INTO `items` (`name`, `slug`, `asset_id`, `category_id`, `location_id`, `supplier_id`, `brand`, `model`, 
                    `model_number`, `serial_number`, `barcode`, `status`, `condition_rating`, `description`, 
                    `purchase_date`, `purchase_price`, `is_active`, `created_at`, `created_by`)
VALUES
('Dell XPS 15 Laptop', 'dell-xps-15-laptop', 'LP-2023-089', 
 (SELECT `id` FROM `categories` WHERE `name` = 'Computers'), 
 (SELECT `id` FROM `locations` WHERE `name` = 'IT Department'), 
 (SELECT `id` FROM `suppliers` WHERE `name` = 'TechSupplies Inc.'), 
 'Dell', 'XPS 15', '9510', 'DX95105872342', '8901234567890', 'available', 'excellent', 
 'The Dell XPS 15 is a high-performance laptop designed for professional use. This laptop features a 15.6" 4K UHD display, Intel Core i7 processor, 16GB RAM, and 512GB SSD storage.',
 '2023-07-15', 1899.99, 1, NOW(), 1),

('Sony A7 III Camera', 'sony-a7-iii-camera', 'CAM-2022-042', 
 (SELECT `id` FROM `categories` WHERE `name` = 'Photography'), 
 (SELECT `id` FROM `locations` WHERE `name` = 'Marketing Department'), 
 (SELECT `id` FROM `suppliers` WHERE `name` = 'Electronics Warehouse'), 
 'Sony', 'Alpha A7 III', 'ILCE-7M3', 'S7M3982371', '7890123456789', 'available', 'good', 
 'Full-frame mirrorless camera with excellent low-light performance, 24.2MP sensor, and 4K video recording capabilities.',
 '2022-04-10', 1999.99, 1, NOW(), 1),

('Noise-Cancelling Headphones', 'noise-cancelling-headphones', 'AUD-WL-005', 
 (SELECT `id` FROM `categories` WHERE `name` = 'Audio Equipment'), 
 (SELECT `id` FROM `locations` WHERE `name` = 'Marketing Department'), 
 (SELECT `id` FROM `suppliers` WHERE `name` = 'Electronics Warehouse'), 
 'Sony', 'WH-1000XM4', 'WH1000XM4B', 'S10XM478935', '6789012345678', 'available', 'excellent', 
 'Premium wireless noise-cancelling headphones with 30-hour battery life and exceptional sound quality.',
 '2023-03-05', 349.99, 1, NOW(), 1),

('4K Video Projector', '4k-video-projector', 'PROJ-2021-007', 
 (SELECT `id` FROM `categories` WHERE `name` = 'Video Equipment'), 
 (SELECT `id` FROM `locations` WHERE `name` = 'Conference Room A'), 
 (SELECT `id` FROM `suppliers` WHERE `name` = 'Electronics Warehouse'), 
 'Epson', 'Pro EX9240', 'EX9240', 'EPX924056789', '5678901234567', 'available', 'good', 
 'High-brightness 4K projector ideal for presentations and video content in meeting rooms and small auditoriums.',
 '2021-11-20', 999.99, 1, NOW(), 1),

('iPad Pro 12.9"', 'ipad-pro-129', 'TAB-2022-031', 
 (SELECT `id` FROM `categories` WHERE `name` = 'Computers'), 
 (SELECT `id` FROM `locations` WHERE `name` = 'Design Lab'), 
 (SELECT `id` FROM `suppliers` WHERE `name` = 'TechSupplies Inc.'), 
 'Apple', 'iPad Pro', 'A2378', 'FPDY87GHJ9L0', '4567890123456', 'available', 'excellent', 
 '12.9-inch iPad Pro with Liquid Retina XDR display, M1 chip, and support for Apple Pencil (2nd generation).',
 '2022-09-15', 1099.99, 1, NOW(), 1),

('Wireless Microphone Set', 'wireless-microphone-set', 'AUD-2023-015', 
 (SELECT `id` FROM `categories` WHERE `name` = 'Audio Equipment'), 
 (SELECT `id` FROM `locations` WHERE `name` = 'Conference Room A'), 
 (SELECT `id` FROM `suppliers` WHERE `name` = 'Electronics Warehouse'), 
 'Shure', 'PGXD24/SM58', 'PGXD24SM58', 'SH7865PGXD24', '3456789012345', 'available', 'excellent', 
 'Professional-grade wireless microphone system with handheld microphone, ideal for presentations and events.',
 '2023-01-25', 399.99, 1, NOW(), 1);

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;