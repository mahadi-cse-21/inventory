<?php
require_once(__DIR__ . '/../../helpers/DbHelper.php');

$conn = DbHelper::getDbConnection();



if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Validate item_id
$itemId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($itemId <= 0) {
    echo "Invalid item ID.";
    exit;
}

// Fetch item details
$stmt = $conn->prepare("SELECT * FROM item WHERE id = ?");
$stmt->execute([$itemId]);
$item = $stmt->fetch();

if (!$item || $item['status'] !== 'available') {
    echo "Item not available for request.";
    exit;
}

// Create a borrow request
$stmt = $conn->prepare("INSERT INTO requests (user_id, item_id, status, request_date) VALUES (?, ?, 'pending', NOW())");
$stmt->execute([$_SESSION['user_id'], $itemId]);

// Redirect or show confirmation
header('Location: ' . BASE_URL . '/items/browse?success=requested');
exit;
?>
