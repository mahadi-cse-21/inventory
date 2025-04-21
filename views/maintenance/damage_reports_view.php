<?php
/**
 * View Damage Report Details
 * 
 * This file displays the details of a damage report
 */

// Set page title
$pageTitle = 'Damage Report Details';
$bodyClass = 'damage-report-page';

// Check permissions
if (!hasRole(['admin', 'manager'])) {
    include 'views/errors/403.php';
    exit;
}

// Get damage report ID from URL
$reportId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$reportId) {
    setFlashMessage('Invalid damage report ID', 'danger');
    redirect(BASE_URL . '/maintenance/damage-reports');
}

// Get damage report
$report = MaintenanceHelper::getDamageReportById($reportId);

if (!$report) {
    setFlashMessage('Damage report not found', 'danger');
    redirect(BASE_URL . '/maintenance/damage-reports');
}

// Get item details
$item = InventoryHelper::getItemById($report['item_id']);

// Include header
include 'includes/header.php';
?>

<div class="content-header">
    <h1 class="page-title">Damage Report: DMG-<?php echo str_pad($report['id'], 4, '0', STR_PAD_LEFT); ?></h1>
    <div class="breadcrumbs">
        <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Home</a>
        <a href="<?php echo BASE_URL; ?>/maintenance" class="breadcrumb-item">Maintenance</a>
        <a href="<?php echo BASE_URL; ?>/maintenance/damage-reports" class="breadcrumb-item">Damage Reports</a>
        <span class="breadcrumb-item">Report Details</span>
    </div>
</div>

