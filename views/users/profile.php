<?php
/**
 * User Profile View
 * 
 * This file displays the user's profile information
 */
$conn = getDbConnection();

// Set page title
$pageTitle = 'My Profile';

// Get current user
$currentUser = AuthHelper::getCurrentUser();

if (!$currentUser) {
    setFlashMessage('You must be logged in to view your profile.', 'danger');
    redirect(BASE_URL . '/auth/login');
}

// Get department name
$departmentName = 'Not Set';
if (!empty($currentUser['department_id'])) {
    $conn = getDbConnection();
    $sql = "SELECT name FROM departments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$currentUser['department_id']]);
    $departmentName = $stmt->fetchColumn() ?: 'Not Set';
}

// Get location name
$locationName = 'Not Set';
if (!empty($currentUser['location_id'])) {
    $conn = getDbConnection();
    $sql = "SELECT name FROM locations WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$currentUser['location_id']]);
    $locationName = $stmt->fetchColumn() ?: 'Not Set';
}

// Get borrowing statistics
// Count currently borrowed items
$borrowedSql = "SELECT COUNT(*) FROM borrowed_items bi 
               JOIN borrow_requests br ON bi.borrow_request_id = br.id 
               WHERE br.user_id = ? AND br.status = 'checked_out' AND bi.is_returned = 0";
$borrowedStmt = $conn->prepare($borrowedSql);
$borrowedStmt->execute([$currentUser['id']]);
$borrowedCount = (int)$borrowedStmt->fetchColumn();

// Calculate on-time return rate
$returnRateSql = "SELECT 
                    COUNT(*) as total_returns,
                    SUM(CASE WHEN br.status = 'returned' AND br.return_date >= br.actual_return_date THEN 1 ELSE 0 END) as on_time_returns
                  FROM borrow_requests br
                  WHERE br.user_id = ? AND br.status = 'returned'";
$returnRateStmt = $conn->prepare($returnRateSql);
$returnRateStmt->execute([$currentUser['id']]);
$returnStats = $returnRateStmt->fetch();

$onTimeReturnRate = 0;
if ($returnStats['total_returns'] > 0) {
    $onTimeReturnRate = round(($returnStats['on_time_returns'] / $returnStats['total_returns']) * 100);
}

// Get average borrow duration
$durationSql = "SELECT AVG(DATEDIFF(COALESCE(br.actual_return_date, NOW()), br.borrow_date)) as avg_duration
               FROM borrow_requests br
               WHERE br.user_id = ? AND (br.status = 'returned' OR br.status = 'checked_out')";
$durationStmt = $conn->prepare($durationSql);
$durationStmt->execute([$currentUser['id']]);
$avgDuration = round((float)$durationStmt->fetchColumn(), 1);

// Get frequently borrowed items
$frequentItemsSql = "SELECT i.id, i.name, i.brand, i.model, i.asset_id, COUNT(*) as borrow_count, MAX(br.created_at) as last_borrowed
                     FROM borrowed_items bi
                     JOIN items i ON bi.item_id = i.id
                     JOIN borrow_requests br ON bi.borrow_request_id = br.id
                     WHERE br.user_id = ?
                     GROUP BY i.id
                     ORDER BY borrow_count DESC, last_borrowed DESC
                     LIMIT 5";
$frequentItemsStmt = $conn->prepare($frequentItemsSql);
$frequentItemsStmt->execute([$currentUser['id']]);
$frequentItems = $frequentItemsStmt->fetchAll();

// Include header
include 'includes/header.php';
?>

<h1 class="page-title">My Profile</h1>

