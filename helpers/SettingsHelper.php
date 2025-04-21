<?php
/**
 * Settings Helper Class
 * 
 * This class handles all settings-related operations
 * Updated to work with the enhanced settings table structure
 */
class SettingsHelper {
    /**
     * Get all settings
     * 
     * @param bool $publicOnly Whether to get only public settings
     * @param string $group Filter by setting group
     * @return array Associative array of settings
     */
    public static function getAllSettings($publicOnly = false, $group = null) {
        $conn = getDbConnection();
        
        $sql = "SELECT setting_key, setting_value, default_value, setting_group, 
                       display_name, description, field_type, options, is_required, 
                       is_hidden, order_index
                FROM settings";
        
        $conditions = [];
        $params = [];
        
        if ($publicOnly) {
            $conditions[] = "is_hidden = 0";
        }
        
        if ($group) {
            $conditions[] = "setting_group = ?";
            $params[] = $group;
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $sql .= " ORDER BY setting_group, order_index, setting_key";
        
        $stmt = $conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }
        
        $settings = [];
        $results = $stmt->fetchAll();
        
        foreach ($results as $row) {
            $settings[$row['setting_key']] = [
                'value' => $row['setting_value'],
                'default_value' => $row['default_value'],
                'group' => $row['setting_group'],
                'display_name' => $row['display_name'],
                'description' => $row['description'],
                'field_type' => $row['field_type'],
                'options' => !empty($row['options']) ? json_decode($row['options'], true) : null, // Add null check
                'is_required' => (bool)$row['is_required'],
                'is_hidden' => (bool)$row['is_hidden'],
                'order_index' => $row['order_index']
            ];
        }
        
        return $settings;
    }
    
    /**
     * Get settings by group
     * 
     * @param string $group Setting group name
     * @param bool $publicOnly Whether to get only public settings
     * @return array Associative array of settings for the specified group
     */
    public static function getSettingsByGroup($group, $publicOnly = false) {
        return self::getAllSettings($publicOnly, $group);
    }
    
    /**
     * Get a specific setting value
     * 
     * @param string $key Setting key
     * @param mixed $default Default value if setting not found
     * @return mixed Setting value or default
     */
    public static function getSetting($key, $default = null) {
        $conn = getDbConnection();
        
        $sql = "SELECT setting_value, default_value FROM settings WHERE setting_key = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$key]);
        
        $row = $stmt->fetch();
        
        if ($row) {
            return $row['setting_value'] !== null && $row['setting_value'] !== '' 
                   ? $row['setting_value'] 
                   : ($row['default_value'] ?? $default);
        }
        
        return $default;
    }
    
    /**
     * Get complete setting information
     * 
     * @param string $key Setting key
     * @return array|null Complete setting data or null if not found
     */
    public static function getSettingData($key) {
        $conn = getDbConnection();
        
        $sql = "SELECT * FROM settings WHERE setting_key = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$key]);
        
        $row = $stmt->fetch();
        
        if ($row) {
            return [
                'key' => $row['setting_key'],
                'value' => $row['setting_value'],
                'default_value' => $row['default_value'],
                'group' => $row['setting_group'],
                'display_name' => $row['display_name'],
                'description' => $row['description'],
                'field_type' => $row['field_type'],
                'options' => json_decode($row['options'], true),
                'is_required' => (bool)$row['is_required'],
                'is_hidden' => (bool)$row['is_hidden'],
                'order_index' => $row['order_index']
            ];
        }
        
        return null;
    }
    

