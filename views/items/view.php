<?php
/**
 * View Inventory Item
 * 
 * This page displays detailed information about an inventory item
 */

// Set page title
$pageTitle = 'View Item';

// Get item ID from query string
$itemId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$itemId) {
    setFlashMessage('Invalid item ID', 'danger');
    redirect(BASE_URL . '/items');
    exit;
}

// Get item details
$item = InventoryHelper::getItemById($itemId);

if (!$item) {
    setFlashMessage('Item not found', 'danger');
    redirect(BASE_URL . '/items');
    exit;
}

// Check if format parameter is set for modal view
$isModal = isset($_GET['format']) && $_GET['format'] === 'modal';

// Format item tags for display
$itemTags = [];
if (!empty($item['tags'])) {
    $itemTags = $item['tags'];
}

// Calculate item status and condition badge classes
$statusClass = '';
$statusText = '';

switch ($item['status']) {
    case 'available':
        $statusClass = 'badge-green';
        $statusText = 'Available';
        break;
    case 'borrowed':
        $statusClass = 'badge-orange';
        $statusText = 'Borrowed';
        break;
    case 'reserved':
        $statusClass = 'badge-blue';
        $statusText = 'Reserved';
        break;
    case 'maintenance':
        $statusClass = 'badge-yellow';
        $statusText = 'In Maintenance';
        break;
    case 'unavailable':
    case 'retired':
        $statusClass = 'badge-red';
        $statusText = ucfirst($item['status']);
        break;
    default:
        $statusClass = 'badge-gray';
        $statusText = ucfirst($item['status']);
}

$conditionClass = '';
switch ($item['condition_rating']) {
    case 'new':
    case 'excellent':
        $conditionClass = 'badge-green';
        break;
    case 'good':
        $conditionClass = 'badge-blue';
        break;
    case 'fair':
        $conditionClass = 'badge-yellow';
        break;
    case 'poor':
    case 'damaged':
        $conditionClass = 'badge-red';
        break;
}

// Get borrow history if not in modal view
if (!$isModal) {
    $borrowHistory = BorrowHelper::getAllBorrowRequests(1, 5, ['item_id' => $itemId]);
}

// Check if we're including this in a modal
if (!$isModal) {
    // Include header if not in modal view
    include 'includes/header.php';
}
?>

<?php if (!$isModal): ?>
<div class="content-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title"><?php echo htmlspecialchars($item['name']); ?></h1>
        <div class="action-buttons">
            <?php if (hasRole(['admin', 'manager'])): ?>
            <a href="<?php echo BASE_URL; ?>/items/edit?id=<?php echo $itemId; ?>" class="btn btn-outline">
                <i class="fas fa-edit btn-icon"></i> Edit
            </a>
            <?php endif; ?>
            <?php if ($item['status'] === 'available' && !hasRole(['admin', 'manager'])): ?>
            <a href="<?php echo BASE_URL; ?>/borrow/create?item_id=<?php echo $itemId; ?>" class="btn btn-primary">
                <i class="fas fa-hand-holding btn-icon"></i> Request Item
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="breadcrumbs">
        <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Home</a>
        <a href="<?php echo BASE_URL; ?>/items" class="breadcrumb-item">Inventory</a>
        <span class="breadcrumb-item"><?php echo htmlspecialchars($item['name']); ?></span>
    </div>
</div>
<?php endif; ?>

