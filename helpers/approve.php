<?php
require_once __DIR__ . '/../helpers/DbHelper.php';
$conn = DbHelper::getDbConnection();

// Get request ID from URL
if (!isset($_GET['id'])) {
    die('Request ID is required.');
}

$requestId = (int) $_GET['id'];

// Update request status to 'approved'
$updateSql = "UPDATE requests SET status = 'approved' WHERE id = ?";
$updateStmt = $conn->prepare($updateSql);
$updateSuccess = $updateStmt->execute([$requestId]);

// Fetch item_id and quantity
$selectSql = "SELECT item_id, quantity FROM requests WHERE id = ?";
$selectStmt = $conn->prepare($selectSql);
$selectStmt->execute([$requestId]);
$row = $selectStmt->fetch();
$row['quantity']=1;

if (!$row || empty($row['item_id']) || empty($row['quantity'])) {
    die('Missing item or quantity in request.');
}

// Insert into borrowed_item
$insertSql = "INSERT INTO borrowed_item (request_id, item_id, quantity, borrow_date, due_date, status) 
              VALUES (?, ?, ?, NOW(), ?, ?)";

$dueDate = date('Y-m-d', strtotime('+7 days'));

$insertStmt = $conn->prepare($insertSql);
$insertSuccess = $insertStmt->execute([
    $requestId,            // request_id
    $row['item_id'],       // item_id
    1,      // quantity
    $dueDate,              // due_date
    'borrowed'             // status
]);

// Fetch current quantity of the item
$itemId = $row['item_id']; // Use the item_id from the request

$sql = "SELECT available_quantity FROM item WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$itemId]);

$item = $stmt->fetch();
if (!$item) {
    die('Item not found.');
}

// Update the item quantity (subtracting the quantity requested)
$newQuantity = $item['available_quantity'] - 1; // Subtract the requested quantity

echo $newQuantity;
// Ensure quantity does not go below 0
if ($newQuantity < 0) {
    die('Not enough stock available.');
}

// Update the quantity in the database
$updateSql = "UPDATE item SET available_quantity = ? WHERE id = ?";
$stmt = $conn->prepare($updateSql);



$updateSuccessItem = $stmt->execute([$newQuantity, $itemId]);



// Check if all updates were successful
if ($updateSuccess && $insertSuccess && $updateSuccessItem) {
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/dashboard'));
    exit;
} else {
    die('Failed to approve request or insert borrowed item.');
}
?>
