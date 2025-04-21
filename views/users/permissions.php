<?php
/**
 * User Permissions Management
 * 
 * This file allows administrators to manage specific permissions for a user
 */

// Set page title
$pageTitle = 'User Permissions';
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

// Get permissions data
$conn = getDbConnection();

// Get all available permissions grouped by category
$allPermissionsSql = "SELECT * FROM permissions ORDER BY category, name";
$allPermissionsStmt = $conn->prepare($allPermissionsSql);
$allPermissionsStmt->execute();
$allPermissions = [];

while ($row = $allPermissionsStmt->fetch()) {
    $category = $row['category'] ?: 'Other';
    if (!isset($allPermissions[$category])) {
        $allPermissions[$category] = [];
    }
    $allPermissions[$category][] = $row;
}

// Get role permissions (base permissions for user's role)
$rolePermissionsSql = "SELECT p.id, p.name 
                      FROM permissions p 
                      JOIN role_permissions rp ON p.id = rp.permission_id 
                      WHERE rp.role = ?";
$rolePermissionsStmt = $conn->prepare($rolePermissionsSql);
$rolePermissionsStmt->execute([$user['role']]);
$rolePermissions = [];

while ($row = $rolePermissionsStmt->fetch()) {
    $rolePermissions[$row['id']] = true;
}

// Get user-specific permission overrides
$userPermissionsSql = "SELECT permission_id, granted 
                       FROM user_permissions 
                       WHERE user_id = ?";
$userPermissionsStmt = $conn->prepare($userPermissionsSql);
$userPermissionsStmt->execute([$userId]);
$userPermissions = [];

while ($row = $userPermissionsStmt->fetch()) {
    $userPermissions[$row['permission_id']] = (bool)$row['granted'];
}

// Include header
include 'includes/header.php';
?>
<style>
    /* User Permissions Page Styles */

/* User Info Header */
.user-permissions-header {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.user-info-card {
    display: flex;
    align-items: center;
    background-color: white;
    border-radius: 0.5rem;
    padding: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    flex: 1;
    min-width: 300px;
}

.user-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 600;
    margin-right: 1rem;
}

.user-info {
    flex: 1;
}

.user-name {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.user-meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.9rem;
}

.user-username {
    color: var(--gray-600);
}

.permissions-info-card {
    flex: 2;
    min-width: 300px;
}

/* Permissions Toolbar */
.permissions-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    background-color: white;
    border-radius: 0.5rem;
    padding: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.permissions-search {
    position: relative;
    flex: 1;
    min-width: 200px;
}

.permissions-search input {
    padding-left: 2.5rem;
    width: 100%;
}

.permissions-search i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-500);
}

.permissions-actions {
    display: flex;
    gap: 0.5rem;
}

/* Permissions Categories */
.permissions-container {
    margin-bottom: 2rem;
}

.permissions-category {
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    margin-bottom: 1rem;
    overflow: hidden;
}

.permissions-category-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background-color: var(--gray-50);
    border-bottom: 1px solid var(--gray-200);
}

.permissions-category-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
}

.category-toggle {
    margin-right: 0.5rem;
    cursor: pointer;
    width: 1rem;
    text-align: center;
}

.permissions-category-actions {
    display: flex;
    gap: 0.5rem;
}

.permissions-list {
    padding: 1rem;
}

/* Individual Permission Items */
.permission-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 1rem;
    border-bottom: 1px solid var(--gray-200);
}

.permission-item:last-child {
    border-bottom: none;
}

.permission-info {
    flex: 1;
    margin-right: 1rem;
}

.permission-name {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 0.25rem 0;
}

.permission-description {
    font-size: 0.9rem;
    color: var(--gray-600);
    margin: 0 0 0.5rem 0;
}

.permission-default {
    font-size: 0.8rem;
    color: var(--gray-500);
}

.text-success {
    color: var(--success);
}

.text-danger {
    color: var(--danger);
}

.text-muted {
    color: var(--gray-500);
}

/* Permission Toggle Controls */
.permission-controls {
    min-width: 150px;
}

