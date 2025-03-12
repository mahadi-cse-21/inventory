<?php
session_start();
include 'db_connection.php';

$userId = $_SESSION['user_id'];


    function user_id($userId){
        global $conn;
        $sql = "SELECT id FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc()['id'];
    }

// Function to get active cart ID for a user (assuming user ID is hardcoded as 1 for this case)
function getActiveCartId($userId) {
    global $conn;
    
    // Check if the user exists
    $userCheckQuery = "SELECT id FROM users WHERE id = $userId";
    $userCheckResult = $conn->query($userCheckQuery);
    
    if ($userCheckResult->num_rows > 0) {
        // If user exists, proceed to find or create a cart
        $sql = "SELECT cart_id FROM carts WHERE user_id = $userId";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['cart_id'];
        } else {
            // Create a new cart if no active cart is found
            $sql = "INSERT INTO carts (user_id) VALUES ($userId)";
            $conn->query($sql);
            return $conn->insert_id;
        }
    } else {
        // Return an error or handle the case where the user does not exist
        throw new Exception("User with ID $userId does not exist.");
    }
}


// Function to add product to the cart
function addToCart($cartId, $productId) {
    global $conn;
    $sql = "INSERT INTO cart_items (cart_id, product_id, quantity) VALUES ($cartId, $productId, 1)
            ON DUPLICATE KEY UPDATE quantity = quantity + 1";
    $conn->query($sql);
}

// Get the product_id from the POST request
if (isset($_POST['product_id'])) {
    $productId = $_POST['product_id'];
    
    // Get the active cart ID for the user
    $cartId = getActiveCartId(user_id($userId));
    
    // Add the product to the cart
    addToCart($cartId, $productId);
    
    // Get the total number of items in the cart
    $sql = "SELECT SUM(quantity) AS total FROM cart_items WHERE cart_id = $cartId";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $totalCartItem = $row['total'];
    
    // Return the total cart item count as a JSON response
    echo json_encode(['totalCartItem' => $totalCartItem]);
}





// DELETE fROM cart_items;
// DELETE fROM carts;
// ALTER TABLE cart_items
// ADD CONSTRAINT unique_cart_item UNIQUE (cart_id, product_id);


