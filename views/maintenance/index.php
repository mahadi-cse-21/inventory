<?php
/**
 * Maintenance Index View
 * 
 * This file displays all maintenance requests
 */

//  include('helpers/MaintenanceHelper.php') ;

// Set page title
$pageTitle = 'Maintenance Requests';
$bodyClass = 'maintenance-page';

// Check permissions
if (!hasRole(['admin', 'manager'])) {
    include 'views/errors/403.php';
    exit;
}

// Get current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Get filters from query string
$filters = [
    'search' => isset($_GET['q']) ? cleanInput($_GET['q']) : '',
    'maintenance_type' => isset($_GET['type']) ? cleanInput($_GET['type']) : '',
    'status' => isset($_GET['status']) ? cleanInput($_GET['status']) : '',
    'requested_by' => isset($_GET['requested_by']) ? (int)$_GET['requested_by'] : '',
    'assigned_to' => isset($_GET['assigned_to']) ? (int)$_GET['assigned_to'] : ''
];

// Get maintenance records with pagination
$maintenanceResult = MaintenanceHelper::getAllMaintenanceRecords($page, ITEMS_PER_PAGE, $filters);
$records = $maintenanceResult['records'];
$pagination = $maintenanceResult['pagination'];

// Get users for filter dropdown
$usersResult = UserHelper::getAllUsers(1, 100);
$users = $usersResult['users'];

// Get maintenance statistics
$stats = MaintenanceHelper::getMaintenanceStatistics();

// Include header
include 'includes/header.php';
?>


<div class="content-header">
    <h1 class="page-title">Maintenance Requests</h1>
    <div class="breadcrumbs">
        <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Home</a>
        <a href="<?php echo BASE_URL; ?>/maintenance" class="breadcrumb-item">Maintenance</a>
        <span class="breadcrumb-item">Requests</span>
    </div>
</div>

<!-- Stats Cards with Colorful Icons -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-title">Open Requests</div>
            <div class="stat-card-icon">
                <i class="fas fa-tools"></i>
            </div>
        </div>
        <div class="stat-card-value">
            <?php echo $stats['status_counts']['requested'] ?? 0; ?>
        </div>
        <div class="stat-card-info">
            <div class="stat-progress">
                <div class="stat-progress-bar" style="width: <?php echo min(100, (($stats['status_counts']['requested'] ?? 0) / max(1, array_sum($stats['status_counts']))) * 100); ?>%"></div>
            </div>
            <div class="stat-card-label">Awaiting assignment</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-title">In Progress</div>
            <div class="stat-card-icon">
                <i class="fas fa-wrench"></i>
            </div>
        </div>
        <div class="stat-card-value">
            <?php echo ($stats['status_counts']['in_progress'] ?? 0) + ($stats['status_counts']['scheduled'] ?? 0); ?>
        </div>
        <div class="stat-card-info">
            <div class="stat-progress">
                <div class="stat-progress-bar" style="width: <?php echo min(100, ((($stats['status_counts']['in_progress'] ?? 0) + ($stats['status_counts']['scheduled'] ?? 0)) / max(1, array_sum($stats['status_counts']))) * 100); ?>%"></div>
            </div>
            <div class="stat-card-label">Currently being worked on</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-title">Completed</div>
            <div class="stat-card-icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-card-value">
            <?php echo $stats['status_counts']['completed'] ?? 0; ?>
        </div>
        <div class="stat-card-info">
            <div class="stat-progress">
                <div class="stat-progress-bar" style="width: <?php echo min(100, (($stats['status_counts']['completed'] ?? 0) / max(1, array_sum($stats['status_counts']))) * 100); ?>%"></div>
            </div>
            <div class="stat-card-label">This month</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-title">Upcoming</div>
            <div class="stat-card-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
        </div>
        <div class="stat-card-value">
            <?php echo $stats['upcoming_count']; ?>
        </div>
        <div class="stat-card-info">
            <div class="stat-progress">
                <div class="stat-progress-bar" style="width: <?php echo min(100, ($stats['upcoming_count'] / max(1, $stats['upcoming_count'] + array_sum($stats['status_counts']))) * 100); ?>%"></div>
            </div>
            <div class="stat-card-label">Next 30 days</div>
        </div>
    </div>
</div>

