<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../public/login.php");
    exit;
}

// Auto-expire 30 mins
if(time() - $_SESSION['last_activity'] > 1800){
    session_unset();
    session_destroy();
    header("Location: ../auth/login.php?expired=1");
    exit;
}
$_SESSION['last_activity'] = time();
?>
