<?php
/**
 * Complete Maintenance Request
 * 
 * This file allows users to mark a maintenance request as complete
 */

// Set page title
$pageTitle = 'Complete Maintenance';
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

// Check if maintenance is in progress
if ($maintenance['status'] !== 'in_progress') {
    setFlashMessage('Only in-progress maintenance requests can be completed', 'warning');
    redirect(BASE_URL . '/maintenance/view?id=' . $maintenanceId);
}

// Get item details
$item = InventoryHelper::getItemById($maintenance['item_id']);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    validateCsrfToken();
    
    // Gather form data
    $completionData = [
        'resolution' => isset($_POST['resolution']) ? cleanInput($_POST['resolution']) : '',
        'actual_cost' => isset($_POST['actual_cost']) ? (float)$_POST['actual_cost'] : null,
        'parts_used' => isset($_POST['parts_used']) ? cleanInput($_POST['parts_used']) : null,
        'end_date' => isset($_POST['completion_date']) ? cleanInput($_POST['completion_date']) : date('Y-m-d'),
        'notes' => isset($_POST['notes']) ? cleanInput($_POST['notes']) : null,
        'update_item_condition' => isset($_POST['update_item_condition']) && $_POST['update_item_condition'] === '1',
        'item_condition' => isset($_POST['item_condition']) ? cleanInput($_POST['item_condition']) : 'good',
        'schedule_next' => isset($_POST['schedule_next']) && $_POST['schedule_next'] === '1',
    ];
    
    // Basic validation
    $validationErrors = [];
    
    if (empty($completionData['resolution'])) {
        $validationErrors[] = 'Resolution details are required';
    }
    
    if (empty($completionData['end_date'])) {
        $validationErrors[] = 'Completion date is required';
    }
    
    // If no validation errors, complete maintenance record
    if (empty($validationErrors)) {
        $result = MaintenanceHelper::completeMaintenanceRecord($maintenanceId, $completionData);
        
        if ($result['success']) {
            setFlashMessage($result['message'], 'success');
            redirect(BASE_URL . '/maintenance/view?id=' . $maintenanceId);
        } else {
            setFlashMessage($result['message'], 'danger');
        }
    } else {
        setFlashMessage('Please correct the errors below: ' . implode(', ', $validationErrors), 'danger');
    }
}

// Include header
include 'includes/header.php';
?>

