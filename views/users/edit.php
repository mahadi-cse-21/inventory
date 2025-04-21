<?php
/**
 * User Edit View
 * 
 * This file provides a form to edit existing user accounts
 */

// Set page title
$pageTitle = 'Edit User';
$bodyClass = 'users-page';

// Check permissions
if (!hasRole(['admin'])) {
    include 'views/errors/403.php';
    exit;
}

// Get user ID from query parameter
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$userId) {
    setFlashMessage('Invalid user ID.', 'danger');
    redirect(BASE_URL . '/users');
}

// Get user data
$user = UserHelper::getUserById($userId);

if (!$user) {
    setFlashMessage('User not found.', 'danger');
    redirect(BASE_URL . '/users');
}

// Get departments for dropdown
$departments = UserHelper::getAllDepartments();

// Get locations for dropdown
$conn = getDbConnection();
$locationSql = "SELECT * FROM locations WHERE is_active = 1 ORDER BY name ASC";
$locationStmt = $conn->prepare($locationSql);
$locationStmt->execute();
$locations = $locationStmt->fetchAll();

// Include header
include 'includes/header.php';
?>
<link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/users.css">

<div class="content-header">
    <h1 class="page-title">Edit User: <?php echo htmlspecialchars($user['username']); ?></h1>
    <div class="breadcrumbs">
        <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Home</a>
        <a href="<?php echo BASE_URL; ?>/users" class="breadcrumb-item">Users</a>
        <span class="breadcrumb-item">Edit</span>
    </div>
</div>

