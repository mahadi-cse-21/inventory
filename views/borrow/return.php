<?php
/**
 * Return Borrowed Items (Admin/Manager View)
 * 
 * This file allows admins/managers to process item returns
 */

// Set page title
$pageTitle = 'Return Borrowed Items';

// Check if user is logged in and has the right permissions
if (!AuthHelper::isAuthenticated() || !hasRole(['admin', 'manager'])) {
    setFlashMessage('You do not have permission to access this page.', 'danger');
    redirect(BASE_URL . '/dashboard');
    exit;
}

// Get current user
$currentUser = AuthHelper::getCurrentUser();

// Get request ID
$requestId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$requestId) {
    setFlashMessage('Invalid request ID', 'danger');
    redirect(BASE_URL . '/borrow/requests');
}

// Get request details
$request = BorrowHelper::getBorrowRequestById($requestId);

// Check if request exists
if (!$request) {
    setFlashMessage('Borrow request not found', 'danger');
    redirect(BASE_URL . '/borrow/requests');
}

// Check if request is in a valid state for returns
if (!in_array($request['status'], ['checked_out', 'overdue', 'partially_returned'])) {
    setFlashMessage('This request is not currently checked out', 'warning');
    redirect(BASE_URL . '/borrow/view?id=' . $requestId);
}

// Process return if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    validateCsrfToken();
    
    // Get items data
    $returnItems = isset($_POST['return_items']) ? $_POST['return_items'] : [];
    $conditions = isset($_POST['condition']) ? $_POST['condition'] : [];
    $notes = isset($_POST['notes']) ? $_POST['notes'] : [];
    
    // Make sure at least one item is selected
    if (empty($returnItems)) {
        setFlashMessage('Please select at least one item to return', 'danger');
        redirect(BASE_URL . '/borrow/return?id=' . $requestId);
        exit;
    }
    
    // Prepare items data for return
    $itemsData = [];
    foreach ($returnItems as $itemId) {
        $itemsData[] = [
            'item_id' => (int)$itemId,
            'condition_after' => isset($conditions[$itemId]) ? cleanInput($conditions[$itemId]) : 'good',
            'notes' => isset($notes[$itemId]) ? cleanInput($notes[$itemId]) : null
        ];
    }
    
    // Process return
    $result = BorrowHelper::returnBorrowedItems($requestId, $itemsData, $currentUser['id']);
    
    if ($result['success']) {
        if ($result['all_returned']) {
            setFlashMessage('All items have been returned successfully', 'success');
        } else {
            setFlashMessage('Items have been returned successfully', 'success');
        }
        redirect(BASE_URL . '/borrow/view?id=' . $requestId);
    } else {
        setFlashMessage($result['message'], 'danger');
        redirect(BASE_URL . '/borrow/return?id=' . $requestId);
    }
    
    exit;
}

// Include header
include 'includes/header.php';
?>

<div class="content-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Return Items</h1>
            <nav class="breadcrumbs">
                <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Home</a>
                <a href="<?php echo BASE_URL; ?>/borrow/requests" class="breadcrumb-item">Borrow Requests</a>
                <a href="<?php echo BASE_URL; ?>/borrow/view?id=<?php echo $request['id']; ?>" class="breadcrumb-item">View Request</a>
                <span class="breadcrumb-item">Return Items</span>
            </nav>
        </div>
        <div>
            <a href="<?php echo BASE_URL; ?>/borrow/view?id=<?php echo $request['id']; ?>" class="btn btn-outline">
                <i class="fas fa-arrow-left btn-icon"></i> Back to Request
            </a>
        </div>
    </div>
</div>

