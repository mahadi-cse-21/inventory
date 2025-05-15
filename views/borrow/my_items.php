<?php

/**
 * My Borrowed Items View
 * 
 * This file displays the currently borrowed items for the logged-in user
 */

// Set page title
$pageTitle = 'My Borrowed Items';

// Check if user is logged in
if (!AuthHelper::isAuthenticated()) {
    setFlashMessage('You must be logged in to view your borrowed items.', 'danger');
    redirect(BASE_URL . '/auth/login');
    exit;
}

// Get current user
$currentUser = AuthHelper::getCurrentUser();

// Get the user's borrowed items
$borrowedItems = BorrowHelper::getUserBorrowedItems($currentUser['id']);

// Get overdue items
$overdueItems = BorrowHelper::getUserOverdueItems($currentUser['id']);

// Check for overdue items in the system
if (hasRole(['admin'])) {
    // Only admins and managers should run this check
    BorrowHelper::checkForOverdueItems();
}

// Include header
include 'includes/header.php';
?>

<div class="content-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">My Borrowed Items</h1>
            <nav class="breadcrumbs">
                <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Home</a>
                <span class="breadcrumb-item">My Borrowed Items</span>
            </nav>
        </div>
        <div>
            <a href="<?php echo BASE_URL; ?>/borrow/create" class="btn btn-primary">
                <i class="fas fa-plus-circle btn-icon"></i> New Request
            </a>
        </div>
    </div>
</div>
<?php if (empty($borrowedItems)): ?>
    <!-- No Items View -->
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="fas fa-box-open"></i>
        </div>
        <h3>No Borrowed Items</h3>
        <p>You currently don't have any borrowed items. Browse our inventory to request items.</p>
        <a href="<?php echo BASE_URL; ?>/items/browse" class="btn btn-primary">
            <i class="fas fa-search btn-icon"></i> Browse Inventory
        </a>
    </div>