    /**
     * Update settings method with debugging and enhanced error handling
     */
    public static function updateSettings($settings) {
        $conn = getDbConnection();
        
        try {
            // Debug: Log settings being updated
            error_log("Updating settings: " . print_r($settings, true));
            
            $conn->beginTransaction();
            
            $updateSql = "UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?";
            $updateStmt = $conn->prepare($updateSql);
            
            $updatedCount = 0;
            $errors = [];
            
            foreach ($settings as $key => $value) {
                // Skip CSRF token or other non-setting inputs that might be in the form
                if (strpos($key, 'csrf_token') !== false || strpos($key, '_form') !== false) {
                    continue;
                }
                
                // Check if setting exists
                $checkSql = "SELECT COUNT(*) FROM settings WHERE setting_key = ?";
                $checkStmt = $conn->prepare($checkSql);
                $checkStmt->execute([$key]);
                
                if ((int)$checkStmt->fetchColumn() === 0) {
                    $errors[] = "Setting key does not exist: $key";
                    error_log("Setting key does not exist: $key");
                    continue;
                }
                
                // Debug: Log each update operation
                error_log("Updating setting: $key = $value");
                
                // Execute update
                $result = $updateStmt->execute([$value, $key]);
                
                if ($result) {
                    $updatedCount++;
                } else {
                    $errors[] = "Failed to update setting: $key";
                    error_log("Failed to update setting: $key. PDO error: " . print_r($updateStmt->errorInfo(), true));
                }
            }
            
            // If there were any errors, roll back
            if (!empty($errors)) {
                $conn->rollBack();
                
                error_log("Settings update failed: " . implode(", ", $errors));
                
                return [
                    'success' => false,
                    'message' => 'Failed to update settings: ' . implode(", ", $errors),
                    'errors' => $errors
                ];
            }
            
            // If we got here, all updates were successful
            $conn->commit();
            
            // Log settings update
            if (class_exists('UtilityHelper')) {
                UtilityHelper::logActivity(
                    $_SESSION['user_id'] ?? null,
                    'update_settings',
                    'settings',
                    null,
                    'Updated system settings: ' . implode(', ', array_keys($settings))
                );
            }
            
            error_log("Successfully updated $updatedCount settings");
            
            return [
                'success' => true,
                'message' => "Settings updated successfully ($updatedCount changes).",
                'reload' => true
            ];
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            
            error_log("Exception in updateSettings: " . $e->getMessage());
            error_log($e->getTraceAsString());
            
            return [
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ];
        }
    }
    /**
     * Update a single setting
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return array Result with success status and message
     */
    public static function updateSetting($key, $value) {
        return self::updateSettings([$key => $value]);
    }
    
