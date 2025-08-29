<?php
session_start();
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$current_user_id = $_SESSION['user']['id'];
$target_user_id = (int)($_POST['user_id'] ?? 0);

if (!$target_user_id || $target_user_id === $current_user_id) {
    echo json_encode(['success' => false, 'error' => 'Некорректный ID']);
    exit;
}

// Проверка на существование уже существующей заявки
$check = $db->prepare("
    SELECT id FROM friends 
    WHERE (requester_id = ? AND receiver_id = ?) 
       OR (receiver_id = ? AND requester_id = ?)
");
$check->bind_param("iiii", $current_user_id, $target_user_id, $current_user_id, $target_user_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Запрос уже существует']);
    exit;
}

$stmt = $db->prepare("
    INSERT INTO friends (requester_id, receiver_id, status, created_at) 
    VALUES (?, ?, 'pending', NOW())
");
$stmt->bind_param("ii", $current_user_id, $target_user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Ошибка базы данных']);
}