<!-- Profile Overview -->
<div style="display: flex; gap: 2rem; margin-bottom: 2rem; flex-wrap: wrap;">
    <div style="flex: 0 0 200px;">
        <div style="background-color: var(--primary-light); width: 200px; height: 200px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 4rem; font-weight: 500; margin-bottom: 1rem;">
            <?php
            $initials = '';
            $nameParts = explode(' ', $currentUser['full_name']);
            foreach ($nameParts as $part) {
                $initials .= strtoupper(substr($part, 0, 1));
            }
            echo substr($initials, 0, 2);
            ?>
        </div>
        <a href="<?php echo BASE_URL; ?>/users/update-profile" class="btn btn-outline" style="width: 100%; margin-bottom: 0.5rem;">
            <i class="fas fa-pencil-alt btn-icon"></i> Edit Profile
        </a>
        <a href="<?php echo BASE_URL; ?>/users/change-password" class="btn btn-outline" style="width: 100%;">
            <i class="fas fa-key btn-icon"></i> Change Password
        </a>
    </div>
    <div style="flex: 1; min-width: 300px;">
        <div style="background-color: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); padding: 1.5rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2 style="font-size: 1.25rem; font-weight: 600;">Personal Information</h2>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem;">
                <div>
                    <div style="font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Full Name</div>
                    <div style="font-weight: 500;"><?php echo htmlspecialchars($currentUser['full_name']); ?></div>
                </div>
                <div>
                    <div style="font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Email Address</div>
                    <div style="font-weight: 500;"><?php echo htmlspecialchars($currentUser['email']); ?></div>
                </div>
                <div>
                    <div style="font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Phone Number</div>
                    <div style="font-weight: 500;"><?php echo !empty($currentUser['phone']) ? htmlspecialchars($currentUser['phone']) : 'Not Set'; ?></div>
                </div>
                <div>
                    <div style="font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Department</div>
                    <div style="font-weight: 500;"><?php echo htmlspecialchars($departmentName); ?></div>
                </div>
                <div>
                    <div style="font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Job Title</div>
                    <div style="font-weight: 500;"><?php echo !empty($currentUser['job_title']) ? htmlspecialchars($currentUser['job_title']) : 'Not Set'; ?></div>
                </div>
                <div>
                    <div style="font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Location</div>
                    <div style="font-weight: 500;"><?php echo htmlspecialchars($locationName); ?></div>
                </div>
            </div>
        </div>

        <div style="background-color: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); padding: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2 style="font-size: 1.25rem; font-weight: 600;">Account Information</h2>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem;">
                <div>
                    <div style="font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Username</div>
                    <div style="font-weight: 500;"><?php echo htmlspecialchars($currentUser['username']); ?></div>
                </div>
                <div>
                    <div style="font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Role</div>
                    <div style="font-weight: 500;"><?php echo ucfirst(htmlspecialchars($currentUser['role'])); ?></div>
                </div>
                <div>
                    <div style="font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Member Since</div>
                    <div style="font-weight: 500;"><?php echo UtilityHelper::formatDateForDisplay($currentUser['created_at'], 'long'); ?></div>
                </div>
                <div>
                    <div style="font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.25rem;">Last Login</div>
                    <div style="font-weight: 500;"><?php echo $currentUser['last_login'] ? UtilityHelper::formatDateForDisplay($currentUser['last_login'], 'datetime') : 'Never'; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs for additional profile sections -->
