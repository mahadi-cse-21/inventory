<?php
/**
 * Update User Profile View
 * 
 * This file allows users to update their profile information
 */

// Set page title
$pageTitle = 'Update Profile';

// Check if user is logged in
if (!AuthHelper::isAuthenticated()) {
    setFlashMessage('You must be logged in to update your profile.', 'danger');
    redirect(BASE_URL . '/auth/login');
}

// Get current user
$currentUser = AuthHelper::getCurrentUser();

// Get departments for select dropdown
$departments = UserHelper::getAllDepartments();

// Get locations for select dropdown
$locationsSql = "SELECT id, name FROM locations WHERE is_active = 1 ORDER BY name";
$locationsStmt = getDbConnection()->prepare($locationsSql);
$locationsStmt->execute();
$locations = $locationsStmt->fetchAll();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    validateCsrfToken();
    
    // Get form data
    $userData = [
        'full_name' => cleanInput($_POST['full_name'] ?? ''),
        'email' => cleanInput($_POST['email'] ?? ''),
        'phone' => cleanInput($_POST['phone'] ?? ''),
        'job_title' => cleanInput($_POST['job_title'] ?? ''),
        'department_id' => !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null,
        'location_id' => !empty($_POST['location_id']) ? (int)$_POST['location_id'] : null
    ];
    
    // Validate required fields
    $errors = [];
    
    if (empty($userData['full_name'])) {
        $errors[] = 'Full name is required';
    }
    
    if (empty($userData['email'])) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    // Check if email already exists (excluding this user)
    if ($userData['email'] !== $currentUser['email'] && 
        UtilityHelper::valueExists('users', 'email', $userData['email'], $currentUser['id'])) {
        $errors[] = 'Email already exists. Please use a different email address.';
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        $result = UserHelper::updateUser($currentUser['id'], $userData);
        
        if ($result['success']) {
            setFlashMessage('Profile updated successfully', 'success');
            redirect(BASE_URL . '/users/profile');
        } else {
            setFlashMessage($result['message'], 'danger');
        }
    } else {
        // Show validation errors
        setFlashMessage(implode('<br>', $errors), 'danger');
    }
}

// Include header
include 'includes/header.php';
?>

<h1 class="page-title">Update Profile</h1>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Edit Your Profile</div>
    </div>
    <div class="panel-body">
        <form method="POST" action="<?php echo BASE_URL; ?>/users/update-profile">
            <!-- CSRF Token -->
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
            
            <div class="form-group">
                <label class="form-label" for="full_name">Full Name <span style="color: var(--danger);">*</span></label>
                <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($currentUser['full_name']); ?>" required>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address <span style="color: var(--danger);">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="department_id">Department</label>
                        <select class="form-control" id="department_id" name="department_id">
                            <option value="">-- Select Department --</option>
                            <?php foreach ($departments as $department): ?>
                                <option value="<?php echo $department['id']; ?>" <?php echo ($currentUser['department_id'] == $department['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($department['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="job_title">Job Title</label>
                        <input type="text" class="form-control" id="job_title" name="job_title" value="<?php echo htmlspecialchars($currentUser['job_title'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="location_id">Location</label>
                <select class="form-control" id="location_id" name="location_id">
                    <option value="">-- Select Location --</option>
                    <?php foreach ($locations as $location): ?>
                        <option value="<?php echo $location['id']; ?>" <?php echo ($currentUser['location_id'] == $location['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($location['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save btn-icon"></i> Save Changes
                </button>
                <a href="<?php echo BASE_URL; ?>/users/profile" class="btn btn-outline">
                    <i class="fas fa-times btn-icon"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>