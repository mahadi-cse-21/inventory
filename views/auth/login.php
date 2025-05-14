<?php
/**
 * Login View
 * 
 * This file handles the login form and authentication
 */

// Set page title
$pageTitle = 'Login';

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
    $password = $_POST['password']; // Don't clean passwords
    $rememberMe = isset($_POST['remember_me']);
    
    // Validate form data
    $errors = [];
    
    if (empty($email)) {
        $errors[] = 'email is required';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    
    // If no errors, attempt login
    if (empty($errors)) {
        $result = AuthHelper::login($email, $password);
        
        if ($result['success']) {
            // Set remember me cookie if checked
            if ($rememberMe) {
                $token = bin2hex(random_bytes(32));
                $userId = $result['user']['id'];
                $expiry = time() + (86400 * 30); // 30 days
                
                // Store token in database
                $conn = getDbConnection();
                $sql = "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, FROM_UNIXTIME(?))";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$userId, password_hash($token, PASSWORD_DEFAULT), $expiry]);
                
                // Set cookie
            }
            
            // Set success message and redirect
            setFlashMessage('You have been logged in successfully', 'success');
            redirect(BASE_URL . '/dashboard');
        } else {
            $errors[] = $result['message'];
        }
    }
}

// Include header - but we'll modify it to include our custom styles
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - InventoryPro' : 'InventoryPro'; ?></title>
    <!-- Include your existing CSS files here -->
    <?php if (isset($headerStyles)): ?>
        <?php echo $headerStyles; ?>
    <?php endif; ?>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-hover: #3a56d4;
            --secondary-color: #f0f0f0;
            --text-color: #333;
            --error-color: #dc3545;
            --success-color: #28a745;
            --border-color: #e0e0e0;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', 'Roboto', sans-serif;
        }

        body.auth-page {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-color);
        }

        .auth-page {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .auth-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .auth-header {
            background-color: var(--primary-color);
            padding: 30px 20px;
            text-align: center;
            color: white;
        }

        .auth-title {
            font-size: 28px;
            font-weight: 700;
        }

        .auth-body {
            padding: 30px 25px;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.2);
            color: var(--error-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .form-check {
            display: flex;
            align-items: center;
        }

        .form-check-input {
            margin-right: 8px;
            width: 16px;
            height: 16px;
            accent-color: var(--primary-color);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-primary:active {
            transform: translateY(1px);
        }

        .w-full {
            width: 100%;
        }

        .btn-icon {
            margin-right: 8px;
        }

        .mt-4 {
            margin-top: 24px;
        }

        .text-center {
            text-align: center;
        }

        .text-primary {
            color: var(--primary-color);
            text-decoration: none;
        }

        .text-primary:hover {
            text-decoration: underline;
        }

        .auth-footer {
            text-align: center;
            padding: 20px;
            background-color: #f9fafb;
            border-top: 1px solid var(--border-color);
        }

        .auth-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .auth-container {
                padding: 10px;
            }
            
            .auth-body {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body class="<?php echo isset($bodyClass) ? $bodyClass : ''; ?>">

<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Inventory<span style="color: #f0f0f0;">Pro</span></h1>
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
                
                <form method="POST" action="<?php echo BASE_URL; ?>/auth/login">
                    <!-- CSRF Token -->
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                    
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input type="text" id="email" name="email" class="form-control" 
                               value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" id="remember_me" name="remember_me" class="form-check-input">
                            <label for="remember_me" class="form-check-label">Remember me</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary w-full">
                            <i class="fas fa-sign-in-alt btn-icon"></i> Login
                        </button>
                    </div>
                </form>
                
                <div class="mt-4 text-center">
                    <a href="<?php echo BASE_URL; ?>/auth/forgot-password" class="text-primary">Forgot your password?</a>
                </div>
            </div>
            
            <div class="auth-footer">
                Don't have an account? <a href="<?php echo BASE_URL; ?>/auth/register">Register</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Add any custom JavaScript here
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation or other enhancements can be added here
    });
</script>

</body>
</html>
<?php
// We've replaced the include footer line since we're using our own complete HTML structure
?>