<?php else: ?>
    <!-- Borrowed Items -->
    <div class="panel" style="margin-bottom: 1.5rem;">
        <div class="panel-header">
            <div class="panel-title">Currently Borrowed Items</div>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Request ID</th>
                            <th>Borrowed On</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($borrowedItems as $item): ?>
                            <!-- <?php
                                    // Determine if item is overdue
                                    // $dueDate = new DateTime($item['return_date']);
                                    // $today = new DateTime();
                                    // $isOverdue = $dueDate < $today && $item['status'] === 'checked_out';

                                    // // Set row class for overdue items
                                    // $rowClass = $isOverdue ? 'class="overdue-row"' : '';

                                    // // Determine status badge class
                                    // $statusBadgeClass = 'badge-purple';
                                    // $statusText = 'Checked Out';

                                    // if ($isOverdue) {
                                    //     $statusBadgeClass = 'badge-orange';
                                    //     $statusText = 'Overdue';
                                    // } elseif ($item['status'] === 'approved') {
                                    //     $statusBadgeClass = 'badge-blue';
                                    //     $statusText = 'Approved';
                                    // }
                                    ?> -->
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center;">
                                        <?php
                                        // // Determine icon based on category
                                        // $iconClass = 'box';

                                        // if (!empty($item['category_name'])) {
                                        //     if (stripos($item['category_name'], 'computer') !== false || stripos($item['item_name'], 'laptop') !== false) {
                                        //         $iconClass = 'laptop';
                                        //     } elseif (stripos($item['category_name'], 'audio') !== false) {
                                        //         $iconClass = 'headphones';
                                        //     } elseif (stripos($item['category_name'], 'video') !== false || stripos($item['item_name'], 'projector') !== false) {
                                        //         $iconClass = 'video';
                                        //     } elseif (stripos($item['category_name'], 'camera') !== false || stripos($item['category_name'], 'photo') !== false) {
                                        //         $iconClass = 'camera';
                                        //     } elseif (stripos($item['item_name'], 'tablet') !== false || stripos($item['item_name'], 'ipad') !== false) {
                                        //         $iconClass = 'tablet-alt';
                                        //     } elseif (stripos($item['item_name'], 'microphone') !== false || stripos($item['item_name'], 'mic') !== false) {
                                        //         $iconClass = 'microphone';
                                        //     }
                                        // }
                                        ?>
                                        <i class="fas fa-<?php echo $iconClass; ?>" style="margin-right: 10px; color: var(--gray-500);"></i>
                                        <div>
                                            <div style="font-weight: 500;"><?php echo htmlspecialchars($item['name']); ?></div>
                                            <div style="font-size: 0.8rem; color: var(--gray-500);">Asset #<?php echo htmlspecialchars($item['id'] ?? 'N/A'); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($item['request_id']); ?>

                                </td>
                                <td><?php echo UtilityHelper::formatDateForDisplay($item['borrow_date']); ?></td>
                                <td>
                                    <?php echo UtilityHelper::formatDateForDisplay($item['due_date']); ?>

                                </td>
                                <td>
                                    <span class="badge <?php echo $statusBadgeClass; ?>">
                                        <?php echo $item['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (strtolower(trim($item['status'])) !== 'returned'): ?>
                                        <?php if ($currentUser['role'] !== 'student'): ?>
                                            <!-- Active Return button for non-students -->
                                            <a href="<?php echo BASE_URL; ?>/borrow/return?id=<?php echo urlencode($item['id']); ?>"
                                                style="margin-left: auto; padding: 6px 12px; font-size: 0.85rem; background-color: #007bff; color: white; border: none; border-radius: 4px; text-decoration: none;">
                                                Return
                                            </a>
                                        <?php else: ?>
                                            <!-- Disabled Return button for students -->
                                            <a href="javascript:void(0);"
                                                style="margin-left: auto; padding: 6px 12px; font-size: 0.85rem; background-color: #aaa; color: white; border: none; border-radius: 4px; text-decoration: none; cursor: not-allowed;"
                                                title="Students cannot return items directly">
                                                Return
                                            </a>
                                        <?php endif; ?>

                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline" disabled>Returned</button>
                                    <?php endif; ?>
                                </td>


                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Return Instructions -->
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">Return Information</div>
        </div>
        <div class="panel-body">
            <div style="display: flex; flex-wrap: wrap; gap: 1.5rem;">
                <div style="flex: 1; min-width: 250px;">
                    <div style="font-weight: 600; margin-bottom: 0.75rem;">How to Return Items</div>
                    <ol style="padding-left: 1.5rem; margin-bottom: 0;">
                        <li>Click the "Return" button next to the item you want to return.</li>
                        <li>Complete the return form, indicating the condition of each item.</li>
                        <li>Submit the form to complete your return.</li>
                        <li>Receive a return receipt for your records.</li>
                    </ol>
                </div>
                <div style="flex: 1; min-width: 250px;">
                    <div style="font-weight: 600; margin-bottom: 0.75rem;">Return Policies</div>
                    <ul style="padding-left: 1.5rem; margin-bottom: 0;">
                        <li>Items must be returned by the due date to avoid penalties.</li>
                        <li>All components and accessories must be returned together.</li>
                        <li>Items should be in the same condition as when borrowed.</li>
                        <li>Report any damage or issues immediately.</li>
                    </ul>
                </div>
                <div style="flex: 1; min-width: 250px;">
                    <div style="font-weight: 600; margin-bottom: 0.75rem;">Return Locations</div>
                    <div>
                        <strong>Main Office:</strong> 2nd Floor, Room 215<br>
                        <strong>Hours:</strong> Monday-Friday, 9:00 AM - 5:00 PM<br>
                        <strong>Contact:</strong> inventory@example.com
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background-color: var(--gray-50);
        border-radius: 8px;
        margin-top: 2rem;
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

    .empty-state p {
        color: var(--gray-500);
        margin-bottom: 1.5rem;
    }

    .overdue-row {
        background-color: rgba(var(--danger-rgb), 0.05);
    }

    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
        }
    }
</style>

<?php
// Include footer
include 'includes/footer.php';
?>