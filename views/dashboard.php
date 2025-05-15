<?php

/**
 * Dashboard View
 * 
 * This is the main dashboard page that users see after logging in.
 * It displays summary information and quick access to common functions.
 * Enhanced with responsive design for all screen sizes.
 */

// Set page title
$pageTitle = 'Dashboard';

// Get current user
$currentUser = AuthHelper::getCurrentUser();

// Get dashboard statistics
$conn = getDbConnection();

// Count currently borrowed items by the user
if (hasRole('admin')) {
    $borrowedSql = "SELECT COUNT(*) FROM borrowed_item WHERE status = 'borrowed'";
    $borrowedStmt = $conn->prepare($borrowedSql);
    $borrowedStmt->execute();
} else {
    $borrowedSql = "SELECT COUNT(*) 
                    FROM borrowed_item 
                    JOIN requests ON borrowed_item.request_id = requests.id 
                    WHERE requests.user_id = ? AND borrowed_item.status = 'borrowed'";
    $borrowedStmt = $conn->prepare($borrowedSql);
    $borrowedStmt->execute([$currentUser['id']]);
}

$borrowedSql = "SELECT COUNT(*) FROM borrowed_item 
where status = 'borrowed'";


$borrowedCount = (int)$borrowedStmt->fetchColumn();

// Count pending requests by the user
if (hasRole('admin')) {
    // Admin: count all pending requests
    $pendingSql = "SELECT COUNT(*) FROM requests WHERE status = 'pending'";
    $pendingStmt = $conn->prepare($pendingSql);
    $pendingStmt->execute();
} else {
    // Student: count only their pending requests
    $pendingSql = "SELECT COUNT(*) FROM requests WHERE user_id = ? AND status = 'pending'";
    $pendingStmt = $conn->prepare($pendingSql);
    $pendingStmt->execute([$currentUser['id']]);
}

// Fetch the count result
$pendingCount = $pendingStmt->fetchColumn();
$pendingCount = (int)$pendingStmt->fetchColumn();

// Count past borrows in the last 90 days
$pastSql = "SELECT COUNT(*) FROM borrowed_item 
           WHERE request_id = ? AND (status = 'returned' OR status = 'approved') 
           ";
$pastStmt = $conn->prepare($pastSql);
$pastStmt->execute([$currentUser['id']]);
$pastCount = (int)$pastStmt->fetchColumn();

// Count upcoming reservations
$reservationCount = 0;

$pendingrequestssql = "SELECT * from requests where status = 'pending'";
$pendingRequestsstmt = $conn->prepare($pendingrequestssql);
$pendingRequestsstmt->execute();

$pendingRequests = $pendingRequestsstmt->fetchAll(PDO::FETCH_ASSOC);
$recommendedItemSql = "
    SELECT 
        item.id,
        item.name,
        category.name AS category_name,
        location.building_name,
        location.room,
        COUNT(*) AS borrow_count
    FROM borrowed_item
    JOIN item ON borrowed_item.item_id = item.id
    LEFT JOIN category ON item.cat_id = category.id
    LEFT JOIN location ON item.location_id = location.id
    GROUP BY 
        item.id,
        item.name,
        category.name,
        location.building_name,
        location.room
    ORDER BY borrow_count DESC
    LIMIT 5
";


$stmt = $conn->prepare($recommendedItemSql);
$stmt->execute();
$recommendedItems = $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];


$activeBorrowssql = "
    SELECT 
        borrowed_item.*, 
        item.name AS item_name, 
        item.id AS asset_id
    FROM 
        borrowed_item
    INNER JOIN 
        item ON borrowed_item.item_id = item.id
    WHERE 
        borrow_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)
";
$activeBorrowsstmt = $conn->prepare($activeBorrowssql);
$activeBorrowsstmt->execute();
$activeBorrows = $activeBorrowsstmt->fetchAll(PDO::FETCH_ASSOC);



// Include header
include 'includes/header.php';
?>

