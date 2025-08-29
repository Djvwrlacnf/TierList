<?php
session_start();
require_once 'db.php';

$user_id = $_SESSION['user']['id'] ?? null;
$item_id = $_POST['item_id'] ?? null;
$content = trim($_POST['content'] ?? '');
$answer_id = $_POST['answer_id'] ?? null;

if (!$user_id || !$item_id || !$content) {
    http_response_code(400);
    exit('Ошибка ввода.');
}

$stmt = $db->prepare("INSERT INTO comments (item_id, user_id, content, answer_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iisi", $item_id, $user_id, $content, $answer_id);
$stmt->execute();

header("Location: ../item.php?item_id=$item_id");
