-- Sample Data for Inventory Pro
-- This script adds additional sample data to make the dashboard functional

USE inventory_pro;

-- Create a few more users
INSERT INTO `users` (`username`, `password`, `email`, `full_name`, `phone`, `department_id`, `job_title`, `location_id`, `role`, `is_active`, `created_at`) VALUES
('jsmith', '$2y$10$xJ7Sd8fmHR8rKqI5iEa1y.IYwAYWO0JMJxnqPMY08G9NOu4OKs5uG', 'john.smith@example.com', 'John Smith', '555-123-4567', 
 (SELECT `id` FROM `departments` WHERE `name` = 'IT Department'), 'Inventory Manager', 
 (SELECT `id` FROM `locations` WHERE `name` = 'IT Department'), 'manager', 1, NOW()),

('sjohnson', '$2y$10$xJ7Sd8fmHR8rKqI5iEa1y.IYwAYWO0JMJxnqPMY08G9NOu4OKs5uG', 'sarah.johnson@example.com', 'Sarah Johnson', '555-234-5678', 
 (SELECT `id` FROM `departments` WHERE `name` = 'Marketing'), 'Marketing Specialist', 
 (SELECT `id` FROM `locations` WHERE `name` = 'Marketing Department'), 'user', 1, NOW()),

('mlee', '$2y$10$xJ7Sd8fmHR8rKqI5iEa1y.IYwAYWO0JMJxnqPMY08G9NOu4OKs5uG', 'michael.lee@example.com', 'Michael Lee', '555-345-6789', 
 (SELECT `id` FROM `departments` WHERE `name` = 'IT Department'), 'IT Support Specialist', 
 (SELECT `id` FROM `locations` WHERE `name` = 'IT Department'), 'user', 1, NOW()),

('ewong', '$2y$10$xJ7Sd8fmHR8rKqI5iEa1y.IYwAYWO0JMJxnqPMY08G9NOu4OKs5uG', 'emily.wong@example.com', 'Emily Wong', '555-456-7890', 
 (SELECT `id` FROM `departments` WHERE `name` = 'Sales'), 'Sales Representative', 
 (SELECT `id` FROM `locations` WHERE `name` = 'Main Warehouse'), 'user', 1, NOW()),

('dkim', '$2y$10$xJ7Sd8fmHR8rKqI5iEa1y.IYwAYWO0JMJxnqPMY08G9NOu4OKs5uG', 'david.kim@example.com', 'David Kim', '555-567-8901', 
 (SELECT `id` FROM `departments` WHERE `name` = 'IT Department'), 'IT Administrator', 
 (SELECT `id` FROM `locations` WHERE `name` = 'IT Department'), 'user', 1, NOW()),

('jmartinez', '$2y$10$xJ7Sd8fmHR8rKqI5iEa1y.IYwAYWO0JMJxnqPMY08G9NOu4OKs5uG', 'jessica.martinez@example.com', 'Jessica Martinez', '555-678-9012', 
 (SELECT `id` FROM `departments` WHERE `name` = 'Human Resources'), 'HR Specialist', 
 (SELECT `id` FROM `locations` WHERE `name` = 'Office Supplies'), 'user', 1, NOW());

-- Create some sample borrow requests
INSERT INTO `borrow_requests` (`request_id`, `user_id`, `department_id`, `purpose`, `project_name`, `borrow_date`, `return_date`, `status`, `notes`, `created_at`) VALUES
-- Active borrows (checked out) for the first user
('BR-2025-0001', 2, (SELECT `id` FROM `departments` WHERE `name` = 'Marketing'), 'Project Work', 'Spring Marketing Campaign', 
 DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'checked_out', 'Needed for the upcoming marketing campaign', 
 DATE_SUB(CURDATE(), INTERVAL 11 DAY)),

('BR-2025-0002', 2, (SELECT `id` FROM `departments` WHERE `name` = 'Marketing'), 'Client Meeting', 'Client Presentation', 
 DATE_SUB(CURDATE(), INTERVAL 8 DAY), DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'checked_out', 'Presentation equipment for client meeting', 
 DATE_SUB(CURDATE(), INTERVAL 9 DAY)),

