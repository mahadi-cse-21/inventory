<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Base styles */
        :root {
            --primary: #6d28d9;
            --primary-light: #8b5cf6;
            --primary-dark: #5b21b6;
            --primary-50: #f5f3ff;
            --danger: #ef4444;
            --danger-light: #fecaca;
            --danger-dark: #b91c1c;
            --danger-50: #fef2f2;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-800);
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }
        
        /* Error page container */
        .error-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
        }
        
        /* Animation for floating shapes */
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        
        @keyframes floatReverse {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(-5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        /* Background decorative elements */
        .shape {
            position: absolute;
            border-radius: 50%;
            z-index: -1;
        }
        
        .shape-1 {
            width: 300px;
            height: 300px;
            top: -100px;
            right: -100px;
            background-color: var(--danger-50);
            opacity: 0.5;
            animation: float 8s ease-in-out infinite;
        }
        
        .shape-2 {
            width: 200px;
            height: 200px;
            bottom: -50px;
            left: -50px;
            background-color: var(--danger-50);
            opacity: 0.4;
            animation: floatReverse 7s ease-in-out infinite;
        }
        
        .shape-3 {
            width: 150px;
            height: 150px;
            top: 50%;
            right: 10%;
            background-color: var(--danger-50);
            opacity: 0.3;
            animation: float 9s ease-in-out infinite;
        }
        
        .shape-4 {
            width: 80px;
            height: 80px;
            bottom: 20%;
            left: 20%;
            background-color: var(--danger-50);
            opacity: 0.2;
            animation: floatReverse 6s ease-in-out infinite;
        }
        
        .error-content {
            max-width: 600px;
            width: 100%;
            text-align: center;
            animation: fadeIn 0.8s ease-in-out;
            position: relative;
            z-index: 1;
        }
        
        /* Content animation */
        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        
        .error-code-container {
            position: relative;
            height: 200px;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        /* Individual digit animations */
        @keyframes digitSlideIn {
            0% { transform: translateY(150px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-10px); }
            40%, 80% { transform: translateX(10px); }
        }
        
        .error-code {
            font-size: 8rem;
            font-weight: 800;
            color: var(--danger);
            line-height: 1;
            display: flex;
            justify-content: center;
        }
        
        .error-digit {
            display: inline-block;
            animation: digitSlideIn 0.6s ease-out forwards;
            opacity: 0;
            margin: 0 -5px;
            transform-origin: bottom;
            text-shadow: 4px 4px 0 var(--danger-light);
        }
        
        .error-digit:nth-child(1) {
            animation-delay: 0.2s;
        }
        
        .error-digit:nth-child(2) {
            animation-delay: 0.4s;
        }
        
        .error-digit:nth-child(3) {
            animation-delay: 0.6s;
        }
        
        .error-digit:hover {
            animation: shake 0.5s ease-in-out;
            color: var(--danger-dark);
            cursor: default;
        }
        
        .error-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 1rem;
            opacity: 0;
            animation: fadeIn 0.5s ease-in-out forwards;
            animation-delay: 0.8s;
        }
        
        .error-message {
            font-size: 1.125rem;
            color: var(--gray-600);
            margin-bottom: 2rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
            opacity: 0;
            animation: fadeIn 0.5s ease-in-out forwards;
            animation-delay: 1s;
        }
        
        /* Lock animation */
        .error-illustration {
            margin-bottom: 2rem;
            opacity: 0;
            animation: fadeIn 0.5s ease-in-out forwards, pulse 4s ease-in-out infinite;
            animation-delay: 1.2s;
        }
        
        .lock-animation {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto;
        }
        
        .lock-body {
            width: 60px;
            height: 45px;
            background: linear-gradient(135deg, var(--danger) 0%, var(--danger-dark) 100%);
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .lock-hole {
            width: 18px;
            height: 10px;
            background-color: rgba(0, 0, 0, 0.2);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
        }
        
        .lock-shackle {
            width: 40px;
            height: 40px;
            border: 10px solid var(--danger-dark);
            border-bottom: none;
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 20px 20px 0 0;
            animation: lockShackle 4s ease-in-out infinite;
        }
        
        @keyframes lockShackle {
            0%, 50%, 100% { transform: translateX(-50%) translateY(0); }
            25% { transform: translateX(-50%) translateY(5px); }
            75% { transform: translateX(-50%) translateY(-5px); }
        }
        
        .lock-shine {
            position: absolute;
            width: 15px;
            height: 15px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            top: 10px;
            left: 10px;
            animation: shine 3s ease-in-out infinite;
        }
        
        @keyframes shine {
            0%, 100% { opacity: 0.2; }
            50% { opacity: 0.8; }
        }
        
        /* Alert box animation */
        .alert {
            padding: 1.25rem;
            margin: 1.5rem 0;
            border-radius: 0.5rem;
            border-left: 4px solid var(--danger);
            background-color: var(--danger-50);
            color: var(--danger-dark);
            font-size: 0.95rem;
            text-align: left;
            position: relative;
            overflow: hidden;
            opacity: 0;
            animation: fadeIn 0.5s ease-in-out forwards;
            animation-delay: 1.4s;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.1);
        }
        
        .alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background-color: var(--danger);
            animation: pulse 2s ease-in-out infinite;
        }
        
        .alert-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }
        
        .alert-title i {
            margin-right: 0.5rem;
            animation: bounce 2s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        
        /* Buttons */
        .error-actions {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
            opacity: 0;
            animation: fadeIn 0.5s ease-in-out forwards;
            animation-delay: 1.6s;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            line-height: 1.5;
            border-radius: 0.375rem;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid transparent;
            position: relative;
            overflow: hidden;
        }
        
        .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }
        
        .btn:hover::after {
            animation: ripple 1s ease-out;
        }
        
        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            100% {
                transform: scale(100, 100);
                opacity: 0;
            }
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(109, 40, 217, 0.2);
        }
        
        .btn-outline {
            background-color: transparent;
            color: var(--gray-700);
            border-color: var(--gray-300);
        }
        
        .btn-outline:hover {
            background-color: var(--gray-100);
            border-color: var(--gray-400);
            color: var(--gray-900);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .btn-icon {
            margin-right: 0.5rem;
        }
        
        /* Additional elements */
        .divider {
            margin: 2rem 0;
            border: 0;
            height: 1px;
            background-image: linear-gradient(to right, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0));
            opacity: 0;
            animation: fadeIn 0.5s ease-in-out forwards;
            animation-delay: 1.8s;
        }
        
        .contact-info {
            opacity: 0;
            animation: fadeIn 0.5s ease-in-out forwards;
            animation-delay: 2s;
        }
        
        /* Typing animation for email */
        .typewriter {
            display: inline-block;
            overflow: hidden;
            white-space: nowrap;
            border-right: 3px solid var(--primary);
            color: var(--primary);
            font-weight: 600;
            animation: typing 3.5s steps(30, end) 2.2s forwards, blinkCursor 0.75s step-end infinite;
            width: 0;
        }
        
        @keyframes typing {
            from { width: 0 }
            to { width: 100% }
        }
        
        @keyframes blinkCursor {
            from, to { border-color: transparent }
            50% { border-color: var(--primary) }
        }
        
        /* Footer */
        .error-footer {
            text-align: center;
            padding: 1.5rem;
            color: var(--gray-500);
            font-size: 0.875rem;
            border-top: 1px solid var(--gray-200);
            opacity: 0;
            animation: fadeIn 0.5s ease-in-out forwards;
            animation-delay: 2.5s;
        }
        
        /* Shield decoration */
        .shield-decoration {
            position: absolute;
            font-size: 200px;
            color: var(--danger-50);
            opacity: 0.2;
            z-index: -1;
            animation: rotate 20s linear infinite;
        }
        
        .shield-top {
            top: 5%;
            right: 10%;
        }
        
        .shield-bottom {
            bottom: 5%;
            left: 10%;
            font-size: 150px;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 640px) {
            .error-code {
                font-size: 6rem;
            }
            
            .error-title {
                font-size: 1.5rem;
            }
            
            .error-message {
                font-size: 1rem;
            }
            
            .error-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .shape-1 {
                width: 200px;
                height: 200px;
            }
            
            .shape-2 {
                width: 150px;
                height: 150px;
            }
            
            .shape-3 {
                display: none;
            }
            
            .shield-decoration {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <!-- Animated background shapes -->
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
        
        <!-- Shield decorations -->
        <i class="fas fa-shield-alt shield-decoration shield-top"></i>
        <i class="fas fa-shield-alt shield-decoration shield-bottom"></i>
        
        <div class="error-content">
            <div class="error-code-container">
                <div class="error-code">
                    <span class="error-digit">4</span>
                    <span class="error-digit">0</span>
                    <span class="error-digit">3</span>
                </div>
            </div>
            
            <h1 class="error-title">Access Denied</h1>
            <p class="error-message">You don't have permission to access this page. Please contact your administrator if you believe this is an error.</p>
            
            <div class="error-illustration">
                <div class="lock-animation">
                    <div class="lock-shackle"></div>
                    <div class="lock-body">
                        <div class="lock-hole"></div>
                        <div class="lock-shine"></div>
                    </div>
                </div>
            </div>
            
            <div class="alert">
                <div class="alert-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    You need additional permissions
                </div>
                <p>Your current role doesn't have the necessary permissions to access this resource. This area may be restricted to administrators or managers only.</p>
            </div>
            
            <div class="error-actions">
                <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">
                    <i class="fas fa-home btn-icon"></i> Back to Home
                </a>
                <a href="javascript:history.back()" class="btn btn-outline">
                    <i class="fas fa-arrow-left btn-icon"></i> Go Back
                </a>
            </div>
            
            <hr class="divider">
            
            <div class="contact-info">
                <p style="color: var(--gray-600); font-size: 0.95rem; margin-bottom: 0.75rem;">
                    If you need access to this page, please contact:
                </p>
                <div class="typewriter-container" style="min-height: 24px;">
                    <span class="typewriter">system.admin@example.com</span>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="error-footer">
        <div>© <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</div>
        <div style="margin-top: 0.5rem;">Version <?php echo APP_VERSION; ?></div>
    </footer>
    
    <script>
        // Add interactive animations when digits are clicked
        document.querySelectorAll('.error-digit').forEach(digit => {
            digit.addEventListener('click', function() {
                this.style.animation = 'none';
                void this.offsetWidth; // Trigger reflow
                this.style.animation = 'shake 0.5s ease-in-out';
            });
        });
    </script>
</body>
</html>