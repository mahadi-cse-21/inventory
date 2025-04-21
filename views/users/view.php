<?php
/**
 * User View Details
 * 
 * This file displays detailed information about a user
 */

// Set page title
$pageTitle = 'User Details';
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

// Get user permissions
$permissions = UserHelper::getUserPermissions($userId);

// Get user borrowing statistics
$conn = getDbConnection();

// Count currently borrowed items
$borrowedSql = "SELECT COUNT(*) FROM borrowed_items bi 
               JOIN borrow_requests br ON bi.borrow_request_id = br.id 
               WHERE br.user_id = ? AND br.status = 'checked_out' AND bi.is_returned = 0";
$borrowedStmt = $conn->prepare($borrowedSql);
$borrowedStmt->execute([$userId]);
$borrowedCount = (int)$borrowedStmt->fetchColumn();

// Count total borrow requests
$requestsSql = "SELECT COUNT(*) FROM borrow_requests WHERE user_id = ?";
$requestsStmt = $conn->prepare($requestsSql);
$requestsStmt->execute([$userId]);
$requestsCount = (int)$requestsStmt->fetchColumn();

// Get last 5 borrowed items
$recentBorrowsSql = "SELECT i.name as item_name, i.asset_id, br.borrow_date, br.return_date, br.actual_return_date, br.status
                     FROM borrow_requests br
                     JOIN borrowed_items bi ON br.id = bi.borrow_request_id
                     JOIN items i ON bi.item_id = i.id
                     WHERE br.user_id = ?
                     ORDER BY br.created_at DESC
                     LIMIT 5";
$recentBorrowsStmt = $conn->prepare($recentBorrowsSql);
$recentBorrowsStmt->execute([$userId]);
$recentBorrows = $recentBorrowsStmt->fetchAll();

// Get recent activity
$activityLogs = UserHelper::getUserActivityLogs($userId, 1, 5)['logs'];

// Include header
include 'includes/header.php';
?>

<!-- Include user management specific styles -->
<link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/users.css">
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
    <h1 class="page-title">User Details</h1>
    <div class="breadcrumbs">
        <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Home</a>
        <a href="<?php echo BASE_URL; ?>/users" class="breadcrumb-item">Users</a>
        <span class="breadcrumb-item">View</span>
    </div>
</div>

