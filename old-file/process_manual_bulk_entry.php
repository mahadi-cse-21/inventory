<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Assuming you have a database connection $conn
    foreach ($_POST['productName'] as $index => $productName) {
        $productId = $_POST['productId'][$index];
        $quantity = $_POST['quantity'][$index];
        $inStock = $_POST['inStock'][$index];

        // Insert the data into the database
        $sql = "INSERT INTO inventory (product_name, product_id, quantity, in_stock) VALUES ('$productName', '$productId', '$quantity', '$inStock')";
        mysqli_query($conn, $sql);
    }

    echo "Inventory data has been successfully uploaded.";
} else {
    echo "Invalid request.";
}
?>
