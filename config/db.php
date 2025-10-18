<?php
$host = 'localhost';
$dbname   = 'ecommerce_system';
$user = 'root'; 
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>