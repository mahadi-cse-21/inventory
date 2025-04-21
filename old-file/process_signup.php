<?php
include('db_connection.php');

$user_id = $_POST['user_id'];
$full_name = $_POST['full_name'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);
$role_id = 2; // Default role: general
$status = 'active';

// Handle profile photo upload
$profile_photo = $_FILES['profile_photo'];
$photo_name = time() . '_' . $profile_photo['name'];
$target_dir = "uploads/profile_photos/";
$target_file = $target_dir . basename($photo_name);

if (move_uploaded_file($profile_photo['tmp_name'], $target_file)) {
    $query = "INSERT INTO users (user_id, full_name, email, password, role_id, status, profile_photo) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssiss", $user_id, $full_name, $email, $password, $role_id, $status, $photo_name);

    if ($stmt->execute()) {
        echo "Signup successful!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Error uploading profile photo.";
}

$conn->close();
?>
