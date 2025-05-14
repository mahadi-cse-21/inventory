<?php
class UserHelper {
    /**
     * Get user by ID
     */
    public static function getUserById($userId) {
        $conn = getDbConnection();
        
        $sql = "SELECT u.*, d.name as department_name, l.name as location_name 
                FROM users u 
                LEFT JOIN departments d ON u.department_id = d.id 
                LEFT JOIN locations l ON u.location_id = l.id 
                WHERE u.id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Get all users with pagination
     */
    public static function getAllUsers($page = 1, $limit = ITEMS_PER_PAGE, $filters = []) {
        $conn = getDbConnection();
        
        // Build query
        $sql = "SELECT u.*, d.name as department_name, l.name as location_name 
                FROM users u 
                LEFT JOIN departments d ON u.department_id = d.id 
                LEFT JOIN locations l ON u.location_id = l.id";
        
        $where = [];
        $params = [];
        
        // Apply filters
        if (!empty($filters['search'])) {
            $where[] = "(u.username LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
            $searchParam = '%' . $filters['search'] . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if (!empty($filters['role'])) {
            $where[] = "u.role = ?";
            $params[] = $filters['role'];
        }
        
        if (!empty($filters['department_id'])) {
            $where[] = "u.department_id = ?";
            $params[] = $filters['department_id'];
        }
        
        if (!array_key_exists('is_active', $filters)) {
            $where[] = "u.is_active = ?";
            $params[] = 1; // Default to active users if not specified
        } else if($filters['is_active'] !== '') {
            $where[] = "u.is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        // Combine WHERE clauses
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        // Count total records for pagination
        $countSql = str_replace("u.*, d.name as department_name, l.name as location_name", "COUNT(*) as total", $sql);
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute($params);
        $totalItems = (int)$countStmt->fetchColumn();
        
        // Add order and limit
        $sql .= " ORDER BY u.full_name ASC";
        
        // Calculate pagination
        $pagination = UtilityHelper::paginate($totalItems, $page, $limit);
        $sql .= " LIMIT " . $pagination['offset'] . ", " . $pagination['itemsPerPage'];
        
        // Execute query
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();
        
        return [
            'users' => $users,
            'pagination' => $pagination
        ];
    }
    
    /**
     * Create a new user
     */
    public static function createUser($userData) {
        $conn = getDbConnection();
        
        // Check if username already exists
        if (UtilityHelper::valueExists('users', 'username', $userData['username'])) {
            return [
                'success' => false,
                'message' => 'Username already exists. Please choose a different username.'
            ];
        }
        
        // Check if email already exists
        if (UtilityHelper::valueExists('users', 'email', $userData['email'])) {
            return [
                'success' => false,
                'message' => 'Email already exists. Please use a different email address.'
            ];
        }
        
        // Hash password
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        // Insert new user
        $sql = "INSERT INTO users (username, password, email, full_name, phone, department_id, job_title, 
                                  location_id, role, is_active, created_at, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            $userData['username'],
            $hashedPassword,
            $userData['email'],
            $userData['full_name'],
            $userData['phone'] ?? null,
            $userData['department_id'] ?? null,
            $userData['job_title'] ?? null,
            $userData['location_id'] ?? null,
            $userData['role'] ?? 'user',
            $userData['is_active'] ?? 1,
            $_SESSION['user_id'] ?? null
        ]);
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Failed to create user. Please try again.'
            ];
        }
        
        $userId = $conn->lastInsertId();
        
        // Log activity
        UtilityHelper::logActivity(
            $_SESSION['user_id'] ?? $userId, 
            'create_user', 
            'users', 
            $userId, 
            'User created: ' . $userData['username']
        );
        