<!-- Add responsive meta tag if not already in header -->
<style>
    /* Responsive Styles */
    .dashboard-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .dashboard-card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        padding: 20px;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
    }

    .dashboard-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .dashboard-card-value {
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .dashboard-card-description {
        color: var(--gray-500);
        font-size: 0.9rem;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table th,
    table td {
        padding: 12px;
        text-align: left;
    }

    /* Responsive Item Cards */
    .items-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }

    .item-card {
        display: flex;
        flex-direction: column;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        background-color: #fff;
        transition: transform 0.2s;
    }

    .item-card:hover {
        transform: translateY(-5px);
    }

    .item-card-image {
        height: 120px;
        background-color: var(--gray-100);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .item-card-image i {
        font-size: 3rem;
        color: var(--gray-400);
    }

    .item-card-body {
        padding: 15px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .item-card-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 10px;
    }

    .badge {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 500;
    }

    .item-card-title {
        margin: 0 0 10px 0;
        font-size: 1.1rem;
    }

    .item-card-details {
        margin-bottom: 15px;
        flex-grow: 1;
    }

    .item-detail {
        display: flex;
        margin-bottom: 5px;
        font-size: 0.9rem;
    }

    .item-detail-label {
        color: var(--gray-500);
        margin-right: 5px;
        flex-shrink: 0;
        width: 60px;
    }

    .item-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: auto;
    }

    /* Panel styles */
    .panel {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }

    .panel-header {
        padding: 15px 20px;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .panel-title {
        font-size: 1.1rem;
        font-weight: 500;
    }

    .panel-body {
        padding: 20px;
    }

    /* Status indicators */
    .status {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .status-active {
        background-color: rgba(72, 187, 120, 0.1);
        color: #2f855a;
    }

    .status-pending {
        background-color: rgba(237, 137, 54, 0.1);
        color: #c05621;
    }

    .status-inactive {
        background-color: rgba(229, 62, 62, 0.1);
        color: #c53030;
    }

    /* Responsive Button styles */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 16px;
        border-radius: 4px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.2s;
        white-space: nowrap;
    }

    .btn-icon {
        margin-right: 6px;
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 0.875rem;
    }

    .btn-outline {
        border: 1px solid var(--gray-300);
        background-color: transparent;
        color: var(--gray-700);
    }

    .btn-primary {
        background-color: var(--primary);
        color: #fff;
        border: none;
    }

    /* Mobile-specific styles */
    @media (max-width: 768px) {
        .dashboard-cards {
            grid-template-columns: 1fr;
        }

        .panel-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .panel-header .btn {
            width: 100%;
            margin-top: 8px;
        }

        /* Table responsiveness for mobile */
        table thead {
            display: none;
        }

        table,
        table tbody,
        table tr,
        table td {
            display: block;
            width: 100%;
        }

        table tr {
            margin-bottom: 15px;
            border-bottom: 1px solid var(--gray-200);
            padding-bottom: 15px;
        }

        table td {
            display: flex;
            justify-content: space-between;
            text-align: right;
            padding: 8px 12px;
        }

        table td::before {
            content: attr(data-label);
            font-weight: 600;
            float: left;
            text-align: left;
        }

        /* Adjust item grid for mobile */
        .items-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Small tablet styles */
    @media (min-width: 576px) and (max-width: 767px) {
        .items-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    /* Large screens */
    @media (min-width: 992px) {
        .items-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    /* Extra large screens */
    @media (min-width: 1200px) {
        .dashboard-cards {
            grid-template-columns: repeat(4, 1fr);
        }

        .items-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }
</style>

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
        <div class="dashboard-card-description">Active items</div>
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
                                <td data-label="Item">
                                    <div style="display: flex; align-items: center;">
                                        <i class="fas fa-box" style="margin-right: 10px; color: var(--gray-500);"></i>
                                        <div>
                                            <div style="font-weight: 500;"><?php echo htmlspecialchars($borrow['item_name']); ?></div>
                                            <div style="font-size: 0.8rem; color: var(--gray-500);">Asset #<?php echo htmlspecialchars($borrow['asset_id']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Borrowed On"><?php echo UtilityHelper::formatDateForDisplay($borrow['borrow_date'], 'short'); ?></td>
                                <td data-label="Due Date"><?php echo UtilityHelper::formatDateForDisplay($borrow['due_date'], 'short'); ?></td>
                                <td data-label="Status">
                                    <?php echo $borrow['status']; ?>
                                </td>

                                <td data-label="Actions">
                                    <?php if ($borrow['status'] !== 'returned'): ?>
                                        <a href="<?php echo BASE_URL; ?>/borrow/return?id=<?php echo $borrow['id']; ?>" class="btn btn-sm btn-outline">Return</a>
                                    <?php endif; ?>
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
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingRequests as $request): ?>
                            <tr>
                                <td data-label="Request ID">
                                    <span style="font-weight: 500; color: var(--primary);"><?php echo htmlspecialchars($request['id']); ?></span>
                                </td>
                                <td data-label="Items"><?php echo $request['quantity']; ?> item(s)</td>
                                <td data-label="Requested On"><?php echo UtilityHelper::formatDateForDisplay($request['request_date'], 'short'); ?></td>
                                <td data-label="Status">
                                    <span class="status status-pending"><?php echo $request['status'] ?></span>
                                </td>

                                <!-- Actions Column -->
                                <td data-label="Actions">
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <?php if ($currentUser['role']=='admin'): ?>
                                            <!-- Active buttons for non-students -->
                                            <a href="<?php echo BASE_URL; ?>/borrow/approve?id=<?php echo $request['id']; ?>"
                                                class="btn btn-sm btn-success"
                                                onclick="return confirm('Are you sure you want to approve this request?');">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/borrow/cancel?id=<?php echo $request['id']; ?>"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to cancel this request?');">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php else: ?>
                                            <!-- Disabled buttons for students -->
                                            <button class="btn btn-sm btn-success" disabled>
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" disabled>
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
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
        <div class="panel-title">Recommended Items </div>
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

                        <h3 class="item-card-title"><?php echo htmlspecialchars($item['name']); ?></h3>
                        <div class="item-card-details">
                            <?php if (!empty($item['brand'])): ?>

                            <?php endif; ?>


                            <?php
                            // Get location name
                            $locationDisplay = 'Unknown';
                            if (!empty($item['building_name']) || !empty($item['room'])) {
                                $locationDisplay = htmlspecialchars($item['building_name'] . ' - ' . $item['room']);
                            }
                            ?>
                            <div class="item-detail">
                                <span class="item-detail-label">Location:</span>
                                <span class="item-detail-value"><?php echo $locationDisplay; ?></span>
                            </div>
                        </div>
                        <div class="item-card-footer">
                            <div class="item-status">
                                <span class="status-indicator status-available"></span>
                                <span>Available</span>
                            </div>


                            <a href="<?php
                                        if ($currentUser['role'] == 'student') {

                                            echo BASE_URL . '/borrow/requestto?id=' . $item['id'];
                                        } else {
                                            echo BASE_URL . '/borrow/requests?id=' . $item['id'];
                                        }
                                        ?>" class="btn btn-sm btn-primary">Request</a>
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