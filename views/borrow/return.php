<?php


require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../config/database.php');

require_once(__DIR__ . '/../../helpers/UtilityHelper.php');
require_once(__DIR__ . '/../../helpers/AuthHelper.php');
require_once(__DIR__ . '/../../helpers/UserHelper.php');
require_once(__DIR__ . '/../../helpers/InventoryHelper.php');

require_once(__DIR__ . '/../../helpers/BorrowHelper.php');
require_once(__DIR__ . '/../../helpers/LocationHelper.php');
require_once(__DIR__ . '/../../helpers/SettingsHelper.php');
require_once(__DIR__ . '/../../helpers/MaintenanceHelper.php');
require_once(__DIR__ . '/../../init.php');



// Now you can safely use AuthHelper
// Example usage


/**
 * Return Borrowed Items (Admin/Manager View)
 * 
 * This file allows admins/managers to process item returns
 */

// Set page title
$pageTitle = 'Return Borrowed Items';

// Check if user is logged in and has the right permissions
if (!AuthHelper::isAuthenticated() || !hasRole(['admin'])) {
    setFlashMessage('You do not have permission to access this page.', 'danger');
    redirect(BASE_URL . '/dashboard');
    exit;
}


$currentUser = AuthHelper::getCurrentUser();



$conn = getDbConnection();


$borrowedItemId = $_GET['id'];

try {
    $conn->beginTransaction();

    $stmtInfo = $conn->prepare("SELECT request_id, item_id FROM borrowed_item WHERE id = ?");
    $stmtInfo->execute([$borrowedItemId]);
    $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        throw new Exception("Invalid borrowed item ID.");
    }

    $requestId = $info['request_id'];
    $itemId = $info['item_id'];

    $stmt1 = $conn->prepare("UPDATE borrowed_item 
                             SET status = 'returned', 
                                 return_date = NOW() 
                             WHERE id = ?");
    $stmt1->execute([$borrowedItemId]);

    $stmt2 = $conn->prepare("UPDATE item 
                             SET available_quantity = available_quantity + 1 
                             WHERE id = ?");
    $stmt2->execute([$itemId]);

    $stmt3 = $conn->prepare("SELECT COUNT(*) FROM borrowed_item 
                             WHERE request_id = ? AND status != 'returned'");
    $stmt3->execute([$requestId]);

    $remaining = $stmt3->fetchColumn();

    if ($remaining == 0) {
        $stmt4 = $conn->prepare("UPDATE requests 
                                 SET status = 'completed' 
                                 WHERE id = ?");
        $stmt4->execute([$requestId]);
    }

    $conn->commit();
   header("Location: " . BASE_URL . "/borrow/requests");
} catch (Exception $e) {
    $conn->rollBack();
    echo "Error: " . $e->getMessage();
}