<div class="user-profile-container">
    <div class="user-profile-header">
        <div class="user-profile-avatar">
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
        <div class="user-profile-info">
            <h2 class="user-profile-name"><?php echo htmlspecialchars($user['full_name']); ?></h2>
            <div class="user-profile-meta">
                <span class="user-profile-username"><?php echo htmlspecialchars($user['username']); ?></span>
                <span class="user-profile-divider">•</span>
                <span class="badge <?php echo ($user['role'] === 'admin') ? 'badge-red' : (($user['role'] === 'manager') ? 'badge-orange' : 'badge-blue'); ?>">
                    <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                </span>
                <span class="user-profile-divider">•</span>
                <span class="status-indicator <?php echo ($user['is_active'] == 1) ? 'status-active' : 'status-inactive'; ?>">
                    <?php echo ($user['is_active'] == 1) ? 'Active' : 'Inactive'; ?>
                </span>
            </div>
            <div class="user-profile-contact">
                <div class="user-contact-item">
                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                </div>
                <?php if (!empty($user['phone'])): ?>
                <div class="user-contact-item">
                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone']); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="user-profile-actions">
            <a href="<?php echo BASE_URL; ?>/users/edit?id=<?php echo $user['id']; ?>" class="btn btn-primary">
                <i class="fas fa-edit btn-icon"></i> Edit User
            </a>
            <div class="dropdown">
                <button class="btn btn-outline dropdown-toggle">
                    <i class="fas fa-ellipsis-v btn-icon"></i> More Actions
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="<?php echo BASE_URL; ?>/users/permissions?id=<?php echo $user['id']; ?>" class="dropdown-item">
                        <i class="fas fa-key dropdown-icon"></i> Edit Permissions
                    </a>
                    <a href="<?php echo BASE_URL; ?>/borrow/history?user_id=<?php echo $user['id']; ?>" class="dropdown-item">
                        <i class="fas fa-history dropdown-icon"></i> Borrow History
                    </a>
                    <?php if ($user['account_locked']): ?>
                    <button class="dropdown-item" onclick="unlockAccount(<?php echo $user['id']; ?>)">
                        <i class="fas fa-unlock dropdown-icon"></i> Unlock Account
                    </button>
                    <?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                    <button class="dropdown-item text-danger" onclick="confirmDeleteUser()">
                        <i class="fas fa-trash-alt dropdown-icon"></i> Delete User
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="user-stats-row">
        <div class="user-stat-card">
            <div class="stat-icon"><i class="fas fa-box"></i></div>
            <div class="stat-value"><?php echo $borrowedCount; ?></div>
            <div class="stat-label">Currently Borrowed</div>
        </div>
        <div class="user-stat-card">
            <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-value"><?php echo $requestsCount; ?></div>
            <div class="stat-label">Total Requests</div>
        </div>
        <div class="user-stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-value"><?php echo UtilityHelper::formatDateForDisplay($user['created_at'], 'short'); ?></div>
            <div class="stat-label">Member Since</div>
        </div>
        <div class="user-stat-card">
            <div class="stat-icon"><i class="fas fa-sign-in-alt"></i></div>
            <div class="stat-value"><?php echo $user['last_login'] ? UtilityHelper::formatDateForDisplay($user['last_login'], 'short') : 'Never'; ?></div>
            <div class="stat-label">Last Login</div>
        </div>
    </div>
    
    <div class="user-profile-content">
        <div class="row">
            <div class="col-md-6">
                <!-- Personal Information -->
                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title">Personal Information</div>
                    </div>
                    <div class="panel-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Full Name</div>
                                <div class="info-value"><?php echo htmlspecialchars($user['full_name']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Email</div>
                                <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Phone</div>
                                <div class="info-value"><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not provided'; ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Department</div>
                                <div class="info-value"><?php echo !empty($user['department_name']) ? htmlspecialchars($user['department_name']) : 'Not assigned'; ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Job Title</div>
                                <div class="info-value"><?php echo !empty($user['job_title']) ? htmlspecialchars($user['job_title']) : 'Not provided'; ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Location</div>
                                <div class="info-value"><?php echo !empty($user['location_name']) ? htmlspecialchars($user['location_name']) : 'Not assigned'; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Account Information -->
                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title">Account Information</div>
                    </div>
                    <div class="panel-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Username</div>
                                <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Role</div>
                                <div class="info-value">
                                    <span class="badge <?php echo ($user['role'] === 'admin') ? 'badge-red' : (($user['role'] === 'manager') ? 'badge-orange' : 'badge-blue'); ?>">
                                        <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Status</div>
                                <div class="info-value">
                                    <span class="status-indicator <?php echo ($user['is_active'] == 1) ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo ($user['is_active'] == 1) ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Account Status</div>
                                <div class="info-value">
                                    <?php if ($user['account_locked']): ?>
                                    <span class="status-indicator status-danger">Locked</span>
                                    <button class="btn btn-sm btn-outline" onclick="unlockAccount(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-unlock"></i> Unlock
                                    </button>
                                    <?php else: ?>
                                    <span class="status-indicator status-success">Normal</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Failed Login Attempts</div>
                                <div class="info-value"><?php echo (int)$user['failed_login_attempts']; ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Last Login</div>
                                <div class="info-value"><?php echo $user['last_login'] ? UtilityHelper::formatDateForDisplay($user['last_login'], 'datetime') : 'Never'; ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Account Created</div>
                                <div class="info-value"><?php echo UtilityHelper::formatDateForDisplay($user['created_at'], 'datetime'); ?></div>
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
                            <?php if ($user['updated_at']): ?>
                            <div class="info-item">
                                <div class="info-label">Last Updated</div>
                                <div class="info-value"><?php echo UtilityHelper::formatDateForDisplay($user['updated_at'], 'datetime'); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <!-- Recent Borrows -->
                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title">Recent Borrow Activity</div>
                        <a href="<?php echo BASE_URL; ?>/borrow/history?user_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline">
                            View All
                        </a>
                    </div>
                    <div class="panel-body">
                        <?php if (count($recentBorrows) > 0): ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Borrow Date</th>
                                        <th>Return Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentBorrows as $borrow): ?>
                                    <tr>
                                        <td>
                                            <div class="item-info">
                                                <i class="fas fa-box"></i>
                                                <div>
                                                    <div class="item-name"><?php echo htmlspecialchars($borrow['item_name']); ?></div>
                                                    <?php if (!empty($borrow['asset_id'])): ?>
                                                    <div class="item-id">ID: <?php echo htmlspecialchars($borrow['asset_id']); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo UtilityHelper::formatDateForDisplay($borrow['borrow_date'], 'short'); ?></td>
                                        <td>
                                            <?php if ($borrow['status'] === 'returned'): ?>
                                                <span class="text-success"><?php echo UtilityHelper::formatDateForDisplay($borrow['actual_return_date'], 'short'); ?></span>
                                            <?php else: ?>
                                                <?php echo UtilityHelper::formatDateForDisplay($borrow['return_date'], 'short'); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = 'status-default';
                                            switch ($borrow['status']) {
                                                case 'returned':
                                                    $statusClass = 'status-success';
                                                    break;
                                                case 'checked_out':
                                                    $statusClass = 'status-primary';
                                                    break;
                                                case 'overdue':
                                                    $statusClass = 'status-danger';
                                                    break;
                                                case 'approved':
                                                case 'pending':
                                                    $statusClass = 'status-warning';
                                                    break;
                                            }
                                            ?>
                                            <span class="status-indicator <?php echo $statusClass; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $borrow['status'])); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> This user has no borrow history.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title">Recent Activity</div>
                    </div>
                    <div class="panel-body">
                        <?php if (count($activityLogs) > 0): ?>
                        <ul class="activity-list">
                            <?php foreach ($activityLogs as $log): ?>
                            <li class="activity-item">
                                <?php
                                // Determine icon based on action
                                $iconClass = 'info-circle';
                                $iconColor = 'blue';
                                
                                if (strpos($log['action'], 'login') !== false) {
                                    $iconClass = 'sign-in-alt';
                                    $iconColor = 'purple';
                                } elseif (strpos($log['action'], 'logout') !== false) {
                                    $iconClass = 'sign-out-alt';
                                    $iconColor = 'gray';
                                } elseif (strpos($log['action'], 'borrow') !== false) {
                                    $iconClass = 'hand-holding';
                                    $iconColor = 'blue';
                                } elseif (strpos($log['action'], 'return') !== false) {
                                    $iconClass = 'undo';
                                    $iconColor = 'green';
                                } elseif (strpos($log['action'], 'create') !== false) {
                                    $iconClass = 'plus-circle';
                                    $iconColor = 'green';
                                } elseif (strpos($log['action'], 'update') !== false || strpos($log['action'], 'edit') !== false) {
                                    $iconClass = 'edit';
                                    $iconColor = 'orange';
                                } elseif (strpos($log['action'], 'delete') !== false) {
                                    $iconClass = 'trash-alt';
                                    $iconColor = 'red';
                                }
                                ?>
                                <div class="activity-icon icon-<?php echo $iconColor; ?>">
                                    <i class="fas fa-<?php echo $iconClass; ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        <?php echo ucwords(str_replace('_', ' ', $log['action'])); ?>
                                    </div>
                                    <div class="activity-description">
                                        <?php echo htmlspecialchars($log['description'] ?? 'No details available'); ?>
                                    </div>
                                    <div class="activity-time">
                                        <i class="far fa-clock"></i> <?php echo UtilityHelper::formatDateForDisplay($log['created_at'], 'datetime'); ?>
                                    </div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No activity records found.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- User Permissions Summary -->
                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title">Permissions Summary</div>
                        <a href="<?php echo BASE_URL; ?>/users/permissions?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline">
                            <i class="fas fa-edit"></i> Edit Permissions
                        </a>
                    </div>
                    <div class="panel-body">
                        <?php if (count($permissions) > 0): ?>
                        <div class="permission-summary">
                            <?php
                            // Group permissions by category
                            $permissionCategories = [];
                            foreach ($permissions as $permission) {
                                $category = $permission['category'] ?? 'Other';
                                if (!isset($permissionCategories[$category])) {
                                    $permissionCategories[$category] = [];
                                }
                                $permissionCategories[$category][] = $permission;
                            }
                            
                            foreach ($permissionCategories as $category => $categoryPermissions):
                            ?>
                            <div class="permission-category">
                                <h4 class="permission-category-title"><?php echo htmlspecialchars($category); ?></h4>
                                <div class="permission-tags">
                                    <?php foreach ($categoryPermissions as $permission): ?>
                                    <span class="permission-tag <?php echo $permission['granted'] ? 'permission-granted' : 'permission-denied'; ?>">
                                        <?php echo ucwords(str_replace('_', ' ', $permission['name'])); ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No specific permissions set. This user has default permissions based on their role.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
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
    // Initialize dropdowns
    document.querySelectorAll('.dropdown-toggle').forEach(function(dropdown) {
        dropdown.addEventListener('click', function(event) {
            event.stopPropagation();
            const menu = this.nextElementSibling;
            menu.classList.toggle('show');
            
            // Close other open dropdowns
            document.querySelectorAll('.dropdown-menu.show').forEach(function(openMenu) {
                if (openMenu !== menu) {
                    openMenu.classList.remove('show');
                }
            });
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.matches('.dropdown-toggle') && !event.target.closest('.dropdown-menu')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                menu.classList.remove('show');
            });
        }
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
            // Send AJAX request to unlock account
            fetch('<?php echo BASE_URL; ?>/users/unlock-account', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'user_id=' + userId + '&<?php echo CSRF_TOKEN_NAME; ?>=<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>'
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