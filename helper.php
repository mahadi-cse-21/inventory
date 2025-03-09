<?php

include 'dbconnect.php';
// Only proceed if the form is submitted and GET data is available
if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password =  $_POST['password'];

    // Now you can safely use the values for your SQL query
    $sql = "SELECT email, user_id, password FROM users WHERE email = '$email'";

    // Execute the query and handle the result
    $result = mysqli_query($conn, $sql);
    // $row= mysqli_execute($conn,$sql);

    if (mysqli_num_rows($result) > 0) {
        // Login successful
        $row = mysqli_fetch_assoc($result);
        echo $row['email'];
        echo "<br>";
        echo $row['password'];
        echo "<br>";
        echo password_hash($password, PASSWORD_DEFAULT);
        echo "<br>";
        // echo "Login Successful!";
        if(password_verify($password,$row['password'])){
            echo "Login Successfull!";
            $_SESSION['user_id'] = $row['user_id'];
            header("Location: index.php");
        }
        else{
            echo "password Wrong";
        }   
        // You can also redirect the user or start a session here
    } else {
        // Invalid login
        echo "Invalid email or password!";
    }

    // Close the database connection
    mysqli_close($conn);
}