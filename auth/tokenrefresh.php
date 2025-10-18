<?php
require_once 'helpers.php';
$raw = $_POST['tokenrefresh'] ?? $_GET['tokenrefresh'] ?? null;
if (!$raw) { http_response_code(400); echo "Missing token"; exit; }

$row = find_refresh_record($raw);
if (!$row) { http_response_code(401); echo "Invalid or expired refresh token"; exit; }

// get user
$c = db(); $stmt = $c->prepare("SELECT id,full_name,role FROM users WHERE id=?"); $stmt->bind_param('i',$row['user_id']); $stmt->execute(); $u = $stmt->get_result()->fetch_assoc(); $stmt->close();
if (!$u) { http_response_code(401); echo "Invalid token user"; exit; }

// rotate
$new_refresh = create_refresh_token($u['id'], $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null);
revoke_refresh_by_id((int)$row['id']);

$jwt = create_jwt(['user_id'=>$u['id'],'full_name'=>$u['full_name'],'role'=>$u['role']]);
header('Content-Type: application/json');
echo json_encode(['access_token'=>$jwt,'expires_in'=>JWT_EXP,'refresh_token'=>$new_refresh]);
