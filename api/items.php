<?php
/**
 * Items API Endpoint
 * 
 * Provides item data for AJAX requests in JSON format
 */

// Make sure this is an AJAX request
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
          isset($_GET['format']);

if (!$isAjax) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// Set content type to JSON
header('Content-Type: application/json');

// Get request parameters
$action = isset($_GET['action']) ? cleanInput($_GET['action']) : 'list';
$format = isset($_GET['format']) ? cleanInput($_GET['format']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : ITEMS_PER_PAGE;

// Build filters from query parameters
$filters = [
    'search' => isset($_GET['search']) ? cleanInput($_GET['search']) : '',
    'category_id' => isset($_GET['category']) ? (int)$_GET['category'] : '',
    'location_id' => isset($_GET['location']) ? (int)$_GET['location'] : '',
    'status' => isset($_GET['status']) ? cleanInput($_GET['status']) : '',
    'is_active' => 1
];

// Handle different actions
switch ($action) {
    case 'get':
        // Get a single item
        $itemId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$itemId) {
            http_response_code(400);
            echo json_encode(['error' => 'Item ID is required']);
            exit;
        }
        
        $item = InventoryHelper::getItemById($itemId);
        
        if (!$item) {
            http_response_code(404);
            echo json_encode(['error' => 'Item not found']);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'item' => $item
        ]);
        break;
        
    case 'search':
        // Advanced search with autocomplete support
        $term = isset($_GET['term']) ? cleanInput($_GET['term']) : '';
        $filters['search'] = $term;
        
        // For autocomplete, we just need minimal item data
        $itemsResult = InventoryHelper::getAllItems(1, 10, $filters);
        $items = $itemsResult['items'];
        
        // Format for autocomplete
        $results = [];
        foreach ($items as $item) {
            $results[] = [
                'id' => $item['id'],
                'label' => $item['name'],
                'value' => $item['name'],
                'category' => $item['category_name'],
                'location' => $item['location_name'],
                'asset_id' => $item['asset_id']
            ];
        }
        
        echo json_encode($results);
        break;
        
    case 'list':
    default:
        // Get items with pagination
        $itemsResult = InventoryHelper::getAllItems($page, $limit, $filters);
        $items = $itemsResult['items'];
        $pagination = $itemsResult['pagination'];
        
        // If modal format, we need to minimize the data
        if ($format === 'modal') {
            $modalItems = [];
            foreach ($items as $item) {
                $modalItems[] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'category_name' => $item['category_name'],
                    'location_name' => $item['location_name'],
                    'asset_id' => $item['asset_id'],
                    'status' => $item['status'],
                    'tags' => array_slice($item['tags'], 0, 2) // Limit tags for performance
                ];
            }
            
            echo json_encode([
                'success' => true,
                'items' => $modalItems,
                'pagination' => $pagination
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'items' => $items,
                'pagination' => $pagination
            ]);
        }
        break;
}