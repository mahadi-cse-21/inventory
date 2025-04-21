<?php
/**
 * View Borrow Request Details (Admin/Manager View)
 * 
 * This file displays detailed information about a borrow request with admin controls
 */

// Set page title
$pageTitle = 'Borrow Request Details';

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

// Process approve/reject/checkout/return if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    validateCsrfToken();
    
    $action = isset($_POST['action']) ? cleanInput($_POST['action']) : '';
    $notes = isset($_POST['notes']) ? cleanInput($_POST['notes']) : '';
    
    if (!in_array($action, ['approve', 'reject', 'checkout', 'return'])) {
        setFlashMessage('Invalid action', 'danger');
        redirect(BASE_URL . '/borrow/view?id=' . $requestId);
        exit;
    }
    
    // Determine the new status based on action
    $newStatus = '';
    
    switch ($action) {
        case 'approve':
            $newStatus = 'approved';
            break;
        case 'reject':
            $newStatus = 'rejected';
            break;
        case 'checkout':
            $newStatus = 'checked_out';
            break;
        case 'return':
            $newStatus = 'returned';
            break;
    }
    
    // Update request status
    $result = BorrowHelper::updateBorrowRequestStatus($requestId, $newStatus, $currentUser['id'], $notes);
    
    if ($result['success']) {
        setFlashMessage('Borrow request ' . str_replace('_', ' ', $newStatus) . ' successfully', 'success');
    } else {
        setFlashMessage($result['message'], 'danger');
    }
    
    // Redirect to refresh the page
    redirect(BASE_URL . '/borrow/view?id=' . $requestId);
    exit;
}

// Include header
include 'includes/header.php';
?>

<div class="content-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Request: <?php echo htmlspecialchars($request['request_id']); ?></h1>
            <nav class="breadcrumbs">
                <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Home</a>
                <a href="<?php echo BASE_URL; ?>/borrow/requests" class="breadcrumb-item">Borrow Requests</a>
                <span class="breadcrumb-item">View Request</span>
            </nav>
        </div>
        <div>
            <a href="<?php echo BASE_URL; ?>/borrow/requests" class="btn btn-outline">
                <i class="fas fa-arrow-left btn-icon"></i> Back to Requests
            </a>
            
            <?php if ($request['status'] === 'pending'): ?>
                <button class="btn btn-green process-btn" data-action="approve">
                    <i class="fas fa-check btn-icon"></i> Approve
                </button>
                <button class="btn btn-danger process-btn" data-action="reject">
                    <i class="fas fa-times btn-icon"></i> Reject
                </button>
            <?php endif; ?>
            
            <?php if ($request['status'] === 'approved'): ?>
                <button class="btn btn-purple process-btn" data-action="checkout">
                    <i class="fas fa-hand-holding btn-icon"></i> Check Out
                </button>
            <?php endif; ?>
            
            <?php if ($request['status'] === 'checked_out' || $request['status'] === 'overdue'): ?>
                <a href="<?php echo BASE_URL; ?>/borrow/return?id=<?php echo $request['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-undo-alt btn-icon"></i> Return Items
                </a>
            <?php endif; ?>
            
            <button class="btn btn-outline" id="print-receipt-btn">
                <i class="fas fa-print btn-icon"></i> Print
            </button>
        </div>
    </div>
</div>

<!-- Status Banner -->
<div class="status-banner status-<?php echo $request['status']; ?>">
    <div class="status-icon">
        <?php
        $statusIcon = 'info-circle';
        
        switch ($request['status']) {
            case 'pending':
                $statusIcon = 'hourglass-half';
                break;
            case 'approved':
                $statusIcon = 'check-circle';
                break;
            case 'rejected':
                $statusIcon = 'times-circle';
                break;
            case 'cancelled':
                $statusIcon = 'ban';
                break;
            case 'checked_out':
                $statusIcon = 'hand-holding';
                break;
            case 'overdue':
                $statusIcon = 'exclamation-circle';
                break;
            case 'partially_returned':
                $statusIcon = 'sync';
                break;
            case 'returned':
                $statusIcon = 'undo-alt';
                break;
        }
        ?>
        <i class="fas fa-<?php echo $statusIcon; ?>"></i>
    </div>
    <div class="status-info">
        <div class="status-title">
            Status: <?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?>
        </div>
        <div class="status-description">
            <?php
            switch ($request['status']) {
                case 'pending':
                    echo 'This request is awaiting approval.';
                    break;
                case 'approved':
                    echo 'This request has been approved and is ready for checkout.';
                    break;
                case 'rejected':
                    echo 'This request has been rejected.';
                    break;
                case 'cancelled':
                    echo 'This request has been cancelled by the requester.';
                    break;
                case 'checked_out':
                    echo 'Items have been checked out and are currently borrowed.';
                    break;
                case 'overdue':
                    echo 'Items are overdue. Please contact the borrower.';
                    break;
                case 'partially_returned':
                    echo 'Some items have been returned, others are still checked out.';
                    break;
                case 'returned':
                    echo 'All items have been returned.';
                    break;
            }
            ?>
        </div>
    </div>
