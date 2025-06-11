<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "reserve"; 

$conn = mysqli_connect($servername, $username, $password, $dbname);



if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4"); // charset sicuro contro injection

?>
