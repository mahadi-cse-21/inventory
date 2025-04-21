<?php
/**
 * Item Search Results
 * 
 * This page displays search results for inventory items with advanced filtering options
 */

// Set page title
$pageTitle = 'Search Items';

// Get current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Get search query and filters
$searchQuery = isset($_GET['q']) ? cleanInput($_GET['q']) : '';
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : '';
$locationId = isset($_GET['location']) ? (int)$_GET['location'] : '';
$status = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
$condition = isset($_GET['condition']) ? cleanInput($_GET['condition']) : '';
$tag = isset($_GET['tag']) ? cleanInput($_GET['tag']) : '';
$supplierId = isset($_GET['supplier']) ? (int)$_GET['supplier'] : '';
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : '';
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : '';
$sort = isset($_GET['sort']) ? cleanInput($_GET['sort']) : 'name_asc';

// Build filters array for query
$filters = [
    'search' => $searchQuery,
    'category_id' => $categoryId,
    'location_id' => $locationId,
    'status' => $status,
    'condition_rating' => $condition,
    'tag' => $tag,
    'supplier_id' => $supplierId,
    'min_price' => $minPrice,
    'max_price' => $maxPrice,
    'is_active' => 1
];

// Get search results with pagination
$itemsResult = InventoryHelper::getAllItems($page, ITEMS_PER_PAGE, $filters);
$items = $itemsResult['items'];
$pagination = $itemsResult['pagination'];

// Get data for filter dropdowns
$categories = InventoryHelper::getAllCategories();
$locationResult = LocationHelper::getAllLocations(1, 100, ['is_active' => 1]);
$locations = $locationResult['locations'];
$suppliers = InventoryHelper::getAllSuppliers();

// Get popular tags for tag filtering
// $tags = InventoryHelper::getPopularTags(20);

// Include header
include 'includes/header.php';
?>