<!-- Actions & Filters -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <a href="<?php echo BASE_URL; ?>/maintenance/create" class="btn btn-primary" id="create-maintenance-btn">
        <i class="fas fa-plus btn-icon"></i> Create Request
    </a>
    <div>
        <a href="<?php echo BASE_URL; ?>/maintenance/export" class="btn btn-outline" style="margin-right: 0.5rem;">
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
        <a href="<?php echo BASE_URL; ?>/maintenance" class="btn btn-sm btn-outline">
            <i class="fas fa-redo-alt"></i> Reset
        </a>
    </div>
    <div class="panel-body">
        <form action="<?php echo BASE_URL; ?>/maintenance" method="GET" id="filter-form">
            <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Status:</label>
                    <select class="form-control" name="status" id="status-filter">
                        <option value="">All Statuses</option>
                        <option value="requested" <?php echo ($filters['status'] == 'requested') ? 'selected' : ''; ?>>Requested</option>
                        <option value="scheduled" <?php echo ($filters['status'] == 'scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="in_progress" <?php echo ($filters['status'] == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo ($filters['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo ($filters['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Type:</label>
                    <select class="form-control" name="type" id="type-filter">
                        <option value="">All Types</option>
                        <option value="repair" <?php echo ($filters['maintenance_type'] == 'repair') ? 'selected' : ''; ?>>Repair</option>
                        <option value="inspection" <?php echo ($filters['maintenance_type'] == 'inspection') ? 'selected' : ''; ?>>Inspection</option>
                        <option value="cleaning" <?php echo ($filters['maintenance_type'] == 'cleaning') ? 'selected' : ''; ?>>Cleaning</option>
                        <option value="calibration" <?php echo ($filters['maintenance_type'] == 'calibration') ? 'selected' : ''; ?>>Calibration</option>
                        <option value="upgrade" <?php echo ($filters['maintenance_type'] == 'upgrade') ? 'selected' : ''; ?>>Upgrade</option>
                        <option value="other" <?php echo ($filters['maintenance_type'] == 'other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Assigned To:</label>
                    <select class="form-control" name="assigned_to" id="assigned-to-filter">
                        <option value="">All Technicians</option>
                        <option value="unassigned" <?php echo ($filters['assigned_to'] === 'unassigned') ? 'selected' : ''; ?>>Unassigned</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>" <?php echo ($filters['assigned_to'] == $user['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Search:</label>
                    <input type="text" class="form-control" name="q" value="<?php echo htmlspecialchars($filters['search']); ?>" placeholder="Search maintenance requests...">
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

<!-- Maintenance Request Cards -->
<div class="stats-grid">
    <?php if (count($records) > 0): ?>
        <?php foreach ($records as $record): ?>
            <div class="maintenance-card" data-type="<?php echo htmlspecialchars($record['maintenance_type']); ?>">
                <div class="maintenance-header">
                    <div style="display: flex; align-items: center;">
                        <div class="maintenance-icon">
                            <?php
                            $typeIcon = 'fa-tools';
                            switch ($record['maintenance_type']) {
                                case 'repair':
                                    $typeIcon = 'fa-wrench';
                                    break;
                                case 'inspection':
                                    $typeIcon = 'fa-search';
                                    break;
                                case 'cleaning':
                                    $typeIcon = 'fa-broom';
                                    break;
                                case 'calibration':
                                    $typeIcon = 'fa-sliders-h';
                                    break;
                                case 'upgrade':
                                    $typeIcon = 'fa-arrow-up';
                                    break;
                                case 'other':
                                    $typeIcon = 'fa-tools';
                                    break;
                            }
                            ?>
                            <i class="fas <?php echo $typeIcon; ?>"></i>
                        </div>
                        <div class="maintenance-info">
                            <div class="maintenance-id">MR-<?php echo str_pad($record['id'], 4, '0', STR_PAD_LEFT); ?></div>
                            <div class="maintenance-item"><?php echo htmlspecialchars($record['item_name']); ?></div>
                        </div>
                    </div>
                    <div>
                        <?php
                        $priorityClass = '';
                        $priorityText = '';
                        
                        // Determine priority level based on urgency or context
                        if ($record['status'] === 'requested') {
                            $priorityClass = 'priority-medium';
                            $priorityText = 'Medium Priority';
                        } elseif ($record['status'] === 'in_progress') {
                            $priorityClass = 'priority-high';
                            $priorityText = 'High Priority';
                        } elseif ($record['status'] === 'scheduled') {
                            $priorityClass = 'priority-medium';
                            $priorityText = 'Medium Priority';
                        } else {
                            $priorityClass = 'priority-low';
                            $priorityText = 'Low Priority';
                        }
                        ?>
                        <span class="priority-badge <?php echo $priorityClass; ?>"><?php echo $priorityText; ?></span>
                    </div>
                </div>
                <div class="maintenance-body">
                    <div class="maintenance-detail-item">
                        <div class="maintenance-detail-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="maintenance-detail-text">
                            <strong>Issue: </strong><?php echo htmlspecialchars($record['maintenance_type']); ?>
                        </div>
                    </div>
                    <div class="maintenance-detail-item">
                        <div class="maintenance-detail-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="maintenance-detail-text">
                            <strong>Reported by: </strong><?php echo htmlspecialchars($record['requested_by_name']); ?>
                        </div>
                    </div>
                    <div class="maintenance-detail-item">
                        <div class="maintenance-detail-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="maintenance-detail-text">
                            <strong>Reported: </strong><?php echo UtilityHelper::formatDateForDisplay($record['created_at'], 'short'); ?>
                        </div>
                    </div>
                    <div class="maintenance-detail-item">
                        <div class="maintenance-detail-icon">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <div class="maintenance-detail-text">
                            <strong>Assigned to: </strong><?php echo $record['assigned_to_name'] ? htmlspecialchars($record['assigned_to_name']) : 'Unassigned'; ?>
                        </div>
                    </div>
                    <div class="maintenance-detail-item">
                        <div class="maintenance-detail-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="maintenance-detail-text">
                            <strong>Status: </strong>
                            <?php
                            $statusClass = '';
                            $statusText = '';
                            
                            switch ($record['status']) {
                                case 'requested':
                                    $statusClass = 'status-pending';
                                    $statusText = 'Requested';
                                    break;
                                case 'scheduled':
                                    $statusClass = 'status-pending';
                                    $statusText = 'Scheduled';
                                    break;
                                case 'in_progress':
                                    $statusClass = 'status-pending';
                                    $statusText = 'In Progress';
                                    break;
                                case 'completed':
                                    $statusClass = 'status-active';
                                    $statusText = 'Completed';
                                    break;
                                case 'cancelled':
                                    $statusClass = 'status-inactive';
                                    $statusText = 'Cancelled';
                                    break;
                                default:
                                    $statusClass = 'status-pending';
                                    $statusText = $record['status'];
                            }
                            ?>
                            <span class="status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                        </div>
                    </div>
                    <div class="maintenance-description">
                        <?php echo htmlspecialchars(substr($record['description'], 0, 150) . (strlen($record['description']) > 150 ? '...' : '')); ?>
                    </div>
                </div>
                <div class="maintenance-footer">
                    <a href="<?php echo BASE_URL; ?>/maintenance/view?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-outline">
                        <i class="fas fa-eye btn-icon"></i> View
                    </a>
                    <a href="<?php echo BASE_URL; ?>/maintenance/edit?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-outline">
                        <i class="fas fa-edit btn-icon"></i> Update
                    </a>
                    
                    <?php if ($record['status'] === 'requested'): ?>
                        <a href="<?php echo BASE_URL; ?>/maintenance/assign?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-outline">
                            <i class="fas fa-user-plus btn-icon"></i> Assign
                        </a>
                    <?php elseif ($record['status'] === 'scheduled'): ?>
                        <a href="<?php echo BASE_URL; ?>/maintenance/start?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-play btn-icon"></i> Start Work
                        </a>
                    <?php elseif ($record['status'] === 'in_progress'): ?>
                        <a href="<?php echo BASE_URL; ?>/maintenance/complete?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-check btn-icon"></i> Complete
                        </a>
                    <?php elseif ($record['status'] === 'completed'): ?>
                        <a href="<?php echo BASE_URL; ?>/maintenance/verify?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-outline">
                            <i class="fas fa-check-double btn-icon"></i> Verify
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No maintenance requests found. Use the "Create Request" button to add a new request.
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filters change
    const statusFilter = document.getElementById('status-filter');
    const typeFilter = document.getElementById('type-filter');
    const assignedToFilter = document.getElementById('assigned-to-filter');
    
    statusFilter.addEventListener('change', function() {
        document.getElementById('filter-form').submit();
    });
    
    typeFilter.addEventListener('change', function() {
        document.getElementById('filter-form').submit();
    });
    
    assignedToFilter.addEventListener('change', function() {
        document.getElementById('filter-form').submit();
    });
    
    // Add some visual feedback on hover for the stats cards
    const statsCards = document.querySelectorAll('.stat-card');
    statsCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            const icon = this.querySelector('.stat-card-icon i');
            icon.classList.add('fa-beat');
            
            setTimeout(() => {
                icon.classList.remove('fa-beat');
            }, 1000);
        });
    });
    
    // Animate maintenance icons on card hover
    const maintenanceCards = document.querySelectorAll('.maintenance-card');
    maintenanceCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            const icon = this.querySelector('.maintenance-icon i');
            icon.classList.add('fa-beat');
            
            setTimeout(() => {
                icon.classList.remove('fa-beat');
            }, 1000);
        });
    });
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?>