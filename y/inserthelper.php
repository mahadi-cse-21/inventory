<?php
    include('connect.php');
    $name = $_POST['n'];
    $roll = $_POST['r'];
    $email = $_POST['e'];
    $pass = $_POST['p'];

    $sql = "INSERT INTO students (name, roll, email, password) VALUES ('". $name. "'," . "'" . $roll . "','". $email. "','". $pass. "')";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    header('location: index.php');
?>