</div>

<!-- Request Details -->
<div class="panel" style="margin-bottom: 1.5rem;">
    <div class="panel-header">
        <div class="panel-title">Request Information</div>
    </div>
    <div class="panel-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <div style="font-weight: 600; font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Requester</div>
                <div style="font-weight: 500;"><?php echo htmlspecialchars($request['requester_name']); ?></div>
                <div style="font-size: 0.85rem; color: var(--gray-600);"><?php echo htmlspecialchars($request['requester_email']); ?></div>
            </div>
            <div>
                <div style="font-weight: 600; font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Department</div>
                <div style="font-weight: 500;"><?php echo htmlspecialchars($request['department_name'] ?? 'Not Specified'); ?></div>
            </div>
            <div>
                <div style="font-weight: 600; font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Request Date</div>
                <div style="font-weight: 500;"><?php echo UtilityHelper::formatDateForDisplay($request['created_at'], 'datetime'); ?></div>
            </div>
            <div>
                <div style="font-weight: 600; font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Purpose</div>
                <div style="font-weight: 500;"><?php echo htmlspecialchars($request['purpose']); ?></div>
            </div>
            <?php if (!empty($request['project_name'])): ?>
            <div>
                <div style="font-weight: 600; font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Project/Event</div>
                <div style="font-weight: 500;"><?php echo htmlspecialchars($request['project_name']); ?></div>
            </div>
            <?php endif; ?>
            <div>
                <div style="font-weight: 600; font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Borrow Date</div>
                <div style="font-weight: 500;"><?php echo UtilityHelper::formatDateForDisplay($request['borrow_date']); ?></div>
            </div>
            <div>
                <div style="font-weight: 600; font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Return Date</div>
                <div style="font-weight: 500;"><?php echo UtilityHelper::formatDateForDisplay($request['return_date']); ?></div>
            </div>
            <div>
                <div style="font-weight: 600; font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Duration</div>
                <div style="font-weight: 500;"><?php echo UtilityHelper::dateDiffInDays($request['borrow_date'], $request['return_date']); ?> days</div>
            </div>
            <?php if (!empty($request['pickup_time'])): ?>
            <div>
                <div style="font-weight: 600; font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Pickup Time</div>
                <div style="font-weight: 500;"><?php echo date('g:i A', strtotime($request['pickup_time'])); ?></div>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($request['notes'])): ?>
        <div style="margin-bottom: 1.5rem;">
            <div style="font-weight: 600; font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Additional Notes</div>
            <div style="padding: 0.75rem; background-color: var(--gray-50); border-radius: 6px; color: var(--gray-700);">
                <?php echo nl2br(htmlspecialchars($request['notes'])); ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($request['status'] === 'rejected' && !empty($request['rejection_reason'])): ?>
        <div style="margin-bottom: 1.5rem;">
            <div style="font-weight: 600; font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Rejection Reason</div>
            <div style="padding: 0.75rem; background-color: var(--error-light); border-radius: 6px; color: var(--danger);">
                <?php echo nl2br(htmlspecialchars($request['rejection_reason'])); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Borrowed Items -->
