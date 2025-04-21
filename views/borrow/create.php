<?php
/**
 * Create Borrow Request View
 * 
 * This file displays the form for creating a new borrow request for item(s)
 */

// Set page title
$pageTitle = 'New Borrow Request';

// Check if user is logged in
if (!AuthHelper::isAuthenticated()) {
    setFlashMessage('You must be logged in to create a borrow request.', 'danger');
    redirect(BASE_URL . '/auth/login');
}

// Get current user
$currentUser = AuthHelper::getCurrentUser();

// Get department
$departmentId = $currentUser['department_id'] ?? null;
$departmentName = 'Not Set';

if ($departmentId) {
    $conn = getDbConnection();
    $sql = "SELECT name FROM departments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$departmentId]);
    $departmentName = $stmt->fetchColumn() ?: 'Not Set';
}

// Check if specific item ID was provided
$itemId = isset($_GET['item']) ? (int)$_GET['item'] : 0;
$item = null;

if ($itemId) {
    $item = InventoryHelper::getItemById($itemId);
    
    // Check if item exists and is available
    if (!$item) {
        setFlashMessage('Item not found', 'danger');
        redirect(BASE_URL . '/items/browse');
    }
    
    if ($item['status'] !== 'available') {
        setFlashMessage('This item is not available for borrowing', 'warning');
        redirect(BASE_URL . '/items/browse');
    }
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    validateCsrfToken();
    
    // Get form data
    $borrowDate = isset($_POST['borrow_date']) ? cleanInput($_POST['borrow_date']) : '';
    $returnDate = isset($_POST['return_date']) ? cleanInput($_POST['return_date']) : '';
    $purpose = isset($_POST['purpose']) ? cleanInput($_POST['purpose']) : '';
    $projectName = isset($_POST['project_name']) ? cleanInput($_POST['project_name']) : null;
    $notes = isset($_POST['notes']) ? cleanInput($_POST['notes']) : null;
    $itemIds = isset($_POST['item_ids']) ? $_POST['item_ids'] : [];
    
    // Validate fields
    $errors = [];
    
    if (empty($borrowDate)) {
        $errors[] = 'Borrow date is required';
    }
    
    if (empty($returnDate)) {
        $errors[] = 'Return date is required';
    }
    
    if (empty($purpose)) {
        $errors[] = 'Purpose is required';
    }
    
    if ($purpose === 'Project Work' || $purpose === 'Event') {
        if (empty($projectName)) {
            $errors[] = 'Project/Event name is required';
        }
    }
    
    if (empty($itemIds)) {
        $errors[] = 'At least one item must be selected';
    }
    
    // Check if borrow date is in the past
    if (!empty($borrowDate) && strtotime($borrowDate) < strtotime(date('Y-m-d'))) {
        $errors[] = 'Borrow date cannot be in the past';
    }
    
    // Check if return date is before borrow date
    if (!empty($borrowDate) && !empty($returnDate) && strtotime($returnDate) < strtotime($borrowDate)) {
        $errors[] = 'Return date cannot be before borrow date';
    }
    
    // If no errors, create borrow request
    if (empty($errors)) {
        // Prepare items data
        $items = [];
        foreach ($itemIds as $id) {
            $items[] = [
                'item_id' => (int)$id,
                'quantity' => 1,
                'condition_before' => 'good'
            ];
        }
        
        // Prepare request data
        $requestData = [
            'user_id' => $currentUser['id'],
            'department_id' => $departmentId,
            'purpose' => $purpose,
            'project_name' => $projectName,
            'borrow_date' => $borrowDate,
            'return_date' => $returnDate,
            'notes' => $notes
        ];
        
        // Create borrow request
        $result = BorrowHelper::createBorrowRequest($requestData, $items);
        
        if ($result['success']) {
            setFlashMessage('Borrow request created successfully. Request ID: ' . $result['request_id'], 'success');
            redirect(BASE_URL . '/borrow/history');
        } else {
            setFlashMessage($result['message'], 'danger');
        }
    } else {
        // Display errors
        foreach ($errors as $error) {
            setFlashMessage($error, 'danger');
        }
    }
}

// Load user's cart items if not requesting a specific item
$cartItems = [];
if (!$itemId) {
    $cartItems = BorrowHelper::getUserCartItems($currentUser['id']);
}

// Include header
include 'includes/header.php';
?>

