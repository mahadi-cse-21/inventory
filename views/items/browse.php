<?php
/**
 * Browse Items View
 * 
 * This file displays all available items for browsing/borrowing
 */

// Set page title
$pageTitle = 'Browse Items';
$bodyClass = 'browse-page';

// Get current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Get filters from query string
$filters = [
    'search' => isset($_GET['q']) ? cleanInput($_GET['q']) : '',
    'category_id' => isset($_GET['category']) ? (int)$_GET['category'] : '',
    'location_id' => isset($_GET['location']) ? (int)$_GET['location'] : '',
    'status' => isset($_GET['status']) ? cleanInput($_GET['status']) : 'available',
    'is_active' => 1
];

// Get all items with pagination
$itemsResult = InventoryHelper::getAllItems($page, ITEMS_PER_PAGE, $filters);
$items = $itemsResult['items'];
$pagination = $itemsResult['pagination'];

// Get categories for filter
$categories = InventoryHelper::getAllCategories();

// Get locations for filter
$locationResult = LocationHelper::getAllLocations(1, 100, ['is_active' => 1]);

// Include header
include 'includes/header.php';
?>

<div class="content-header">
    <h1 class="page-title">Browse Items</h1>
</div>

<!-- Filters Panel -->
<div class="panel" style="margin-bottom: 1.5rem;">
    <div class="panel-header">
        <div class="panel-title">Filters</div>
        <a href="<?php echo BASE_URL; ?>/items/browse" class="btn btn-sm btn-outline">
            <i class="fas fa-redo-alt btn-icon"></i>
            Reset
        </a>
    </div>
    <div class="panel-body">
        <form action="<?php echo BASE_URL; ?>/items/browse" method="GET" id="filter-form">
            <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Category</label>
                    <select class="form-control" name="category" id="category-filter">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo ($filters['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                            <?php foreach ($category['children'] as $child): ?>
                                <option value="<?php echo $child['id']; ?>" <?php echo ($filters['category_id'] == $child['id']) ? 'selected' : ''; ?>>
                                    &nbsp;&nbsp;└ <?php echo htmlspecialchars($child['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Availability</label>
                    <select class="form-control" name="status" id="status-filter">
                        <option value="available" <?php echo ($filters['status'] == 'available') ? 'selected' : ''; ?>>Available Now</option>
                        <option value="" <?php echo ($filters['status'] === '') ? 'selected' : ''; ?>>All Items</option>
                        <option value="reserved" <?php echo ($filters['status'] == 'reserved') ? 'selected' : ''; ?>>Reserved</option>
                        <option value="borrowed" <?php echo ($filters['status'] == 'borrowed') ? 'selected' : ''; ?>>Currently Borrowed</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Location</label>
                    <select class="form-control" name="location" id="location-filter">
                        <option value="">All Locations</option>
                        <?php foreach ($locations as $location): ?>
                            <option value="<?php echo $location['id']; ?>" <?php echo ($filters['location_id'] == $location['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($location['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="q" value="<?php echo htmlspecialchars($filters['search']); ?>" placeholder="Search by name, description...">
                </div>
            </div>
            <div style="margin-top: 1rem; text-align: right;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search btn-icon"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Options -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <div>
            <label class="form-label" style="display: inline-block; margin-right: 0.5rem;">Sort by:</label>
            <select class="form-control" id="sort-select" style="display: inline-block; width: auto;">
                <option value="name">Name</option>
                <option value="newest">Newest First</option>
                <option value="category">Category</option>
            </select>
        </div>
        <div>
            <span style="color: var(--gray-500);">Showing <?php echo $pagination['totalItems'] > 0 ? ($pagination['offset'] + 1) : 0; ?>-<?php echo min($pagination['offset'] + $pagination['itemsPerPage'], $pagination['totalItems']); ?> of <?php echo $pagination['totalItems']; ?> items</span>
        </div>
    </div>
    <div>
        <button class="btn btn-sm btn-outline view-mode-btn" data-mode="list">
            <i class="fas fa-th-list"></i>
        </button>
        <button class="btn btn-sm btn-outline view-mode-btn active" data-mode="grid">
            <i class="fas fa-th-large"></i>
        </button>
    </div>
</div>

<?php if (count($items) > 0): ?>
    <!-- Items Grid -->
    <div class="items-grid">
        <?php foreach ($items as $item): ?>
        <div class="item-card">
            <div class="item-card-image">
                <?php if (!empty($item['images']) && count($item['images']) > 0): ?>
                    <img src="<?php echo BASE_URL . '/uploads/items/' . $item['id'] . '/' . $item['images'][0]['file_name']; ?>" alt="<?php echo htmlspecialchars($item['name'] ?? 'Item'); ?>">
                <?php else: ?>
                    <?php
                    // Determine icon based on category or item name
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
                    <i class="fas fa-<?php echo $iconClass; ?>" aria-hidden="true"></i>
                <?php endif; ?>
            </div>

            <div class="item-card-body">
                <div class="item-card-tags">
                    <span class="badge badge-blue"><?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?></span>
                    <?php if (!empty($item['tags'])): ?>
                        <?php foreach (array_slice($item['tags'], 0, 2) as $tag): ?>
                            <span class="badge badge-purple"><?php echo htmlspecialchars($tag['name']); ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <h3 class="item-card-title"><?php echo htmlspecialchars($item['name'] ?? 'Unnamed Item'); ?></h3>

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
                        } elseif (in_array($item['status'], ['retired', 'unavailable'])) {
                            $statusClass = 'status-unavailable';
                            $statusText = 'Unavailable';
                        }
                        ?>
                        <span class="status-indicator <?php echo $statusClass; ?>"></span>
                        <span><?php echo $statusText; ?></span>
                    </div>

                    <div class="item-actions">
                        <button class="btn btn-sm btn-outline item-quick-view" data-item-id="<?php echo $item['id']; ?>">
                            <i class="fas fa-eye"></i>
                        </button>

                        <?php if ($item['status'] === 'available'): ?>
                            <a href="<?php echo BASE_URL; ?>/views/borrow/requestto.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">Request</a>
                        <?php elseif ($item['status'] === 'reserved'): ?>
                            <a href="<?php echo BASE_URL; ?>/views/borrow/reserve?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline">Reserve</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['totalPages'] > 1): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem;">
            <div class="pagination-info">
                Showing <?php echo $pagination['totalItems'] > 0 ? ($pagination['offset'] + 1) : 0; ?>-<?php echo min($pagination['offset'] + $pagination['itemsPerPage'], $pagination['totalItems']); ?> of <?php echo $pagination['totalItems']; ?> items
            </div>
            <div class="pagination">
                <?php if ($pagination['currentPage'] > 1): ?>
                    <a href="<?php echo BASE_URL; ?>/items/browse?page=<?php echo ($pagination['currentPage'] - 1); ?>&category=<?php echo $filters['category_id']; ?>&location=<?php echo $filters['location_id']; ?>&status=<?php echo $filters['status']; ?>&q=<?php echo urlencode($filters['search']); ?>" class="btn btn-sm btn-outline">Previous</a>
                <?php else: ?>
                    <button class="btn btn-sm btn-outline" disabled>Previous</button>
                <?php endif; ?>

                <?php
                // Calculate page range to display
                $startPage = max(1, $pagination['currentPage'] - 2);
                $endPage = min($pagination['totalPages'], $pagination['currentPage'] + 2);
                
                // Always show first page
                if ($startPage > 1) {
                    echo '<a href="' . BASE_URL . '/items/browse?page=1&category=' . $filters['category_id'] . '&location=' . $filters['location_id'] . '&status=' . $filters['status'] . '&q=' . urlencode($filters['search']) . '" class="btn btn-sm btn-outline">1</a>';
                    if ($startPage > 2) {
                        echo '<span style="margin: 0 0.5rem;">...</span>';
                    }
                }
                
                // Display page numbers
                for ($i = $startPage; $i <= $endPage; $i++) {
                    if ($i == $pagination['currentPage']) {
                        echo '<button class="btn btn-sm btn-primary">' . $i . '</button>';
                    } else {
                        echo '<a href="' . BASE_URL . '/items/browse?page=' . $i . '&category=' . $filters['category_id'] . '&location=' . $filters['location_id'] . '&status=' . $filters['status'] . '&q=' . urlencode($filters['search']) . '" class="btn btn-sm btn-outline">' . $i . '</a>';
                    }
                }
                
                // Always show last page
                if ($endPage < $pagination['totalPages']) {
                    if ($endPage < $pagination['totalPages'] - 1) {
                        echo '<span style="margin: 0 0.5rem;">...</span>';
                    }
                    echo '<a href="' . BASE_URL . '/items/browse?page=' . $pagination['totalPages'] . '&category=' . $filters['category_id'] . '&location=' . $filters['location_id'] . '&status=' . $filters['status'] . '&q=' . urlencode($filters['search']) . '" class="btn btn-sm btn-outline">' . $pagination['totalPages'] . '</a>';
                }
                ?>

                <?php if ($pagination['currentPage'] < $pagination['totalPages']): ?>
                    <a href="<?php echo BASE_URL; ?>/items/browse?page=<?php echo ($pagination['currentPage'] + 1); ?>&category=<?php echo $filters['category_id']; ?>&location=<?php echo $filters['location_id']; ?>&status=<?php echo $filters['status']; ?>&q=<?php echo urlencode($filters['search']); ?>" class="btn btn-sm btn-outline">Next</a>
                <?php else: ?>
                    <button class="btn btn-sm btn-outline" disabled>Next</button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No items found matching your criteria. Try adjusting your filters or search terms.
    </div>
<?php endif; ?>

<!-- Item Detail Modal Template (Initially Hidden) -->
<div class="modal-backdrop" id="item-detail-modal" style="display: none;">
    <div class="modal" style="max-width: 800px;">
        <div class="modal-header">
            <div class="modal-title" id="modal-item-title">Item Details</div>
            <button class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="modal-item-content">
            <div class="spinner-container">
                <div class="spinner"></div>
                <p>Loading item details...</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const filterForm = document.getElementById('filter-form');
    const categoryFilter = document.getElementById('category-filter');
    const statusFilter = document.getElementById('status-filter');
    const locationFilter = document.getElementById('location-filter');
    
    // Auto-submit form when filters change
    categoryFilter.addEventListener('change', function() {
        filterForm.submit();
    });
    
    statusFilter.addEventListener('change', function() {
        filterForm.submit();
    });
    
    locationFilter.addEventListener('change', function() {
        filterForm.submit();
    });
    
    // View mode toggle
    const viewModeBtns = document.querySelectorAll('.view-mode-btn');
    const itemsContainer = document.querySelector('.items-grid');
    
    viewModeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const mode = this.getAttribute('data-mode');
            
            // Remove active class from all buttons
            viewModeBtns.forEach(b => b.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Toggle view mode
            if (mode === 'list') {
                itemsContainer.classList.add('list-view');
            } else {
                itemsContainer.classList.remove('list-view');
            }
            
            // Save preference to localStorage
            localStorage.setItem('itemViewMode', mode);
        });
    });
    
    // Load saved view mode preference
    const savedViewMode = localStorage.getItem('itemViewMode');
    if (savedViewMode) {
        const btn = document.querySelector(`.view-mode-btn[data-mode="${savedViewMode}"]`);
        if (btn) {
            btn.click();
        }
    }
    
    // Item quick view functionality
    const quickViewBtns = document.querySelectorAll('.item-quick-view');
    const itemDetailModal = document.getElementById('item-detail-modal');
    const modalClose = itemDetailModal.querySelector('.modal-close');
    const modalTitle = document.getElementById('modal-item-title');
    const modalContent = document.getElementById('modal-item-content');
    
    quickViewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.getAttribute('data-item-id');
            
            // Show modal with loading spinner
            itemDetailModal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevent scrolling
            
            // Reset content
            modalTitle.textContent = 'Item Details';
            modalContent.innerHTML = `
                <div class="spinner-container">
                    <div class="spinner"></div>
                    <p>Loading item details...</p>
                </div>
            `;
            
            // Fetch item details via AJAX
            fetch(`<?php echo BASE_URL; ?>/items/view?id=${itemId}&format=modal`)
                .then(response => response.text())
                .then(html => {
                    // Update modal content
                    modalContent.innerHTML = html;
                    
                    // Update modal title
                    const itemName = modalContent.querySelector('.item-name');
                    if (itemName) {
                        modalTitle.textContent = itemName.textContent;
                    }
                    
                    // Initialize tabs if they exist
                    const tabsContainer = modalContent.querySelector('.tabs-container');
                    if (tabsContainer) {
                        const tabs = tabsContainer.querySelectorAll('.tab');
                        const tabContents = tabsContainer.querySelectorAll('.tab-content');
                        
                        tabs.forEach((tab, index) => {
                            tab.addEventListener('click', function() {
                                // Remove active class from all tabs and contents
                                tabs.forEach(t => t.classList.remove('active'));
                                tabContents.forEach(c => c.classList.remove('active'));
                                
                                // Add active class to clicked tab and corresponding content
                                this.classList.add('active');
                                if (tabContents[index]) {
                                    tabContents[index].classList.add('active');
                                }
                            });
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading item details:', error);
                    modalContent.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> Failed to load item details. Please try again.
                        </div>
                    `;
                });
        });
    });
    
    // Close modal on button click or by clicking outside
    modalClose.addEventListener('click', function() {
        itemDetailModal.style.display = 'none';
        document.body.style.overflow = ''; // Restore scrolling
    });
    
    itemDetailModal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
            document.body.style.overflow = ''; // Restore scrolling
        }
    });
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && itemDetailModal.style.display === 'flex') {
            itemDetailModal.style.display = 'none';
            document.body.style.overflow = ''; // Restore scrolling
        }
    });
    
    // Sort functionality
    const sortSelect = document.getElementById('sort-select');
    
    sortSelect.addEventListener('change', function() {
        const sortValue = this.value;
        const itemCards = Array.from(document.querySelectorAll('.item-card'));
        const itemsParent = document.querySelector('.items-grid');
        
        // Sort items
        itemCards.sort((a, b) => {
            if (sortValue === 'name') {
                const nameA = a.querySelector('.item-card-title').textContent.toLowerCase();
                const nameB = b.querySelector('.item-card-title').textContent.toLowerCase();
                return nameA.localeCompare(nameB);
            } else if (sortValue === 'newest') {
                // Sort by id in reverse (assuming newer items have higher IDs)
                const idA = parseInt(a.querySelector('.item-quick-view').getAttribute('data-item-id'));
                const idB = parseInt(b.querySelector('.item-quick-view').getAttribute('data-item-id'));
                return idB - idA;
            } else if (sortValue === 'category') {
                const categoryA = a.querySelector('.badge').textContent.toLowerCase();
                const categoryB = b.querySelector('.badge').textContent.toLowerCase();
                return categoryA.localeCompare(categoryB);
            }
            
            return 0;
        });
        
        // Reappend in sorted order
        itemCards.forEach(card => {
            itemsParent.appendChild(card);
        });
    });
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?>