<div class="item-view-container">
    <!-- Main Item Info Panel -->
    <div class="panel">
        <div class="panel-body">
            <div class="item-view-layout">
                <!-- Item Images Section -->
                <div class="item-images">
                    <?php if (!empty($item['images'])): ?>
                        <div class="item-main-image">
                            <?php 
                            // Find primary image or use first one
                            $primaryImage = null;
                            foreach ($item['images'] as $image) {
                                if ($image['is_primary']) {
                                    $primaryImage = $image;
                                    break;
                                }
                            }
                            
                            if (!$primaryImage && count($item['images']) > 0) {
                                $primaryImage = $item['images'][0];
                            }
                            
                            if ($primaryImage):
                            ?>
                                <img src="<?php echo BASE_URL . '/uploads/items/' . $item['id'] . '/' . $primaryImage['file_name']; ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     id="main-image">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-image"></i>
                                    <span>No image available</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (count($item['images']) > 1): ?>
                            <div class="item-thumbnails">
                                <?php foreach($item['images'] as $index => $image): ?>
                                    <div class="thumbnail <?php echo ($primaryImage && $image['id'] == $primaryImage['id']) ? 'active' : ''; ?>" 
                                         data-src="<?php echo BASE_URL . '/uploads/items/' . $item['id'] . '/' . $image['file_name']; ?>">
                                        <img src="<?php echo BASE_URL . '/uploads/items/' . $item['id'] . '/' . $image['file_name']; ?>" 
                                             alt="Thumbnail <?php echo $index+1; ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-image">
                            <i class="fas fa-image"></i>
                            <span>No image available</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Item Details Section -->
                <div class="item-details">
                    <div class="item-header">
                        <h2 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h2>
                        <div class="item-badges">
                            <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                            <span class="badge <?php echo $conditionClass; ?>"><?php echo ucfirst($item['condition_rating']); ?></span>
                            <?php if (!empty($item['category_name'])): ?>
                                <span class="badge badge-blue"><?php echo htmlspecialchars($item['category_name']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($itemTags)): ?>
                        <div class="item-tags">
                            <?php foreach($itemTags as $tag): ?>
                                <span class="tag"><?php echo htmlspecialchars($tag['name']); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($item['description'])): ?>
                        <div class="item-description">
                            <?php echo nl2br(htmlspecialchars($item['description'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="item-info-grid">
                        <?php if (!empty($item['asset_id'])): ?>
                            <div class="info-item">
                                <div class="info-label">Asset ID</div>
                                <div class="info-value"><?php echo htmlspecialchars($item['asset_id']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($item['location_name'])): ?>
                            <div class="info-item">
                                <div class="info-label">Location</div>
                                <div class="info-value"><?php echo htmlspecialchars($item['location_name']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($item['brand'])): ?>
                            <div class="info-item">
                                <div class="info-label">Brand</div>
                                <div class="info-value"><?php echo htmlspecialchars($item['brand']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($item['model'])): ?>
                            <div class="info-item">
                                <div class="info-label">Model</div>
                                <div class="info-value"><?php echo htmlspecialchars($item['model']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($item['serial_number'])): ?>
                            <div class="info-item">
                                <div class="info-label">Serial Number</div>
                                <div class="info-value"><?php echo htmlspecialchars($item['serial_number']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($item['purchase_date'])): ?>
                            <div class="info-item">
                                <div class="info-label">Purchase Date</div>
                                <div class="info-value"><?php echo UtilityHelper::formatDateForDisplay($item['purchase_date']); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!$isModal): ?>
    <!-- Tabs for Additional Information -->
    <div class="panel">
        <div class="panel-body">
            <div class="item-tabs">
                <div class="tabs">
                    <div class="tab active" data-tab="specifications">Specifications</div>
                    <div class="tab" data-tab="purchase-info">Purchase & Warranty</div>
                    <?php if (!empty($borrowHistory['requests'])): ?>
                    <div class="tab" data-tab="history">Borrow History</div>
                    <?php endif; ?>
                    <?php if (hasRole(['admin', 'manager'])): ?>
                    <div class="tab" data-tab="management">Management</div>
                    <?php endif; ?>
                </div>
                
                <!-- Specifications Tab -->
                <div class="tab-content active" id="specifications">
                    <div class="tab-content-inner">
                        <?php if (!empty($item['specifications'])): ?>
                            <div class="section">
                                <h3 class="section-title">Technical Specifications</h3>
                                <div class="section-content">
                                    <?php echo nl2br(htmlspecialchars($item['specifications'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="section">
                            <h3 class="section-title">Details</h3>
                            <div class="details-grid">
                                <?php if (!empty($item['model_number'])): ?>
                                    <div class="detail-item">
                                        <div class="detail-label">Model Number</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($item['model_number']); ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($item['barcode'])): ?>
                                    <div class="detail-item">
                                        <div class="detail-label">Barcode</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($item['barcode']); ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($item['supplier_name'])): ?>
                                    <div class="detail-item">
                                        <div class="detail-label">Supplier</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($item['supplier_name']); ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Status information -->
                                <div class="detail-item">
                                    <div class="detail-label">Status</div>
                                    <div class="detail-value">
                                        <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    </div>
                                </div>
                                
                                <!-- Condition information -->
                                <div class="detail-item">
                                    <div class="detail-label">Condition</div>
                                    <div class="detail-value">
                                        <span class="badge <?php echo $conditionClass; ?>"><?php echo ucfirst($item['condition_rating']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($item['notes'])): ?>
                            <div class="section">
                                <h3 class="section-title">Notes</h3>
                                <div class="section-content">
                                    <?php echo nl2br(htmlspecialchars($item['notes'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Purchase & Warranty Tab -->
                <div class="tab-content" id="purchase-info">
                    <div class="tab-content-inner">
                        <div class="section">
                            <h3 class="section-title">Purchase Information</h3>
                            <div class="details-grid">
                                <?php if (!empty($item['purchase_date'])): ?>
                                    <div class="detail-item">
                                        <div class="detail-label">Purchase Date</div>
                                        <div class="detail-value"><?php echo UtilityHelper::formatDateForDisplay($item['purchase_date']); ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($item['purchase_price'])): ?>
                                    <div class="detail-item">
                                        <div class="detail-label">Purchase Price</div>
                                        <div class="detail-value"><?php echo UtilityHelper::formatCurrency($item['purchase_price']); ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($item['supplier_name'])): ?>
                                    <div class="detail-item">
                                        <div class="detail-label">Supplier</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($item['supplier_name']); ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($item['current_value'])): ?>
                                    <div class="detail-item">
                                        <div class="detail-label">Current Value</div>
                                        <div class="detail-value"><?php echo UtilityHelper::formatCurrency($item['current_value']); ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="section">
                            <h3 class="section-title">Warranty Information</h3>
                            <div class="details-grid">
                                <?php if (!empty($item['warranty_expiry'])): ?>
                                    <div class="detail-item">
                                        <div class="detail-label">Warranty Expiry</div>
                                        <div class="detail-value"><?php echo UtilityHelper::formatDateForDisplay($item['warranty_expiry']); ?></div>
                                    </div>
                                    
                                    <?php
                                    // Check if warranty is expired
                                    $today = new DateTime();
                                    $warrantyDate = new DateTime($item['warranty_expiry']);
                                    $isExpired = $warrantyDate < $today;
                                    $daysRemaining = $today->diff($warrantyDate)->days;
                                    ?>
                                    
                                    <div class="detail-item">
                                        <div class="detail-label">Warranty Status</div>
                                        <div class="detail-value">
                                            <?php if ($isExpired): ?>
                                                <span class="badge badge-red">Expired</span>
                                            <?php else: ?>
                                                <span class="badge badge-green">Active (<?php echo $daysRemaining; ?> days remaining)</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="detail-item">
                                        <div class="detail-label">Warranty Information</div>
                                        <div class="detail-value">No warranty information available</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="section">
                            <h3 class="section-title">Maintenance Information</h3>
                            <div class="details-grid">
                                <?php if (!empty($item['maintenance_interval'])): ?>
                                    <div class="detail-item">
                                        <div class="detail-label">Maintenance Interval</div>
                                        <div class="detail-value"><?php echo $item['maintenance_interval']; ?> days</div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($item['next_maintenance_date'])): ?>
                                    <div class="detail-item">
                                        <div class="detail-label">Next Maintenance</div>
                                        <div class="detail-value"><?php echo UtilityHelper::formatDateForDisplay($item['next_maintenance_date']); ?></div>
                                    </div>
                                    
                                    <?php
                                    // Check if maintenance is due
                                    $today = new DateTime();
                                    $maintenanceDate = new DateTime($item['next_maintenance_date']);
                                    $isDue = $maintenanceDate <= $today;
                                    $daysDifference = $today->diff($maintenanceDate)->days;
                                    $daysPrefix = $maintenanceDate < $today ? 'overdue by' : 'in';
                                    ?>
                                    
                                    <div class="detail-item">
                                        <div class="detail-label">Maintenance Status</div>
                                        <div class="detail-value">
                                            <?php if ($isDue): ?>
                                                <span class="badge badge-red">Due Now (<?php echo $daysDifference; ?> days overdue)</span>
                                            <?php else: ?>
                                                <span class="badge badge-blue">Scheduled (<?php echo $daysPrefix; ?> <?php echo $daysDifference; ?> days)</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="detail-item">
                                        <div class="detail-label">Maintenance Schedule</div>
                                        <div class="detail-value">No maintenance schedule set</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($borrowHistory['requests'])): ?>
                <!-- Borrow History Tab -->
                <div class="tab-content" id="history">
                    <div class="tab-content-inner">
                        <div class="section">
                            <h3 class="section-title">Borrow History</h3>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Request ID</th>
                                            <th>Borrower</th>
                                            <th>Department</th>
                                            <th>Borrow Date</th>
                                            <th>Return Date</th>
                                            <th>Status</th>
                                            <?php if (hasRole(['admin', 'manager'])): ?>
                                            <th>Actions</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($borrowHistory['requests'] as $request): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($request['request_id']); ?></td>
                                                <td><?php echo htmlspecialchars($request['requester_name']); ?></td>
                                                <td><?php echo htmlspecialchars($request['department_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo UtilityHelper::formatDateForDisplay($request['borrow_date'], 'short'); ?></td>
                                                <td><?php echo UtilityHelper::formatDateForDisplay($request['return_date'], 'short'); ?></td>
                                                <td>
                                                    <?php 
                                                    $historyStatusClass = '';
                                                    switch ($request['status']) {
                                                        case 'pending': $historyStatusClass = 'badge-yellow'; break;
                                                        case 'approved': $historyStatusClass = 'badge-blue'; break;
                                                        case 'checked_out': $historyStatusClass = 'badge-orange'; break;
                                                        case 'returned': $historyStatusClass = 'badge-green'; break;
                                                        case 'overdue': $historyStatusClass = 'badge-red'; break;
                                                        case 'rejected': $historyStatusClass = 'badge-red'; break;
                                                        default: $historyStatusClass = 'badge-gray';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $historyStatusClass; ?>"><?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?></span>
                                                </td>
                                                <?php if (hasRole(['admin', 'manager'])): ?>
                                                <td>
                                                    <a href="<?php echo BASE_URL; ?>/borrow/view-request?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-outline">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php if (count($borrowHistory['requests']) >= 5): ?>
                                <div class="text-center" style="margin-top: 1rem;">
                                    <a href="<?php echo BASE_URL; ?>/borrow/history?item_id=<?php echo $itemId; ?>" class="btn btn-outline">
                                        View All History
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (hasRole(['admin', 'manager'])): ?>
                <!-- Management Tab (Admin/Manager Only) -->
                <div class="tab-content" id="management">
                    <div class="tab-content-inner">
                        <div class="section">
                            <h3 class="section-title">Management Options</h3>
                            <div class="management-actions">
                                <a href="<?php echo BASE_URL; ?>/items/edit?id=<?php echo $itemId; ?>" class="action-card">
                                    <div class="action-icon"><i class="fas fa-edit"></i></div>
                                    <div class="action-title">Edit Item</div>
                                    <div class="action-description">Update item information, specifications, or images</div>
                                </a>
                                
                                <?php if ($item['status'] !== 'maintenance'): ?>
                                <a href="<?php echo BASE_URL; ?>/maintenance/create?item_id=<?php echo $itemId; ?>" class="action-card">
                                    <div class="action-icon"><i class="fas fa-tools"></i></div>
                                    <div class="action-title">Schedule Maintenance</div>
                                    <div class="action-description">Create maintenance record and update item status</div>
                                </a>
                                <?php endif; ?>
                                
                                <a href="<?php echo BASE_URL; ?>/items/transfer?id=<?php echo $itemId; ?>" class="action-card">
                                    <div class="action-icon"><i class="fas fa-exchange-alt"></i></div>
                                    <div class="action-title">Transfer Item</div>
                                    <div class="action-description">Move item to a different location</div>
                                </a>
                                
                                <?php if ($item['is_active']): ?>
                                <a href="<?php echo BASE_URL; ?>/items/deactivate?id=<?php echo $itemId; ?>" class="action-card">
                                    <div class="action-icon"><i class="fas fa-archive"></i></div>
                                    <div class="action-title">Deactivate Item</div>
                                    <div class="action-description">Mark item as inactive (will not appear in search)</div>
                                </a>
                                <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>/items/activate?id=<?php echo $itemId; ?>" class="action-card">
                                    <div class="action-icon"><i class="fas fa-check-circle"></i></div>
                                    <div class="action-title">Activate Item</div>
                                    <div class="action-description">Mark item as active and available</div>
                                </a>
                                <?php endif; ?>
                                
                                <div class="action-card danger-action" id="delete-item-btn">
                                    <div class="action-icon"><i class="fas fa-trash"></i></div>
                                    <div class="action-title">Delete Item</div>
                                    <div class="action-description">Permanently remove this item from inventory</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="section">
                            <h3 class="section-title">System Information</h3>
                            <div class="details-grid">
                                <div class="detail-item">
                                    <div class="detail-label">Date Created</div>
                                    <div class="detail-value"><?php echo UtilityHelper::formatDateForDisplay($item['created_at'], 'datetime'); ?></div>
                                </div>
                                
                                <?php if (!empty($item['updated_at'])): ?>
                                <div class="detail-item">
                                    <div class="detail-label">Last Updated</div>
                                    <div class="detail-value"><?php echo UtilityHelper::formatDateForDisplay($item['updated_at'], 'datetime'); ?></div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Status</div>
                                    <div class="detail-value">
                                        <?php echo $item['is_active'] ? '<span class="badge badge-green">Active</span>' : '<span class="badge badge-red">Inactive</span>'; ?>
                                    </div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Item ID</div>
                                    <div class="detail-value"><?php echo $item['id']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal (Initially Hidden) -->
<?php if (!$isModal && hasRole(['admin', 'manager'])): ?>
<div class="modal-backdrop" id="delete-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Delete Item</div>
            <button class="modal-close" id="close-delete-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Warning:</strong> This action cannot be undone.
            </div>
            <p>Are you sure you want to permanently delete the following item?</p>
            <div class="item-to-delete">
                <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                <?php if (!empty($item['asset_id'])): ?>
                <div>Asset ID: <?php echo htmlspecialchars($item['asset_id']); ?></div>
                <?php endif; ?>
            </div>
            <p>This will remove all associated images, transaction history, and other data related to this item.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="cancel-delete">Cancel</button>
            <a href="<?php echo BASE_URL; ?>/items/delete?id=<?php echo $itemId; ?>&token=<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>" class="btn btn-danger">
                <i class="fas fa-trash btn-icon"></i> Delete Item
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.item-view-container {
    margin-bottom: 2rem;
}

.item-view-layout {
    display: flex;
    flex-wrap: wrap;
    gap: 2rem;
}

/* Item Images Styling */
.item-images {
    flex: 0 0 350px;
}

.item-main-image {
    height: 350px;
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid var(--gray-200);
    background-color: var(--gray-50);
    margin-bottom: 1rem;
    position: relative;
}

.item-main-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center;
}

.no-image {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: var(--gray-400);
}

.no-image i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.item-thumbnails {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    padding-bottom: 0.5rem;
    scrollbar-width: thin;
}

.thumbnail {
    width: 70px;
    height: 70px;
    border-radius: 6px;
    overflow: hidden;
    border: 2px solid var(--gray-200);
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.thumbnail:hover {
    border-color: var(--primary-light);
}

.thumbnail.active {
    border-color: var(--primary);
}

.thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
}

/* Item Details Styling */
.item-details {
    flex: 1;
    min-width: 280px;
}

.item-header {
    margin-bottom: 1rem;
}

.item-name {
    font-size: 1.75rem;
    font-weight: 600;
    margin: 0 0 0.75rem 0;
    color: var(--gray-800);
}

.item-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.badge {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 500;
    border-radius: 4px;
    color: white;
}

.badge-green {
    background-color: var(--success);
}

.badge-blue {
    background-color: var(--primary);
}

.badge-orange {
    background-color: var(--warning);
}

.badge-yellow {
    background-color: var(--warning-light);
    color: var(--gray-800);
}

.badge-red {
    background-color: var(--danger);
}

.badge-gray {
    background-color: var(--gray-500);
}

.item-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.tag {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    font-size: 0.85rem;
    background-color: var(--gray-100);
    color: var(--gray-700);
    border-radius: 50px;
    border: 1px solid var(--gray-200);
}

.item-description {
    margin-bottom: 1.5rem;
    color: var(--gray-700);
    line-height: 1.6;
}

.item-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.info-item {
    border: 1px solid var(--gray-200);
    border-radius: 6px;
    padding: 0.75rem;
    background-color: var(--gray-50);
}

.info-label {
    font-size: 0.85rem;
    color: var(--gray-500);
    margin-bottom: 0.25rem;
}

.info-value {
    font-weight: 500;
    color: var(--gray-800);
}

/* Tabs & Tab Content */
.item-tabs {
    margin-top: 1rem;
}

.tabs {
    display: flex;
    border-bottom: 1px solid var(--gray-200);
    margin-bottom: 1.5rem;
    overflow-x: auto;
    scrollbar-width: none; /* For Firefox */
}

.tabs::-webkit-scrollbar {
    display: none; /* For Chrome, Safari, and Opera */
}

.tab {
    padding: 1rem 1.5rem;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    font-weight: 500;
    white-space: nowrap;
    transition: all 0.2s ease;
}

.tab:hover {
    color: var(--primary);
    background-color: var(--gray-50);
}

.tab.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
    font-weight: 600;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

.tab-content-inner {
    padding: 0 0.5rem;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Sections */
.section {
    margin-bottom: 2rem;
}

.section-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin: 0 0 1rem 0;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--gray-200);
    color: var(--gray-700);
}

.section-content {
    line-height: 1.6;
    color: var(--gray-700);
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
}

.detail-item {
    padding: 0.75rem;
    border-radius: 6px;
    background-color: var(--gray-50);
    border: 1px solid var(--gray-200);
}

.detail-label {
    font-size: 0.85rem;
    color: var(--gray-500);
    margin-bottom: 0.25rem;
}

.detail-value {
    font-weight: 500;
    color: var(--gray-800);
}

/* Table styling */
.table-responsive {
    overflow-x: auto;
    border: 1px solid var(--gray-200);
    border-radius: 6px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid var(--gray-200);
}

th {
    background-color: var(--gray-50);
    font-weight: 600;
    color: var(--gray-700);
}

tr:last-child td {
    border-bottom: none;
}

/* Management actions */
.management-actions {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
}

.action-card {
    display: flex;
    flex-direction: column;
    padding: 1.25rem;
    border-radius: 8px;
    border: 1px solid var(--gray-200);
    background-color: white;
    transition: all 0.2s ease;
    text-decoration: none;
    color: var(--gray-800);
    cursor: pointer;
}

.action-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-color: var(--primary-light);
}

.action-icon {
    font-size: 1.5rem;
    margin-bottom: 0.75rem;
    color: var(--primary);
}

.action-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--gray-800);
}

