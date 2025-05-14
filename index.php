<?php
/**
 * InventoryPro - Main Entry Point
 * 
 * This file handles all requests and routes them to the appropriate views
 */

// Initialize the application
require_once 'init.php';

// Check if user is logged in, redirect to login if not (except for login and register pages)
$allowedPaths = ['auth/login', 'auth/register', 'auth/forgot-password', 'auth/reset-password'];
$requestPath = isset($_GET['path']) ? $_GET['path'] : '';
$pathSegments = explode('/', trim($requestPath, '/'));

if (!AuthHelper::isAuthenticated() && (!isset($pathSegments[0]) || !in_array($pathSegments[0] . '/' . ($pathSegments[1] ?? ''), $allowedPaths))) {
    if ($requestPath !== 'auth/login') { // Prevent redirect loop
        redirect(BASE_URL . '/auth/login');
        exit;
    }
}

// Define routes and their corresponding view files
$routes = [
    // Dashboard
    'dashboard/index' => 'views/dashboard.php',
    
    // Authentication
    'auth/login' => 'views/auth/login.php',
    'auth/logout' => 'views/auth/logout.php',
    'auth/register' => 'views/auth/register.php',
    'auth/forgot-password' => 'views/auth/forgot_password.php',
    'auth/reset-password' => 'views/auth/reset_password.php',
    
    // User Profile
    'users/profile' => 'views/users/profile.php',
    'users/update-profile' => 'views/users/update_profile.php',
    'users/change-password' => 'views/users/change_password.php',
    
    // User Management (Admin)
    'users/index' => 'views/users/index.php',
    'users/create' => 'views/users/create.php',
    'users/edit' => 'views/users/edit.php',
    'users/view' => 'views/users/view.php',
    'users/delete' => 'views/users/delete.php',
    'users/permissions' => 'views/users/permissions.php',

    // Item Management
    'items/index' => 'views/items/index.php',
    'items/create' => 'views/items/create.php',
    'items/edit' => 'views/items/edit.php',
    'items/view' => 'views/items/view.php',
    'items/delete' => 'views/items/delete.php',
    'items/browse' => 'views/items/browse.php',
    'items/search' => 'views/items/search.php',


    
    // Borrow Management
    'borrow/index' => 'views/borrow/index.php',
    'borrow/create' => 'views/borrow/create.php',
    'borrow/my-items' => 'views/borrow/my_items.php',
    'borrow/history' => 'views/borrow/history.php',
    'borrow/return' => 'views/borrow/return.php',
    'borrow/requests' => 'views/borrow/requests.php',
    'borrow/cancel' => 'views/borrow/cancel.php',
    // 'borrow/view-request' => 'views/borrow/view_request.php',
    'borrow/process-request' => 'views/borrow/process_request.php',
    'borrow/view' => 'views/borrow/view.php', // Add this line to support /borrow/view?id=X
    'borrow/view-request' => 'views/borrow/view.php', // Keep this for backward compatibility
    
    // Location Management
    'locations/index' => 'views/locations/index.php',
    'locations/create' => 'views/locations/create.php',
    'locations/edit' => 'views/locations/edit.php',
    'locations/view' => 'views/locations/view.php',
    'locations/delete' => 'views/locations/delete.php',
    'locations/transfer' => 'views/locations/transfer.php',
    
    // Maintenance Management
    // 'maintenance/index' => 'views/maintenance/index.php',
    // 'maintenance/create' => 'views/maintenance/create.php',
    // 'maintenance/edit' => 'views/maintenance/edit.php',
    // 'maintenance/view' => 'views/maintenance/view.php',
    // 'maintenance/complete' => 'views/maintenance/complete.php',
    // 'maintenance/damage-reports/create' => 'views/maintenance/damage_reports_create.php',
    // 'maintenance/damage-reports' => 'views/maintenance/damage_reports.php',
    
    // Settings
    'settings/index' => 'views/settings/index.php',
    'settings/system' => 'views/settings/system.php',
    'settings/categories' => 'views/settings/categories.php',
    'settings/suppliers' => 'views/settings/suppliers.php',
    
    // API Endpoints
    'api/items' => 'api/items.php',
    'api/users' => 'api/users.php',
    'api/locations' => 'api/locations.php',
    'api/borrow' => 'api/borrow.php',
    
    // Error pages
    '404' => 'views/errors/404.php',
    '403' => 'views/errors/403.php',
    '500' => 'views/errors/500.php',
];


// Add these routes to the $routes array in index.php

// Maintenance Management
$routes['maintenance/index'] = 'views/maintenance/index.php';
$routes['maintenance/create'] = 'views/maintenance/create.php';
$routes['maintenance/edit'] = 'views/maintenance/edit.php';
$routes['maintenance/view'] = 'views/maintenance/view.php';
$routes['maintenance/assign'] = 'views/maintenance/assign.php';
$routes['maintenance/start'] = 'views/maintenance/start.php';
$routes['maintenance/complete'] = 'views/maintenance/complete.php';
$routes['maintenance/verify'] = 'views/maintenance/verify.php';
$routes['maintenance/cancel'] = 'views/maintenance/cancel.php';

// Damage Reports
$routes['maintenance/damage-reports'] = 'views/maintenance/damage_reports.php';
$routes['maintenance/damage-reports/create'] = 'views/maintenance/damage_report_create.php';
$routes['maintenance/damage-reports/view'] = 'views/maintenance/damage_report_view.php';
$routes['maintenance/damage-reports/edit'] = 'views/maintenance/damage_report_edit.php';
$routes['maintenance/damage-reports/resolve'] = 'views/maintenance/damage_report_resolve.php';

// API Endpoints for Maintenance
$routes['api/maintenance'] = 'api/maintenance.php';

// Routing Logic
$path = isset($_GET['path']) ? $_GET['path'] : '';
$segments = explode('/', trim($path, '/'));

// Default controller and action
$controller = !empty($segments[0]) ? $segments[0] : 'dashboard';
$action = isset($segments[1]) ? $segments[1] : 'index';
$param = isset($segments[2]) ? $segments[2] : null;

// Fallback for invalid routes
if (!isset($routes["$controller/$action"])) {
    include 'views/errors/404.php';
    exit;
}

// Construct the route key
$routeKey = $controller . '/' . $action;

// Check if route exists
if (isset($routes[$routeKey])) {
    $viewFile = $routes[$routeKey];
    
    // Check if file exists
    if (file_exists($viewFile)) {
        // Check permissions for admin-only routes
        if (strpos($routeKey, 'users/') === 0 && $action !== 'profile' && $action !== 'update-profile' && $action !== 'change-password') {
            if (!hasRole('admin')) {
                include 'views/errors/403.php';
                exit;
            }
        }
        
        // Check permissions for manager-only routes
        if (strpos($routeKey, 'settings/') === 0 || $routeKey === 'borrow/requests') {
            if (!hasRole(['admin', 'manager'])) {
                include 'views/errors/403.php';
                exit;
            }
        }
        
        // Include the appropriate view file
        include $viewFile;
    } else {
        // View file not found
        include 'views/errors/404.php';
    }
} else {
    // Route not found
    include 'views/errors/404.php';
}