<div class="content-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">New Borrow Request</h1>
            <nav class="breadcrumbs">
                <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Home</a>
                <span class="breadcrumb-item">New Borrow Request</span>
            </nav>
        </div>
        <div>
            <a href="<?php echo BASE_URL; ?>/items/browse" class="btn btn-outline">
                <i class="fas fa-search btn-icon"></i> Browse Items
            </a>
        </div>
    </div>
</div>

<!-- Progress Stepper -->
<div style="margin-bottom: 2rem;">
    <div class="stepper">
        <div class="stepper-progress" style="width: 50%;"></div>
        <div class="stepper-step active">
            <div class="stepper-dot">1</div>
            <div class="stepper-label">Select Items</div>
        </div>
        <div class="stepper-step">
            <div class="stepper-dot">2</div>
            <div class="stepper-label">Request Details</div>
        </div>
        <div class="stepper-step">
            <div class="stepper-dot">3</div>
            <div class="stepper-label">Confirmation</div>
        </div>
    </div>
</div>

<form action="<?php echo BASE_URL; ?>/borrow/create" method="POST" id="borrow-request-form">
    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
    
    <div class="borrow-step" id="items-step">
        <!-- Selected Items Section -->
        <div style="display: flex; flex-wrap: wrap; gap: 2rem;">
            <!-- Selected Items -->
            <div style="flex: 1; min-width: 300px;">
                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title">Selected Items</div>
                        <button type="button" class="btn btn-sm btn-primary" id="add-items-btn">
                            <i class="fas fa-plus btn-icon"></i> Add Items
                        </button>
                    </div>
                    <div class="panel-body">
                        <div id="selected-items-container">
                            <?php if ($item): ?>
                                <!-- Display the single specific item -->
                                <div class="selected-item" data-item-id="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="item_ids[]" value="<?php echo $item['id']; ?>">
                                    <div class="selected-item-info">
                                        <div class="selected-item-image">
                                            <?php if (!empty($item['images']) && count($item['images']) > 0): ?>
                                                <img src="<?php echo BASE_URL . '/uploads/items/' . $item['id'] . '/' . $item['images'][0]['file_name']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                            <?php else: ?>
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
                                                <i class="fas fa-<?php echo $iconClass; ?>"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="selected-item-details">
                                            <div class="selected-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                            <div class="selected-item-meta">
                                                <span class="badge badge-blue"><?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?></span>
                                                <span class="badge badge-green">Available</span>
                                            </div>
                                            <div class="selected-item-id">Asset #<?php echo htmlspecialchars($item['asset_id'] ?? 'N/A'); ?></div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline remove-item-btn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            <?php elseif (!empty($cartItems)): ?>
                                <!-- Display items from cart -->
                                <?php foreach ($cartItems as $cartItem): ?>
                                    <div class="selected-item" data-item-id="<?php echo $cartItem['item_id']; ?>">
                                        <input type="hidden" name="item_ids[]" value="<?php echo $cartItem['item_id']; ?>">
                                        <div class="selected-item-info">
                                            <div class="selected-item-image">
                                                <?php
                                                // Determine icon based on category
                                                $iconClass = 'box';
                                                
                                                if (stripos($cartItem['category_name'], 'computer') !== false || stripos($cartItem['item_name'], 'laptop') !== false) {
                                                    $iconClass = 'laptop';
                                                } elseif (stripos($cartItem['category_name'], 'audio') !== false) {
                                                    $iconClass = 'headphones';
                                                } elseif (stripos($cartItem['category_name'], 'video') !== false || stripos($cartItem['item_name'], 'projector') !== false) {
                                                    $iconClass = 'video';
                                                } elseif (stripos($cartItem['category_name'], 'camera') !== false || stripos($cartItem['category_name'], 'photo') !== false) {
                                                    $iconClass = 'camera';
                                                } elseif (stripos($cartItem['item_name'], 'tablet') !== false || stripos($cartItem['item_name'], 'ipad') !== false) {
                                                    $iconClass = 'tablet-alt';
                                                } elseif (stripos($cartItem['item_name'], 'microphone') !== false || stripos($cartItem['item_name'], 'mic') !== false) {
                                                    $iconClass = 'microphone';
                                                }
                                                ?>
                                                <i class="fas fa-<?php echo $iconClass; ?>"></i>
                                            </div>
                                            <div class="selected-item-details">
                                                <div class="selected-item-name"><?php echo htmlspecialchars($cartItem['item_name']); ?></div>
                                                <div class="selected-item-meta">
                                                    <span class="badge badge-blue"><?php echo htmlspecialchars($cartItem['category_name'] ?? 'Uncategorized'); ?></span>
                                                    <?php if ($cartItem['item_status'] === 'available'): ?>
                                                        <span class="badge badge-green">Available</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-orange"><?php echo ucfirst($cartItem['item_status']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="selected-item-id">Asset #<?php echo htmlspecialchars($cartItem['asset_id'] ?? 'N/A'); ?></div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline remove-item-btn">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state" id="no-items-message">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <h3>No Items Selected</h3>
                                    <p>Click "Add Items" to select items you want to borrow.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Request Details -->
            <div style="flex: 1; min-width: 300px;">
                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title">Request Details</div>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label class="form-label">Purpose <span style="color: var(--danger);">*</span></label>
                            <select class="form-control" name="purpose" id="purpose" required>
                                <option value="">Select purpose...</option>
                                <option value="Project Work">Project Work</option>
                                <option value="Event">Event</option>
                                <option value="Presentation">Presentation</option>
                                <option value="Training">Training</option>
                                <option value="Remote Work">Remote Work</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="form-group" id="project-name-group" style="display: none;">
                            <label class="form-label">Project/Event Name</label>
                            <input type="text" class="form-control" name="project_name" id="project_name" placeholder="Enter project or event name">
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label">Borrow Date <span style="color: var(--danger);">*</span></label>
                                    <input type="date" class="form-control" name="borrow_date" id="borrow_date" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label">Return Date <span style="color: var(--danger);">*</span></label>
                                    <input type="date" class="form-control" name="return_date" id="return_date" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <label class="form-label" style="margin-bottom: 0;">Duration</label>
                                <span style="font-size: 0.85rem; color: var(--gray-500);">Maximum: 14 days</span>
                            </div>
                            <div style="padding: 0.75rem; background-color: var(--gray-50); border-radius: 6px; color: var(--gray-700);" id="duration-display">
                                Select dates to calculate duration
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Department</label>
                            <div style="padding: 0.75rem; background-color: var(--gray-50); border-radius: 6px; color: var(--gray-700);">
                                <?php echo htmlspecialchars($departmentName); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Additional Notes</label>
                            <textarea class="form-control" name="notes" rows="4" placeholder="Add any special requirements or details..."></textarea>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" id="terms" name="terms" class="form-check-input" required>
                                <label for="terms" class="form-check-label">I agree to handle the equipment responsibly and return it in the same condition</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 1.5rem; text-align: right;">
            <button type="button" class="btn btn-outline" onclick="window.location.href='<?php echo BASE_URL; ?>/items/browse'">
                <i class="fas fa-times btn-icon"></i> Cancel
            </button>
            <button type="button" class="btn btn-primary step-next" id="review-btn">
                <i class="fas fa-arrow-right btn-icon"></i> Review Request
            </button>
        </div>
    </div>
    
    <div class="borrow-step" id="review-step" style="display: none;">
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">Review Your Request</div>
            </div>
            <div class="panel-body">
                <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Request Summary</h3>
                <div style="background-color: var(--gray-50); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
                    <div style="margin-bottom: 1.5rem;">
                        <div style="font-weight: 600; font-size: 1.1rem; margin-bottom: 1rem;">Selected Items</div>
                        <div id="review-items-list">
                            <!-- Items will be populated by JavaScript -->
                        </div>
                    </div>

                    <div style="border-top: 1px solid var(--gray-200); padding-top: 1.5rem;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
                            <div>
                                <div style="font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Purpose</div>
                                <div style="font-weight: 500;" id="review-purpose">-</div>
                            </div>
                            <div id="review-project-container" style="display: none;">
                                <div style="font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Project/Event</div>
                                <div style="font-weight: 500;" id="review-project">-</div>
                            </div>
                            <div>
                                <div style="font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Borrow Date</div>
                                <div style="font-weight: 500;" id="review-borrow-date">-</div>
                            </div>
                            <div>
                                <div style="font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Return Date</div>
                                <div style="font-weight: 500;" id="review-return-date">-</div>
                            </div>
                            <div>
                                <div style="font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Duration</div>
                                <div style="font-weight: 500;" id="review-duration">-</div>
                            </div>
                            <div>
                                <div style="font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Department</div>
                                <div style="font-weight: 500;"><?php echo htmlspecialchars($departmentName); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;" id="notes-section">
                    <div style="font-weight: 600; margin-bottom: 0.75rem;">Additional Notes</div>
                    <div style="background-color: var(--gray-50); border-radius: 8px; padding: 1rem; color: var(--gray-700); font-size: 0.95rem;" id="review-notes">
                        -
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: flex-start;">
                        <div style="margin-right: 1rem; color: var(--primary); font-size: 1.5rem;">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div>
                            <div style="font-weight: 600; margin-bottom: 0.25rem;">Important Information</div>
                            <ul style="padding-left: 1.25rem; margin-bottom: 0; font-size: 0.95rem; color: var(--gray-700);">
                                <li style="margin-bottom: 0.5rem;">Your request will be reviewed by the inventory manager.</li>
                                <li style="margin-bottom: 0.5rem;">You will receive a notification when your request is approved or rejected.</li>
                                <li style="margin-bottom: 0.5rem;">You are responsible for all items during the borrowing period.</li>
                                <li>Return all items on time to avoid penalties.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" id="confirm_terms" name="confirm_terms" class="form-check-input" required>
                        <label for="confirm_terms" class="form-check-label">I confirm that all information is correct and agree to the borrowing terms</label>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 1.5rem; display: flex; justify-content: space-between;">
            <button type="button" class="btn btn-outline step-prev">
                <i class="fas fa-arrow-left btn-icon"></i> Back to Form
            </button>
            <button type="submit" class="btn btn-primary" id="submit-btn">
                <i class="fas fa-check btn-icon"></i> Submit Request
            </button>
        </div>
    </div>
