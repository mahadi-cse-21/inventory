<?php

//require_once(__DIR__ . '/../init.php');

/**
 * Cancel Borrow Request
 * 
 * This file handles cancellation of a pending borrow request
 */

// Check if user is logged in
if (!AuthHelper::isAuthenticated()) {
    setFlashMessage('You must be logged in to cancel a borrow request.', 'danger');
    redirect(BASE_URL . '/auth/login');
    exit;
}

// Get current user
$currentUser = AuthHelper::getCurrentUser();

// Get request ID
$requestId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$requestId) {
    setFlashMessage('Invalid request ID', 'danger');
    redirect(BASE_URL . '/borrow/history');
    exit;
}

// Get request details
$request = BorrowHelper::getBorrowRequestById($requestId);

// Check if request exists
if (!$request) {
    setFlashMessage('Borrow request not found', 'danger');
    redirect(BASE_URL . '/borrow/history');
    exit;
}

// Check if user has permission to cancel this request
$hasPermission = ($request['user_id'] == $currentUser['id']) || 
                hasRole(['admin']);

if (!$hasPermission) {
    setFlashMessage('You do not have permission to cancel this request', 'danger');
    redirect(BASE_URL . '/borrow/history');
    exit;
}

// Check if request is in a status that can be cancelled
if ($request['status'] !== 'pending') {
    setFlashMessage('Only pending requests can be cancelled', 'warning');
    redirect(BASE_URL . '/borrow/view?id=' . $requestId);
    exit;
}

// Cancel the request
$result = BorrowHelper::updateBorrowRequestStatus($requestId, 'rejected', $currentUser['id']);

 // optional: prevent further execution during debugging

if ($result['success']) {
    setFlashMessage('Borrow request cancelled successfully', 'success');
} else {
    setFlashMessage($result['message'], 'danger');
}

// Redirect back to history page
redirect(BASE_URL . '/borrow/history');