('BR-2025-0003', 2, (SELECT `id` FROM `departments` WHERE `name` = 'Marketing'), 'Field Work', 'Product Photoshoot', 
 DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 9 DAY), 'checked_out', 'Photography equipment for new product line', 
 DATE_SUB(CURDATE(), INTERVAL 6 DAY)),

-- Pending requests for the first user
('BR-2025-0004', 2, (SELECT `id` FROM `departments` WHERE `name` = 'Marketing'), 'Event', 'Trade Show', 
 DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 12 DAY), 'pending', 'Need equipment for the upcoming trade show', 
 DATE_SUB(CURDATE(), INTERVAL 2 DAY)),

('BR-2025-0005', 2, (SELECT `id` FROM `departments` WHERE `name` = 'Marketing'), 'Remote Work', 'Work From Home', 
 DATE_ADD(CURDATE(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 16 DAY), 'pending', 'Equipment needed for remote work period', 
 DATE_SUB(CURDATE(), INTERVAL 1 DAY)),

-- Completed/returned for the first user
('BR-2025-0006', 2, (SELECT `id` FROM `departments` WHERE `name` = 'Marketing'), 'Training', 'New Team Training', 
 DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_SUB(CURDATE(), INTERVAL 23 DAY), 'returned', 'Equipment for new team member training session', 
 DATE_SUB(CURDATE(), INTERVAL 32 DAY)),

('BR-2025-0007', 2, (SELECT `id` FROM `departments` WHERE `name` = 'Marketing'), 'Conference', 'Marketing Conference', 
 DATE_SUB(CURDATE(), INTERVAL 45 DAY), DATE_SUB(CURDATE(), INTERVAL 40 DAY), 'returned', 'Equipment for speaking at the annual marketing conference', 
 DATE_SUB(CURDATE(), INTERVAL 47 DAY)),

-- Requests for other users
('BR-2025-0008', 3, (SELECT `id` FROM `departments` WHERE `name` = 'IT Department'), 'Support', 'Server Maintenance', 
 DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'checked_out', 'Equipment needed for server maintenance work', 
 DATE_SUB(CURDATE(), INTERVAL 16 DAY)),

('BR-2025-0009', 4, (SELECT `id` FROM `departments` WHERE `name` = 'Sales'), 'Client Demo', 'Product Demo', 
 DATE_SUB(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'checked_out', 'Demo equipment for client presentation', 
 DATE_SUB(CURDATE(), INTERVAL 8 DAY)),

('BR-2025-0010', 5, (SELECT `id` FROM `departments` WHERE `name` = 'IT Department'), 'Setup', 'New Employee Setup', 
 DATE_ADD(CURDATE(), INTERVAL 1 DAY), DATE_ADD(CURDATE(), INTERVAL 8 DAY), 'pending', 'Equipment for new employee onboarding', 
 DATE_SUB(CURDATE(), INTERVAL 1 DAY));

-- Add borrowed items
INSERT INTO `borrowed_items` (`borrow_request_id`, `item_id`, `quantity`, `condition_before`, `status`, `is_returned`) VALUES
-- Items for BR-2025-0001
(1, 1, 1, 'excellent', 'checked_out', 0),  -- Dell XPS 15 Laptop
(1, 3, 1, 'excellent', 'checked_out', 0),  -- Noise-Cancelling Headphones

-- Items for BR-2025-0002
(2, 4, 1, 'good', 'checked_out', 0),  -- 4K Video Projector

-- Items for BR-2025-0003
(3, 2, 1, 'excellent', 'checked_out', 0),  -- Sony A7 III Camera
(3, 6, 1, 'good', 'checked_out', 0),  -- Wireless Microphone Set

-- Items for pending requests
(4, 2, 1, 'good', 'pending', 0),  -- Sony A7 III Camera
(4, 4, 1, 'good', 'pending', 0),  -- 4K Video Projector

(5, 1, 1, 'excellent', 'pending', 0),  -- Dell XPS 15 Laptop
(5, 3, 1, 'excellent', 'pending', 0),  -- Noise-Cancelling Headphones

-- Items for returned requests
(6, 5, 1, 'excellent', 'returned', 1),  -- iPad Pro
(6, 6, 1, 'good', 'returned', 1),  -- Wireless Microphone Set

(7, 2, 1, 'excellent', 'returned', 1),  -- Sony A7 III Camera
(7, 3, 1, 'excellent', 'returned', 1),  -- Noise-Cancelling Headphones

-- Items for other users
(8, 1, 1, 'excellent', 'checked_out', 0),  -- Dell XPS 15 Laptop
(9, 4, 1, 'good', 'checked_out', 0),  -- 4K Video Projector
(10, 5, 1, 'excellent', 'pending', 0);  -- iPad Pro

-- Update item status based on borrowed items
UPDATE `items` SET `status` = 'borrowed' WHERE `id` IN (
  SELECT `item_id` FROM `borrowed_items` WHERE `status` = 'checked_out' AND `is_returned` = 0
);

-- Create some reservations
INSERT INTO `reservations` (`user_id`, `item_id`, `start_date`, `end_date`, `purpose`, `status`, `notes`, `created_at`) VALUES
(2, 5, DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'Design Workshop', 'confirmed', 
 'Reserved for the upcoming design thinking workshop', DATE_SUB(CURDATE(), INTERVAL 5 DAY)),

(3, 6, DATE_ADD(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 12 DAY), 'Team Meeting', 'confirmed', 
 'Reserved for the quarterly team meeting', DATE_SUB(CURDATE(), INTERVAL 3 DAY)),

(4, 2, DATE_ADD(CURDATE(), INTERVAL 20 DAY), DATE_ADD(CURDATE(), INTERVAL 25 DAY), 'Product Photography', 'pending', 
 'Need for product catalog photoshoot', DATE_SUB(CURDATE(), INTERVAL 1 DAY));

-- Create some activity logs for the dashboard
INSERT INTO `activity_logs` (`user_id`, `action`, `entity_type`, `entity_id`, `description`, `created_at`) VALUES
(2, 'login', 'users', 2, 'User logged in', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 'view_items', 'items', NULL, 'Browsed inventory items', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 'create_borrow_request', 'borrow_requests', 5, 'Created borrow request BR-2025-0005', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 'login', 'users', 2, 'User logged in', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 'create_borrow_request', 'borrow_requests', 4, 'Created borrow request BR-2025-0004', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 'logout', 'users', 2, 'User logged out', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 'login', 'users', 2, 'User logged in', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 'return_borrowed_items', 'borrow_requests', 7, 'Returned items for request BR-2025-0007', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 'logout', 'users', 2, 'User logged out', DATE_SUB(NOW(), INTERVAL 3 DAY));

-- Create some notifications
INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `related_record_type`, `related_record_id`, `is_read`, `created_at`) VALUES
(2, 'Borrow Request Approved', 'Your request BR-2025-0001 has been approved.', 'success', 'borrow_requests', 1, 1, DATE_SUB(CURDATE(), INTERVAL 11 DAY)),
(2, 'Borrow Request Approved', 'Your request BR-2025-0002 has been approved.', 'success', 'borrow_requests', 2, 1, DATE_SUB(CURDATE(), INTERVAL 9 DAY)),
(2, 'Borrow Request Approved', 'Your request BR-2025-0003 has been approved.', 'success', 'borrow_requests', 3, 1, DATE_SUB(CURDATE(), INTERVAL 6 DAY)),
(2, 'Item Due Soon', 'Your borrowed item "4K Video Projector" is due in 2 days.', 'warning', 'borrow_requests', 2, 0, DATE_SUB(CURDATE(), INTERVAL 1 DAY)),
(2, 'New Message', 'You have a new message from the IT Department regarding your equipment requests.', 'info', NULL, NULL, 0, DATE_SUB(CURDATE(), INTERVAL 12 HOUR));

-- Add some item tags
INSERT INTO `tags` (`name`) VALUES 
('Portable'), ('High-Performance'), ('Wireless'), ('Video'), ('Audio'), ('Photography'), ('Meeting Room'), ('4K'), ('Touchscreen');

-- Associate tags with items
INSERT INTO `item_tags` (`item_id`, `tag_id`) VALUES
(1, 1), (1, 2),  -- Dell XPS 15 Laptop: Portable, High-Performance
(2, 1), (2, 6),  -- Sony A7 III Camera: Portable, Photography
(3, 1), (3, 3), (3, 5),  -- Noise-Cancelling Headphones: Portable, Wireless, Audio
(4, 4), (4, 7), (4, 8),  -- 4K Video Projector: Video, Meeting Room, 4K
(5, 1), (5, 9),  -- iPad Pro: Portable, Touchscreen
(6, 3), (6, 5);  -- Wireless Microphone Set: Wireless, Audio



-- Insert general settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `default_value`, `setting_group`, `display_name`, `description`, `field_type`, `options`, `is_required`, `order_index`) VALUES
-- System Preferences
('site_name', 'Inventory Pro', 'Inventory Pro', 'general', 'System Name', 'Name displayed in browser tabs and system communications', 'text', NULL, 1, 10),
('company_name', 'Your Company', 'Your Company', 'general', 'Company Name', 'Your company or organization name used in emails and reports', 'text', NULL, 1, 20),
('company_email', 'admin@example.com', 'admin@example.com', 'general', 'Company Email', 'Default email address for system notifications', 'email', NULL, 1, 30),
('default_sidebar_state', 'expanded', 'expanded', 'general', 'Navigation Sidebar', 'Default sidebar state when users log in', 'select', '["expanded", "collapsed", "remember"]', 0, 40),
('session_timeout', '30', '30', 'general', 'Session Timeout', 'Automatically log out inactive users after this period', 'select', '["15", "30", "60", "120", "240", "480"]', 1, 50),
('dashboard_refresh', '0', '0', 'general', 'Dashboard Auto-Refresh', 'Automatically refresh dashboard data at regular intervals', 'toggle', NULL, 0, 60),
('dashboard_refresh_interval', '60', '60', 'general', 'Refresh Interval', 'How often the dashboard will refresh automatically', 'select', '["30", "60", "300", "600", "1800"]', 0, 70),

