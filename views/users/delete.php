<?php
/**
 * Delete User Handler
 * 
 * This file processes user deletion requests
 */

// Check permissions
if (!hasRole(['admin'])) {
    include 'views/errors/403.php';
    exit;
}

// Get user ID from URL
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Prevent self-deletion
if ($userId === $_SESSION['user_id']) {
    setFlashMessage('You cannot delete your own account.', 'danger');
    redirect(BASE_URL . '/users');
    exit;
}

if (!$userId) {
    setFlashMessage('Invalid user ID', 'danger');
    redirect(BASE_URL . '/users');
    exit;
}

// Get user details
$user = UserHelper::getUserById($userId);

if (!$user) {
    setFlashMessage('User not found', 'danger');
    redirect(BASE_URL . '/users');
    exit;
}

// Process deletion
$result = UserHelper::deleteUser($userId);

if ($result['success']) {
    setFlashMessage($result['message'], 'success');
} else {
    setFlashMessage($result['message'], 'danger');
}

// Redirect back to users list
redirect(BASE_URL . '/users');