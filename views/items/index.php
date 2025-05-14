<?php
/**
 * Admin Inventory Management Overview
 * 
 * This page displays an overview of all inventory items with management options
 */

// Set page title
$pageTitle = 'Inventory Management';

// Check if user has appropriate permissions
if (!hasRole(['admin', 'manager'])) {
    redirect(BASE_URL . '/errors/403');
    exit;
}

// Get current page from query string
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Get filters from query string
$filters = [
    'search' => isset($_GET['q']) ? cleanInput($_GET['q']) : '',
    'category_id' => isset($_GET['category']) ? (int)$_GET['category'] : '',
    'location_id' => isset($_GET['location']) ? (int)$_GET['location'] : '',
    'status' => isset($_GET['status']) ? cleanInput($_GET['status']) : '',
    
];

// Get all items with pagination
$itemsResult = InventoryHelper::getAllItems($page, ITEMS_PER_PAGE, $filters);
$items = $itemsResult['items'];
$pagination = $itemsResult['pagination'];

// Get categories for filter
$categories = InventoryHelper::getAllCategories();

// Get locations for filter
$locationResult = LocationHelper::getAllLocations(1, 100, ['is_active' => 1]);

// Include header
include 'includes/header.php';
?>

<div class="content-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">Inventory Management</h1>
        <div>
            <a href="<?php echo BASE_URL; ?>/items/create" class="btn btn-primary">
                <i class="fas fa-plus btn-icon"></i> Add New Item
            </a>
        </div>
    </div>
    <div class="breadcrumbs">
        <div class="breadcrumb-item">Home</div>
        <div class="breadcrumb-item">Inventory</div>
    </div>
</div>

<!-- Stats Cards -->
<div class="dashboard-cards">
    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <div class="dashboard-card-title">Total Items</div>
            <div class="dashboard-card-icon">
                <i class="fas fa-box"></i>
            </div>
        </div>
        <div class="dashboard-card-value"><?php echo $pagination['totalItems']; ?></div>
        <div class="dashboard-card-description">Items in inventory</div>
    </div>
    
    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <div class="dashboard-card-title">Available</div>
            <div class="dashboard-card-icon card-icon-green">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="dashboard-card-value">
            <?php 
            $availableCount = 0;
            foreach ($items as $item) {
                if ($item['status'] === 'available') {
                    $availableCount++;
                }
            }
            echo $availableCount;
            ?>
        </div>
        <div class="dashboard-card-description">Ready to borrow</div>
    </div>
    
    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <div class="dashboard-card-title">Borrowed</div>
            <div class="dashboard-card-icon card-icon-orange">
                <i class="fas fa-user-clock"></i>
            </div>
        </div>
        <div class="dashboard-card-value">
            <?php 
            $borrowedCount = 0;
            foreach ($items as $item) {
                if ($item['status'] === 'borrowed') {
                    $borrowedCount++;
                }
            }
            echo $borrowedCount;
            ?>
        </div>
        <div class="dashboard-card-description">Currently checked out</div>
    </div>
    
    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <div class="dashboard-card-title">In Maintenance</div>
            <div class="dashboard-card-icon card-icon-blue">
                <i class="fas fa-tools"></i>
            </div>
        </div>
        <div class="dashboard-card-value">
            <?php 
            $maintenanceCount = 0;
            foreach ($items as $item) {
                if ($item['status'] === 'maintenance') {
                    $maintenanceCount++;
                }
            }
            echo $maintenanceCount;
            ?>
        </div>
        <div class="dashboard-card-description">Under maintenance</div>
    </div>
</div>

