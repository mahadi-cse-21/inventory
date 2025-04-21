<?php
/**
 * Damage Reports View
 * 
 * This file displays all damage reports
 */

// Set page title
$pageTitle = 'Damage Reports';
$bodyClass = 'damage-reports-page';

// Check permissions
if (!hasRole(['admin', 'manager'])) {
    include 'views/errors/403.php';
    exit;
}

// Get current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Get active tab
$activeTab = isset($_GET['tab']) ? cleanInput($_GET['tab']) : 'all';

// Get filters from query string
$filters = [
    'search' => isset($_GET['q']) ? cleanInput($_GET['q']) : '',
    'severity' => isset($_GET['severity']) ? cleanInput($_GET['severity']) : '',
    'status' => isset($_GET['status']) ? cleanInput($_GET['status']) : '',
    'reported_by' => isset($_GET['reported_by']) ? (int)$_GET['reported_by'] : '',
    'date_from' => isset($_GET['date_from']) ? cleanInput($_GET['date_from']) : '',
    'date_to' => isset($_GET['date_to']) ? cleanInput($_GET['date_to']) : ''
];

// Apply tab-specific filters
switch ($activeTab) {
    case 'open':
        $filters['status'] = 'open';
        break;
    case 'in-progress':
        $filters['status'] = 'in_progress';
        break;
    case 'resolved':
        $filters['status'] = 'resolved';
        break;
    case 'pending-maintenance':
        $filters['status'] = 'pending_maintenance';
        break;
}

// Get damage reports with pagination
$reportsResult = MaintenanceHelper::getAllDamageReports($page, ITEMS_PER_PAGE, $filters);
$reports = $reportsResult['reports'];
$pagination = $reportsResult['pagination'];

// Include header
include 'includes/header.php';
?>

<div class="content-header">
    <h1 class="page-title">Damage Reports</h1>
    <div class="breadcrumbs">
        <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Home</a>
        <a href="<?php echo BASE_URL; ?>/maintenance" class="breadcrumb-item">Maintenance</a>
        <span class="breadcrumb-item">Damage Reports</span>
    </div>
</div>

<!-- Tabs -->
<div class="tabs">
    <a href="<?php echo BASE_URL; ?>/maintenance/damage-reports?tab=all" class="tab <?php echo $activeTab === 'all' ? 'active' : ''; ?>">All Reports</a>
    <a href="<?php echo BASE_URL; ?>/maintenance/damage-reports?tab=open" class="tab <?php echo $activeTab === 'open' ? 'active' : ''; ?>">Open</a>
    <a href="<?php echo BASE_URL; ?>/maintenance/damage-reports?tab=in-progress" class="tab <?php echo $activeTab === 'in-progress' ? 'active' : ''; ?>">In Progress</a>
    <a href="<?php echo BASE_URL; ?>/maintenance/damage-reports?tab=resolved" class="tab <?php echo $activeTab === 'resolved' ? 'active' : ''; ?>">Resolved</a>
    <a href="<?php echo BASE_URL; ?>/maintenance/damage-reports?tab=pending-maintenance" class="tab <?php echo $activeTab === 'pending-maintenance' ? 'active' : ''; ?>">Pending Maintenance</a>
</div>

