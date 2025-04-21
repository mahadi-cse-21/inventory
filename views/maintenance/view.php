<?php
/**
 * View Maintenance Request Details
 * 
 * This file displays the details of a maintenance request
 */

// Set page title
$pageTitle = 'Maintenance Request Details';
$bodyClass = 'maintenance-page';

// Check permissions
if (!hasRole(['admin', 'manager'])) {
    include 'views/errors/403.php';
    exit;
}

// Get maintenance ID from URL
$maintenanceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$maintenanceId) {
    setFlashMessage('Invalid maintenance request ID', 'danger');
    redirect(BASE_URL . '/maintenance');
}

// Get maintenance record
$maintenance = MaintenanceHelper::getMaintenanceById($maintenanceId);

if (!$maintenance) {
    setFlashMessage('Maintenance request not found', 'danger');
    redirect(BASE_URL . '/maintenance');
}

// Get item details
$item = InventoryHelper::getItemById($maintenance['item_id']);

// Get maintenance history for this item
$historyResult = MaintenanceHelper::getItemMaintenanceHistory($maintenance['item_id'], 1, 5);
$maintenanceHistory = $historyResult['records'];

// Include header
include 'includes/header.php';
?>

<div class="content-header">
    <h1 class="page-title">Maintenance Request: MR-<?php echo str_pad($maintenance['id'], 4, '0', STR_PAD_LEFT); ?></h1>
    <div class="breadcrumbs">
        <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Home</a>
        <a href="<?php echo BASE_URL; ?>/maintenance" class="breadcrumb-item">Maintenance</a>
        <span class="breadcrumb-item">Request Details</span>
    </div>
</div>

<!-- Request Status Banner -->
<div style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 6px; display: flex; align-items: center;">
    <?php
    $statusClass = '';
    $statusIcon = '';
    $statusTitle = '';
    $statusDescription = '';
    
    switch ($maintenance['status']) {
        case 'requested':
            $statusClass = 'bg-warning-soft';
            $statusIcon = 'clock';
            $statusTitle = 'Awaiting Assignment';
            $statusDescription = 'This request is awaiting technician assignment.';
            break;
        case 'scheduled':
            $statusClass = 'bg-info-soft';
            $statusIcon = 'calendar-alt';
            $statusTitle = 'Scheduled';
            $statusDescription = 'Maintenance is scheduled but not yet started.';
            break;
        case 'in_progress':
            $statusClass = 'bg-primary-soft';
            $statusIcon = 'wrench';
            $statusTitle = 'In Progress';
            $statusDescription = 'Maintenance is currently being performed.';
            break;
        case 'completed':
            $statusClass = 'bg-success-soft';
            $statusIcon = 'check-circle';
            $statusTitle = 'Completed';
            $statusDescription = 'Maintenance has been completed successfully.';
            break;
        case 'cancelled':
            $statusClass = 'bg-danger-soft';
            $statusIcon = 'times-circle';
            $statusTitle = 'Cancelled';
            $statusDescription = 'This maintenance request was cancelled.';
            break;
        default:
            $statusClass = 'bg-secondary-soft';
            $statusIcon = 'info-circle';
            $statusTitle = ucfirst($maintenance['status']);
            $statusDescription = 'Current status: ' . ucfirst($maintenance['status']);
    }
    ?>
    <div class="<?php echo $statusClass; ?>" style="width: 100%; padding: 1rem; border-radius: 6px;">
        <div style="display: flex; align-items: center;">
            <i class="fas fa-<?php echo $statusIcon; ?>" style="font-size: 1.5rem; margin-right: 1rem;"></i>
            <div>
                <div style="font-weight: 600; margin-bottom: 0.25rem;"><?php echo $statusTitle; ?></div>
                <div style="font-size: 0.875rem;"><?php echo $statusDescription; ?></div>
            </div>
        </div>
    </div>
</div>

