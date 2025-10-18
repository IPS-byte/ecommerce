<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "ecommerce_pr";

$mysqli = new mysqli($host, $user, $pass, $dbname);

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}
?>