<!-- Actions & Filters -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <a href="<?php echo BASE_URL; ?>/maintenance/damage-reports/create" class="btn btn-primary" id="create-damage-report-btn">
        <i class="fas fa-plus btn-icon"></i> Report Damage
    </a>
    <div>
        <a href="<?php echo BASE_URL; ?>/maintenance/damage-reports/export" class="btn btn-outline" style="margin-right: 0.5rem;">
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
        <a href="<?php echo BASE_URL; ?>/maintenance/damage-reports<?php echo $activeTab !== 'all' ? '?tab=' . $activeTab : ''; ?>" class="btn btn-sm btn-outline">
            <i class="fas fa-redo-alt"></i> Reset
        </a>
    </div>
    <div class="panel-body">
        <form action="<?php echo BASE_URL; ?>/maintenance/damage-reports" method="GET" id="filter-form">
            <!-- Preserve active tab -->
            <input type="hidden" name="tab" value="<?php echo $activeTab; ?>">
            
            <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Severity:</label>
                    <select class="form-control" name="severity" id="severity-filter">
                        <option value="">All Severities</option>
                        <option value="critical" <?php echo ($filters['severity'] == 'critical') ? 'selected' : ''; ?>>Critical</option>
                        <option value="high" <?php echo ($filters['severity'] == 'high') ? 'selected' : ''; ?>>High</option>
                        <option value="medium" <?php echo ($filters['severity'] == 'medium') ? 'selected' : ''; ?>>Medium</option>
                        <option value="low" <?php echo ($filters['severity'] == 'low') ? 'selected' : ''; ?>>Low</option>
                    </select>
                </div>
                <?php if ($activeTab === 'all'): ?>
                    <div style="flex: 1; min-width: 200px;">
                        <label class="form-label">Status:</label>
                        <select class="form-control" name="status" id="status-filter">
                            <option value="">All Statuses</option>
                            <option value="open" <?php echo ($filters['status'] == 'open') ? 'selected' : ''; ?>>Open</option>
                            <option value="in_progress" <?php echo ($filters['status'] == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                            <option value="resolved" <?php echo ($filters['status'] == 'resolved') ? 'selected' : ''; ?>>Resolved</option>
                            <option value="pending_maintenance" <?php echo ($filters['status'] == 'pending_maintenance') ? 'selected' : ''; ?>>Pending Maintenance</option>
                        </select>
                    </div>
                <?php endif; ?>
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Date Range:</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="date" class="form-control" name="date_from" value="<?php echo $filters['date_from']; ?>" placeholder="From">
                        <input type="date" class="form-control" name="date_to" value="<?php echo $filters['date_to']; ?>" placeholder="To">
                    </div>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Search:</label>
                    <input type="text" class="form-control" name="q" value="<?php echo htmlspecialchars($filters['search']); ?>" placeholder="Search by ID, item or description...">
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

<!-- Damage Reports List -->
<div class="damage-reports-container">
    <?php if (count($reports) > 0): ?>
        <?php foreach ($reports as $report): ?>
            <div class="damage-card">
                <div class="damage-header">
                    <div style="display: flex; align-items: center;">
                        <div class="damage-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="damage-info">
                            <div class="damage-title"><?php echo htmlspecialchars($report['item_name'] . ' - ' . $report['description']); ?></div>
                            <div class="damage-id">ID: DMG-<?php echo str_pad($report['id'], 4, '0', STR_PAD_LEFT); ?> • Reported: <?php echo UtilityHelper::formatDateForDisplay($report['created_at'], 'short'); ?></div>
                        </div>
                    </div>
                    <div>
                        <?php
                        $statusClass = '';
                        $statusText = '';
                        
                        switch ($report['status']) {
                            case 'open':
                                $statusClass = 'status-active';
                                $statusText = 'Open';
                                break;
                            case 'in_progress':
                                $statusClass = 'status-pending';
                                $statusText = 'In Progress';
                                break;
                            case 'pending_maintenance':
                                $statusClass = 'status-pending';
                                $statusText = 'Pending Maintenance';
                                break;
                            case 'resolved':
                                $statusClass = 'status-inactive';
                                $statusText = 'Resolved';
                                break;
                            default:
                                $statusClass = 'status-pending';
                                $statusText = ucfirst($report['status']);
                        }
                        ?>
                        <span class="status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                    </div>
                </div>
                <div class="damage-body">
                    <div class="damage-item-details">
                        <div class="damage-item-detail">
                            <div class="detail-label">Item</div>
                            <div class="detail-value"><?php echo htmlspecialchars($report['item_name']); ?></div>
                        </div>
                        <div class="damage-item-detail">
                            <div class="detail-label">SKU</div>
                            <div class="detail-value"><?php echo htmlspecialchars($report['asset_id'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="damage-item-detail">
                            <div class="detail-label">Location</div>
                            <div class="detail-value"><?php echo isset($report['item_location']) ? htmlspecialchars($report['item_location']) : 'Not specified'; ?></div>
                        </div>
                        <div class="damage-item-detail">
                            <div class="detail-label">Severity</div>
                            <div class="detail-value">
                                <?php
                                $severityClass = '';
                                switch ($report['severity']) {
                                    case 'critical':
                                        $severityClass = 'severity-critical';
                                        break;
                                    case 'high':
                                        $severityClass = 'severity-high';
                                        break;
                                    case 'medium':
                                        $severityClass = 'severity-medium';
                                        break;
                                    case 'low':
                                        $severityClass = 'severity-low';
                                        break;
                                }
                                ?>
                                <span class="severity-indicator <?php echo $severityClass; ?>"></span>
                                <?php echo ucfirst($report['severity']); ?>
                            </div>
                        </div>
                        <div class="damage-item-detail">
                            <div class="detail-label">Reported By</div>
                            <div class="detail-value"><?php echo htmlspecialchars($report['reported_by_name']); ?></div>
                        </div>
                    </div>
                    <div class="damage-description">
                        <?php echo htmlspecialchars(substr($report['description'], 0, 200) . (strlen($report['description']) > 200 ? '...' : '')); ?>
                    </div>
                    <div style="display: flex; margin-top: 1rem;">
                        <div class="detail-label" style="margin-right: 0.5rem;">Maintenance Request:</div>
                        <div class="detail-value">
                            <?php if ($report['maintenance_record_id']): ?>
                                <span class="status status-active">Created</span> • MR-<?php echo str_pad($report['maintenance_record_id'], 4, '0', STR_PAD_LEFT); ?>
                            <?php else: ?>
                                <span class="status status-pending">Not Created</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="damage-footer">
                    <a href="<?php echo BASE_URL; ?>/maintenance/damage-reports/view?id=<?php echo $report['id']; ?>" class="btn btn-sm btn-outline">
                        <i class="fas fa-eye btn-icon"></i> View Details
                    </a>
                    <a href="<?php echo BASE_URL; ?>/maintenance/damage-reports/edit?id=<?php echo $report['id']; ?>" class="btn btn-sm btn-outline">
                        <i class="fas fa-edit btn-icon"></i> Update
                    </a>
                    <?php if ($report['status'] === 'open' && !$report['maintenance_record_id']): ?>
                        <a href="<?php echo BASE_URL; ?>/maintenance/create?damage_id=<?php echo $report['id']; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-tools btn-icon"></i> Create Maintenance
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No damage reports found. Use the "Report Damage" button to create a new report.
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($pagination['totalPages'] > 1): ?>
    <div class="pagination">
        <?php if ($pagination['hasPreviousPage']): ?>
            <a href="<?php echo BASE_URL; ?>/maintenance/damage-reports?page=1<?php echo !empty($activeTab) && $activeTab !== 'all' ? '&tab=' . $activeTab : ''; ?><?php echo !empty($filters['search']) ? '&q=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['severity']) ? '&severity=' . $filters['severity'] : ''; ?>" class="page-btn">
                <i class="fas fa-angle-double-left"></i>
            </a>
            <a href="<?php echo BASE_URL; ?>/maintenance/damage-reports?page=<?php echo $pagination['currentPage'] - 1; ?><?php echo !empty($activeTab) && $activeTab !== 'all' ? '&tab=' . $activeTab : ''; ?><?php echo !empty($filters['search']) ? '&q=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['severity']) ? '&severity=' . $filters['severity'] : ''; ?>" class="page-btn">
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
            echo '<a href="' . BASE_URL . '/maintenance/damage-reports?page=1' . 
                (!empty($activeTab) && $activeTab !== 'all' ? '&tab=' . $activeTab : '') . 
                (!empty($filters['search']) ? '&q=' . urlencode($filters['search']) : '') . 
                (!empty($filters['severity']) ? '&severity=' . $filters['severity'] : '') . 
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
                echo '<a href="' . BASE_URL . '/maintenance/damage-reports?page=' . $i . 
                    (!empty($activeTab) && $activeTab !== 'all' ? '&tab=' . $activeTab : '') . 
                    (!empty($filters['search']) ? '&q=' . urlencode($filters['search']) : '') . 
                    (!empty($filters['severity']) ? '&severity=' . $filters['severity'] : '') . 
                    '" class="page-btn">' . $i . '</a>';
            }
        }
        
        // Always show last page
        if ($endPage < $pagination['totalPages']) {
            if ($endPage < $pagination['totalPages'] - 1) {
                echo '<span class="page-btn disabled">...</span>';
            }
            
            echo '<a href="' . BASE_URL . '/maintenance/damage-reports?page=' . $pagination['totalPages'] . 
                (!empty($activeTab) && $activeTab !== 'all' ? '&tab=' . $activeTab : '') . 
                (!empty($filters['search']) ? '&q=' . urlencode($filters['search']) : '') . 
                (!empty($filters['severity']) ? '&severity=' . $filters['severity'] : '') . 
                '" class="page-btn">' . $pagination['totalPages'] . '</a>';
        }
        ?>
        
        <?php if ($pagination['hasNextPage']): ?>
            <a href="<?php echo BASE_URL; ?>/maintenance/damage-reports?page=<?php echo $pagination['currentPage'] + 1; ?><?php echo !empty($activeTab) && $activeTab !== 'all' ? '&tab=' . $activeTab : ''; ?><?php echo !empty($filters['search']) ? '&q=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['severity']) ? '&severity=' . $filters['severity'] : ''; ?>" class="page-btn">
                <i class="fas fa-angle-right"></i>
            </a>
            <a href="<?php echo BASE_URL; ?>/maintenance/damage-reports?page=<?php echo $pagination['totalPages']; ?><?php echo !empty($activeTab) && $activeTab !== 'all' ? '&tab=' . $activeTab : ''; ?><?php echo !empty($filters['search']) ? '&q=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['severity']) ? '&severity=' . $filters['severity'] : ''; ?>" class="page-btn">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filters change
    const severityFilter = document.getElementById('severity-filter');
    <?php if ($activeTab === 'all'): ?>
    const statusFilter = document.getElementById('status-filter');
    
    statusFilter.addEventListener('change', function() {
        document.getElementById('filter-form').submit();
    });
    <?php endif; ?>
    
    severityFilter.addEventListener('change', function() {
        document.getElementById('filter-form').submit();
    });
});
</script>

<!-- Footer -->
<footer style="text-align: center; margin-top: 2rem; padding: 1rem; border-top: 1px solid var(--gray-200); color: var(--gray-500); font-size: 0.875rem;">
    <div>© <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</div>
    <div style="margin-top: 0.5rem;">Version <?php echo APP_VERSION; ?></div>
</footer>

<?php
// Include footer
include 'includes/footer.php';
?>