<?php

/* $servername = "172.17.0.1:3314";
$username = "nunzia";
$password = "j2effphAy4.550996.";
$dbname = "reserve2"; */

//$conn = mysqli_connect('mysqli80.r103.websupport.se', 'nunzia', $password , $dbname, port: 3314);

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