<div class="page-actions">
    <a href="<?php echo BASE_URL; ?>/users/view?id=<?php echo $user['id']; ?>" class="btn btn-outline">
        <i class="fas fa-eye btn-icon"></i> View Profile
    </a>
    <?php if ($user['id'] != $_SESSION['user_id']): ?>
    <button class="btn btn-danger" onclick="confirmDeleteUser()">
        <i class="fas fa-trash-alt btn-icon"></i> Delete User
    </button>
    <?php endif; ?>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">User Information</div>
        <a href="<?php echo BASE_URL; ?>/users" class="btn btn-sm btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>
    <div class="panel-body">
        <form action="<?php echo BASE_URL; ?>/users/edit-process" method="POST" id="edit-user-form">
            <!-- CSRF Token -->
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            
            <div class="form-tabs">
                <div class="form-tab active" data-tab="account">Account Details</div>
                <div class="form-tab" data-tab="personal">Personal Information</div>
                <div class="form-tab" data-tab="role">Role & Status</div>
            </div>
            
            <div class="form-tab-content active" id="account-tab">
                <div class="form-section">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="username" class="required">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required pattern="[a-zA-Z0-9_]{3,20}" title="Username must be 3-20 characters and can only contain letters, numbers, and underscores">
                            <div class="form-text">3-20 characters, letters, numbers, and underscores only</div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="email" class="required">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="password">Password</label>
                            <div class="password-input">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                                <button type="button" class="password-toggle" tabindex="-1">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Minimum 8 characters. Leave blank to keep current password.</div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="confirm_password">Confirm Password</label>
                            <div class="password-input">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Leave blank to keep current password">
                                <button type="button" class="password-toggle" tabindex="-1">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($user['account_locked']): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> This account is currently locked due to too many failed login attempts.
                        <button type="button" class="btn btn-sm btn-warning" onclick="unlockAccount(<?php echo $user['id']; ?>)">
                            <i class="fas fa-unlock"></i> Unlock Account
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($user['password_reset_token'] && $user['password_reset_expires'] > date('Y-m-d H:i:s')): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Password reset is pending for this user (expires <?php echo UtilityHelper::formatDateForDisplay($user['password_reset_expires'], 'datetime'); ?>).
                    </div>
                    <?php endif; ?>
                </div>
                <div class="form-buttons">
                    <button type="button" class="btn btn-primary next-tab">Next: Personal Information</button>
                </div>
            </div>
            
            <div class="form-tab-content" id="personal-tab">
                <div class="form-section">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="full_name" class="required">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="phone">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="department_id">Department</label>
                            <select class="form-control" id="department_id" name="department_id">
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $department): ?>
                                <option value="<?php echo $department['id']; ?>" <?php echo ($user['department_id'] == $department['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($department['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="job_title">Job Title</label>
                            <input type="text" class="form-control" id="job_title" name="job_title" value="<?php echo htmlspecialchars($user['job_title'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="location_id">Location</label>
                            <select class="form-control" id="location_id" name="location_id">
                                <option value="">Select Location</option>
                                <?php foreach ($locations as $location): ?>
                                <option value="<?php echo $location['id']; ?>" <?php echo ($user['location_id'] == $location['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($location['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-buttons">
                    <button type="button" class="btn btn-outline prev-tab">Previous</button>
                    <button type="button" class="btn btn-primary next-tab">Next: Role & Status</button>
                </div>
            </div>
            
            <div class="form-tab-content" id="role-tab">
                <div class="form-section">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="role" class="required">User Role</label>
                            <select class="form-control" id="role" name="role" required <?php echo ($user['id'] == $_SESSION['user_id']) ? 'disabled' : ''; ?>>
                                <option value="user" <?php echo ($user['role'] == 'user') ? 'selected' : ''; ?>>Regular User</option>
                                <option value="manager" <?php echo ($user['role'] == 'manager') ? 'selected' : ''; ?>>Manager</option>
                                <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Administrator</option>
                            </select>
                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                            <div class="form-text text-danger">You cannot change your own role.</div>
                            <input type="hidden" name="role" value="<?php echo $user['role']; ?>">
                            <?php endif; ?>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="is_active">Account Status</label>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="is_active" name="is_active" value="1" <?php echo ($user['is_active'] == 1) ? 'checked' : ''; ?> <?php echo ($user['id'] == $_SESSION['user_id']) ? 'disabled' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Active Account</span>
                            </div>
                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                            <div class="form-text text-danger">You cannot deactivate your own account.</div>
                            <input type="hidden" name="is_active" value="1">
                            <?php else: ?>
                            <div class="form-text">Inactive accounts cannot log in</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> User permissions are determined by their role. To customize individual permissions, use the 
                        <a href="<?php echo BASE_URL; ?>/users/permissions?id=<?php echo $user['id']; ?>">User Permissions</a> page.
                    </div>
                </div>
                <div class="form-buttons">
                    <button type="button" class="btn btn-outline prev-tab">Previous</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Account Activity Panel -->
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Account Activity</div>
    </div>
    <div class="panel-body">
        <div class="info-grid-2col">
            <div class="info-item">
                <div class="info-label">Last Login</div>
                <div class="info-value"><?php echo $user['last_login'] ? UtilityHelper::formatDateForDisplay($user['last_login'], 'datetime') : 'Never'; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Created On</div>
                <div class="info-value"><?php echo UtilityHelper::formatDateForDisplay($user['created_at'], 'datetime'); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Failed Login Attempts</div>
                <div class="info-value"><?php echo (int)$user['failed_login_attempts']; ?></div>
            </div>
            <?php if ($user['created_by']): ?>
            <div class="info-item">
                <div class="info-label">Created By</div>
                <div class="info-value">
                    <?php 
                    $createdBy = UserHelper::getUserById($user['created_by']);
                    echo $createdBy ? htmlspecialchars($createdBy['username']) : 'Unknown';
                    ?>
                </div>
            </div>
            <?php endif; ?>
            <div class="info-item">
                <div class="info-label">Last Updated</div>
                <div class="info-value"><?php echo $user['updated_at'] ? UtilityHelper::formatDateForDisplay($user['updated_at'], 'datetime') : 'Never'; ?></div>
            </div>
            <?php if ($user['updated_by']): ?>
            <div class="info-item">
                <div class="info-label">Updated By</div>
                <div class="info-value">
                    <?php 
                    $updatedBy = UserHelper::getUserById($user['updated_by']);
                    echo $updatedBy ? htmlspecialchars($updatedBy['username']) : 'Unknown';
                    ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-backdrop" id="delete-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Confirm Delete</div>
            <button class="modal-close" id="close-delete-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete the user <strong><?php echo htmlspecialchars($user['username']); ?></strong>?</p>
            <p>This action cannot be undone and will remove all data associated with this user account.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="cancel-delete">Cancel</button>
            <a href="<?php echo BASE_URL; ?>/users/delete?id=<?php echo $user['id']; ?>" class="btn btn-danger">
                <i class="fas fa-trash-alt btn-icon"></i> Delete User
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab Navigation
    const tabs = document.querySelectorAll('.form-tab');
    const tabContents = document.querySelectorAll('.form-tab-content');
    const nextButtons = document.querySelectorAll('.next-tab');
    const prevButtons = document.querySelectorAll('.prev-tab');
    
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => {
            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            tabs.forEach(t => {
                t.classList.remove('active');
            });
            
            // Show the selected tab content
            tabContents[index].classList.add('active');
            
            // Add active class to the clicked tab
            tab.classList.add('active');
        });
    });
    
    nextButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Find the currently active tab index
            let activeIndex = Array.from(tabs).findIndex(tab => tab.classList.contains('active'));
            
            // If there's a next tab, activate it
            if (activeIndex < tabs.length - 1) {
                tabs[activeIndex + 1].click();
            }
        });
    });
    
    prevButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Find the currently active tab index
            let activeIndex = Array.from(tabs).findIndex(tab => tab.classList.contains('active'));
            
            // If there's a previous tab, activate it
            if (activeIndex > 0) {
                tabs[activeIndex - 1].click();
            }
        });
    });
    
    // Form validation
    const form = document.getElementById('edit-user-form');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    form.addEventListener('submit', function(event) {
        if (password.value !== confirmPassword.value) {
            event.preventDefault();
            alert('Passwords do not match!');
            confirmPassword.focus();
        }
        
        if (password.value !== '' && password.value.length < 8) {
            event.preventDefault();
            alert('Password must be at least 8 characters long!');
            password.focus();
        }
    });
    
    // Password toggle
    const toggleButtons = document.querySelectorAll('.password-toggle');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Delete modal
    const deleteModal = document.getElementById('delete-modal');
    const closeDeleteModal = document.getElementById('close-delete-modal');
    const cancelDelete = document.getElementById('cancel-delete');
    
    window.confirmDeleteUser = function() {
        deleteModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };
    
    function closeDeleteUserModal() {
        deleteModal.style.display = 'none';
        document.body.style.overflow = '';
    }
    
    closeDeleteModal.addEventListener('click', closeDeleteUserModal);
    cancelDelete.addEventListener('click', closeDeleteUserModal);
    
    // Close modal when clicking outside
    deleteModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteUserModal();
        }
    });
    
    // Unlock account function
    window.unlockAccount = function(userId) {
        if (confirm('Are you sure you want to unlock this account?')) {
            fetch('<?php echo BASE_URL; ?>/users/unlock-account', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `user_id=${userId}&<?php echo CSRF_TOKEN_NAME; ?>=<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Account unlocked successfully!');
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    };
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?>