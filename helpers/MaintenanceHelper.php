<?php
class MaintenanceHelper {
    /**
     * Get maintenance record by ID
     */
    public static function getMaintenanceById($maintenanceId) {
        $conn = getDbConnection();
        
        $sql = "SELECT m.*, i.name as item_name, i.asset_id, i.barcode, 
                       r.full_name as requested_by_name, 
                       a.full_name as assigned_to_name 
                FROM maintenance_records m 
                LEFT JOIN items i ON m.item_id = i.id 
                LEFT JOIN users r ON m.requested_by = r.id 
                LEFT JOIN users a ON m.assigned_to = a.id 
                WHERE m.id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$maintenanceId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Get all maintenance records with pagination and filters
     */
    public static function getAllMaintenanceRecords($page = 1, $limit = ITEMS_PER_PAGE, $filters = []) {
        $conn = getDbConnection();
        
        // Build query
        $sql = "SELECT m.*, i.name as item_name, i.asset_id, i.barcode, 
                       r.full_name as requested_by_name, 
                       a.full_name as assigned_to_name 
                FROM maintenance_records m 
                LEFT JOIN items i ON m.item_id = i.id 
                LEFT JOIN users r ON m.requested_by = r.id 
                LEFT JOIN users a ON m.assigned_to = a.id";
        
        $where = [];
        $params = [];
        
        // Apply filters
        if (!empty($filters['search'])) {
            $where[] = "(i.name LIKE ? OR i.asset_id LIKE ? OR i.barcode LIKE ? OR m.description LIKE ?)";
            $searchParam = '%' . $filters['search'] . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if (!empty($filters['maintenance_type'])) {
            $where[] = "m.maintenance_type = ?";
            $params[] = $filters['maintenance_type'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = "m.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['requested_by'])) {
            $where[] = "m.requested_by = ?";
            $params[] = $filters['requested_by'];
        }
        
        if (!empty($filters['assigned_to'])) {
            $where[] = "m.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "m.start_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "m.end_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Combine WHERE clauses
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        // Count total records for pagination
        $countSql = str_replace("m.*, i.name as item_name, i.asset_id, i.barcode, 
                       r.full_name as requested_by_name, 
                       a.full_name as assigned_to_name", "COUNT(*) as total", $sql);
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute($params);
        $totalItems = (int)$countStmt->fetchColumn();
        
        // Add order and limit
        $sql .= " ORDER BY m.created_at DESC";
        
        // Calculate pagination
        $pagination = UtilityHelper::paginate($totalItems, $page, $limit);
        $sql .= " LIMIT " . $pagination['offset'] . ", " . $pagination['itemsPerPage'];
        
        // Execute query
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll();
        
        return [
            'records' => $records,
            'pagination' => $pagination
        ];
    }
    
    /**
     * Create a new maintenance record
     */
    public static function createMaintenanceRecord($data) {
        $conn = getDbConnection();
        
        // Check if item exists
        $itemSql = "SELECT id FROM items WHERE id = ?";
        $itemStmt = $conn->prepare($itemSql);
        $itemStmt->execute([$data['item_id']]);
        
        if (!$itemStmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Item not found'
            ];
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        try {
            // Insert new maintenance record
            $sql = "INSERT INTO maintenance_records (item_id, maintenance_type, description, requested_by, 
                                                assigned_to, status, estimated_cost, actual_cost, parts_used, 
                                                start_date, end_date, resolution, notes, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([
                $data['item_id'],
                $data['maintenance_type'],
                $data['description'],
                $data['requested_by'],
                $data['assigned_to'] ?? null,
                $data['status'] ?? 'requested',
                $data['estimated_cost'] ?? null,
                $data['actual_cost'] ?? null,
                $data['parts_used'] ?? null,
                $data['start_date'] ?? null,
                $data['end_date'] ?? null,
                $data['resolution'] ?? null,
                $data['notes'] ?? null
            ]);
            
            if (!$result) {
                throw new Exception('Failed to create maintenance record');
            }
            
            $maintenanceId = $conn->lastInsertId();
            
            // Update item status if needed
            if (isset($data['update_item_status']) && $data['update_item_status']) {
                $updateItemSql = "UPDATE items SET status = 'maintenance' WHERE id = ?";
                $updateItemStmt = $conn->prepare($updateItemSql);
                $updateItemResult = $updateItemStmt->execute([$data['item_id']]);
                
                if (!$updateItemResult) {
                    throw new Exception('Failed to update item status');
                }
                
                // Log inventory transaction
                $transactionData = [
                    'item_id' => $data['item_id'],
                    'transaction_type' => 'maintenance',
                    'quantity' => 1,
                    'related_record_type' => 'maintenance_records',
                    'related_record_id' => $maintenanceId,
                    'notes' => 'Item placed in maintenance: ' . $data['description']
                ];
                InventoryHelper::logInventoryTransaction($transactionData);
            }
            
            // Log activity
            UtilityHelper::logActivity(
                $data['requested_by'],
                'create_maintenance', 
                'maintenance_records', 
                $maintenanceId, 
                'Maintenance record created for item ID: ' . $data['item_id']
            );
            
            // Commit transaction
            $conn->commit();
            
            return [
                'success' => true,
                'message' => 'Maintenance record created successfully',
                'maintenance_id' => $maintenanceId
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to create maintenance record: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update maintenance record
     */
    public static function updateMaintenanceRecord($maintenanceId, $data) {
        $conn = getDbConnection();
        
        // Check if record exists
        $checkSql = "SELECT id, item_id, status FROM maintenance_records WHERE id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute([$maintenanceId]);
        $record = $checkStmt->fetch();
        
        if (!$record) {
            return [
                'success' => false,
                'message' => 'Maintenance record not found'
            ];
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        try {
            // Build update query
            $updateFields = [];
            $params = [];
            
            $possibleFields = [
                'maintenance_type', 'description', 'assigned_to', 'status', 
                'estimated_cost', 'actual_cost', 'parts_used', 'start_date', 
                'end_date', 'resolution', 'notes'
            ];
            
            foreach ($possibleFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
            
            // Add updated_at
            $updateFields[] = "updated_at = NOW()";
            
            // Add maintenance ID
            $params[] = $maintenanceId;
            
            // Execute update
            $sql = "UPDATE maintenance_records SET " . implode(", ", $updateFields) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute($params);
            
            if (!$result) {
                throw new Exception('Failed to update maintenance record');
            }
            
            // Handle status changes that affect the item
            if (isset($data['status'])) {
                $oldStatus = $record['status'];
                $newStatus = $data['status'];
                $itemId = $record['item_id'];
                
                // If completed, update item status back to available
                if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                    $updateItemSql = "UPDATE items SET status = 'available' WHERE id = ? AND status = 'maintenance'";
                    $updateItemStmt = $conn->prepare($updateItemSql);
                    $updateItemStmt->execute([$itemId]);
                    
                    // Log inventory transaction
                    $transactionData = [
                        'item_id' => $itemId,
                        'transaction_type' => 'adjust',
                        'quantity' => 1,
                        'related_record_type' => 'maintenance_records',
                        'related_record_id' => $maintenanceId,
                        'notes' => 'Maintenance completed. Item returned to available status.'
                    ];
                    InventoryHelper::logInventoryTransaction($transactionData);
                }
                // If changed to in_progress, update item status to maintenance if not already
                elseif ($newStatus === 'in_progress' && $oldStatus !== 'in_progress') {
                    $updateItemSql = "UPDATE items SET status = 'maintenance' WHERE id = ? AND status != 'maintenance'";
                    $updateItemStmt = $conn->prepare($updateItemSql);
                    $updateItemStmt->execute([$itemId]);
                    
                    // Log inventory transaction if status changed
                    if ($updateItemStmt->rowCount() > 0) {
                        $transactionData = [
                            'item_id' => $itemId,
                            'transaction_type' => 'maintenance',
                            'quantity' => 1,
                            'related_record_type' => 'maintenance_records',
                            'related_record_id' => $maintenanceId,
                            'notes' => 'Maintenance in progress. Item status updated.'
                        ];
                        InventoryHelper::logInventoryTransaction($transactionData);
                    }
                }
            }
            
            // Log activity
            UtilityHelper::logActivity(
                $_SESSION['user_id'] ?? null,
                'update_maintenance', 
                'maintenance_records', 
                $maintenanceId, 
                'Maintenance record updated'
            );
            
            // Commit transaction
            $conn->commit();
            
            return [
                'success' => true,
                'message' => 'Maintenance record updated successfully'
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to update maintenance record: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete maintenance record
     */
    public static function deleteMaintenanceRecord($maintenanceId) {
        $conn = getDbConnection();
        
        // Check if record exists
        $checkSql = "SELECT id, item_id, status FROM maintenance_records WHERE id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute([$maintenanceId]);
        $record = $checkStmt->fetch();
        
        if (!$record) {
            return [
                'success' => false,
                'message' => 'Maintenance record not found'
            ];
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        try {
            // Delete record
            $sql = "DELETE FROM maintenance_records WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([$maintenanceId]);
            
            if (!$result) {
                throw new Exception('Failed to delete maintenance record');
            }
            
            // Log activity
            UtilityHelper::logActivity(
                $_SESSION['user_id'] ?? null,
                'delete_maintenance', 
                'maintenance_records', 
                $maintenanceId, 
                'Maintenance record deleted'
            );
            
            // Commit transaction
            $conn->commit();
            
            return [
                'success' => true,
                'message' => 'Maintenance record deleted successfully'
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to delete maintenance record: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Complete maintenance record
     */
    public static function completeMaintenanceRecord($maintenanceId, $data) {
        $conn = getDbConnection();
        
        // Check if record exists
        $checkSql = "SELECT id, item_id, status FROM maintenance_records WHERE id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute([$maintenanceId]);
        $record = $checkStmt->fetch();
        
        if (!$record) {
            return [
                'success' => false,
                'message' => 'Maintenance record not found'
            ];
        }
        
        if ($record['status'] === 'completed') {
            return [
                'success' => false,
                'message' => 'Maintenance record is already completed'
            ];
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        try {
            // Update maintenance record
            $sql = "UPDATE maintenance_records SET 
                    status = 'completed', 
                    actual_cost = ?, 
                    parts_used = ?, 
                    end_date = ?, 
                    resolution = ?, 
                    notes = ?, 
                    updated_at = NOW() 
                    WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([
                $data['actual_cost'] ?? null,
                $data['parts_used'] ?? null,
                $data['end_date'] ?? date('Y-m-d'),
                $data['resolution'] ?? null,
                $data['notes'] ?? null,
                $maintenanceId
            ]);
            
            if (!$result) {
                throw new Exception('Failed to complete maintenance record');
            }
            
            // Update item status if needed
            if (isset($data['update_item_condition']) && $data['update_item_condition']) {
                $updateItemSql = "UPDATE items SET 
                                 status = 'available',
                                 condition_rating = ?,
                                 notes = CONCAT(IFNULL(notes, ''), '\n[', NOW(), '] Maintenance completed: ', ?)
                                 WHERE id = ?";
                $updateItemStmt = $conn->prepare($updateItemSql);
                $updateItemResult = $updateItemStmt->execute([
                    $data['item_condition'] ?? 'good',
                    $data['resolution'] ?? 'Maintenance completed',
                    $record['item_id']
                ]);
                
                if (!$updateItemResult) {
                    throw new Exception('Failed to update item status');
                }
            } else {
                // Just update item status to available
                $updateItemSql = "UPDATE items SET status = 'available' WHERE id = ? AND status = 'maintenance'";
                $updateItemStmt = $conn->prepare($updateItemSql);
                $updateItemStmt->execute([$record['item_id']]);
            }
            
            // Log inventory transaction
            $transactionData = [
                'item_id' => $record['item_id'],
                'transaction_type' => 'adjust',
                'quantity' => 1,
                'related_record_type' => 'maintenance_records',
                'related_record_id' => $maintenanceId,
                'notes' => 'Maintenance completed: ' . ($data['resolution'] ?? 'No details provided')
            ];
            InventoryHelper::logInventoryTransaction($transactionData);
            
            // Schedule next maintenance if interval is set
            if (isset($data['schedule_next']) && $data['schedule_next']) {
                $item = InventoryHelper::getItemById($record['item_id']);
                
                if ($item && !empty($item['maintenance_interval'])) {
                    $interval = (int)$item['maintenance_interval'];
                    $nextDate = date('Y-m-d', strtotime("+{$interval} days"));
                    
                    // Update item's next maintenance date
                    $updateNextSql = "UPDATE items SET next_maintenance_date = ? WHERE id = ?";
                    $updateNextStmt = $conn->prepare($updateNextSql);
                    $updateNextStmt->execute([$nextDate, $record['item_id']]);
                }
            }
            
            // Log activity
            UtilityHelper::logActivity(
                $_SESSION['user_id'] ?? null,
                'complete_maintenance', 
                'maintenance_records', 
                $maintenanceId, 
                'Maintenance record completed'
            );
            
            // Commit transaction
            $conn->commit();
            
            return [
                'success' => true,
                'message' => 'Maintenance record completed successfully'
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to complete maintenance record: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all maintenance records for an item
     */
    public static function getItemMaintenanceHistory($itemId, $page = 1, $limit = ITEMS_PER_PAGE) {
        $filters = ['item_id' => $itemId];
        return self::getAllMaintenanceRecords($page, $limit, $filters);
    }
    
    /**
     * Get upcoming maintenance based on next_maintenance_date in items table
     */
    public static function getUpcomingMaintenance($days = 30, $page = 1, $limit = ITEMS_PER_PAGE) {
        $conn = getDbConnection();
        
        // Calculate the date range
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime("+{$days} days"));
        
        // Build query to get items with upcoming maintenance
        $sql = "SELECT i.*, c.name as category_name, l.name as location_name 
                FROM items i 
                LEFT JOIN categories c ON i.category_id = c.id 
                LEFT JOIN locations l ON i.location_id = l.id 
                WHERE i.is_active = 1 
                AND i.next_maintenance_date IS NOT NULL 
                AND i.next_maintenance_date BETWEEN ? AND ?
                ORDER BY i.next_maintenance_date ASC";
        
        // Count total records for pagination
        $countSql = str_replace("i.*, c.name as category_name, l.name as location_name", "COUNT(*) as total", $sql);
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute([$startDate, $endDate]);
        $totalItems = (int)$countStmt->fetchColumn();
        
        // Calculate pagination
        $pagination = UtilityHelper::paginate($totalItems, $page, $limit);
        $sql .= " LIMIT " . $pagination['offset'] . ", " . $pagination['itemsPerPage'];
        
        // Execute query
        $stmt = $conn->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        $items = $stmt->fetchAll();
        
        return [
            'items' => $items,
            'pagination' => $pagination
        ];
    }
    
    /**
     * Get maintenance statistics
     */
    public static function getMaintenanceStatistics() {
        $conn = getDbConnection();
        
        // Count by status
        $statusSql = "SELECT status, COUNT(*) as count 
                     FROM maintenance_records 
                     GROUP BY status";
        $statusStmt = $conn->prepare($statusSql);
        $statusStmt->execute();
        $statusCounts = [];
        
        while ($row = $statusStmt->fetch()) {
            $statusCounts[$row['status']] = (int)$row['count'];
        }
        
        // Count by type
        $typeSql = "SELECT maintenance_type, COUNT(*) as count 
                   FROM maintenance_records 
                   GROUP BY maintenance_type";
        $typeStmt = $conn->prepare($typeSql);
        $typeStmt->execute();
        $typeCounts = [];
        
        while ($row = $typeStmt->fetch()) {
            $typeCounts[$row['maintenance_type']] = (int)$row['count'];
        }
        
        // Get total costs
        $costSql = "SELECT SUM(actual_cost) as total_cost 
                   FROM maintenance_records 
                   WHERE status = 'completed'";
        $costStmt = $conn->prepare($costSql);
        $costStmt->execute();
        $totalCost = (float)$costStmt->fetchColumn();
        
        // Get recent activities
        $recentSql = "SELECT m.*, i.name as item_name 
                     FROM maintenance_records m 
                     JOIN items i ON m.item_id = i.id 
                     ORDER BY m.created_at DESC 
                     LIMIT 5";
        $recentStmt = $conn->prepare($recentSql);
        $recentStmt->execute();
        $recentActivities = $recentStmt->fetchAll();
        
        // Count upcoming maintenance
        $upcomingSql = "SELECT COUNT(*) as count 
                       FROM items 
                       WHERE next_maintenance_date IS NOT NULL 
                       AND next_maintenance_date BETWEEN CURRENT_DATE() 
                       AND DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY)";
        $upcomingStmt = $conn->prepare($upcomingSql);
        $upcomingStmt->execute();
        $upcomingCount = (int)$upcomingStmt->fetchColumn();
        
        return [
            'status_counts' => $statusCounts,
            'type_counts' => $typeCounts,
            'total_cost' => $totalCost,
            'recent_activities' => $recentActivities,
            'upcoming_count' => $upcomingCount
        ];
    }
    
    /**
     * Get all damage reports with pagination and filters
     */
    public static function getAllDamageReports($page = 1, $limit = ITEMS_PER_PAGE, $filters = []) {
        $conn = getDbConnection();
        
        // Build query
        $sql = "SELECT d.*, i.name as item_name, i.asset_id, i.barcode, 
                       u.full_name as reported_by_name, 
                       m.id as maintenance_record_id 
                FROM damage_reports d 
                LEFT JOIN items i ON d.item_id = i.id 
                LEFT JOIN users u ON d.reported_by = u.id 
                LEFT JOIN maintenance_records m ON d.maintenance_record_id = m.id";
        
        $where = [];
        $params = [];
        
        // Apply filters
        if (!empty($filters['search'])) {
            $where[] = "(i.name LIKE ? OR i.asset_id LIKE ? OR i.barcode LIKE ? OR d.description LIKE ?)";
            $searchParam = '%' . $filters['search'] . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if (!empty($filters['severity'])) {
            $where[] = "d.severity = ?";
            $params[] = $filters['severity'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = "d.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['reported_by'])) {
            $where[] = "d.reported_by = ?";
            $params[] = $filters['reported_by'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "d.damage_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "d.damage_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Combine WHERE clauses
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        // Count total records for pagination
        $countSql = str_replace("d.*, i.name as item_name, i.asset_id, i.barcode, 
                       u.full_name as reported_by_name, 
                       m.id as maintenance_record_id", "COUNT(*) as total", $sql);
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute($params);
        $totalItems = (int)$countStmt->fetchColumn();
        
        // Add order and limit
        $sql .= " ORDER BY d.created_at DESC";
        
        // Calculate pagination
        $pagination = UtilityHelper::paginate($totalItems, $page, $limit);
        $sql .= " LIMIT " . $pagination['offset'] . ", " . $pagination['itemsPerPage'];
        
        // Execute query
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $reports = $stmt->fetchAll();
        
        // Get images for each report
        foreach ($reports as &$report) {
            $report['images'] = self::getDamageImages($report['id']);
        }
        
        return [
            'reports' => $reports,
            'pagination' => $pagination
        ];
    }
    
    /**
     * Get damage report by ID
     */
    public static function getDamageReportById($reportId) {
        $conn = getDbConnection();
        
        $sql = "SELECT d.*, i.name as item_name, i.asset_id, i.barcode, 
                       u.full_name as reported_by_name, 
                       m.id as maintenance_record_id 
                FROM damage_reports d 
                LEFT JOIN items i ON d.item_id = i.id 
                LEFT JOIN users u ON d.reported_by = u.id 
                LEFT JOIN maintenance_records m ON d.maintenance_record_id = m.id 
                WHERE d.id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$reportId]);
        
        $report = $stmt->fetch();
        
        if ($report) {
            // Get images
            $report['images'] = self::getDamageImages($report['id']);
            
            // Get associated maintenance record if exists
            if ($report['maintenance_record_id']) {
                $report['maintenance'] = self::getMaintenanceById($report['maintenance_record_id']);
            }
        }
        
        return $report;
    }
    
    /**
     * Get damage images for a report
     */
    public static function getDamageImages($reportId) {
        $conn = getDbConnection();
        
        $sql = "SELECT * FROM damage_images WHERE damage_report_id = ? ORDER BY created_at ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$reportId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Create a new damage report
     */
    public static function createDamageReport($data) {
        $conn = getDbConnection();
        
        // Check if item exists
        $itemSql = "SELECT id FROM items WHERE id = ?";
        $itemStmt = $conn->prepare($itemSql);
        $itemStmt->execute([$data['item_id']]);
        
        if (!$itemStmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Item not found'
            ];
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        try {
            // Insert new damage report
            $sql = "INSERT INTO damage_reports (item_id, reported_by, damage_date, severity, 
                                            description, status, notes, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([
                $data['item_id'],
                $data['reported_by'],
                $data['damage_date'] ?? date('Y-m-d'),
                $data['severity'],
                $data['description'],
                $data['status'] ?? 'open',
                $data['notes'] ?? null
            ]);
            
            if (!$result) {
                throw new Exception('Failed to create damage report');
            }
            
            $reportId = $conn->lastInsertId();
            
            // Handle image uploads if provided
            if (!empty($data['images'])) {
                $uploadDir = UPLOAD_DIR . '/damage_reports/' . $reportId;
                
                foreach ($data['images'] as $image) {
                    $uploadResult = UtilityHelper::uploadFile($image, $uploadDir, ALLOWED_IMAGE_TYPES);
                    
                    if ($uploadResult['success']) {
                        // Insert image record
                        $imageSql = "INSERT INTO damage_images (damage_report_id, file_path, file_name, file_size, file_type) 
                                    VALUES (?, ?, ?, ?, ?)";
                        
                        $imageStmt = $conn->prepare($imageSql);
                        $imageStmt->execute([
                            $reportId,
                            $uploadResult['path'],
                            $uploadResult['filename'],
                            $image['size'] ?? null,
                            $image['type'] ?? null
                        ]);
                    }
                }
            }
            
            // Create maintenance request if requested
            if (isset($data['create_maintenance']) && $data['create_maintenance']) {
                $maintenanceData = [
                    'item_id' => $data['item_id'],
                    'maintenance_type' => 'repair',
                    'description' => 'Repair for damage: ' . $data['description'],
                    'requested_by' => $data['reported_by'],
                    'status' => 'requested',
                    'notes' => $data['notes'] ?? null,
                    'update_item_status' => false // Don't update yet, only when maintenance starts
                ];
                
                $maintenanceResult = self::createMaintenanceRecord($maintenanceData);
                
                if ($maintenanceResult['success']) {
                    // Link damage report to maintenance record
                    $linkSql = "UPDATE damage_reports SET maintenance_record_id = ?, status = 'pending_maintenance' WHERE id = ?";
                    $linkStmt = $conn->prepare($linkSql);
                    $linkStmt->execute([
                        $maintenanceResult['maintenance_id'],
                        $reportId
                    ]);
                }
            }
            
            // Log activity
            UtilityHelper::logActivity(
                $data['reported_by'],
                'create_damage_report', 
                'damage_reports', 
                $reportId, 
                'Damage report created for item ID: ' . $data['item_id']
            );
            
            // Commit transaction
            $conn->commit();
            
            return [
                'success' => true,
                'message' => 'Damage report created successfully',
                'report_id' => $reportId
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to create damage report: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update damage report
     */
    public static function updateDamageReport($reportId, $data) {
        $conn = getDbConnection();
        
        // Check if report exists
        $checkSql = "SELECT id, item_id, status, maintenance_record_id FROM damage_reports WHERE id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute([$reportId]);
        $report = $checkStmt->fetch();
        
        if (!$report) {
            return [
                'success' => false,
                'message' => 'Damage report not found'
            ];
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        try {
            // Build update query
            $updateFields = [];
            $params = [];
            
            $possibleFields = [
                'severity', 'description', 'status', 'resolution', 
                'resolution_date', 'notes'
            ];
            
            foreach ($possibleFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
            
            // Add updated_at
            $updateFields[] = "updated_at = NOW()";
            
            // Add report ID
            $params[] = $reportId;
            
            // Execute update
            $sql = "UPDATE damage_reports SET " . implode(", ", $updateFields) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute($params);
            
            if (!$result) {
                throw new Exception('Failed to update damage report');
            }
            
            // Handle status changes
            if (isset($data['status'])) {
                $oldStatus = $report['status'];
                $newStatus = $data['status'];
                
                // If resolved and no maintenance record exists
                if ($newStatus === 'resolved' && $oldStatus !== 'resolved' && !$report['maintenance_record_id']) {
                    // Mark damage in item notes
                    $updateItemSql = "UPDATE items SET 
                                     notes = CONCAT(IFNULL(notes, ''), '\n[', NOW(), '] Damage resolved: ', ?)
                                     WHERE id = ?";
                    $updateItemStmt = $conn->prepare($updateItemSql);
                    $updateItemStmt->execute([
                        $data['resolution'] ?? 'Damage resolved',
                        $report['item_id']
                    ]);
                }
                
                // If status changed to pending_maintenance and no maintenance record linked
                if ($newStatus === 'pending_maintenance' && $oldStatus !== 'pending_maintenance' && 
                    !$report['maintenance_record_id'] && isset($data['create_maintenance']) && $data['create_maintenance']) {
                    
                    $maintenanceData = [
                        'item_id' => $report['item_id'],
                        'maintenance_type' => 'repair',
                        'description' => 'Repair for damage report #' . $reportId,
                        'requested_by' => $_SESSION['user_id'] ?? null,
                        'status' => 'requested',
                        'notes' => $data['notes'] ?? null,
                        'update_item_status' => false
                    ];
                    
                    $maintenanceResult = self::createMaintenanceRecord($maintenanceData);
                    
                    if ($maintenanceResult['success']) {
                        // Link damage report to maintenance record
                        $linkSql = "UPDATE damage_reports SET maintenance_record_id = ? WHERE id = ?";
                        $linkStmt = $conn->prepare($linkSql);
                        $linkStmt->execute([
                            $maintenanceResult['maintenance_id'],
                            $reportId
                        ]);
                    }
                }
            }
            
            // Handle maintenance record linking
            if (isset($data['maintenance_record_id']) && !$report['maintenance_record_id']) {
                $linkSql = "UPDATE damage_reports SET maintenance_record_id = ?, status = 'pending_maintenance' WHERE id = ?";
                $linkStmt = $conn->prepare($linkSql);
                $linkStmt->execute([
                    $data['maintenance_record_id'],
                    $reportId
                ]);
            }
            
            // Handle image uploads if provided
            if (!empty($data['images'])) {
                $uploadDir = UPLOAD_DIR . '/damage_reports/' . $reportId;
                
                foreach ($data['images'] as $image) {
                    $uploadResult = UtilityHelper::uploadFile($image, $uploadDir, ALLOWED_IMAGE_TYPES);
                    
                    if ($uploadResult['success']) {
                        // Insert image record
                        $imageSql = "INSERT INTO damage_images (damage_report_id, file_path, file_name, file_size, file_type) 
                                    VALUES (?, ?, ?, ?, ?)";
                        
                        $imageStmt = $conn->prepare($imageSql);
                        $imageStmt->execute([
                            $reportId,
                            $uploadResult['path'],
                            $uploadResult['filename'],
                            $image['size'] ?? null,
                            $image['type'] ?? null
                        ]);
                    }
                }
            }
            
            // Log activity
            UtilityHelper::logActivity(
                $_SESSION['user_id'] ?? null,
                'update_damage_report', 
                'damage_reports', 
                $reportId, 
                'Damage report updated'
            );
            
            // Commit transaction
            $conn->commit();
            
            return [
                'success' => true,
                'message' => 'Damage report updated successfully'
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to update damage report: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete damage report
     */
    public static function deleteDamageReport($reportId) {
        $conn = getDbConnection();
        
        // Check if report exists
        $checkSql = "SELECT id, item_id FROM damage_reports WHERE id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute([$reportId]);
        $report = $checkStmt->fetch();
        
        if (!$report) {
            return [
                'success' => false,
                'message' => 'Damage report not found'
            ];
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        try {
            // Get images to delete files
            $imagesSql = "SELECT file_path FROM damage_images WHERE damage_report_id = ?";
            $imagesStmt = $conn->prepare($imagesSql);
            $imagesStmt->execute([$reportId]);
            $images = $imagesStmt->fetchAll();
            
            // Delete image files
            foreach ($images as $image) {
                if (file_exists($image['file_path'])) {
                    unlink($image['file_path']);
                }
            }
            
            // Delete image records
            $deleteImagesSql = "DELETE FROM damage_images WHERE damage_report_id = ?";
            $deleteImagesStmt = $conn->prepare($deleteImagesSql);
            $deleteImagesStmt->execute([$reportId]);
            
            // Delete damage report
            $sql = "DELETE FROM damage_reports WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([$reportId]);
            
            if (!$result) {
                throw new Exception('Failed to delete damage report');
            }
            
            // Log activity
            UtilityHelper::logActivity(
                $_SESSION['user_id'] ?? null,
                'delete_damage_report', 
                'damage_reports', 
                $reportId, 
                'Damage report deleted'
            );
            
            // Commit transaction
            $conn->commit();
            
            return [
                'success' => true,
                'message' => 'Damage report deleted successfully'
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to delete damage report: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get damage reports for an item
     */
    public static function getItemDamageReports($itemId, $page = 1, $limit = ITEMS_PER_PAGE) {
        $filters = ['item_id' => $itemId];
        return self::getAllDamageReports($page, $limit, $filters);
    }
}