.permission-select {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.toggle-display {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 36px;
    padding: 0 1rem;
    border-radius: 0.25rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.toggle-display.default {
    background-color: var(--gray-100);
    color: var(--gray-600);
    border: 1px solid var(--gray-300);
}

.toggle-display.granted {
    background-color: var(--success-light);
    color: var(--success);
    border: 1px solid var(--success);
}

.toggle-display.denied {
    background-color: var(--danger-light);
    color: var(--danger);
    border: 1px solid var(--danger);
}

.toggle-display i {
    margin-right: 0.5rem;
}

.toggle-display .toggle-default,
.toggle-display .toggle-granted,
.toggle-display .toggle-denied {
    display: none;
}

.toggle-display.default .toggle-default,
.toggle-display.granted .toggle-granted,
.toggle-display.denied .toggle-denied {
    display: inline-block;
}

.toggle-text {
    display: none;
}

.toggle-display.default .toggle-text.toggle-default,
.toggle-display.granted .toggle-text.toggle-granted,
.toggle-display.denied .toggle-text.toggle-denied {
    display: inline-block;
}

/* Form Submit Section */
.form-submit {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--gray-200);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .permissions-category-actions {
        display: none;
    }
    
    .permission-item {
        flex-direction: column;
    }
    
    .permission-info {
        margin-right: 0;
        margin-bottom: 1rem;
        width: 100%;
    }
    
    .permission-controls {
        width: 100%;
    }
    
    .toggle-display {
        width: 100%;
    }
}
</style>
<style>
    /* Previous CSS styles */
    
    /* Button Styles Fix */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1rem;
        font-size: 0.9375rem;
        font-weight: 500;
        line-height: 1.5;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        cursor: pointer;
        user-select: none;
        border: 1px solid transparent;
        border-radius: 0.375rem;
        transition: all 0.15s ease-in-out;
    }

    .btn:focus, .btn:hover {
        text-decoration: none;
        outline: 0;
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.15);
    }

    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.8125rem;
        border-radius: 0.25rem;
    }

    .btn-icon {
        margin-right: 0.5rem;
    }

    /* Button Variants */
    .btn-primary {
        color: #fff;
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-primary:hover, .btn-primary:focus {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
    }

    .btn-outline {
        color: var(--gray-700);
        background-color: transparent;
        border-color: var(--gray-300);
    }

    .btn-outline:hover, .btn-outline:focus {
        color: var(--primary);
        border-color: var(--primary);
        background-color: rgba(var(--primary-rgb), 0.05);
    }

    .btn-danger {
        color: #fff;
        background-color: var(--danger);
        border-color: var(--danger);
    }

    .btn-danger:hover, .btn-danger:focus {
        background-color: var(--danger-dark);
        border-color: var(--danger-dark);
    }

    /* Dropdown Styling */
    .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-toggle {
        position: relative;
    }

    .dropdown-toggle::after {
        display: inline-block;
        width: 0;
        height: 0;
        margin-left: 0.5rem;
        vertical-align: middle;
        content: "";
        border-top: 0.3em solid;
        border-right: 0.3em solid transparent;
        border-left: 0.3em solid transparent;
    }

    .dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        z-index: 1000;
        display: none;
        min-width: 180px;
        padding: 0.5rem 0;
        margin: 0.125rem 0 0;
        font-size: 0.9375rem;
        color: var(--gray-700);
        text-align: left;
        list-style: none;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid var(--gray-200);
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }

    .dropdown-menu.show {
        display: block;
    }

    .dropdown-menu-right {
        right: 0;
        left: auto;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        width: 100%;
        padding: 0.5rem 1rem;
        clear: both;
        font-weight: 400;
        color: var(--gray-700);
        text-align: inherit;
        white-space: nowrap;
        background-color: transparent;
        border: 0;
        cursor: pointer;
    }

    .dropdown-item:hover, .dropdown-item:focus {
        color: var(--gray-900);
        text-decoration: none;
        background-color: var(--gray-100);
    }

    .dropdown-icon {
        margin-right: 0.5rem;
        width: 16px;
        text-align: center;
        font-size: 0.875rem;
    }

    .dropdown-divider {
        height: 0;
        margin: 0.5rem 0;
        overflow: hidden;
        border-top: 1px solid var(--gray-200);
    }

    /* Fix for permissions category actions */
    .permissions-category-actions .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8125rem;
        margin-left: 0.25rem;
    }

    /* Fix for toggle display in permission items */
    .toggle-display {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 36px;
        padding: 0 0.75rem;
        border-radius: 0.25rem;
        cursor: pointer;
        transition: all 0.2s ease;
        width: 100%;
    }

    /* Form submit buttons */
    .form-submit .btn {
        min-width: 120px;
    }

    /* For mobile devices */
    @media (max-width: 768px) {
        .btn {
            padding: 0.4375rem 0.875rem;
        }
        
        .permissions-toolbar {
            flex-direction: column;
            align-items: stretch;
        }
        
        .permissions-actions {
            display: flex;
            width: 100%;
            margin-top: 1rem;
        }
        
        .permissions-actions .btn {
            flex: 1;
        }
        
        .dropdown-menu {
            width: 100%;
        }
    }