<div class="content-header">
    <div class="content-header-top">
        <h1 class="page-title">
            <?php if (!empty($searchQuery)): ?>
                Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"
            <?php else: ?>
                Browse Inventory
            <?php endif; ?>
        </h1>
        
        <?php if (hasRole(['admin', 'manager'])): ?>
        <a href="<?php echo BASE_URL; ?>/items/create" class="btn btn-primary">
            <i class="fas fa-plus btn-icon"></i> Add New Item
        </a>
        <?php endif; ?>
    </div>
    
    <div class="search-container main-search">
        <form action="<?php echo BASE_URL; ?>/items/search" method="GET" id="main-search-form">
            <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="q" class="search-input" placeholder="Search items by name, description, tags..." value="<?php echo htmlspecialchars($searchQuery); ?>" autofocus>
                <button type="submit" class="search-btn">Search</button>
            </div>
            
            <div class="search-options">
                <button type="button" class="advanced-search-toggle" id="toggle-advanced-search">
                    <i class="fas fa-sliders-h"></i> Advanced Search
                </button>
                
                <?php if (!empty($searchQuery) || !empty($categoryId) || !empty($locationId) || !empty($status) || !empty($tag)): ?>
                <a href="<?php echo BASE_URL; ?>/items/search" class="clear-search">
                    <i class="fas fa-times"></i> Clear Filters
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Advanced Search Panel -->
<div class="panel advanced-search-panel" id="advanced-search-panel" style="display: none;">
    <div class="panel-header">
        <div class="panel-title">Advanced Search</div>
        <button type="button" class="btn btn-sm btn-outline" id="close-advanced-search">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="panel-body">
        <form action="<?php echo BASE_URL; ?>/items/search" method="GET" id="advanced-search-form">
            <!-- Keep the main search query -->
            <input type="hidden" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>">
            
            <div class="advanced-search-grid">
                <div class="form-group">
                    <label for="category" class="form-label">Category</label>
                    <select name="category" id="category" class="form-control">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo ($categoryId == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                            <?php foreach ($category['children'] as $child): ?>
                                <option value="<?php echo $child['id']; ?>" <?php echo ($categoryId == $child['id']) ? 'selected' : ''; ?>>
                                    &nbsp;&nbsp;└ <?php echo htmlspecialchars($child['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="location" class="form-label">Location</label>
                    <select name="location" id="location" class="form-control">
                        <option value="">All Locations</option>
                        <?php foreach ($locations as $location): ?>
                            <option value="<?php echo $location['id']; ?>" <?php echo ($locationId == $location['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($location['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">Any Status</option>
                        <option value="available" <?php echo ($status === 'available') ? 'selected' : ''; ?>>Available</option>
                        <option value="borrowed" <?php echo ($status === 'borrowed') ? 'selected' : ''; ?>>Borrowed</option>
                        <option value="reserved" <?php echo ($status === 'reserved') ? 'selected' : ''; ?>>Reserved</option>
                        <option value="maintenance" <?php echo ($status === 'maintenance') ? 'selected' : ''; ?>>In Maintenance</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="condition" class="form-label">Condition</label>
                    <select name="condition" id="condition" class="form-control">
                        <option value="">Any Condition</option>
                        <option value="new" <?php echo ($condition === 'new') ? 'selected' : ''; ?>>New</option>
                        <option value="excellent" <?php echo ($condition === 'excellent') ? 'selected' : ''; ?>>Excellent</option>
                        <option value="good" <?php echo ($condition === 'good') ? 'selected' : ''; ?>>Good</option>
                        <option value="fair" <?php echo ($condition === 'fair') ? 'selected' : ''; ?>>Fair</option>
                        <option value="poor" <?php echo ($condition === 'poor') ? 'selected' : ''; ?>>Poor</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="supplier" class="form-label">Supplier</label>
                    <select name="supplier" id="supplier" class="form-control">
                        <option value="">All Suppliers</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?php echo $supplier['id']; ?>" <?php echo ($supplierId == $supplier['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($supplier['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="tag" class="form-label">Tag</label>
                    <select name="tag" id="tag" class="form-control">
                        <option value="">All Tags</option>
                        <?php foreach ($tags as $tagItem): ?>
                            <option value="<?php echo $tagItem['name']; ?>" <?php echo ($tag === $tagItem['name']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tagItem['name']); ?> (<?php echo $tagItem['count']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="price-filter">
                <label class="form-label">Price Range</label>
                <div class="price-inputs">
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="min_price" class="form-control" placeholder="Min" value="<?php echo !empty($minPrice) ? htmlspecialchars($minPrice) : ''; ?>" min="0" step="0.01">
                    </div>
                    <span class="price-range-separator">to</span>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="max_price" class="form-control" placeholder="Max" value="<?php echo !empty($maxPrice) ? htmlspecialchars($maxPrice) : ''; ?>" min="0" step="0.01">
                    </div>
                </div>
            </div>
            
            <div class="advanced-search-actions">
                <a href="<?php echo BASE_URL; ?>/items/search" class="btn btn-outline">Reset Filters</a>
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<!-- Results and Filters Display -->
<div class="search-results-container">
    <!-- Active Filters -->
    <?php if (!empty($searchQuery) || !empty($categoryId) || !empty($locationId) || !empty($status) || !empty($condition) || !empty($tag) || !empty($supplierId) || !empty($minPrice) || !empty($maxPrice)): ?>
    <div class="active-filters">
        <div class="active-filters-label">Active Filters:</div>
        <div class="active-filters-list">
            <?php if (!empty($searchQuery)): ?>
                <div class="filter-tag">
                    <span>Search: <?php echo htmlspecialchars($searchQuery); ?></span>
                    <a href="<?php echo BASE_URL; ?>/items/search?<?php echo http_build_query(array_merge($_GET, ['q' => null])); ?>" class="filter-remove">×</a>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($categoryId)): ?>
                <?php 
                $categoryName = "";
                foreach ($categories as $category) {
                    if ($category['id'] == $categoryId) {
                        $categoryName = $category['name'];
                        break;
                    }
                    foreach ($category['children'] as $child) {
                        if ($child['id'] == $categoryId) {
                            $categoryName = $child['name'];
                            break 2;
                        }
                    }
                }
                ?>
                <div class="filter-tag">
                    <span>Category: <?php echo htmlspecialchars($categoryName); ?></span>
                    <a href="<?php echo BASE_URL; ?>/items/search?<?php echo http_build_query(array_merge($_GET, ['category' => null])); ?>" class="filter-remove">×</a>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($locationId)): ?>
                <?php 
                $locationName = "";
                foreach ($locations as $location) {
                    if ($location['id'] == $locationId) {
                        $locationName = $location['name'];
                        break;
                    }
                }
                ?>
                <div class="filter-tag">
                    <span>Location: <?php echo htmlspecialchars($locationName); ?></span>
                    <a href="<?php echo BASE_URL; ?>/items/search?<?php echo http_build_query(array_merge($_GET, ['location' => null])); ?>" class="filter-remove">×</a>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($status)): ?>
                <div class="filter-tag">
                    <span>Status: <?php echo ucfirst(htmlspecialchars($status)); ?></span>
                    <a href="<?php echo BASE_URL; ?>/items/search?<?php echo http_build_query(array_merge($_GET, ['status' => null])); ?>" class="filter-remove">×</a>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($condition)): ?>
                <div class="filter-tag">
                    <span>Condition: <?php echo ucfirst(htmlspecialchars($condition)); ?></span>
                    <a href="<?php echo BASE_URL; ?>/items/search?<?php echo http_build_query(array_merge($_GET, ['condition' => null])); ?>" class="filter-remove">×</a>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($tag)): ?>
                <div class="filter-tag">
                    <span>Tag: <?php echo htmlspecialchars($tag); ?></span>
                    <a href="<?php echo BASE_URL; ?>/items/search?<?php echo http_build_query(array_merge($_GET, ['tag' => null])); ?>" class="filter-remove">×</a>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($supplierId)): ?>
                <?php 
                $supplierName = "";
                foreach ($suppliers as $supplier) {
                    if ($supplier['id'] == $supplierId) {
                        $supplierName = $supplier['name'];
                        break;
                    }
                }
                ?>
                <div class="filter-tag">
                    <span>Supplier: <?php echo htmlspecialchars($supplierName); ?></span>
                    <a href="<?php echo BASE_URL; ?>/items/search?<?php echo http_build_query(array_merge($_GET, ['supplier' => null])); ?>" class="filter-remove">×</a>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($minPrice) || !empty($maxPrice)): ?>
                <div class="filter-tag">
                    <span>Price: 
                        <?php if (!empty($minPrice) && !empty($maxPrice)): ?>
                            $<?php echo htmlspecialchars($minPrice); ?> - $<?php echo htmlspecialchars($maxPrice); ?>
                        <?php elseif (!empty($minPrice)): ?>
                            Min: $<?php echo htmlspecialchars($minPrice); ?>
                        <?php else: ?>
                            Max: $<?php echo htmlspecialchars($maxPrice); ?>
                        <?php endif; ?>
                    </span>
                    <a href="<?php echo BASE_URL; ?>/items/search?<?php echo http_build_query(array_merge($_GET, ['min_price' => null, 'max_price' => null])); ?>" class="filter-remove">×</a>
                </div>
            <?php endif; ?>
        </div>
        <a href="<?php echo BASE_URL; ?>/items/search" class="clear-all-filters">Clear All</a>
    </div>
    <?php endif; ?>
    
    <!-- Results Header -->
    <div class="results-header">
        <div class="results-count">
            <strong><?php echo $pagination['totalItems']; ?></strong> items found
        </div>
        <div class="results-options">
            <div class="sort-options">
                <label for="sort-select">Sort by:</label>
                <select id="sort-select" class="form-control form-control-sm">
                    <option value="name_asc" <?php echo ($sort === 'name_asc') ? 'selected' : ''; ?>>Name (A-Z)</option>
                    <option value="name_desc" <?php echo ($sort === 'name_desc') ? 'selected' : ''; ?>>Name (Z-A)</option>
                    <option value="newest" <?php echo ($sort === 'newest') ? 'selected' : ''; ?>>Newest First</option>
                    <option value="oldest" <?php echo ($sort === 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                    <option value="price_asc" <?php echo ($sort === 'price_asc') ? 'selected' : ''; ?>>Price (Low to High)</option>
                    <option value="price_desc" <?php echo ($sort === 'price_desc') ? 'selected' : ''; ?>>Price (High to Low)</option>
                </select>
            </div>
            <div class="view-options">
                <button class="btn btn-sm btn-outline view-mode-btn" data-mode="list">
                    <i class="fas fa-th-list"></i>
                </button>
                <button class="btn btn-sm btn-outline view-mode-btn active" data-mode="grid">
                    <i class="fas fa-th-large"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Results Content -->
    <?php if (count($items) > 0): ?>
        <div class="items-grid">
            <?php foreach ($items as $item): ?>
                <div class="item-card">
                    <div class="item-card-image">
                        <?php if (!empty($item['images']) && count($item['images']) > 0): ?>
                            <?php
                            // Find primary image or use first
                            $displayImage = $item['images'][0];
                            foreach ($item['images'] as $image) {
                                if ($image['is_primary']) {
                                    $displayImage = $image;
                                    break;
                                }
                            }
                            ?>
                            <img src="<?php echo BASE_URL . '/uploads/items/' . $item['id'] . '/' . $displayImage['file_name']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <?php else: ?>
                            <?php 
                            // Determine icon based on category
                            $iconClass = 'box';
                            
                            if (stripos($item['category_name'], 'computer') !== false || stripos($item['name'], 'laptop') !== false) {
                                $iconClass = 'laptop';
                            } elseif (stripos($item['category_name'], 'audio') !== false) {
                                $iconClass = 'headphones';
                            } elseif (stripos($item['category_name'], 'video') !== false || stripos($item['name'], 'projector') !== false) {
                                $iconClass = 'video';
                            } elseif (stripos($item['category_name'], 'camera') !== false || stripos($item['category_name'], 'photo') !== false) {
                                $iconClass = 'camera';
                            } elseif (stripos($item['name'], 'tablet') !== false || stripos($item['name'], 'ipad') !== false) {
                                $iconClass = 'tablet-alt';
                            } elseif (stripos($item['name'], 'microphone') !== false || stripos($item['name'], 'mic') !== false) {
                                $iconClass = 'microphone';
                            }
                            ?>
                            <i class="fas fa-<?php echo $iconClass; ?>"></i>
                        <?php endif; ?>
                    </div>
                    <div class="item-card-body">
                        <div class="item-card-tags">
                            <span class="badge badge-blue"><?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?></span>
                            <?php if (!empty($item['tags'])): ?>
                                <?php foreach (array_slice($item['tags'], 0, 2) as $tagItem): ?>
                                    <span class="badge badge-purple"><?php echo htmlspecialchars($tagItem['name']); ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <h3 class="item-card-title"><?php echo htmlspecialchars($item['name']); ?></h3>
                        <div class="item-card-details">
                            <?php if (!empty($item['brand'])): ?>
                            <div class="item-detail">
                                <span class="item-detail-label">Brand:</span>
                                <span class="item-detail-value"><?php echo htmlspecialchars($item['brand']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($item['model'])): ?>
                            <div class="item-detail">
                                <span class="item-detail-label">Model:</span>
                                <span class="item-detail-value"><?php echo htmlspecialchars($item['model']); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="item-detail">
                                <span class="item-detail-label">Location:</span>
                                <span class="item-detail-value"><?php echo htmlspecialchars($item['location_name'] ?? 'Not Set'); ?></span>
                            </div>
                            <?php if (!empty($item['purchase_price'])): ?>
                            <div class="item-detail item-detail-price">
                                <span class="item-detail-label">Value:</span>
                                <span class="item-detail-value"><?php echo UtilityHelper::formatCurrency($item['current_value'] ?? $item['purchase_price']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="item-card-footer">
                            <div class="item-status">
                                <?php 
                                $statusClass = 'status-available';
                                $statusText = 'Available';
                                
                                if ($item['status'] === 'borrowed') {
                                    $statusClass = 'status-borrowed';
                                    $statusText = 'Borrowed';
                                } elseif ($item['status'] === 'reserved') {
                                    $statusClass = 'status-reserved';
                                    $statusText = 'Reserved';
                                } elseif ($item['status'] === 'maintenance') {
                                    $statusClass = 'status-maintenance';
                                    $statusText = 'In Maintenance';
                                } elseif ($item['status'] === 'retired' || $item['status'] === 'unavailable') {
                                    $statusClass = 'status-unavailable';
                                    $statusText = 'Unavailable';
                                }
                                ?>
                                <span class="status-indicator <?php echo $statusClass; ?>"></span>
                                <span><?php echo $statusText; ?></span>
                            </div>
                            <div class="item-actions">
                                <a href="<?php echo BASE_URL; ?>/items/view?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($item['status'] === 'available' && !hasRole(['admin', 'manager'])): ?>
                                    <a href="<?php echo BASE_URL; ?>/borrow/create?item_id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">Borrow</a>
                                <?php elseif (hasRole(['admin', 'manager'])): ?>
                                    <a href="<?php echo BASE_URL; ?>/items/edit?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($pagination['totalPages'] > 1): ?>
            <div class="pagination-container">
                <div class="pagination">
                    <?php if ($pagination['currentPage'] > 1): ?>
                        <a href="<?php echo BASE_URL; ?>/items/search?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['currentPage'] - 1])); ?>" class="page-btn page-nav">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php else: ?>
                        <span class="page-btn page-nav disabled">
                            <i class="fas fa-chevron-left"></i>
                        </span>
                    <?php endif; ?>
                    
                    <?php
                    // Calculate page range to display
                    $startPage = max(1, $pagination['currentPage'] - 2);
                    $endPage = min($pagination['totalPages'], $pagination['currentPage'] + 2);
                    
                    // Always show first page
                    if ($startPage > 1) {
                        echo '<a href="' . BASE_URL . '/items/search?' . http_build_query(array_merge($_GET, ['page' => 1])) . '" class="page-btn">1</a>';
                        if ($startPage > 2) {
                            echo '<span class="page-ellipsis">...</span>';
                        }
                    }
                    
                    // Display page numbers
                    for ($i = $startPage; $i <= $endPage; $i++) {
                        if ($i == $pagination['currentPage']) {
                            echo '<span class="page-btn active">' . $i . '</span>';
                        } else {
                            echo '<a href="' . BASE_URL . '/items/search?' . http_build_query(array_merge($_GET, ['page' => $i])) . '" class="page-btn">' . $i . '</a>';
                        }
                    }
                    
                    // Always show last page
                    if ($endPage < $pagination['totalPages']) {
                        if ($endPage < $pagination['totalPages'] - 1) {
                            echo '<span class="page-ellipsis">...</span>';
                        }
                        echo '<a href="' . BASE_URL . '/items/search?' . http_build_query(array_merge($_GET, ['page' => $pagination['totalPages']])) . '" class="page-btn">' . $pagination['totalPages'] . '</a>';
                    }
                    ?>
                    
                    <?php if ($pagination['currentPage'] < $pagination['totalPages']): ?>
                        <a href="<?php echo BASE_URL; ?>/items/search?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['currentPage'] + 1])); ?>" class="page-btn page-nav">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="page-btn page-nav disabled">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="pagination-info">
                    Showing <?php echo ($pagination['offset'] + 1); ?>-<?php echo min($pagination['offset'] + count($items), $pagination['totalItems']); ?> of <?php echo $pagination['totalItems']; ?> items
                </div>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <!-- No Results -->
        <div class="no-results">
            <div class="no-results-icon">
                <i class="fas fa-search"></i>
            </div>
            <h3>No items found</h3>
            <p>Try adjusting your search criteria or filters to find what you're looking for.</p>
            <?php if (!empty($searchQuery) || !empty($categoryId) || !empty($locationId) || !empty($status) || !empty($condition) || !empty($tag)): ?>
                <a href="<?php echo BASE_URL; ?>/items/search" class="btn btn-outline">
                    <i class="fas fa-redo-alt btn-icon"></i> Reset All Filters
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
/* Content Header Styling */
.content-header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

/* Main Search Styling */
.main-search {
    margin-bottom: 1.5rem;
}

.search-container {
    width: 100%;
    max-width: 100%;
}

.search-input-wrapper {
    display: flex;
    position: relative;
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-400);
}

.search-input {
    flex: 1;
    padding: 1rem 1rem 1rem 2.5rem;
    border: 1px solid var(--gray-300);
    border-right: none;
    border-top-left-radius: 6px;
    border-bottom-left-radius: 6px;
    font-size: 1rem;
}

.search-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
}

