<?php
/**
 * Logout Script
 * 
 * This file handles user logout
 */

// Logout the user
$result = AuthHelper::logout();

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    // Parse the cookie value
    list($userId, $token) = explode(':', $_COOKIE['remember_token'], 2);
    
    // Delete token from database
    $conn = getDbConnection();
    $sql = "DELETE FROM remember_tokens WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId]);
    
    // Expire the cookie
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Set success message and redirect
setFlashMessage($result['message'], 'success');
redirect(BASE_URL . '/auth/login');
exit;
?>