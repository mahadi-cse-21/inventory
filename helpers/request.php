<?php
require_once __DIR__ . '/../helpers/DbHelper.php';
$conn = DbHelper::getDbConnection();

// Example: get item_id from URL (e.g., create_request.php?item_id=123)
if (!isset($_GET['id'])) {
    die('Item ID is required.');
}

$itemId = (int) $_GET['id'];

// Get logged-in user ID (e.g., from session)
session_start();
if (!isset($_SESSION['user_id'])) {
    die('User not logged in.');
}
$userId = $_SESSION['user_id'];

// Optional default values
$quantity = 1;
$status = 'pending';
$requestDate = date('Y-m-d');

// Insert into requests table
$sql = "INSERT INTO requests (user_id, item_id, quantity, request_date, status) 
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$success = $stmt->execute([
    $userId,
    $itemId,
    $quantity,
    $requestDate,
    $status
]);

if ($success) {
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/dashboard.php'));
    exit;
} else {
    die('Failed to create request.');
}
