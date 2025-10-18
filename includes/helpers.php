<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

function generate_token($data) {
    $now = time();
    $payload = [
        'iat' => $now,
        'exp' => $now + JWT_EXP,
        'data' => $data
    ];
    return JWT::encode($payload, JWT_SECRET, 'HS256');
}

function decode_token($jwt) {
    try {
        return JWT::decode($jwt, new Key(JWT_SECRET, 'HS256'));
    } catch (Exception $e) {
        return null;
    }
}
