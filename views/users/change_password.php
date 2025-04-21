<?php
/**
 * Change Password View
 * 
 * This file allows users to change their password
 */

// Set page title
$pageTitle = 'Change Password';

// Check if user is logged in
if (!AuthHelper::isAuthenticated()) {
    setFlashMessage('You must be logged in to change your password.', 'danger');
    redirect(BASE_URL . '/auth/login');
}

// Get current user
$currentUser = AuthHelper::getCurrentUser();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    validateCsrfToken();
    
    // Get form data
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    $errors = [];
    
    if (empty($currentPassword)) {
        $errors[] = 'Current password is required';
    }
    
    if (empty($newPassword)) {
        $errors[] = 'New password is required';
    } elseif (strlen($newPassword) < 8) {
        $errors[] = 'New password must be at least 8 characters long';
    }
    
    if ($newPassword !== $confirmPassword) {
        $errors[] = 'New password and confirmation do not match';
    }
    
    // If no errors, update password
    if (empty($errors)) {
        $result = AuthHelper::changePassword($currentUser['id'], $currentPassword, $newPassword);
        
        if ($result['success']) {
            setFlashMessage('Password changed successfully', 'success');
            redirect(BASE_URL . '/users/profile');
        } else {
            setFlashMessage($result['message'], 'danger');
        }
    } else {
        // Show validation errors
        setFlashMessage(implode('<br>', $errors), 'danger');
    }
}

// Include header
include 'includes/header.php';
?>

<h1 class="page-title">Change Password</h1>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Change Your Password</div>
    </div>
    <div class="panel-body">
        <form method="POST" action="<?php echo BASE_URL; ?>/users/change-password">
            <!-- CSRF Token -->
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
            
            <div class="form-group">
                <label class="form-label" for="current_password">Current Password <span style="color: var(--danger);">*</span></label>
                <input type="password" class="form-control" id="current_password" name="current_password" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="new_password">New Password <span style="color: var(--danger);">*</span></label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
                <div class="form-hint">Password must be at least 8 characters long</div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm New Password <span style="color: var(--danger);">*</span></label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-key btn-icon"></i> Change Password
                </button>
                <a href="<?php echo BASE_URL; ?>/users/profile" class="btn btn-outline">
                    <i class="fas fa-times btn-icon"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<div class="panel" style="margin-top: 1.5rem;">
    <div class="panel-header">
        <div class="panel-title">Password Security Tips</div>
    </div>
    <div class="panel-body">
        <ul>
            <li>Use a combination of uppercase and lowercase letters, numbers, and special characters</li>
            <li>Avoid using common words or personal information</li>
            <li>Use a unique password that you don't use for other accounts</li>
            <li>Change your password regularly for better security</li>
            <li>Never share your password with others</li>
        </ul>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>