<!-- Filters Panel -->
<div class="panel" style="margin-bottom: 1.5rem;">
    <div class="panel-header">
        <div class="panel-title">Filters</div>
        <a href="<?php echo BASE_URL; ?>/items" class="btn btn-sm btn-outline">
            <i class="fas fa-redo-alt btn-icon"></i>
            Reset
        </a>
    </div>
    <div class="panel-body">
        <form action="<?php echo BASE_URL; ?>/items" method="GET" id="filter-form">
            <div class="form-row">
                <div class="form-col">
                    <label class="form-label">Category</label>
                    <select class="form-control" name="category" id="category-filter">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo ($filters['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                            <?php foreach ($category['children'] as $child): ?>
                                <option value="<?php echo $child['id']; ?>" <?php echo ($filters['category_id'] == $child['id']) ? 'selected' : ''; ?>>
                                    &nbsp;&nbsp;└ <?php echo htmlspecialchars($child['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-col">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status" id="status-filter">
                        <option value="">All Statuses</option>
                        <option value="available" <?php echo ($filters['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                        <option value="borrowed" <?php echo ($filters['status'] == 'borrowed') ? 'selected' : ''; ?>>Borrowed</option>
                        <option value="maintenance" <?php echo ($filters['status'] == 'maintenance') ? 'selected' : ''; ?>>In Maintenance</option>
                        <option value="reserved" <?php echo ($filters['status'] == 'reserved') ? 'selected' : ''; ?>>Reserved</option>
                        <option value="retired" <?php echo ($filters['status'] == 'retired') ? 'selected' : ''; ?>>Retired</option>
                    </select>
                </div>

                
            </div>
            <div class="form-row" style="margin-top: 1rem;">
                <div class="form-col" style="flex: 2;">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="q" value="<?php echo htmlspecialchars($filters['search']); ?>" placeholder="Search by name, ID, serial number...">
                </div>
                <div class="form-col" style="align-self: flex-end;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search btn-icon"></i> Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Actions Bar -->
<div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem;">
    <div>
        <span style="color: var(--gray-500);">Showing <?php echo $pagination['totalItems'] > 0 ? ($pagination['offset'] + 1) : 0; ?>-<?php echo min($pagination['offset'] + $pagination['itemsPerPage'], $pagination['totalItems']); ?> of <?php echo $pagination['totalItems']; ?> items</span>
    </div>
    <div>
        <button class="btn btn-outline" id="bulk-action-btn" disabled>
            <i class="fas fa-cog btn-icon"></i> Bulk Actions
        </button>
        <button class="btn btn-outline" id="export-btn">
            <i class="fas fa-file-export btn-icon"></i> Export
        </button>
        <button class="btn btn-outline" id="print-btn">
            <i class="fas fa-print btn-icon"></i> Print
        </button>
    </div>
</div>

<?php if (count($items) > 0): ?>
    <!-- Inventory Table -->
    <div class="panel">
        <div class="panel-body table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th width="40px">
                            <div class="form-check">
                                <input type="checkbox" id="select-all" class="form-check-input">
                            </div>
                        </th>
                        <th>Asset ID</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Status</th>

                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr class="item-row" data-id="<?php echo $item['id']; ?>">
                            <td>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input item-checkbox" value="<?php echo $item['id']; ?>">
                                </div>
                            </td>
                            <td><?php echo !empty($item['id']) ? htmlspecialchars($item['id']) : '-'; ?></td>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <div style="width: 40px; height: 40px; background-color: var(--gray-100); border-radius: 4px; margin-right: 10px; display: flex; align-items: center; justify-content: center;">
                                        <?php 
                                        // Determine icon based on category
                                        $iconClass = 'box';
                                        
                                        if (stripos($item['category_name'], 'computer') !== false || stripos($item['name'], 'laptop') !== false) {
                                            $iconClass = 'laptop';
                                        } elseif (stripos($item['category_name'], 'audio') !== false) {
                                            $iconClass = 'headphones';
                                        } elseif (stripos($item['category_name'], 'video') !== false || stripos($item['name'], 'projector') !== false) {
                                            $iconClass = 'video';
                                        } elseif (stripos($item['category_name'], 'camera') !== false || stripos($item['category_name'], 'photo') !== false) {
                                            $iconClass = 'camera';
                                        } elseif (stripos($item['name'], 'tablet') !== false || stripos($item['name'], 'ipad') !== false) {
                                            $iconClass = 'tablet-alt';
                                        } elseif (stripos($item['name'], 'microphone') !== false || stripos($item['name'], 'mic') !== false) {
                                            $iconClass = 'microphone';
                                        }
                                        ?>
                                        <i class="fas fa-<?php echo $iconClass; ?>" style="color: var(--gray-500);"></i>
                                    </div>
                                    <div>
                                        <a href="<?php echo BASE_URL; ?>/items/view?id=<?php echo $item['id']; ?>" style="font-weight: 500;"><?php echo htmlspecialchars($item['name']); ?></a>
                                        <?php if (!empty($item['brand']) || !empty($item['model'])): ?>
                                            <div style="font-size: 0.85rem; color: var(--gray-500);">
                                                <?php 
                                                $details = [];
                                                if (!empty($item['brand'])) $details[] = htmlspecialchars($item['brand']);
                                                if (!empty($item['model'])) $details[] = htmlspecialchars($item['model']);
                                                echo implode(' | ', $details);
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo !empty($item['category_name']) ? htmlspecialchars($item['category_name']) : 'Uncategorized'; ?></td>
                            <td><?php echo !empty($item['location_name']) ? htmlspecialchars($item['location_name']) : 'Not Set'; ?></td>
                            <td>
                                <?php 
                                $statusClass = '';
                                $statusText = 'Available';
                                
                                if ($item['status'] === 'available') {
                                    $statusClass = 'badge-green';
                                    $statusText = 'Available';
                                } elseif ($item['status'] === 'borrowed') {
                                    $statusClass = 'badge-orange';
                                    $statusText = 'Borrowed';
                                } elseif ($item['status'] === 'reserved') {
                                    $statusClass = 'badge-purple';
                                    $statusText = 'Reserved';
                                } elseif ($item['status'] === 'maintenance') {
                                    $statusClass = 'badge-blue';
                                    $statusText = 'Maintenance';
                                } elseif ($item['status'] === 'retired' || $item['status'] === 'unavailable') {
                                    $statusClass = 'badge-red';
                                    $statusText = 'Unavailable';
                                }
                                ?>
                                <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                            </td>
                          
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline dropdown-toggle">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu">
                                        <a href="<?php echo BASE_URL; ?>/items/view?id=<?php echo $item['id']; ?>" class="dropdown-item">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/items/edit?id=<?php echo $item['id']; ?>" class="dropdown-item">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        
                                        <?php if ($item['status'] === 'available'): ?>
                                            <a href="<?php echo BASE_URL; ?>/borrow/create?item_id=<?php echo $item['id']; ?>" class="dropdown-item">
                                                <i class="fas fa-hand-holding"></i> Create Borrow Request
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($item['status'] !== 'maintenance'): ?>
                                            <a href="<?php echo BASE_URL; ?>/maintenance/create?item_id=<?php echo $item['id']; ?>" class="dropdown-item">
                                                <i class="fas fa-tools"></i> Schedule Maintenance
                                            </a>
                                        <?php endif; ?>
                                        
                                        <div class="dropdown-divider"></div>
                                        
                                        <a href="<?php echo BASE_URL; ?>/items/delete?id=<?php echo $item['id']; ?>" class="dropdown-item text-danger delete-item" data-id="<?php echo $item['id']; ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['totalPages'] > 1): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
            <div class="pagination-info">
                Showing <?php echo $pagination['totalItems'] > 0 ? ($pagination['offset'] + 1) : 0; ?>-<?php echo min($pagination['offset'] + $pagination['itemsPerPage'], $pagination['totalItems']); ?> of <?php echo $pagination['totalItems']; ?> items
            </div>
            <div class="pagination">
                <?php if ($pagination['currentPage'] > 1): ?>
                    <a href="<?php echo BASE_URL; ?>/items?page=<?php echo ($pagination['currentPage'] - 1); ?>&category=<?php echo $filters['category_id']; ?>&location=<?php echo $filters['location_id']; ?>&status=<?php echo $filters['status']; ?>&supplier=<?php echo $filters['supplier_id']; ?>&q=<?php echo urlencode($filters['search']); ?>" class="page-btn">
                        <i class="fas fa-angle-left"></i>
                    </a>
                <?php else: ?>
                    <button class="page-btn disabled">
                        <i class="fas fa-angle-left"></i>
                    </button>
                <?php endif; ?>

                <?php
                // Calculate page range to display
                $startPage = max(1, $pagination['currentPage'] - 2);
                $endPage = min($pagination['totalPages'], $pagination['currentPage'] + 2);
                
                // Always show first page
                if ($startPage > 1) {
                    echo '<a href="' . BASE_URL . '/items?page=1&category=' . $filters['category_id'] . '&location=' . $filters['location_id'] . '&status=' . $filters['status'] . '&supplier=' . $filters['supplier_id'] . '&q=' . urlencode($filters['search']) . '" class="page-btn">1</a>';
                    if ($startPage > 2) {
                        echo '<span class="page-btn disabled">...</span>';
                    }
                }
                
                // Display page numbers
                for ($i = $startPage; $i <= $endPage; $i++) {
                    if ($i == $pagination['currentPage']) {
                        echo '<span class="page-btn active">' . $i . '</span>';
                    } else {
                        echo '<a href="' . BASE_URL . '/items?page=' . $i . '&category=' . $filters['category_id'] . '&location=' . $filters['location_id'] . '&status=' . $filters['status'] . '&supplier=' . $filters['supplier_id'] . '&q=' . urlencode($filters['search']) . '" class="page-btn">' . $i . '</a>';
                    }
                }
                
                // Always show last page
                if ($endPage < $pagination['totalPages']) {
                    if ($endPage < $pagination['totalPages'] - 1) {
                        echo '<span class="page-btn disabled">...</span>';
                    }
                    echo '<a href="' . BASE_URL . '/items?page=' . $pagination['totalPages'] . '&category=' . $filters['category_id'] . '&location=' . $filters['location_id'] . '&status=' . $filters['status'] . '&supplier=' . $filters['supplier_id'] . '&q=' . urlencode($filters['search']) . '" class="page-btn">' . $pagination['totalPages'] . '</a>';
                }
                ?>

                <?php if ($pagination['currentPage'] < $pagination['totalPages']): ?>
                    <a href="<?php echo BASE_URL; ?>/items?page=<?php echo ($pagination['currentPage'] + 1); ?>&category=<?php echo $filters['category_id']; ?>&location=<?php echo $filters['location_id']; ?>&status=<?php echo $filters['status']; ?>&supplier=<?php echo $filters['supplier_id']; ?>&q=<?php echo urlencode($filters['search']); ?>" class="page-btn">
                        <i class="fas fa-angle-right"></i>
                    </a>
                <?php else: ?>
                    <button class="page-btn disabled">
                        <i class="fas fa-angle-right"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="panel" style="text-align: center; padding: 3rem 1.5rem;">
        <div style="font-size: 3rem; color: var(--gray-400); margin-bottom: 1.5rem;">
            <i class="fas fa-box-open"></i>
        </div>
        <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">No Inventory Items Found</h2>
        <p style="color: var(--gray-600); margin-bottom: 1.5rem;">There are no items matching your search criteria. Try adjusting your filters or add a new item.</p>
        <a href="<?php echo BASE_URL; ?>/items/create" class="btn btn-primary">
            <i class="fas fa-plus btn-icon"></i> Add New Item
        </a>
    </div>
<?php endif; ?>

<!-- Bulk Actions Dropdown (Initially Hidden) -->
<div class="dropdown-menu" id="bulk-actions-menu" style="display: none; position: absolute; z-index: 1000; min-width: 200px; background-color: white; border: 1px solid var(--gray-200); border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); padding: 0.5rem 0;">
    <a href="#" class="dropdown-item bulk-action" data-action="export" style="display: block; padding: 0.5rem 1rem; color: var(--gray-700); text-decoration: none;">
        <i class="fas fa-file-export"></i> Export Selected
    </a>
    <a href="#" class="dropdown-item bulk-action" data-action="change-status" style="display: block; padding: 0.5rem 1rem; color: var(--gray-700); text-decoration: none;">
        <i class="fas fa-exchange-alt"></i> Change Status
    </a>
    <a href="#" class="dropdown-item bulk-action" data-action="change-location" style="display: block; padding: 0.5rem 1rem; color: var(--gray-700); text-decoration: none;">
        <i class="fas fa-map-marker-alt"></i> Change Location
    </a>
    <div class="dropdown-divider" style="height: 0; margin: 0.5rem 0; border-top: 1px solid var(--gray-200);"></div>
    <a href="#" class="dropdown-item bulk-action text-danger" data-action="delete" style="display: block; padding: 0.5rem 1rem; color: var(--danger); text-decoration: none;">
        <i class="fas fa-trash"></i> Delete Selected
    </a>
</div>

<!-- Change Status Modal (Initially Hidden) -->
<div class="modal-backdrop" id="change-status-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Change Item Status</div>
            <button class="modal-close" id="close-status-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="change-status-form">
                <input type="hidden" id="status-item-ids" name="item_ids" value="">
                
                <div class="form-group">
                    <label class="form-label" for="new-status">New Status</label>
                    <select class="form-control" id="new-status" name="status" required>
                        <option value="">Select status...</option>
                        <option value="available">Available</option>
                        <option value="reserved">Reserved</option>
                        <option value="maintenance">In Maintenance</option>
                        <option value="retired">Retired</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="status-note">Note (Optional)</label>
                    <textarea class="form-control" id="status-note" name="note" rows="3" placeholder="Add a note about this status change..."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="cancel-status-btn">Cancel</button>
            <button class="btn btn-primary" id="save-status-btn">
                <i class="fas fa-save btn-icon"></i> Save Changes
            </button>
        </div>
    </div>
</div>

<!-- Change Location Modal (Initially Hidden) -->
<div class="modal-backdrop" id="change-location-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Change Item Location</div>
            <button class="modal-close" id="close-location-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="change-location-form">
                <input type="hidden" id="location-item-ids" name="item_ids" value="">
                
                <div class="form-group">
                    <label class="form-label" for="new-location">New Location</label>
                    <select class="form-control" id="new-location" name="location_id" required>
                        <option value="">Select location...</option>
                        <?php foreach ($locations as $location): ?>
                            <option value="<?php echo $location['id']; ?>"><?php echo htmlspecialchars($location['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="location-note">Note (Optional)</label>
                    <textarea class="form-control" id="location-note" name="note" rows="3" placeholder="Add a note about this location change..."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="cancel-location-btn">Cancel</button>
            <button class="btn btn-primary" id="save-location-btn">
                <i class="fas fa-save btn-icon"></i> Save Changes
            </button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal (Initially Hidden) -->
<div class="modal-backdrop" id="delete-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Confirm Deletion</div>
            <button class="modal-close" id="close-delete-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> Warning: This action cannot be undone!
            </div>
            <p>Are you sure you want to delete the selected item(s)? This will permanently remove them from the inventory system.</p>
            <div id="delete-items-list"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="cancel-delete-btn">Cancel</button>
            <button class="btn btn-danger" id="confirm-delete-btn">
                <i class="fas fa-trash btn-icon"></i> Delete
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Item selection functionality
    const selectAllCheckbox = document.getElementById('select-all');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const bulkActionBtn = document.getElementById('bulk-action-btn');
    const bulkActionsMenu = document.getElementById('bulk-actions-menu');
    
    // Select all checkbox
    selectAllCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
        });
        
        updateBulkActionButton();
    });
    
    // Individual checkboxes
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkActionButton();
            
            // Update "select all" checkbox state
            if (!this.checked) {
                selectAllCheckbox.checked = false;
            } else {
                const allChecked = [...itemCheckboxes].every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
            }
        });
    });
    
    // Update bulk action button state
    function updateBulkActionButton() {
        const checkedCount = [...itemCheckboxes].filter(cb => cb.checked).length;
        
        if (checkedCount > 0) {
            bulkActionBtn.removeAttribute('disabled');
            bulkActionBtn.textContent = `Bulk Actions (${checkedCount})`;
        } else {
            bulkActionBtn.setAttribute('disabled', 'disabled');
            bulkActionBtn.innerHTML = '<i class="fas fa-cog btn-icon"></i> Bulk Actions';
        }
    }
    
    // Bulk actions dropdown
    bulkActionBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        
        const rect = this.getBoundingClientRect();
        bulkActionsMenu.style.display = 'block';
        bulkActionsMenu.style.top = (rect.bottom + window.scrollY) + 'px';
        bulkActionsMenu.style.left = rect.left + 'px';
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function() {
        bulkActionsMenu.style.display = 'none';
    });
    
    // Prevent dropdown from closing when clicking inside
    bulkActionsMenu.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // Bulk action handlers
    const bulkActions = document.querySelectorAll('.bulk-action');
    const changeStatusModal = document.getElementById('change-status-modal');
    const changeLocationModal = document.getElementById('change-location-modal');
    const deleteModal = document.getElementById('delete-modal');
    
    bulkActions.forEach(action => {
        action.addEventListener('click', function(e) {
            e.preventDefault();
            
            const actionType = this.getAttribute('data-action');
            const selectedItems = [...itemCheckboxes].filter(cb => cb.checked).map(cb => cb.value);
            
            if (selectedItems.length === 0) {
                return;
            }
            
            switch (actionType) {
                case 'export':
                    // Handle export
                    alert('Export functionality would go here');
                    break;
                    
                case 'change-status':
                    // Show change status modal
                    document.getElementById('status-item-ids').value = selectedItems.join(',');
                    changeStatusModal.style.display = 'flex';
                    break;
                    
                case 'change-location':
                    // Show change location modal
                    document.getElementById('location-item-ids').value = selectedItems.join(',');
                    changeLocationModal.style.display = 'flex';
                    break;
                    
                case 'delete':
                    // Show delete confirmation modal
                    const deleteItemsList = document.getElementById('delete-items-list');
                    deleteItemsList.innerHTML = '';
                    
                    if (selectedItems.length <= 5) {
                        // List items by name
                        const selectedRows = selectedItems.map(id => {
                            const row = document.querySelector(`.item-row[data-id="${id}"]`);
                            return row.querySelector('a').textContent;
                        });
                        
                        const ul = document.createElement('ul');
                        ul.style.marginTop = '1rem';
                        
                        selectedRows.forEach(name => {
                            const li = document.createElement('li');
                            li.textContent = name;
                            ul.appendChild(li);
                        });
                        
                        deleteItemsList.appendChild(ul);
                    } else {
                        // Just show count
                        const p = document.createElement('p');
                        p.style.marginTop = '1rem';
                        p.style.fontWeight = 'bold';
                        p.textContent = `${selectedItems.length} items selected for deletion`;
                        deleteItemsList.appendChild(p);
                    }
                    
                    // Store selected IDs for deletion
                    deleteItemsList.setAttribute('data-ids', selectedItems.join(','));
                    
                    deleteModal.style.display = 'flex';
                    break;
            }
            
            // Hide dropdown
            bulkActionsMenu.style.display = 'none';
        });
    });
    
    // Modal close handlers
    document.querySelectorAll('.modal-close, #cancel-status-btn, #cancel-location-btn, #cancel-delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            changeStatusModal.style.display = 'none';
            changeLocationModal.style.display = 'none';
            deleteModal.style.display = 'none';
        });
    });
    
    // Save status changes
    document.getElementById('save-status-btn').addEventListener('click', function() {
        const form = document.getElementById('change-status-form');
        const formData = new FormData(form);
        
        // In a real application, you would submit this data via AJAX
        // For now, we'll just show an alert
        alert('Status would be updated for items: ' + formData.get('item_ids'));
        
        // Close modal
        changeStatusModal.style.display = 'none';
    });
    
    // Save location changes
    document.getElementById('save-location-btn').addEventListener('click', function() {
        const form = document.getElementById('change-location-form');
        const formData = new FormData(form);
        
        // In a real application, you would submit this data via AJAX
        // For now, we'll just show an alert
        alert('Location would be updated for items: ' + formData.get('item_ids'));
        
        // Close modal
        changeLocationModal.style.display = 'none';
    });
    
    // Confirm delete
    document.getElementById('confirm-delete-btn').addEventListener('click', function() {
        const itemIds = document.getElementById('delete-items-list').getAttribute('data-ids');
        
        // In a real application, you would submit this data via AJAX
        // For now, we'll just show an alert
        alert('Items would be deleted: ' + itemIds);
        
        // Close modal
        deleteModal.style.display = 'none';
    });
    
    // Individual delete buttons
    document.querySelectorAll('.delete-item').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const itemId = this.getAttribute('data-id');
            const itemName = this.getAttribute('data-name');
            
            // Show delete confirmation modal
            const deleteItemsList = document.getElementById('delete-items-list');
            deleteItemsList.innerHTML = `<p style="margin-top: 1rem;">Are you sure you want to delete <strong>${itemName}</strong>?</p>`;
            deleteItemsList.setAttribute('data-ids', itemId);
            
            deleteModal.style.display = 'flex';
        });
    });
    
    // Export button
    document.getElementById('export-btn').addEventListener('click', function() {
        // This would be implemented with actual export functionality
        alert('Export functionality would go here');
    });
    
    // Print button
    document.getElementById('print-btn').addEventListener('click', function() {
        window.print();
    });
    
    // Initialize dropdowns if any
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const dropdown = this.nextElementSibling;
            
            // Close all other dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if (menu !== dropdown && menu.classList.contains('show')) {
                    menu.classList.remove('show');
                }
            });
            
            // Toggle this dropdown
            dropdown.classList.toggle('show');
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    });
});
</script>

