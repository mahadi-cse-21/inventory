<?php
/**
 * Settings API Endpoints
 * 
 * This file handles API requests for settings management
 * Updated to work with the enhanced settings table structure
 */

// Required headers
header("Content-Type: application/json");

// Check if user is authenticated and has admin role
if (!AuthHelper::isAuthenticated() || !hasRole('admin')) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'You do not have permission to access this resource.'
    ]);
    exit;
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get request endpoint
$endpoint = isset($_GET['endpoint']) ? cleanInput($_GET['endpoint']) : '';

// Process request based on method and endpoint
switch ($method) {
    case 'GET':
        handleGetRequest($endpoint);
        break;
    case 'POST':
        handlePostRequest($endpoint);
        break;
    case 'PUT':
        handlePutRequest($endpoint);
        break;
    case 'DELETE':
        handleDeleteRequest($endpoint);
        break;
    default:
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
        break;
}

/**
 * Handle GET requests
 */
function handleGetRequest($endpoint) {
    switch ($endpoint) {
        case 'all':
            // Get all settings
            $group = isset($_GET['group']) ? cleanInput($_GET['group']) : null;
            $publicOnly = isset($_GET['public_only']) && $_GET['public_only'] === '1';
            
            $settings = $group 
                ? SettingsHelper::getSettingsByGroup($group, $publicOnly)
                : SettingsHelper::getAllSettings($publicOnly);
            
            echo json_encode([
                'success' => true,
                'data' => $settings
            ]);
            break;
        
        case 'groups':
            // Get all setting groups
            $conn = getDbConnection();
            $sql = "SELECT DISTINCT setting_group FROM settings ORDER BY setting_group";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            
            $groups = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo json_encode([
                'success' => true,
                'data' => $groups
            ]);
            break;
        
        case 'export':
            // Export settings as JSON
            $publicOnly = isset($_GET['public_only']) && $_GET['public_only'] === '1';
            $jsonData = SettingsHelper::exportSettings($publicOnly);
            
            echo json_encode([
                'success' => true,
                'data' => $jsonData
            ]);
            break;
        
        default:
            // Get a specific setting
            if (!empty($endpoint)) {
                $settingData = SettingsHelper::getSettingData($endpoint);
                
                if ($settingData) {
                    echo json_encode([
                        'success' => true,
                        'data' => $settingData
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Setting not found.'
                    ]);
                }
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid request. Please specify an endpoint.'
                ]);
            }
            break;
    }
}

/**
 * Handle POST requests
 */
