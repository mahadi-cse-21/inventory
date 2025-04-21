<?php
/**
 * Users Index View
 * 
 * This file displays all users with management options
 */

// Set page title
$pageTitle = 'User Management';
$bodyClass = 'users-page';

// Check permissions
if (!hasRole(['admin'])) {
    include 'views/errors/403.php';
    exit;
}

// Get current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Get filters from query string
$filters = [
    'search' => isset($_GET['q']) ? cleanInput($_GET['q']) : '',
    'role' => isset($_GET['role']) ? cleanInput($_GET['role']) : '',
    'department_id' => isset($_GET['department']) ? (int)$_GET['department'] : '',
    'is_active' => isset($_GET['status']) ? (int)$_GET['status'] : ''
];

// Get users with pagination
$usersResult = UserHelper::getAllUsers($page, ITEMS_PER_PAGE, $filters);
$users = $usersResult['users'];
$pagination = $usersResult['pagination'];

// Get departments for filter dropdown
$departments = UserHelper::getAllDepartments();

// Include header
include 'includes/header.php';
?>

<div class="content-header">
    <h1 class="page-title">User Management</h1>
    <div class="breadcrumbs">
        <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Home</a>
        <span class="breadcrumb-item">Users</span>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-title">Total Users</div>
            <div class="stat-card-icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="stat-card-value">
            <?php echo $pagination['totalItems']; ?>
        </div>
        <div class="stat-card-info">
            <div class="stat-card-label">Registered users</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-title">Admins</div>
            <div class="stat-card-icon">
                <i class="fas fa-user-shield"></i>
            </div>
        </div>
        <div class="stat-card-value">
            <?php 
            // Count admins
            $adminCount = 0;
            foreach ($users as $user) {
                if ($user['role'] === 'admin') {
                    $adminCount++;
                }
            }
            echo $adminCount; 
            ?>
        </div>
        <div class="stat-card-info">
            <div class="stat-card-label">Administrator accounts</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-title">Managers</div>
            <div class="stat-card-icon">
                <i class="fas fa-user-tie"></i>
            </div>
        </div>
        <div class="stat-card-value">
            <?php 
            // Count managers
            $managerCount = 0;
            foreach ($users as $user) {
                if ($user['role'] === 'manager') {
                    $managerCount++;
                }
            }
            echo $managerCount; 
            ?>
        </div>
        <div class="stat-card-info">
            <div class="stat-card-label">Manager accounts</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-title">Active</div>
            <div class="stat-card-icon">
                <i class="fas fa-user-check"></i>
            </div>
        </div>
        <div class="stat-card-value">
            <?php 
            // Count active users
            $activeCount = 0;
            foreach ($users as $user) {
                if ($user['is_active'] == 1) {
                    $activeCount++;
                }
            }
            echo $activeCount; 
            ?>
        </div>
        <div class="stat-card-info">
            <div class="stat-card-label">Active accounts</div>
        </div>
    </div>
</div>

<!-- Actions & Filters -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <a href="<?php echo BASE_URL; ?>/users/create" class="btn btn-primary">
        <i class="fas fa-plus btn-icon"></i> Add User
    </a>
    <div>
        <a href="<?php echo BASE_URL; ?>/users/export" class="btn btn-outline" style="margin-right: 0.5rem;">
            <i class="fas fa-file-export btn-icon"></i> Export
        </a>
        <button class="btn btn-outline" onclick="window.print()">
            <i class="fas fa-print btn-icon"></i> Print
        </button>
    </div>
</div>

