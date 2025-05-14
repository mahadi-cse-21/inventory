<?php

/**
 * Borrow Request History View
 * 
 * This file displays the user's borrow request history
 */

// Set page title
$pageTitle = 'Borrow Request History';

// Check if user is logged in
if (!AuthHelper::isAuthenticated()) {
    setFlashMessage('You must be logged in to view your borrow history.', 'danger');
    redirect(BASE_URL . '/auth/login');
}

// Get current user
$currentUser = AuthHelper::getCurrentUser();

// Get page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Build filters
$filters = [
    'user_id' => $currentUser['id'],
    'search' => isset($_GET['search']) ? cleanInput($_GET['search']) : '',
    'status' => isset($_GET['status']) ? cleanInput($_GET['status']) : '',
    'date_from' => isset($_GET['date_from']) ? cleanInput($_GET['date_from']) : '',
    'date_to' => isset($_GET['date_to']) ? cleanInput($_GET['date_to']) : ''
];

// Get user's borrow requests with pagination
$requestsData = BorrowHelper::getAllBorrowRequests($page, ITEMS_PER_PAGE, $filters);
$requests = $requestsData['requests'];
$pagination = $requestsData['pagination'];

// Include header
include 'includes/header.php';
?>

<div class="content-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Borrow Request History</h1>
            <nav class="breadcrumbs">
                <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Home</a>
                <span class="breadcrumb-item">Borrow History</span>
            </nav>
        </div>
        <div>
            <a href="<?php echo BASE_URL; ?>/borrow/create" class="btn btn-primary">
                <i class="fas fa-plus-circle btn-icon"></i> New Request
            </a>
        </div>
    </div>
</div>

<!-- Filters Panel -->
<div class="panel" style="margin-bottom: 1.5rem;">
    <div class="panel-header">
        <div class="panel-title">Filters</div>
        <a href="<?php echo BASE_URL; ?>/borrow/history" class="btn btn-sm btn-outline">
            <i class="fas fa-redo-alt btn-icon"></i>
            Reset
        </a>
    </div>
    <div class="panel-body">
        <form action="<?php echo BASE_URL; ?>/borrow/history" method="GET" id="filter-form">
            <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status" id="status-filter">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo ($filters['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo ($filters['status'] == 'approved') ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo ($filters['status'] == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                        <option value="checked_out" <?php echo ($filters['status'] == 'checked_out') ? 'selected' : ''; ?>>Checked Out</option>
                        <option value="returned" <?php echo ($filters['status'] == 'returned') ? 'selected' : ''; ?>>Returned</option>
                        <option value="overdue" <?php echo ($filters['status'] == 'overdue') ? 'selected' : ''; ?>>Overdue</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="date_from" value="<?php echo $filters['date_from']; ?>">
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="date_to" value="<?php echo $filters['date_to']; ?>">
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" placeholder="Search by ID, purpose...">
                </div>
            </div>
            <div style="margin-top: 1rem; text-align: right;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search btn-icon"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Requests List -->
