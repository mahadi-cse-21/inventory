<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'inventory_pro_1');
define('DB_USER', 'root');         // Change according to your setup
define('DB_PASS', '');             // Change according to your setup
define('DB_CHARSET', 'utf8mb4');

// Establish database connection
function getDbConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log the error and show a friendly message
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please contact the administrator.");
        }
    }
    
    return $conn;
}