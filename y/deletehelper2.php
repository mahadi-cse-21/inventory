<?php
    include('connect.php');
    $stmt = $conn->prepare("DELETE FROM students");
    $stmt->execute();
    header('location: index.php');
?>