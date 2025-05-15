<?php
class BorrowHelper
{
    /**
     * Get borrow request by ID
     */
    public static function getBorrowRequestById($requestId)
    {
       
        $conn = getDbConnection();

        $stmt = $conn->prepare("
        SELECT 
            r.id,
            u.name AS name,
            r.request_date,
            bi.borrow_date,
            bi.due_date,
            bi.return_date,
            bi.status as status,
            u.email as requester_email
        FROM requests r
        JOIN users u ON r.user_id = u.id
        JOIN borrowed_item bi ON bi.request_id = r.id
        WHERE r.id = :requestId
    ");

        $stmt->execute(['requestId' => $requestId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }




    /**
     * Get borrowed items for a borrow request
     */
    public static function getBorrowedItems($borrowRequestId)
    {
        $conn = getDbConnection();

        $sql = "SELECT bi.*, i.name as item_name, i.asset_id, i.barcode, i.category_id,
                       c.name as category_name, i.location_id, l.name as location_name
                FROM borrowed_items bi 
                LEFT JOIN items i ON bi.item_id = i.id 
                LEFT JOIN categories c ON i.category_id = c.id
                LEFT JOIN locations l ON i.location_id = l.id
                WHERE bi.borrow_request_id = ?
                ORDER BY i.name";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$borrowRequestId]);

        return $stmt->fetchAll();
    }

    /**
     * Get all borrow requests with pagination and filters
     */
   /**
     * Get all borrow requests with pagination and filters
     */
public static function getAllBorrowRequests($filters = [], $currentUser = null)
{
    $currentUser = AuthHelper::getCurrentUser();
    $conn = getDbConnection();

    // Add debug logging
    error_log("getAllBorrowRequests called with user role: " . ($currentUser ? $currentUser['role'] : 'null'));

    $sql = "SELECT
    r.id,
    u.id AS user_id,
    r.request_date,
    i.name as item,
    r.status AS status
    FROM requests r
    JOIN users u ON r.user_id = u.id
    JOIN item i ON r.item_id = i.id";

    $where = [];
    $params = [];

    // Apply filters from $filters array
    if (!empty($filters['search'])) {
        $where[] = "(r.id LIKE ? OR u.name LIKE ?)";
        $searchParam = '%' . $filters['search'] . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    if (!empty($filters['user_id'])) {
        $where[] = "r.user_id = ?";
        $params[] = $filters['user_id'];
    }

    if (!empty($filters['status'])) {
        $where[] = "r.status = ?";
        $params[] = $filters['status'];
    }

    if (!empty($filters['item_id'])) {
        $where[] = "i.id = ?";
        $params[] = $filters['item_id'];
    }

    // Role-based filtering: if not admin, only show own requests
    if ($currentUser) {
        if ($currentUser['role'] == 'student') {
            error_log("Applying student filter - only showing user's own requests");
            $where[] = "r.user_id = ?";
            $params[] = $currentUser['id'];
        } else if ($currentUser['role'] == 'admin') {
            error_log("Admin user detected - showing all requests");
            // Admin sees all requests, no filter needed
        } else {
            error_log("Unknown role: " . $currentUser['role'] . " - defaulting to user's own requests");
            $where[] = "r.user_id = ?";
            $params[] = $currentUser['id'];
        }
    } else {
        error_log("No current user provided - showing all requests");
    }

    // Combine WHERE clauses
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    // Add order
    $sql .= " ORDER BY r.request_date DESC";

    // Log the SQL for debugging
    error_log("Executing SQL: " . $sql);

    // Remove pagination, execute the query without LIMIT
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll();

    error_log("Returning " . count($requests) . " requests");

    // Return the requests without pagination
    return [
        'requests' => $requests
    ];
}


    /**
     * Create a new borrow request
     */
    public static function createBorrowRequest($requestData, $items)
    {
        $conn = getDbConnection();

        // Begin transaction
        $conn->beginTransaction();

        try {
            // Generate unique request ID
            $requestId = self::generateBorrowRequestId();

            // Insert borrow request
            $sql = "INSERT INTO borrow_requests (request_id, user_id, department_id, purpose, project_name, 
                                             borrow_date, return_date, pickup_time, status, notes, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([
                $requestId,
                $requestData['user_id'],
                $requestData['department_id'] ?? null,
                $requestData['purpose'],
                $requestData['project_name'] ?? null,
                $requestData['borrow_date'],
                $requestData['return_date'],
                $requestData['pickup_time'] ?? null,
                'pending',
                $requestData['notes'] ?? null
            ]);

            if (!$result) {
                throw new Exception('Failed to create borrow request');
            }

            $borrowRequestId = $conn->lastInsertId();

            // Insert borrowed items
            $itemSql = "INSERT INTO borrowed_items (borrow_request_id, item_id, quantity, condition_before, status) 
                        VALUES (?, ?, ?, ?, ?)";
            $itemStmt = $conn->prepare($itemSql);

            foreach ($items as $item) {
                // Check if item is available
                $availableSql = "SELECT status FROM items WHERE id = ?";
                $availableStmt = $conn->prepare($availableSql);
                $availableStmt->execute([$item['item_id']]);
                $itemStatus = $availableStmt->fetchColumn();

                if ($itemStatus !== 'available' && $itemStatus !== 'reserved') {
                    throw new Exception('Item ' . $item['item_id'] . ' is not available for borrowing');
                }

                // Insert borrowed item
                $itemResult = $itemStmt->execute([
                    $borrowRequestId,
                    $item['item_id'],
                    $item['quantity'] ?? 1,
                    $item['condition_before'] ?? 'good',
                    'pending'
                ]);

                if (!$itemResult) {
                    throw new Exception('Failed to add item to borrow request');
                }
            }

            // Clear from cart if needed
            if (!empty($requestData['clear_cart']) && !empty($requestData['user_id'])) {
                $clearCartSql = "DELETE FROM cart_items WHERE user_id = ?";
                $clearCartStmt = $conn->prepare($clearCartSql);
                $clearCartStmt->execute([$requestData['user_id']]);
            }

            // Log activity
            UtilityHelper::logActivity(
                $requestData['user_id'],
                'create_borrow_request',
                'borrow_requests',
                $borrowRequestId,
                'Borrow request created: ' . $requestId
            );

            // Commit transaction
            $conn->commit();

            return [
                'success' => true,
                'message' => 'Borrow request created successfully',
                'request_id' => $requestId,
                'id' => $borrowRequestId
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();

            return [
                'success' => false,
                'message' => 'Failed to create borrow request: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Approve or reject a borrow request
     */
    public static function updateBorrowRequestStatus($requestId, $status, $userId, $notes = null)
    {
        $conn = getDbConnection();

        try {
            $stmt = $conn->prepare("
            UPDATE requests
            SET status = :status
               
            WHERE id = :requestId
        ");

            $stmt->execute([
                'status' => $status,


                'requestId' => $requestId
            ]);

            return ['success' => true];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error updating borrow request: ' . $e->getMessage()
            ];
        }
    }


    /**
     * Return specific items from a borrow request
     */
    public static function returnBorrowedItems($borrowRequestId, $itemsData, $userId)
    {
        $conn = getDbConnection();

        // Check if request exists
        $request = self::getBorrowRequestById($borrowRequestId);
        if (!$request) {
            return [
                'success' => false,
                'message' => 'Borrow request not found'
            ];
        }

        // Begin transaction
        $conn->beginTransaction();

        try {
            foreach ($itemsData as $itemData) {
                // Update borrowed item
                $updateItemSql = "UPDATE borrowed_item 
                                  SET status = 'returned', 
                                      condition_after = ?, 
                                      return_notes = ?, 
                                      is_returned = 1, 
                                      returned_at = NOW() 
                                  WHERE borrow_request_id = ? AND item_id = ?";

                $updateItemStmt = $conn->prepare($updateItemSql);
                $updateItemStmt->execute([
                    $itemData['condition_after'] ?? 'good',
                    $itemData['notes'] ?? null,
                    $borrowRequestId,
                    $itemData['item_id']
                ]);

                // Update item status in items table
                $updateItemStatusSql = "UPDATE items SET status = 'available' WHERE id = ?";
                $updateItemStatusStmt = $conn->prepare($updateItemStatusSql);
                $updateItemStatusStmt->execute([$itemData['item_id']]);

                // Log inventory transaction
                $transactionData = [
                    'item_id' => $itemData['item_id'],

                    'quantity' => 1,
                    'related_record_type' => 'borrow_requests',
                    'related_record_id' => $borrowRequestId,
                    'notes' => $itemData['notes'] ?? 'Item returned'
                ];
                InventoryHelper::logInventoryTransaction($transactionData);
            }

            // Check if all items are returned
            $checkSql = "SELECT COUNT(*) FROM borrowed_items WHERE borrow_request_id = ? AND is_returned = 0";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute([$borrowRequestId]);
            $remainingItems = (int)$checkStmt->fetchColumn();

            // Update request status if all items are returned
            if ($remainingItems === 0) {
                $updateRequestSql = "UPDATE borrow_requests 
                                     SET status = 'returned', 
                                         returned_by = ?, 
                                         returned_at = NOW(), 
                                         updated_at = NOW() 
                                     WHERE id = ?";

                $updateRequestStmt = $conn->prepare($updateRequestSql);
                $updateRequestStmt->execute([$userId, $borrowRequestId]);
            } else {
                // Set to partially returned
                $updateRequestSql = "UPDATE requests 
                                     SET status = 'partially_returned', 
                                         updated_at = NOW() 
                                     WHERE id = ?";

                $updateRequestStmt = $conn->prepare($updateRequestSql);
                $updateRequestStmt->execute([$borrowRequestId]);
            }

            // Log activity
            UtilityHelper::logActivity(
                $userId,
                'return_borrowed_items',
                'borrow_requests',
                $borrowRequestId,
                'Items returned for borrow request: ' . $request['request_id']
            );

            // Commit transaction
            $conn->commit();

            return [
                'success' => true,
                'message' => 'Items returned successfully',
                'all_returned' => ($remainingItems === 0)
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();

            return [
                'success' => false,
                'message' => 'Failed to return items: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get user's currently borrowed items
     */
    public static function getUserBorrowedItems($userId)
    {
        $conn = getDbConnection();
        if (!$conn) return [];

        $sql = "SELECT bi.id ,i.name as name, r.id as request_id , bi.borrow_date, bi.due_date,bi.status as status,bi.return_date as return_date
      from borrowed_item bi
      join requests r join item i
      where bi.request_id = r.id and i.id = r.item_id and r.user_id=? ";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    /**
     * Get user's overdue items
     */
    public static function getUserOverdueItems($userId)
    {
        $conn = getDbConnection();

        $sql = "SELECT *
        FROM borrowed_item bi 
        join requests r join item i
        WHERE bi.status = 'borrowed' and r.item_id = i.id
        AND return_date IS NULL and r.user_id=?";


        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    /**
     * Check for overdue items and update status
     */
    public static function checkForOverdueItems()
    {
        $conn = getDbConnection();

        // Find overdue requests
        $sql = "SELECT bi.id, bi.request_id
        FROM borrowed_item bi
        JOIN requests br ON bi.request_id = br.id
        WHERE bi.status = 'borrowd'
        AND bi.return_date < CURRENT_DATE()";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $overdueRequests = $stmt->fetchAll();

        $updated = 0;

        // Update each overdue request
        foreach ($overdueRequests as $request) {
            $updateSql = "UPDATE borrow_requests SET status = 'overdue', updated_at = NOW() WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->execute([$request['id']]);

            // Log activity
            UtilityHelper::logActivity(
                null, // System action
                'mark_overdue',
                'borrow_requests',
                $request['id'],
                'Borrow request marked as overdue: ' . $request['request_id']
            );

            $updated++;
        }

        return [
            'success' => true,
            'updated_count' => $updated,
            'message' => "$updated requests marked as overdue"
        ];
    }

    /**
     * Get checkout statistics
     */
    public static function getBorrowStatistics()
    {
        $conn = getDbConnection();

        // Total active checkouts
        $activeSql = "SELECT COUNT(*) FROM borrow_requests WHERE status IN ('checked_out', 'partially_returned', 'overdue')";
        $activeStmt = $conn->prepare($activeSql);
        $activeStmt->execute();
        $activeCheckouts = (int)$activeStmt->fetchColumn();

        // Total pending requests
        $pendingSql = "SELECT COUNT(*) FROM borrow_requests WHERE status = 'pending'";
        $pendingStmt = $conn->prepare($pendingSql);
        $pendingStmt->execute();
        $pendingRequests = (int)$pendingStmt->fetchColumn();

        // Overdue items
        $overdueSql = "SELECT COUNT(*) FROM borrow_requests WHERE status = 'overdue'";
        $overdueStmt = $conn->prepare($overdueSql);
        $overdueStmt->execute();
        $overdueItems = (int)$overdueStmt->fetchColumn();

        // Top borrowed items
        $topItemsSql = "SELECT i.id, i.name, COUNT(*) as borrow_count 
                        FROM borrowed_items bi 
                        JOIN items i ON bi.item_id = i.id 
                        GROUP BY i.id 
                        ORDER BY borrow_count DESC 
                        LIMIT 5";
        $topItemsStmt = $conn->prepare($topItemsSql);
        $topItemsStmt->execute();
        $topItems = $topItemsStmt->fetchAll();

        // Borrow activity by month (last 6 months)
        $activitySql = "SELECT 
                          DATE_FORMAT(created_at, '%Y-%m') as month, 
                          COUNT(*) as request_count 
                        FROM borrow_requests 
                        WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH) 
                        GROUP BY month 
                        ORDER BY month";
        $activityStmt = $conn->prepare($activitySql);
        $activityStmt->execute();
        $monthlyActivity = $activityStmt->fetchAll();

        return [
            'active_checkouts' => $activeCheckouts,
            'pending_requests' => $pendingRequests,
            'overdue_items' => $overdueItems,
            'top_items' => $topItems,
            'monthly_activity' => $monthlyActivity
        ];
    }

    /**
     * Generate a unique borrow request ID
     */
    private static function generateBorrowRequestId()
    {
        $prefix = 'BR-' . date('Y');
        $conn = getDbConnection();

        // Get the maximum request ID with this prefix
        $sql = "SELECT MAX(request_id) FROM borrow_requests WHERE request_id LIKE ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$prefix . '-%']);
        $maxId = $stmt->fetchColumn();

        if ($maxId) {
            // Extract the numeric portion and increment
            $parts = explode('-', $maxId);
            $lastNumber = (int)end($parts);
            $newNumber = $lastNumber + 1;
        } else {
            // First request with this prefix
            $newNumber = 1;
        }

        // Format with leading zeros (e.g., BR-2023-0001)
        return $prefix . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Add item to user's cart
     */
    public static function addToCart($userId, $itemId, $quantity = 1, $borrowDate = null, $returnDate = null, $notes = null)
    {
        $conn = getDbConnection();

        // Check if item exists and is available
        $itemSql = "SELECT id, status FROM items WHERE id = ?";
        $itemStmt = $conn->prepare($itemSql);
        $itemStmt->execute([$itemId]);
        $item = $itemStmt->fetch();

        if (!$item) {
            return [
                'success' => false,
                'message' => 'Item not found'
            ];
        }

        if ($item['status'] !== 'available' && $item['status'] !== 'reserved') {
            return [
                'success' => false,
                'message' => 'Item is not available for borrowing'
            ];
        }

        // Check if item is already in cart
        $checkSql = "SELECT id, quantity FROM cart_items WHERE user_id = ? AND item_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute([$userId, $itemId]);
        $cartItem = $checkStmt->fetch();

        if ($cartItem) {
            // Update quantity if already in cart
            $updateSql = "UPDATE cart_items 
                          SET quantity = ?, 
                              planned_borrow_date = ?, 
                              planned_return_date = ?, 
                              notes = ?, 
                              added_at = NOW() 
                          WHERE id = ?";

            $stmt = $conn->prepare($updateSql);
            $stmt->execute([
                $quantity,
                $borrowDate,
                $returnDate,
                $notes,
                $cartItem['id']
            ]);

            return [
                'success' => true,
                'message' => 'Cart item updated successfully',
                'cart_item_id' => $cartItem['id']
            ];
        } else {
            // Add new item to cart
            $insertSql = "INSERT INTO cart_items (user_id, item_id, quantity, planned_borrow_date, planned_return_date, notes, added_at) 
                          VALUES (?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $conn->prepare($insertSql);
            $stmt->execute([
                $userId,
                $itemId,
                $quantity,
                $borrowDate,
                $returnDate,
                $notes
            ]);

            return [
                'success' => true,
                'message' => 'Item added to cart successfully',
                'cart_item_id' => $conn->lastInsertId()
            ];
        }
    }

    /**
     * Remove item from user's cart
     */
    public static function removeFromCart($userId, $cartItemId)
    {
        $conn = getDbConnection();

        $sql = "DELETE FROM cart_items WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$cartItemId, $userId]);

        $rowCount = $stmt->rowCount();

        return [
            'success' => ($rowCount > 0),
            'message' => ($rowCount > 0) ? 'Item removed from cart successfully' : 'Item not found in cart'
        ];
    }

    /**
     * Get user's cart items
     */
    public static function getUserCartItems($userId)
    {
        $conn = getDbConnection();

        $sql = "SELECT c.*, i.name as item_name, i.asset_id, i.barcode, i.status as item_status,
                       cat.name as category_name
                FROM cart_items c 
                JOIN items i ON c.item_id = i.id 
                LEFT JOIN categories cat ON i.category_id = cat.id
                WHERE c.user_id = ? 
                ORDER BY c.added_at DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    /**
     * Clear user's cart
     */
    public static function clearUserCart($userId)
    {
        $conn = getDbConnection();

        $sql = "DELETE FROM cart_items WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId]);

        $rowCount = $stmt->rowCount();

        return [
            'success' => true,
            'message' => "$rowCount items removed from cart",
            'deleted_count' => $rowCount
        ];
    }

    /**
     * Create borrow request from user's cart
     */
    public static function createRequestFromCart($userId, $requestData)
    {
        $conn = getDbConnection();

        // Get cart items
        $cartItems = self::getUserCartItems($userId);

        if (empty($cartItems)) {
            return [
                'success' => false,
                'message' => 'Cart is empty'
            ];
        }

        // Prepare items for borrow request
        $items = [];
        foreach ($cartItems as $cartItem) {
            $items[] = [
                'item_id' => $cartItem['item_id'],
                'quantity' => $cartItem['quantity'],
                'condition_before' => 'good'
            ];
        }

        // Set flag to clear cart after creating request
        $requestData['clear_cart'] = true;
        $requestData['user_id'] = $userId;

        // Create borrow request
        return self::createBorrowRequest($requestData, $items);
    }

    /**
     * Get pending borrow requests for approval
     */
    public static function getPendingRequests($page = 1, $limit = ITEMS_PER_PAGE)
    {
        $filters = ['status' => 'pending'];
        return self::getAllBorrowRequests($page, $limit, $filters);
    }

    /**
     * Create a reservation for an item
     */
    public static function createReservation($reservationData)
    {
        $conn = getDbConnection();

        // Check if item exists and can be reserved
        $itemSql = "SELECT id, status FROM items WHERE id = ?";
        $itemStmt = $conn->prepare($itemSql);
        $itemStmt->execute([$reservationData['item_id']]);
        $item = $itemStmt->fetch();

        if (!$item) {
            return [
                'success' => false,
                'message' => 'Item not found'
            ];
        }

        // Check for conflicting reservations
        $conflictSql = "SELECT id FROM reservations 
                        WHERE item_id = ? 
                        AND status IN ('pending', 'confirmed') 
                        AND (
                            (start_date <= ? AND end_date >= ?) OR 
                            (start_date <= ? AND end_date >= ?) OR 
                            (start_date >= ? AND end_date <= ?)
                        )";

        $conflictStmt = $conn->prepare($conflictSql);
        $conflictStmt->execute([
            $reservationData['item_id'],
            $reservationData['start_date'],
            $reservationData['start_date'],
            $reservationData['end_date'],
            $reservationData['end_date'],
            $reservationData['start_date'],
            $reservationData['end_date']
        ]);

        if ($conflictStmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Item is already reserved for part or all of the selected period'
            ];
        }

        // Insert reservation
        $sql = "INSERT INTO reservations (user_id, item_id, start_date, end_date, purpose, status, notes, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            $reservationData['user_id'],
            $reservationData['item_id'],
            $reservationData['start_date'],
            $reservationData['end_date'],
            $reservationData['purpose'] ?? null,
            'pending',
            $reservationData['notes'] ?? null
        ]);

        if (!$result) {
            return [
                'success' => false,
                'message' => 'Failed to create reservation'
            ];
        }

        $reservationId = $conn->lastInsertId();

        // Log activity
        UtilityHelper::logActivity(
            $reservationData['user_id'],
            'create_reservation',
            'reservations',
            $reservationId,
            'Reservation created for item ' . $reservationData['item_id']
        );

        return [
            'success' => true,
            'message' => 'Reservation created successfully',
            'reservation_id' => $reservationId
        ];
    }

    /**
     * Get user's reservations
     */
    public static function getUserReservations($userId, $includeCompleted = false)
    {
        $conn = getDbConnection();

        $sql = "SELECT r.*, i.name as item_name, i.asset_id, i.barcode, i.status as item_status,
                       cat.name as category_name
                FROM reservations r 
                JOIN items i ON r.item_id = i.id 
                LEFT JOIN categories cat ON i.category_id = cat.id
                WHERE r.user_id = ?";

        if (!$includeCompleted) {
            $sql .= " AND r.status IN ('pending', 'confirmed')";
        }

        $sql .= " ORDER BY r.start_date ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }
}
