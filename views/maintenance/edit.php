<?php
/**
 * Edit Maintenance Request View
 * 
 * This file allows users to edit an existing maintenance request
 */

// Set page title
$pageTitle = 'Edit Maintenance Request';
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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    validateCsrfToken();
    
    // Gather form data
    $maintenanceData = [
        'maintenance_type' => isset($_POST['maintenance_type']) ? cleanInput($_POST['maintenance_type']) : $maintenance['maintenance_type'],
        'description' => isset($_POST['description']) ? cleanInput($_POST['description']) : $maintenance['description'],
        'status' => isset($_POST['status']) ? cleanInput($_POST['status']) : $maintenance['status'],
        'assigned_to' => isset($_POST['assigned_to']) && !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null,
        'start_date' => isset($_POST['start_date']) ? cleanInput($_POST['start_date']) : $maintenance['start_date'],
        'end_date' => isset($_POST['end_date']) ? cleanInput($_POST['end_date']) : $maintenance['end_date'],
        'estimated_cost' => isset($_POST['estimated_cost']) ? (float)$_POST['estimated_cost'] : $maintenance['estimated_cost'],
        'actual_cost' => isset($_POST['actual_cost']) ? (float)$_POST['actual_cost'] : $maintenance['actual_cost'],
        'parts_used' => isset($_POST['parts_used']) ? cleanInput($_POST['parts_used']) : $maintenance['parts_used'],
        'resolution' => isset($_POST['resolution']) ? cleanInput($_POST['resolution']) : $maintenance['resolution'],
        'notes' => isset($_POST['notes']) ? cleanInput($_POST['notes']) : $maintenance['notes']
    ];
    
    // Basic validation
    $validationErrors = [];
    
    if (empty($maintenanceData['maintenance_type'])) {
        $validationErrors[] = 'Maintenance type is required';
    }
    
    if (empty($maintenanceData['description'])) {
        $validationErrors[] = 'Description is required';
    }
    
    if (empty($maintenanceData['status'])) {
        $validationErrors[] = 'Status is required';
    }
    
    // Status-specific validation
    if ($maintenanceData['status'] === 'scheduled' && empty($maintenanceData['assigned_to'])) {
        $validationErrors[] = 'Assigned technician is required for scheduled maintenance';
    }
    
    if ($maintenanceData['status'] === 'scheduled' && empty($maintenanceData['start_date'])) {
        $validationErrors[] = 'Start date is required for scheduled maintenance';
    }
    
    if ($maintenanceData['status'] === 'completed') {
        if (empty($maintenanceData['resolution'])) {
            $validationErrors[] = 'Resolution details are required for completed maintenance';
        }
        
        if (empty($maintenanceData['end_date'])) {
            $validationErrors[] = 'End date is required for completed maintenance';
        }
    }
    
    // If no validation errors, update maintenance record
    if (empty($validationErrors)) {
        $result = MaintenanceHelper::updateMaintenanceRecord($maintenanceId, $maintenanceData);
        
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

// Get users for technician dropdown
$usersResult = UserHelper::getAllUsers(1, 100);
$users = $usersResult['users'];

// Include header
include 'includes/header.php';
?>

<div class="content-header">
    <h1 class="page-title">Edit Maintenance Request</h1>
    <div class="breadcrumbs">
        <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Home</a>
        <a href="<?php echo BASE_URL; ?>/maintenance" class="breadcrumb-item">Maintenance</a>
        <a href="<?php echo BASE_URL; ?>/maintenance/view?id=<?php echo $maintenanceId; ?>" class="breadcrumb-item">Request Details</a>
        <span class="breadcrumb-item">Edit</span>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Maintenance Request Details</div>
        <div>
            <span class="badge badge-blue">MR-<?php echo str_pad($maintenance['id'], 4, '0', STR_PAD_LEFT); ?></span>
            <span class="badge badge-purple"><?php echo htmlspecialchars($maintenance['item_name']); ?></span>
        </div>
    </div>
    <div class="panel-body">
        <form action="<?php echo BASE_URL; ?>/maintenance/edit?id=<?php echo $maintenanceId; ?>" method="POST">
            <!-- CSRF Token -->
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
            
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="end_date">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $maintenance['end_date'] ? htmlspecialchars($maintenance['end_date']) : ''; ?>">
                    </div>
                </div>
            </div>
            
            <div id="completion-section" style="display: none;">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label" for="parts_used">Parts Used</label>
                            <textarea class="form-control" id="parts_used" name="parts_used" rows="3"><?php echo htmlspecialchars($maintenance['parts_used'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label" for="actual_cost">Actual Cost</label>
                            <input type="number" class="form-control" id="actual_cost" name="actual_cost" step="0.01" min="0" value="<?php echo htmlspecialchars($maintenance['actual_cost'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="resolution">Resolution</label>
                    <textarea class="form-control" id="resolution" name="resolution" rows="4"><?php echo htmlspecialchars($maintenance['resolution'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="notes">Additional Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($maintenance['notes'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-actions">
                <a href="<?php echo BASE_URL; ?>/maintenance/view?id=<?php echo $maintenanceId; ?>" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save btn-icon"></i> Update Maintenance Request
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide sections based on status
    const statusSelect = document.getElementById('status');
    const assignedToSelect = document.getElementById('assigned_to');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const completionSection = document.getElementById('completion-section');
    const resolutionInput = document.getElementById('resolution');
    
    statusSelect.addEventListener('change', function() {
        const status = this.value;
        
        // Show/hide completion section
        if (status === 'completed') {
            completionSection.style.display = 'block';
            resolutionInput.setAttribute('required', 'required');
            endDateInput.setAttribute('required', 'required');
        } else {
            completionSection.style.display = 'none';
            resolutionInput.removeAttribute('required');
            endDateInput.removeAttribute('required');
        }
        
        // Handle scheduled status
        if (status === 'scheduled') {
            assignedToSelect.setAttribute('required', 'required');
            startDateInput.setAttribute('required', 'required');
        } else {
            assignedToSelect.removeAttribute('required');
            startDateInput.removeAttribute('required');
        }
        
        // Handle in_progress status
        if (status === 'in_progress') {
            assignedToSelect.setAttribute('required', 'required');
            startDateInput.setAttribute('required', 'required');
        }
    });
    
    // Initialize form based on current status
    statusSelect.dispatchEvent(new Event('change'));
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?>