<div class="content-header">
    <h1 class="page-title">Complete Maintenance Request</h1>
    <div class="breadcrumbs">
        <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Home</a>
        <a href="<?php echo BASE_URL; ?>/maintenance" class="breadcrumb-item">Maintenance</a>
        <a href="<?php echo BASE_URL; ?>/maintenance/view?id=<?php echo $maintenanceId; ?>" class="breadcrumb-item">Request Details</a>
        <span class="breadcrumb-item">Complete</span>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Maintenance Completion</div>
        <div>
            <span class="badge badge-blue">MR-<?php echo str_pad($maintenance['id'], 4, '0', STR_PAD_LEFT); ?></span>
        </div>
    </div>
    <div class="panel-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> You are completing maintenance for: <strong><?php echo htmlspecialchars($item['name']); ?></strong>
            <?php if (!empty($item['asset_id'])): ?>
                (Asset ID: <?php echo htmlspecialchars($item['asset_id']); ?>)
            <?php endif; ?>
        </div>
        
        <form action="<?php echo BASE_URL; ?>/maintenance/complete?id=<?php echo $maintenanceId; ?>" method="POST">
            <!-- CSRF Token -->
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
            
            <div class="form-group">
                <label class="form-label" for="completion_date">Completion Date*</label>
                <input type="date" class="form-control" id="completion_date" name="completion_date" value="<?php echo isset($_POST['completion_date']) ? htmlspecialchars($_POST['completion_date']) : date('Y-m-d'); ?>" required>
                <div class="form-hint">Date when maintenance work was completed</div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="resolution">Work Performed / Resolution*</label>
                <textarea class="form-control" id="resolution" name="resolution" rows="4" required><?php echo isset($_POST['resolution']) ? htmlspecialchars($_POST['resolution']) : ''; ?></textarea>
                <div class="form-hint">Describe the work performed and how the issue was resolved</div>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="parts_used">Parts Used</label>
                        <textarea class="form-control" id="parts_used" name="parts_used" rows="3"><?php echo isset($_POST['parts_used']) ? htmlspecialchars($_POST['parts_used']) : ''; ?></textarea>
                        <div class="form-hint">List any parts or materials used for the repair</div>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="actual_cost">Actual Cost</label>
                        <input type="number" class="form-control" id="actual_cost" name="actual_cost" step="0.01" min="0" value="<?php echo isset($_POST['actual_cost']) ? htmlspecialchars($_POST['actual_cost']) : (isset($maintenance['estimated_cost']) ? $maintenance['estimated_cost'] : '0.00'); ?>">
                        <div class="form-hint">Total cost of maintenance (parts and labor)</div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="notes">Additional Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                <div class="form-hint">Any additional notes or follow-up required</div>
            </div>
            
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="update_item_condition" name="update_item_condition" value="1" <?php echo isset($_POST['update_item_condition']) && $_POST['update_item_condition'] === '1' ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="update_item_condition">Update item condition after maintenance</label>
                </div>
                <div class="form-hint">Check this to update the item's condition rating based on the maintenance performed</div>
            </div>
            
            <div id="condition_section" style="display: none;">
                <div class="form-group">
                    <label class="form-label" for="item_condition">Item Condition After Maintenance</label>
                    <select class="form-control" id="item_condition" name="item_condition">
                        <option value="new" <?php echo isset($_POST['item_condition']) && $_POST['item_condition'] === 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="excellent" <?php echo isset($_POST['item_condition']) && $_POST['item_condition'] === 'excellent' ? 'selected' : ''; ?>>Excellent</option>
                        <option value="good" <?php echo (isset($_POST['item_condition']) && $_POST['item_condition'] === 'good') || !isset($_POST['item_condition']) ? 'selected' : ''; ?>>Good</option>
                        <option value="fair" <?php echo isset($_POST['item_condition']) && $_POST['item_condition'] === 'fair' ? 'selected' : ''; ?>>Fair</option>
                        <option value="poor" <?php echo isset($_POST['item_condition']) && $_POST['item_condition'] === 'poor' ? 'selected' : ''; ?>>Poor</option>
                        <option value="damaged" <?php echo isset($_POST['item_condition']) && $_POST['item_condition'] === 'damaged' ? 'selected' : ''; ?>>Damaged</option>
                    </select>
                    <div class="form-hint">The condition of the item after maintenance has been completed</div>
                </div>
            </div>
            
            <?php if (!empty($item['maintenance_interval'])): ?>
                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="schedule_next" name="schedule_next" value="1" <?php echo isset($_POST['schedule_next']) && $_POST['schedule_next'] === '1' ? 'checked' : 'checked'; ?>>
                        <label class="form-check-label" for="schedule_next">Schedule next maintenance in <?php echo (int)$item['maintenance_interval']; ?> days</label>
                    </div>
                    <div class="form-hint">Automatically schedule the next maintenance date based on this item's maintenance interval</div>
                </div>
            <?php endif; ?>
            
            <div class="form-actions">
                <a href="<?php echo BASE_URL; ?>/maintenance/view?id=<?php echo $maintenanceId; ?>" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check btn-icon"></i> Mark as Complete
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide condition section based on checkbox
    const updateConditionCheckbox = document.getElementById('update_item_condition');
    const conditionSection = document.getElementById('condition_section');
    
    updateConditionCheckbox.addEventListener('change', function() {
        conditionSection.style.display = this.checked ? 'block' : 'none';
    });
    
    // Initialize the form based on current values
    updateConditionCheckbox.dispatchEvent(new Event('change'));
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?>