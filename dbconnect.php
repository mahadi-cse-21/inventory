<?php
$host = '127.0.0.1';  // This should be the host (localhost or IP address of the server)
$user = 'root';        // Database username
$pass = '';            // Database password (empty in this case)
$dbname = 'demo';      // The name of the database you're connecting to

// Establish the connection
$conn = mysqli_connect($host, $user, $pass, $dbname);

?>