</form>

<!-- Item Selection Modal (Initially Hidden) -->
<div class="modal-backdrop" id="item-selection-modal" style="display: none;">
    <div class="modal" style="max-width: 800px;">
        <div class="modal-header">
            <div class="modal-title">Select Items</div>
            <button class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div style="margin-bottom: 1.5rem;">
                <div class="search-container" style="width: 100%;">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" id="modal-search" placeholder="Search for items...">
                </div>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="flex: 1; min-width: 180px;">
                    <label class="form-label">Category</label>
                    <select class="form-control" id="modal-category">
                        <option value="" selected>All Categories</option>
                        <?php foreach (InventoryHelper::getAllCategories() as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex: 1; min-width: 180px;">
                    <label class="form-label">Location</label>
                    <select class="form-control" id="modal-location">
                        <option value="" selected>All Locations</option>
                        <?php foreach (LocationHelper::getAllLocations(1, 100, ['is_active' => 1])['locations'] as $location): ?>
                            <option value="<?php echo $location['id']; ?>"><?php echo htmlspecialchars($location['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex: 1; min-width: 180px;">
                    <label class="form-label">Status</label>
                    <select class="form-control" id="modal-status">
                        <option value="available" selected>Available Now</option>
                        <option value="">All Items</option>
                    </select>
                </div>
            </div>

            <div id="modal-items-container" style="max-height: 400px; overflow-y: auto;">
                <div class="spinner-container">
                    <div class="spinner"></div>
                    <p>Loading items...</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <div style="display: flex; justify-content: space-between; width: 100%;">
                <button class="btn btn-outline" id="clear-selection-btn">
                    <i class="fas fa-times btn-icon"></i> Clear Selection
                </button>
                <div>
                    <button class="btn btn-outline" id="cancel-selection-btn" style="margin-right: 0.75rem;">Cancel</button>
                    <button class="btn btn-primary" id="confirm-selection-btn">
                        <i class="fas fa-check btn-icon"></i> Confirm Selection (<span id="selected-count">0</span>)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default dates (today and 7 days from now)
    const today = new Date();
    const returnDate = new Date();
    returnDate.setDate(today.getDate() + 7);
    
    const borrowDateInput = document.getElementById('borrow_date');
    const returnDateInput = document.getElementById('return_date');
    
    borrowDateInput.value = today.toISOString().split('T')[0];
    returnDateInput.value = returnDate.toISOString().split('T')[0];
    
    updateDuration();
    
    // Date validation and duration calculation
    borrowDateInput.addEventListener('change', function() {
        // Set min return date to borrow date
        returnDateInput.min = this.value;
        
        // If return date is before new borrow date, update it
        if (returnDateInput.value < this.value) {
            returnDateInput.value = this.value;
        }
        
        updateDuration();
    });
    
    returnDateInput.addEventListener('change', updateDuration);
    
    function updateDuration() {
        const borrowDate = new Date(borrowDateInput.value);
        const returnDate = new Date(returnDateInput.value);
        
        if (borrowDate && returnDate) {
            const diffTime = Math.abs(returnDate - borrowDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            document.getElementById('duration-display').textContent = diffDays + ' days';
        }
    }
    
    // Show/hide project name field based on purpose selection
    const purposeSelect = document.getElementById('purpose');
    const projectNameGroup = document.getElementById('project-name-group');
    
    purposeSelect.addEventListener('change', function() {
        const selectedPurpose = this.value;
        
        if (selectedPurpose === 'Project Work' || selectedPurpose === 'Event') {
            projectNameGroup.style.display = 'block';
            document.getElementById('project_name').setAttribute('required', 'required');
        } else {
            projectNameGroup.style.display = 'none';
            document.getElementById('project_name').removeAttribute('required');
        }
    });
    
    // Step navigation
    const reviewBtn = document.getElementById('review-btn');
    const itemsStep = document.getElementById('items-step');
    const reviewStep = document.getElementById('review-step');
    const backBtns = document.querySelectorAll('.step-prev');
    
    reviewBtn.addEventListener('click', function() {
        const selectedItems = document.querySelectorAll('input[name="item_ids[]"]');
        
        if (selectedItems.length === 0) {
            alert('Please select at least one item to borrow.');
            return;
        }
        
        if (!borrowDateInput.value) {
            alert('Please select a borrow date.');
            borrowDateInput.focus();
            return;
        }
        
        if (!returnDateInput.value) {
            alert('Please select a return date.');
            returnDateInput.focus();
            return;
        }
        
        if (!purposeSelect.value) {
            alert('Please select a purpose for borrowing.');
            purposeSelect.focus();
            return;
        }
        
        if ((purposeSelect.value === 'Project Work' || purposeSelect.value === 'Event') && 
            !document.getElementById('project_name').value) {
            alert('Please enter a project/event name.');
            document.getElementById('project_name').focus();
            return;
        }
        
        // Populate review step
        updateReviewStep();
        
        // Show review step, hide items step
        itemsStep.style.display = 'none';
        reviewStep.style.display = 'block';
        
        // Scroll to top
        window.scrollTo(0, 0);
    });
    
    backBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            reviewStep.style.display = 'none';
            itemsStep.style.display = 'block';
            
            // Scroll to top
            window.scrollTo(0, 0);
        });
    });
    
    function updateReviewStep() {
        // Update selected items
        const reviewItemsList = document.getElementById('review-items-list');
        const selectedItems = document.querySelectorAll('.selected-item');
        
        reviewItemsList.innerHTML = '';
        
        if (selectedItems.length === 0) {
            reviewItemsList.innerHTML = '<div style="text-align: center; padding: 1rem;"><em>No items selected</em></div>';
        } else {
            selectedItems.forEach(item => {
                const itemName = item.querySelector('.selected-item-name').textContent;
                const itemId = item.querySelector('.selected-item-id').textContent;
                const itemIcon = item.querySelector('.selected-item-image i')?.className || '';
                
                const itemElement = document.createElement('div');
                itemElement.style.display = 'flex';
                itemElement.style.padding = '0.75rem 0';
                itemElement.style.borderBottom = '1px solid var(--gray-200)';
                
                itemElement.innerHTML = `
                    <div style="width: 30px; margin-right: 1rem; text-align: center;">
                        <i class="${itemIcon}" style="color: var(--gray-500);"></i>
                    </div>
                    <div>
                        <div style="font-weight: 500;">${itemName}</div>
                        <div style="font-size: 0.85rem; color: var(--gray-600);">${itemId}</div>
                    </div>
                `;
                
                reviewItemsList.appendChild(itemElement);
            });
        }
        
        // Update purpose
        document.getElementById('review-purpose').textContent = purposeSelect.value || '-';
        
        // Update project name
        const projectName = document.getElementById('project_name').value;
        if (projectName && (purposeSelect.value === 'Project Work' || purposeSelect.value === 'Event')) {
            document.getElementById('review-project').textContent = projectName;
            document.getElementById('review-project-container').style.display = 'block';
        } else {
            document.getElementById('review-project-container').style.display = 'none';
        }
        
        // Update dates
        const borrowDate = new Date(borrowDateInput.value);
        const returnDate = new Date(returnDateInput.value);
        
        document.getElementById('review-borrow-date').textContent = borrowDate.toLocaleDateString();
        document.getElementById('review-return-date').textContent = returnDate.toLocaleDateString();
        
        // Update duration
        const diffTime = Math.abs(returnDate - borrowDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        document.getElementById('review-duration').textContent = diffDays + ' days';
        
        // Update notes
        const notes = document.querySelector('textarea[name="notes"]').value;
        if (notes.trim()) {
            document.getElementById('review-notes').textContent = notes;
            document.getElementById('notes-section').style.display = 'block';
        } else {
            document.getElementById('notes-section').style.display = 'none';
        }
    }
    
    // Item selection modal
    const addItemsBtn = document.getElementById('add-items-btn');
    const itemSelectionModal = document.getElementById('item-selection-modal');
    const modalClose = itemSelectionModal.querySelector('.modal-close');
    const cancelSelectionBtn = document.getElementById('cancel-selection-btn');
    const confirmSelectionBtn = document.getElementById('confirm-selection-btn');
    const clearSelectionBtn = document.getElementById('clear-selection-btn');
    const modalItemsContainer = document.getElementById('modal-items-container');
    const selectedItemsContainer = document.getElementById('selected-items-container');
    const selectedCountElement = document.getElementById('selected-count');
    const noItemsMessage = document.getElementById('no-items-message');
    
    // Track selected items
    const selectedItemIds = new Set();
    
    // Initialize with any pre-selected items
    document.querySelectorAll('input[name="item_ids[]"]').forEach(input => {
        selectedItemIds.add(input.value);
    });
    updateSelectedCount();
    
    // Open modal
    addItemsBtn.addEventListener('click', function() {
        itemSelectionModal.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Prevent scrolling
        
        // Load items
        fetchAvailableItems();
    });
    
    // Close modal functions
    function closeModal() {
        itemSelectionModal.style.display = 'none';
        document.body.style.overflow = ''; // Restore scrolling
    }
    
    modalClose.addEventListener('click', closeModal);
    cancelSelectionBtn.addEventListener('click', closeModal);
    
    itemSelectionModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    
    // Fetch available items
    function fetchAvailableItems() {
        const searchTerm = document.getElementById('modal-search').value;
        const categoryId = document.getElementById('modal-category').value;
        const locationId = document.getElementById('modal-location').value;
        const status = document.getElementById('modal-status').value;
        
        modalItemsContainer.innerHTML = `
            <div class="spinner-container">
                <div class="spinner"></div>
                <p>Loading items...</p>
            </div>
        `;
        
        // Make AJAX request to get available items
        fetch(`<?php echo BASE_URL; ?>/api/items?format=modal&search=${encodeURIComponent(searchTerm)}&category=${categoryId}&location=${locationId}&status=${status}`)
            .then(response => response.json())
            .then(data => {
                renderModalItems(data.items);
            })
            .catch(error => {
                console.error('Error fetching items:', error);
                modalItemsContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> Failed to load items. Please try again.
                    </div>
                `;
            });
    }
    
    // Render items in modal
    function renderModalItems(items) {
        if (!items || items.length === 0) {
            modalItemsContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h3>No Items Found</h3>
                    <p>Try adjusting your search criteria.</p>
                </div>
            `;
            return;
        }
        
        modalItemsContainer.innerHTML = '';
        
        items.forEach(item => {
            const isSelected = selectedItemIds.has(item.id.toString());
            
            const itemElement = document.createElement('div');
            itemElement.className = 'modal-item';
            if (isSelected) {
                itemElement.classList.add('selected');
            }
            itemElement.dataset.itemId = item.id;
            
            // Determine icon based on category
            let iconClass = 'box';
            
            if (item.category_name) {
                if (item.category_name.toLowerCase().includes('computer') || item.name.toLowerCase().includes('laptop')) {
                    iconClass = 'laptop';
                } else if (item.category_name.toLowerCase().includes('audio')) {
                    iconClass = 'headphones';
                } else if (item.category_name.toLowerCase().includes('video') || item.name.toLowerCase().includes('projector')) {
                    iconClass = 'video';
                } else if (item.category_name.toLowerCase().includes('camera') || item.category_name.toLowerCase().includes('photo')) {
                    iconClass = 'camera';
                } else if (item.name.toLowerCase().includes('tablet') || item.name.toLowerCase().includes('ipad')) {
                    iconClass = 'tablet-alt';
                } else if (item.name.toLowerCase().includes('microphone') || item.name.toLowerCase().includes('mic')) {
                    iconClass = 'microphone';
                }
            }
            
            itemElement.innerHTML = `
                <div style="margin-right: 1rem;">
                    <input type="checkbox" ${isSelected ? 'checked' : ''}>
                </div>
                <div style="display: flex; flex: 1; min-width: 0;">
                    <div style="width: 60px; height: 60px; background-color: var(--gray-100); border-radius: 6px; display: flex; align-items: center; justify-content: center; margin-right: 1rem; flex-shrink: 0;">
                        <i class="fas fa-${iconClass}" style="font-size: 1.5rem; color: var(--gray-500);"></i>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 600; margin-bottom: 0.25rem;">${item.name}</div>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.25rem;">
                            <span class="badge badge-blue">${item.category_name || 'Uncategorized'}</span>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--gray-600);">${item.location_name || 'No Location'} • Asset #${item.asset_id || 'N/A'}</div>
                    </div>
                    <div style="display: flex; flex-direction: column; justify-content: center; align-items: flex-end; margin-left: 1rem;">
                        <span class="badge badge-green" style="margin-bottom: 0.5rem;">Available</span>
                        <button class="btn btn-sm btn-${isSelected ? 'primary' : 'outline'} select-btn">
                            ${isSelected ? 'Selected' : 'Select'}
                        </button>
                    </div>
                </div>
            `;
            
            // Add click event for selection
            itemElement.addEventListener('click', function(e) {
                // Don't trigger if clicking on the button specifically
                if (e.target.classList.contains('select-btn') || e.target.closest('.select-btn')) {
                    return;
                }
                
                const checkbox = this.querySelector('input[type="checkbox"]');
                checkbox.checked = !checkbox.checked;
                
                toggleItemSelection(this);
            });
            
            // Add click event for select button
            const selectBtn = itemElement.querySelector('.select-btn');
            selectBtn.addEventListener('click', function() {
                const checkbox = itemElement.querySelector('input[type="checkbox"]');
                checkbox.checked = !checkbox.checked;
                
                toggleItemSelection(itemElement);
            });
            
            modalItemsContainer.appendChild(itemElement);
        });
    }
    
    // Toggle item selection in modal
    function toggleItemSelection(itemElement) {
        const itemId = itemElement.dataset.itemId;
        const checkbox = itemElement.querySelector('input[type="checkbox"]');
        const selectBtn = itemElement.querySelector('.select-btn');
        
        if (checkbox.checked) {
            selectedItemIds.add(itemId);
            itemElement.classList.add('selected');
            selectBtn.classList.remove('btn-outline');
            selectBtn.classList.add('btn-primary');
            selectBtn.textContent = 'Selected';
        } else {
            selectedItemIds.delete(itemId);
            itemElement.classList.remove('selected');
            selectBtn.classList.remove('btn-primary');
            selectBtn.classList.add('btn-outline');
            selectBtn.textContent = 'Select';
        }
        
        updateSelectedCount();
    }
    
    // Update selected count
    function updateSelectedCount() {
        selectedCountElement.textContent = selectedItemIds.size;
    }
    
    // Clear selection
    clearSelectionBtn.addEventListener('click', function() {
        selectedItemIds.clear();
        
        // Update modal UI
        document.querySelectorAll('.modal-item').forEach(item => {
            const checkbox = item.querySelector('input[type="checkbox"]');
            const selectBtn = item.querySelector('.select-btn');
            
            checkbox.checked = false;
            item.classList.remove('selected');
            selectBtn.classList.remove('btn-primary');
            selectBtn.classList.add('btn-outline');
            selectBtn.textContent = 'Select';
        });
        
        updateSelectedCount();
    });
    
    // Confirm selection
    confirmSelectionBtn.addEventListener('click', function() {
        // Update selected items container
        updateSelectedItemsContainer();
        
        // Close modal
        closeModal();
    });
    
    // Update the main form with selected items
    function updateSelectedItemsContainer() {
        // Get all items from modal
        const modalItems = document.querySelectorAll('.modal-item.selected');
        
        if (modalItems.length === 0) {
            return;
        }
        
        // Clear no items message if present
        if (noItemsMessage) {
            noItemsMessage.style.display = 'none';
        }
        
        // Add each selected item to form
        modalItems.forEach(modalItem => {
            const itemId = modalItem.dataset.itemId;
            
            // Check if item is already in the form
            const existingItem = selectedItemsContainer.querySelector(`.selected-item[data-item-id="${itemId}"]`);
            if (existingItem) {
                return;
            }
            
            // Create new item element
            const itemName = modalItem.querySelector('div > div:nth-child(2) > div').textContent;
            const itemMeta = modalItem.querySelector('div > div:nth-child(2) > div:nth-child(2)').innerHTML;
            const itemLocation = modalItem.querySelector('div > div:nth-child(2) > div:nth-child(3)').textContent;
            const iconClass = modalItem.querySelector('i').className;
            
            const newItem = document.createElement('div');
            newItem.className = 'selected-item';
            newItem.dataset.itemId = itemId;
            
            newItem.innerHTML = `
                <input type="hidden" name="item_ids[]" value="${itemId}">
                <div class="selected-item-info">
                    <div class="selected-item-image">
                        <i class="${iconClass}"></i>
                    </div>
                    <div class="selected-item-details">
                        <div class="selected-item-name">${itemName}</div>
                        <div class="selected-item-meta">${itemMeta}</div>
                        <div class="selected-item-id">${itemLocation}</div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline remove-item-btn">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            // Add remove event listener
            const removeBtn = newItem.querySelector('.remove-item-btn');
            removeBtn.addEventListener('click', function() {
                newItem.remove();
                selectedItemIds.delete(itemId);
                
                // Show no items message if no items left
                if (selectedItemsContainer.querySelectorAll('.selected-item').length === 0) {
                    noItemsMessage.style.display = 'block';
                }
            });
            
            // Add to container
            selectedItemsContainer.appendChild(newItem);
        });
    }
    
    // Add event listeners for existing remove buttons
    document.querySelectorAll('.remove-item-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const item = this.closest('.selected-item');
            const itemId = item.dataset.itemId;
            
            item.remove();
            selectedItemIds.delete(itemId);
            
            // Show no items message if no items left
            if (selectedItemsContainer.querySelectorAll('.selected-item').length === 0) {
                noItemsMessage.style.display = 'block';
            }
        });
    });
    
    // Modal search and filters
    document.getElementById('modal-search').addEventListener('input', debounce(fetchAvailableItems, 500));
    document.getElementById('modal-category').addEventListener('change', fetchAvailableItems);
    document.getElementById('modal-location').addEventListener('change', fetchAvailableItems);
    document.getElementById('modal-status').addEventListener('change', fetchAvailableItems);
    
    // Form validation before submit
    document.getElementById('borrow-request-form').addEventListener('submit', function(e) {
        const selectedItems = document.querySelectorAll('input[name="item_ids[]"]');
        
        if (selectedItems.length === 0) {
            e.preventDefault();
            alert('Please select at least one item to borrow.');
            return;
        }
        
        if (!document.getElementById('confirm_terms').checked) {
            e.preventDefault();
            alert('Please confirm that all information is correct and agree to the borrowing terms.');
            return;
        }
    });
    
    // Debounce function for search
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }
});
</script>

<style>
/* Additional styles for borrow request page */
.selected-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    border: 1px solid var(--gray-200);
    border-radius: 8px;
    margin-bottom: 0.75rem;
    background-color: white;
}

.selected-item-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.selected-item-image {
    width: 60px;
    height: 60px;
    background-color: var(--gray-100);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gray-500);
    font-size: 1.5rem;
}

.selected-item-details {
    min-width: 0;
}

.selected-item-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.selected-item-meta {
    margin-bottom: 0.25rem;
}

.selected-item-id {
    font-size: 0.85rem;
    color: var(--gray-600);
}

.modal-item {
    display: flex;
    padding: 1rem;
    border: 1px solid var(--gray-200);
    border-radius: 8px;
    margin-bottom: 0.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.modal-item:hover {
    border-color: var(--primary-light);
    background-color: rgba(var(--primary-rgb), 0.05);
}

.modal-item.selected {
    border: 2px solid var(--primary);
    background-color: rgba(var(--primary-rgb), 0.1);
}

.stepper {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin-bottom: 2rem;
}

.stepper-progress {
    position: absolute;
    height: 2px;
    background-color: var(--primary);
    top: 24px;
    z-index: 1;
    transition: width 0.3s ease;
}

.stepper-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
    width: 33.33%;
}

.stepper-dot {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background-color: white;
    border: 2px solid var(--gray-300);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.stepper-step.active .stepper-dot {
    border-color: var(--primary);
    background-color: var(--primary);
    color: white;
}

.stepper-step.completed .stepper-dot {
    border-color: var(--primary);
    background-color: var(--primary);
    color: white;
}

.stepper-label {
    font-weight: 500;
    color: var(--gray-500);
    transition: color 0.3s ease;
}

.stepper-step.active .stepper-label {
    color: var(--primary);
}

.stepper-step.completed .stepper-label {
    color: var(--primary);
}

.empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
    background-color: var(--gray-50);
    border-radius: 8px;
}

.empty-state-icon {
    font-size: 3rem;
    color: var(--gray-400);
    margin-bottom: 1rem;
}

.empty-state h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.spinner-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 0;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid var(--gray-200);
    border-top: 3px solid var(--primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<?php
// Include footer
include 'includes/footer.php';
?>