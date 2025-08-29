<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo "Требуется авторизация";
    exit;
}

$user_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Метод не поддерживается";
    exit;
}

$item_id = $_POST['item_id'] ?? null;
$tier = $_POST['tier'] ?? null;

$allowed_tiers = ['S','A','B','C','D','E','F','unranked'];

if (!$item_id || !$tier || !in_array($tier, $allowed_tiers)) {
    http_response_code(400);
    echo "Некорректные данные";
    exit;
}

// Проверяем, что этот item принадлежит текущему пользователю
$stmt = $db->prepare("SELECT id FROM items WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $item_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(403);
    echo "Нет доступа к элементу";
    exit;
}

// Обновляем tier
$stmt = $db->prepare("UPDATE items SET tier = ? WHERE id = ?");
$stmt->bind_param("si", $tier, $item_id);
if (!$stmt->execute()) {
    http_response_code(500);
    echo "Ошибка обновления в базе данных";
    exit;
}

http_response_code(200);
echo "Успешно";
