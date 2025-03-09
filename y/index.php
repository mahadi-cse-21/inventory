<?php
    include('connect.php');
    session_start();
    if( !isset($_SESSION['id'])  ){
        header('location: login.php');
        exit;
    }
?>

<!DOCTYPE html>
    <head>
        <title>Homepage</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    </head>
    <body>
        <a href="logouthelper.php">Logout</a>
        <div class="container">
        <form method="POST" action="inserthelper.php">
            <h5>Name</h5>
            <input type="text" name="n">

            <h5>Roll</h5>
            <input type="text" name="r">

            <h5>Email</h5>
            <input type="text" name="e">

            <h5>Password</h5>
            <input type="password" name="p">

            <button type="submit" name="b">Create New User</button>
        </form>


        <a href="deletehelper2.php">Delete all</a>
        <table class="table">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">SL</th>
                    <th scope="col">Roll</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Password</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $stmt = $conn->prepare("SELECT * FROM students");
                $stmt->execute();
                $res = $stmt->get_result();
                $sl = 0;
                while($row = $res->fetch_assoc()){
                    $sl++;
            ?>   
                <tr>
                    <td><?php echo $sl;?></td>
                    <td><?php echo $row['roll'];?></td>
                    <td><?php echo $row['name'];?></td>
                    <td><?php echo $row['email'];?></td>
                    <td><?php echo $row['password']?></td>
                    <td><a href = "deletehelper.php?x=<?php echo $row['roll'];?>">Delete</a> <a href="#">Update Email</a> </td>
                </tr>
            <?php
                }
            ?>
            </tbody>
            </table>
        </div>


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
</html>