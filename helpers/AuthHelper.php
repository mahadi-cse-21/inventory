<?php
class AuthHelper {
    /**
     * Authenticate user with provided credentials
     */
    public static function login($email, $password) {
        $conn = getDbConnection();
        
        // Get user by username
        $sql = "SELECT id, username, password, email, full_name, role, department_id, is_active, 
                       account_locked, failed_login_attempts 
                FROM users 
                WHERE email = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // Check if user exists
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid email or password.'
            ];
        }
        
        // Check if account is active
        if (!$user['is_active']) {
            return [
                'success' => false,
                'message' => 'Your account is inactive. Please contact the administrator.'
            ];
        }
        
        // Check if account is locked
        if ($user['account_locked']) {
            return [
                'success' => false,
                'message' => 'Your account has been locked due to too many failed login attempts. Please contact the administrator.'
            ];
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            // Increment failed login attempts
            self::incrementFailedLoginAttempts($user['id']);
            
            return [
                'success' => false,
                'message' => 'Invalid email or password.'
            ];
        }
        
        // Reset failed login attempts
        self::resetFailedLoginAttempts($user['id']);
        
        // Update last login timestamp
        $updateSql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->execute([$user['id']]);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['department_id'] = $user['department_id'];
        
        // Log activity
        UtilityHelper::logActivity(
            $user['id'], 
            'login', 
            'users', 
            $user['id'], 
            'User logged in'
        );
        
        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ];
    }
    
    /**
     * Log out the current user
     */
    public static function logout() {
        // Log activity if user is logged in
        if (isset($_SESSION['user_id'])) {
            UtilityHelper::logActivity(
                $_SESSION['user_id'], 
                'logout', 
                'users', 
                $_SESSION['user_id'], 
                'User logged out'
            );
        }
        
        // Clear all session variables
        $_SESSION = [];
        
        // Destroy the session
        session_destroy();
        
        return [
            'success' => true,
            'message' => 'You have been successfully logged out.'
        ];
    }
    
    /**
     * Register a new user
     */
    public static function register($userData) {
        $conn = getDbConnection();
        
        // Remove unnecessary username check or set a default value
        $userData['username'] = $userData['username'] ?? $userData['email']; // Use email as username if not provided
        
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
                                  location_id, role, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            $userData['username'], // Ensure username is passed
            $hashedPassword,
            $userData['email'],
            $userData['full_name'],
            $userData['phone'] ?? null,
            $userData['department_id'] ?? null,
            $userData['job_title'] ?? null,
            $userData['location_id'] ?? null,
            $userData['role'] ?? 'user',
            $userData['is_active'] ?? 1
        ]);
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Failed to register user. Please try again.'
            ];
        }
        
        $userId = $conn->lastInsertId();
        
        // Log activity
        UtilityHelper::logActivity(
            $userId, 
            'register', 
            'users', 
            $userId, 
            'New user registered'
        );
        
        return [
            'success' => true,
            'message' => 'User registered successfully.',
            'user_id' => $userId
        ];
    }
    
    /**
     * Reset password
     */
    public static function resetPassword($email) {
        $conn = getDbConnection();
        
        // Check if email exists
        $sql = "SELECT id, username, full_name FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Email not found.'
            ];
        }
        
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Update user with token
        $updateSql = "UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->execute([$token, $expires, $user['id']]);
        
        // Log activity
        UtilityHelper::logActivity(
            $user['id'], 
            'password_reset_request', 
            'users', 
            $user['id'], 
            'Password reset requested'
        );
        
        return [
            'success' => true,
            'message' => 'Password reset link has been sent to your email.',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'full_name' => $user['full_name']
            ]
        ];
    }
    
    /**
     * Validate reset token
     */
    public static function validateResetToken($token) {
        $conn = getDbConnection();
        
        $sql = "SELECT id FROM users 
                WHERE password_reset_token = ? 
                AND password_reset_expires > NOW()";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        return $user !== false;
    }
    
    /**
     * Change password with reset token
     */
    public static function changePasswordWithToken($token, $newPassword) {
        $conn = getDbConnection();
        
        // Get user by token
        $sql = "SELECT id FROM users 
                WHERE password_reset_token = ? 
                AND password_reset_expires > NOW()";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid or expired token.'
            ];
        }
        
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update user's password and clear token
        $updateSql = "UPDATE users 
                      SET password = ?, 
                          password_reset_token = NULL, 
                          password_reset_expires = NULL 
                      WHERE id = ?";
        
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->execute([$hashedPassword, $user['id']]);
        
        // Log activity
        UtilityHelper::logActivity(
            $user['id'], 
            'password_changed', 
            'users', 
            $user['id'], 
            'Password changed using reset token'
        );
        
        return [
            'success' => true,
            'message' => 'Password has been changed successfully.'
        ];
    }
    
    /**
     * Change password for logged in user
     */
    public static function changePassword($userId, $currentPassword, $newPassword) {
        $conn = getDbConnection();
        
        // Get user
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.'
            ];
        }
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            return [
                'success' => false,
                'message' => 'Current password is incorrect.'
            ];
        }
        
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password
        $updateSql = "UPDATE users SET password = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->execute([$hashedPassword, $userId]);
        
        // Log activity
        UtilityHelper::logActivity(
            $userId, 
            'password_changed', 
            'users', 
            $userId, 
            'Password changed by user'
        );
        
        return [
            'success' => true,
            'message' => 'Password has been changed successfully.'
        ];
    }
    
    /**
     * Increment failed login attempts
     */
    private static function incrementFailedLoginAttempts($userId) {
        $conn = getDbConnection();
        
        // Get current attempts
        $sql = "SELECT failed_login_attempts FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId]);
        $attempts = (int)$stmt->fetchColumn();
        
        // Increment attempts
        $attempts++;
        
        // Update user
        $updateSql = "UPDATE users SET failed_login_attempts = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->execute([$attempts, $userId]);
        
        // Lock account if max attempts reached
        if ($attempts >= 5) {
            $lockSql = "UPDATE users SET account_locked = 1 WHERE id = ?";
            $lockStmt = $conn->prepare($lockSql);
            $lockStmt->execute([$userId]);
            
            // Log activity
            UtilityHelper::logActivity(
                $userId, 
                'account_locked', 
                'users', 
                $userId, 
                'Account locked due to too many failed login attempts'
            );
        }
    }
    
    /**
     * Reset failed login attempts
     */
    private static function resetFailedLoginAttempts($userId) {
        $conn = getDbConnection();
        
        $sql = "UPDATE users SET failed_login_attempts = 0 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId]);
    }
    
    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Get current authenticated user
     */
    public static function getCurrentUser() {
        if (!self::isAuthenticated()) {
            return null;
        }
        
        $conn = getDbConnection();
        
        $sql = "SELECT id, username, email, full_name, phone, department_id, job_title, 
                       location_id, role, is_active, last_login, created_at 
                FROM users 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$_SESSION['user_id']]);
        
        return $stmt->fetch();
    }
}