<style>
/* Additional CSS Fixes */
.stat-card {
    background-color: white;
    border-radius: 8px;
    box-shadow: var(--shadow);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
}

.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Fix for dropdowns */
.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-menu {
    position: absolute;
    right: 0;
    z-index: 1000;
    display: none;
    min-width: 10rem;
    padding: 0.5rem 0;
    margin: 0;
    font-size: 0.875rem;
    color: var(--gray-700);
    text-align: left;
    list-style: none;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid var(--gray-200);
    border-radius: 0.25rem;
    box-shadow: var(--shadow-md);
}

.dropdown-menu.show {
    display: block;
}

.dropdown-divider {
    height: 0;
    margin: 0.5rem 0;
    overflow: hidden;
    border-top: 1px solid var(--gray-200);
}

.dropdown-item {
    display: block;
    width: 100%;
    padding: 0.5rem 1rem;
    clear: both;
    font-weight: 400;
    color: var(--gray-700);
    text-align: inherit;
    text-decoration: none;
    white-space: nowrap;
    background-color: transparent;
    border: 0;
}

.dropdown-item:hover {
    color: var(--gray-900);
    background-color: var(--gray-100);
}

.dropdown-item.active, 
.dropdown-item:active {
    color: #fff;
    text-decoration: none;
    background-color: var(--primary);
}

.info-table {
    width: 100%;
    border-collapse: collapse;
}

.info-table th {
    width: 30%;
    text-align: left;
    padding: 0.5rem 1rem 0.5rem 0;
    color: var(--gray-600);
    font-weight: 500;
    vertical-align: top;
}

.info-table td {
    padding: 0.5rem 0;
    vertical-align: top;
}
</style>

<?php
// Include footer
include 'includes/footer.php';
?>