-- Usage Analytics
('collect_usage_data', '1', '1', 'analytics', 'Collect Anonymous Usage Data', 'Helps us improve the system by collecting anonymous usage statistics', 'toggle', NULL, 0, 80),
('track_feature_usage', '1', '1', 'analytics', 'Feature Usage Tracking', 'Track which features are most commonly used to help prioritize improvements', 'toggle', NULL, 0, 90),
('error_reporting', '1', '1', 'analytics', 'Error Reporting', 'Automatically report errors to help us improve stability', 'toggle', NULL, 0, 100),
('usage_reports_frequency', 'monthly', 'monthly', 'analytics', 'Usage Reports', 'Receive periodic reports about your system usage and optimization suggestions', 'select', '["never", "monthly", "quarterly"]', 0, 110),

-- System Maintenance
('automatic_updates', '1', '1', 'maintenance', 'Automatic Updates', 'Automatically install minor updates and security patches', 'toggle', NULL, 0, 120),
('maintenance_window', 'night', 'night', 'maintenance', 'Maintenance Window', 'Schedule when system maintenance should occur', 'select', '["anytime", "night", "weekend", "custom"]', 0, 130),
('db_optimization_frequency', 'weekly', 'weekly', 'maintenance', 'Database Optimization', 'Automatically optimize database performance', 'select', '["daily", "weekly", "monthly", "never"]', 0, 140),
('log_retention_days', '30', '30', 'maintenance', 'System Logs', 'How long to keep system logs before archiving', 'select', '["7", "30", "90", "180", "365"]', 0, 150),

