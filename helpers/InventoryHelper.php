<?php
class InventoryHelper {
    /**
     * Get item by ID
     */
    public static function getItemById($itemId) {
        $conn = getDbConnection();
        
        $sql = "SELECT i.*, c.name as category_name, l.name as location_name, s.name as supplier_name 
                FROM items i 
                LEFT JOIN categories c ON i.category_id = c.id 
                LEFT JOIN locations l ON i.location_id = l.id 
                LEFT JOIN suppliers s ON i.supplier_id = s.id 
                WHERE i.id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$itemId]);
        
        $item = $stmt->fetch();
        
        if ($item) {
            // Get item tags
            $item['tags'] = self::getItemTags($itemId);
            
            // Get item images
            $item['images'] = self::getItemImages($itemId);
        }
        
        return $item;
    }
    
    /**
     * Get all items with pagination and filters
     */
    public static function getAllItems($page = 1, $limit = ITEMS_PER_PAGE, $filters = []) {
        $conn = getDbConnection();
        
        // Build query
        $sql = "SELECT i.*, c.name as category_name, l.name as location_name, s.name as supplier_name 
                FROM items i 
                LEFT JOIN categories c ON i.category_id = c.id 
                LEFT JOIN locations l ON i.location_id = l.id 
                LEFT JOIN suppliers s ON i.supplier_id = s.id";
        
        $where = [];
        $params = [];
        
        // Apply filters
        if (!empty($filters['search'])) {
            $where[] = "(i.name LIKE ? OR i.description LIKE ? OR i.asset_id LIKE ? OR i.barcode LIKE ?)";
            $searchParam = '%' . $filters['search'] . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if (!empty($filters['category_id'])) {
            $where[] = "i.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['location_id'])) {
            $where[] = "i.location_id = ?";
            $params[] = $filters['location_id'];
        }
        
        if (!empty($filters['supplier_id'])) {
            $where[] = "i.supplier_id = ?";
            $params[] = $filters['supplier_id'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = "i.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['is_active'])) {
            $where[] = "i.is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        // Tag filter
        if (!empty($filters['tag'])) {
            $sql .= " INNER JOIN item_tags it ON i.id = it.item_id 
                      INNER JOIN tags t ON it.tag_id = t.id";
            $where[] = "t.name = ?";
            $params[] = $filters['tag'];
        }
        
        // Combine WHERE clauses
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        // Count total records for pagination
        $countSql = str_replace("i.*, c.name as category_name, l.name as location_name, s.name as supplier_name", 
                                "COUNT(DISTINCT i.id) as total", $sql);
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute($params);
        $totalItems = (int)$countStmt->fetchColumn();
        
        // Add order and limit
        $sql .= " GROUP BY i.id ORDER BY i.name ASC";
        
        // Calculate pagination
        $pagination = UtilityHelper::paginate($totalItems, $page, $limit);
        $sql .= " LIMIT " . $pagination['offset'] . ", " . $pagination['itemsPerPage'];
        
        // Execute query
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();
        
        // Get tags and images for each item
        foreach ($items as &$item) {
            $item['tags'] = self::getItemTags($item['id']);
            $item['images'] = self::getItemImages($item['id']);
        }
        
        return [
            'items' => $items,
            'pagination' => $pagination
        ];
    }
    
    /**
     * Create a new item
     */
    public static function createItem($itemData) {
        $conn = getDbConnection();
        
        // Begin transaction
        $conn->beginTransaction();
        
        try {
            // Generate slug if not provided
            if (empty($itemData['slug'])) {
                $itemData['slug'] = self::generateSlug($itemData['name']);
            }
            
            // Check if slug already exists
            if (UtilityHelper::valueExists('items', 'slug', $itemData['slug'])) {
                return [
                    'success' => false,
                    'message' => 'Item with this slug already exists. Please use a different name.'
                ];
            }
            
            // Check if asset_id already exists (if provided)
            if (!empty($itemData['asset_id']) && UtilityHelper::valueExists('items', 'asset_id', $itemData['asset_id'])) {
                return [
                    'success' => false,
                    'message' => 'Item with this Asset ID already exists.'
                ];
            }
            
            // Check if barcode already exists (if provided)
            if (!empty($itemData['barcode']) && UtilityHelper::valueExists('items', 'barcode', $itemData['barcode'])) {
                return [
                    'success' => false,
                    'message' => 'Item with this Barcode already exists.'
                ];
            }
            
            // Insert new item
            $sql = "INSERT INTO items (name, slug, asset_id, category_id, location_id, supplier_id, brand, model, 
                                      model_number, serial_number, barcode, status, condition_rating, description, 
                                      specifications, notes, purchase_date, purchase_price, warranty_expiry, 
                                      current_value, maintenance_interval, next_maintenance_date, is_active, 
                                      created_at, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
            
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([
                $itemData['name'],
                $itemData['slug'],
                $itemData['asset_id'] ?? null,
                $itemData['category_id'] ?? null,
                $itemData['location_id'] ?? null,
                $itemData['supplier_id'] ?? null,
                $itemData['brand'] ?? null,
                $itemData['model'] ?? null,
                $itemData['model_number'] ?? null,
                $itemData['serial_number'] ?? null,
                $itemData['barcode'] ?? null,
                $itemData['status'] ?? 'available',
                $itemData['condition_rating'] ?? 'good',
                $itemData['description'] ?? null,
                $itemData['specifications'] ?? null,
                $itemData['notes'] ?? null,
                $itemData['purchase_date'] ?? null,
                $itemData['purchase_price'] ?? null,
                $itemData['warranty_expiry'] ?? null,
                $itemData['current_value'] ?? null,
                $itemData['maintenance_interval'] ?? null,
                $itemData['next_maintenance_date'] ?? null,
                $itemData['is_active'] ?? 1,
                $_SESSION['user_id'] ?? null
            ]);
            
            if (!$result) {
                throw new Exception('Failed to create item');
            }
            
            $itemId = $conn->lastInsertId();
            
            // Handle tags if provided
            if (!empty($itemData['tags'])) {
                self::updateItemTags($itemId, $itemData['tags']);
            }
            
            // Handle images if provided
            if (!empty($itemData['images'])) {
                foreach ($itemData['images'] as $image) {
                    self::addItemImage($itemId, $image);
                }
            }
            
            // Log activity
            UtilityHelper::logActivity(
                $_SESSION['user_id'] ?? null,
                'create_item', 
                'items', 
                $itemId, 
                'Item created: ' . $itemData['name']
            );
            
            // Log inventory transaction
            $transactionData = [
                'item_id' => $itemId,
                'transaction_type' => 'purchase',
                'quantity' => 1,
                'notes' => 'Initial item creation'
            ];
            self::logInventoryTransaction($transactionData);
            
            // Commit transaction
            $conn->commit();
            
            return [
                'success' => true,
                'message' => 'Item created successfully.',
                'item_id' => $itemId
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to create item: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update an existing item
     */
    public static function updateItem($itemId, $itemData) {
        $conn = getDbConnection();
        
        // Begin transaction
        $conn->beginTransaction();
        
        try {
            // Check if item exists
            $checkSql = "SELECT id FROM items WHERE id = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute([$itemId]);
            
            if (!$checkStmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Item not found.'
                ];
            }
            
            // Generate slug if name is changed and slug is not provided
            if (!empty($itemData['name']) && empty($itemData['slug'])) {
                $itemData['slug'] = self::generateSlug($itemData['name']);
            }
            
            // Check if slug already exists (excluding this item)
            if (!empty($itemData['slug']) && 
                UtilityHelper::valueExists('items', 'slug', $itemData['slug'], $itemId)) {
                return [
                    'success' => false,
                    'message' => 'Item with this slug already exists. Please use a different name.'
                ];
            }
            
            // Check if asset_id already exists (if provided, excluding this item)
            if (!empty($itemData['asset_id']) && 
                UtilityHelper::valueExists('items', 'asset_id', $itemData['asset_id'], $itemId)) {
                return [
                    'success' => false,
                    'message' => 'Item with this Asset ID already exists.'
                ];
            }
            
            // Check if barcode already exists (if provided, excluding this item)
            if (!empty($itemData['barcode']) && 
                UtilityHelper::valueExists('items', 'barcode', $itemData['barcode'], $itemId)) {
                return [
                    'success' => false,
                    'message' => 'Item with this Barcode already exists.'
                ];
            }
            
            // Build update query
            $updateFields = [];
            $params = [];
            
            $possibleFields = [
                'name', 'slug', 'asset_id', 'category_id', 'location_id', 'supplier_id', 'brand', 
                'model', 'model_number', 'serial_number', 'barcode', 'status', 'condition_rating', 
                'description', 'specifications', 'notes', 'purchase_date', 'purchase_price', 
                'warranty_expiry', 'current_value', 'maintenance_interval', 'next_maintenance_date', 
                'is_active'
            ];
            
            foreach ($possibleFields as $field) {
                if (isset($itemData[$field])) {
                    $updateFields[] = "$field = ?";
                    $params[] = $itemData[$field];
                }
            }
            
            // Add updated_at and updated_by
            $updateFields[] = "updated_at = NOW()";
            $updateFields[] = "updated_by = ?";
            $params[] = $_SESSION['user_id'] ?? null;
            
            // Add item ID
            $params[] = $itemId;
            
            // Execute update
            $sql = "UPDATE items SET " . implode(", ", $updateFields) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute($params);
            
            if (!$result) {
                throw new Exception('Failed to update item');
            }
            
            // Handle tags if provided
            if (isset($itemData['tags'])) {
                self::updateItemTags($itemId, $itemData['tags']);
            }
            
            // Handle images if provided
            if (!empty($itemData['images'])) {
                foreach ($itemData['images'] as $image) {
                    self::addItemImage($itemId, $image);
                }
            }
            
            // Log activity
            UtilityHelper::logActivity(
                $_SESSION['user_id'] ?? null,
                'update_item', 
                'items', 
                $itemId, 
                'Item updated: ' . ($itemData['name'] ?? 'ID: ' . $itemId)
            );
            
            // Commit transaction
            $conn->commit();
            
            return [
                'success' => true,
                'message' => 'Item updated successfully.'
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to update item: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete an item
     */
    public static function deleteItem($itemId) {
        $conn = getDbConnection();
        
        // Get item details for logging
        $item = self::getItemById($itemId);
        if (!$item) {
            return [
                'success' => false,
                'message' => 'Item not found.'
            ];
        }
        
        // Check if item has associated records
        $checks = [
            ["borrowed_items", "item_id", "active borrow records"],
            ["cart_items", "item_id", "cart items"],
            ["maintenance_records", "item_id", "maintenance records"]
        ];
        
        foreach ($checks as $check) {
            list($table, $column, $description) = $check;
            
            $checkSql = "SELECT COUNT(*) FROM $table WHERE $column = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute([$itemId]);
            
            if ((int)$checkStmt->fetchColumn() > 0) {
                return [
                    'success' => false,
                    'message' => "Cannot delete item because it has associated $description. Consider deactivating the item instead."
                ];
            }
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        try {
            // Delete item tags
            $deleteTagsSql = "DELETE FROM item_tags WHERE item_id = ?";
            $deleteTagsStmt = $conn->prepare($deleteTagsSql);
            $deleteTagsStmt->execute([$itemId]);
            
            // Delete item images
            $imagesSql = "SELECT file_path FROM item_images WHERE item_id = ?";
            $imagesStmt = $conn->prepare($imagesSql);
            $imagesStmt->execute([$itemId]);
            $images = $imagesStmt->fetchAll();
            
            // Delete image files
            foreach ($images as $image) {
                if (file_exists($image['file_path'])) {
                    unlink($image['file_path']);
                }
            }
            
            // Delete image records
            $deleteImagesSql = "DELETE FROM item_images WHERE item_id = ?";
            $deleteImagesStmt = $conn->prepare($deleteImagesSql);
            $deleteImagesStmt->execute([$itemId]);
            
            // Delete inventory transactions
            $deleteTransactionsSql = "DELETE FROM inventory_transactions WHERE item_id = ?";
            $deleteTransactionsStmt = $conn->prepare($deleteTransactionsSql);
            $deleteTransactionsStmt->execute([$itemId]);
            
            // Delete item
            $sql = "DELETE FROM items WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([$itemId]);
            
            if (!$result) {
                throw new Exception('Failed to delete item');
            }
            
            // Log activity
            UtilityHelper::logActivity(
                $_SESSION['user_id'] ?? null,
                'delete_item', 
                'items', 
                $itemId, 
                'Item deleted: ' . $item['name']
            );
            
            // Commit transaction
            $conn->commit();
            
            return [
                'success' => true,
                'message' => 'Item deleted successfully.'
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to delete item: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get item tags
     */
    public static function getItemTags($itemId) {
        $conn = getDbConnection();
        
        $sql = "SELECT t.id, t.name 
                FROM tags t 
                JOIN item_tags it ON t.id = it.tag_id 
                WHERE it.item_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$itemId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Update item tags
     */
    public static function updateItemTags($itemId, $tags) {
        $conn = getDbConnection();
        
        // Begin transaction
        $conn->beginTransaction();
        
        try {
            // Delete existing tags
            $deleteSql = "DELETE FROM item_tags WHERE item_id = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->execute([$itemId]);
            
            // Add new tags
            if (!empty($tags)) {
                $insertTagSql = "INSERT INTO tags (name) VALUES (?) ON DUPLICATE KEY UPDATE name = VALUES(name)";
                $insertTagStmt = $conn->prepare($insertTagSql);
                
                $insertItemTagSql = "INSERT INTO item_tags (item_id, tag_id) VALUES (?, ?)";
                $insertItemTagStmt = $conn->prepare($insertItemTagSql);
                
                foreach ($tags as $tag) {
                    // Insert tag if not exists
                    $insertTagStmt->execute([$tag]);
                    
                    // Get tag ID
                    $getTagSql = "SELECT id FROM tags WHERE name = ?";
                    $getTagStmt = $conn->prepare($getTagSql);
                    $getTagStmt->execute([$tag]);
                    $tagId = $getTagStmt->fetchColumn();
                    
                    // Insert item-tag relationship
                    $insertItemTagStmt->execute([$itemId, $tagId]);
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            
            return false;
        }
    }
    
    /**
     * Get item images
     */
    public static function getItemImages($itemId) {
        $conn = getDbConnection();
        
        $sql = "SELECT * FROM item_images WHERE item_id = ? ORDER BY is_primary DESC, display_order ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$itemId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Add item image
     */
    public static function addItemImage($itemId, $imageData) {
        $conn = getDbConnection();
        
        // Check if item exists
        $checkSql = "SELECT id FROM items WHERE id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute([$itemId]);
        
        if (!$checkStmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Item not found.'
            ];
        }
        
        // Upload image file if it's a file upload
        if (isset($imageData['tmp_name'])) {
            $uploadDir = UPLOAD_DIR . '/items/' . $itemId;
            $uploadResult = UtilityHelper::uploadFile($imageData, $uploadDir, ALLOWED_IMAGE_TYPES);
            
            if (!$uploadResult['success']) {
                return $uploadResult;
            }
            
            $filePath = $uploadResult['path'];
            $fileName = $uploadResult['filename'];
        } else {
            // If it's just image data without file upload
            $filePath = $imageData['file_path'] ?? null;
            $fileName = $imageData['file_name'] ?? null;
        }
        
        // Get max display order
        $maxOrderSql = "SELECT MAX(display_order) FROM item_images WHERE item_id = ?";
        $maxOrderStmt = $conn->prepare($maxOrderSql);
        $maxOrderStmt->execute([$itemId]);
        $maxOrder = (int)$maxOrderStmt->fetchColumn();
        
        // Determine if this is primary image
        $isPrimary = $imageData['is_primary'] ?? 0;
        
        // If setting as primary, update existing primary images
        if ($isPrimary) {
            $updatePrimarySql = "UPDATE item_images SET is_primary = 0 WHERE item_id = ?";
            $updatePrimaryStmt = $conn->prepare($updatePrimarySql);
            $updatePrimaryStmt->execute([$itemId]);
        }
        
        // Insert image record
        $sql = "INSERT INTO item_images (item_id, file_path, file_name, file_size, file_type, is_primary, display_order) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            $itemId,
            $filePath,
            $fileName,
            $imageData['file_size'] ?? null,
            $imageData['file_type'] ?? null,
            $isPrimary,
            $maxOrder + 1
        ]);
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Failed to add image.'
            ];
        }
        
        $imageId = $conn->lastInsertId();
        
        return [
            'success' => true,
            'message' => 'Image added successfully.',
            'image_id' => $imageId
        ];
    }
    
    /**
     * Delete item image
     */
    public static function deleteItemImage($imageId) {
        $conn = getDbConnection();
        
        // Get image details
        $sql = "SELECT * FROM item_images WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$imageId]);
        $image = $stmt->fetch();
        
        if (!$image) {
            return [
                'success' => false,
                'message' => 'Image not found.'
            ];
        }
        
        // Delete file if exists
        if (!empty($image['file_path']) && file_exists($image['file_path'])) {
            unlink($image['file_path']);
        }
        
        // Delete record
        $deleteSql = "DELETE FROM item_images WHERE id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $result = $deleteStmt->execute([$imageId]);
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Failed to delete image.'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Image deleted successfully.'
        ];
    }
    
    /**
     * Generate item slug
     */
    public static function generateSlug($name) {
        // Convert to lowercase and replace spaces with hyphens
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        $slug = preg_replace('/[\s\-]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Ensure the slug is unique
        $conn = getDbConnection();
        $baseSlug = $slug;
        $counter = 1;
        
        while (UtilityHelper::valueExists('items', 'slug', $slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Log inventory transaction
     */
    public static function logInventoryTransaction($data) {
        $conn = getDbConnection();
        
        $sql = "INSERT INTO inventory_transactions (item_id, transaction_type, quantity, from_location_id, 
                                                  to_location_id, related_record_type, related_record_id, 
                                                  performed_by, notes, transaction_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            $data['item_id'],
            $data['transaction_type'],
            $data['quantity'],
            $data['from_location_id'] ?? null,
            $data['to_location_id'] ?? null,
            $data['related_record_type'] ?? null,
            $data['related_record_id'] ?? null,
            $_SESSION['user_id'] ?? null,
            $data['notes'] ?? null
        ]);
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Failed to log inventory transaction.'
            ];
        }
        
        return [
            'success' => true,
            'transaction_id' => $conn->lastInsertId()
        ];
    }
    
    /**
     * Get inventory transactions
     */
    public static function getInventoryTransactions($filters = [], $page = 1, $limit = ITEMS_PER_PAGE) {
        $conn = getDbConnection();
        
        // Build query
        $sql = "SELECT t.*, i.name as item_name, fl.name as from_location, tl.name as to_location, 
                       u.full_name as performed_by_name 
                FROM inventory_transactions t 
                LEFT JOIN items i ON t.item_id = i.id 
                LEFT JOIN locations fl ON t.from_location_id = fl.id 
                LEFT JOIN locations tl ON t.to_location_id = tl.id 
                LEFT JOIN users u ON t.performed_by = u.id";
        
        $where = [];
        $params = [];
        
        // Apply filters
        if (!empty($filters['item_id'])) {
            $where[] = "t.item_id = ?";
            $params[] = $filters['item_id'];
        }
        
        if (!empty($filters['transaction_type'])) {
            $where[] = "t.transaction_type = ?";
            $params[] = $filters['transaction_type'];
        }
        
        if (!empty($filters['from_location_id'])) {
            $where[] = "t.from_location_id = ?";
            $params[] = $filters['from_location_id'];
        }
        
        if (!empty($filters['to_location_id'])) {
            $where[] = "t.to_location_id = ?";
            $params[] = $filters['to_location_id'];
        }
        
        if (!empty($filters['performed_by'])) {
            $where[] = "t.performed_by = ?";
            $params[] = $filters['performed_by'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(t.transaction_date) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(t.transaction_date) <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Combine WHERE clauses
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        // Count total records for pagination
        $countSql = str_replace("t.*, i.name as item_name, fl.name as from_location, tl.name as to_location, 
                       u.full_name as performed_by_name", "COUNT(*) as total", $sql);
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
     * Get all categories with hierarchy
     */
    public static function getAllCategories() {
        $conn = getDbConnection();
        
        $sql = "SELECT c.*, p.name as parent_name, 
                       (SELECT COUNT(*) FROM items WHERE category_id = c.id) as item_count 
                FROM categories c 
                LEFT JOIN categories p ON c.parent_id = p.id 
                WHERE c.is_active = 1 
                ORDER BY c.parent_id, c.name";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $categories = $stmt->fetchAll();
        
        // Build hierarchy
        $categoriesById = [];
        $rootCategories = [];
        
        foreach ($categories as $category) {
            $categoriesById[$category['id']] = $category;
            $categoriesById[$category['id']]['children'] = [];
        }
        
        foreach ($categories as $category) {
            if ($category['parent_id']) {
                $categoriesById[$category['parent_id']]['children'][] = &$categoriesById[$category['id']];
            } else {
                $rootCategories[] = &$categoriesById[$category['id']];
            }
        }
        
        return $rootCategories;
    }
    
    /**
     * Get all suppliers
     */
    public static function getAllSuppliers() {
        $conn = getDbConnection();
        
        $sql = "SELECT * FROM suppliers WHERE is_active = 1 ORDER BY name";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}