<!-- Filters -->
<div class="panel" style="margin-bottom: 1.5rem;">
    <div class="panel-header">
        <div class="panel-title">Filters</div>
        <a href="<?php echo BASE_URL; ?>/users" class="btn btn-sm btn-outline">
            <i class="fas fa-redo-alt"></i> Reset
        </a>
    </div>
    <div class="panel-body">
        <form action="<?php echo BASE_URL; ?>/users" method="GET" id="filter-form">
            <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Role:</label>
                    <select class="form-control" name="role" id="role-filter">
                        <option value="">All Roles</option>
                        <option value="admin" <?php echo ($filters['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="manager" <?php echo ($filters['role'] == 'manager') ? 'selected' : ''; ?>>Manager</option>
                        <option value="user" <?php echo ($filters['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Department:</label>
                    <select class="form-control" name="department" id="department-filter">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?php echo $department['id']; ?>" <?php echo ($filters['department_id'] == $department['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($department['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Status:</label>
                    <select class="form-control" name="status" id="status-filter">
                        <option value="">All Statuses</option>
                        <option value="1" <?php echo ($filters['is_active'] === 1) ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo ($filters['is_active'] === 0) ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Search:</label>
                    <input type="text" class="form-control" name="q" value="<?php echo htmlspecialchars($filters['search']); ?>" placeholder="Search by name, email, username...">
                </div>
            </div>
            <div style="margin-top: 1rem; text-align: right;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search btn-icon"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="panel">
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar" data-initials="<?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>"></div>
                                        <div>
                                            <div class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                            <?php if (!empty($user['job_title'])): ?>
                                                <div class="user-title"><?php echo htmlspecialchars($user['job_title']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['department_name'] ?? 'None'); ?></td>
                                <td>
                                    <?php
                                    $roleClass = '';
                                    switch ($user['role']) {
                                        case 'admin':
                                            $roleClass = 'badge-red';
                                            break;
                                        case 'manager':
                                            $roleClass = 'badge-orange';
                                            break;
                                        default:
                                            $roleClass = 'badge-blue';
                                    }
                                    ?>
                                    <span class="badge <?php echo $roleClass; ?>"><?php echo ucfirst($user['role']); ?></span>
                                </td>
                                <td>
                                    <?php if ($user['is_active'] == 1): ?>
                                        <span class="status status-active">Active</span>
                                    <?php else: ?>
                                        <span class="status status-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?php echo BASE_URL; ?>/users/view?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/users/edit?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <button class="btn btn-sm btn-outline btn-delete" title="Delete" data-id="<?php echo $user['id']; ?>" data-name="<?php echo htmlspecialchars($user['full_name']); ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No users found matching your criteria. Try adjusting your filters.
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if ($pagination['totalPages'] > 1): ?>
    <div class="pagination">
        <?php if ($pagination['hasPreviousPage']): ?>
            <a href="<?php echo BASE_URL; ?>/users?page=1<?php echo !empty($filters['search']) ? '&q=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['role']) ? '&role=' . $filters['role'] : ''; ?><?php echo !empty($filters['department_id']) ? '&department=' . $filters['department_id'] : ''; ?>" class="page-btn">
                <i class="fas fa-angle-double-left"></i>
            </a>
            <a href="<?php echo BASE_URL; ?>/users?page=<?php echo $pagination['currentPage'] - 1; ?><?php echo !empty($filters['search']) ? '&q=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['role']) ? '&role=' . $filters['role'] : ''; ?><?php echo !empty($filters['department_id']) ? '&department=' . $filters['department_id'] : ''; ?>" class="page-btn">
                <i class="fas fa-angle-left"></i>
            </a>
        <?php else: ?>
            <span class="page-btn disabled">
                <i class="fas fa-angle-double-left"></i>
            </span>
            <span class="page-btn disabled">
                <i class="fas fa-angle-left"></i>
            </span>
        <?php endif; ?>
        
        <?php
        // Calculate range of pages to display
        $startPage = max(1, $pagination['currentPage'] - 2);
        $endPage = min($pagination['totalPages'], $pagination['currentPage'] + 2);
        
        // Always show first page
        if ($startPage > 1) {
            echo '<a href="' . BASE_URL . '/users?page=1' . 
                (!empty($filters['search']) ? '&q=' . urlencode($filters['search']) : '') . 
                (!empty($filters['role']) ? '&role=' . $filters['role'] : '') . 
                (!empty($filters['department_id']) ? '&department=' . $filters['department_id'] : '') . 
                '" class="page-btn">1</a>';
            
            if ($startPage > 2) {
                echo '<span class="page-btn disabled">...</span>';
            }
        }
        
        // Show page numbers
        for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i == $pagination['currentPage']) {
                echo '<span class="page-btn active">' . $i . '</span>';
            } else {
                echo '<a href="' . BASE_URL . '/users?page=' . $i . 
                    (!empty($filters['search']) ? '&q=' . urlencode($filters['search']) : '') . 
                    (!empty($filters['role']) ? '&role=' . $filters['role'] : '') . 
                    (!empty($filters['department_id']) ? '&department=' . $filters['department_id'] : '') . 
                    '" class="page-btn">' . $i . '</a>';
            }
        }
        
        // Always show last page
        if ($endPage < $pagination['totalPages']) {
            if ($endPage < $pagination['totalPages'] - 1) {
                echo '<span class="page-btn disabled">...</span>';
            }
            
            echo '<a href="' . BASE_URL . '/users?page=' . $pagination['totalPages'] . 
                (!empty($filters['search']) ? '&q=' . urlencode($filters['search']) : '') . 
                (!empty($filters['role']) ? '&role=' . $filters['role'] : '') . 
                (!empty($filters['department_id']) ? '&department=' . $filters['department_id'] : '') . 
                '" class="page-btn">' . $pagination['totalPages'] . '</a>';
        }
        ?>
        
        <?php if ($pagination['hasNextPage']): ?>
            <a href="<?php echo BASE_URL; ?>/users?page=<?php echo $pagination['currentPage'] + 1; ?><?php echo !empty($filters['search']) ? '&q=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['role']) ? '&role=' . $filters['role'] : ''; ?><?php echo !empty($filters['department_id']) ? '&department=' . $filters['department_id'] : ''; ?>" class="page-btn">
                <i class="fas fa-angle-right"></i>
            </a>
            <a href="<?php echo BASE_URL; ?>/users?page=<?php echo $pagination['totalPages']; ?><?php echo !empty($filters['search']) ? '&q=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['role']) ? '&role=' . $filters['role'] : ''; ?><?php echo !empty($filters['department_id']) ? '&department=' . $filters['department_id'] : ''; ?>" class="page-btn">
                <i class="fas fa-angle-double-right"></i>
            </a>
        <?php else: ?>
            <span class="page-btn disabled">
                <i class="fas fa-angle-right"></i>
            </span>
            <span class="page-btn disabled">
                <i class="fas fa-angle-double-right"></i>
            </span>
        <?php endif; ?>
    </div>
<?php endif; ?>

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
            <p>Are you sure you want to delete the user: <strong id="delete-user-name"></strong>?</p>
            <p>This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="cancel-delete">Cancel</button>
            <a href="#" class="btn btn-danger" id="confirm-delete">
                <i class="fas fa-trash-alt btn-icon"></i> Delete User
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filters change
    const roleFilter = document.getElementById('role-filter');
    const departmentFilter = document.getElementById('department-filter');
    const statusFilter = document.getElementById('status-filter');
    
    roleFilter.addEventListener('change', function() {
        document.getElementById('filter-form').submit();
    });
    
    departmentFilter.addEventListener('change', function() {
        document.getElementById('filter-form').submit();
    });
    
    statusFilter.addEventListener('change', function() {
        document.getElementById('filter-form').submit();
    });
    
    // Delete confirmation modal
    const deleteModal = document.getElementById('delete-modal');
    const deleteUserName = document.getElementById('delete-user-name');
    const confirmDeleteBtn = document.getElementById('confirm-delete');
    const closeDeleteModalBtn = document.getElementById('close-delete-modal');
    const cancelDeleteBtn = document.getElementById('cancel-delete');
    const deleteButtons = document.querySelectorAll('.btn-delete');
    
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            const userName = this.getAttribute('data-name');
            
            // Update modal
            deleteUserName.textContent = userName;
            confirmDeleteBtn.href = '<?php echo BASE_URL; ?>/users/delete?id=' + userId;
            
            // Show modal
            deleteModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
    });
    
    // Close modal
    closeDeleteModalBtn.addEventListener('click', function() {
        deleteModal.style.display = 'none';
        document.body.style.overflow = '';
    });
    
    cancelDeleteBtn.addEventListener('click', function() {
        deleteModal.style.display = 'none';
        document.body.style.overflow = '';
    });
    
    // Close modal when clicking outside
    deleteModal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
            document.body.style.overflow = '';
        }
    });
    
    // Initialize user avatar initials
    document.querySelectorAll('.user-avatar[data-initials]').forEach(avatar => {
        const initials = avatar.getAttribute('data-initials');
        const randomColor = getRandomColor(initials);
        
        avatar.style.backgroundColor = randomColor;
        avatar.textContent = initials;
    });
    
    // Generate consistent random color based on initials
    function getRandomColor(str) {
        // Generate hash from string
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }
        
        // Convert hash to RGB color
        const h = Math.abs(hash % 360);
        const s = 60 + Math.abs((hash / 360) % 20); // 60-80%
        const l = 35 + Math.abs((hash / 360) % 15); // 35-50%
        
        return `hsl(${h}, ${s}%, ${l}%)`;
    }
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?>