<div class="panel" style="margin-bottom: 1.5rem;">
    <div class="panel-header">
        <div class="panel-title">Request Information</div>
    </div>
    <div class="panel-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
            <div>
                <div style="font-weight: 600; margin-bottom: 0.25rem;">Request ID</div>
                <div><?php echo htmlspecialchars($request['request_id']); ?></div>
            </div>
            <div>
                <div style="font-weight: 600; margin-bottom: 0.25rem;">Requester</div>
                <div><?php echo htmlspecialchars($request['requester_name']); ?></div>
            </div>
            <div>
                <div style="font-weight: 600; margin-bottom: 0.25rem;">Department</div>
                <div><?php echo htmlspecialchars($request['department_name'] ?? 'Not Set'); ?></div>
            </div>
            <div>
                <div style="font-weight: 600; margin-bottom: 0.25rem;">Return Due Date</div>
                <div><?php echo UtilityHelper::formatDateForDisplay($request['return_date']); ?></div>
            </div>
            <div>
                <div style="font-weight: 600; margin-bottom: 0.25rem;">Status</div>
                <div>
                    <?php 
                    $badgeClass = 'badge-blue';
                    
                    switch ($request['status']) {
                        case 'checked_out':
                            $badgeClass = 'badge-purple';
                            break;
                        case 'overdue':
                            $badgeClass = 'badge-orange';
                            break;
                        case 'partially_returned':
                            $badgeClass = 'badge-yellow';
                            break;
                    }
                    ?>
                    <span class="badge <?php echo $badgeClass; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<form action="<?php echo BASE_URL; ?>/borrow/return?id=<?php echo $request['id']; ?>" method="POST">
    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
    
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">Select Items to Return</div>
        </div>
        <div class="panel-body">
            <?php if (!empty($request['items'])): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="50px">
                                    <div class="form-check">
                                        <input type="checkbox" id="select_all" class="form-check-input">
                                    </div>
                                </th>
                                <th>Item</th>
                                <th>Asset ID</th>
                                <th>Current Status</th>
                                <th>Return Condition</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $hasItemsToReturn = false;
                            foreach ($request['items'] as $item): 
                                // Skip already returned items
                                if ($item['status'] === 'returned') continue;
                                $hasItemsToReturn = true;
                            ?>
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input type="checkbox" name="return_items[]" value="<?php echo $item['item_id']; ?>" class="form-check-input item-checkbox" <?php echo ($item['status'] === 'checked_out') ? '' : 'disabled'; ?>>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center;">
                                            <?php 
                                            // Determine icon based on category
                                            $iconClass = 'box';
                                            
                                            if (!empty($item['category_name'])) {
                                                if (stripos($item['category_name'], 'computer') !== false || stripos($item['item_name'], 'laptop') !== false) {
                                                    $iconClass = 'laptop';
                                                } elseif (stripos($item['category_name'], 'audio') !== false) {
                                                    $iconClass = 'headphones';
                                                } elseif (stripos($item['category_name'], 'video') !== false || stripos($item['item_name'], 'projector') !== false) {
                                                    $iconClass = 'video';
                                                } elseif (stripos($item['category_name'], 'camera') !== false || stripos($item['category_name'], 'photo') !== false) {
                                                    $iconClass = 'camera';
                                                } elseif (stripos($item['item_name'], 'tablet') !== false || stripos($item['item_name'], 'ipad') !== false) {
                                                    $iconClass = 'tablet-alt';
                                                } elseif (stripos($item['item_name'], 'microphone') !== false || stripos($item['item_name'], 'mic') !== false) {
                                                    $iconClass = 'microphone';
                                                }
                                            }
                                            ?>
                                            <i class="fas fa-<?php echo $iconClass; ?>" style="margin-right: 10px; color: var(--gray-500);"></i>
                                            <div>
                                                <div style="font-weight: 500;"><?php echo htmlspecialchars($item['item_name']); ?></div>
                                                <?php if (!empty($item['category_name'])): ?>
                                                    <div style="font-size: 0.8rem; color: var(--gray-500);">
                                                        <?php echo htmlspecialchars($item['category_name']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['asset_id'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php 
                                        $itemBadgeClass = 'badge-blue';
                                        
                                        switch ($item['status']) {
                                            case 'checked_out':
                                                $itemBadgeClass = 'badge-purple';
                                                break;
                                            case 'returned':
                                                $itemBadgeClass = 'badge-green';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $itemBadgeClass; ?>">
                                            <?php echo ucfirst($item['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <select name="condition[<?php echo $item['item_id']; ?>]" class="form-control" <?php echo ($item['status'] !== 'checked_out') ? 'disabled' : ''; ?>>
                                            <option value="excellent">Excellent</option>
                                            <option value="good" selected>Good</option>
                                            <option value="fair">Fair</option>
                                            <option value="poor">Poor</option>
                                            <option value="damaged">Damaged</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="notes[<?php echo $item['item_id']; ?>]" class="form-control" placeholder="Optional notes..." <?php echo ($item['status'] !== 'checked_out') ? 'disabled' : ''; ?>>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (!$hasItemsToReturn): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> All items for this request have already been returned.
                    </div>
                <?php endif; ?>
                
                <?php if ($hasItemsToReturn): ?>
                    <div style="margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-primary" id="return-items-btn">
                            <i class="fas fa-undo-alt btn-icon"></i> Complete Return
                        </button>
                    </div>
                <?php else: ?>
                    <div style="margin-top: 1.5rem;">
                        <a href="<?php echo BASE_URL; ?>/borrow/view?id=<?php echo $request['id']; ?>" class="btn btn-outline">
                            <i class="fas fa-arrow-left btn-icon"></i> Back to Request
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> No items found for this request.
                </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox
    const selectAllCheckbox = document.getElementById('select_all');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox:not(:disabled)');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
        });
    }
    
    // Update select all checkbox state when individual items are checked/unchecked
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
            const anyChecked = Array.from(itemCheckboxes).some(cb => cb.checked);
            
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = !allChecked && anyChecked;
            }
        });
    });
    
    // Form validation
    const returnForm = document.querySelector('form');
    const returnItemsBtn = document.getElementById('return-items-btn');
    
    if (returnForm && returnItemsBtn) {
        returnForm.addEventListener('submit', function(e) {
            const selectedItems = document.querySelectorAll('.item-checkbox:checked');
            
            if (selectedItems.length === 0) {
                e.preventDefault();
                alert('Please select at least one item to return.');
                return;
            }
        });
    }
});
</script>

<style>
.form-check {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
}

.form-check-input {
    width: 1.2em;
    height: 1.2em;
    cursor: pointer;
}
</style>

<?php include 'includes/footer.php'; ?>