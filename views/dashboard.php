<?php
/**
 * Dashboard View
 * 
 * This is the main dashboard page that users see after logging in.
 * It displays summary information and quick access to common functions.
 */

// Set page title
$pageTitle = 'Dashboard';

// Get current user
$currentUser = AuthHelper::getCurrentUser();

// Get dashboard statistics
$conn = getDbConnection();

// Count currently borrowed items by the user
$borrowedSql = "SELECT COUNT(*) FROM borrowed_items bi 
               JOIN borrow_requests br ON bi.borrow_request_id = br.id 
               WHERE br.user_id = ? AND br.status = 'checked_out' AND bi.is_returned = 0";
$borrowedStmt = $conn->prepare($borrowedSql);
$borrowedStmt->execute([$currentUser['id']]);
$borrowedCount = (int)$borrowedStmt->fetchColumn();

// Count pending requests by the user
$pendingSql = "SELECT COUNT(*) FROM borrow_requests 
              WHERE user_id = ? AND status = 'pending'";
$pendingStmt = $conn->prepare($pendingSql);
$pendingStmt->execute([$currentUser['id']]);
$pendingCount = (int)$pendingStmt->fetchColumn();

// Count past borrows in the last 90 days
$pastSql = "SELECT COUNT(*) FROM borrow_requests 
           WHERE user_id = ? AND (status = 'returned' OR status = 'checked_out') 
           AND created_at > DATE_SUB(NOW(), INTERVAL 90 DAY)";
$pastStmt = $conn->prepare($pastSql);
$pastStmt->execute([$currentUser['id']]);
$pastCount = (int)$pastStmt->fetchColumn();

// Count upcoming reservations
$reservationSql = "SELECT COUNT(*) FROM reservations 
                  WHERE user_id = ? AND status = 'confirmed' 
                  AND start_date > CURDATE()";
$reservationStmt = $conn->prepare($reservationSql);
$reservationStmt->execute([$currentUser['id']]);
$reservationCount = (int)$reservationStmt->fetchColumn();

// Get currently borrowed items
$activeBorrowsSql = "SELECT bi.*, i.name as item_name, i.asset_id, br.borrow_date, br.return_date, br.status as request_status
                    FROM borrowed_items bi 
                    JOIN items i ON bi.item_id = i.id
                    JOIN borrow_requests br ON bi.borrow_request_id = br.id 
                    WHERE br.user_id = ? AND br.status = 'checked_out' AND bi.is_returned = 0
                    ORDER BY br.return_date ASC
                    LIMIT 5";
$activeBorrowsStmt = $conn->prepare($activeBorrowsSql);
$activeBorrowsStmt->execute([$currentUser['id']]);
$activeBorrows = $activeBorrowsStmt->fetchAll();

// Get pending requests
$pendingRequestsSql = "SELECT br.*, 
                      (SELECT COUNT(*) FROM borrowed_items WHERE borrow_request_id = br.id) as item_count 
                      FROM borrow_requests br
                      WHERE br.user_id = ? AND br.status = 'pending'
                      ORDER BY br.created_at DESC
                      LIMIT 5";
$pendingRequestsStmt = $conn->prepare($pendingRequestsSql);
$pendingRequestsStmt->execute([$currentUser['id']]);
$pendingRequests = $pendingRequestsStmt->fetchAll();

// Get recommended items
$recommendedSql = "SELECT i.*, c.name as category_name
                  FROM items i
                  JOIN categories c ON i.category_id = c.id
                  WHERE i.status = 'available' AND i.is_active = 1
                  ORDER BY RAND()
                  LIMIT 6";
$recommendedStmt = $conn->prepare($recommendedSql);
$recommendedStmt->execute();
$recommendedItems = $recommendedStmt->fetchAll();

// Include header
include 'includes/header.php';
?>

<h1 class="page-title">Dashboard</h1>

