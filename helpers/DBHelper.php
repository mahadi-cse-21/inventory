<?php
class DbHelper {
    public static function getDbConnection() {
        static $conn = null;

        if ($conn === null) {
            require_once __DIR__ . '/../config/config.php';

            try {
                $dsn = "mysql:host=" . "localhost" . ";dbname=" ."inventory". ";charset=" . 'utf8mb4';
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];

                $conn = new PDO($dsn, 'root', '', $options);
            } catch (PDOException $e) {
                error_log("DB Connection Error: " . $e->getMessage());
                die("Database connection failed.");
            }
        }

        return $conn;
    }
}
