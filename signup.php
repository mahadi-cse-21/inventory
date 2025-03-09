<?php
// Include database connection file
include 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize form data
    $first_name = htmlspecialchars($_POST['first']);
    $mid_name = htmlspecialchars($_POST['middle']);
    $last_name = htmlspecialchars($_POST['last']);
    $email = htmlspecialchars($_POST['email']);
    $contact = htmlspecialchars($_POST['contact']);
    $password = $_POST['password'];
    $rPassword = $_POST['rpassword'];

    // Check if passwords match
    if ($password != $rPassword) {
        echo "Passwords do not match.";
        exit; // Stop execution if passwords don't match
    }

    // Hash the password before inserting into the database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Correct SQL query: Removed user_id as it should auto-increment
    $sql = "INSERT INTO users (user_id,first_name, middle_name, last_name, email,  password) 
            VALUES ('$contact','$first_name', '$mid_name', '$last_name', '$email', '$hashedPassword')";

    // Execute the query and check if successful
    if (mysqli_query($conn, $sql)) {
        echo "<h1>Registration Successful!</h1>";
    } else {
        echo "<h1>Registration Failed: " . mysqli_error($conn) . "</h1>";
    }

    // Close the database connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <section class="vh-100">
            <div class="container h-100">
                <div class="row d-flex justify-content-center align-items-center h-100">
                    <div class="col-lg-12 col-xl-11">
                        <div class="card text-black" style="border-radius: 25px;">
                            <div class="card-body p-md-5" style="margin: 10px!important;">
                                <div class="row justify-content-center">
                                    <div class="col-md-10 col-lg-6 col-xl-5 order-2 order-lg-1">
                                        <p class="text-center h1 fw-bold mb-5 mx-1 mx-md-4 mt-4" style="margin:10px!important;">Sign up</p>
                                        <form class="mx-1 mx-md-4" action="signup.php" method="POST">
                                            <!-- First Name -->
                                            <div class="d-flex flex-row align-items-center mb-4">
                                                <i class="fas fa-user fa-lg me-3 fa-fw"></i>
                                                <div class="form-outline flex-fill mb-0">
                                                    <label class="form-label" for="form3Example1c">First Name</label>
                                                    <input name="first" type="text" id="form3Example1c" class="form-control" placeholder="Enter first name" required />
                                                </div>
                                            </div>

                                            <!-- Middle Name -->
                                            <div class="d-flex flex-row align-items-center mb-4">
                                                <i class="fas fa-envelope fa-lg me-3 fa-fw"></i>
                                                <div class="form-outline flex-fill mb-0">
                                                    <label class="form-label" for="form3Example3c">Middle Name</label>
                                                    <input name="middle" type="text" id="form3Example3c" class="form-control" placeholder="Enter middle name" />
                                                </div>
                                            </div>

                                            <!-- Last Name -->
                                            <div class="d-flex flex-row align-items-center mb-4">
                                                <i class="fas fa-lock fa-lg me-3 fa-fw"></i>
                                                <div class="form-outline flex-fill mb-0">
                                                    <label class="form-label" for="form3Example4c">Last Name</label>
                                                    <input name="last" type="text" id="form3Example4c" class="form-control" placeholder="Enter last name" />
                                                </div>
                                            </div>

                                            <!-- Email -->
                                            <div class="d-flex flex-row align-items-center mb-4">
                                                <i class="fas fa-key fa-lg me-3 fa-fw"></i>
                                                <div class="form-outline flex-fill mb-0">
                                                    <label class="form-label" for="form3Example4cd">Email</label>
                                                    <input name="email" type="email" id="form3Example4cd" class="form-control" placeholder="Enter a valid email" required />
                                                </div>
                                            </div>

                                            <!-- Contact -->
                                            <div class="d-flex flex-row align-items-center mb-4">
                                                <i class="fas fa-key fa-lg me-3 fa-fw"></i>
                                                <div class="form-outline flex-fill mb-0">
                                                    <label class="form-label" for="form3Example4cd">Contact</label>
                                                    <input name="contact" type="number" id="form3Example4cd" class="form-control" placeholder="Enter an active cell phone number" required />
                                                </div>
                                            </div>

                                            <!-- Password -->
                                            <div class="d-flex flex-row align-items-center mb-4">
                                                <i class="fas fa-key fa-lg me-3 fa-fw"></i>
                                                <div class="form-outline flex-fill mb-0">
                                                    <label class="form-label" for="form3Example4cd">Password</label>
                                                    <input name="password" type="password" id="form3Example4cd" class="form-control" placeholder="Enter a password" required />
                                                </div>
                                            </div>

                                            <!-- Repeat Password -->
                                            <div class="d-flex flex-row align-items-center mb-4">
                                                <i class="fas fa-key fa-lg me-3 fa-fw"></i>
                                                <div class="form-outline flex-fill mb-0">
                                                    <label class="form-label" for="form3Example4cd">Repeat password</label>
                                                    <input name="rpassword" type="password" id="form3Example4cd" class="form-control" placeholder="Enter password again" required />
                                                </div>
                                            </div>

                                            <!-- Submit Button -->
                                            <div class="d-flex justify-content-between mx-4 mb-3 mb-lg-4">
                                                <a href="login.php" class="btn btn-primary">Login</a>
                                                <button type="submit" class="btn btn-primary">Register</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