<div class="panel-row">
    <!-- Left Column -->
    <div class="panel-col">
        <!-- Request Information -->
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">Request Information</div>
                <a href="<?php echo BASE_URL; ?>/maintenance/edit?id=<?php echo $maintenance['id']; ?>" class="btn btn-sm btn-outline">
                    <i class="fas fa-edit btn-icon"></i> Edit
                </a>
            </div>
            <div class="panel-body">
                <div class="detail-list">
                    <div class="detail-item">
                        <div class="detail-label">Request ID</div>
                        <div class="detail-value">MR-<?php echo str_pad($maintenance['id'], 4, '0', STR_PAD_LEFT); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Type</div>
                        <div class="detail-value"><?php echo ucfirst($maintenance['maintenance_type']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <?php
                            $statusClass = '';
                            
                            switch ($maintenance['status']) {
                                case 'requested':
                                    $statusClass = 'status-pending';
                                    break;
                                case 'scheduled':
                                    $statusClass = 'status-pending';
                                    break;
                                case 'in_progress':
                                    $statusClass = 'status-pending';
                                    break;
                                case 'completed':
                                    $statusClass = 'status-active';
                                    break;
                                case 'cancelled':
                                    $statusClass = 'status-inactive';
                                    break;
                                default:
                                    $statusClass = 'status-pending';
                            }
                            ?>
                            <span class="status <?php echo $statusClass; ?>"><?php echo ucfirst($maintenance['status']); ?></span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Requested By</div>
                        <div class="detail-value"><?php echo htmlspecialchars($maintenance['requested_by_name']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Request Date</div>
                        <div class="detail-value"><?php echo UtilityHelper::formatDateForDisplay($maintenance['created_at']); ?></div>
                    </div>
                    <?php if ($maintenance['start_date']): ?>
                        <div class="detail-item">
                            <div class="detail-label">Start Date</div>
                            <div class="detail-value"><?php echo UtilityHelper::formatDateForDisplay($maintenance['start_date']); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if ($maintenance['end_date']): ?>
                        <div class="detail-item">
                            <div class="detail-label">End Date</div>
                            <div class="detail-value"><?php echo UtilityHelper::formatDateForDisplay($maintenance['end_date']); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="detail-section">
                    <div class="detail-section-title">Description</div>
                    <div class="detail-section-content">
                        <?php echo nl2br(htmlspecialchars($maintenance['description'])); ?>
                    </div>
                </div>
                
                <?php if ($maintenance['notes']): ?>
                    <div class="detail-section">
                        <div class="detail-section-title">Notes</div>
                        <div class="detail-section-content">
                            <?php echo nl2br(htmlspecialchars($maintenance['notes'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Assignment Information -->
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">Assignment Information</div>
                <?php if ($maintenance['status'] === 'requested'): ?>
                    <a href="<?php echo BASE_URL; ?>/maintenance/assign?id=<?php echo $maintenance['id']; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-user-plus btn-icon"></i> Assign
                    </a>
                <?php elseif ($maintenance['status'] === 'scheduled'): ?>
                    <a href="<?php echo BASE_URL; ?>/maintenance/reassign?id=<?php echo $maintenance['id']; ?>" class="btn btn-sm btn-outline">
                        <i class="fas fa-user-plus btn-icon"></i> Reassign
                    </a>
                <?php endif; ?>
            </div>
            <div class="panel-body">
                <?php if ($maintenance['assigned_to']): ?>
                    <div class="detail-list">
                        <div class="detail-item">
                            <div class="detail-label">Assigned To</div>
                            <div class="detail-value"><?php echo htmlspecialchars($maintenance['assigned_to_name']); ?></div>
                        </div>
                        <?php if ($maintenance['status'] === 'scheduled'): ?>
                            <div class="detail-item">
                                <div class="detail-label">Scheduled Date</div>
                                <div class="detail-value"><?php echo UtilityHelper::formatDateForDisplay($maintenance['start_date']); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if ($maintenance['status'] === 'in_progress'): ?>
                            <div class="detail-item">
                                <div class="detail-label">Started On</div>
                                <div class="detail-value"><?php echo UtilityHelper::formatDateForDisplay($maintenance['start_date']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Estimated Completion</div>
                                <div class="detail-value">
                                    <?php if ($maintenance['end_date']): ?>
                                        <?php echo UtilityHelper::formatDateForDisplay($maintenance['end_date']); ?>
                                    <?php else: ?>
                                        Not specified
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($maintenance['status'] === 'completed'): ?>
                            <div class="detail-item">
                                <div class="detail-label">Started On</div>
                                <div class="detail-value"><?php echo UtilityHelper::formatDateForDisplay($maintenance['start_date']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Completed On</div>
                                <div class="detail-value"><?php echo UtilityHelper::formatDateForDisplay($maintenance['end_date']); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if ($maintenance['estimated_cost']): ?>
                            <div class="detail-item">
                                <div class="detail-label">Estimated Cost</div>
                                <div class="detail-value"><?php echo UtilityHelper::formatCurrency($maintenance['estimated_cost']); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if ($maintenance['actual_cost']): ?>
                            <div class="detail-item">
                                <div class="detail-label">Actual Cost</div>
                                <div class="detail-value"><?php echo UtilityHelper::formatCurrency($maintenance['actual_cost']); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($maintenance['parts_used']): ?>
                        <div class="detail-section">
                            <div class="detail-section-title">Parts Used</div>
                            <div class="detail-section-content">
                                <?php echo nl2br(htmlspecialchars($maintenance['parts_used'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($maintenance['resolution']): ?>
                        <div class="detail-section">
                            <div class="detail-section-title">Resolution</div>
                            <div class="detail-section-content">
                                <?php echo nl2br(htmlspecialchars($maintenance['resolution'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> This maintenance request has not been assigned to a technician yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Right Column -->
    <div class="panel-col">
        <!-- Item Information -->
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">Item Information</div>
                <a href="<?php echo BASE_URL; ?>/items/view?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline">
                    <i class="fas fa-external-link-alt btn-icon"></i> View Item
                </a>
            </div>
            <div class="panel-body">
                <div style="display: flex; align-items: flex-start; margin-bottom: 1rem;">
                    <div class="item-image-placeholder" style="width: 80px; height: 80px; background-color: var(--gray-100); border-radius: 6px; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                        <?php if (!empty($item['images']) && count($item['images']) > 0): ?>
                            <img src="<?php echo BASE_URL . '/uploads/items/' . $item['id'] . '/' . $item['images'][0]['file_name']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="max-width: 100%; max-height: 100%; border-radius: 6px;">
                        <?php else: ?>
                            <i class="fas fa-box" style="font-size: 2rem; color: var(--gray-500);"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3 style="margin: 0 0 0.25rem 0; font-size: 1.2rem;"><?php echo htmlspecialchars($item['name']); ?></h3>
                        <div style="margin-bottom: 0.5rem; color: var(--gray-500); font-size: 0.875rem;">
                            <?php if (!empty($item['asset_id'])): ?>
                                Asset ID: <?php echo htmlspecialchars($item['asset_id']); ?>
                            <?php endif; ?>
                            <?php if (!empty($item['barcode'])): ?>
                                <?php echo !empty($item['asset_id']) ? ' | ' : ''; ?>
                                Barcode: <?php echo htmlspecialchars($item['barcode']); ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <span class="status <?php echo $item['status'] === 'maintenance' ? 'status-pending' : ($item['status'] === 'available' ? 'status-active' : 'status-inactive'); ?>">
                                <?php echo ucfirst($item['status']); ?>
                            </span>
                            
                            <span class="badge badge-blue" style="margin-left: 0.5rem;">
                                <?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="detail-list">
                    <?php if (!empty($item['brand']) || !empty($item['model'])): ?>
                        <div class="detail-item">
                            <div class="detail-label">Brand/Model</div>
                            <div class="detail-value">
                                <?php echo !empty($item['brand']) ? htmlspecialchars($item['brand']) : ''; ?>
                                <?php echo !empty($item['brand']) && !empty($item['model']) ? ' - ' : ''; ?>
                                <?php echo !empty($item['model']) ? htmlspecialchars($item['model']) : ''; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($item['serial_number'])): ?>
                        <div class="detail-item">
                            <div class="detail-label">Serial Number</div>
                            <div class="detail-value"><?php echo htmlspecialchars($item['serial_number']); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="detail-item">
                        <div class="detail-label">Location</div>
                        <div class="detail-value"><?php echo htmlspecialchars($item['location_name'] ?? 'Not Specified'); ?></div>
                    </div>
                    
                    <?php if (!empty($item['purchase_date'])): ?>
                        <div class="detail-item">
                            <div class="detail-label">Purchase Date</div>
                            <div class="detail-value"><?php echo UtilityHelper::formatDateForDisplay($item['purchase_date']); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($item['warranty_expiry'])): ?>
                        <div class="detail-item">
                            <div class="detail-label">Warranty</div>
                            <div class="detail-value">
                                <?php
                                $now = new DateTime();
                                $warranty = new DateTime($item['warranty_expiry']);
                                $isWarrantyValid = $warranty > $now;
                                
                                echo $isWarrantyValid 
                                    ? '<span class="status status-active">In Warranty (until ' . UtilityHelper::formatDateForDisplay($item['warranty_expiry']) . ')</span>' 
                                    : '<span class="status status-inactive">Expired on ' . UtilityHelper::formatDateForDisplay($item['warranty_expiry']) . '</span>';
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="detail-item">
                        <div class="detail-label">Condition</div>
                        <div class="detail-value"><?php echo ucfirst($item['condition_rating']); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Maintenance History -->
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">Maintenance History</div>
                <a href="<?php echo BASE_URL; ?>/maintenance?filter=item&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline">
                    <i class="fas fa-history btn-icon"></i> View All
                </a>
            </div>
            <div class="panel-body">
                <?php if (count($maintenanceHistory) > 1 || (count($maintenanceHistory) === 1 && $maintenanceHistory[0]['id'] !== $maintenance['id'])): ?>
                    <div class="timeline">
                        <?php foreach ($maintenanceHistory as $record): ?>
                            <?php if ($record['id'] != $maintenance['id']): ?>
                                <div class="timeline-item">
                                    <div class="timeline-dot <?php echo $record['status'] === 'completed' ? 'active' : ''; ?>"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-title">
                                            <a href="<?php echo BASE_URL; ?>/maintenance/view?id=<?php echo $record['id']; ?>">
                                                <?php echo ucfirst($record['maintenance_type']); ?> - MR-<?php echo str_pad($record['id'], 4, '0', STR_PAD_LEFT); ?>
                                            </a>
                                        </div>
                                        <div class="timeline-date"><?php echo UtilityHelper::formatDateForDisplay($record['created_at'], 'datetime'); ?></div>
                                        <div class="timeline-description">
                                            <?php echo htmlspecialchars(substr($record['description'], 0, 100) . (strlen($record['description']) > 100 ? '...' : '')); ?>
                                        </div>
                                        <div class="timeline-status">
                                            <span class="status <?php echo $record['status'] === 'completed' ? 'status-active' : 'status-pending'; ?>">
                                                <?php echo ucfirst($record['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No previous maintenance records found for this item.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="action-bar">
    <a href="<?php echo BASE_URL; ?>/maintenance" class="btn btn-outline">
        <i class="fas fa-arrow-left btn-icon"></i> Back to Maintenance List
    </a>
    
    <?php if ($maintenance['status'] === 'requested'): ?>
        <a href="<?php echo BASE_URL; ?>/maintenance/assign?id=<?php echo $maintenance['id']; ?>" class="btn btn-primary">
            <i class="fas fa-user-plus btn-icon"></i> Assign Technician
        </a>
    <?php elseif ($maintenance['status'] === 'scheduled'): ?>
        <a href="<?php echo BASE_URL; ?>/maintenance/start?id=<?php echo $maintenance['id']; ?>" class="btn btn-primary">
            <i class="fas fa-play btn-icon"></i> Start Work
        </a>
    <?php elseif ($maintenance['status'] === 'in_progress'): ?>
        <a href="<?php echo BASE_URL; ?>/maintenance/complete?id=<?php echo $maintenance['id']; ?>" class="btn btn-primary">
            <i class="fas fa-check btn-icon"></i> Mark as Complete
        </a>
    <?php endif; ?>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>