.search-btn {
    padding: 0.75rem 1.5rem;
    background-color: var(--primary);
    color: white;
    border: none;
    border-top-right-radius: 6px;
    border-bottom-right-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.search-btn:hover {
    background-color: var(--primary-dark);
}

.search-options {
    display: flex;
    justify-content: space-between;
    margin-top: 0.75rem;
}

.advanced-search-toggle, .clear-search {
    background: none;
    border: none;
    color: var(--primary);
    font-size: 0.9rem;
    cursor: pointer;
    padding: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.advanced-search-toggle:hover, .clear-search:hover {
    text-decoration: underline;
}

/* Advanced Search Panel */
.advanced-search-panel {
    margin-bottom: 1.5rem;
}

.advanced-search-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.price-filter {
    margin-bottom: 1.5rem;
}

.price-inputs {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.price-range-separator {
    color: var(--gray-500);
}

.advanced-search-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

/* Active Filters */
.active-filters {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background-color: var(--gray-50);
    border: 1px solid var(--gray-200);
    border-radius: 6px;
}

.active-filters-label {
    font-weight: 600;
    color: var(--gray-700);
}

.active-filters-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    flex: 1;
}

.filter-tag {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    background-color: white;
    border: 1px solid var(--gray-300);
    border-radius: 4px;
    font-size: 0.9rem;
}

.filter-remove {
    margin-left: 0.5rem;
    color: var(--gray-500);
    font-weight: bold;
    text-decoration: none;
}

.filter-remove:hover {
    color: var(--danger);
}

.clear-all-filters {
    color: var(--danger);
    font-size: 0.9rem;
    text-decoration: none;
}

.clear-all-filters:hover {
    text-decoration: underline;
}

/* Results Header */
.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--gray-200);
}

