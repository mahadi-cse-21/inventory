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

        <!-- <?php
         //echo BASE_URL; 
         ?> -->

        <!-- <div class="nav-section">
            <div class="nav-section-title">Actions</div>
            
            <a href="
            
            /borrow/return" class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/borrow/return') !== false) ? 'active' : ''; ?>">
                <div class="nav-item-icon">
                    <i class="fas fa-undo-alt"></i>
                </div>
                <span>Requested Items</span>
            </a>
        </div> -->

        <?php if (hasRole(['admin', 'manager'])): ?>
        <div class="nav-section">
            <div class="nav-section-title">Management</div>
            <a href="<?php echo BASE_URL; ?>/items" class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/items') !== false && strpos($_SERVER['REQUEST_URI'], '/items/browse') === false) ? 'active' : ''; ?>">
                <div class="nav-item-icon">
                    <i class="fas fa-box"></i>
                </div>
                <span>Items</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/borrow/requests" class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/borrow/requests') !== false) ? 'active' : ''; ?>">
                <div class="nav-item-icon">
                    <i class="fas fa-hand-holding"></i>
                </div>
                <span>Borrowed Items</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/maintenance" class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/maintenance') !== false) ? 'active' : ''; ?>">
                <div class="nav-item-icon">
                    <i class="fas fa-tools"></i>
                </div>
                <span>Maintenance</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/locations" class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/locations') !== false) ? 'active' : ''; ?>">
                <div class="nav-item-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <span>Locations</span>
            </a>
            <?php if (hasRole('admin')): ?>
            <a href="<?php echo BASE_URL; ?>/users" class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/users') !== false && strpos($_SERVER['REQUEST_URI'], '/users/profile') === false) ? 'active' : ''; ?>">
                <div class="nav-item-icon">
                    <i class="fas fa-users"></i>
                </div>
                <span>Users</span>
            </a>
          
            <?php endif; ?>
        </div>
        <?php endif; ?>

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
        
        <!-- <div class="sidebar-footer">
            <div class="sidebar-theme-toggle">
                <label class="theme-switch" for="themeSwitch">
                    <input type="checkbox" id="themeSwitch" />
                    <span class="theme-slider">
                        <i class="fas fa-sun"></i>
                        <i class="fas fa-moon"></i>
                    </span>
                </label>
                <span class="theme-label">Dark Mode</span>
            </div>
        </div> -->
    </div>
</div>