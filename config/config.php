<?php
// Application configuration
define('APP_NAME', 'Inventory Pro');
define('APP_VERSION', '1.0.0');
define('SITE_URL', 'http://localhost/inventory'); // Change according to your setup
define('BASE_URL', '/inventory');
 // Change according to your setup
define('ASSETS_URL', BASE_URL . '/assets');

// Session configuration
define('SESSION_LIFETIME', 3600); // 30 minutes
define('SESSION_NAME', 'inventory_pro_session');

// File upload settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('UPLOAD_DIR', __DIR__ . '/../uploads');

// Pagination settings
define('ITEMS_PER_PAGE', 5);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Time zone
date_default_timezone_set('America/New_York');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');

// Initialize session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
    session_regenerate_id(true);
}

// Set up CSRF token if not exists
if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
}