.results-options {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.sort-options {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.sort-options label {
    color: var(--gray-700);
    font-size: 0.9rem;
}

.view-options {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.form-control-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.9rem;
}

.view-mode-btn.active {
    background-color: var(--primary);
    color: white;
    border-color: var(--primary);
}

/* Items Grid */
.items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.items-grid.list-view {
    grid-template-columns: 1fr;
}

.item-card {
    display: flex;
    flex-direction: column;
    border: 1px solid var(--gray-200);
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.2s ease;
}

.item-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.items-grid.list-view .item-card {
    flex-direction: row;
}

.item-card-image {
    height: 180px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--gray-50);
    overflow: hidden;
}

.items-grid.list-view .item-card-image {
    width: 200px;
    height: 200px;
    flex-shrink: 0;
}

.item-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.item-card:hover .item-card-image img {
    transform: scale(1.05);
}

.item-card-image i {
    font-size: 3rem;
    color: var(--gray-400);
}

.item-card-body {
    display: flex;
    flex-direction: column;
    padding: 1rem;
    flex: 1;
}

.item-card-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 500;
    border-radius: 4px;
    color: white;
}

.badge-blue {
    background-color: var(--primary);
}

.badge-purple {
    background-color: var(--secondary);
}

.item-card-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0 0 0.75rem 0;
    color: var(--gray-800);
}