function handlePostRequest($endpoint) {
    // Get POST data
    $postData = json_decode(file_get_contents('php://input'), true);
    
    // Validate CSRF token except for test endpoints in development
    if (!isset($postData['csrf_token']) || $postData['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
        // Allow test endpoints without CSRF in development environment
        if (!(ENVIRONMENT === 'development' && in_array($endpoint, ['test-email', 'test-connection']))) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid CSRF token.'
            ]);
            exit;
        }
    }
    
    switch ($endpoint) {
        case 'update':
            // Update multiple settings
            if (isset($postData['settings']) && is_array($postData['settings'])) {
                // Validate settings before updating
                $validSettings = [];
                $invalidSettings = [];
                
                foreach ($postData['settings'] as $key => $value) {
                    $validationResult = SettingsHelper::validateSettingValue($key, $value);
                    
                    if ($validationResult['valid']) {
                        $validSettings[$key] = $validationResult['value'];
                    } else {
                        $invalidSettings[$key] = $validationResult['message'];
                    }
                }
                
                if (!empty($invalidSettings)) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Some settings have invalid values.',
                        'errors' => $invalidSettings
                    ]);
                    return;
                }
                
                $result = SettingsHelper::updateSettings($validSettings);
                
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Settings updated successfully.',
                        'reload' => $result['reload'] ?? false
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => $result['message']
                    ]);
                }
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid request. Please provide settings to update.'
                ]);
            }
            break;
        
        case 'add':
            // Add a new setting
            if (isset($postData['key']) && isset($postData['value'])) {
                $result = SettingsHelper::addSetting(
                    $postData['key'],
                    $postData['value'],
                    $postData['default_value'] ?? null,
                    $postData['group'] ?? 'general',
                    $postData['display_name'] ?? '',
                    $postData['description'] ?? '',
                    $postData['field_type'] ?? 'text',
                    $postData['options'] ?? null,
                    $postData['is_required'] ?? false,
                    $postData['order_index'] ?? 0,
                    $postData['is_hidden'] ?? false
                );
                
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Setting added successfully.',
                        'reload' => true
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => $result['message']
                    ]);
                }
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid request. Please provide key and value.'
                ]);
            }
            break;
        
        case 'import':
            // Import settings from JSON
            if (isset($postData['json_data'])) {
                $result = SettingsHelper::importSettings($postData['json_data']);
                
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => $result['message'],
                        'reload' => $result['reload'] ?? true
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => $result['message']
                    ]);
                }
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid request. Please provide JSON data.'
                ]);
            }
            break;
        
        case 'reset':
            // Reset all settings to default values
            $result = SettingsHelper::resetSettings();
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => 'All settings have been reset to default values.',
                    'reload' => true
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }
            break;
        
        case 'clear-cache':
            // Clear system cache
            $cacheDir = __DIR__ . '/../cache';
            
            // Check if cache directory exists
            if (file_exists($cacheDir) && is_dir($cacheDir)) {
                $files = glob($cacheDir . '/*');
                $success = true;
                $errors = [];
                
                // Delete all files in cache directory
                foreach ($files as $file) {
                    if (is_file($file)) {
                        if (!@unlink($file)) {
                            $success = false;
                            $errors[] = "Failed to delete: " . basename($file);
                        }
                    }
                }
                
                if ($success) {
                    // Log activity
                    UtilityHelper::logActivity(
                        $_SESSION['user_id'] ?? null,
                        'clear_cache',
                        'settings',
                        null,
                        'Cleared system cache'
                    );
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'System cache cleared successfully.'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to clear some cache files: ' . implode(', ', $errors)
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => 'Cache directory does not exist or is empty.'
                ]);
            }
            break;
        
        case 'test-email':
            // Send test email
            if (isset($postData['email'])) {
                // Get email settings from post data
                $emailSettings = [
                    'smtp_host' => $postData['smtp_host'] ?? '',
                    'smtp_port' => $postData['smtp_port'] ?? '',
                    'smtp_security' => $postData['smtp_security'] ?? '',
                    'smtp_auth' => $postData['smtp_auth'] ?? 0,
                    'smtp_username' => $postData['smtp_username'] ?? '',
                    'smtp_password' => $postData['smtp_password'] ?? '',
                    'from_email' => $postData['from_email'] ?? '',
                    'from_name' => $postData['from_name'] ?? ''
                ];
                
                // Create email helper instance
                require_once __DIR__ . '/../lib/EmailHelper.php';
                $emailHelper = new EmailHelper($emailSettings);
                
                // Send test email
                $subject = 'Test Email from Inventory Pro';
                $body = 'This is a test email from your Inventory Pro system. If you received this email, your email settings are configured correctly.';
                
                $result = $emailHelper->sendEmail($postData['email'], $subject, $body);
                
                if ($result['success']) {
                    // Log activity
                    UtilityHelper::logActivity(
                        $_SESSION['user_id'] ?? null,
                        'test_email',
                        'settings',
                        null,
                        'Sent test email to: ' . $postData['email']
                    );
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Test email sent successfully to ' . $postData['email']
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to send test email: ' . $result['message']
                    ]);
                }
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid request. Please provide an email address.'
                ]);
            }
            break;
        
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid endpoint.'
            ]);
            break;
    }
}

/**
 * Handle PUT requests
 */
function handlePutRequest($endpoint) {
    // Get PUT data
    $putData = json_decode(file_get_contents('php://input'), true);
    
    // Validate CSRF token
    if (!isset($putData['csrf_token']) || $putData['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid CSRF token.'
        ]);
        exit;
    }
    
    // Update a specific setting
    if (!empty($endpoint) && isset($putData['value'])) {
        // Validate the setting value
        $validationResult = SettingsHelper::validateSettingValue($endpoint, $putData['value']);
        
        if (!$validationResult['valid']) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $validationResult['message']
            ]);
            return;
        }
        
        $result = SettingsHelper::updateSetting($endpoint, $validationResult['value']);
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'Setting updated successfully.',
                'reload' => $result['reload'] ?? false
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $result['message']
            ]);
        }
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request. Please provide a setting key and value.'
        ]);
    }
}

/**
 * Handle DELETE requests
 */
function handleDeleteRequest($endpoint) {
    // Validate CSRF token via query parameter
    if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid CSRF token.'
        ]);
        exit;
    }
    
    // Delete a specific setting
    if (!empty($endpoint)) {
        $result = SettingsHelper::deleteSetting($endpoint);
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'Setting deleted successfully.',
                'reload' => true
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $result['message']
            ]);
        }
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request. Please provide a setting key.'
        ]);
    }
}