<?php if (count($requests) > 0): ?>
    <div class="panel">
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Item</th>
                            <th>Request Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td>
                                    <span style="font-weight: 500; color: var(--primary);"><?php echo htmlspecialchars($request['id']); ?></span>
                                </td>
                                <td><?php echo $request['item']; ?></td>
                                <td>
                                    <?php echo UtilityHelper::formatDateForDisplay($request['request_date'], 'short'); ?>
                                </td>
                                <td>
                                    <?php
                                    $badgeClass = 'badge-blue';

                                    switch ($request['status']) {
                                        case 'pending':
                                            $badgeClass = 'badge-blue';
                                            break;
                                        case 'approved':
                                            $badgeClass = 'badge-green';
                                            break;
                                        case 'rejected':
                                            $badgeClass = 'badge-red';
                                            break;
                                        case 'cancelled':
                                            $badgeClass = 'badge-gray';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <!-- Approve button -->
                                        <a href="<?php echo BASE_URL; ?>/borrow/approve?id=<?php echo $request['id']; ?>"
                                            class="btn btn-sm btn-success"
                                            onclick="return confirm('Are you sure you want to approve this request?');">
                                            <i class="fas fa-check"></i>
                                        </a>

                                        <!-- Cancel button -->
                                        <a href="<?php echo BASE_URL; ?>/borrow/cancel?id=<?php echo $request['id']; ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to cancel this request?');">
                                            <i class="fas fa-times"></i>
                                        </a>

                                    <?php endif; ?>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['totalPages'] > 1): ?>
            <div class="panel-footer">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="pagination-info">
                        Showing <?php echo $pagination['totalItems'] > 0 ? ($pagination['offset'] + 1) : 0; ?>-<?php echo min($pagination['offset'] + $pagination['itemsPerPage'], $pagination['totalItems']); ?> of <?php echo $pagination['totalItems']; ?> requests
                    </div>
                    <div class="pagination">
                        <?php if ($pagination['currentPage'] > 1): ?>
                            <a href="<?php echo BASE_URL; ?>/borrow/history?page=<?php echo ($pagination['currentPage'] - 1); ?>&status=<?php echo $filters['status']; ?>&date_from=<?php echo $filters['date_from']; ?>&date_to=<?php echo $filters['date_to']; ?>&search=<?php echo urlencode($filters['search']); ?>" class="btn btn-sm btn-outline">Previous</a>
                        <?php else: ?>
                            <button class="btn btn-sm btn-outline" disabled>Previous</button>
                        <?php endif; ?>

                        <?php
                        // Calculate page range to display
                        $startPage = max(1, $pagination['currentPage'] - 2);
                        $endPage = min($pagination['totalPages'], $pagination['currentPage'] + 2);

                        // Always show first page
                        if ($startPage > 1) {
                            echo '<a href="' . BASE_URL . '/borrow/history?page=1&status=' . $filters['status'] . '&date_from=' . $filters['date_from'] . '&date_to=' . $filters['date_to'] . '&search=' . urlencode($filters['search']) . '" class="btn btn-sm btn-outline">1</a>';
                            if ($startPage > 2) {
                                echo '<span style="margin: 0 0.5rem;">...</span>';
                            }
                        }

                        // Display page numbers
                        for ($i = $startPage; $i <= $endPage; $i++) {
                            if ($i == $pagination['currentPage']) {
                                echo '<button class="btn btn-sm btn-primary">' . $i . '</button>';
                            } else {
                                echo '<a href="' . BASE_URL . '/borrow/history?page=' . $i . '&status=' . $filters['status'] . '&date_from=' . $filters['date_from'] . '&date_to=' . $filters['date_to'] . '&search=' . urlencode($filters['search']) . '" class="btn btn-sm btn-outline">' . $i . '</a>';
                            }
                        }

                        // Always show last page
                        if ($endPage < $pagination['totalPages']) {
                            if ($endPage < $pagination['totalPages'] - 1) {
                                echo '<span style="margin: 0 0.5rem;">...</span>';
                            }
                            echo '<a href="' . BASE_URL . '/borrow/history?page=' . $pagination['totalPages'] . '&status=' . $filters['status'] . '&date_from=' . $filters['date_from'] . '&date_to=' . $filters['date_to'] . '&search=' . urlencode($filters['search']) . '" class="btn btn-sm btn-outline">' . $pagination['totalPages'] . '</a>';
                        }
                        ?>

                        <?php if ($pagination['currentPage'] < $pagination['totalPages']): ?>
                            <a href="<?php echo BASE_URL; ?>/borrow/history?page=<?php echo ($pagination['currentPage'] + 1); ?>&status=<?php echo $filters['status']; ?>&date_from=<?php echo $filters['date_from']; ?>&date_to=<?php echo $filters['date_to']; ?>&search=<?php echo urlencode($filters['search']); ?>" class="btn btn-sm btn-outline">Next</a>
                        <?php else: ?>
                            <button class="btn btn-sm btn-outline" disabled>Next</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="fas fa-file-alt"></i>
        </div>
        <h3>No Borrow Requests Found</h3>
        <p>You haven't made any borrow requests yet or none match your filters.</p>
        <a href="<?php echo BASE_URL; ?>/borrow/create" class="btn btn-primary">
            <i class="fas fa-plus-circle btn-icon"></i> Create New Request
        </a>
    </div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit form when filters change
        const statusFilter = document.getElementById('status-filter');

        statusFilter.addEventListener('change', function() {
            document.getElementById('filter-form').submit();
        });
    });
</script>

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
</style>

<?php
// Include footer
include 'includes/footer.php';
?>