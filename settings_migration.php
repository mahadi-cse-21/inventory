<?php
/**
 * Settings Table Migration Script
 * 
 * This script will migrate data from the old settings table structure to the new enhanced structure
 * Run this script after creating the new settings table schema
 */

// Include database connection

// require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/config/database.php'; // Adjusted path to match the correct location

// Ensure the getDbConnection function is defined
if (!function_exists('getDbConnection')) {
    function getDbConnection() {
        try {
            $dsn = 'mysql:host=localhost;dbname=your_database;charset=utf8mb4';
            $username = 'your_username';
            $password = 'your_password';
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            return new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
}
/**
 * Migrate settings data from old table structure to new one
 */
function migrateSettingsTable() {
    $conn = getDbConnection();
    
    try {
        // Start transaction
        $conn->beginTransaction();
        
        echo "Starting settings table migration...\n";
        
        // Step 1: Check if new table columns exist
        $tableInfoSql = "SHOW COLUMNS FROM settings";
        $tableInfoStmt = $conn->prepare($tableInfoSql);
        $tableInfoStmt->execute();
        $columns = $tableInfoStmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Check if we need to run the migration
        $needsMigration = !in_array('field_type', $columns) || 
                         !in_array('setting_group', $columns) || 
                         !in_array('display_name', $columns);
        
        if (!$needsMigration) {
            echo "Table already has the new structure. No migration needed.\n";
            return true;
        }
        
        echo "Backing up existing settings data...\n";
        
        // Step 2: Backup existing settings data
        $backupSql = "CREATE TABLE settings_backup LIKE settings";
        $conn->exec($backupSql);
        
        $backupDataSql = "INSERT INTO settings_backup SELECT * FROM settings";
        $conn->exec($backupDataSql);
        
        echo "Backup created successfully.\n";
        
        // Step 3: Get existing settings data
        $getOldSettingsSql = "SELECT setting_key, setting_value, setting_description, is_public 
                             FROM settings";
        $oldSettingsStmt = $conn->prepare($getOldSettingsSql);
        $oldSettingsStmt->execute();
        $oldSettings = $oldSettingsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Retrieved " . count($oldSettings) . " existing settings.\n";
        
        // Step 4: Create new table structure
        $dropTableSql = "DROP TABLE settings";
        $conn->exec($dropTableSql);
        
        $createTableSql = "
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
              `order_index` int(11) NOT NULL DEFAULT 0,
              `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `setting_key` (`setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $conn->exec($createTableSql);
        
        echo "Created new settings table structure.\n";
        
        // Step 5: Define setting mapping with additional attributes
        $settingsMap = [
            // System Preferences
            'site_name' => ['group' => 'general', 'display_name' => 'System Name', 'type' => 'text', 'required' => true, 'index' => 10],
            'company_name' => ['group' => 'general', 'display_name' => 'Company Name', 'type' => 'text', 'required' => true, 'index' => 20],
            'company_email' => ['group' => 'general', 'display_name' => 'Company Email', 'type' => 'email', 'required' => true, 'index' => 30],
            'default_sidebar_state' => ['group' => 'general', 'display_name' => 'Navigation Sidebar', 'type' => 'select', 'options' => '["expanded", "collapsed", "remember"]', 'index' => 40],
            'session_timeout' => ['group' => 'general', 'display_name' => 'Session Timeout', 'type' => 'select', 'options' => '["15", "30", "60", "120", "240", "480"]', 'required' => true, 'index' => 50],
            'dashboard_refresh' => ['group' => 'general', 'display_name' => 'Dashboard Auto-Refresh', 'type' => 'toggle', 'index' => 60],
            'dashboard_refresh_interval' => ['group' => 'general', 'display_name' => 'Refresh Interval', 'type' => 'select', 'options' => '["30", "60", "300", "600", "1800"]', 'index' => 70],
            
            // Analytics
            'collect_usage_data' => ['group' => 'analytics', 'display_name' => 'Collect Anonymous Usage Data', 'type' => 'toggle', 'index' => 80],
            'track_feature_usage' => ['group' => 'analytics', 'display_name' => 'Feature Usage Tracking', 'type' => 'toggle', 'index' => 90],
            'error_reporting' => ['group' => 'analytics', 'display_name' => 'Error Reporting', 'type' => 'toggle', 'index' => 100],
            'usage_reports_frequency' => ['group' => 'analytics', 'display_name' => 'Usage Reports', 'type' => 'select', 'options' => '["never", "monthly", "quarterly"]', 'index' => 110],
            
            // Maintenance
            'automatic_updates' => ['group' => 'maintenance', 'display_name' => 'Automatic Updates', 'type' => 'toggle', 'index' => 120],
            'maintenance_window' => ['group' => 'maintenance', 'display_name' => 'Maintenance Window', 'type' => 'select', 'options' => '["anytime", "night", "weekend", "custom"]', 'index' => 130],
            'db_optimization_frequency' => ['group' => 'maintenance', 'display_name' => 'Database Optimization', 'type' => 'select', 'options' => '["daily", "weekly", "monthly", "never"]', 'index' => 140],
            'log_retention_days' => ['group' => 'maintenance', 'display_name' => 'System Logs', 'type' => 'select', 'options' => '["7", "30", "90", "180", "365"]', 'index' => 150],
            
            // Advanced
            'debug_mode' => ['group' => 'advanced', 'display_name' => 'Debug Mode', 'type' => 'toggle', 'index' => 160],
            'enable_api' => ['group' => 'advanced', 'display_name' => 'API Access', 'type' => 'toggle', 'index' => 170],
            
            // Regional - Localization
            'language' => ['group' => 'regional', 'display_name' => 'Language', 'type' => 'select', 'required' => true, 'index' => 180],
            'timezone' => ['group' => 'regional', 'display_name' => 'Time Zone', 'type' => 'select', 'required' => true, 'index' => 190],
            'date_format' => ['group' => 'regional', 'display_name' => 'Date Format', 'type' => 'select', 'required' => true, 'index' => 200],
            'time_format' => ['group' => 'regional', 'display_name' => 'Time Format', 'type' => 'select', 'required' => true, 'index' => 210],
            'first_day_of_week' => ['group' => 'regional', 'display_name' => 'First Day of Week', 'type' => 'select', 'options' => '["0", "1", "6"]', 'index' => 220],
            'currency' => ['group' => 'regional', 'display_name' => 'Currency', 'type' => 'select', 'options' => '["USD", "EUR", "GBP", "JPY", "CAD"]', 'required' => true, 'index' => 230],
            'measurement_system' => ['group' => 'regional', 'display_name' => 'Measurement System', 'type' => 'select', 'options' => '["imperial", "metric"]', 'index' => 240],
            'number_format' => ['group' => 'regional', 'display_name' => 'Number Format', 'type' => 'select', 'options' => '["1,234.56", "1.234,56", "1 234.56"]', 'index' => 250],
            
            // Interface
            'items_per_page' => ['group' => 'interface', 'display_name' => 'Items Per Page', 'type' => 'select', 'options' => '["10", "20", "50", "100"]', 'index' => 260],
            'default_landing_page' => ['group' => 'interface', 'display_name' => 'Default Landing Page', 'type' => 'select', 'options' => '["dashboard", "items/browse", "borrow/my-items"]', 'index' => 270],
            'enable_theme_customization' => ['group' => 'interface', 'display_name' => 'Enable Theme Customization', 'type' => 'toggle', 'index' => 280],
            'show_quick_actions' => ['group' => 'interface', 'display_name' => 'Show Quick Actions', 'type' => 'toggle', 'index' => 290],
            'default_theme' => ['group' => 'interface', 'display_name' => 'Default Theme', 'type' => 'select', 'options' => '["light", "dark", "system"]', 'index' => 300],
            'primary_color' => ['group' => 'interface', 'display_name' => 'Primary Color', 'type' => 'color', 'index' => 310],
            'secondary_color' => ['group' => 'interface', 'display_name' => 'Secondary Color', 'type' => 'color', 'index' => 320],
            'ui_density' => ['group' => 'interface', 'display_name' => 'UI Density', 'type' => 'select', 'options' => '["comfortable", "compact"]', 'index' => 330],
            'animation_level' => ['group' => 'interface', 'display_name' => 'Animation Settings', 'type' => 'select', 'options' => '["full", "reduced", "none"]', 'index' => 340],
            
            // Inventory
            'inventory_method' => ['group' => 'inventory', 'display_name' => 'Inventory Method', 'type' => 'select', 'options' => '["fifo", "lifo", "avg"]', 'required' => true, 'index' => 350],
            'low_stock_threshold' => ['group' => 'inventory', 'display_name' => 'Low Stock Threshold', 'type' => 'number', 'required' => true, 'index' => 360],
            'auto_generate_purchase_orders' => ['group' => 'inventory', 'display_name' => 'Auto-Generate Purchase Orders', 'type' => 'toggle', 'index' => 370],
            'auto_assign_asset_tags' => ['group' => 'inventory', 'display_name' => 'Auto-assign Asset Tags', 'type' => 'toggle', 'index' => 380],
            'asset_tag_prefix' => ['group' => 'inventory', 'display_name' => 'Asset Tag Prefix', 'type' => 'text', 'index' => 390],
            'require_inventory_photos' => ['group' => 'inventory', 'display_name' => 'Require Inventory Photos', 'type' => 'toggle', 'index' => 400],
            
            // Maintenance
            'enable_maintenance_scheduler' => ['group' => 'inventory', 'display_name' => 'Enable Maintenance Scheduling', 'type' => 'toggle', 'index' => 410],
            'default_maintenance_interval' => ['group' => 'inventory', 'display_name' => 'Default Maintenance Interval', 'type' => 'number', 'index' => 420],
            'maintenance_alert_days' => ['group' => 'inventory', 'display_name' => 'Maintenance Alert Days', 'type' => 'number', 'index' => 430],
            'enable_damage_reports' => ['group' => 'inventory', 'display_name' => 'Enable Damage Reports', 'type' => 'toggle', 'index' => 440],
            
            // Checkout
            'max_borrow_days' => ['group' => 'checkout', 'display_name' => 'Maximum Checkout Period', 'type' => 'select', 'options' => '["7", "14", "30", "90", "unlimited"]', 'index' => 450],
            'default_borrow_days' => ['group' => 'checkout', 'display_name' => 'Default Borrowing Period', 'type' => 'number', 'index' => 460],
            'allow_renewals' => ['group' => 'checkout', 'display_name' => 'Allow Renewals', 'type' => 'toggle', 'index' => 470],
            'max_renewals' => ['group' => 'checkout', 'display_name' => 'Maximum Renewals', 'type' => 'number', 'index' => 480],
            'advance_reservation_days' => ['group' => 'checkout', 'display_name' => 'Advance Reservation', 'type' => 'select', 'options' => '["7", "14", "30", "90", "180"]', 'index' => 490],
            
            // Notifications & Reminders
            'send_overdue_notifications' => ['group' => 'checkout', 'display_name' => 'Overdue Notifications', 'type' => 'toggle', 'index' => 500],
            'overdue_reminder_days' => ['group' => 'checkout', 'display_name' => 'Due Date Reminder', 'type' => 'number', 'index' => 510],
            'max_overdue_days' => ['group' => 'checkout', 'display_name' => 'Maximum Overdue Days', 'type' => 'number', 'index' => 520],
            'notify_manager_on_approval' => ['group' => 'checkout', 'display_name' => 'Notify Manager on Approval', 'type' => 'toggle', 'index' => 530],
            'notify_department_head' => ['group' => 'checkout', 'display_name' => 'Notify Department Head', 'type' => 'toggle', 'index' => 540],
            
            // Barcode
            'barcode_format' => ['group' => 'barcoding', 'display_name' => 'Barcode Format', 'type' => 'select', 'options' => '["code128", "code39", "ean13", "upc", "qr"]', 'index' => 550],
            'auto_generate_barcodes' => ['group' => 'barcoding', 'display_name' => 'Auto-generate Barcodes', 'type' => 'toggle', 'index' => 560],
            'barcode_prefix' => ['group' => 'barcoding', 'display_name' => 'Barcode Prefix', 'type' => 'text', 'index' => 570],
            'include_location_in_barcode' => ['group' => 'barcoding', 'display_name' => 'Include Location in Barcode', 'type' => 'toggle', 'index' => 580],
            
            // Label
            'label_paper_size' => ['group' => 'barcoding', 'display_name' => 'Label Paper Size', 'type' => 'select', 'options' => '["letter", "a4", "2x4", "1x3", "custom"]', 'index' => 590],
            'label_include_name' => ['group' => 'barcoding', 'display_name' => 'Include Item Name', 'type' => 'toggle', 'index' => 600],
            'label_include_asset_id' => ['group' => 'barcoding', 'display_name' => 'Include Asset ID', 'type' => 'toggle', 'index' => 610],
            'label_include_model' => ['group' => 'barcoding', 'display_name' => 'Include Model Number', 'type' => 'toggle', 'index' => 620],
            'label_include_location' => ['group' => 'barcoding', 'display_name' => 'Include Location', 'type' => 'toggle', 'index' => 630],
            'label_include_company_name' => ['group' => 'barcoding', 'display_name' => 'Include Company Name', 'type' => 'toggle', 'index' => 640],
            
            // Other settings (generic mapping for anything not explicitly defined)
            'default' => ['group' => 'general', 'display_name' => '', 'type' => 'text', 'index' => 1000]
        ];
        
        // Step 6: Default values for common settings
        $defaultValues = [
            'site_name' => 'Inventory Pro',
            'company_name' => 'Your Company',
            'company_email' => 'admin@example.com',
            'default_sidebar_state' => 'expanded',
            'session_timeout' => '30',
            'dashboard_refresh' => '0',
            'dashboard_refresh_interval' => '60',
            'collect_usage_data' => '1',
            'track_feature_usage' => '1',
            'error_reporting' => '1',
            'usage_reports_frequency' => 'monthly',
            'automatic_updates' => '1',
            'maintenance_window' => 'night',
            'db_optimization_frequency' => 'weekly',
            'log_retention_days' => '30',
            'debug_mode' => '0',
            'enable_api' => '0',
            'language' => 'en',
            'timezone' => 'America/New_York',
            'date_format' => 'M j, Y',
            'time_format' => 'g:i A',
            'first_day_of_week' => '0',
            'currency' => 'USD',
            'measurement_system' => 'imperial',
            'number_format' => '1,234.56',
            'items_per_page' => '20',
            'default_landing_page' => 'dashboard',
            'enable_theme_customization' => '1',
            'show_quick_actions' => '1',
            'default_theme' => 'light',
            'primary_color' => '#6d28d9',
            'secondary_color' => '#10b981',
            'ui_density' => 'comfortable',
            'animation_level' => 'full',
            'inventory_method' => 'fifo',
            'low_stock_threshold' => '20',
            'auto_generate_purchase_orders' => '0',
            'auto_assign_asset_tags' => '1',
            'asset_tag_prefix' => 'ASSET-',
            'require_inventory_photos' => '0',
            'enable_maintenance_scheduler' => '1',
            'default_maintenance_interval' => '90',
            'maintenance_alert_days' => '7',
            'enable_damage_reports' => '1',
            'max_borrow_days' => '14',
            'default_borrow_days' => '7',
            'allow_renewals' => '1',
            'max_renewals' => '3',
            'advance_reservation_days' => '30',
            'send_overdue_notifications' => '1',
            'overdue_reminder_days' => '3',
            'max_overdue_days' => '14',
            'notify_manager_on_approval' => '1',
            'notify_department_head' => '0',
            'barcode_format' => 'code128',
            'auto_generate_barcodes' => '1',
            'barcode_prefix' => 'INV-',
            'include_location_in_barcode' => '0',
            'label_paper_size' => 'letter',
            'label_include_name' => '1',
            'label_include_asset_id' => '1',
            'label_include_model' => '0',
            'label_include_location' => '0',
            'label_include_company_name' => '1'
        ];
        
        // Step 7: Migrate data from old settings to new structure
        $insertSql = "INSERT INTO settings (
                       setting_key, setting_value, default_value, setting_group, 
                       display_name, description, field_type, options, 
                       is_required, order_index, is_hidden
                   ) VALUES (
                       ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                   )";
        $insertStmt = $conn->prepare($insertSql);
        
        $migrationCount = 0;
        
        foreach ($oldSettings as $setting) {
            $key = $setting['setting_key'];
            $value = $setting['setting_value'];
            $description = $setting['setting_description'];
            $isPublic = $setting['is_public'];
            
            // Get setting map data, or use default if not specifically mapped
            $mapData = isset($settingsMap[$key]) ? $settingsMap[$key] : $settingsMap['default'];
            
            // Set display name if not provided in the map
            $displayName = $mapData['display_name'];
            if (empty($displayName)) {
                // Convert key to title case for display name
                $displayName = str_replace('_', ' ', $key);
                $displayName = ucwords($displayName);
            }
            
            // Get default value
            $defaultValue = isset($defaultValues[$key]) ? $defaultValues[$key] : $value;
            
            // Insert into new table
            $insertStmt->execute([
                $key,
                $value,
                $defaultValue,
                $mapData['group'],
                $displayName,
                $description,
                $mapData['type'],
                $mapData['options'] ?? null,
                isset($mapData['required']) && $mapData['required'] ? 1 : 0,
                $mapData['index'],
                !$isPublic ? 1 : 0
            ]);
            
            $migrationCount++;
        }
        
        echo "Migrated $migrationCount settings to the new structure.\n";
        
        // Step 8: Check for any missing settings and add them
        foreach ($defaultValues as $key => $defaultValue) {
            // Check if setting exists
            $checkSql = "SELECT COUNT(*) FROM settings WHERE setting_key = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute([$key]);
            
            if ((int)$checkStmt->fetchColumn() === 0) {
                $mapData = isset($settingsMap[$key]) ? $settingsMap[$key] : $settingsMap['default'];
                
                // Set display name if not provided in the map
                $displayName = $mapData['display_name'];
                if (empty($displayName)) {
                    // Convert key to title case for display name
                    $displayName = str_replace('_', ' ', $key);
                    $displayName = ucwords($displayName);
                }
                
                // Insert missing setting
                $insertStmt->execute([
                    $key,
                    $defaultValue,
                    $defaultValue,
                    $mapData['group'],
                    $displayName,
                    '',
                    $mapData['type'],
                    $mapData['options'] ?? null,
                    isset($mapData['required']) && $mapData['required'] ? 1 : 0,
                    $mapData['index'],
                    0
                ]);
                
                echo "Added missing setting: $key\n";
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        echo "Settings table migration completed successfully.\n";
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        
        echo "Error during migration: " . $e->getMessage() . "\n";
        
        // Restore from backup if it exists
        try {
            $checkBackupSql = "SHOW TABLES LIKE 'settings_backup'";
            $checkBackupStmt = $conn->prepare($checkBackupSql);
            $checkBackupStmt->execute();
            
            if ($checkBackupStmt->rowCount() > 0) {
                echo "Restoring from backup...\n";
                
                // Drop the failed new table if it exists
                $dropNewSql = "DROP TABLE IF EXISTS settings";
                $conn->exec($dropNewSql);
                
                // Restore from backup
                $restoreSql = "CREATE TABLE settings LIKE settings_backup";
                $conn->exec($restoreSql);
                
                $restoreDataSql = "INSERT INTO settings SELECT * FROM settings_backup";
                $conn->exec($restoreDataSql);
                
                echo "Restored from backup successfully.\n";
            }
        } catch (Exception $restoreException) {
            echo "Error during restoration: " . $restoreException->getMessage() . "\n";
        }
        
        return false;
    }
}

// Execute the migration
echo "=======================================================\n";
echo "Settings Table Migration\n";
echo "=======================================================\n\n";

$result = migrateSettingsTable();

if ($result) {
    echo "\nMigration completed successfully!\n";
    
    // Keep backup for safety or remove it
    $keepBackup = true; // Set to false to remove backup
    
    if (!$keepBackup) {
        try {
            $conn = getDbConnection();
            $dropBackupSql = "DROP TABLE IF EXISTS settings_backup";
            $conn->exec($dropBackupSql);
            echo "Backup table removed.\n";
        } catch (Exception $e) {
            echo "Error removing backup table: " . $e->getMessage() . "\n";
        }
    } else {
        echo "Backup table 'settings_backup' preserved for reference.\n";
    }
} else {
    echo "\nMigration failed. See errors above.\n";
}

echo "\n=======================================================\n";
echo "End of Migration Script\n";
echo "=======================================================\n";