</style>
<div class="content-header">
    <h1 class="page-title">User Permissions</h1>
    <div class="breadcrumbs">
        <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Home</a>
        <a href="<?php echo BASE_URL; ?>/users" class="breadcrumb-item">Users</a>
        <a href="<?php echo BASE_URL; ?>/users/view?id=<?php echo $user['id']; ?>" class="breadcrumb-item">User Details</a>
        <span class="breadcrumb-item">Permissions</span>
    </div>
</div>

<div class="user-permissions-header">
    <div class="user-info-card">
        <div class="user-avatar">
            <?php
            $initials = '';
            $nameParts = explode(' ', $user['full_name']);
            foreach ($nameParts as $part) {
                if (!empty($part)) {
                    $initials .= strtoupper(substr($part, 0, 1));
                }
            }
            echo htmlspecialchars(substr($initials, 0, 2));
            ?>
        </div>
        <div class="user-info">
            <h3 class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></h3>
            <div class="user-meta">
                <span class="badge <?php echo ($user['role'] === 'admin') ? 'badge-red' : (($user['role'] === 'manager') ? 'badge-orange' : 'badge-blue'); ?>">
                    <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                </span>
                <span class="user-username"><?php echo htmlspecialchars($user['username']); ?></span>
            </div>
        </div>
    </div>
    
    <div class="permissions-info-card">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> <strong>About Permissions</strong>
            <p>Each user inherits default permissions from their role (<?php echo ucfirst($user['role']); ?>). You can override specific permissions below.</p>
            <p><i class="fas fa-check-circle text-success"></i> Green toggles grant a permission, <i class="fas fa-times-circle text-danger"></i> red toggles deny it, and <i class="fas fa-circle text-muted"></i> gray toggles use role defaults.</p>
        </div>
    </div>
</div>

