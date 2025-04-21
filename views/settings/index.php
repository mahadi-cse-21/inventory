<?php
/**
 * Settings Index View
 * 
 * This file displays the main settings page and navigation
 */

// Set page title
$pageTitle = 'System Settings';

// Check if user has admin permission
if (!hasRole('admin')) {
    setFlashMessage('You do not have permission to access this page.', 'danger');
    redirect(BASE_URL);
    exit;
}

// Include header
include 'includes/header.php';

// Get active tab from URL or default to 'general'
$activeTab = isset($_GET['tab']) ? cleanInput($_GET['tab']) : 'general';

// Define available tabs with their titles and icons
$tabs = [
    'general' => ['title' => 'General', 'icon' => 'sliders-h', 'description' => 'System preferences and basic settings'],
    'company' => ['title' => 'Company Profile', 'icon' => 'building', 'description' => 'Organization information and branding'],
    'notifications' => ['title' => 'Notifications', 'icon' => 'bell', 'description' => 'Email and system notification settings'],
    'security' => ['title' => 'Security', 'icon' => 'shield-alt', 'description' => 'Password policies and security options'],
    'inventory' => ['title' => 'Inventory', 'icon' => 'box', 'description' => 'Inventory management and tracking settings'],
    'backup' => ['title' => 'Backup & Data', 'icon' => 'database', 'description' => 'Data export and backup configuration'],
    'users' => ['title' => 'User Management', 'icon' => 'users', 'description' => 'Manage system users and accounts'],
    'roles' => ['title' => 'Roles & Permissions', 'icon' => 'id-badge', 'description' => 'Define user roles and access levels']
];

// Get settings from database
$settings = SettingsHelper::getAllSettings();

// Process form submission for settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    validateCsrfToken();
    
    // Get form data
    $updatedSettings = $_POST['settings'] ?? [];
    
    // Update settings
    $result = SettingsHelper::updateSettings($updatedSettings);
    
    if ($result['success']) {
        // Refresh settings data
        $settings = SettingsHelper::getAllSettings();
        setFlashMessage('Settings updated successfully.', 'success');
    } else {
        setFlashMessage('Failed to update settings: ' . $result['message'], 'danger');
    }
}
?>

<!-- Flash message container for AJAX responses -->
<div id="flash-message-container"></div>

<!-- Breadcrumb -->
<div class="content-header">
    <div class="breadcrumbs">
        <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Dashboard</a>
        <span class="breadcrumb-item">Settings</span>
        <span class="breadcrumb-item"><?php echo $tabs[$activeTab]['title']; ?></span>
    </div>
</div>

<div class="settings-layout">
    <!-- Settings Sidebar -->
    <div class="settings-sidebar">
        <div class="settings-sidebar-header">
            <div class="settings-title">System Settings</div>
            <div class="settings-subtitle">Configure your inventory system</div>
        </div>

        <ul class="settings-nav">
            <?php foreach ($tabs as $tabId => $tab): ?>
                <li class="settings-nav-item <?php echo $activeTab === $tabId ? 'active' : ''; ?>"
                    onclick="location.href='<?php echo BASE_URL; ?>/settings?tab=<?php echo $tabId; ?>'">
                    <span class="settings-nav-icon"><i class="fas fa-<?php echo $tab['icon']; ?>"></i></span>
                    <span><?php echo $tab['title']; ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Settings Content -->
    <div class="settings-content">
        <h1 class="page-title"><?php echo $tabs[$activeTab]['title']; ?> Settings</h1>
        <p class="settings-description"><?php echo $tabs[$activeTab]['description']; ?></p>
        
        <?php
        // Include the appropriate tab content based on active tab
        $tabFile = 'views/settings/tabs/' . $activeTab . '.php';
        if (file_exists($tabFile)) {
            include $tabFile;
        } else {
            echo '<div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                The requested settings page does not exist.
            </div>';
        }
        ?>

        <!-- Action Bar -->
        <div class="action-bar">
            <button type="button" class="btn btn-outline" onclick="window.location.href='<?php echo BASE_URL; ?>'">
                <i class="fas fa-times btn-icon"></i>
                Cancel
            </button>
            <button type="submit" form="settings-form" class="btn btn-primary">
                <i class="fas fa-save btn-icon"></i>
                Save Changes
            </button>
        </div>
    </div>
</div>

<!-- Add enhanced JavaScript file reference -->
<script src="<?php echo BASE_URL; ?>/assets/js/settings.js"></script>

<?php
// Include footer
include 'includes/footer.php';
?>