<?php

$conn = getDbConnection();


/**
 * Borrow Requests Management
 * 
 * This file shows all borrow requests for admin/manager approval
 */

// Set page title
$pageTitle = 'Manage Borrow Requests';

// Check if user is logged in and has the right permissions
// Check if user is logged in
if (!AuthHelper::isAuthenticated()) {
    setFlashMessage('Please log in to continue.', 'danger');
    redirect(BASE_URL . '/login');
    exit;
}




// Get current user
$currentUser = AuthHelper::getCurrentUser();

// Get page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$borroweditemsql = "SELECT bi.id,u.name as username, i.name as itemname,bi.quantity,bi.borrow_date,bi.due_date,bi.return_date, bi.status 
FROM borrowed_item bi join requests r join item i join users u
where bi.request_id = r.id and i.id = r.item_id and  r.user_id = u.id
";

$borrowedstmt2 = $conn->prepare($borroweditemsql);
$borrowedstmt2->execute();
$borrowedItems2 = $borrowedstmt2->fetchAll();
// Build filters
$filters = [
  'search' => isset($_GET['search']) ? cleanInput($_GET['search']) : '',
  'status' => isset($_GET['status']) ? cleanInput($_GET['status']) : '',
  'department_id' => isset($_GET['department']) ? (int)$_GET['department'] : '',
  'date_from' => isset($_GET['date_from']) ? cleanInput($_GET['date_from']) : '',
  'date_to' => isset($_GET['date_to']) ? cleanInput($_GET['date_to']) : ''
];

// Get all borrow requests with pagination
$requestsData = BorrowHelper::getAllBorrowRequests($page, ITEMS_PER_PAGE, $filters);
$requests = $requestsData['requests'];


// Get departments for filter dropdown


// Process approve/reject if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Validate CSRF token
  validateCsrfToken();

  $requestId = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
  $action = isset($_POST['action']) ? cleanInput($_POST['action']) : '';

  if (!$requestId || !in_array($action, ['approve', 'reject', 'checkout', 'return'])) {
    setFlashMessage('Invalid request or action', 'danger');
    redirect(BASE_URL . '/borrow/requests');
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

  // Redirect to maintain the same filters
  $redirect = BASE_URL . '/borrow/requests?page=' . $page;
  foreach ($filters as $key => $value) {
    if (!empty($value)) {
      $redirect .= '&' . $key . '=' . urlencode($value);
    }
  }

  redirect($redirect);
  exit;
}

// Get counts for dashboard
$pendingCount = 0;
$approvedCount = 0;
$checkedOutCount = 0;
$returnedCount = 0;

// Simple query to count requests by status
$conn = getDbConnection();
$statusCountSql = "SELECT status, COUNT(*) as count FROM requests GROUP BY status";
$statusCountStmt = $conn->prepare($statusCountSql);
$statusCountStmt->execute();
$statusCounts = $statusCountStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$pendingCount = $statusCounts['pending'] ?? 0;
$approvedCount = $statusCounts['approved'] ?? 0;
$checkedOutCount = ($statusCounts['checked_out'] ?? 0) + ($statusCounts['overdue'] ?? 0) + ($statusCounts['partially_returned'] ?? 0);
$returnedCount = $statusCounts['returned'] ?? 0;

