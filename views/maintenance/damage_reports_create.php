<?php
/**
 * Create Damage Report
 * 
 * This file allows users to create a new damage report
 */

// Set page title
$pageTitle = 'Report Damage';
$bodyClass = 'damage-report-page';

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
    $reportData = [
        'item_id' => isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0,
        'severity' => isset($_POST['severity']) ? cleanInput($_POST['severity']) : '',
        'description' => isset($_POST['description']) ? cleanInput($_POST['description']) : '',
        'damage_date' => isset($_POST['damage_date']) ? cleanInput($_POST['damage_date']) : date('Y-m-d'),
        'reported_by' => $_SESSION['user_id'],
        'notes' => isset($_POST['notes']) ? cleanInput($_POST['notes']) : null,
        'create_maintenance' => isset($_POST['create_maintenance']) && $_POST['create_maintenance'] === '1'
    ];
    
    // Handle file uploads
    $reportData['images'] = [];
    
    if (isset($_FILES['damage_photos']) && is_array($_FILES['damage_photos']['name'])) {
        foreach ($_FILES['damage_photos']['name'] as $key => $name) {
            if (!empty($name)) {
                $reportData['images'][] = [
                    'name' => $_FILES['damage_photos']['name'][$key],
                    'type' => $_FILES['damage_photos']['type'][$key],
                    'tmp_name' => $_FILES['damage_photos']['tmp_name'][$key],
                    'error' => $_FILES['damage_photos']['error'][$key],
                    'size' => $_FILES['damage_photos']['size'][$key]
                ];
            }
        }
    }
    
    // Basic validation
    $validationErrors = [];
    
    if (empty($reportData['item_id'])) {
        $validationErrors[] = 'Item is required';
    }
    
    if (empty($reportData['severity'])) {
        $validationErrors[] = 'Severity is required';
    }
    
    if (empty($reportData['description'])) {
        $validationErrors[] = 'Description is required';
    }
    
    if (empty($reportData['damage_date'])) {
        $validationErrors[] = 'Damage date is required';
    }
    
    // If no validation errors, create damage report
    if (empty($validationErrors)) {
        $result = MaintenanceHelper::createDamageReport($reportData);
        
        if ($result['success']) {
            setFlashMessage($result['message'], 'success');
            redirect(BASE_URL . '/maintenance/damage-reports');
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

// Include header
include 'includes/header.php';
?>

<div class="content-header">
    <h1 class="page-title">Report Damage</h1>
    <div class="breadcrumbs">
        <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Home</a>
        <a href="<?php echo BASE_URL; ?>/maintenance" class="breadcrumb-item">Maintenance</a>
        <a href="<?php echo BASE_URL; ?>/maintenance/damage-reports" class="breadcrumb-item">Damage Reports</a>
        <span class="breadcrumb-item">Create</span>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Damage Report Details</div>
    </div>
    <div class="panel-body">
        <form action="<?php echo BASE_URL; ?>/maintenance/damage-reports/create" method="POST" enctype="multipart/form-data">
            <!-- CSRF Token -->
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
            
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="item_id">Item*</label>
                        <select class="form-control" id="item_id" name="item_id" required>
                            <option value="">Select an Item</option>
                            <?php foreach ($items as $item): ?>
                                <option value="<?php echo $item['id']; ?>" <?php echo isset($_POST['item_id']) && $_POST['item_id'] == $item['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($item['name']); ?> 
                                    <?php if (!empty($item['asset_id'])): ?>(<?php echo htmlspecialchars($item['asset_id']); ?>)<?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-hint">Select the item that has been damaged</div>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="severity">Severity*</label>
                        <select class="form-control" id="severity" name="severity" required>
                            <option value="">Select Severity</option>
                            <option value="critical" <?php echo isset($_POST['severity']) && $_POST['severity'] === 'critical' ? 'selected' : ''; ?>>Critical - Unusable/Safety Risk</option>
                            <option value="high" <?php echo isset($_POST['severity']) && $_POST['severity'] === 'high' ? 'selected' : ''; ?>>High - Significantly Impaired</option>
                            <option value="medium" <?php echo isset($_POST['severity']) && $_POST['severity'] === 'medium' ? 'selected' : ''; ?>>Medium - Partially Impaired</option>
                            <option value="low" <?php echo isset($_POST['severity']) && $_POST['severity'] === 'low' ? 'selected' : ''; ?>>Low - Minor Issue</option>
                        </select>
                        <div class="form-hint">How severely this damage affects the item's functionality or safety</div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="description">Damage Description*</label>
                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                <div class="form-hint">Describe the damage in detail, including what happened and how it affects the item's functionality</div>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="damage_date">Date Damaged*</label>
                        <input type="date" class="form-control" id="damage_date" name="damage_date" value="<?php echo isset($_POST['damage_date']) ? htmlspecialchars($_POST['damage_date']) : date('Y-m-d'); ?>" required>
                        <div class="form-hint">When the damage occurred</div>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="damage_photos">Damage Photos</label>
                        <input type="file" class="form-control" id="damage_photos" name="damage_photos[]" multiple accept="image/*">
                        <div class="form-hint">Upload photos showing the damage (optional)</div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="notes">Additional Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                <div class="form-hint">Provide any other relevant information about the damage or the circumstances</div>
            </div>
            
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="create_maintenance" name="create_maintenance" value="1" <?php echo isset($_POST['create_maintenance']) && $_POST['create_maintenance'] === '1' ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="create_maintenance">Create maintenance request automatically</label>
                </div>
                <div class="form-hint">Automatically generate a maintenance request to repair the damage</div>
            </div>
            
            <div class="form-actions">
                <a href="<?php echo BASE_URL; ?>/maintenance/damage-reports" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save btn-icon"></i> Submit Report
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview uploaded images
    const fileInput = document.getElementById('damage_photos');
    
    fileInput.addEventListener('change', function() {
        // Check if there's a preview container, create one if not
        let previewContainer = document.getElementById('image-preview-container');
        
        if (!previewContainer) {
            previewContainer = document.createElement('div');
            previewContainer.id = 'image-preview-container';
            previewContainer.className = 'image-preview-container';
            previewContainer.style.display = 'flex';
            previewContainer.style.flexWrap = 'wrap';
            previewContainer.style.gap = '10px';
            previewContainer.style.marginTop = '10px';
            
            // Insert after the file input
            fileInput.parentNode.appendChild(previewContainer);
        } else {
            // Clear existing previews
            previewContainer.innerHTML = '';
        }
        
        // Create previews for each selected file
        for (let i = 0; i < this.files.length; i++) {
            const file = this.files[i];
            
            // Skip non-image files
            if (!file.type.startsWith('image/')) continue;
            
            const preview = document.createElement('div');
            preview.style.width = '100px';
            preview.style.height = '100px';
            preview.style.position = 'relative';
            preview.style.overflow = 'hidden';
            preview.style.borderRadius = '4px';
            preview.style.border = '1px solid #ddd';
            
            const img = document.createElement('img');
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            
            preview.appendChild(img);
            previewContainer.appendChild(preview);
            
            // Read the image file
            const reader = new FileReader();
            reader.onload = (function(aImg) {
                return function(e) {
                    aImg.src = e.target.result;
                };
            })(img);
            
            reader.readAsDataURL(file);
        }
    });
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?>