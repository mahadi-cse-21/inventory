<?php

/**
 * Register View
 * 
 * This file handles the registration form and account creation
 */

// Set page title
$pageTitle = 'Register';

// Hide sidebar and set body class
$hideSidebar = true;
$bodyClass = 'auth-page';
$hideTopbar = true;
$hideFooter = true;



// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    validateCsrfToken();

    // Get form data
    $userData = [
        'email' => cleanInput($_POST['email']),
        'password' => $_POST['password'], // Don't clean passwords
        'name' => cleanInput($_POST['name']),
        'phone' => cleanInput($_POST['phone'] ?? ''),
        'department' => isset($_POST['department']) ? (int)$_POST['department'] : null,
        
        'role' => 'user', // Default role for new registrations
        
    ];

    // Validate form data
    $errors = [];



    if (empty($userData['password'])) {
        $errors[] = 'Password is required';
    } elseif (strlen($userData['password']) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    }

    if (empty($userData['email'])) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }

    if (empty($userData['name'])) {
        $errors[] = 'Full name is required';
    }

    // Check password confirmation
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $errors[] = 'Passwords do not match';
    }

    // If no errors, attempt registration
    if (empty($errors)) {
        $result = AuthHelper::register($userData);

        if ($result['success']) {
            // Set success message and redirect to login
            setFlashMessage('Registration successful! Your account will be reviewed by an administrator.', 'success');
            redirect(BASE_URL . '/auth/login');
        } else {
            $errors[] = $result['message'];
        }
    }
}

// Include custom CSS for auth pages
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME; ?></title>
    <!-- External CSS -->
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/inventory-styles.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <?php if (isset($extraCSS)): ?>
        <?php foreach ($extraCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo ASSETS_URL . '/css/' . $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <style>
        /* Override styles for auth pages */
        body.auth-page {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 0;
            margin: 0;
            /* background: linear-gradient(135deg,rgb(255, 255, 255) 0%, #2575fc 100%); */
        }

        body.auth-page .container {
            display: block;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            padding: 2rem;
        }

        body.auth-page .main {
            margin-left: 0;
            padding: 0;
            background: transparent;
        }

        body.auth-page .content {
            padding: 0;
        }

        .auth-container {
            width: 100%;
        }

        .auth-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .auth-header {
            padding: 2.5rem 1.5rem;
            text-align: center;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            position: relative;
        }

        .auth-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at bottom right, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0) 70%);
        }

        .auth-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            position: relative;
        }

        .auth-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            position: relative;
        }

        .auth-body {
            padding: 2.5rem 2rem;
        }

        .auth-footer {
            padding: 1.5rem;
            text-align: center;
            border-top: 1px solid var(--gray-200);
            font-size: 0.9rem;
            color: var(--gray-600);
            background-color: var(--gray-50);
        }

        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition-colors);
        }

        .auth-footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Flash message styling */
        .alert {
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body class="auth-page">
    <div class="container">
        <div class="main">
            <!-- Flash Messages -->
            <?php $flashMessage = getFlashMessage(); ?>
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo $flashMessage['type']; ?>" data-auto-dismiss>
                    <?php echo $flashMessage['message']; ?>
                    <button class="close">&times;</button>
                </div>
            <?php endif; ?>

            <div class="content">
                <div class="auth-container">
                    <div class="auth-card">
                        <div class="auth-header">
                            <h1 class="auth-title">Inventory<span style="color: #f0f0f0;">Pro</span></h1>
                            <div class="auth-subtitle">Create a new account</div>
                        </div>

                        <div class="auth-body">
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <ul style="margin: 0; padding-left: 1.5rem;">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo $error; ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="<?php echo BASE_URL; ?>/auth/register">
                                <!-- CSRF Token -->
                                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                                <div class="form-group">
                                    <label class="form-label" for="full_name">Full Name</label>
                                    <input type="text" id="full_name" name="name" class="form-control"
                                        value="<?php echo isset($userData['name']) ? htmlspecialchars($userData['name']) : ''; ?>" required>
                                </div>
                                <div class="form-row">
                                    <!-- <div class="form-col">
                                        <div class="form-group">
                                            <label class="form-label" for="email">email</label>
                                            <input type="hidden" id="email" value="user" name="username" class="form-control" 
                                                value="<?php echo isset($userData['email']) ? htmlspecialchars($userData['email']) : ''; ?>" required>
                                        </div>
                                    </div> -->

                                    <div class="form-col">
                                        <div class="form-group">
                                            <label class="form-label" for="email">Email</label>
                                            <input type="email" id="email" name="email" class="form-control"
                                                value="<?php echo isset($userData['email']) ? htmlspecialchars($userData['email']) : ''; ?>" required>
                                        </div>
                                    </div>
                                </div>



                                <div class="form-row">
                                    <div class="form-col">
                                        <div class="form-group">
                                            <label class="form-label" for="phone">Phone Number</label>
                                            <input type="tel" id="phone" name="phone" class="form-control"
                                                value="<?php echo isset($userData['phone']) ? htmlspecialchars($userData['phone']) : ''; ?>">
                                        </div>
                                    </div>


                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="department">Department</label>
                                    <select id="department" name="department" class="form-control">
                                        <option value="">Select Department</option>
                                        <option value="cse">Computer Science and Engineering</option>
                                        <option value="mat">Mathematics</option>
                                        <option value="acc">Accounting</option>
                                        <option value="eng">English</option>

                                    </select>
                                </div>

                                <div class="form-row">
                                    <div class="form-col">
                                        <div class="form-group">
                                            <label class="form-label" for="password">Password</label>
                                            <input type="password" id="password" name="password" class="form-control" required>
                                            <span class="form-hint">At least 8 characters</span>
                                        </div>
                                    </div>

                                    <div class="form-col">
                                        <div class="form-group">
                                            <label class="form-label" for="confirm_password">Confirm Password</label>
                                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary w-full">
                                        <i class="fas fa-user-plus btn-icon"></i> Register
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="auth-footer">
                            Already have an account? <a href="<?php echo BASE_URL; ?>/auth/login">Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?php echo ASSETS_URL; ?>/js/main.js"></script>
    <?php if (isset($extraJS)): ?>
        <?php foreach ($extraJS as $js): ?>
            <script src="<?php echo ASSETS_URL . '/js/' . $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>

</html>
<?php
// Skip including footer since we included a custom one
$skipFooter = true;
?>