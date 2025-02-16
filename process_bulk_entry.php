<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['inventoryFile'])) {
    $fileName = $_FILES['inventoryFile']['tmp_name'];

    if ($_FILES['inventoryFile']['size'] > 0) {
        $file = fopen($fileName, 'r');

        // Skip the first line if it contains headers
        fgetcsv($file);

        while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
            // Process each row of the CSV file
            $productName = $column[0];
            $productId = $column[1];
            $quantity = $column[2];
            $inStock = $column[3];

            // Insert the data into the database
            // Assuming you have a database connection $conn
            $sql = "INSERT INTO inventory (product_name, product_id, quantity, in_stock) VALUES ('$productName', '$productId', '$quantity', '$inStock')";
            mysqli_query($conn, $sql);
        }

        fclose($file);
        echo "Inventory data has been successfully uploaded.";
    } else {
        echo "Invalid file or no file uploaded.";
    }
} else {
    echo "Invalid request.";
}
?>