<form action="<?php echo BASE_URL; ?>/users/permissions-save" method="POST" id="permissions-form">
    <!-- CSRF Token -->
    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
    
    <div class="permissions-toolbar">
        <div class="permissions-search">
            <input type="text" class="form-control" id="permission-search" placeholder="Search permissions...">
            <i class="fas fa-search"></i>
        </div>
        <div class="permissions-actions">
            <button type="button" class="btn btn-sm btn-outline" id="reset-all-permissions">
                <i class="fas fa-undo btn-icon"></i> Reset All to Defaults
            </button>
            <div class="dropdown">
                <button type="button" class="btn btn-sm btn-outline dropdown-toggle" id="bulk-actions-dropdown">
                    <i class="fas fa-cog btn-icon"></i> Bulk Actions
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <button type="button" class="dropdown-item" id="grant-all-permissions">
                        <i class="fas fa-check-circle dropdown-icon text-success"></i> Grant All Permissions
                    </button>
                    <button type="button" class="dropdown-item" id="deny-all-permissions">
                        <i class="fas fa-times-circle dropdown-icon text-danger"></i> Deny All Permissions
                    </button>
                    <div class="dropdown-divider"></div>
                    <button type="button" class="dropdown-item" id="expand-all-categories">
                        <i class="fas fa-plus-square dropdown-icon"></i> Expand All Categories
                    </button>
                    <button type="button" class="dropdown-item" id="collapse-all-categories">
                        <i class="fas fa-minus-square dropdown-icon"></i> Collapse All Categories
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="permissions-container">
        <?php foreach ($allPermissions as $category => $permissions): ?>
        <div class="permissions-category">
            <div class="permissions-category-header">
                <h3 class="permissions-category-title">
                    <i class="fas fa-caret-down category-toggle"></i> <?php echo htmlspecialchars($category); ?>
                </h3>
                <div class="permissions-category-actions">
                    <button type="button" class="btn btn-sm btn-outline grant-category" data-category="<?php echo htmlspecialchars($category); ?>">
                        Grant All
                    </button>
                    <button type="button" class="btn btn-sm btn-outline deny-category" data-category="<?php echo htmlspecialchars($category); ?>">
                        Deny All
                    </button>
                    <button type="button" class="btn btn-sm btn-outline reset-category" data-category="<?php echo htmlspecialchars($category); ?>">
                        Reset to Default
                    </button>
                </div>
            </div>
            <div class="permissions-list">
                <?php foreach ($permissions as $permission): ?>
                <div class="permission-item" data-permission-name="<?php echo htmlspecialchars($permission['name']); ?>" data-category="<?php echo htmlspecialchars($category); ?>">
                    <div class="permission-info">
                        <h4 class="permission-name"><?php echo htmlspecialchars($permission['name']); ?></h4>
                        <p class="permission-description"><?php echo htmlspecialchars($permission['description']); ?></p>
                        <?php if (isset($rolePermissions[$permission['id']])): ?>
                        <span class="permission-default">Default: <span class="text-success">Granted</span> by <?php echo ucfirst($user['role']); ?> role</span>
                        <?php else: ?>
                        <span class="permission-default">Default: <span class="text-danger">Denied</span> by <?php echo ucfirst($user['role']); ?> role</span>
                        <?php endif; ?>
                    </div>
                    <div class="permission-controls">
                        <div class="permission-toggle">
                            <?php
                            $hasOverride = isset($userPermissions[$permission['id']]);
                            $isGranted = $hasOverride ? $userPermissions[$permission['id']] : (isset($rolePermissions[$permission['id']]) ? true : false);
                            $toggleState = $hasOverride ? ($isGranted ? 'granted' : 'denied') : 'default';
                            ?>
                            <select name="permissions[<?php echo $permission['id']; ?>]" class="permission-select" data-permission-id="<?php echo $permission['id']; ?>">
                                <option value="default" <?php echo $toggleState === 'default' ? 'selected' : ''; ?>>Default</option>
                                <option value="granted" <?php echo $toggleState === 'granted' ? 'selected' : ''; ?>>Grant</option>
                                <option value="denied" <?php echo $toggleState === 'denied' ? 'selected' : ''; ?>>Deny</option>
                            </select>
                            <div class="toggle-display <?php echo $toggleState; ?>">
                                <i class="fas fa-circle toggle-default"></i>
                                <i class="fas fa-check-circle toggle-granted"></i>
                                <i class="fas fa-times-circle toggle-denied"></i>
                                <span class="toggle-text toggle-default">Default</span>
                                <span class="toggle-text toggle-granted">Granted</span>
                                <span class="toggle-text toggle-denied">Denied</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="form-submit">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save btn-icon"></i> Save Permissions
        </button>
        <a href="<?php echo BASE_URL; ?>/users/view?id=<?php echo $user['id']; ?>" class="btn btn-outline">Cancel</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle categories
    document.querySelectorAll('.category-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const permissionsList = this.closest('.permissions-category').querySelector('.permissions-list');
            const isExpanded = permissionsList.style.display !== 'none';
            
            if (isExpanded) {
                permissionsList.style.display = 'none';
                this.classList.remove('fa-caret-down');
                this.classList.add('fa-caret-right');
            } else {
                permissionsList.style.display = 'block';
                this.classList.remove('fa-caret-right');
                this.classList.add('fa-caret-down');
            }
        });
    });
    
    // Permission toggle functionality
    document.querySelectorAll('.permission-select').forEach(function(select) {
        select.addEventListener('change', function() {
            const toggleDisplay = this.nextElementSibling;
            toggleDisplay.className = 'toggle-display ' + this.value;
        });
    });
    
    // Search functionality
    const searchInput = document.getElementById('permission-search');
    searchInput.addEventListener('input', function() {
        const searchValue = this.value.toLowerCase();
        
        document.querySelectorAll('.permission-item').forEach(function(item) {
            const permissionName = item.getAttribute('data-permission-name').toLowerCase();
            const permissionDescription = item.querySelector('.permission-description').textContent.toLowerCase();
            
            if (permissionName.includes(searchValue) || permissionDescription.includes(searchValue)) {
                item.style.display = 'flex';
                
                // Make sure the category is expanded
                const category = item.closest('.permissions-category');
                const permissionsList = category.querySelector('.permissions-list');
                const toggle = category.querySelector('.category-toggle');
                
                permissionsList.style.display = 'block';
                toggle.classList.remove('fa-caret-right');
                toggle.classList.add('fa-caret-down');
            } else {
                item.style.display = 'none';
            }
        });
        
        // Hide empty categories
        document.querySelectorAll('.permissions-category').forEach(function(category) {
            const visibleItems = category.querySelectorAll('.permission-item[style="display: flex"]').length;
            
            if (visibleItems === 0 && searchValue !== '') {
                category.style.display = 'none';
            } else {
                category.style.display = 'block';
            }
        });
    });
    
    // Bulk actions
    // Reset all permissions to default
    document.getElementById('reset-all-permissions').addEventListener('click', function() {
        if (confirm('Reset all permissions to role defaults?')) {
            document.querySelectorAll('.permission-select').forEach(function(select) {
                select.value = 'default';
                select.dispatchEvent(new Event('change'));
            });
        }
    });
    
    // Grant all permissions
    document.getElementById('grant-all-permissions').addEventListener('click', function() {
        if (confirm('Grant ALL permissions to this user?')) {
            document.querySelectorAll('.permission-select').forEach(function(select) {
                select.value = 'granted';
                select.dispatchEvent(new Event('change'));
            });
        }
    });
    
    // Deny all permissions
    document.getElementById('deny-all-permissions').addEventListener('click', function() {
        if (confirm('Deny ALL permissions for this user?')) {
            document.querySelectorAll('.permission-select').forEach(function(select) {
                select.value = 'denied';
                select.dispatchEvent(new Event('change'));
            });
        }
    });
    
    // Expand all categories
    document.getElementById('expand-all-categories').addEventListener('click', function() {
        document.querySelectorAll('.permissions-list').forEach(function(list) {
            list.style.display = 'block';
        });
        
        document.querySelectorAll('.category-toggle').forEach(function(toggle) {
            toggle.classList.remove('fa-caret-right');
            toggle.classList.add('fa-caret-down');
        });
    });
    
    // Collapse all categories
    document.getElementById('collapse-all-categories').addEventListener('click', function() {
        document.querySelectorAll('.permissions-list').forEach(function(list) {
            list.style.display = 'none';
        });
        
        document.querySelectorAll('.category-toggle').forEach(function(toggle) {
            toggle.classList.remove('fa-caret-down');
            toggle.classList.add('fa-caret-right');
        });
    });
    
    // Category specific actions
    // Grant all in category
    document.querySelectorAll('.grant-category').forEach(function(button) {
        button.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            const selects = document.querySelectorAll(`.permission-item[data-category="${category}"] .permission-select`);
            
            selects.forEach(function(select) {
                select.value = 'granted';
                select.dispatchEvent(new Event('change'));
            });
        });
    });
    
    // Deny all in category
    document.querySelectorAll('.deny-category').forEach(function(button) {
        button.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            const selects = document.querySelectorAll(`.permission-item[data-category="${category}"] .permission-select`);
            
            selects.forEach(function(select) {
                select.value = 'denied';
                select.dispatchEvent(new Event('change'));
            });
        });
    });
    
    // Reset all in category
    document.querySelectorAll('.reset-category').forEach(function(button) {
        button.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            const selects = document.querySelectorAll(`.permission-item[data-category="${category}"] .permission-select`);
            
            selects.forEach(function(select) {
                select.value = 'default';
                select.dispatchEvent(new Event('change'));
            });
        });
    });
    
    // Initialize dropdown
    const bulkActionsDropdown = document.getElementById('bulk-actions-dropdown');
    const dropdownMenu = bulkActionsDropdown.nextElementSibling;
    
    bulkActionsDropdown.addEventListener('click', function() {
        dropdownMenu.classList.toggle('show');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.matches('#bulk-actions-dropdown') && !event.target.closest('.dropdown-menu')) {
            dropdownMenu.classList.remove('show');
        }
    });
    
    // Form submission
    document.getElementById('permissions-form').addEventListener('submit', function() {
        document.getElementById('bulk-actions-dropdown').disabled = true;
        document.querySelectorAll('.permissions-category-actions button').forEach(function(button) {
            button.disabled = true;
        });
    });
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?>