-- Advanced Settings
('debug_mode', '0', '0', 'advanced', 'Debug Mode', 'Enable detailed logging for system troubleshooting', 'toggle', NULL, 0, 160),
('enable_api', '0', '0', 'advanced', 'API Access', 'Allow external applications to access the system API', 'toggle', NULL, 0, 170),

-- Regional Settings - Localization
('language', 'en', 'en', 'regional', 'Language', 'Default system language', 'select', NULL, 1, 180),
('timezone', 'America/New_York', 'America/New_York', 'regional', 'Time Zone', 'Default time zone for date and time displays', 'select', NULL, 1, 190),
('date_format', 'M j, Y', 'M j, Y', 'regional', 'Date Format', 'How dates should be displayed throughout the system', 'select', NULL, 1, 200),
('time_format', 'g:i A', 'g:i A', 'regional', 'Time Format', 'Time display format', 'select', NULL, 1, 210),
('first_day_of_week', '0', '0', 'regional', 'First Day of Week', 'Which day is considered the start of the week', 'select', '["0", "1", "6"]', 0, 220),

-- Regional Settings - Currency and Units
('currency', 'USD', 'USD', 'regional', 'Currency', 'Default currency for financial values', 'select', '["USD", "EUR", "GBP", "JPY", "CAD"]', 1, 230),
('measurement_system', 'imperial', 'imperial', 'regional', 'Measurement System', 'Unit system for dimensions and weights', 'select', '["imperial", "metric"]', 0, 240),
('number_format', '1,234.56', '1,234.56', 'regional', 'Number Format', 'How numbers should be formatted', 'select', '["1,234.56", "1.234,56", "1 234.56"]', 0, 250),

