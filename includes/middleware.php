<?php
session_start();

function require_login($roles = []) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /auth/login.php");
        exit();
    }

    if ($roles && !in_array($_SESSION['role'], $roles)) {
        http_response_code(403);
        echo "Access denied.";
        exit();
    }
}