// Include header
include 'includes/header.php';
?>
<style>
  /* Colorful Icons Enhancement for Borrow Requests Page */

  /* Status Badge Colors with Improved Contrast */
  .badge {
    display: inline-flex;
    align-items: center;
    padding: 0.4rem 0.8rem;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1;
    border-radius: 20px;
    white-space: nowrap;
  }
 .table-responsive {
    max-height: 400px; /* Set a fixed height, adjust this value as needed */
    overflow-y: auto;  /* Enable vertical scrolling */
}

  .badge i {
    margin-right: 0.4rem;
    font-size: 0.85em;
  }

  .badge-blue {
    background-color: #e6f2ff;
    color: #0066cc;
  }

  .badge-green {
    background-color: #e6fff0;
    color: #00994d;
  }

  .badge-red {
    background-color: #ffe6e6;
    color: #cc0000;
  }

  .badge-gray {
    background-color: #f2f2f2;
    color: #666666;
  }

  .badge-purple {
    background-color: #f0e6ff;
    color: #6600cc;
  }

  .badge-orange {
    background-color: #fff2e6;
    color: #cc6600;
  }

  .badge-yellow {
    background-color: #fffde6;
    color: #cc9900;
  }

  /* Enhanced Stats Cards with Gradient Icons */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
  }

  .stats-card {
    background-color: white;
    border-radius: 12px;
    padding: 1.75rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
  }

  .stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
  }

  .stats-card-content {
    flex: 1;
  }

  .stats-card-value {
    font-size: 2.25rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    background: linear-gradient(45deg, #333 30%, #666 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1.1;
  }

  .stats-card-label {
    color: var(--gray-600);
    font-size: 0.95rem;
    font-weight: 500;
  }

  .stats-card-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    transition: transform 0.3s ease;
  }

  .stats-card:hover .stats-card-icon {
    transform: rotate(10deg);
  }

  /* Colorful icon styles */
  .icon-pending {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    box-shadow: 0 4px 10px rgba(79, 172, 254, 0.3);
  }

  .icon-approved {
    background: linear-gradient(135deg, #0ba360 0%, #3cba92 100%);
    color: white;
    box-shadow: 0 4px 10px rgba(11, 163, 96, 0.3);
  }

  .icon-borrowed {
    background: linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%);
    color: white;
    box-shadow: 0 4px 10px rgba(142, 45, 226, 0.3);
  }

  .icon-returned {
    background: linear-gradient(135deg, #606c88 0%, #3f4c6b 100%);
    color: white;
    box-shadow: 0 4px 10px rgba(96, 108, 136, 0.3);
  }

  /* Table Action Buttons with Animation */
  .action-buttons {
    display: flex;
    gap: 0.5rem;
  }

  .action-buttons .btn {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
  }

  .action-buttons .btn:hover {
    transform: translateY(-2px);
  }

  .action-buttons .btn::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    transform: scale(0) translate(-50%, -50%);
    transform-origin: left top;
    opacity: 0;
  }

  .action-buttons .btn:active::after {
    animation: ripple 0.6s ease-out;
  }

  @keyframes ripple {
    0% {
      transform: scale(0) translate(-50%, -50%);
      opacity: 0.5;
    }

    100% {
      transform: scale(10) translate(-50%, -50%);
      opacity: 0;
    }
  }

  .btn-view {
    background-color: #f2f2f2;
    color: #555;
  }

  .btn-view:hover {
    background-color: #e6e6e6;
    color: #333;
  }

  .btn-approve {
    background: linear-gradient(135deg, #0ba360 0%, #3cba92 100%);
    color: white;
    border: none;
  }

  .btn-approve:hover {
    box-shadow: 0 4px 10px rgba(11, 163, 96, 0.3);
  }

  .btn-reject {
    background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
    color: white;
    border: none;
  }

  .btn-reject:hover {
    box-shadow: 0 4px 10px rgba(255, 65, 108, 0.3);
  }

  .btn-checkout {
    background: linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%);
    color: white;
    border: none;
  }

  .btn-checkout:hover {
    box-shadow: 0 4px 10px rgba(142, 45, 226, 0.3);
  }

  .btn-return {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    border: none;
  }

  .btn-return:hover {
    box-shadow: 0 4px 10px rgba(79, 172, 254, 0.3);
  }

  /* Filter Panel Enhancement */
  .filter-panel {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    margin-bottom: 2rem;
    overflow: hidden;
    border: 1px solid rgba(0, 0, 0, 0.05);
  }

  .filter-header {
    background: linear-gradient(to right, #f8f9fa, #ffffff);
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .filter-title {
    font-weight: 600;
    font-size: 1.1rem;
    color: #343a40;
    display: flex;
    align-items: center;
  }

  .filter-title i {
    margin-right: 0.75rem;
    color: #6c63ff;
  }

  /* Empty State Enhancement */
  .empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background-color: #f8f9fa;
    border-radius: 12px;
    border: 1px dashed #dee2e6;
  }

  .empty-state-icon {
    font-size: 4rem;
    margin-bottom: 1.5rem;
    position: relative;
    display: inline-block;
  }

  .empty-state-icon i {
    background: linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .empty-state h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: #343a40;
  }

  .empty-state p {
    color: #6c757d;
    margin-bottom: 2rem;
    font-size: 1.05rem;
    max-width: 450px;
    margin-left: auto;
    margin-right: auto;
  }

  /* Table Enhancements */
  .table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
  }

  .table th {
    background-color: #f8f9fa;
    color: #495057;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
    padding: 1rem;
    border-bottom: 2px solid #dee2e6;
  }

  .table td {
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
  }

  .table tr:hover {
    background-color: #f8f9fa;
  }

  .table-request-id {
    font-weight: 600;
    color: #6c63ff;
    text-decoration: none;
    display: flex;
    align-items: center;
  }

  .table-request-id i {
    margin-right: 0.5rem;
    font-size: 0.9rem;
  }

  .table-request-id:hover {
    text-decoration: underline;
  }

  /* Status Badge Icons */
  .badge-pending {
    display: flex;
    align-items: center;
  }

  .badge-pending i {
    color: #0066cc;
  }

  .badge-approved i {
    color: #00994d;
  }

  .badge-rejected i {
    color: #cc0000;
  }

  .badge-cancelled i {
    color: #666666;
  }

  .badge-checked-out i {
    color: #6600cc;
  }

  .badge-overdue i {
    color: #cc6600;
  }

  .badge-partially-returned i {
    color: #cc9900;
  }

  .badge-returned i {
    color: #00994d;
  }

  /* Modal Enhancements */
  .modal {
    background-color: white;
    border-radius: 12px;
    width: 100%;
    max-width: 550px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    overflow: hidden;
  }

  .modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(to right, #f8f9fa, #ffffff);
  }

  .modal-title {
    font-weight: 600;
    font-size: 1.25rem;
    color: #343a40;
    display: flex;
    align-items: center;
  }

  .modal-title.approve-title {
    color: #00994d;
  }

  .modal-title.reject-title {
    color: #cc0000;
  }

  .modal-title.checkout-title {
    color: #6600cc;
  }

  .modal-title i {
    margin-right: 0.75rem;
    font-size: 1.2rem;
  }

  .modal-close {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: #f8f9fa;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #6c757d;
    transition: all 0.2s ease;
  }

  .modal-close:hover {
    background-color: #e9ecef;
    color: #343a40;
  }

  .modal-body {
    padding: 1.5rem;
  }

  .modal-footer {
    padding: 1.25rem 1.5rem;
    border-top: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 1rem;
    background-color: #f8f9fa;
  }

  /* Responsive Adjustments */
  @media (max-width: 768px) {
    .stats-grid {
      grid-template-columns: 1fr;
    }

    .stats-card {
      padding: 1.25rem;
    }

    .stats-card-value {
      font-size: 1.75rem;
    }

    .stats-card-icon {
      width: 50px;
      height: 50px;
      font-size: 1.4rem;
    }

    .table th {
      display: none;
    }

    .table td {
      display: block;
      width: 100%;
      text-align: right;
      padding: 0.75rem 1rem;
      position: relative;
      padding-left: 50%;
    }

    .table td:before {
      content: attr(data-label);
      position: absolute;
      left: 1rem;
      width: 45%;
      padding-right: 10px;
      white-space: nowrap;
      font-weight: 600;
      text-align: left;
    }

    .table tr {
      display: block;
      margin-bottom: 1rem;
      border: 1px solid #e9ecef;
      border-radius: 8px;
    }

    .action-buttons {
      justify-content: flex-end;
    }
  }
