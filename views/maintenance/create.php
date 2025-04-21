<?php
/**
 * Create Maintenance Request View
 * 
 * This file allows users to create a new maintenance request
 */

// Set page title
$pageTitle = 'Create Maintenance Request';
$bodyClass = 'maintenance-page';

// Check permissions
if (!hasRole(['admin', 'manager'])) {
    include 'views/errors/403.php';
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    validateCsrfToken();
    
    // Gather form data
    $maintenanceData = [
        'item_id' => isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0,
        'maintenance_type' => isset($_POST['maintenance_type']) ? cleanInput($_POST['maintenance_type']) : '',
        'description' => isset($_POST['description']) ? cleanInput($_POST['description']) : '',
        'requested_by' => $_SESSION['user_id'],
        'assigned_to' => isset($_POST['assigned_to']) && !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null,
        'start_date' => isset($_POST['start_date']) ? cleanInput($_POST['start_date']) : date('Y-m-d'),
        'estimated_cost' => isset($_POST['estimated_cost']) ? (float)$_POST['estimated_cost'] : null,
        'notes' => isset($_POST['notes']) ? cleanInput($_POST['notes']) : null,
        'update_item_status' => isset($_POST['update_item_status']) && $_POST['update_item_status'] === '1'
    ];
    
    // Set initial status based on assignment
    $maintenanceData['status'] = $maintenanceData['assigned_to'] ? 'scheduled' : 'requested';
    
    // Basic validation
    $validationErrors = [];
    
    if (empty($maintenanceData['item_id'])) {
        $validationErrors[] = 'Item is required';
    }
    
    if (empty($maintenanceData['maintenance_type'])) {
        $validationErrors[] = 'Maintenance type is required';
    }
    
    if (empty($maintenanceData['description'])) {
        $validationErrors[] = 'Description is required';
    }
    
    // If no validation errors, create maintenance record
    if (empty($validationErrors)) {
        $result = MaintenanceHelper::createMaintenanceRecord($maintenanceData);
        
        if ($result['success']) {
            setFlashMessage($result['message'], 'success');
            redirect(BASE_URL . '/maintenance');
        } else {
            setFlashMessage($result['message'], 'danger');
        }
    } else {
        setFlashMessage('Please correct the errors below: ' . implode(', ', $validationErrors), 'danger');
    }
}

// Get available items for dropdown
$itemsResult = InventoryHelper::getAllItems(1, 500, ['is_active' => 1]);
$items = $itemsResult['items'];

// Get users for technician dropdown
$usersResult = UserHelper::getAllUsers(1, 100);
$users = $usersResult['users'];

// Include header
include 'includes/header.php';
?>



<!-- Enhanced Content Header -->
<div class="content-header">
    <h1 class="page-title">Create Maintenance Request</h1>
    <div class="breadcrumbs">
        <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Home</a>
        <a href="<?php echo BASE_URL; ?>/maintenance" class="breadcrumb-item">Maintenance</a>
        <span class="breadcrumb-item">Create Request</span>
    </div>
</div>