<div class="panel" style="margin-bottom: 1.5rem;">
    <div class="panel-header">
        <div class="panel-title">Borrowed Items</div>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Asset ID</th>
                        <th>Location</th>
                        <th>Status</th>
                        <?php if ($request['status'] === 'checked_out' || $request['status'] === 'partially_returned' || $request['status'] === 'returned'): ?>
                        <th>Condition</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($request['items'])): ?>
                        <?php foreach ($request['items'] as $item): ?>
                            <tr>
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
                                <td><?php echo htmlspecialchars($item['location_name'] ?? 'Not Set'); ?></td>
                                <td>
                                    <?php 
                                    $itemBadgeClass = 'badge-blue';
                                    
                                    switch ($item['status']) {
                                        case 'pending':
                                            $itemBadgeClass = 'badge-blue';
                                            break;
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
                                <?php if ($request['status'] === 'checked_out' || $request['status'] === 'partially_returned' || $request['status'] === 'returned'): ?>
                                <td>
                                    <?php if ($item['status'] === 'returned'): ?>
                                        <?php echo ucfirst($item['condition_after'] ?? 'Good'); ?>
                                        <?php if (!empty($item['return_notes'])): ?>
                                            <i class="fas fa-info-circle" title="<?php echo htmlspecialchars($item['return_notes']); ?>"></i>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php echo ucfirst($item['condition_before'] ?? 'Good'); ?>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">No items in this request</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Request Timeline -->
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Request Timeline</div>
    </div>
    <div class="panel-body">
        <div class="timeline">
            <!-- Created -->
            <div class="timeline-item">
                <div class="timeline-dot active"></div>
                <div class="timeline-content">
                    <div class="timeline-title">Request Submitted</div>
                    <div class="timeline-date"><?php echo UtilityHelper::formatDateForDisplay($request['created_at'], 'datetime'); ?></div>
                    <div>Submitted by: <?php echo htmlspecialchars($request['requester_name']); ?></div>
                </div>
            </div>
            
            <!-- Approved/Rejected -->
            <?php if ($request['status'] !== 'pending'): ?>
                <?php if ($request['status'] === 'approved' || $request['status'] === 'checked_out' || $request['status'] === 'overdue' || $request['status'] === 'partially_returned' || $request['status'] === 'returned'): ?>
                    <div class="timeline-item">
                        <div class="timeline-dot active"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Request Approved</div>
                            <div class="timeline-date"><?php echo UtilityHelper::formatDateForDisplay($request['approved_at'], 'datetime'); ?></div>
                            <div>Approved by: <?php echo htmlspecialchars($request['approver_name'] ?? 'System'); ?></div>
                        </div>
                    </div>
                <?php elseif ($request['status'] === 'rejected'): ?>
                    <div class="timeline-item">
                        <div class="timeline-dot active"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Request Rejected</div>
                            <div class="timeline-date"><?php echo UtilityHelper::formatDateForDisplay($request['updated_at'], 'datetime'); ?></div>
                            <?php if (!empty($request['rejection_reason'])): ?>
                                <div>Reason: <?php echo htmlspecialchars($request['rejection_reason']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif ($request['status'] === 'cancelled'): ?>
                    <div class="timeline-item">
                        <div class="timeline-dot active"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Request Cancelled</div>
                            <div class="timeline-date"><?php echo UtilityHelper::formatDateForDisplay($request['updated_at'], 'datetime'); ?></div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Checked Out -->
            <?php if ($request['status'] === 'checked_out' || $request['status'] === 'overdue' || $request['status'] === 'partially_returned' || $request['status'] === 'returned'): ?>
                <div class="timeline-item">
                    <div class="timeline-dot active"></div>
                    <div class="timeline-content">
                        <div class="timeline-title">Items Checked Out</div>
                        <div class="timeline-date"><?php echo UtilityHelper::formatDateForDisplay($request['checked_out_at'], 'datetime'); ?></div>
                        <div>Checked out by: <?php echo htmlspecialchars($request['checkout_by_name'] ?? 'System'); ?></div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Overdue -->
            <?php if ($request['status'] === 'overdue'): ?>
                <div class="timeline-item">
                    <div class="timeline-dot active"></div>
                    <div class="timeline-content">
                        <div class="timeline-title">Items Overdue</div>
                        <div class="timeline-date"><?php echo UtilityHelper::formatDateForDisplay($request['return_date'], 'datetime'); ?></div>
                        <div>Items were due to be returned on this date.</div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Partially Returned -->
            <?php if ($request['status'] === 'partially_returned'): ?>
                <div class="timeline-item">
                    <div class="timeline-dot active"></div>
                    <div class="timeline-content">
                        <div class="timeline-title">Items Partially Returned</div>
                        <div class="timeline-date"><?php echo UtilityHelper::formatDateForDisplay($request['updated_at'], 'datetime'); ?></div>
                        <div>Some items have been returned. Remaining items are still checked out.</div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Returned -->
            <?php if ($request['status'] === 'returned'): ?>
                <div class="timeline-item">
                    <div class="timeline-dot active"></div>
                    <div class="timeline-content">
                        <div class="timeline-title">All Items Returned</div>
                        <div class="timeline-date"><?php echo UtilityHelper::formatDateForDisplay($request['returned_at'], 'datetime'); ?></div>
                        <div>Returned by: <?php echo htmlspecialchars($request['returned_by_name'] ?? 'System'); ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Process Request Form (Hidden) -->
<div class="modal-backdrop" id="process-modal" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title" id="process-modal-title">Process Request</div>
            <button class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="process-form" method="POST" action="<?php echo BASE_URL; ?>/borrow/view?id=<?php echo $request['id']; ?>">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                <input type="hidden" name="action" id="process-action" value="">
                
                <div id="reject-fields" style="display: none;">
                    <div class="form-group">
                        <label class="form-label" for="reject-reason">Rejection Reason</label>
                        <textarea class="form-control" id="reject-reason" name="notes" rows="3" placeholder="Please provide a reason for rejecting this request..."></textarea>
                    </div>
                </div>
                
                <div id="approve-fields" style="display: none;">
                    <div class="form-group">
                        <label class="form-label" for="approve-notes">Notes (Optional)</label>
                        <textarea class="form-control" id="approve-notes" name="notes" rows="3" placeholder="Add any notes or instructions for the requester..."></textarea>
                    </div>
                </div>
                
                <div id="checkout-fields" style="display: none;">
                    <div class="form-group">
                        <label class="form-label" for="checkout-notes">Checkout Notes (Optional)</label>
                        <textarea class="form-control" id="checkout-notes" name="notes" rows="3" placeholder="Add any notes about item condition or special instructions..."></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" id="cancel-process-btn">Cancel</button>
            <button type="submit" form="process-form" class="btn btn-primary" id="confirm-process-btn">Confirm</button>
        </div>
    </div>
</div>

<!-- Print Receipt Template (Hidden) -->
<div id="print-template" style="display: none;">
    <div style="max-width: 800px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
        <div style="text-align: center; margin-bottom: 20px;">
            <h1 style="margin-bottom: 5px;">Inventory Pro</h1>
            <h2><?php echo $request['status'] === 'returned' ? 'Return Receipt' : 'Borrow Receipt'; ?></h2>
            <p>Request ID: <?php echo htmlspecialchars($request['request_id']); ?></p>
        </div>
        
        <div style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #ddd;">
            <div style="display: flex; justify-content: space-between;">
                <div>
                    <strong>Requester:</strong> <?php echo htmlspecialchars($request['requester_name']); ?><br>
                    <strong>Department:</strong> <?php echo htmlspecialchars($request['department_name'] ?? 'Not Specified'); ?><br>
                    <strong>Purpose:</strong> <?php echo htmlspecialchars($request['purpose']); ?>
                    <?php if (!empty($request['project_name'])): ?>
                        <br><strong>Project:</strong> <?php echo htmlspecialchars($request['project_name']); ?>
                    <?php endif; ?>
                </div>
                <div>
                    <strong>Borrow Date:</strong> <?php echo UtilityHelper::formatDateForDisplay($request['borrow_date']); ?><br>
                    <strong>Return Date:</strong> <?php echo UtilityHelper::formatDateForDisplay($request['return_date']); ?><br>
                    <strong>Status:</strong> <?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?>
                </div>
            </div>
        </div>
        
        <div style="margin-bottom: 20px;">
            <h3>Items</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Item</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Asset ID</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Location</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Status</th>
                        <?php if ($request['status'] === 'checked_out' || $request['status'] === 'partially_returned' || $request['status'] === 'returned'): ?>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Condition</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($request['items'])): ?>
                        <?php foreach ($request['items'] as $item): ?>
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px;">
                                    <?php echo htmlspecialchars($item['item_name']); ?>
                                </td>
                                <td style="border: 1px solid #ddd; padding: 8px;">
                                    <?php echo htmlspecialchars($item['asset_id'] ?? 'N/A'); ?>
                                </td>
                                <td style="border: 1px solid #ddd; padding: 8px;">
                                    <?php echo htmlspecialchars($item['location_name'] ?? 'Not Set'); ?>
                                </td>
                                <td style="border: 1px solid #ddd; padding: 8px;">
                                    <?php echo ucfirst($item['status']); ?>
                                </td>
                                <?php if ($request['status'] === 'checked_out' || $request['status'] === 'partially_returned' || $request['status'] === 'returned'): ?>
                                <td style="border: 1px solid #ddd; padding: 8px;">
                                    <?php if ($item['status'] === 'returned'): ?>
                                        <?php echo ucfirst($item['condition_after'] ?? 'Good'); ?>
                                    <?php else: ?>
                                        <?php echo ucfirst($item['condition_before'] ?? 'Good'); ?>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div style="margin-bottom: 20px;">
            <h3>Notes</h3>
            <p><?php echo !empty($request['notes']) ? nl2br(htmlspecialchars($request['notes'])) : 'No notes'; ?></p>
        </div>
        
        <?php if ($request['status'] === 'returned'): ?>
            <div style="margin-bottom: 20px;">
                <h3>Return Information</h3>
                <p><strong>Returned On:</strong> <?php echo UtilityHelper::formatDateForDisplay($request['returned_at'], 'datetime'); ?></p>
                <p><strong>Processed By:</strong> <?php echo htmlspecialchars($request['returned_by_name'] ?? 'System'); ?></p>
            </div>
        <?php elseif ($request['status'] === 'checked_out'): ?>
            <div style="margin-bottom: 20px;">
                <h3>Checkout Information</h3>
                <p><strong>Checked Out On:</strong> <?php echo UtilityHelper::formatDateForDisplay($request['checked_out_at'], 'datetime'); ?></p>
                <p><strong>Processed By:</strong> <?php echo htmlspecialchars($request['checkout_by_name'] ?? 'System'); ?></p>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 40px; display: flex; justify-content: space-between;">
            <div style="width: 45%; border-top: 1px solid #000;">
                <p style="text-align: center;">Requester Signature</p>
            </div>
            <div style="width: 45%; border-top: 1px solid #000;">
                <p style="text-align: center;">Staff Signature</p>
            </div>
        </div>
        
        <div style="margin-top: 30px; font-size: 0.8em; text-align: center; color: #666;">
            <p>This receipt was generated on <?php echo date('F j, Y, g:i a'); ?></p>
            <p>Inventory Pro System</p>
        </div>
    </div>
</div>

<style>
.status-banner {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.status-pending {
    background-color: rgba(var(--blue-rgb), 0.1);
    border-left: 4px solid var(--blue);
}

.status-approved {
    background-color: rgba(var(--green-rgb), 0.1);
    border-left: 4px solid var(--green);
}

.status-rejected {
    background-color: rgba(var(--danger-rgb), 0.1);
    border-left: 4px solid var(--danger);
}

.status-cancelled {
    background-color: rgba(var(--gray-rgb), 0.1);
    border-left: 4px solid var(--gray-600);
}

.status-checked_out {
    background-color: rgba(var(--purple-rgb), 0.1);
    border-left: 4px solid var(--purple);
}

.status-overdue {
    background-color: rgba(var(--warning-rgb), 0.1);
    border-left: 4px solid var(--warning);
}

.status-partially_returned {
    background-color: rgba(var(--yellow-rgb), 0.1);
    border-left: 4px solid var(--yellow);
}

.status-returned {
    background-color: rgba(var(--green-rgb), 0.1);
    border-left: 4px solid var(--green);
}

.status-icon {
    font-size: 2rem;
    margin-right: 1rem;
}

.status-pending .status-icon {
    color: var(--blue);
}

.status-approved .status-icon {
    color: var(--green);
}

.status-rejected .status-icon {
    color: var(--danger);
}

.status-cancelled .status-icon {
    color: var(--gray-600);
}

.status-checked_out .status-icon {
    color: var(--purple);
}

.status-overdue .status-icon {
    color: var(--warning);
}

.status-partially_returned .status-icon {
    color: var(--yellow);
}

.status-returned .status-icon {
    color: var(--green);
}

.status-title {
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 0.25rem;
}

.status-description {
    color: var(--gray-600);
}

.timeline {
    position: relative;
    padding-left: 30px;
    margin: 0 0 20px 10px;
}

.timeline:before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: var(--gray-200);
}

.timeline-item {
    position: relative;
    margin-bottom: 25px;
}

.timeline-dot {
    position: absolute;
    left: -38px;
    top: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background-color: var(--gray-400);
    border: 2px solid white;
}

.timeline-dot.active {
    background-color: var(--primary);
}

.timeline-content {
    padding-bottom: 10px;
}

.timeline-title {
    font-weight: 600;
    margin-bottom: 5px;
}

.timeline-date {
    font-size: 0.85rem;
    color: var(--gray-600);
    margin-bottom: 5px;
}

.btn-green {
    background-color: var(--green);
    color: white;
}

.btn-green:hover {
    background-color: var(--green-dark);
}

.btn-purple {
    background-color: var(--purple);
    color: white;
}

.btn-purple:hover {
    background-color: var(--purple-dark);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Print receipt functionality
    const printReceiptBtn = document.getElementById('print-receipt-btn');
    
    if (printReceiptBtn) {
        printReceiptBtn.addEventListener('click', function() {
            const printTemplate = document.getElementById('print-template');
            const printWindow = window.open('', '_blank');
            
            printWindow.document.write('<html><head><title>Borrow Receipt</title>');
            printWindow.document.write('<style>@media print { body { font-family: Arial, sans-serif; } }</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(printTemplate.innerHTML);
            printWindow.document.write('</body></html>');
            
            printWindow.document.close();
            printWindow.focus();
            
            // Print after a slight delay to ensure the content is loaded
            setTimeout(function() {
                printWindow.print();
                printWindow.close();
            }, 250);
        });
    }
    
    // Process request modal
    const processModal = document.getElementById('process-modal');
    const modalTitle = document.getElementById('process-modal-title');
    const modalClose = processModal.querySelector('.modal-close');
    const cancelBtn = document.getElementById('cancel-process-btn');
    const processForm = document.getElementById('process-form');
    const processAction = document.getElementById('process-action');
    const rejectFields = document.getElementById('reject-fields');
    const approveFields = document.getElementById('approve-fields');
    const checkoutFields = document.getElementById('checkout-fields');
    const confirmBtn = document.getElementById('confirm-process-btn');
    
    // Handle process button clicks
    const processBtns = document.querySelectorAll('.process-btn');
    
    processBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            
            // Set form values
            processAction.value = action;
            
            // Update modal title and visible fields
            switch (action) {
                case 'approve':
                    modalTitle.textContent = 'Approve Request';
                    rejectFields.style.display = 'none';
                    approveFields.style.display = 'block';
                    checkoutFields.style.display = 'none';
                    confirmBtn.className = 'btn btn-green';
                    confirmBtn.textContent = 'Approve Request';
                    break;
                case 'reject':
                    modalTitle.textContent = 'Reject Request';
                    rejectFields.style.display = 'block';
                    approveFields.style.display = 'none';
                    checkoutFields.style.display = 'none';
                    confirmBtn.className = 'btn btn-danger';
                    confirmBtn.textContent = 'Reject Request';
                    break;
                case 'checkout':
                    modalTitle.textContent = 'Check Out Items';
                    rejectFields.style.display = 'none';
                    approveFields.style.display = 'none';
                    checkoutFields.style.display = 'block';
                    confirmBtn.className = 'btn btn-purple';
                    confirmBtn.textContent = 'Confirm Check Out';
                    break;
            }
            
            // Show modal
            processModal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        });
    });
    
    // Close modal
    modalClose.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    
    processModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && processModal.style.display === 'flex') {
            closeModal();
        }
    });
    
    function closeModal() {
        processModal.style.display = 'none';
        document.body.style.overflow = ''; // Restore scrolling
        processForm.reset();
    }
    
    // Handle form validation
    processForm.addEventListener('submit', function(e) {
        const action = processAction.value;
        
        if (action === 'reject') {
            const rejectReason = document.getElementById('reject-reason').value.trim();
            if (!rejectReason) {
                e.preventDefault();
                alert('Please provide a reason for rejecting this request.');
                return;
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>