</style>

<div class="content-header">
  <div style="display: flex; justify-content: space-between; align-items: center;">
    <div>
      <h1 class="page-title">Borrow Requests</h1>
      <nav class="breadcrumbs">
        <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Home</a>
        <span class="breadcrumb-item">Borrow Requests</span>
      </nav>
    </div>
  </div>
</div>

<!-- Stats Cards with Colorful Icons -->
<div class="stats-grid">
  <div class="stats-card">
    <div class="stats-card-content">
      <div class="stats-card-value"><?php echo $pendingCount; ?></div>
      <div class="stats-card-label">Pending Requests</div>
    </div>
    <div class="stats-card-icon icon-pending">
      <i class="fas fa-hourglass-half"></i>
    </div>
  </div>

  <div class="stats-card">
    <div class="stats-card-content">
      <div class="stats-card-value"><?php echo $approvedCount; ?></div>
      <div class="stats-card-label">Approved Requests</div>
    </div>
    <div class="stats-card-icon icon-approved">
      <i class="fas fa-check-circle"></i>
    </div>
  </div>

  <div class="stats-card">
    <div class="stats-card-content">
      <div class="stats-card-value"><?php echo $checkedOutCount; ?></div>
      <div class="stats-card-label">Checked Out Items</div>
    </div>
    <div class="stats-card-icon icon-borrowed">
      <i class="fas fa-hand-holding"></i>
    </div>
  </div>

  <div class="stats-card">
    <div class="stats-card-content">
      <div class="stats-card-value"><?php echo $returnedCount; ?></div>
      <div class="stats-card-label">Returned Items</div>
    </div>
    <div class="stats-card-icon icon-returned">
      <i class="fas fa-undo-alt"></i>
    </div>
  </div>
