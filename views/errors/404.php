<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Base styles */
        :root {
            --primary: #6d28d9;
            --primary-light: #8b5cf6;
            --primary-dark: #5b21b6;
            --primary-50: #f5f3ff;
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
            0% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(5deg);
            }

            100% {
                transform: translateY(0px) rotate(0deg);
            }
        }

        @keyframes floatReverse {
            0% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-15px) rotate(-5deg);
            }

            100% {
                transform: translateY(0px) rotate(0deg);
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        /* Background decorative elements */
        .shape {
            position: absolute;
            border-radius: 50%;
            background: var(--primary-50);
            z-index: -1;
        }

        .shape-1 {
            width: 300px;
            height: 300px;
            top: -100px;
            right: -100px;
            opacity: 0.5;
            animation: float 8s ease-in-out infinite;
        }

        .shape-2 {
            width: 200px;
            height: 200px;
            bottom: -50px;
            left: -50px;
            opacity: 0.4;
            animation: floatReverse 7s ease-in-out infinite;
        }

        .shape-3 {
            width: 150px;
            height: 150px;
            top: 50%;
            right: 10%;
            opacity: 0.3;
            animation: float 9s ease-in-out infinite;
        }

        .shape-4 {
            width: 80px;
            height: 80px;
            bottom: 20%;
            left: 20%;
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
            0% {
                opacity: 0;
                transform: translateY(30px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-code-container {
            position: relative;
            height: 200px;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        /* Individual digit animations */
        @keyframes digitSlideIn {
            0% {
                transform: translateY(150px);
                opacity: 0;
            }

            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            20%,
            60% {
                transform: translateX(-10px);
            }

            40%,
            80% {
                transform: translateX(10px);
            }
        }

        .error-code {
            font-size: 8rem;
            font-weight: 800;
            color: var(--primary);
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
            text-shadow: 4px 4px 0 var(--primary-light);
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
            color: var(--primary-dark);
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

        .error-illustration {
            margin-bottom: 2rem;
            opacity: 0;
            animation: fadeIn 0.5s ease-in-out forwards, pulse 4s ease-in-out infinite;
            animation-delay: 1.2s;
        }

        .search-animation {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto;
        }

        .search-icon {
            font-size: 5rem;
            color: var(--primary-light);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-image: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        }

        /* Magnifying glass handle */
        .search-handle {
            position: absolute;
            bottom: 15px;
            right: 15px;
            width: 16px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            transform: rotate(45deg);
            border-radius: 10px;
            transform-origin: top center;
            animation: searchHandle 3s ease-in-out infinite;
        }

        @keyframes searchHandle {

            0%,
            100% {
                transform: rotate(45deg);
            }

            50% {
                transform: rotate(30deg);
            }
        }

        /* Animated dots */
        .dots-container {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 1rem;
            opacity: 0;
            animation: fadeIn 0.5s ease-in-out forwards;
            animation-delay: 1.4s;
        }

        .dot {
            width: 12px;
            height: 12px;
            background-color: var(--primary-light);
            border-radius: 50%;
            animation: dotPulse 1.5s ease-in-out infinite;
        }

        .dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes dotPulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.5;
            }

            50% {
                transform: scale(1.5);
                opacity: 1;
            }
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
            border-top: 1px solid var(--gray-200);
            opacity: 0;
            animation: fadeIn 0.5s ease-in-out forwards;
            animation-delay: 1.8s;
        }

        .suggestion-container {
            opacity: 0;
            animation: fadeIn 0.5s ease-in-out forwards;
            animation-delay: 2s;
        }

        .suggestion-list {
            list-style-type: none;
            text-align: left;
            max-width: 400px;
            margin: 0 auto;
        }

        .suggestion-list li {
            margin-bottom: 0.75rem;
            padding-left: 1.5rem;
            position: relative;
            transform: translateX(-20px);
            opacity: 0;
            animation: slideRight 0.5s ease-out forwards;
        }

        @keyframes slideRight {
            0% {
                transform: translateX(-20px);
                opacity: 0;
            }

            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .suggestion-list li:nth-child(1) {
            animation-delay: 2.1s;
        }

        .suggestion-list li:nth-child(2) {
            animation-delay: 2.2s;
        }

        .suggestion-list li:nth-child(3) {
            animation-delay: 2.3s;
        }

        .suggestion-list li:nth-child(4) {
            animation-delay: 2.4s;
        }

        .suggestion-list li::before {
            content: "•";
            color: var(--primary);
            font-weight: bold;
            font-size: 1.25rem;
            position: absolute;
            left: 0;
            top: -0.125rem;
        }

        .suggestion-list a {
            color: var(--primary);
            text-decoration: none;
            position: relative;
        }

        .suggestion-list a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--primary);
            transition: width 0.3s ease;
        }

        .suggestion-list a:hover::after {
            width: 100%;
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

        <div class="error-content">
            <div class="error-code-container">
                <div class="error-code">
                    <span class="error-digit">4</span>
                    <span class="error-digit">0</span>
                    <span class="error-digit">4</span>
                </div>
            </div>

            <h1 class="error-title">Page Not Found</h1>
            <p class="error-message">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>

            <div class="error-illustration">
                <div class="search-animation">
                    <div class="search-handle"></div>
                    <i class="fas fa-search search-icon"></i>
                </div>

                <div class="dots-container">
                    <div class="dot"></div>
                    <div class="dot"></div>
                    <div class="dot"></div>
                </div>
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

            <div class="suggestion-container">
                <h2 style="font-size: 1.25rem; margin-bottom: 1rem; color: var(--gray-800);">You might want to try:</h2>
                <ul class="suggestion-list">
                    <!-- <li><a href="<?php echo BASE_URL; ?>/maintenance">View Maintenance Requests</a></li> -->
                    <li><a href="<?php echo BASE_URL; ?>/items">Browse Inventory Items</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/dashboard">Go to Dashboard</a></li>
                    <li>Check the URL for typing errors</li>
                </ul>
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