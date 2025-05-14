<?php
class LocationHelper {
    /**
     * Get location by ID
     */
    public static function getLocationById($locationId) {
        $conn = getDbConnection();
        
        $sql = "SELECT l.*, u.full_name as manager_name 
                FROM locations l 
                LEFT JOIN users u ON l.manager_id = u.id 
                WHERE l.id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$locationId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Get all locations with pagination and filters
     */
 public static function getAllLocations($page = 1, $limit = ITEMS_PER_PAGE, $filters = []) {
    // Get the database connection
    $conn = getDbConnection();

    // Base SQL query to fetch locations
    $sql = "SELECT id, building_name, room FROM location WHERE 1"; // 'WHERE 1' is used as a placeholder for dynamic conditions
    
    // Apply filters if provided
    if (!empty($filters['building_name'])) {
        $sql .= " AND building_name LIKE :building_name";
    }
    if (!empty($filters['room'])) {
        $sql .= " AND room LIKE :room";
    }

    // Apply pagination
    $offset = ($page - 1) * $limit;
    $sql .= " LIMIT :offset, :limit";

    // Prepare the query
    $stmt = $conn->prepare($sql);

    // Bind the filters
    if (!empty($filters['building_name'])) {
        $stmt->bindValue(':building_name', '%' . $filters['building_name'] . '%', PDO::PARAM_STR);
    }
    if (!empty($filters['room'])) {
        $stmt->bindValue(':room', '%' . $filters['room'] . '%', PDO::PARAM_STR);
    }

    // Bind pagination parameters
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

    // Execute the query
    $stmt->execute();

    // Fetch all the locations as an associative array
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the locations
    return $locations;
}

    
    /**
     * Create a new location
     */
    public static function createLocation($locationData) {
        $conn = getDbConnection();
        
        // Check if location name already exists
        if (UtilityHelper::valueExists('locations', 'name', $locationData['name'])) {
            return [
                'success' => false,
                'message' => 'Location with this name already exists.'
            ];
        }
        
        // Insert new location
        $sql = "INSERT INTO locations (name, description, address, building, floor, room, capacity, 
                                      manager_id, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            $locationData['name'],
            $locationData['description'] ?? null,
            $locationData['address'] ?? null,
            $locationData['building'] ?? null,
            $locationData['floor'] ?? null,
            $locationData['room'] ?? null,
            $locationData['capacity'] ?? null,
            $locationData['manager_id'] ?? null,
            $locationData['is_active'] ?? 1
        ]);
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Failed to create location. Please try again.'
            ];
        }
        
        $locationId = $conn->lastInsertId();
        
        // Log activity
        UtilityHelper::logActivity(
            $_SESSION['user_id'] ?? null,
            'create_location', 
            'locations', 
            $locationId, 
            'Location created: ' . $locationData['name']
        );
        
        return [
            'success' => true,
            'message' => 'Location created successfully.',
            'location_id' => $locationId
        ];
    }
    
    /**
     * Update location
     */
    public static function updateLocation($locationId, $locationData) {
        $conn = getDbConnection();
        
        // Check if location exists
        $location = self::getLocationById($locationId);
        if (!$location) {
            return [
                'success' => false,
                'message' => 'Location not found.'
            ];
        }
        
        // Check if name already exists (excluding this location)
        if (!empty($locationData['name']) && 
            UtilityHelper::valueExists('locations', 'name', $locationData['name'], $locationId)) {
            return [
                'success' => false,
                'message' => 'Location with this name already exists.'
            ];
        }
        
        // Build update query
        $updateFields = [];
        $params = [];
        
        $possibleFields = [
            'name', 'description', 'address', 'building', 'floor', 'room', 
            'capacity', 'manager_id', 'is_active'
        ];
        
        foreach ($possibleFields as $field) {
            if (isset($locationData[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $locationData[$field];
            }
        }
        
        // Add updated_at
        $updateFields[] = "updated_at = NOW()";
        
        // Add location ID
        $params[] = $locationId;
        
        // Execute update
        $sql = "UPDATE locations SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute($params);
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Failed to update location. Please try again.'
            ];
        }
        
        // Log activity
        UtilityHelper::logActivity(
            $_SESSION['user_id'] ?? null,
            'update_location', 
            'locations', 
            $locationId, 
            'Location updated: ' . ($locationData['name'] ?? $location['name'])
        );
        
        return [
            'success' => true,
            'message' => 'Location updated successfully.'
        ];
    }
    
    /**
     * Delete location
     */
    public static function deleteLocation($locationId) {
        $conn = getDbConnection();
        
        // Get location details for logging
        $location = self::getLocationById($locationId);
        if (!$location) {
            return [
                'success' => false,
                'message' => 'Location not found.'
            ];
        }
        
        // Check if location has associated items
        $checkSql = "SELECT COUNT(*) FROM items WHERE location_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute([$locationId]);
        
        if ((int)$checkStmt->fetchColumn() > 0) {
            return [
                'success' => false,
                'message' => 'Cannot delete location because it has associated items. Consider deactivating the location instead.'
            ];
        }
        
        // Check if location has associated users
        $checkUsersSql = "SELECT COUNT(*) FROM users WHERE location_id = ?";
        $checkUsersStmt = $conn->prepare($checkUsersSql);
        $checkUsersStmt->execute([$locationId]);
        
        if ((int)$checkUsersStmt->fetchColumn() > 0) {
            return [
                'success' => false,
                'message' => 'Cannot delete location because it has associated users. Consider deactivating the location instead.'
            ];
        }
        
        // Delete location
        $sql = "DELETE FROM locations WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$locationId]);
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Failed to delete location. Please try again.'
            ];
        }
        
        // Log activity
        UtilityHelper::logActivity(
            $_SESSION['user_id'] ?? null,
            'delete_location', 
            'locations', 
            $locationId, 
            'Location deleted: ' . $location['name']
        );
        
        return [
            'success' => true,
            'message' => 'Location deleted successfully.'
        ];
    }
    
    /**
     * Get location capacity utilization
     */
    public static function getLocationCapacityUtilization($locationId) {
        $conn = getDbConnection();
        
        // Get location capacity
        $capacitySql = "SELECT capacity FROM locations WHERE id = ?";
        $capacityStmt = $conn->prepare($capacitySql);
        $capacityStmt->execute([$locationId]);
        $capacity = (int)$capacityStmt->fetchColumn();
        
        if ($capacity <= 0) {
            return [
                'capacity' => 0,
                'used' => 0,
                'utilization' => 0,
                'status' => 'unknown'
            ];
        }
        
        // Count items in location
        $countSql = "SELECT COUNT(*) FROM items WHERE location_id = ? AND is_active = 1";
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute([$locationId]);
        $itemCount = (int)$countStmt->fetchColumn();
        
        // Calculate utilization
        $utilization = ($capacity > 0) ? ($itemCount / $capacity) * 100 : 0;
        
        // Determine status
        $status = 'low';
        if ($utilization >= 90) {
            $status = 'full';
        } elseif ($utilization >= 75) {
            $status = 'high';
        } elseif ($utilization >= 50) {
            $status = 'medium';
        }
        
        return [
            'capacity' => $capacity,
            'used' => $itemCount,
            'utilization' => $utilization,
            'status' => $status
        ];
    }
    
    /**
     * Get items by location
     */
    public static function getItemsByLocation($locationId, $page = 1, $limit = ITEMS_PER_PAGE, $filters = []) {
        $conn = getDbConnection();
        
        // Build query
        $sql = "SELECT i.*, c.name as category_name
                FROM items i 
                LEFT JOIN categories c ON i.category_id = c.id 
                WHERE i.location_id = ?";
        
        $params = [$locationId];
        
        // Apply filters
        if (!empty($filters['search'])) {
            $sql .= " AND (i.name LIKE ? OR i.description LIKE ? OR i.asset_id LIKE ? OR i.barcode LIKE ?)";
            $searchParam = '%' . $filters['search'] . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if (!empty($filters['category_id'])) {
            $sql .= " AND i.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND i.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['is_active'])) {
            $sql .= " AND i.is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        // Count total records for pagination
        $countSql = str_replace("i.*, c.name as category_name", "COUNT(*) as total", $sql);
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute($params);
        $totalItems = (int)$countStmt->fetchColumn();
        
        // Add order and limit
        $sql .= " ORDER BY i.name ASC";
        
        // Calculate pagination
        $pagination = UtilityHelper::paginate($totalItems, $page, $limit);
        $sql .= " LIMIT " . $pagination['offset'] . ", " . $pagination['itemsPerPage'];
        
        // Execute query
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();
        
        return [
            'items' => $items,
            'pagination' => $pagination
        ];
    }
    
    /**
     * Get location transactions
     */
    public static function getLocationTransactions($locationId, $page = 1, $limit = ITEMS_PER_PAGE) {
        $conn = getDbConnection();
        
        // Build query for transactions involving this location
        $sql = "SELECT t.*, i.name as item_name, u.full_name as performed_by_name,
                       fl.name as from_location_name, tl.name as to_location_name
                FROM inventory_transactions t
                LEFT JOIN items i ON t.item_id = i.id
                LEFT JOIN users u ON t.performed_by = u.id
                LEFT JOIN locations fl ON t.from_location_id = fl.id
                LEFT JOIN locations tl ON t.to_location_id = tl.id
                WHERE t.from_location_id = ? OR t.to_location_id = ?";
        
        $params = [$locationId, $locationId];
        
        // Count total records for pagination
        $countSql = str_replace("t.*, i.name as item_name, u.full_name as performed_by_name,
                       fl.name as from_location_name, tl.name as to_location_name", 
                                "COUNT(*) as total", $sql);
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute($params);
        $totalItems = (int)$countStmt->fetchColumn();
        
        // Add order and limit
        $sql .= " ORDER BY t.transaction_date DESC";
        
        // Calculate pagination
        $pagination = UtilityHelper::paginate($totalItems, $page, $limit);
        $sql .= " LIMIT " . $pagination['offset'] . ", " . $pagination['itemsPerPage'];
        
        // Execute query
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll();
        
        return [
            'transactions' => $transactions,
            'pagination' => $pagination
        ];
    }
    
    /**
     * Get category distribution for a location
     */
    public static function getCategoryDistribution($locationId) {
        $conn = getDbConnection();
        
        $sql = "SELECT c.id, c.name, COUNT(i.id) as item_count
                FROM categories c
                JOIN items i ON c.id = i.category_id
                WHERE i.location_id = ? AND i.is_active = 1
                GROUP BY c.id, c.name
                ORDER BY item_count DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$locationId]);
        $categories = $stmt->fetchAll();
        
        // Calculate percentages
        $totalItems = 0;
        foreach ($categories as $category) {
            $totalItems += $category['item_count'];
        }
        
        foreach ($categories as &$category) {
            $category['percentage'] = ($totalItems > 0) ? 
                round(($category['item_count'] / $totalItems) * 100, 1) : 0;
        }
        
        return [
            'categories' => $categories,
            'total_items' => $totalItems
        ];
    }
    
    /**
     * Transfer items between locations
     */
    public static function transferItems($fromLocationId, $toLocationId, $items, $notes = null) {
        $conn = getDbConnection();
        
        // Check if locations exist
        $fromLocation = self::getLocationById($fromLocationId);
        $toLocation = self::getLocationById($toLocationId);
        
        if (!$fromLocation || !$toLocation) {
            return [
                'success' => false,
                'message' => 'One or both locations not found.'
            ];
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        try {
            // Update each item's location
            $updateSql = "UPDATE items SET location_id = ?, updated_at = NOW(), updated_by = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            
            // Log each item transfer
            $transactionSql = "INSERT INTO inventory_transactions (item_id, transaction_type, quantity, 
                                                                from_location_id, to_location_id, 
                                                                performed_by, notes, transaction_date) 
                              VALUES (?, 'transfer', 1, ?, ?, ?, ?, NOW())";
            $transactionStmt = $conn->prepare($transactionSql);
            
            foreach ($items as $itemId) {
                // Update item location
                $updateStmt->execute([$toLocationId, $_SESSION['user_id'] ?? null, $itemId]);
                
                // Log transaction
                $transactionStmt->execute([
                    $itemId,
                    $fromLocationId,
                    $toLocationId,
                    $_SESSION['user_id'] ?? null,
                    $notes
                ]);
            }
            
            // Log activity
            UtilityHelper::logActivity(
                $_SESSION['user_id'] ?? null,
                'transfer_items', 
                'locations', 
                null, 
                'Transferred ' . count($items) . ' items from ' . $fromLocation['name'] . ' to ' . $toLocation['name']
            );
            
            // Commit transaction
            $conn->commit();
            
            return [
                'success' => true,
                'message' => count($items) . ' items transferred successfully.',
                'from_location' => $fromLocation['name'],
                'to_location' => $toLocation['name']
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to transfer items: ' . $e->getMessage()
            ];
        }
    }
}