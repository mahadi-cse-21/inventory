<?php
session_start();  // Start the session at the top of the page

// Now you can safely access the $_SESSION array.
if (isset($_SESSION['user_id'])) {
    // You can use the session variables, e.g.:
    echo "Welcome, " . $_SESSION['full_name'];
    echo "<br>";
    echo "Your user ID is: " . $_SESSION['user_id'];
} else {
    echo "You are not logged in.";
}
?>