<!-- Report Status Banner -->
<div style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 6px; display: flex; align-items: center;">
    <?php
    $statusClass = '';
    $statusIcon = '';
    $statusTitle = '';
    $statusDescription = '';
    
    switch ($report['status']) {
        case 'open':
            $statusClass = 'bg-warning-soft';
            $statusIcon = 'exclamation-circle';
            $statusTitle = 'Open';
            $statusDescription = 'This damage report is open and needs assessment.';
            break;
        case 'in_progress':
            $statusClass = 'bg-primary-soft';
            $statusIcon = 'tools';
            $statusTitle = 'In Progress';
            $statusDescription = 'This damage is currently being addressed.';
            break;
        case 'pending_maintenance':
            $statusClass = 'bg-info-soft';
            $statusIcon = 'clock';
            $statusTitle = 'Pending Maintenance';
            $statusDescription = 'A maintenance request has been created and is waiting to be processed.';
            break;
        case 'resolved':
            $statusClass = 'bg-success-soft';
            $statusIcon = 'check-circle';
            $statusTitle = 'Resolved';
            $statusDescription = 'This damage has been resolved.';
            break;
        default:
            $statusClass = 'bg-secondary-soft';
            $statusIcon = 'info-circle';
            $statusTitle = ucfirst($report['status']);
            $statusDescription = 'Current status: ' . ucfirst($report['status']);
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
        <!-- Report Information -->
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">Report Information</div>
                <a href="<?php echo BASE_URL; ?>/maintenance/damage-reports/edit?id=<?php echo $report['id']; ?>" class="btn btn-sm btn-outline">
                    <i class="fas fa-edit btn-icon"></i> Edit
                </a>
            </div>
            <div class="panel-body">
                <div class="detail-list">
                    <div class="detail-item">
                        <div class="detail-label">Report ID</div>
                        <div class="detail-value">DMG-<?php echo str_pad($report['id'], 4, '0', STR_PAD_LEFT); ?></div>
                    </div>
                    <div class="detail-item">
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
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <?php
                            $statusClass = '';
                            
                            switch ($report['status']) {
                                case 'open':
                                    $statusClass = 'status-active';
                                    break;
                                case 'in_progress':
                                case 'pending_maintenance':
                                    $statusClass = 'status-pending';
                                    break;
                                case 'resolved':
                                    $statusClass = 'status-inactive';
                                    break;
                                default:
                                    $statusClass = 'status-pending';
                            }
                            ?>
                            <span class="status <?php echo $statusClass; ?>"><?php echo ucfirst(str_replace('_', ' ', $report['status'])); ?></span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Reported By</div>
                        <div class="detail-value"><?php echo htmlspecialchars($report['reported_by_name']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Report Date</div>
                        <div class="detail-value"><?php echo UtilityHelper::formatDateForDisplay($report['created_at']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Damage Date</div>
                        <div class="detail-value"><?php echo UtilityHelper::formatDateForDisplay($report['damage_date']); ?></div>
                    </div>
                    <?php if ($report['resolution_date']): ?>
                        <div class="detail-item">
                            <div class="detail-label">Resolution Date</div>
                            <div class="detail-value"><?php echo UtilityHelper::formatDateForDisplay($report['resolution_date']); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="detail-section">
                    <div class="detail-section-title">Description</div>
                    <div class="detail-section-content">
                        <?php echo nl2br(htmlspecialchars($report['description'])); ?>
                    </div>
                </div>
                
                <?php if ($report['notes']): ?>
                    <div class="detail-section">
                        <div class="detail-section-title">Notes</div>
                        <div class="detail-section-content">
                            <?php echo nl2br(htmlspecialchars($report['notes'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($report['resolution']): ?>
                    <div class="detail-section">
                        <div class="detail-section-title">Resolution</div>
                        <div class="detail-section-content">
                            <?php echo nl2br(htmlspecialchars($report['resolution'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Damage Images -->
        <?php if (!empty($report['images'])): ?>
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">Damage Photos</div>
                </div>
                <div class="panel-body">
                    <div class="image-gallery">
                        <?php foreach ($report['images'] as $image): ?>
                            <div class="damage-image">
                                <a href="<?php echo BASE_URL . $image['file_path']; ?>" target="_blank">
                                    <img src="<?php echo BASE_URL . $image['file_path']; ?>" alt="Damage photo">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Related Maintenance Request -->
        <?php if ($report['maintenance_record_id']): ?>
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">Maintenance Request</div>
                    <a href="<?php echo BASE_URL; ?>/maintenance/view?id=<?php echo $report['maintenance_record_id']; ?>" class="btn btn-sm btn-outline">
                        <i class="fas fa-external-link-alt btn-icon"></i> View Request
                    </a>
                </div>
                <div class="panel-body">
                    <?php if (isset($report['maintenance'])): ?>
                        <div class="detail-list">
                            <div class="detail-item">
                                <div class="detail-label">Request ID</div>
                                <div class="detail-value">MR-<?php echo str_pad($report['maintenance_record_id'], 4, '0', STR_PAD_LEFT); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Type</div>
                                <div class="detail-value"><?php echo ucfirst($report['maintenance']['maintenance_type']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Status</div>
                                <div class="detail-value">
                                    <?php
                                    $maintenanceStatusClass = '';
                                    
                                    switch ($report['maintenance']['status']) {
                                        case 'requested':
                                            $maintenanceStatusClass = 'status-pending';
                                            break;
                                        case 'scheduled':
                                            $maintenanceStatusClass = 'status-pending';
                                            break;
                                        case 'in_progress':
                                            $maintenanceStatusClass = 'status-pending';
                                            break;
                                        case 'completed':
                                            $maintenanceStatusClass = 'status-active';
                                            break;
                                        case 'cancelled':
                                            $maintenanceStatusClass = 'status-inactive';
                                            break;
                                        default:
                                            $maintenanceStatusClass = 'status-pending';
                                    }
                                    ?>
                                    <span class="status <?php echo $maintenanceStatusClass; ?>"><?php echo ucfirst($report['maintenance']['status']); ?></span>
                                </div>
                            </div>
                            <?php if ($report['maintenance']['assigned_to_name']): ?>
                                <div class="detail-item">
                                    <div class="detail-label">Assigned To</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($report['maintenance']['assigned_to_name']); ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if ($report['maintenance']['start_date']): ?>
                                <div class="detail-item">
                                    <div class="detail-label">Start Date</div>
                                    <div class="detail-value"><?php echo UtilityHelper::formatDateForDisplay($report['maintenance']['start_date']); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($report['maintenance']['description']): ?>
                            <div class="detail-section">
                                <div class="detail-section-title">Description</div>
                                <div class="detail-section-content">
                                    <?php echo nl2br(htmlspecialchars($report['maintenance']['description'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Maintenance request MR-<?php echo str_pad($report['maintenance_record_id'], 4, '0', STR_PAD_LEFT); ?> is linked to this damage report.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
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
        
        <!-- Activity Timeline -->
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">Activity Timeline</div>
            </div>
            <div class="panel-body">
                <div class="timeline">
                    <?php if ($report['status'] === 'resolved'): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot active"></div>
                            <div class="timeline-content">
                                <div class="timeline-title">Damage Resolved</div>
                                <div class="timeline-date"><?php echo UtilityHelper::formatDateForDisplay($report['resolution_date'] ?: $report['updated_at'], 'datetime'); ?></div>
                                <?php if ($report['resolution']): ?>
                                    <div><?php echo htmlspecialchars(substr($report['resolution'], 0, 100) . (strlen($report['resolution']) > 100 ? '...' : '')); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($report['maintenance_record_id']): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot active"></div>
                            <div class="timeline-content">
                                <div class="timeline-title">Maintenance Request Created</div>
                                <div class="timeline-date"><?php echo isset($report['maintenance']) ? UtilityHelper::formatDateForDisplay($report['maintenance']['created_at'], 'datetime') : ''; ?></div>
                                <div>Maintenance request created to address the reported damage</div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($report['status'] === 'in_progress' || $report['status'] === 'pending_maintenance' || $report['status'] === 'resolved'): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot active"></div>
                            <div class="timeline-content">
                                <div class="timeline-title">Processing Started</div>
                                <div class="timeline-date"><?php echo UtilityHelper::formatDateForDisplay($report['updated_at'], 'datetime'); ?></div>
                                <div>Damage assessment and repairs initiated</div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="timeline-item">
                        <div class="timeline-dot active"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Damage Reported</div>
                            <div class="timeline-date"><?php echo UtilityHelper::formatDateForDisplay($report['created_at'], 'datetime'); ?></div>
                            <div>Reported by: <?php echo htmlspecialchars($report['reported_by_name']); ?></div>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-dot active"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Damage Occurred</div>
                            <div class="timeline-date"><?php echo UtilityHelper::formatDateForDisplay($report['damage_date'], 'datetime'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="action-bar">
    <a href="<?php echo BASE_URL; ?>/maintenance/damage-reports" class="btn btn-outline">
        <i class="fas fa-arrow-left btn-icon"></i> Back to Damage Reports
    </a>
    
    <?php if ($report['status'] === 'open'): ?>
        <?php if (!$report['maintenance_record_id']): ?>
            <a href="<?php echo BASE_URL; ?>/maintenance/create?damage_id=<?php echo $report['id']; ?>" class="btn btn-primary">
                <i class="fas fa-tools btn-icon"></i> Create Maintenance Request
            </a>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if ($report['status'] !== 'resolved'): ?>
        <a href="<?php echo BASE_URL; ?>/maintenance/damage-reports/resolve?id=<?php echo $report['id']; ?>" class="btn btn-success">
            <i class="fas fa-check btn-icon"></i> Mark as Resolved
        </a>
    <?php endif; ?>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>