.item-card-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    margin-bottom: 1rem;
    flex: 1;
}

.items-grid.list-view .item-card-details {
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
}

.item-detail {
    font-size: 0.9rem;
}

.item-detail-label {
    color: var(--gray-500);
    margin-right: 0.25rem;
}

.item-detail-value {
    font-weight: 500;
    color: var(--gray-700);
}

.item-detail-price .item-detail-value {
    color: var(--primary);
    font-weight: 600;
}

.item-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
    padding-top: 0.75rem;
    border-top: 1px solid var(--gray-200);
}

.item-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: var(--gray-700);
}

.status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.status-available {
    background-color: var(--success);
}

.status-borrowed {
    background-color: var(--warning);
}

.status-reserved {
    background-color: var(--primary);
}

.status-maintenance {
    background-color: var(--secondary);
}

.status-unavailable {
    background-color: var(--danger);
}

.item-actions {
    display: flex;
    gap: 0.5rem;
}

/* Pagination */
.pagination-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--gray-200);
}

.pagination {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.5rem;
    height: 2.5rem;
    padding: 0 0.75rem;
    background-color: white;
    border: 1px solid var(--gray-300);
    border-radius: 6px;
    color: var(--gray-700);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
}

.page-btn:hover {
    background-color: var(--gray-100);
    border-color: var(--gray-400);
}