</div>

<!-- Filters Panel -->
<div class="panel filter-panel">
  <div class="panel-header filter-header">
    <div class="panel-title filter-title">
      Filters
    </div>
    <a href="<?php echo BASE_URL; ?>/borrow/requests" class="btn btn-sm btn-outline">
      <i class="fas fa-redo-alt btn-icon"></i>
      Reset
    </a>
  </div>
  <div class="panel-body">
    <form action="<?php echo BASE_URL; ?>/borrow/requests" method="GET" id="filter-form">
      <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
        <div style="flex: 1; min-width: 200px;">
          <label class="form-label">Status</label>
          <select class="form-control" name="status" id="status-filter">
            <option value="">All Statuses</option>
            <option value="pending" <?php echo ($filters['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
            <option value="approved" <?php echo ($filters['status'] == 'approved') ? 'selected' : ''; ?>>Approved</option>
            <option value="rejected" <?php echo ($filters['status'] == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
            <option value="checked_out" <?php echo ($filters['status'] == 'checked_out') ? 'selected' : ''; ?>>Checked Out</option>
            <option value="overdue" <?php echo ($filters['status'] == 'overdue') ? 'selected' : ''; ?>>Overdue</option>
            <option value="returned" <?php echo ($filters['status'] == 'returned') ? 'selected' : ''; ?>>Returned</option>
          </select>
        </div>
        <div style="flex: 1; min-width: 200px;">
          <label class="form-label">Department</label>
          <select class="form-control" name="department" id="department-filter">
            <option value="">All Departments</option>
            <?php ?>
            <option value="" <?php echo ($filters['department_id'] == $department['id']) ? 'selected' : ''; ?>>

            </option>
            <?php  ?>
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
      </div>
      <div style="display: flex; align-items: center; margin-top: 1rem; gap: 1rem;">
        <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" placeholder="Search by ID, requester, or purpose..." style="flex: 1;">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-search btn-icon"></i> Apply Filters
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Requests List -->
<div class="panel">
  <div class="panel-header">
    <div class="panel-title">Borrowed Items</div>
    <div>
      <button class="btn btn-sm btn-outline" id="export-btn">
        <i class="fas fa-file-export btn-icon"></i> Export
      </button>
    </div>
  </div>
  <div class="panel-body">
    <?php if (count($borrowedItems2) > 0): ?>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Borrowed ID</th>
              <th>User Name</th>
              <th>Item Name</th>
              <th>Quantity</th>
              <th>Borrow Date</th>
              <th>Due Date</th>
              <th>Return Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($borrowedItems2 as $borrowedItem2): ?>
              <tr>
                <td data-label="Borrowed ID">
                  <?php echo $borrowedItem2['id'] ?>
                </td>
                <td data-label="User Name"><?php echo htmlspecialchars($borrowedItem2['username']); ?></td>
                <td data-label="Item Name"><?php echo htmlspecialchars($borrowedItem2['itemname']); ?></td>
                <td data-label="Quantity"><?php echo htmlspecialchars($borrowedItem2['quantity']); ?></td>

                <td data-label="Borrow Date"><?php echo UtilityHelper::formatDateForDisplay($borrowedItem2['borrow_date'], 'short'); ?></td>
                <td data-label="Due Date"><?php echo UtilityHelper::formatDateForDisplay($borrowedItem2['due_date'], 'short'); ?></td>
                <td data-label="Return Date"><?php echo UtilityHelper::formatDateForDisplay($borrowedItem2['return_date'], 'short'); ?></td>

                <td data-label="Status">
                  <?php
                  $badgeClass = 'badge-blue';
                  $statusIcon = 'fas fa-clock';

                  switch ($borrowedItem2['status']) {
                    case 'borrowed':
                      $badgeClass = 'badge-green';
                      $statusIcon = 'fas fa-clock';
                      break;
                    case 'approved':
                      $badgeClass = 'badge-green';
                      $statusIcon = 'fas fa-check-circle';
                      break;
                    case 'rejected':
                      $badgeClass = 'badge-red';
                      $statusIcon = 'fas fa-times-circle';
                      break;
                    case 'cancelled':
                      $badgeClass = 'badge-gray';
                      $statusIcon = 'fas fa-ban';
                      break;
                  }

                  $statusClass = 'badge-' . str_replace('_', '-', $borrowedItem2['status']);
                  ?>
                  <span class="badge <?php echo $badgeClass; ?> <?php echo $statusClass; ?>">
                    <i class="<?php echo $statusIcon; ?>"></i>
                    <?php echo ucfirst(str_replace('_', ' ', $borrowedItem2['status'])); ?>
                  </span>
                </td>
                <td data-label="Actions">
                  <div class="action-buttons">
                    <!-- Always show View Details -->
                    <a href="<?php echo BASE_URL; ?>/borrow/view?id=<?php echo $borrowedItem2['id']; ?>" class="btn btn-sm btn-view" title="View Details">
                      <i class="fas fa-eye"></i>
                    </a>

                    <!-- Show Return only if item is checked out or overdue -->
                    <?php if ($borrowedItem2['status'] === 'borrowed' || $borrowedItem2['status'] === 'overdue'): ?>
                      <a href="<?php echo BASE_URL; ?>/borrow/return?id=<?php echo $borrowedItem2['id']; ?>" class="btn btn-sm btn-return" title="Return Items">
                        <i class="fas fa-undo-alt"></i>
                      </a>
                    <?php endif; ?>
                  </div>
                </td>

              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <?php else: ?>
      <div class="empty-state">
        <div class="empty-state-icon">
          <i class="fas fa-inbox"></i>
        </div>
        <h3>No Requests Found</h3>
        <p>There are no borrow requests matching your filters. Try adjusting your search criteria.</p>
        <a href="<?php echo BASE_URL; ?>/borrow/requests" class="btn btn-primary">
          <i class="fas fa-redo-alt btn-icon"></i> Reset Filters
        </a>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Process Request Modal (Approve/Reject/Checkout) -->
<div class="modal-backdrop" id="process-modal" style="display: none;">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="process-modal-title">Process Request</div>
      <button class="modal-close">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="modal-body">
      <form id="process-form" method="POST" action="<?php echo BASE_URL; ?>/borrow/requests">
        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
        <input type="hidden" name="request_id" id="process-request-id" value="">
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

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filters change
    Filter = document.getElementById('status-filter');
    const departmentFilter = document.getElementById('department-filter');

    if (statusFilter) {
      statusFilter.addEventListener('change', function() {
        document.getElementById('filter-form').submit();
      });
    }

    if (departmentFilter) {
      departmentFilter.addEventListener('change', function() {
        document.getElementById('filter-form').submit();
      });
    }

    // Process request modal
    const processModal = document.getElementById('process-modal');
    const modalTitle = document.getElementById('process-modal-title');
    const modalClose = processModal.querySelector('.modal-close');
    const cancelBtn = document.getElementById('cancel-process-btn');
    const processForm = document.getElementById('process-form');
    const processRequestId = document.getElementById('process-request-id');
    const processAction = document.getElementById('process-action');
    const rejectFields = document.getElementById('reject-fields');
    const approveFields = document.getElementById('approve-fields');
    const checkoutFields = document.getElementById('checkout-fields');
    const confirmBtn = document.getElementById('confirm-process-btn');

    // Handle process button clicks
    const processBtns = document.querySelectorAll('.process-btn');

    processBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const requestId = this.getAttribute('data-id');
        const action = this.getAttribute('data-action');

        // Set form values
        processRequestId.value = requestId;
        processAction.value = action;

        // Update modal title and visible fields
        switch (action) {
          case 'approve':
            modalTitle.textContent = 'Approve Request';
            modalTitle.className = 'modal-title approve-title';
            modalTitle.innerHTML = '<i class="fas fa-check-circle"></i> Approve Request';
            rejectFields.style.display = 'none';
            approveFields.style.display = 'block';
            checkoutFields.style.display = 'none';
            confirmBtn.className = 'btn btn-approve';
            confirmBtn.textContent = 'Approve Request';
            break;
          case 'reject':
            modalTitle.textContent = 'Reject Request';
            modalTitle.className = 'modal-title reject-title';
            modalTitle.innerHTML = '<i class="fas fa-times-circle"></i> Reject Request';
            rejectFields.style.display = 'block';
            approveFields.style.display = 'none';
            checkoutFields.style.display = 'none';
            confirmBtn.className = 'btn btn-reject';
            confirmBtn.textContent = 'Reject Request';
            break;
          case 'checkout':
            modalTitle.textContent = 'Check Out Items';
            modalTitle.className = 'modal-title checkout-title';
            modalTitle.innerHTML = '<i class="fas fa-hand-holding"></i> Check Out Items';
            rejectFields.style.display = 'none';
            approveFields.style.display = 'none';
            checkoutFields.style.display = 'block';
            confirmBtn.className = 'btn btn-checkout';
            confirmBtn.textContent = 'Confirm Check Out';
            break;
        }

        // Show modal
        processModal.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Prevent scrolling
      });
    });

    // Submit the form when the confirm button is clicked
    confirmBtn.addEventListener('click', function() {
      processForm.submit();
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

    // Export functionality
    const exportBtn = document.getElementById('export-btn');

    if (exportBtn) {
      exportBtn.addEventListener('click', function() {
        // Get current filters
        const status = statusFilter ? statusFilter.value : '';
        const department = departmentFilter ? departmentFilter.value : '';
        const dateFrom = document.querySelector('input[name="date_from"]') ? document.querySelector('input[name="date_from"]').value : '';
        const dateTo = document.querySelector('input[name="date_to"]') ? document.querySelector('input[name="date_to"]').value : '';
        const search = document.querySelector('input[name="search"]') ? document.querySelector('input[name="search"]').value : '';

        // Redirect to export endpoint with current filters
        window.location.href = `${BASE_URL}/api/borrow/export?status=${status}&department=${department}&date_from=${dateFrom}&date_to=${dateTo}&search=${encodeURIComponent(search)}`;
      });
    }

    // Add some visual feedback on hover for the stats cards
    const statsCards = document.querySelectorAll('.stats-card');
    statsCards.forEach(card => {
      card.addEventListener('mouseenter', function() {
        const icon = this.querySelector('.stats-card-icon i');
        icon.classList.add('fa-beat');

        setTimeout(() => {
          icon.classList.remove('fa-beat');
        }, 1000);
      });
    });
  });
</script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<?php include 'includes/footer.php'; ?>