        return [
            'success' => true,
            'message' => 'User created successfully.',
            'user_id' => $userId
        ];
    }
    
    /**
     * Update user
     */
    public static function updateUser($userId, $userData) {
        $conn = getDbConnection();
        
        // Check if username already exists (excluding this user)
        if (!empty($userData['username']) && 
            UtilityHelper::valueExists('users', 'username', $userData['username'], $userId)) {
            return [
                'success' => false,
                'message' => 'Username already exists. Please choose a different username.'
            ];
        }
        
        // Check if email already exists (excluding this user)
        if (!empty($userData['email']) && 
            UtilityHelper::valueExists('users', 'email', $userData['email'], $userId)) {
            return [
                'success' => false,
                'message' => 'Email already exists. Please use a different email address.'
            ];
        }
        
        // Build update query
        $updateFields = [];
        $params = [];
        
        $possibleFields = [
            'username', 'email', 'full_name', 'phone', 'department_id', 
            'job_title', 'location_id', 'role', 'is_active'
        ];
        
        foreach ($possibleFields as $field) {
            if (isset($userData[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $userData[$field];
            }
        }
        
        // If password is being updated
        if (!empty($userData['password'])) {
            $updateFields[] = "password = ?";
            $params[] = password_hash($userData['password'], PASSWORD_DEFAULT);
        }
        
        // Add updated_at and updated_by
        $updateFields[] = "updated_at = NOW()";
        $updateFields[] = "updated_by = ?";
        $params[] = $_SESSION['user_id'] ?? null;
        
        // Add user ID
        $params[] = $userId;
        
        // Execute update
        $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute($params);
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Failed to update user. Please try again.'
            ];
        }
        
        // Log activity
        UtilityHelper::logActivity(
            $_SESSION['user_id'] ?? null,
            'update_user', 
            'users', 
            $userId, 
            'User updated: ' . ($userData['username'] ?? 'ID: ' . $userId)
        );
        
        return [
            'success' => true,
            'message' => 'User updated successfully.'
        ];
    }
    
    /**
     * Delete user
     */
    public static function deleteUser($userId) {
        $conn = getDbConnection();
        
        // Get user details for logging
        $user = self::getUserById($userId);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.'
            ];
        }
        
        // Check if user has associated records
        $checks = [
            ["borrowed_items", "user_id", "active borrow records"],
            ["borrow_requests", "user_id", "borrow requests"],
            ["purchase_orders", "ordered_by", "purchase orders"]
        ];
        
        foreach ($checks as $check) {
            list($table, $column, $description) = $check;
            
            $checkSql = "SELECT COUNT(*) FROM $table WHERE $column = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute([$userId]);
            
            if ((int)$checkStmt->fetchColumn() > 0) {
                return [
                    'success' => false,
                    'message' => "Cannot delete user because they have associated $description. Consider deactivating the user instead."
                ];
            }
        }
        
        // Delete user
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$userId]);
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Failed to delete user. Please try again.'
            ];
        }
        
        // Log activity
        UtilityHelper::logActivity(
            $_SESSION['user_id'] ?? null,
            'delete_user', 
            'users', 
            $userId, 
            'User deleted: ' . $user['username']
        );
        
        return [
            'success' => true,
            'message' => 'User deleted successfully.'
        ];
    }
    
    /**
     * Get user permissions
     */
    public static function getUserPermissions($userId) {
        $conn = getDbConnection();
        
        // Get user's role
        $roleSql = "SELECT role FROM users WHERE id = ?";
        $roleStmt = $conn->prepare($roleSql);
        $roleStmt->execute([$userId]);
        $role = $roleStmt->fetchColumn();
        
        if (!$role) {
            return [];
        }
        
        // Get role permissions
        $sql = "SELECT p.id, p.name, p.description 
                FROM permissions p 
                JOIN role_permissions rp ON p.id = rp.permission_id 
                WHERE rp.role = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$role]);
        $rolePermissions = $stmt->fetchAll();
        
        // Get user specific permissions (overrides)
        $overrideSql = "SELECT p.id, p.name, p.description, up.granted 
                        FROM permissions p 
                        JOIN user_permissions up ON p.id = up.permission_id 
                        WHERE up.user_id = ?";
        
        $overrideStmt = $conn->prepare($overrideSql);
        $overrideStmt->execute([$userId]);
        $userPermissions = $overrideStmt->fetchAll();
        
        // Combine permissions (user-specific overrides take precedence)
        $permissions = [];
        
        foreach ($rolePermissions as $permission) {
            $permissions[$permission['name']] = [
                'id' => $permission['id'],
                'name' => $permission['name'],
                'description' => $permission['description'],
                'granted' => true
            ];
        }
        
        foreach ($userPermissions as $permission) {
            $permissions[$permission['name']] = [
                'id' => $permission['id'],
                'name' => $permission['name'],
                'description' => $permission['description'],
                'granted' => (bool)$permission['granted']
            ];
        }
        
        return array_values($permissions);
    }
    
    /**
     * Set user permissions (overrides)
     */
    public static function setUserPermissions($userId, $permissions) {
        $conn = getDbConnection();
        
        // Begin transaction
        $conn->beginTransaction();
        
        try {
            // Clear existing user permissions
            $clearSql = "DELETE FROM user_permissions WHERE user_id = ?";
            $clearStmt = $conn->prepare($clearSql);
            $clearStmt->execute([$userId]);
            
            // Insert new permissions
            $insertSql = "INSERT INTO user_permissions (user_id, permission_id, granted) VALUES (?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            
            foreach ($permissions as $permissionId => $granted) {
                $insertStmt->execute([$userId, $permissionId, $granted ? 1 : 0]);
            }
            
            // Commit transaction
            $conn->commit();
            
            // Log activity
            UtilityHelper::logActivity(
                $_SESSION['user_id'] ?? null,
                'update_permissions', 
                'users', 
                $userId, 
                'User permissions updated'
            );
            
            return [
                'success' => true,
                'message' => 'User permissions updated successfully.'
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to update user permissions: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get user activity logs
     */
    public static function getUserActivityLogs($userId, $page = 1, $limit = ITEMS_PER_PAGE) {
        $conn = getDbConnection();
        
        // Count total records
        $countSql = "SELECT COUNT(*) FROM activity_logs WHERE user_id = ?";
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute([$userId]);
        $totalItems = (int)$countStmt->fetchColumn();
        
        // Calculate pagination
        $pagination = UtilityHelper::paginate($totalItems, $page, $limit);
        
        // Get logs
        $sql = "SELECT * FROM activity_logs 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?, ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId, $pagination['offset'], $pagination['itemsPerPage']]);
        $logs = $stmt->fetchAll();
        
        return [
            'logs' => $logs,
            'pagination' => $pagination
        ];
    }
    
    /**
     * Get all departments
     */
    public static function getAllDepartments() {
       
    }
}