.action-description {
    font-size: 0.9rem;
    color: var(--gray-600);
    line-height: 1.4;
}

.danger-action {
    border-color: var(--danger-light);
}

.danger-action .action-icon {
    color: var(--danger);
}

.danger-action:hover {
    border-color: var(--danger);
    background-color: var(--danger-light);
}

/* Modal styling */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal {
    background-color: white;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem;
    border-bottom: 1px solid var(--gray-200);
}

.modal-title {
    font-weight: 600;
    font-size: 1.25rem;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: var(--gray-500);
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.25rem;
    border-top: 1px solid var(--gray-200);
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

.item-to-delete {
    background-color: var(--gray-50);
    border: 1px solid var(--gray-200);
    border-radius: 6px;
    padding: 1rem;
    margin: 1rem 0;
}

/* Alert */
.alert {
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1rem;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.alert-danger {
    background-color: var(--danger-light);
    border: 1px solid var(--danger);
    color: var(--danger-dark);
}

.alert i {
    font-size: 1.25rem;
}

/* Text utilities */
.text-center {
    text-align: center;
}

/* Responsive adaptations */
@media (max-width: 768px) {
    .item-view-layout {
        flex-direction: column;
    }
    
    .item-images {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .item-main-image {
        height: 250px;
    }
    
    .management-actions {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabs = document.querySelectorAll('.tabs .tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            tabs.forEach(t => {
                t.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabId).classList.add('active');
            
            // Add active class to selected tab
            this.classList.add('active');
        });
    });
    
    // Image gallery functionality
    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.getElementById('main-image');
    
    if (thumbnails.length > 0 && mainImage) {
        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                // Update main image src
                const imgSrc = this.getAttribute('data-src');
                mainImage.src = imgSrc;
                
                // Update active thumbnail
                thumbnails.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }
    
    // Delete modal functionality
    const deleteBtn = document.getElementById('delete-item-btn');
    const deleteModal = document.getElementById('delete-modal');
    const closeDeleteModal = document.getElementById('close-delete-modal');
    const cancelDelete = document.getElementById('cancel-delete');
    
    if (deleteBtn && deleteModal) {
        deleteBtn.addEventListener('click', function() {
            deleteModal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
        });
        
        if (closeDeleteModal) {
            closeDeleteModal.addEventListener('click', function() {
                deleteModal.style.display = 'none';
                document.body.style.overflow = ''; // Restore scrolling
            });
        }
        
        if (cancelDelete) {
            cancelDelete.addEventListener('click', function() {
                deleteModal.style.display = 'none';
                document.body.style.overflow = ''; // Restore scrolling
            });
        }
        
        // Close modal when clicking outside
        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) {
                deleteModal.style.display = 'none';
                document.body.style.overflow = ''; // Restore scrolling
            }
        });
        
        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && deleteModal.style.display === 'flex') {
                deleteModal.style.display = 'none';
                document.body.style.overflow = ''; // Restore scrolling
            }
        });
    }
});
</script>

<?php if (!$isModal): ?>
<?php
// Include footer if not in modal view
include 'includes/footer.php';
?>
<?php endif; ?>