<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include('db_connection.php');

$user_id = $_SESSION['user_id'];
$purpose = $_POST['purpose'];



// Process the purchase (e.g., save to orders table, clear the cart, etc.)
// This is a placeholder for the actual purchase processing logic

// Clear the cart
$clear_cart_query = "DELETE ci FROM cart_items ci
                     JOIN carts c ON ci.cart_id = c.cart_id
                     WHERE c.user_id = ?";
$clear_cart_stmt = $conn->prepare($clear_cart_query);
$clear_cart_stmt->bind_param("s", $user_id);
$clear_cart_stmt->execute();
$clear_cart_stmt->close();

$conn->close();

header("Location: index.php");
exit();
?>
