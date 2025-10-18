<?php
// includes/functions.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Flash message setter
 */
function flash_set($type, $msg) {
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    $_SESSION['flash'][$type] = $msg;
}

/**
 * Flash message getter
 */
function flash_get($type) {
    if (isset($_SESSION['flash'][$type])) {
        $msg = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $msg;
    }
    return null;
}

/**
 * Display all flash messages (use inside HTML)
 */
function flash_display() {
    if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $type => $message) {
            $color = ($type === 'error') ? '#dc3545' : '#28a745'; // red or green
            echo "<div style='background:$color;color:white;padding:10px;border-radius:6px;margin-bottom:10px;'>$message</div>";
        }
        unset($_SESSION['flash']);
    }
}

/**
 * Log in user and store in session
 */
function login_user($user) {
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'         => $user['id'],
        'email'      => $user['email'],
        'first_name' => $user['first_name'],
        'last_name'  => $user['last_name'],
        'role'       => $user['role']
    ];
}

/**
 * Log out user completely
 */
function logout_user() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

/**
 * Get current logged-in user or null
 */
function current_user() {
    return $_SESSION['user'] ?? null;
}

/**
 * Check if a user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user']);
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Require user to be logged in
 */
function require_login() {
    if (!is_logged_in()) {
        flash_set('error', 'Please login to continue.');
        redirect('/login.php');
    }
}

/**
 * Require specific user role or show 403
 */
function require_role($roles) {
    $user = current_user();
    if (!$user) {
        flash_set('error', 'Please login first.');
        redirect('/login.php');
    }

    // Allow multiple roles as array or single string
    if (is_string($roles)) $roles = [$roles];

    if (!in_array($user['role'], $roles)) {
        http_response_code(403);
        echo "<h2 style='color:#dc3545;text-align:center;margin-top:20px;'>403 Forbidden</h2>";
        echo "<p style='text-align:center;'>You do not have permission to access this page.</p>";
        exit;
    }
}
?>
