<?php

// if(!isset($_SESSION['user_id'])){
//     header("Location: login.php");
//     exit();
// }

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_dbms";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// function user_id($userId){
//     global $conn;
//     $sql = "SELECT id FROM users WHERE user_id = ?";
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("s", $userId);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     $stmt->close();
//     return $result->fetch_assoc()['id'];
// }
?>