-- Interface Options - Display Settings
('items_per_page', '20', '20', 'interface', 'Items Per Page', 'Default number of items to display per page in listings', 'select', '["10", "20", "50", "100"]', 0, 260),
('default_landing_page', 'dashboard', 'dashboard', 'interface', 'Default Landing Page', 'Page shown after login', 'select', '["dashboard", "items/browse", "borrow/my-items"]', 0, 270),
('enable_theme_customization', '1', '1', 'interface', 'Enable Theme Customization', 'Allow users to customize theme and appearance', 'toggle', NULL, 0, 280),
('show_quick_actions', '1', '1', 'interface', 'Show Quick Actions', 'Display quick action buttons on dashboard', 'toggle', NULL, 0, 290),

-- Interface Options - Appearance
('default_theme', 'light', 'light', 'interface', 'Default Theme', 'Default theme for all users', 'select', '["light", "dark", "system"]', 0, 300),
('primary_color', '#6d28d9', '#6d28d9', 'interface', 'Primary Color', 'Main accent color for the interface', 'color', NULL, 0, 310),
('secondary_color', '#10b981', '#10b981', 'interface', 'Secondary Color', 'Secondary accent color', 'color', NULL, 0, 320),
('ui_density', 'comfortable', 'comfortable', 'interface', 'UI Density', 'Controls spacing in the user interface', 'select', '["comfortable", "compact"]', 0, 330),
('animation_level', 'full', 'full', 'interface', 'Animation Settings', 'Control UI animations and transitions', 'select', '["full", "reduced", "none"]', 0, 340),

-- Inventory Management Settings
('inventory_method', 'fifo', 'fifo', 'inventory', 'Inventory Method', 'Method used for inventory valuation', 'select', '["fifo", "lifo", "avg"]', 1, 350),
('low_stock_threshold', '20', '20', 'inventory', 'Low Stock Threshold', 'Default percentage that triggers low stock warnings', 'number', NULL, 1, 360),
('auto_generate_purchase_orders', '0', '0', 'inventory', 'Auto-Generate Purchase Orders', 'Automatically create purchase orders when stock is low', 'toggle', NULL, 0, 370),
('auto_assign_asset_tags', '1', '1', 'inventory', 'Auto-assign Asset Tags', 'Automatically assign asset tags to new inventory items', 'toggle', NULL, 0, 380),
('asset_tag_prefix', 'ASSET-', 'ASSET-', 'inventory', 'Asset Tag Prefix', 'Prefix used when generating asset tags', 'text', NULL, 0, 390),
('require_inventory_photos', '0', '0', 'inventory', 'Require Inventory Photos', 'Require at least one photo when adding new inventory items', 'toggle', NULL, 0, 400),

