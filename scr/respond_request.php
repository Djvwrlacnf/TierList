<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user']['id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$user_id = $_SESSION['user']['id'];
$request_id = intval($_POST['request_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!in_array($action, ['accept', 'decline'], true)) {
    http_response_code(400);
    exit('Invalid action');
}

// Проверяем, принадлежит ли заявка текущему пользователю
$stmt = $db->prepare("SELECT requester_id, receiver_id FROM friends WHERE id = ? AND status = 'pending'");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$stmt->bind_result($requester_id, $receiver_id);
$stmt->fetch();
$stmt->close();

if ($receiver_id !== $user_id) {
    http_response_code(403);
    exit('Forbidden');
}

// Действие
if ($action === 'accept') {
    $stmt = $db->prepare("UPDATE friends SET status = 'accepted' WHERE id = ?");
} else {
    $stmt = $db->prepare("DELETE FROM friends WHERE id = ?");
}

$stmt->bind_param("i", $request_id);
$stmt->execute();
$stmt->close();

http_response_code(200);
echo 'Success';
