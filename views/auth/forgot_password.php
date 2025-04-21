<?php
/**
 * Forgot Password View
 * 
 * This file handles the forgot password form and password reset request
 */

// Set page title
$pageTitle = 'Forgot Password';

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
    $email = cleanInput($_POST['email']);
    
    // Validate form data
    $errors = [];
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    // If no errors, attempt password reset
    if (empty($errors)) {
        $result = AuthHelper::resetPassword($email);
        
        if ($result['success']) {
            // In a real application, you would send an email with the reset link
            // For this demo, we'll just show the token (not secure for production!)
            
            // Set success message and redirect
            setFlashMessage('Password reset instructions have been sent to your email.', 'success');
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
            /* background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); */
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
            background: radial-gradient(circle at bottom right, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
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
                            <h1 class="auth-title">Forgot Password</h1>
                            <div class="auth-subtitle">Enter your email to reset your password</div>
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
                            
                            <form method="POST" action="<?php echo BASE_URL; ?>/auth/forgot-password">
                                <!-- CSRF Token -->
                                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                                
                                <div class="form-group">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" id="email" name="email" class="form-control"
                                           value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required autofocus>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary w-full">
                                        <i class="fas fa-paper-plane btn-icon"></i> Send Reset Link
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="auth-footer">
                            Remember your password? <a href="<?php echo BASE_URL; ?>/auth/login">Login</a>
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