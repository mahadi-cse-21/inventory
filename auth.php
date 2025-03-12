<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['signup'])) {
        // Signup process
        $user_id = $_POST['user_id'];
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $role_id = 1; // Default role: general
        $status = 'active';

        // Handle profile photo upload
        $profile_photo = $_FILES['profile_photo'];
        $photo_name = time() . '_' . $profile_photo['name'];
        $target_dir = "assets/uploads/profile_photos/";
        $target_file = $target_dir . basename($photo_name);

        if (move_uploaded_file($profile_photo['tmp_name'], $target_file)) {
            $query = "INSERT INTO users (user_id, name, email, password, role_id, status, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssiss", $user_id, $full_name, $email, $password, $role_id, $status, $photo_name);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['role_id'] = $role_id;
                header("Location: index.php");
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error uploading profile photo.";
        }
    } elseif (isset($_POST['login'])) {
        // Login process
        $email = $_POST['email'];
        $password = $_POST['password'];

        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['name'];
                $_SESSION['role_id'] = $user['role_id'];
                header("Location: index.php");
                exit();
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "No user found with this email.";
        }

        $stmt->close();
    }
} elseif (isset($_GET['logout'])) {
    // Logout process
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$conn->close();
?>