.page-btn.active {
    background-color: var(--primary);
    border-color: var(--primary);
    color: white;
}

.page-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.page-nav {
    font-size: 0.8rem;
}

.page-ellipsis {
    padding: 0 0.5rem;
    color: var(--gray-500);
}

.pagination-info {
    color: var(--gray-500);
    font-size: 0.9rem;
}

/* No Results */
.no-results {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--gray-600);
}

.no-results-icon {
    font-size: 3rem;
    color: var(--gray-400);
    margin-bottom: 1rem;
}

.no-results h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--gray-700);
}

.no-results p {
    margin-bottom: 2rem;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .items-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
    
    .items-grid.list-view .item-card {
        flex-direction: column;
    }
    
    .items-grid.list-view .item-card-image {
        width: 100%;
        height: 180px;
    }
    
    .results-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .results-options {
        width: 100%;
        justify-content: space-between;
    }
    
    .active-filters {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .active-filters-list {
        margin: 0.5rem 0;
    }
    
    .pagination-container {
        flex-direction: column-reverse;
        gap: 1rem;
    }
    
    .pagination-info {
        text-align: center;
    }
}

@media (max-width: 576px) {
    .advanced-search-grid {
        grid-template-columns: 1fr;
    }
    
    .price-inputs {
        flex-direction: column;
        align-items: stretch;
    }
    
    .price-range-separator {
        text-align: center;
        margin: 0.5rem 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Advanced Search Panel Toggle
    const toggleAdvancedSearch = document.getElementById('toggle-advanced-search');
    const closeAdvancedSearch = document.getElementById('close-advanced-search');
    const advancedSearchPanel = document.getElementById('advanced-search-panel');
    
    if (toggleAdvancedSearch && advancedSearchPanel) {
        toggleAdvancedSearch.addEventListener('click', function() {
            advancedSearchPanel.style.display = advancedSearchPanel.style.display === 'none' ? 'block' : 'none';
        });
    }
    
    if (closeAdvancedSearch && advancedSearchPanel) {
        closeAdvancedSearch.addEventListener('click', function() {
            advancedSearchPanel.style.display = 'none';
        });
    }
    
    // View Mode Toggle
    const viewModeBtns = document.querySelectorAll('.view-mode-btn');
    const itemsGrid = document.querySelector('.items-grid');
    
    if (viewModeBtns.length && itemsGrid) {
        viewModeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                viewModeBtns.forEach(b => b.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Toggle grid/list view
                const mode = this.getAttribute('data-mode');
                if (mode === 'list') {
                    itemsGrid.classList.add('list-view');
                } else {
                    itemsGrid.classList.remove('list-view');
                }
                
                // Save preference to localStorage
                localStorage.setItem('itemsViewMode', mode);
            });
        });
        
        // Load saved view mode preference
        const savedViewMode = localStorage.getItem('itemsViewMode');
        if (savedViewMode) {
            const btn = document.querySelector(`.view-mode-btn[data-mode="${savedViewMode}"]`);
            if (btn) {
                btn.click();
            }
        }
    }
    
    // Sort Options
    const sortSelect = document.getElementById('sort-select');
    
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            // Build URL with the new sort parameter
            const url = new URL(window.location.href);
            url.searchParams.set('sort', this.value);
            
            // Redirect to the URL with the new sort parameter
            window.location.href = url.toString();
        });
    }
    
    // Check if we should auto-open advanced search
    <?php if (!empty($categoryId) || !empty($locationId) || !empty($status) || !empty($condition) || !empty($tag) || !empty($supplierId) || !empty($minPrice) || !empty($maxPrice)): ?>
    if (advancedSearchPanel) {
        advancedSearchPanel.style.display = 'block';
    }
    <?php endif; ?>
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?>