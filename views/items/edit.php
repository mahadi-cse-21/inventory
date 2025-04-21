<?php
/**
 * Edit Inventory Item
 * 
 * This page allows admins and managers to edit existing items in the inventory
 */

// Set page title
$pageTitle = 'Edit Item';

// Check if user has appropriate permissions
if (!hasRole(['admin', 'manager'])) {
    redirect(BASE_URL . '/errors/403');
    exit;
}

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    validateCsrfToken();
    
    // Get form data
    $itemData = [
        'name' => cleanInput($_POST['name']),
        'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
        'location_id' => !empty($_POST['location_id']) ? (int)$_POST['location_id'] : null,
        'supplier_id' => !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null,
        'asset_id' => !empty($_POST['asset_id']) ? cleanInput($_POST['asset_id']) : null,
        'brand' => !empty($_POST['brand']) ? cleanInput($_POST['brand']) : null,
        'model' => !empty($_POST['model']) ? cleanInput($_POST['model']) : null,
        'model_number' => !empty($_POST['model_number']) ? cleanInput($_POST['model_number']) : null,
        'serial_number' => !empty($_POST['serial_number']) ? cleanInput($_POST['serial_number']) : null,
        'barcode' => !empty($_POST['barcode']) ? cleanInput($_POST['barcode']) : null,
        'status' => !empty($_POST['status']) ? cleanInput($_POST['status']) : 'available',
        'condition_rating' => !empty($_POST['condition_rating']) ? cleanInput($_POST['condition_rating']) : 'good',
        'description' => !empty($_POST['description']) ? cleanInput($_POST['description']) : null,
        'specifications' => !empty($_POST['specifications']) ? cleanInput($_POST['specifications']) : null,
        'notes' => !empty($_POST['notes']) ? cleanInput($_POST['notes']) : null,
        'purchase_date' => !empty($_POST['purchase_date']) ? cleanInput($_POST['purchase_date']) : null,
        'purchase_price' => !empty($_POST['purchase_price']) ? (float)$_POST['purchase_price'] : null,
        'warranty_expiry' => !empty($_POST['warranty_expiry']) ? cleanInput($_POST['warranty_expiry']) : null,
        'current_value' => !empty($_POST['current_value']) ? (float)$_POST['current_value'] : null,
        'maintenance_interval' => !empty($_POST['maintenance_interval']) ? (int)$_POST['maintenance_interval'] : null,
        'next_maintenance_date' => !empty($_POST['next_maintenance_date']) ? cleanInput($_POST['next_maintenance_date']) : null,
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];
    
    // Process tags if provided
    if (!empty($_POST['tags'])) {
        $tags = explode(',', $_POST['tags']);
        $itemData['tags'] = array_map('trim', $tags);
    }
    
    // Process image uploads if any
    $itemData['images'] = [];
    
    // Keep existing images if not deleted
    if (!empty($_POST['existing_images'])) {
        $existingImages = json_decode($_POST['existing_images'], true);
        foreach ($existingImages as $imgId => $isPrimary) {
            if ($isPrimary) {
                // Set this as primary image
                $updateImgSql = "UPDATE item_images SET is_primary = 1 WHERE id = ?";
                $updateImgStmt = $conn->prepare($updateImgSql);
                $updateImgStmt->execute([$imgId]);
                
                // Set all other images as non-primary
                $resetImgSql = "UPDATE item_images SET is_primary = 0 WHERE item_id = ? AND id != ?";
                $resetImgStmt = $conn->prepare($resetImgSql);
                $resetImgStmt->execute([$itemId, $imgId]);
            }
        }
    }
    
    // Handle deleted images
    if (!empty($_POST['deleted_images'])) {
        $deletedImages = explode(',', $_POST['deleted_images']);
        foreach ($deletedImages as $imgId) {
            InventoryHelper::deleteItemImage((int)$imgId);
        }
    }
    
    // Handle new image uploads
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['name'] as $key => $name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $itemData['images'][] = [
                    'name' => $_FILES['images']['name'][$key],
                    'tmp_name' => $_FILES['images']['tmp_name'][$key],
                    'type' => $_FILES['images']['type'][$key],
                    'size' => $_FILES['images']['size'][$key],
                    'error' => $_FILES['images']['error'][$key],
                    'is_primary' => isset($_POST['primary_image']) && $_POST['primary_image'] == 'new_' . $key
                ];
            }
        }
    }
    
    // Update the item
    $result = InventoryHelper::updateItem($itemId, $itemData);
    
    if ($result['success']) {
        // Set success message and redirect to view item
        setFlashMessage('Item updated successfully!', 'success');
        redirect(BASE_URL . '/items/view?id=' . $itemId);
        exit;
    } else {
        // Set error message
        setFlashMessage('Failed to update item: ' . $result['message'], 'danger');
    }
}

