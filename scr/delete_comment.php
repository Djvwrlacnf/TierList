<?php
session_start();
require_once 'db.php';

$id = $_POST['id'] ?? null;
if (!$id) {
    http_response_code(400);
    exit;
}

$user_id = $_SESSION['user']['id'] ?? null;
$is_admin = ($_SESSION['user']['role'] ?? '') === 'admin';

$stmt = $db->prepare("SELECT user_id FROM comments WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$comment = $result->fetch_assoc();

if (!$comment || ($comment['user_id'] != $user_id && !$is_admin)) {
    http_response_code(403);
    exit;
}

// Удаляем сам комментарий и вложенные ответы
$db->query("DELETE FROM comments WHERE answer_id = $id");
$db->query("DELETE FROM comments WHERE id = $id");
