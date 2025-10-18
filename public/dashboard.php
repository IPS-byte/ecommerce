<?php
ini_set('session.cookie_path', '/');
session_start();

include '../includes/functions.php'; 
require_login(); // ensure user logged in

$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Welcome to the Dashboard, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
</body>
</html>