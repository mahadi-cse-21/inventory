# InventoryPro

## Overview
InventoryPro is a comprehensive inventory management system designed to streamline the tracking, borrowing, and maintenance of items. It features role-based access control, a user-friendly interface, and robust backend functionality.

## Features
- **Role-Based Access Control**: Admin, Manager, and User roles with specific permissions.
- **Inventory Management**: Add, edit, view, and delete items with detailed attributes.
- **Borrow Requests**: Create, approve, reject, and track borrow requests.
- **Maintenance Management**: Schedule and track maintenance tasks for items.
- **Settings Management**: Configure system preferences, notifications, and security settings.
- **Reports and Analytics**: View and export data for analysis.

## Folder Structure
Below is the folder structure of the project with a brief description of each folder:

```
c:\xampp\htdocs\inventory_pro_1\
├── api/                  # API endpoints for AJAX or external integrations
├── assets/               # Static assets like CSS, JavaScript, and images
├── config/               # Configuration files (e.g., database, constants)
├── helpers/              # Helper functions for common tasks
├── includes/             # Shared components like header, footer, and sidebar
├── views/                # View files for rendering HTML pages
│   ├── auth/             # Authentication-related views (login, register, etc.)
│   ├── borrow/           # Borrowing-related views
│   ├── items/            # Item management views
│   ├── maintenance/      # Maintenance and damage report views
│   ├── settings/         # System settings views
│   ├── users/            # User management views
│   ├── errors/           # Error pages (404, 403, etc.)
├── database.sql          # Main database schema
├── database_sample_data.sql # Sample data for testing
├── index.php             # Main entry point and routing logic
├── init.php              # Initialization script for the application
├── README.md             # Project documentation (this file)
├── settings_migration.php # Script for migrating settings
```

## How to Get Started
1. **Setup Environment**
   - Install XAMPP or any PHP server.
   - Place the project folder in the `htdocs` directory.

2. **Database Configuration**
   - Create a MySQL database.
   - Import the provided `database.sql` file.
   - Optionally, import `database_sample_data.sql` for sample data.
   - Update database credentials in `config/database.php`.

3. **Run the Application**
   - Start the server and navigate to `http://localhost/inventory_pro_1/`.

4. **Default Credentials**
   - Admin: `admin@example.com` / `password`
   - User: `user@example.com` / `password`

## Development Guidelines
- Follow the MVC (Model-View-Controller) pattern.
- Use helper functions for common tasks (e.g., authentication, redirection).
- Add new routes to the `$routes` array in `index.php`.
- Use the `views/` folder for all HTML templates.

## Database Structure
The database includes the following key tables:
- **users**: Stores user information and roles.
- **items**: Tracks inventory items with attributes like category, location, and status.
- **borrow_requests**: Manages borrow requests and their statuses.
- **maintenance_records**: Logs maintenance tasks for items.
- **settings**: Stores system configuration settings.

## Routing Logic
The `index.php` file handles all routing. It maps URL paths to corresponding view files using the `$routes` array. If a route is not found, a 404 error page is displayed.

Example:
```php
$routes = [
    'dashboard/index' => 'views/dashboard.php',
    'auth/login' => 'views/auth/login.php',
    'items/index' => 'views/items/index.php',
    // ...existing routes...
];
```

## Role-Based Access Control
- **Admin**: Full access to all features, including user and settings management.
- **Manager**: Access to most features except user management.
- **User**: Limited access to personal profile, borrowing, and item browsing.

Permissions are enforced in `index.php` using helper functions like `hasRole()`.

## Maintenance
- Use `settings_migration.php` to migrate settings to a new structure.
- Regularly back up the database to prevent data loss.

## Contributing
- Follow coding standards and comment your code.
- Submit pull requests for new features or bug fixes.

## License
This project is licensed under the MIT License.

## Helper Classes Documentation

### Overview
Helper classes in InventoryPro are utility classes that encapsulate common operations, making the codebase modular, reusable, and easier to maintain. These classes interact with the database, handle business logic, and provide utility functions for various modules.

### List of Helper Classes

