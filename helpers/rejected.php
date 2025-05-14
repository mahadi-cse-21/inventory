<?php
require_once __DIR__ . '/../helpers/DbHelper.php';
$conn = DbHelper::getDbConnection();

// Check if the request ID is provided
if (!isset($_GET['id'])) {
    die('Request ID is required.');
}

$requestId = (int) $_GET['id'];

// Update request status to 'rejected'
$sql = "UPDATE requests SET status = 'rejected' WHERE id = ?";
$stmt = $conn->prepare($sql);
$success = $stmt->execute([$requestId]);

if ($success) {
    // Check if the referer is set, otherwise redirect to the dashboard
    $redirectUrl = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/dashboard.php';
    header('Location: ' . $redirectUrl);
    exit;
} else {
    die('Failed to reject request.');
}
