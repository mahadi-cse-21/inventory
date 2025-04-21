<?php
class BorrowHelper {
    /**
     * Get borrow request by ID
     */
    public static function getBorrowRequestById($requestId) {
        $conn = getDbConnection();
        
        $sql = "SELECT br.*, u.full_name as requester_name, u.email as requester_email, 
                       d.name as department_name, a.full_name as approver_name,
                       c.full_name as checkout_by_name, r.full_name as returned_by_name
                FROM borrow_requests br 
                LEFT JOIN users u ON br.user_id = u.id 
                LEFT JOIN departments d ON br.department_id = d.id 
                LEFT JOIN users a ON br.approved_by = a.id
                LEFT JOIN users c ON br.checked_out_by = c.id
                LEFT JOIN users r ON br.returned_by = r.id
                WHERE br.id = ? OR br.request_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$requestId, $requestId]);
        
        $request = $stmt->fetch();
        
        if ($request) {
            // Get borrowed items
            $request['items'] = self::getBorrowedItems($request['id']);
        }
        
        return $request;
    }
    
    /**
     * Get borrowed items for a borrow request
     */
    public static function getBorrowedItems($borrowRequestId) {
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
    public static function getAllBorrowRequests($page = 1, $limit = ITEMS_PER_PAGE, $filters = []) {
        $conn = getDbConnection();
        
        // Build query
        $sql = "SELECT br.*, u.full_name as requester_name, d.name as department_name 
                FROM borrow_requests br 
                LEFT JOIN users u ON br.user_id = u.id 
                LEFT JOIN departments d ON br.department_id = d.id";
        
        $where = [];
        $params = [];
        
        // Apply filters
        if (!empty($filters['search'])) {
            $where[] = "(br.request_id LIKE ? OR u.full_name LIKE ? OR br.purpose LIKE ? OR br.project_name LIKE ?)";
            $searchParam = '%' . $filters['search'] . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if (!empty($filters['user_id'])) {
            $where[] = "br.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['department_id'])) {
            $where[] = "br.department_id = ?";
            $params[] = $filters['department_id'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = "br.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['borrow_date_from'])) {
            $where[] = "br.borrow_date >= ?";
            $params[] = $filters['borrow_date_from'];
        }
        
        if (!empty($filters['borrow_date_to'])) {
            $where[] = "br.borrow_date <= ?";
            $params[] = $filters['borrow_date_to'];
        }
        
        if (!empty($filters['return_date_from'])) {
            $where[] = "br.return_date >= ?";
            $params[] = $filters['return_date_from'];
        }
        
        if (!empty($filters['return_date_to'])) {
            $where[] = "br.return_date <= ?";
            $params[] = $filters['return_date_to'];
        }
        
        // Filter by item if needed
        if (!empty($filters['item_id'])) {
            $sql .= " INNER JOIN borrowed_items bi ON br.id = bi.borrow_request_id";
            $where[] = "bi.item_id = ?";
            $params[] = $filters['item_id'];
        }
        
        // Combine WHERE clauses
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        // Count total records for pagination
        $countSql = str_replace("br.*, u.full_name as requester_name, d.name as department_name", 
                                "COUNT(DISTINCT br.id) as total", $sql);
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute($params);
        $totalItems = (int)$countStmt->fetchColumn();
        
        // Add order and limit
        $sql .= " GROUP BY br.id ORDER BY br.created_at DESC";
        
        // Calculate pagination
        $pagination = UtilityHelper::paginate($totalItems, $page, $limit);
        $sql .= " LIMIT " . $pagination['offset'] . ", " . $pagination['itemsPerPage'];
        
        // Execute query
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $requests = $stmt->fetchAll();
        
        return [
            'requests' => $requests,
            'pagination' => $pagination
        ];
    }
    
    /**
     * Create a new borrow request
     */
    public static function createBorrowRequest($requestData, $items) {
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
    public static function updateBorrowRequestStatus($requestId, $status, $userId, $notes = null) {
        $conn = getDbConnection();
        
        // Check if request exists
        $request = self::getBorrowRequestById($requestId);
        if (!$request) {
            return [
                'success' => false,
                'message' => 'Borrow request not found'
            ];
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        try {
            // Update request status
            $updateFields = [
                'status = ?',
                'updated_at = NOW()'
            ];
            $params = [$status];
            
            // Add action-specific fields
            switch ($status) {
                case 'approved':
                    $updateFields[] = 'approved_by = ?';
                    $updateFields[] = 'approved_at = NOW()';
                    $params[] = $userId;
                    $action = 'approve_borrow_request';
                    $logMessage = 'Borrow request approved';
                    break;
                    
                case 'rejected':
                    $updateFields[] = 'rejection_reason = ?';
                    $params[] = $notes;
                    $action = 'reject_borrow_request';
                    $logMessage = 'Borrow request rejected';
                    break;
                    
                case 'cancelled':
                    $action = 'cancel_borrow_request';
                    $logMessage = 'Borrow request cancelled';
                    break;
                    
                case 'checked_out':
                    $updateFields[] = 'checked_out_by = ?';
                    $updateFields[] = 'checked_out_at = NOW()';
                    $params[] = $userId;
                    $action = 'checkout_borrow_request';
                    $logMessage = 'Items checked out';
                    break;
                    
                case 'returned':
                    $updateFields[] = 'returned_by = ?';
                    $updateFields[] = 'returned_at = NOW()';
                    $params[] = $userId;
                    $action = 'return_borrow_request';
                    $logMessage = 'Items returned';
                    break;
                    
                default:
                    $action = 'update_borrow_request';
                    $logMessage = 'Borrow request updated';
            }
            
            // Add request ID
            $params[] = $request['id'];
            
            // Update request
            $sql = "UPDATE borrow_requests SET " . implode(", ", $updateFields) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute($params);
            
            if (!$result) {
                throw new Exception('Failed to update borrow request status');
            }
            
            // Update individual borrowed items if checked out or returned
            if ($status === 'checked_out' || $status === 'returned') {
                $itemStatus = ($status === 'checked_out') ? 'checked_out' : 'returned';
                $itemSql = "UPDATE borrowed_items SET status = ? WHERE borrow_request_id = ?";
                $itemStmt = $conn->prepare($itemSql);
                $itemStmt->execute([$itemStatus, $request['id']]);
                
                // Also update items table
                foreach ($request['items'] as $item) {
                    $itemTableStatus = ($status === 'checked_out') ? 'borrowed' : 'available';
                    $updateItemSql = "UPDATE items SET status = ? WHERE id = ?";
                    $updateItemStmt = $conn->prepare($updateItemSql);
                    $updateItemStmt->execute([$itemTableStatus, $item['item_id']]);
                    
                    // Log inventory transaction
                    $transactionType = ($status === 'checked_out') ? 'check_out' : 'check_in';
                    $transactionData = [
                        'item_id' => $item['item_id'],
                        'transaction_type' => $transactionType,
                        'quantity' => $item['quantity'],
                        'related_record_type' => 'borrow_requests',
                        'related_record_id' => $request['id'],
                        'notes' => $notes
                    ];
                    InventoryHelper::logInventoryTransaction($transactionData);
                }
            }
            
            // Log activity
            UtilityHelper::logActivity(
                $userId,
                $action, 
                'borrow_requests', 
                $request['id'], 
                $logMessage . ': ' . $request['request_id']
            );
            
            // Commit transaction
            $conn->commit();
            
            return [
                'success' => true,
                'message' => $logMessage . ' successfully'
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to update borrow request: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Return specific items from a borrow request
     */
    public static function returnBorrowedItems($borrowRequestId, $itemsData, $userId) {
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
                $updateItemSql = "UPDATE borrowed_items 
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
                    'transaction_type' => 'check_in',
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
                $updateRequestSql = "UPDATE borrow_requests 
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
    public static function getUserBorrowedItems($userId) {
        $conn = getDbConnection();
        
        $sql = "SELECT bi.*, i.name as item_name, i.asset_id, i.barcode, 
                       c.name as category_name, br.borrow_date, br.return_date, 
                       br.request_id, br.status as request_status 
                FROM borrowed_items bi 
                JOIN borrow_requests br ON bi.borrow_request_id = br.id 
                JOIN items i ON bi.item_id = i.id 
                LEFT JOIN categories c ON i.category_id = c.id 
                WHERE br.user_id = ? 
                AND (bi.status = 'checked_out' OR bi.status = 'pending') 
                AND (br.status = 'checked_out' OR br.status = 'approved' OR br.status = 'partially_returned')
                ORDER BY br.return_date ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get user's overdue items
     */
    public static function getUserOverdueItems($userId) {
        $conn = getDbConnection();
        
        $sql = "SELECT bi.*, i.name as item_name, i.asset_id, i.barcode, 
                       c.name as category_name, br.borrow_date, br.return_date, 
                       br.request_id, br.status as request_status,
                       DATEDIFF(CURRENT_DATE(), br.return_date) as days_overdue
                FROM borrowed_items bi 
                JOIN borrow_requests br ON bi.borrow_request_id = br.id 
                JOIN items i ON bi.item_id = i.id 
                LEFT JOIN categories c ON i.category_id = c.id 
                WHERE br.user_id = ? 
                AND bi.status = 'checked_out' 
                AND br.return_date < CURRENT_DATE()
                AND (br.status = 'checked_out' OR br.status = 'overdue')
                ORDER BY br.return_date ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Check for overdue items and update status
     */
    public static function checkForOverdueItems() {
        $conn = getDbConnection();
        
        // Find overdue requests
        $sql = "SELECT id, request_id, user_id 
                FROM borrow_requests 
                WHERE status = 'checked_out' 
                AND return_date < CURRENT_DATE()";
        
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
    public static function getBorrowStatistics() {
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
    private static function generateBorrowRequestId() {
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
    public static function addToCart($userId, $itemId, $quantity = 1, $borrowDate = null, $returnDate = null, $notes = null) {
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
    public static function removeFromCart($userId, $cartItemId) {
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
    public static function getUserCartItems($userId) {
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
    public static function clearUserCart($userId) {
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
    public static function createRequestFromCart($userId, $requestData) {
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
    public static function getPendingRequests($page = 1, $limit = ITEMS_PER_PAGE) {
        $filters = ['status' => 'pending'];
        return self::getAllBorrowRequests($page, $limit, $filters);
    }
    
    /**
     * Create a reservation for an item
     */
    public static function createReservation($reservationData) {
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
            $reservationData['start_date'], $reservationData['start_date'],
            $reservationData['end_date'], $reservationData['end_date'],
            $reservationData['start_date'], $reservationData['end_date']
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
    public static function getUserReservations($userId, $includeCompleted = false) {
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