#### 1. **AuthHelper**
Handles user authentication and session management.
- **Key Methods**:
  - `login($username, $password)`: Authenticates a user and starts a session.
  - `logout()`: Ends the current user session.
  - `isAuthenticated()`: Checks if a user is logged in.
  - `getCurrentUser()`: Retrieves the currently logged-in user's details.

#### 2. **UserHelper**
Manages user-related operations.
- **Key Methods**:
  - `getUserById($userId)`: Fetches user details by ID.
  - `getAllUsers($page, $limit, $filters)`: Retrieves paginated user data with optional filters.
  - `createUser($userData)`: Creates a new user.
  - `updateUser($userId, $userData)`: Updates user details.
  - `deleteUser($userId)`: Deletes a user after checking for associated records.
  - `getUserPermissions($userId)`: Retrieves permissions for a user.

#### 3. **BorrowHelper**
Handles borrowing and reservation functionalities.
- **Key Methods**:
  - `getBorrowRequestById($requestId)`: Fetches a borrow request by ID.
  - `createBorrowRequest($requestData, $items)`: Creates a new borrow request.
  - `updateBorrowRequestStatus($requestId, $status, $userId, $notes)`: Updates the status of a borrow request.
  - `returnBorrowedItems($borrowRequestId, $itemsData, $userId)`: Processes the return of borrowed items.
  - `getUserCartItems($userId)`: Retrieves items in a user's cart.
  - `addToCart($userId, $itemId, $quantity, $borrowDate, $returnDate, $notes)`: Adds an item to the user's cart.

#### 4. **InventoryHelper**
Manages inventory-related operations.
- **Key Methods**:
  - `getAllItems($page, $limit, $filters)`: Retrieves paginated inventory items with filters.
  - `createItem($itemData)`: Adds a new item to the inventory.
  - `updateItem($itemId, $itemData)`: Updates an existing inventory item.
  - `logInventoryTransaction($data)`: Logs inventory transactions (e.g., check-in, check-out).

#### 5. **LocationHelper**
Handles operations related to storage locations.
- **Key Methods**:
  - `getLocationById($locationId)`: Fetches details of a specific location.
  - `getAllLocations($page, $limit, $filters)`: Retrieves paginated location data with filters.
  - `createLocation($locationData)`: Adds a new location.
  - `updateLocation($locationId, $locationData)`: Updates location details.
  - `deleteLocation($locationId)`: Deletes a location after validation.

#### 6. **MaintenanceHelper**
Manages maintenance and damage reports.
- **Key Methods**:
  - `getMaintenanceById($maintenanceId)`: Fetches a maintenance record by ID.
  - `createMaintenanceRecord($data)`: Creates a new maintenance record.
  - `updateMaintenanceRecord($maintenanceId, $data)`: Updates an existing maintenance record.
  - `deleteMaintenanceRecord($maintenanceId)`: Deletes a maintenance record.

#### 7. **SettingsHelper**
Handles system settings and configurations.
- **Key Methods**:
  - `getAllSettings($publicOnly, $group)`: Retrieves all or public settings, optionally filtered by group.
  - `addSetting($key, $value, $defaultValue, $group, $displayName, $description, $fieldType, $options)`: Adds a new setting.
  - `updateSettings($settings)`: Updates multiple settings.
  - `deleteSetting($key)`: Deletes a specific setting.

#### 8. **UtilityHelper**
Provides general utility functions used across the application.
- **Key Methods**:
  - `formatDate($date, $format)`: Formats a date.
  - `logActivity($userId, $action, $entityType, $entityId, $description)`: Logs user activities.
  - `paginate($totalItems, $currentPage, $itemsPerPage)`: Calculates pagination details.
  - `valueExists($table, $column, $value, $excludeId)`: Checks if a value exists in a database table.

### Usage Guidelines
1. **Include the Helper**: Ensure the helper is included in the `init.php` file or the relevant script.
2. **Call Methods**: Use static methods directly, e.g., `AuthHelper::login($username, $password)`.
3. **Handle Exceptions**: Wrap calls in try-catch blocks where necessary to handle errors gracefully.

#check