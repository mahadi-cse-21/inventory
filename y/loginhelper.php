<?php
    include('connect.php');
    $email = $_POST['e'];
    $pass = $_POST['p'];

    $sql = "SELECT * FROM students WHERE email = '".$email."' and password = '".$pass."'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $res = $stmt->get_result();
    $count = $res->num_rows;
    if($count==0){
        header('location: login.php');
    }
    else{
        session_start();
        $_SESSION['id'] = 1;
        header('location: index.php');
    }
?>