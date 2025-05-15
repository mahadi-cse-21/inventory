<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo">Inventory<span class="logo-accent">Pro</span></div>
        <button class="nav-toggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    <div class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <a href="<?php echo BASE_URL; ?>" class="nav-item <?php echo ($_SERVER['REQUEST_URI'] == BASE_URL || $_SERVER['REQUEST_URI'] == BASE_URL . '/') ? 'active' : ''; ?>">
                <div class="nav-item-icon">
                    <i class="fas fa-th-large"></i>
                </div>
                <span>Dashboard</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/items/browse" class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/items/browse') !== false) ? 'active' : ''; ?>">
                <div class="nav-item-icon">
                    <i class="fas fa-search"></i>
                </div>
                <span>Browse Items</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/borrow/my-items" class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/borrow/my-items') !== false) ? 'active' : ''; ?>">
                <div class="nav-item-icon">
                    <i class="fas fa-hand-holding"></i>
                </div>
                <span>My Borrowed Items</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/borrow/history" class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/borrow/history') !== false) ? 'active' : ''; ?>">
                <div class="nav-item-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <span>Request History</span>
            </a>
        </div>

        <div class="nav-section account-section">
            <div class="nav-section-title">Account</div>
            <a href="<?php echo BASE_URL; ?>/users/profile" class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/users/profile') !== false) ? 'active' : ''; ?>">
                <div class="nav-item-icon">
                    <i class="fas fa-user"></i>
                </div>
                <span>My Profile</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/auth/logout" class="nav-item logout-item">
                <div class="nav-item-icon">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <span>Logout</span>
            </a>
        </div>
    </div>
</div>