    /**
     * Add a new setting
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @param string $defaultValue Default value
     * @param string $group Setting group
     * @param string $displayName Display name
     * @param string $description Setting description
     * @param string $fieldType Field type (text, select, toggle, etc.)
     * @param array $options Options for select fields
     * @param bool $isRequired Whether the setting is required
     * @param int $orderIndex Display order index
     * @param bool $isHidden Whether the setting is hidden
     * @return array Result with success status and message
     */
    public static function addSetting($key, $value, $defaultValue = null, $group = 'general', 
                                     $displayName = '', $description = '', $fieldType = 'text', 
                                     $options = null, $isRequired = false, $orderIndex = 0, 
                                     $isHidden = false) {
        $conn = getDbConnection();
        
        // Check if setting already exists
        $checkSql = "SELECT COUNT(*) FROM settings WHERE setting_key = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute([$key]);
        
        if ((int)$checkStmt->fetchColumn() > 0) {
            return [
                'success' => false,
                'message' => 'Setting with this key already exists.'
            ];
        }
        
        try {
            $sql = "INSERT INTO settings (setting_key, setting_value, default_value, setting_group, 
                                         display_name, description, field_type, options, 
                                         is_required, order_index, is_hidden) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $optionsJson = $options !== null ? json_encode($options) : null;
            
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([
                $key, 
                $value, 
                $defaultValue, 
                $group, 
                $displayName, 
                $description, 
                $fieldType, 
                $optionsJson, 
                $isRequired ? 1 : 0, 
                $orderIndex, 
                $isHidden ? 1 : 0
            ]);
            
            if (!$result) {
                return [
                    'success' => false,
                    'message' => 'Failed to add setting.'
                ];
            }
            
            // Log activity
            UtilityHelper::logActivity(
                $_SESSION['user_id'] ?? null,
                'add_setting',
                'settings',
                null,
                'Added new setting: ' . $key
            );
            
            return [
                'success' => true,
                'message' => 'Setting added successfully.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to add setting: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete a setting
     * 
     * @param string $key Setting key
     * @return array Result with success status and message
     */
    public static function deleteSetting($key) {
        $conn = getDbConnection();
        
        try {
            $sql = "DELETE FROM settings WHERE setting_key = ?";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([$key]);
            
            if (!$result) {
                return [
                    'success' => false,
                    'message' => 'Failed to delete setting.'
                ];
            }
            
            // Log activity
            UtilityHelper::logActivity(
                $_SESSION['user_id'] ?? null,
                'delete_setting',
                'settings',
                null,
                'Deleted setting: ' . $key
            );
            
            return [
                'success' => true,
                'message' => 'Setting deleted successfully.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete setting: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Reset all settings to default values
     * 
     * @return array Result with success status and message
     */
    public static function resetSettings() {
        $conn = getDbConnection();
        
        try {
            $conn->beginTransaction();
            
            // Update all settings to their default values
            $updateSql = "UPDATE settings SET setting_value = default_value, updated_at = NOW()";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->execute();
            
            $conn->commit();
            
            // Log activity
            UtilityHelper::logActivity(
                $_SESSION['user_id'] ?? null,
                'reset_settings',
                'settings',
                null,
                'Reset all system settings to default values'
            );
            
            return [
                'success' => true,
                'message' => 'All settings have been reset to default values.',
                'reload' => true
            ];
        } catch (Exception $e) {
            $conn->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to reset settings: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Export settings as JSON
     * 
     * @param bool $publicOnly Whether to export only public settings
     * @return string JSON representation of settings
     */
    public static function exportSettings($publicOnly = false) {
        $settings = self::getAllSettings($publicOnly);
        
        // Simplify to key => value format
        $exportData = [];
        foreach ($settings as $key => $data) {
            $exportData[$key] = $data['value'];
        }
        
        // Log activity
        UtilityHelper::logActivity(
            $_SESSION['user_id'] ?? null,
            'export_settings',
            'settings',
            null,
            'Exported system settings'
        );
        
        return json_encode($exportData, JSON_PRETTY_PRINT);
    }
    
    /**
     * Import settings from JSON
     * 
     * @param string $jsonData JSON data to import
     * @return array Result with success status and message
     */
    public static function importSettings($jsonData) {
        $conn = getDbConnection();
        
        try {
            $data = json_decode($jsonData, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'message' => 'Invalid JSON data: ' . json_last_error_msg()
                ];
            }
            
            // Validate settings
            $existingSettings = self::getAllSettings();
            $validSettings = [];
            $invalidSettings = [];
            
            foreach ($data as $key => $value) {
                if (isset($existingSettings[$key])) {
                    $validSettings[$key] = $value;
                } else {
                    $invalidSettings[] = $key;
                }
            }
            
            // Update valid settings
            if (!empty($validSettings)) {
                $result = self::updateSettings($validSettings);
                
                if (!$result['success']) {
                    return $result;
                }
            }
            
            // Log activity
            UtilityHelper::logActivity(
                $_SESSION['user_id'] ?? null,
                'import_settings',
                'settings',
                null,
                'Imported system settings'
            );
            
            $message = 'Settings imported successfully.';
            
            if (!empty($invalidSettings)) {
                $message .= ' The following settings were ignored because they do not exist: ' . implode(', ', $invalidSettings);
            }
            
            return [
                'success' => true,
                'message' => $message,
                'reload' => true
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to import settings: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate setting value based on its type and rules
     * 
     * @param string $key Setting key
     * @param mixed $value Value to validate
     * @return array Result with valid status and message
     */
    public static function validateSettingValue($key, $value) {
        $settingData = self::getSettingData($key);
        
        if (!$settingData) {
            return [
                'valid' => false,
                'message' => 'Setting not found.'
            ];
        }
        
        // Check if required
        if ($settingData['is_required'] && ($value === null || $value === '')) {
            return [
                'valid' => false,
                'message' => 'This setting is required.'
            ];
        }
        
        // Validate based on field type
        switch ($settingData['field_type']) {
            case 'number':
                if ($value !== '' && !is_numeric($value)) {
                    return [
                        'valid' => false,
                        'message' => 'Value must be a number.'
                    ];
                }
                break;
                
            case 'email':
                if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return [
                        'valid' => false,
                        'message' => 'Invalid email format.'
                    ];
                }
                break;
                
            case 'select':
                if ($value !== '' && $settingData['options'] && !in_array($value, $settingData['options'])) {
                    return [
                        'valid' => false,
                        'message' => 'Invalid option selected.'
                    ];
                }
                break;
                
            case 'toggle':
                // Convert to boolean or 1/0
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
                break;
        }
        
        return [
            'valid' => true,
            'value' => $value
        ];
    }
    
    /**
     * Get all available language options
     * 
     * @return array List of language options
     */
    public static function getLanguageOptions() {
        return [
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'zh' => 'Chinese',
            'ja' => 'Japanese',
            'ru' => 'Russian',
            'ar' => 'Arabic',
            'pt' => 'Portuguese',
            'it' => 'Italian'
        ];
    }
    
    /**
     * Get all available timezone options
     * 
     * @return array List of timezone options
     */
    public static function getTimezoneOptions() {
        $timezones = [
            'UTC' => 'UTC (Coordinated Universal Time)',
            'America/New_York' => 'Eastern Time (US & Canada)',
            'America/Chicago' => 'Central Time (US & Canada)',
            'America/Denver' => 'Mountain Time (US & Canada)',
            'America/Los_Angeles' => 'Pacific Time (US & Canada)',
            'America/Anchorage' => 'Alaska Time',
            'America/Adak' => 'Hawaii-Aleutian Time',
            'Europe/London' => 'London, Edinburgh (GMT)',
            'Europe/Paris' => 'Paris, Berlin, Rome, Madrid',
            'Europe/Moscow' => 'Moscow, St. Petersburg',
            'Asia/Tokyo' => 'Tokyo, Osaka',
            'Asia/Shanghai' => 'Beijing, Shanghai',
            'Asia/Kolkata' => 'Mumbai, New Delhi',
            'Australia/Sydney' => 'Sydney, Melbourne',
            'Pacific/Auckland' => 'Auckland, Wellington'
        ];
        
        // For more complete list, you could use DateTimeZone::listIdentifiers()
        
        return $timezones;
    }
    
    /**
     * Get all available date format options
     * 
     * @return array List of date format options
     */
    public static function getDateFormatOptions() {
        $today = new DateTime();
        
        return [
            'M j, Y' => $today->format('M j, Y') . ' (M j, Y)',
            'F j, Y' => $today->format('F j, Y') . ' (F j, Y)',
            'j M Y' => $today->format('j M Y') . ' (j M Y)',
            'j F Y' => $today->format('j F Y') . ' (j F Y)',
            'Y-m-d' => $today->format('Y-m-d') . ' (Y-m-d)',
            'm/d/Y' => $today->format('m/d/Y') . ' (m/d/Y)',
            'd/m/Y' => $today->format('d/m/Y') . ' (d/m/Y)'
        ];
    }
    
    /**
     * Get all available time format options
     * 
     * @return array List of time format options
     */
    public static function getTimeFormatOptions() {
        $now = new DateTime();
        
        return [
            'g:i A' => $now->format('g:i A') . ' (g:i A)',
            'h:i A' => $now->format('h:i A') . ' (h:i A)',
            'g:i a' => $now->format('g:i a') . ' (g:i a)',
            'h:i a' => $now->format('h:i a') . ' (h:i a)',
            'H:i' => $now->format('H:i') . ' (H:i)',
            'H:i:s' => $now->format('H:i:s') . ' (H:i:s)'
        ];
    }
}