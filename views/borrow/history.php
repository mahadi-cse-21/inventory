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
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Requester ID</th>
                            <th>Item</th>
                            <th>Request Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><span style="font-weight: 500; color: var(--primary);"><?php echo htmlspecialchars($request['id']); ?></span></td>
                                <td><span style="font-weight: 500; color: var(--primary);"><?php echo htmlspecialchars($request['user_id']); ?></span></td>
                                <td><?php echo $request['item']; ?></td>
                                <td><?php echo UtilityHelper::formatDateForDisplay($request['request_date'], 'short'); ?></td>
                                <td>
                                    <?php
                                    $badgeClass = 'badge-blue';
                                    switch ($request['status']) {
                                        case 'approved':
                                            $badgeClass = 'badge-green';
                                            break;
                                        case 'rejected':
                                            $badgeClass = 'badge-red';
                                            break;
                                        case 'cancelled':
                                            $badgeClass = 'badge-gray';
                                            break;
                                        case 'completed':
                                            $badgeClass = 'badge-dark';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($request['status'] === 'pending' && $currentUser['role'] !== 'student'): ?>
                                        <a href="<?php echo BASE_URL; ?>/borrow/approve?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to approve this request?');">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/borrow/cancel?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this request?');">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php elseif ($request['status'] === 'approved'): ?>
                                        <?php if ($currentUser['role'] === 'admin'): ?>
                                            <button class="btn btn-sm btn-secondary">
                                                <i class="fas fa-flag-checkered"></i> Approved
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                <i class="fas fa-flag-checkered"></i> Approved
                                            </button>
                                        <?php endif; ?>
                                    <?php elseif ($request['status'] === 'completed'): ?>
                                        <?php if ($currentUser['role'] === 'admin'): ?>
                                            <button class="btn btn-sm btn-secondary">
                                                <i class="fas fa-check-circle"></i> Completed
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                <i class="fas fa-check-circle"></i> Completed
                                            </button>
                                        <?php endif; ?>
                                    <?php elseif ($request['status'] === 'rejected'): ?>
                                        <?php if ($currentUser['role'] === 'admin'): ?>
                                            <button class="btn btn-sm btn-danger">
                                                <i class="fas fa-times-circle"></i> Rejected
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-danger" disabled>
                                                <i class="fas fa-times-circle"></i> Rejected
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-success" disabled>
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" disabled>
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
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
    .table-responsive {
    max-height: 400px; /* Set a fixed height, adjust this value as needed */
    overflow-y: auto;  /* Enable vertical scrolling */
}

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