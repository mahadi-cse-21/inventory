<?php
/**
 * User Create View
 * 
 * This file provides a form to create new user accounts
 */

// Set page title
$pageTitle = 'Create User';
$bodyClass = 'users-page';

// Check permissions
if (!hasRole(['admin'])) {
    include 'views/errors/403.php';
    exit;
}

// Get departments for dropdown
$departments = UserHelper::getAllDepartments();

// Get locations for dropdown
$conn = getDbConnection();
$locationSql = "SELECT * FROM locations WHERE is_active = 1 ORDER BY name ASC";
$locationStmt = $conn->prepare($locationSql);
$locationStmt->execute();
$locations = $locationStmt->fetchAll();

// Include header
include 'includes/header.php';
?>
<link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/users.css">
<style>
    
</style>

<div class="content-header">
    <h1 class="page-title">Create User</h1>
    <div class="breadcrumbs">
        <a href="<?php echo BASE_URL; ?>" class="breadcrumb-item">Home</a>
        <a href="<?php echo BASE_URL; ?>/users" class="breadcrumb-item">Users</a>
        <span class="breadcrumb-item">Create</span>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">User Information</div>
        <a href="<?php echo BASE_URL; ?>/users" class="btn btn-sm btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>
    <div class="panel-body">
        <form action="<?php echo BASE_URL; ?>/users/create-process" method="POST" id="create-user-form">
            <!-- CSRF Token -->
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
            
            <div class="form-tabs">
                <div class="form-tab active" data-tab="account">Account Details</div>
                <div class="form-tab" data-tab="personal">Personal Information</div>
                <div class="form-tab" data-tab="role">Role & Permissions</div>
            </div>
            
            <div class="form-tab-content active" id="account-tab">
                <div class="form-section">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="username" class="required">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required pattern="[a-zA-Z0-9_]{3,20}" title="Username must be 3-20 characters and can only contain letters, numbers, and underscores">
                            <div class="form-text">3-20 characters, letters, numbers, and underscores only</div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="email" class="required">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="password" class="required">Password</label>
                            <div class="password-input">
                                <input type="password" class="form-control" id="password" name="password" required minlength="8">
                                <button type="button" class="password-toggle" tabindex="-1">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Minimum 8 characters</div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="confirm_password" class="required">Confirm Password</label>
                            <div class="password-input">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                                <button type="button" class="password-toggle" tabindex="-1">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="password-strength" id="password-strength">
                        <div class="password-strength-label">Password Strength: <span id="strength-text">Weak</span></div>
                        <div class="password-strength-meter">
                            <div class="password-strength-value" id="strength-value"></div>
                        </div>
                        <div class="password-requirements">
                            <div class="requirement" id="req-length"><i class="fas fa-circle"></i> At least 8 characters</div>
                            <div class="requirement" id="req-uppercase"><i class="fas fa-circle"></i> Uppercase letter</div>
                            <div class="requirement" id="req-lowercase"><i class="fas fa-circle"></i> Lowercase letter</div>
                            <div class="requirement" id="req-number"><i class="fas fa-circle"></i> Number</div>
                            <div class="requirement" id="req-special"><i class="fas fa-circle"></i> Special character</div>
                        </div>
                    </div>
                </div>
                <div class="form-buttons">
                    <button type="button" class="btn btn-primary next-tab">Next: Personal Information</button>
                </div>
            </div>
            
            <div class="form-tab-content" id="personal-tab">
                <div class="form-section">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="full_name" class="required">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="phone">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="department_id">Department</label>
                            <select class="form-control" id="department_id" name="department_id">
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $department): ?>
                                <option value="<?php echo $department['id']; ?>"><?php echo htmlspecialchars($department['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="job_title">Job Title</label>
                            <input type="text" class="form-control" id="job_title" name="job_title">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="location_id">Location</label>
                            <select class="form-control" id="location_id" name="location_id">
                                <option value="">Select Location</option>
                                <?php foreach ($locations as $location): ?>
                                <option value="<?php echo $location['id']; ?>"><?php echo htmlspecialchars($location['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-buttons">
                    <button type="button" class="btn btn-outline prev-tab">Previous</button>
                    <button type="button" class="btn btn-primary next-tab">Next: Role & Permissions</button>
                </div>
            </div>
            
            <div class="form-tab-content" id="role-tab">
                <div class="form-section">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="role" class="required">User Role</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="user">Regular User</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Administrator</option>
                            </select>
                            <div class="form-text">
                                <strong>User:</strong> Can browse, borrow, and return items<br>
                                <strong>Manager:</strong> Can manage items, process borrow requests, and view reports<br>
                                <strong>Administrator:</strong> Full system access including user management
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="is_active">Account Status</label>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Active Account</span>
                            </div>
                            <div class="form-text">Inactive accounts cannot log in</div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> User permissions are determined by their role. You can modify individual permissions after creating the user.
                    </div>
                </div>
                <div class="form-buttons">
                    <button type="button" class="btn btn-outline prev-tab">Previous</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Create User
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab Navigation
    const tabs = document.querySelectorAll('.form-tab');
    const tabContents = document.querySelectorAll('.form-tab-content');
    const nextButtons = document.querySelectorAll('.next-tab');
    const prevButtons = document.querySelectorAll('.prev-tab');
    
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => {
            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            tabs.forEach(t => {
                t.classList.remove('active');
            });
            
            // Show the selected tab content
            tabContents[index].classList.add('active');
            
            // Add active class to the clicked tab
            tab.classList.add('active');
        });
    });
    
    nextButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Find the currently active tab index
            let activeIndex = Array.from(tabs).findIndex(tab => tab.classList.contains('active'));
            
            // If there's a next tab, activate it
            if (activeIndex < tabs.length - 1) {
                tabs[activeIndex + 1].click();
            }
        });
    });
    
    prevButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Find the currently active tab index
            let activeIndex = Array.from(tabs).findIndex(tab => tab.classList.contains('active'));
            
            // If there's a previous tab, activate it
            if (activeIndex > 0) {
                tabs[activeIndex - 1].click();
            }
        });
    });
    
    // Form validation
    const form = document.getElementById('create-user-form');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    form.addEventListener('submit', function(event) {
        if (password.value !== confirmPassword.value) {
            event.preventDefault();
            alert('Passwords do not match!');
            confirmPassword.focus();
        }
    });
    
    // Password toggle
    const toggleButtons = document.querySelectorAll('.password-toggle');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Password strength meter
    password.addEventListener('input', checkPasswordStrength);
    
    function checkPasswordStrength() {
        const passwordValue = password.value;
        const strengthValue = document.getElementById('strength-value');
        const strengthText = document.getElementById('strength-text');
        
        // Check requirements
        const requirements = {
            length: passwordValue.length >= 8,
            uppercase: /[A-Z]/.test(passwordValue),
            lowercase: /[a-z]/.test(passwordValue),
            number: /[0-9]/.test(passwordValue),
            special: /[^A-Za-z0-9]/.test(passwordValue)
        };
        
        // Update requirement indicators
        document.getElementById('req-length').classList.toggle('met', requirements.length);
        document.getElementById('req-uppercase').classList.toggle('met', requirements.uppercase);
        document.getElementById('req-lowercase').classList.toggle('met', requirements.lowercase);
        document.getElementById('req-number').classList.toggle('met', requirements.number);
        document.getElementById('req-special').classList.toggle('met', requirements.special);
        
        // Calculate strength percentage
        const metCount = Object.values(requirements).filter(Boolean).length;
        const strengthPercentage = (metCount / 5) * 100;
        
        // Update strength meter
        strengthValue.style.width = strengthPercentage + '%';
        
        // Update strength text
        if (strengthPercentage <= 20) {
            strengthText.textContent = 'Very Weak';
            strengthValue.className = 'password-strength-value strength-very-weak';
        } else if (strengthPercentage <= 40) {
            strengthText.textContent = 'Weak';
            strengthValue.className = 'password-strength-value strength-weak';
        } else if (strengthPercentage <= 60) {
            strengthText.textContent = 'Medium';
            strengthValue.className = 'password-strength-value strength-medium';
        } else if (strengthPercentage <= 80) {
            strengthText.textContent = 'Strong';
            strengthValue.className = 'password-strength-value strength-strong';
        } else {
            strengthText.textContent = 'Very Strong';
            strengthValue.className = 'password-strength-value strength-very-strong';
        }
    }
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?>