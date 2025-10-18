<?php
require_once 'helpers.php';
$token = $_GET['token'] ?? '';
if (!$token) { echo "Invalid link"; exit; }

$c = db();
$stmt = $c->prepare("SELECT id,user_id,expires_at,used FROM email_verifications WHERE token=? AND type='email_verify' LIMIT 1");
$stmt->bind_param('s', $token); $stmt->execute(); $res = $stmt->get_result(); $row = $res->fetch_assoc(); $stmt->close();
if (!$row) { echo "Invalid or expired link"; exit; }
if ($row['used']) { echo "Already used"; exit; }
if (new DateTime($row['expires_at']) < new DateTime()) { echo "Token expired"; exit; }

// mark verified
$stmt = $c->prepare("UPDATE users SET email_verified=1 WHERE id = ?");
$stmt->bind_param('i', $row['user_id']); $stmt->execute(); $stmt->close();
$stmt = $c->prepare("UPDATE email_verifications SET used=1 WHERE id = ?"); $stmt->bind_param('i', $row['id']); $stmt->execute(); $stmt->close();

log_event($row['user_id'], 'email_verified');
echo "Email verified — you can now login.";
