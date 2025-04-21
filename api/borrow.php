<?php
/**
 * Borrow Requests API Endpoint
 * 
 * Provides borrow request data for AJAX requests and export functionality
 */

// Make sure user is logged in and has the right permissions
if (!AuthHelper::isAuthenticated() || !hasRole(['admin', 'manager'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Get action from request
$action = isset($_GET['action']) ? cleanInput($_GET['action']) : '';

// If action is export or no action specified but format is csv, handle export
if ($action === 'export' || (empty($action) && isset($_GET['format']) && $_GET['format'] === 'csv')) {
    // Build filters
    $filters = [
        'search' => isset($_GET['search']) ? cleanInput($_GET['search']) : '',
        'status' => isset($_GET['status']) ? cleanInput($_GET['status']) : '',
        'department_id' => isset($_GET['department']) ? (int)$_GET['department'] : '',
        'date_from' => isset($_GET['date_from']) ? cleanInput($_GET['date_from']) : '',
        'date_to' => isset($_GET['date_to']) ? cleanInput($_GET['date_to']) : ''
    ];
    
    // Get all borrow requests without pagination
    $requestsData = BorrowHelper::getAllBorrowRequests(1, 1000, $filters);
    $requests = $requestsData['requests'];
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="borrow-requests-' . date('Y-m-d') . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8 encoding
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write header row
    fputcsv($output, [
        'Request ID',
        'Requester',
        'Department',
        'Purpose',
        'Project/Event',
        'Borrow Date',
        'Return Date',
        'Status',
        'Approved By',
        'Approval Date',
        'Checked Out By',
        'Checkout Date',
        'Returned By',
        'Return Date',
        'Notes'
    ]);
    
    // Write data rows
    foreach ($requests as $request) {
        fputcsv($output, [
            $request['request_id'],
            $request['requester_name'],
            $request['department_name'] ?? 'Not Set',
            $request['purpose'],
            $request['project_name'] ?? '',
            $request['borrow_date'],
            $request['return_date'],
            ucfirst(str_replace('_', ' ', $request['status'])),
            $request['approver_name'] ?? '',
            $request['approved_at'] ?? '',
            $request['checkout_by_name'] ?? '',
            $request['checked_out_at'] ?? '',
            $request['returned_by_name'] ?? '',
            $request['returned_at'] ?? '',
            $request['notes'] ?? ''
        ]);
    }
    
    // Close output stream
    fclose($output);
    exit;
}

// If it's not an export, handle other API requests
header('Content-Type: application/json');

switch ($action) {
    case 'get':
        // Get a single borrow request
        $requestId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$requestId) {
            http_response_code(400);
            echo json_encode(['error' => 'Request ID is required']);
            exit;
        }
        
        $request = BorrowHelper::getBorrowRequestById($requestId);
        
        if (!$request) {
            http_response_code(404);
            echo json_encode(['error' => 'Borrow request not found']);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'request' => $request
        ]);
        break;
        
    case 'list':
    default:
        // Get page number
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : ITEMS_PER_PAGE;
        
        // Build filters
        $filters = [
            'search' => isset($_GET['search']) ? cleanInput($_GET['search']) : '',
            'status' => isset($_GET['status']) ? cleanInput($_GET['status']) : '',
            'department_id' => isset($_GET['department']) ? (int)$_GET['department'] : '',
            'date_from' => isset($_GET['date_from']) ? cleanInput($_GET['date_from']) : '',
            'date_to' => isset($_GET['date_to']) ? cleanInput($_GET['date_to']) : ''
        ];
        
        // Get requests with pagination
        $requestsData = BorrowHelper::getAllBorrowRequests($page, $limit, $filters);
        
        echo json_encode([
            'success' => true,
            'requests' => $requestsData['requests'],
            'pagination' => $requestsData['pagination']
        ]);
        break;
}