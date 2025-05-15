<?php
include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - InventoryPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --primary-dark: #2980b9;
            --secondary-color: #2ecc71;
            --text-color: #333;
            --light-bg: #f9f9f9;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
            --sidebar-width: 250px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background-color: #2c3e50;
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .sidebar-header {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
        }
        
        .logo-accent {
            color: var(--secondary-color);
        }
        
        .nav-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
        }
        
        .sidebar-nav {
            padding: 20px 0;
        }
        
        .nav-section {
            margin-bottom: 25px;
        }
        
        .nav-section-title {
            padding: 0 20px;
            margin-bottom: 10px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 4px solid var(--secondary-color);
        }
        
        .nav-item.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 4px solid var(--secondary-color);
            font-weight: bold;
        }
        
        .nav-item-icon {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .account-section {
            margin-top: auto;
        }
        
        .logout-item {
            color: #e74c3c;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 30px;
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            font-size: 28px;
            color: var(--text-color);
            margin-bottom: 10px;
        }
        
        .welcome-message {
            color: #7f8c8d;
            font-size: 16px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 24px;
        }
        
        .stat-icon.borrowed {
            background-color: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }
        
        .stat-icon.overdue {
            background-color: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }
        
        .stat-icon.available {
            background-color: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }
        
        .stat-info h3 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-info p {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .recent-section {
            margin-bottom: 30px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .section-header h2 {
            font-size: 20px;
            color: var(--text-color);
        }
        
        .view-all {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .view-all:hover {
            text-decoration: underline;
        }
        
        .recent-table {
            width: 100%;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background-color: #f5f7fa;
            text-align: left;
            padding: 15px;
            color: #7f8c8d;
            font-weight: 500;
            border-bottom: 1px solid #e6e9ed;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #e6e9ed;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .status.borrowed {
            background-color: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }
        
        .status.returned {
            background-color: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }
        
        .status.overdue {
            background-color: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }
        
        .status.pending {
            background-color: rgba(243, 156, 18, 0.1);
            color: #f39c12;
        }
        
        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .action-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .recommendation-card {
            background-color: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--card-shadow);
        }
        
        .card-img {
            height: 160px;
            background-color: #e6e9ed;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: #95a5a6;
        }
        
        .card-content {
            padding: 20px;
        }
        
        .card-title {
            font-size: 18px;
            margin-bottom: 10px;
            color: var(--text-color);
        }
        
        .card-text {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .card-meta {
            display: flex;
            justify-content: space-between;
            color: #95a5a6;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .card-actions {
            display: flex;
            justify-content: flex-end;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                overflow: hidden;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
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
                <a href="#" class="nav-item active">
                    <div class="nav-item-icon">
                        <i class="fas fa-th-large"></i>
                    </div>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="nav-item">
                    <div class="nav-item-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <span>Browse Items</span>
                </a>
                <a href="#" class="nav-item">
                    <div class="nav-item-icon">
                        <i class="fas fa-hand-holding"></i>
                    </div>
                    <span>My Borrowed Items</span>
                </a>
                <a href="#" class="nav-item">
                    <div class="nav-item-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <span>Request History</span>
                </a>
            </div>

            <div class="nav-section account-section">
                <div class="nav-section-title">Account</div>
                <a href="#" class="nav-item">
                    <div class="nav-item-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <span>My Profile</span>
                </a>
                <a href="#" class="nav-item logout-item">
                    <div class="nav-item-icon">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1>Student Dashboard</h1>
            <p class="welcome-message">Welcome back, Alex! Here's an overview of your borrowed items.</p>
        </div>

        <!-- Stats Section -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon borrowed">
                    <i class="fas fa-hand-holding"></i>
                </div>
                <div class="stat-info">
                    <h3>3</h3>
                    <p>Currently Borrowed Items</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon overdue">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-info">
                    <h3>1</h3>
                    <p>Overdue Items</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon available">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3>12</h3>
                    <p>Available to Borrow</p>
                </div>
            </div>
        </div>

        <!-- Recently Borrowed Section -->
        <div class="recent-section">
            <div class="section-header">
                <h2>Recently Borrowed Items</h2>
                <a href="#" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="recent-table">
                <table>
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Borrowed Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Graphing Calculator</td>
                            <td>May 3, 2025</td>
                            <td>May 17, 2025</td>
                            <td><span class="status borrowed">Borrowed</span></td>
                            <td><button class="action-btn">Return</button></td>
                        </tr>
                        <tr>
                            <td>Chemistry Lab Kit</td>
                            <td>April 28, 2025</td>
                            <td>May 12, 2025</td>
                            <td><span class="status overdue">Overdue</span></td>
                            <td><button class="action-btn">Return</button></td>
                        </tr>
                        <tr>
                            <td>Digital Camera</td>
                            <td>May 10, 2025</td>
                            <td>May 24, 2025</td>
                            <td><span class="status borrowed">Borrowed</span></td>
                            <td><button class="action-btn">Return</button></td>
                        </tr>
                        <tr>
                            <td>Reference Book</td>
                            <td>April 15, 2025</td>
                            <td>April 29, 2025</td>
                            <td><span class="status returned">Returned</span></td>
                            <td><button class="action-btn" disabled>Completed</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pending Requests Section -->
        <div class="recent-section">
            <div class="section-header">
                <h2>Pending Requests</h2>
                <a href="#" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="recent-table">
                <table>
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Request Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Physics Equipment Set</td>
                            <td>May 12, 2025</td>
                            <td><span class="status pending">Pending Approval</span></td>
                            <td><button class="action-btn">Cancel Request</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recommended Items -->
        <div class="recent-section">
            <div class="section-header">
                <h2>Recommended for Your Courses</h2>
                <a href="#" class="view-all">Browse All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="cards-grid">
                <div class="recommendation-card">
                    <div class="card-img">
                        <i class="fas fa-microscope"></i>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">Biology Microscope Kit</h3>
                        <p class="card-text">Complete kit with slides and supplies for BIO201.</p>
                        <div class="card-meta">
                            <span><i class="fas fa-check-circle"></i> Available: 5</span>
                            <span><i class="fas fa-clock"></i> 14 days max</span>
                        </div>
                        <div class="card-actions">
                            <button class="action-btn">Request Item</button>
                        </div>
                    </div>
                </div>
                <div class="recommendation-card">
                    <div class="card-img">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">Raspberry Pi Starter Kit</h3>
                        <p class="card-text">Complete kit for your CS332 IoT project.</p>
                        <div class="card-meta">
                            <span><i class="fas fa-check-circle"></i> Available: 3</span>
                            <span><i class="fas fa-clock"></i> 30 days max</span>
                        </div>
                        <div class="card-actions">
                            <button class="action-btn">Request Item</button>
                        </div>
                    </div>
                </div>
                <div class="recommendation-card">
                    <div class="card-img">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">Advanced Statistics Textbook</h3>
                        <p class="card-text">Required reading for MATH350.</p>
                        <div class="card-meta">
                            <span><i class="fas fa-check-circle"></i> Available: 2</span>
                            <span><i class="fas fa-clock"></i> 21 days max</span>
                        </div>
                        <div class="card-actions">
                            <button class="action-btn">Request Item</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.querySelector('.nav-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('mobile-show');
        });
    </script>
</body>
</html>