-- Maintenance Settings
('enable_maintenance_scheduler', '1', '1', 'inventory', 'Enable Maintenance Scheduling', 'Track maintenance schedules for equipment', 'toggle', NULL, 0, 410),
('default_maintenance_interval', '90', '90', 'inventory', 'Default Maintenance Interval', 'Default days between scheduled maintenance', 'number', NULL, 0, 420),
('maintenance_alert_days', '7', '7', 'inventory', 'Maintenance Alert Days', 'Days before scheduled maintenance to send alerts', 'number', NULL, 0, 430),
('enable_damage_reports', '1', '1', 'inventory', 'Enable Damage Reports', 'Allow users to report damaged equipment', 'toggle', NULL, 0, 440),

-- Borrowing Settings
('max_borrow_days', '14', '14', 'checkout', 'Maximum Checkout Period', 'Default maximum time items can be checked out', 'select', '["7", "14", "30", "90", "unlimited"]', 0, 450),
('default_borrow_days', '7', '7', 'checkout', 'Default Borrowing Period', 'Default number of days for borrowing', 'number', NULL, 0, 460),
('allow_renewals', '1', '1', 'checkout', 'Allow Renewals', 'Allow users to extend their checkout periods', 'toggle', NULL, 0, 470),
('max_renewals', '3', '3', 'checkout', 'Maximum Renewals', 'Maximum number of times a checkout can be renewed', 'number', NULL, 0, 480),
('advance_reservation_days', '30', '30', 'checkout', 'Advance Reservation', 'How far in advance items can be reserved', 'select', '["7", "14", "30", "90", "180"]', 0, 490),

-- Notifications & Reminders
('send_overdue_notifications', '1', '1', 'checkout', 'Overdue Notifications', 'Send reminders to users with overdue items', 'toggle', NULL, 0, 500),
('overdue_reminder_days', '3', '3', 'checkout', 'Due Date Reminder', 'Days before due date to send reminder', 'number', NULL, 0, 510),
('max_overdue_days', '14', '14', 'checkout', 'Maximum Overdue Days', 'Maximum days an item can be overdue before escalation', 'number', NULL, 0, 520),
('notify_manager_on_approval', '1', '1', 'checkout', 'Notify Manager on Approval', 'Send notification to manager when borrow request is approved', 'toggle', NULL, 0, 530),
('notify_department_head', '0', '0', 'checkout', 'Notify Department Head', 'Send notifications about borrow requests to department heads', 'toggle', NULL, 0, 540),

-- Barcode Generation
('barcode_format', 'code128', 'code128', 'barcoding', 'Barcode Format', 'Default barcode format for inventory items', 'select', '["code128", "code39", "ean13", "upc", "qr"]', 0, 550),
('auto_generate_barcodes', '1', '1', 'barcoding', 'Auto-generate Barcodes', 'Automatically generate barcodes for new items', 'toggle', NULL, 0, 560),
('barcode_prefix', 'INV-', 'INV-', 'barcoding', 'Barcode Prefix', 'Prefix used when generating barcodes', 'text', NULL, 0, 570),
('include_location_in_barcode', '0', '0', 'barcoding', 'Include Location in Barcode', 'Include location code in generated barcodes', 'toggle', NULL, 0, 580),

-- Label Printing
('label_paper_size', 'letter', 'letter', 'barcoding', 'Label Paper Size', 'Default paper size for printing labels', 'select', '["letter", "a4", "2x4", "1x3", "custom"]', 0, 590),
('label_include_name', '1', '1', 'barcoding', 'Include Item Name', 'Include item name on printed labels', 'toggle', NULL, 0, 600),
('label_include_asset_id', '1', '1', 'barcoding', 'Include Asset ID', 'Include asset ID on printed labels', 'toggle', NULL, 0, 610),
('label_include_model', '0', '0', 'barcoding', 'Include Model Number', 'Include model number on printed labels', 'toggle', NULL, 0, 620),
('label_include_location', '0', '0', 'barcoding', 'Include Location', 'Include location on printed labels', 'toggle', NULL, 0, 630),
('label_include_company_name', '1', '1', 'barcoding', 'Include Company Name', 'Include company name on printed labels', 'toggle', NULL, 0, 640);
