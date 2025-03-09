<?php 
include 'dbconnect.php';

// Only proceed if the form is submitted and GET data is available
if (isset($_GET['email']) && isset($_GET['password'])) {
    $email = $_GET['email'];
    $password = $_GET['password'];

    // Now you can safely use the values for your SQL query
    $sql = "SELECT user_id FROM users WHERE email = '$email' AND password = '$password'";

    // Execute the query and handle the result
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        // Login successful
        echo "Login Successful!";
        // You can also redirect the user or start a session here
    } else {
        // Invalid login
        echo "Invalid email or password!";
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
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
    <section class="h-100 gradient-form">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-xl-12">
                    <div class="card rounded-3 text-black">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="card-body justify-content-center p-md-5 mx-md-4">

                                    <div class="text-center">
                                        <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-login-form/lotus.webp"
                                            style="width: 185px;" alt="logo">
                                        <h4 class="mt-1 mb-5 pb-1">We are The Group 04</h4>
                                    </div>

                                    <!-- Update form action to self-submit -->
                                    <form action="index.php" method="get"> <!-- Form will now submit to the current page -->

                                        <div data-mdb-input-init class="form-outline mb-4">
                                            <label class="form-label" for="form2Example11">Username</label>
                                            <input type="email" name="email" id="form2Example11" class="form-control"
                                                placeholder="Enter your email" required />
                                        </div>

                                        <div data-mdb-input-init class="form-outline mb-4">
                                            <label class="form-label" for="form2Example22">Password</label>
                                            <input name="password" type="password" id="form2Example22" class="form-control" placeholder="Enter your password" required />
                                        </div>

                                        <div class="d-flex justify-content-between pt-1 mb-5 pb-1">
                                            <a class="text-muted" href="#!">Forgot password?</a>
                                            <button data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-block fa-lg gradient-custom-2 mb-3" type="submit">Log in</button> <!-- Changed type to submit -->
                                        </div>

                                        <div class="d-flex align-items-center justify-content-center pb-4">
                                            <p class="mb-0 me-2">Don't have an account?</p>
                                            <a href="signup.php" class="btn btn-success" data-mdb-button-init data-mdb-ripple-init>Create new</a>
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
</body>

</html>
