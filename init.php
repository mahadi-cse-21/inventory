<?php
// Initialize the application
require_once 'config/config.php';
require_once 'config/database.php';

// Load helper classes
require_once 'helpers/UtilityHelper.php';
require_once 'helpers/AuthHelper.php';
require_once 'helpers/UserHelper.php';
require_once 'helpers/InventoryHelper.php';
require_once 'helpers/BorrowHelper.php';
require_once 'helpers/LocationHelper.php';
require_once 'helpers/MaintenanceHelper.php';
require_once 'helpers/SettingsHelper.php';

// Set up error handling
set_error_handler('errorHandler');
set_exception_handler('exceptionHandler');

// Custom error and exception handlers
function errorHandler($errno, $errstr, $errfile, $errline) {
    $message = "Error [$errno] $errstr in $errfile on line $errline";
    error_log($message);
    
    if (error_reporting() & $errno) {
        // Only handle errors that are part of the error_reporting level
        if (ini_get('display_errors')) {
            echo "<div style='color:red;'><strong>Error:</strong> $message</div>";
        } else {
            echo "<div>An error occurred. Please try again or contact support.</div>";
        }
    }
    
    // Don't execute PHP's internal error handler
    return true;
}

function exceptionHandler($exception) {
    $message = "Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    error_log($message);
    
    if (ini_get('display_errors')) {
        echo "<div style='color:red;'><strong>Exception:</strong> " . $exception->getMessage() . "</div>";
        echo "<pre>" . $exception->getTraceAsString() . "</pre>";
    } else {
        echo "<div>An error occurred. Please try again or contact support.</div>";
    }
}

// Flash message helper functions
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return false;
}

// Helper function to check user roles
function hasRole($roles) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    
    if (is_array($roles)) {
        return in_array($_SESSION['user_role'], $roles);
    } else {
        return $_SESSION['user_role'] === $roles;
    }
}

// Redirect helper
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// CSRF token validation
function validateCsrfToken() {
    if (!isset($_POST[CSRF_TOKEN_NAME]) || !isset($_SESSION[CSRF_TOKEN_NAME]) || 
        $_POST[CSRF_TOKEN_NAME] !== $_SESSION[CSRF_TOKEN_NAME]) {
        // Invalid CSRF token
        setFlashMessage('Security validation failed. Please try again.', 'danger');
        redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL);
    }
}

// Clean input data
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}