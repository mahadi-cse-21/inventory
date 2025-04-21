<div class="top-bar">
    <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" class="search-input" placeholder="Search inventory..." id="global-search">
    </div>
    <div class="user-menu">
        <a href="<?php echo BASE_URL; ?>/notifications" class="notification-icon">
            <i class="fas fa-bell"></i>
            <?php 
            // Get unread notifications count
            $notificationCount = 0;
            if (isset($_SESSION['user_id'])) {
                // If you have a notifications table, you can count unread notifications
                $conn = getDbConnection();
                $sql = "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$_SESSION['user_id']]);
                $notificationCount = (int)$stmt->fetchColumn();
            }
            
            if ($notificationCount > 0): 
            ?>
            <span class="notification-badge"><?php echo $notificationCount; ?></span>
            <?php endif; ?>
        </a>
        <div class="user-profile">
            <div class="user-avatar">
                <?php 
                $initials = '';
                if (isset($_SESSION['full_name'])) {
                    $nameParts = explode(' ', $_SESSION['full_name']);
                    foreach ($nameParts as $part) {
                        $initials .= strtoupper(substr($part, 0, 1));
                    }
                    echo substr($initials, 0, 2);
                } else {
                    echo 'U';
                }
                ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Guest'; ?></div>
                <div class="user-role"><?php echo isset($_SESSION['user_role']) ? ucfirst($_SESSION['user_role']) : ''; ?></div>
            </div>
        </div>
    </div>
</div>

<script>
// Handle global search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('global-search');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const searchTerm = this.value.trim();
                if (searchTerm) {
                    window.location.href = '<?php echo BASE_URL; ?>/items/search?q=' + encodeURIComponent(searchTerm);
                }
            }
        });
    }
});
</script>   