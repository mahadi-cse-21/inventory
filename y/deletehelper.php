<?php
    include('connect.php');
    $r = $_GET['x'];
    $sql = "DELETE FROM students WHERE roll = '". $r."'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    header("location: index.php");
?>