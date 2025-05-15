<?php
session_start(); // Required for $_SESSION access

require_once __DIR__ . '/../helpers/DbHelper.php';
$conn = DbHelper::getDbConnection();

// ✅ Validate item ID from GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Item ID is required.');
}

$itemId = (int) $_GET['id'];

// ✅ Validate user session
if (!isset($_SESSION['user_id'])) {
    die('User not logged in.');
}

$userId = $_SESSION['user_id'];

// ✅ Default values
$quantity = 1;
$status = 'pending';
$requestDate = date('Y-m-d');

// ✅ Insert into requests table (WITHOUT approved_admin_id)
$sql = "INSERT INTO requests (user_id, item_id, quantity, request_date, status) 
        VALUES (?, ?, ?, ?, ?)";

try {
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([
        $userId,
        $itemId,
        $quantity,
        $requestDate,
        $status
    ]);

    if ($success) {
        // ✅ Redirect after success
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/dashboard.php'));
        exit;
    } else {
        throw new Exception('Failed to execute insert.');
    }
} catch (PDOException $e) {
    // 🔴 Error during insert
    die('Database error: ' . $e->getMessage());
}