<!-- Dashboard Cards -->
<div class="dashboard-cards">
    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <div class="dashboard-card-title">Currently Borrowed</div>
            <div class="dashboard-card-icon">
                <i class="fas fa-hand-holding"></i>
            </div>
        </div>
        <div class="dashboard-card-value"><?php echo $borrowedCount; ?></div>
        <div class="dashboard-card-description">
            <?php if ($borrowedCount > 0): ?>
                <?php 
                $nextDueDate = null;
                $daysUntilDue = 0;
                
                foreach ($activeBorrows as $borrow) {
                    $dueDate = new DateTime($borrow['return_date']);
                    $today = new DateTime();
                    $interval = $today->diff($dueDate);
                    $daysLeft = $interval->days;
                    
                    if ($nextDueDate === null || $dueDate < $nextDueDate) {
                        $nextDueDate = $dueDate;
                        $daysUntilDue = $daysLeft;
                    }
                }
                
                if ($nextDueDate !== null) {
                    echo 'Items due in ' . $daysUntilDue . ' days';
                } else {
                    echo 'Check due dates';
                }
                ?>
            <?php else: ?>
                No items currently borrowed
            <?php endif; ?>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <div class="dashboard-card-title">Pending Requests</div>
            <div class="dashboard-card-icon card-icon-orange">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="dashboard-card-value"><?php echo $pendingCount; ?></div>
        <div class="dashboard-card-description">Awaiting approval</div>
    </div>
    
    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <div class="dashboard-card-title">Past Borrows</div>
            <div class="dashboard-card-icon card-icon-blue">
                <i class="fas fa-history"></i>
            </div>
        </div>
        <div class="dashboard-card-value"><?php echo $pastCount; ?></div>
        <div class="dashboard-card-description">In the last 90 days</div>
    </div>
    
    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <div class="dashboard-card-title">Upcoming Reservations</div>
            <div class="dashboard-card-icon card-icon-green">
                <i class="fas fa-calendar-check"></i>
            </div>
        </div>
        <div class="dashboard-card-value"><?php echo $reservationCount; ?></div>
        <div class="dashboard-card-description">Starting soon</div>
    </div>
</div>