<!-- Enhanced Panel -->
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Maintenance Request Details</div>
    </div>
    <div class="panel-body">
        <!-- Required Fields Note -->
        <div class="required-text">Required fields</div>
        
        <!-- Interactive Maintenance Type Icons -->
        <div class="maintenance-type-icons">
            <div class="maintenance-type-icon" data-value="repair">
                <i class="fas fa-wrench icon-repair"></i>
                <div class="maintenance-type-name">Repair</div>
            </div>
            <div class="maintenance-type-icon" data-value="inspection">
                <i class="fas fa-search icon-inspection"></i>
                <div class="maintenance-type-name">Inspection</div>
            </div>
            <div class="maintenance-type-icon" data-value="cleaning">
                <i class="fas fa-broom icon-cleaning"></i>
                <div class="maintenance-type-name">Cleaning</div>
            </div>
            <div class="maintenance-type-icon" data-value="calibration">
                <i class="fas fa-sliders-h icon-calibration"></i>
                <div class="maintenance-type-name">Calibration</div>
            </div>
            <div class="maintenance-type-icon" data-value="upgrade">
                <i class="fas fa-arrow-up icon-upgrade"></i>
                <div class="maintenance-type-name">Upgrade</div>
            </div>
            <div class="maintenance-type-icon" data-value="other">
                <i class="fas fa-tools icon-other"></i>
                <div class="maintenance-type-name">Other</div>
            </div>
        </div>

        <form action="<?php echo BASE_URL; ?>/maintenance/create" method="POST" id="maintenance-form">
            <!-- CSRF Token -->
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
            
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="item_id">Item</label>
                        <select class="form-control" id="item_id" name="item_id" required>
                            <option value="">Select an Item</option>
                            <?php foreach ($items as $item): ?>
                                <option value="<?php echo $item['id']; ?>" <?php echo isset($_POST['item_id']) && $_POST['item_id'] == $item['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($item['name']); ?> 
                                    <?php if (!empty($item['asset_id'])): ?>(<?php echo htmlspecialchars($item['asset_id']); ?>)<?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-hint">Select the item that needs maintenance</div>
                        <div class="invalid-feedback">Please select an item</div>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="maintenance_type">Maintenance Type</label>
                        <select class="form-control" id="maintenance_type" name="maintenance_type" required>
                            <option value="">Select Type</option>
                            <option value="repair" <?php echo isset($_POST['maintenance_type']) && $_POST['maintenance_type'] === 'repair' ? 'selected' : ''; ?>>Repair</option>
                            <option value="inspection" <?php echo isset($_POST['maintenance_type']) && $_POST['maintenance_type'] === 'inspection' ? 'selected' : ''; ?>>Inspection</option>
                            <option value="cleaning" <?php echo isset($_POST['maintenance_type']) && $_POST['maintenance_type'] === 'cleaning' ? 'selected' : ''; ?>>Cleaning</option>
                            <option value="calibration" <?php echo isset($_POST['maintenance_type']) && $_POST['maintenance_type'] === 'calibration' ? 'selected' : ''; ?>>Calibration</option>
                            <option value="upgrade" <?php echo isset($_POST['maintenance_type']) && $_POST['maintenance_type'] === 'upgrade' ? 'selected' : ''; ?>>Upgrade</option>
                            <option value="other" <?php echo isset($_POST['maintenance_type']) && $_POST['maintenance_type'] === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                        <div class="form-hint">Type of maintenance required</div>
                        <div class="invalid-feedback">Please select a maintenance type</div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                <div class="form-hint">Describe the issue or maintenance needed in detail</div>
                <div class="invalid-feedback">Please provide a description</div>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="assigned_to">Assign To (Optional)</label>
                        <select class="form-control" id="assigned_to" name="assigned_to">
                            <option value="">Unassigned</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>" <?php echo isset($_POST['assigned_to']) && $_POST['assigned_to'] == $user['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['full_name']); ?> 
                                    <?php if (!empty($user['job_title'])): ?>(<?php echo htmlspecialchars($user['job_title']); ?>)<?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-hint">If known, assign to a specific technician</div>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="start_date">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : date('Y-m-d'); ?>">
                        <div class="form-hint">When maintenance should begin</div>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="estimated_cost">Estimated Cost</label>
                        <input type="number" class="form-control" id="estimated_cost" name="estimated_cost" step="0.01" min="0" value="<?php echo isset($_POST['estimated_cost']) ? htmlspecialchars($_POST['estimated_cost']) : ''; ?>">
                        <div class="form-hint">Estimated cost if known</div>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="update_item_status">Update Item Status</label>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="update_item_status" name="update_item_status" value="1" <?php echo isset($_POST['update_item_status']) && $_POST['update_item_status'] === '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="update_item_status">Mark item as "In Maintenance" status</label>
                        </div>
                        <div class="form-hint">Check this if the item should be marked as unavailable during maintenance</div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="notes">Additional Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                <div class="form-hint">Any other relevant information</div>
            </div>
            
            <div class="form-actions">
                <a href="<?php echo BASE_URL; ?>/maintenance" class="btn btn-outline">
                    <i class="fas fa-times btn-icon"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save btn-icon"></i> Create Maintenance Request
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide assignment fields based on assignment selection
    const assignedToSelect = document.getElementById('assigned_to');
    const startDateInput = document.getElementById('start_date');
    const maintenanceTypeSelect = document.getElementById('maintenance_type');
    const maintenanceForm = document.getElementById('maintenance-form');
    const typeIcons = document.querySelectorAll('.maintenance-type-icon');
    
    // Handle maintenance type selection changes
    maintenanceTypeSelect.addEventListener('change', function() {
        // Remove all previous classes
        this.classList.remove('repair-selected', 'inspection-selected', 'cleaning-selected', 'calibration-selected', 'upgrade-selected');
        
        // Add class based on selected value
        if (this.value) {
            this.classList.add(this.value + '-selected');
        }
        
        // Update active icon
        typeIcons.forEach(icon => {
            icon.classList.remove('active');
            if (icon.getAttribute('data-value') === this.value) {
                icon.classList.add('active');
            }
        });
    });
    
    // Handle icon clicks
    typeIcons.forEach(icon => {
        icon.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            maintenanceTypeSelect.value = value;
            
            // Trigger change event to update classes
            maintenanceTypeSelect.dispatchEvent(new Event('change'));
        });
    });
    
    assignedToSelect.addEventListener('change', function() {
        // If someone is assigned, update date fields visibility
        if (this.value) {
            // If someone is assigned, make sure date is required
            startDateInput.setAttribute('required', 'required');
        } else {
            // If unassigned, date can be optional
            startDateInput.removeAttribute('required');
        }
    });
    
    // Form validation
    maintenanceForm.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        if (!document.getElementById('item_id').value) {
            document.getElementById('item_id').classList.add('is-invalid');
            isValid = false;
        } else {
            document.getElementById('item_id').classList.remove('is-invalid');
            document.getElementById('item_id').classList.add('is-valid');
        }
        
        if (!document.getElementById('maintenance_type').value) {
            document.getElementById('maintenance_type').classList.add('is-invalid');
            isValid = false;
        } else {
            document.getElementById('maintenance_type').classList.remove('is-invalid');
            document.getElementById('maintenance_type').classList.add('is-valid');
        }
        
        if (!document.getElementById('description').value.trim()) {
            document.getElementById('description').classList.add('is-invalid');
            isValid = false;
        } else {
            document.getElementById('description').classList.remove('is-invalid');
            document.getElementById('description').classList.add('is-valid');
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Initialize the form based on current values
    assignedToSelect.dispatchEvent(new Event('change'));
    maintenanceTypeSelect.dispatchEvent(new Event('change'));
    
    // Add animation to submit button
    const submitBtn = document.querySelector('.form-actions .btn-primary');
    submitBtn.addEventListener('mouseenter', function() {
        const icon = this.querySelector('i');
        icon.classList.add('fa-beat');
        
        setTimeout(() => {
            icon.classList.remove('fa-beat');
        }, 1000);
    });
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?>