<div class="tabs-container">
    <div class="tabs-header">
        <div class="tab active">Borrowing Statistics</div>
        <div class="tab">Preferences</div>
        <div class="tab">Activity Log</div>
    </div>

    <!-- Borrowing Statistics Tab -->
    <div class="tab-content active">
        <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div style="flex: 1; min-width: 200px; background-color: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); padding: 1.5rem;">
                <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                    <div style="width: 40px; height: 40px; border-radius: 8px; background-color: var(--primary-light); color: white; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.25rem;">Total Items Borrowed</h3>
                        <div style="color: var(--gray-500); font-size: 0.9rem;">Lifetime statistics</div>
                    </div>
                </div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;"><?php echo $returnStats['total_returns']; ?></div>
                <div style="color: var(--gray-500); font-size: 0.9rem;">
                    <?php if ($borrowedCount > 0): ?>
                        Currently borrowing: <?php echo $borrowedCount; ?> item(s)
                    <?php else: ?>
                        No items currently borrowed
                    <?php endif; ?>
                </div>
            </div>

            <div style="flex: 1; min-width: 200px; background-color: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); padding: 1.5rem;">
                <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                    <div style="width: 40px; height: 40px; border-radius: 8px; background-color: var(--secondary-light); color: white; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.25rem;">On-Time Return Rate</h3>
                        <div style="color: var(--gray-500); font-size: 0.9rem;">All time</div>
                    </div>
                </div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;"><?php echo $onTimeReturnRate; ?>%</div>
                <div style="color: var(--secondary); font-size: 0.9rem;">
                    <?php if ($returnStats['total_returns'] > 0): ?>
                        <?php echo $returnStats['on_time_returns']; ?> out of <?php echo $returnStats['total_returns']; ?> items returned on time
                    <?php else: ?>
                        No return history yet
                    <?php endif; ?>
                </div>
            </div>

            <div style="flex: 1; min-width: 200px; background-color: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); padding: 1.5rem;">
                <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                    <div style="width: 40px; height: 40px; border-radius: 8px; background-color: var(--info); color: white; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.25rem;">Average Borrow Duration</h3>
                        <div style="color: var(--gray-500); font-size: 0.9rem;">All time</div>
                    </div>
                </div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;"><?php echo $avgDuration; ?> days</div>
                <div style="color: var(--gray-500); font-size: 0.9rem;">Typical duration: 3-14 days</div>
            </div>
        </div>

        <!-- Most Frequently Borrowed Items -->
        <?php if (count($frequentItems) > 0): ?>
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">Most Frequently Borrowed Items</div>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Times Borrowed</th>
                                <th>Last Borrowed</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($frequentItems as $item): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center;">
                                            <i class="fas fa-box" style="margin-right: 10px; color: var(--gray-500);"></i>
                                            <div>
                                                <div style="font-weight: 500;"><?php echo htmlspecialchars($item['name']); ?></div>
                                                <div style="font-size: 0.8rem; color: var(--gray-500);">
                                                    <?php if (!empty($item['brand']) || !empty($item['model'])): ?>
                                                        <?php echo htmlspecialchars($item['brand'] . ' ' . $item['model']); ?>
                                                    <?php else: ?>
                                                        Asset #<?php echo htmlspecialchars($item['asset_id']); ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        // Get category name
                                        $categorySql = "SELECT c.name FROM items i JOIN categories c ON i.category_id = c.id WHERE i.id = ?";
                                        $categoryStmt = $conn->prepare($categorySql);
                                        $categoryStmt->execute([$item['id']]);
                                        $categoryName = $categoryStmt->fetchColumn() ?: 'Uncategorized';
                                        echo htmlspecialchars($categoryName);
                                        ?>
                                    </td>
                                    <td><?php echo (int)$item['borrow_count']; ?></td>
                                    <td><?php echo UtilityHelper::formatDateForDisplay($item['last_borrowed'], 'short'); ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/borrow/create?item=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline">Request Again</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            You haven't borrowed any items yet. <a href="<?php echo BASE_URL; ?>/items/browse">Browse items</a> to start borrowing.
        </div>
        <?php endif; ?>
    </div>

    <!-- Preferences Tab (Hidden) -->
    <div class="tab-content" style="display: none;">
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">Display Preferences</div>
            </div>
            <div class="panel-body">
                <form method="POST" action="<?php echo BASE_URL; ?>/users/update-preferences">
                    <!-- CSRF Token -->
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                    
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Dark Mode</div>
                                <div class="settings-item-description">Use dark theme for better visibility in low light</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="preferences[dark_mode]" value="1" 
                                        <?php echo isset($currentUser['preferences']['dark_mode']) && $currentUser['preferences']['dark_mode'] ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Items Per Page</div>
                                <div class="settings-item-description">Number of items to display in lists</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="preferences[items_per_page]" style="width: 180px;">
                                    <option value="10" <?php echo isset($currentUser['preferences']['items_per_page']) && $currentUser['preferences']['items_per_page'] == 10 ? 'selected' : ''; ?>>10 items</option>
                                    <option value="20" <?php echo !isset($currentUser['preferences']['items_per_page']) || $currentUser['preferences']['items_per_page'] == 20 ? 'selected' : ''; ?>>20 items</option>
                                    <option value="50" <?php echo isset($currentUser['preferences']['items_per_page']) && $currentUser['preferences']['items_per_page'] == 50 ? 'selected' : ''; ?>>50 items</option>
                                    <option value="100" <?php echo isset($currentUser['preferences']['items_per_page']) && $currentUser['preferences']['items_per_page'] == 100 ? 'selected' : ''; ?>>100 items</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Email Notifications</div>
                                <div class="settings-item-description">Receive email notifications for requests and updates</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="preferences[email_notifications]" value="1"
                                        <?php echo !isset($currentUser['preferences']['email_notifications']) || $currentUser['preferences']['email_notifications'] ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save btn-icon"></i> Save Preferences
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Activity Log Tab (Hidden) -->
    <div class="tab-content" style="display: none;">
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">Recent Activity</div>
            </div>
            <div class="panel-body">
                <?php
                // Get user activity logs
                $activityResult = UserHelper::getUserActivityLogs($currentUser['id'], 1, 20);
                $activityLogs = $activityResult['logs'];
                ?>
                
                <?php if (count($activityLogs) > 0): ?>
                    <ul class="activity-list">
                        <?php foreach ($activityLogs as $log): ?>
                            <li class="activity-item">
                                <?php
                                // Determine icon based on action
                                $iconClass = 'info-circle';
                                $iconColor = 'blue';
                                
                                if (strpos($log['action'], 'create') !== false) {
                                    $iconClass = 'plus-circle';
                                    $iconColor = 'green';
                                } elseif (strpos($log['action'], 'update') !== false) {
                                    $iconClass = 'edit';
                                    $iconColor = 'orange';
                                } elseif (strpos($log['action'], 'delete') !== false) {
                                    $iconClass = 'trash-alt';
                                    $iconColor = 'red';
                                } elseif (strpos($log['action'], 'login') !== false) {
                                    $iconClass = 'sign-in-alt';
                                    $iconColor = 'purple';
                                } elseif (strpos($log['action'], 'logout') !== false) {
                                    $iconClass = 'sign-out-alt';
                                    $iconColor = 'gray';
                                } elseif (strpos($log['action'], 'borrow') !== false || strpos($log['action'], 'return') !== false) {
                                    $iconClass = 'exchange-alt';
                                    $iconColor = 'blue';
                                }
                                ?>
                                <div class="activity-icon icon-<?php echo $iconColor; ?>">
                                    <i class="fas fa-<?php echo $iconClass; ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        <?php
                                        // Format action for display
                                        $action = str_replace('_', ' ', $log['action']);
                                        echo ucwords($action);
                                        ?>
                                    </div>
                                    <div><?php echo htmlspecialchars($log['description'] ?? 'No description'); ?></div>
                                    <div class="activity-time"><?php echo UtilityHelper::formatDateForDisplay($log['created_at'], 'datetime'); ?></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <?php if ($activityResult['pagination']['totalPages'] > 1): ?>
                        <div class="pagination" style="margin-top: 1rem;">
                            <?php echo UtilityHelper::paginationLinks($activityResult['pagination'], BASE_URL . '/users/profile?tab=activity'); ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">No activity recorded yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Tab switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tabs-header .tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => {
            // Remove active class from all tabs and contents
            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(c => c.style.display = 'none');
            
            // Add active class to clicked tab and corresponding content
            tab.classList.add('active');
            tabContents[index].style.display = 'block';
            
            // Update URL with tab parameter
            const tabNames = ['stats', 'preferences', 'activity'];
            const url = new URL(window.location);
            url.searchParams.set('tab', tabNames[index]);
            history.replaceState(null, '', url);
        });
    });
    
    // Check if tab parameter exists in URL
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    
    if (tabParam) {
        const tabIndex = {
            'stats': 0,
            'preferences': 1,
            'activity': 2
        }[tabParam];
        
        if (tabIndex !== undefined) {
            tabs[tabIndex].click();
        }
    }
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?>