<!-- Currently Borrowed Items -->
<?php if (count($activeBorrows) > 0): ?>
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Currently Borrowed Items</div>
        <a href="<?php echo BASE_URL; ?>/borrow/my-items" class="btn btn-sm btn-outline">
            View All
        </a>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Borrowed On</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activeBorrows as $borrow): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center;">
                                <i class="fas fa-box" style="margin-right: 10px; color: var(--gray-500);"></i>
                                <div>
                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($borrow['item_name']); ?></div>
                                    <div style="font-size: 0.8rem; color: var(--gray-500);">Asset #<?php echo htmlspecialchars($borrow['asset_id']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo UtilityHelper::formatDateForDisplay($borrow['borrow_date'], 'short'); ?></td>
                        <td><?php echo UtilityHelper::formatDateForDisplay($borrow['return_date'], 'short'); ?></td>
                        <td>
                            <?php
                            $dueDate = new DateTime($borrow['return_date']);
                            $today = new DateTime();
                            $daysUntilDue = $today->diff($dueDate)->days;
                            $isPastDue = $today > $dueDate;
                            
                            if ($isPastDue) {
                                echo '<span class="status status-inactive">Overdue</span>';
                            } elseif ($daysUntilDue <= 3) {
                                echo '<span class="status status-pending">Due Soon</span>';
                            } else {
                                echo '<span class="status status-active">Active</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>/borrow/return?item=<?php echo $borrow['id']; ?>" class="btn btn-sm btn-outline">Return</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Pending Requests -->
<?php if (count($pendingRequests) > 0): ?>
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Pending Requests</div>
        <a href="<?php echo BASE_URL; ?>/borrow/history" class="btn btn-sm btn-outline">
            View All
        </a>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Items</th>
                        <th>Requested On</th>
                        <th>Requested Period</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingRequests as $request): ?>
                    <tr>
                        <td>
                            <span style="font-weight: 500; color: var(--primary);"><?php echo htmlspecialchars($request['request_id']); ?></span>
                        </td>
                        <td><?php echo (int)$request['item_count']; ?> item(s)</td>
                        <td><?php echo UtilityHelper::formatDateForDisplay($request['created_at'], 'short'); ?></td>
                        <td><?php echo UtilityHelper::formatDateForDisplay($request['borrow_date'], 'short'); ?> - <?php echo UtilityHelper::formatDateForDisplay($request['return_date'], 'short'); ?></td>
                        <td>
                            <span class="status status-pending">Pending</span>
                        </td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>/borrow/view-request?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-outline">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recommended Items -->
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Recommended Items</div>
        <a href="<?php echo BASE_URL; ?>/items/browse" class="btn btn-sm btn-primary">
            <i class="fas fa-search btn-icon"></i>
            Browse All
        </a>
    </div>
    <div class="panel-body">
        <div class="items-grid">
            <?php foreach ($recommendedItems as $item): ?>
            <div class="item-card">
                <div class="item-card-image">
                    <?php
                    // Display appropriate icon based on category or item type
                    $icon = 'box';
                    $categoryName = strtolower($item['category_name'] ?? '');
                    
                    if (strpos($categoryName, 'laptop') !== false || strpos($categoryName, 'computer') !== false) {
                        $icon = 'laptop';
                    } elseif (strpos($categoryName, 'camera') !== false || strpos($categoryName, 'photo') !== false) {
                        $icon = 'camera';
                    } elseif (strpos($categoryName, 'audio') !== false || strpos($categoryName, 'headphone') !== false) {
                        $icon = 'headphones';
                    } elseif (strpos($categoryName, 'video') !== false || strpos($categoryName, 'projector') !== false) {
                        $icon = 'video';
                    } elseif (strpos($categoryName, 'phone') !== false || strpos($categoryName, 'mobile') !== false) {
                        $icon = 'mobile-alt';
                    } elseif (strpos($categoryName, 'furniture') !== false || strpos($categoryName, 'chair') !== false) {
                        $icon = 'chair';
                    } elseif (strpos($categoryName, 'tablet') !== false || strpos($categoryName, 'ipad') !== false) {
                        $icon = 'tablet-alt';
                    }
                    ?>
                    <i class="fas fa-<?php echo $icon; ?>"></i>
                </div>
                <div class="item-card-body">
                    <div class="item-card-tags">
                        <span class="badge badge-blue"><?php echo htmlspecialchars($item['category_name'] ?? 'Item'); ?></span>
                        <?php if (!empty($item['brand'])): ?>
                        <span class="badge badge-purple"><?php echo htmlspecialchars($item['brand']); ?></span>
                        <?php endif; ?>
                    </div>
                    <h3 class="item-card-title"><?php echo htmlspecialchars($item['name']); ?></h3>
                    <div class="item-card-details">
                        <?php if (!empty($item['brand'])): ?>
                        <div class="item-detail">
                            <span class="item-detail-label">Brand:</span>
                            <span class="item-detail-value"><?php echo htmlspecialchars($item['brand']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($item['model'])): ?>
                        <div class="item-detail">
                            <span class="item-detail-label">Model:</span>
                            <span class="item-detail-value"><?php echo htmlspecialchars($item['model']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php
                        // Get location name
                        $locationName = 'Unknown';
                        if (!empty($item['location_id'])) {
                            $locationSql = "SELECT name FROM locations WHERE id = ?";
                            $locationStmt = $conn->prepare($locationSql);
                            $locationStmt->execute([$item['location_id']]);
                            $locationName = $locationStmt->fetchColumn() ?: 'Unknown';
                        }
                        ?>
                        <div class="item-detail">
                            <span class="item-detail-label">Location:</span>
                            <span class="item-detail-value"><?php echo htmlspecialchars($locationName); ?></span>
                        </div>
                    </div>
                    <div class="item-card-footer">
                        <div class="item-status">
                            <span class="status-indicator status-available"></span>
                            <span>Available</span>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/borrow/create?item=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">Request</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>