// Get categories for dropdown
$categories = InventoryHelper::getAllCategories();

// Get locations for dropdown
$locationResult = LocationHelper::getAllLocations(1, 100, ['is_active' => 1]);
$locations = $locationResult['locations'];

// Get suppliers for dropdown
$suppliers = InventoryHelper::getAllSuppliers();

// Format item tags for display
$itemTagsString = '';
if (!empty($item['tags'])) {
    $tagNames = array_map(function($tag) {
        return $tag['name'];
    }, $item['tags']);
    $itemTagsString = implode(', ', $tagNames);
}

// Include header
include 'includes/header.php';
?>

<div class="content-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">Edit Item</h1>
        <a href="<?php echo BASE_URL; ?>/items/view?id=<?php echo $itemId; ?>" class="btn btn-outline">
            <i class="fas fa-arrow-left btn-icon"></i> Back to Item
        </a>
    </div>
    <div class="breadcrumbs">
        <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Home</a>
        <a href="<?php echo BASE_URL; ?>/items" class="breadcrumb-item">Inventory</a>
        <a href="<?php echo BASE_URL; ?>/items/view?id=<?php echo $itemId; ?>" class="breadcrumb-item"><?php echo htmlspecialchars($item['name']); ?></a>
        <span class="breadcrumb-item">Edit</span>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Edit Item: <?php echo htmlspecialchars($item['name']); ?></div>
    </div>
    <div class="panel-body">
        <form action="<?php echo BASE_URL; ?>/items/edit?id=<?php echo $itemId; ?>" method="POST" enctype="multipart/form-data" id="edit-item-form">
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
            <input type="hidden" name="deleted_images" id="deleted-images" value="">
            <input type="hidden" name="existing_images" id="existing-images" value="">
            
            <!-- Tab Navigation -->
            <div class="tabs">
                <div class="tab active" data-tab="basic-info">Basic Info</div>
                <div class="tab" data-tab="details">Details & Specs</div>
                <div class="tab" data-tab="purchase">Purchase & Warranty</div>
                <div class="tab" data-tab="images">Images</div>
            </div>
            
            <!-- Tab Content -->
            <div class="tab-content active" id="basic-info">
                <div class="tab-content-inner">
                    <div class="section-header">
                        <h3 class="section-title">Basic Information</h3>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="name" class="form-label required">Item Name</label>
                                <input type="text" id="name" name="name" class="form-control" required value="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="asset_id" class="form-label">Asset ID</label>
                                <input type="text" id="asset_id" name="asset_id" class="form-control" placeholder="Optional unique identifier" value="<?php echo htmlspecialchars($item['asset_id'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="category_id" class="form-label">Category</label>
                                <select id="category_id" name="category_id" class="form-control">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo ($item['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                        <?php foreach ($category['children'] as $child): ?>
                                            <option value="<?php echo $child['id']; ?>" <?php echo ($item['category_id'] == $child['id']) ? 'selected' : ''; ?>>
                                                &nbsp;&nbsp;└ <?php echo htmlspecialchars($child['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="location_id" class="form-label">Location</label>
                                <select id="location_id" name="location_id" class="form-control">
                                    <option value="">Select Location</option>
                                    <?php foreach ($locations as $location): ?>
                                        <option value="<?php echo $location['id']; ?>" <?php echo ($item['location_id'] == $location['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($location['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="available" <?php echo ($item['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                                    <option value="borrowed" <?php echo ($item['status'] == 'borrowed') ? 'selected' : ''; ?>>Borrowed</option>
                                    <option value="reserved" <?php echo ($item['status'] == 'reserved') ? 'selected' : ''; ?>>Reserved</option>
                                    <option value="maintenance" <?php echo ($item['status'] == 'maintenance') ? 'selected' : ''; ?>>In Maintenance</option>
                                    <option value="unavailable" <?php echo ($item['status'] == 'unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                                    <option value="retired" <?php echo ($item['status'] == 'retired') ? 'selected' : ''; ?>>Retired</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="condition_rating" class="form-label">Condition</label>
                                <select id="condition_rating" name="condition_rating" class="form-control">
                                    <option value="new" <?php echo ($item['condition_rating'] == 'new') ? 'selected' : ''; ?>>New</option>
                                    <option value="excellent" <?php echo ($item['condition_rating'] == 'excellent') ? 'selected' : ''; ?>>Excellent</option>
                                    <option value="good" <?php echo ($item['condition_rating'] == 'good') ? 'selected' : ''; ?>>Good</option>
                                    <option value="fair" <?php echo ($item['condition_rating'] == 'fair') ? 'selected' : ''; ?>>Fair</option>
                                    <option value="poor" <?php echo ($item['condition_rating'] == 'poor') ? 'selected' : ''; ?>>Poor</option>
                                    <option value="damaged" <?php echo ($item['condition_rating'] == 'damaged') ? 'selected' : ''; ?>>Damaged</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="tags" class="form-label">Tags</label>
                        <input type="text" id="tags" name="tags" class="form-control" placeholder="Enter tags separated by commas" value="<?php echo htmlspecialchars($itemTagsString); ?>">
                        <div class="form-text">Tags help with searching and categorizing items</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
            
            <div class="tab-content" id="details">
                <div class="tab-content-inner">
                    <div class="section-header">
                        <h3 class="section-title">Details & Specifications</h3>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="brand" class="form-label">Brand</label>
                                <input type="text" id="brand" name="brand" class="form-control" value="<?php echo htmlspecialchars($item['brand'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="model" class="form-label">Model</label>
                                <input type="text" id="model" name="model" class="form-control" value="<?php echo htmlspecialchars($item['model'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="model_number" class="form-label">Model Number</label>
                                <input type="text" id="model_number" name="model_number" class="form-control" value="<?php echo htmlspecialchars($item['model_number'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="serial_number" class="form-label">Serial Number</label>
                                <input type="text" id="serial_number" name="serial_number" class="form-control" value="<?php echo htmlspecialchars($item['serial_number'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="barcode" class="form-label">Barcode</label>
                                <input type="text" id="barcode" name="barcode" class="form-control" value="<?php echo htmlspecialchars($item['barcode'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="supplier_id" class="form-label">Supplier</label>
                                <select id="supplier_id" name="supplier_id" class="form-control">
                                    <option value="">Select Supplier</option>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?php echo $supplier['id']; ?>" <?php echo ($item['supplier_id'] == $supplier['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($supplier['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="specifications" class="form-label">Technical Specifications</label>
                        <textarea id="specifications" name="specifications" class="form-control" rows="5"><?php echo htmlspecialchars($item['specifications'] ?? ''); ?></textarea>
                        <div class="form-text">Enter technical specifications, dimensions, requirements, etc.</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($item['notes'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
            
            <div class="tab-content" id="purchase">
                <div class="tab-content-inner">
                    <div class="section-header">
                        <h3 class="section-title">Purchase & Warranty Information</h3>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="purchase_date" class="form-label">Purchase Date</label>
                                <input type="date" id="purchase_date" name="purchase_date" class="form-control" value="<?php echo htmlspecialchars($item['purchase_date'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="purchase_price" class="form-label">Purchase Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" id="purchase_price" name="purchase_price" class="form-control" step="0.01" min="0" value="<?php echo htmlspecialchars($item['purchase_price'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="warranty_expiry" class="form-label">Warranty Expiry</label>
                                <input type="date" id="warranty_expiry" name="warranty_expiry" class="form-control" value="<?php echo htmlspecialchars($item['warranty_expiry'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="current_value" class="form-label">Current Value</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" id="current_value" name="current_value" class="form-control" step="0.01" min="0" value="<?php echo htmlspecialchars($item['current_value'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="maintenance_interval" class="form-label">Maintenance Interval (Days)</label>
                                <input type="number" id="maintenance_interval" name="maintenance_interval" class="form-control" min="0" value="<?php echo htmlspecialchars($item['maintenance_interval'] ?? ''); ?>">
                                <div class="form-text">Number of days between regular maintenance checks</div>
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="next_maintenance_date" class="form-label">Next Maintenance Date</label>
                                <input type="date" id="next_maintenance_date" name="next_maintenance_date" class="form-control" value="<?php echo htmlspecialchars($item['next_maintenance_date'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tab-content" id="images">
                <div class="tab-content-inner">
                    <div class="section-header">
                        <h3 class="section-title">Item Images</h3>
                    </div>
                    
                    <!-- Existing Images -->
                    <?php if (!empty($item['images'])): ?>
                        <div class="form-group">
                            <label class="form-label">Current Images</label>
                            <div class="existing-images-grid">
                                <?php foreach ($item['images'] as $index => $image): ?>
                                    <div class="existing-image-item" data-id="<?php echo $image['id']; ?>">
                                        <div class="image-container">
                                            <img src="<?php echo BASE_URL . '/uploads/items/' . $item['id'] . '/' . $image['file_name']; ?>" alt="Item Image">
                                            <div class="image-overlay">
                                                <div class="image-actions">
                                                    <label class="image-primary">
                                                        <input type="radio" name="primary_image" value="existing_<?php echo $image['id']; ?>" <?php echo $image['is_primary'] ? 'checked' : ''; ?>>
                                                        <span>Primary</span>
                                                    </label>
                                                    <button type="button" class="btn btn-sm btn-danger delete-image" data-id="<?php echo $image['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="image-filename"><?php echo htmlspecialchars($image['file_name']); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Upload New Images -->
                    <div class="form-group">
                        <label class="form-label">Upload New Images</label>
                        <div class="image-upload-container">
                            <div id="image-preview-container" class="image-preview-area">
                                <div class="image-upload-placeholder">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p>Drag &amp; drop images here or click to browse</p>
                                    <p class="text-muted">Supported formats: JPG, PNG, GIF. Max size: 5MB</p>
                                </div>
                                
                                <input type="file" id="images" name="images[]" class="form-control-file" multiple accept="image/*">
                            </div>
                            
                            <div id="image-previews" class="image-previews-grid"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <div class="form-action-left">
                    <div class="form-switch">
                        <input type="checkbox" id="is_active" name="is_active" class="form-check-input" <?php echo $item['is_active'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_active">Item is Active</label>
                    </div>
                </div>
                <div class="form-action-right">
                    <a href="<?php echo BASE_URL; ?>/items/view?id=<?php echo $itemId; ?>" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save btn-icon"></i> Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

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
    
    // Handle existing images
    const existingImagesData = {};
    const existingImages = document.querySelectorAll('.existing-image-item');
    const deletedImagesInput = document.getElementById('deleted-images');
    const existingImagesInput = document.getElementById('existing-images');
    const deletedImages = [];
    
    // Initialize existing images data
    existingImages.forEach(img => {
        const imgId = img.getAttribute('data-id');
        const isPrimary = img.querySelector('input[type="radio"]').checked;
        existingImagesData[imgId] = isPrimary;
    });
    
    // Update existing images JSON when primary selection changes
    document.querySelectorAll('input[name="primary_image"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value.startsWith('existing_')) {
                const imgId = this.value.replace('existing_', '');
                
                // Reset all existing images primary status
                Object.keys(existingImagesData).forEach(id => {
                    existingImagesData[id] = false;
                });
                
                // Set this image as primary
                existingImagesData[imgId] = true;
                
                // Update the hidden input
                existingImagesInput.value = JSON.stringify(existingImagesData);
            }
        });
    });
    
    // Delete existing image
    document.querySelectorAll('.delete-image').forEach(btn => {
        btn.addEventListener('click', function() {
            const imgId = this.getAttribute('data-id');
            const imgContainer = this.closest('.existing-image-item');
            
            // Add to deleted images list
            deletedImages.push(imgId);
            deletedImagesInput.value = deletedImages.join(',');
            
            // Remove from existing images data
            delete existingImagesData[imgId];
            existingImagesInput.value = JSON.stringify(existingImagesData);
            
            // Remove from DOM
            imgContainer.remove();
        });
    });
    
    // Initialize existing images input
    existingImagesInput.value = JSON.stringify(existingImagesData);
    
    // Image upload and preview for new images
    const imageInput = document.getElementById('images');
    const imagePreviews = document.getElementById('image-previews');
    const imagePreviewContainer = document.getElementById('image-preview-container');
    
    imageInput.addEventListener('change', function() {
        // Clear existing previews
        imagePreviews.innerHTML = '';
        
        if (this.files.length > 0) {
            const placeholder = imagePreviewContainer.querySelector('.image-upload-placeholder');
            if (placeholder) {
                placeholder.style.display = 'none';
            }
            
            for (let i = 0; i < this.files.length; i++) {
                const file = this.files[i];
                
                // Only process image files
                if (!file.type.match('image.*')) {
                    continue;
                }
                
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const preview = document.createElement('div');
                    preview.classList.add('image-preview-item');
                    
                    const imgContainer = document.createElement('div');
                    imgContainer.classList.add('image-container');
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.title = file.name;
                    
                    const overlay = document.createElement('div');
                    overlay.classList.add('image-overlay');
                    
                    const actions = document.createElement('div');
                    actions.classList.add('image-actions');
                    
                    const primaryLabel = document.createElement('label');
                    primaryLabel.classList.add('image-primary');
                    primaryLabel.innerHTML = `
                        <input type="radio" name="primary_image" value="new_${i}">
                        <span>Primary</span>
                    `;
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.classList.add('btn', 'btn-sm', 'btn-danger', 'remove-image');
                    removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                    removeBtn.addEventListener('click', function() {
                        preview.remove();
                        
                        // Reset input if all previews are removed
                        if (imagePreviews.children.length === 0) {
                            imageInput.value = '';
                            const placeholder = imagePreviewContainer.querySelector('.image-upload-placeholder');
                            if (placeholder) {
                                placeholder.style.display = 'flex';
                            }
                        }
                    });
                    
                    actions.appendChild(primaryLabel);
                    actions.appendChild(removeBtn);
                    
                    overlay.appendChild(actions);
                    
                    imgContainer.appendChild(img);
                    imgContainer.appendChild(overlay);
                    
                    const filename = document.createElement('div');
                    filename.classList.add('image-filename');
                    filename.textContent = file.name;
                    
                    preview.appendChild(imgContainer);
                    preview.appendChild(filename);
                    
                    imagePreviews.appendChild(preview);
                    
                    // Set first new image as primary if no existing images
                    if (i === 0 && existingImages.length === 0) {
                        primaryLabel.querySelector('input').checked = true;
                    }
                };
                
                reader.readAsDataURL(file);
            }
        } else if (imagePreviews.children.length === 0) {
            const placeholder = imagePreviewContainer.querySelector('.image-upload-placeholder');
            if (placeholder) {
                placeholder.style.display = 'flex';
            }
        }
    });
    
    // Auto-generate next maintenance date based on interval
    document.getElementById('maintenance_interval').addEventListener('input', function() {
        const interval = parseInt(this.value);
        const nextMaintenanceDate = document.getElementById('next_maintenance_date');
        
        if (interval > 0) {
            // Calculate next maintenance date from today
            const today = new Date();
            today.setDate(today.getDate() + interval);
            
            // Format date as yyyy-mm-dd
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            
            nextMaintenanceDate.value = `${year}-${month}-${day}`;
        }
    });
    
    // Calculate current value based on purchase price, purchase date, and depreciation
    function calculateCurrentValue() {
        const purchasePrice = parseFloat(document.getElementById('purchase_price').value);
        const purchaseDate = document.getElementById('purchase_date').value;
        
        if (purchasePrice && purchaseDate) {
            // Simple linear depreciation: 20% per year
            const annualDepreciationRate = 0.20;
            
            const purchaseDateObj = new Date(purchaseDate);
            const today = new Date();
            
            // Calculate years difference
            const diffYears = (today - purchaseDateObj) / (1000 * 60 * 60 * 24 * 365);
            
            // Calculate current value (don't let it go below 10% of purchase price)
            let currentValue = purchasePrice * Math.max(0.1, 1 - (annualDepreciationRate * diffYears));
            
            // Round to 2 decimal places
            currentValue = Math.round(currentValue * 100) / 100;
            
            document.getElementById('current_value').value = currentValue;
        }
    }
    
    // Trigger calculation when purchase price or date change
    document.getElementById('purchase_price').addEventListener('input', calculateCurrentValue);
    document.getElementById('purchase_date').addEventListener('change', calculateCurrentValue);
    
    // Form validation before submit
    document.getElementById('edit-item-form').addEventListener('submit', function(e) {
        const itemName = document.getElementById('name').value.trim();
        
        if (itemName === '') {
            e.preventDefault();
            alert('Item name is required');
            document.getElementById('name').focus();
            
            // Switch to basic info tab if not active
            if (!document.getElementById('basic-info').classList.contains('active')) {
                document.querySelector('.tab[data-tab="basic-info"]').click();
            }
            
            return false;
        }
        
        return true;
    });
});
</script>

<style>
/* Tab styling improvements */
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

/* Tab content improvements */
.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

.tab-content-inner {
    padding: 1rem 0;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Section styling */
.section-header {
    margin-bottom: 1.5rem;
}

.section-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--gray-700);
    margin: 0;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--gray-200);
}

/* Form actions */
.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--gray-200);
}

.form-action-left {
    display: flex;
    align-items: center;
}

.form-action-right {
    display: flex;
    gap: 0.75rem;
}

/* Image management improvements */
.existing-images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.existing-image-item, .image-preview-item {
    border: 1px solid var(--gray-200);
    border-radius: 8px;
    overflow: hidden;
    background-color: var(--gray-50);
    transition: all 0.2s ease;
}

.existing-image-item:hover, .image-preview-item:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.image-container {
    position: relative;
    padding-top: 75%; /* 4:3 aspect ratio */
    overflow: hidden;
}

.image-container img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.image-container:hover .image-overlay {
    opacity: 1;
}

.image-actions {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.image-primary {
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    background-color: rgba(0, 0, 0, 0.4);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.image-filename {
    padding: 0.5rem;
    font-size: 0.85rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-align: center;
}

.image-previews-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.image-preview-area {
    border: 2px dashed var(--gray-300);
    border-radius: 8px;
    background-color: var(--gray-50);
    padding: 2rem;
    transition: all 0.2s ease;
}

.image-preview-area:hover {
    border-color: var(--primary);
    background-color: var(--gray-100);
}

.image-upload-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    cursor: pointer;
}

.image-upload-placeholder i {
    font-size: 2.5rem;
    color: var(--gray-400);
    margin-bottom: 1rem;
}

.image-upload-placeholder p {
    margin: 0.25rem 0;
    color: var(--gray-600);
}

.image-upload-placeholder .text-muted {
    font-size: 0.85rem;
    color: var(--gray-500);
}

input[type="file"] {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    border: 0;
}

/* Form improvements */
.form-row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -0.75rem;
    margin-bottom: 1rem;
}

.form-col {
    flex: 1;
    min-width: 250px;
    padding: 0 0.75rem;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.form-label.required::after {
    content: '*';
    color: var(--danger);
    margin-left: 0.25rem;
}

.form-control {
    display: block;
    width: 100%;
    padding: 0.75rem;
    font-size: 1rem;
    border: 1px solid var(--gray-300);
    border-radius: 6px;
    background-color: white;
    transition: border-color 0.2s ease;
}

.form-control:focus {
    border-color: var(--primary);
    outline: none;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
}

.form-text {
    margin-top: 0.25rem;
    font-size: 0.85rem;
    color: var(--gray-500);
}

.input-group {
    display: flex;
    align-items: stretch;
}

.input-group-text {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    font-size: 1rem;
    color: var(--gray-700);
    background-color: var(--gray-100);
    border: 1px solid var(--gray-300);
    border-right: none;
    border-top-left-radius: 6px;
    border-bottom-left-radius: 6px;
}

.input-group .form-control {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.form-switch {
    display: flex;
    align-items: center;
}

.form-check-input {
    margin-right: 0.5rem;
}

.form-check-label {
    font-weight: 500;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    font-weight: 500;
    border: 1px solid transparent;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 4px;
}

.btn-icon {
    margin-right: 0.5rem;
}

.btn-primary {
    background-color: var(--primary);
    color: white;
}

.btn-primary:hover {
    background-color: var(--primary-dark);
}

.btn-outline {
    background-color: transparent;
    border-color: var(--gray-300);
    color: var(--gray-700);
}

.btn-outline:hover {
    background-color: var(--gray-100);
    border-color: var(--gray-400);
}

.btn-danger {
    background-color: var(--danger);
    color: white;
}

.btn-danger:hover {
    background-color: var(--danger-dark);
}

/* Breadcrumbs */
.breadcrumbs {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    font-size: 0.875rem;
    color: var(--gray-500);
    margin-bottom: 1.5rem;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
}

.breadcrumb-item:not(:last-child)::after {
    content: '/';
    margin: 0 0.5rem;
    color: var(--gray-400);
}

.breadcrumb-item a {
    color: var(--gray-600);
    text-decoration: none;
}

.breadcrumb-item a:hover {
    color: var(--primary);
    text-decoration: underline;
}
</style>

<?php
// Include footer
include 'includes/footer.php';
?>