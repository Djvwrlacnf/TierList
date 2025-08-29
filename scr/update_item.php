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
$title = $_POST['title'] ?? null;
$description = $_POST['description'] ?? null;

if (!$item_id || !is_numeric($item_id) || !$title) {
    http_response_code(400);
    echo "Некорректные данные";
    exit;
}

// Проверяем, что элемент принадлежит текущему пользователю
$stmt = $db->prepare("SELECT user_id FROM items WHERE id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    http_response_code(404);
    echo "Элемент не найден";
    exit;
}

if (intval($item['user_id']) !== $user_id) {
    http_response_code(403);
    echo "Нет доступа для редактирования";
    exit;
}

// Обновляем title и description
$stmt = $db->prepare("UPDATE items SET title = ?, description = ? WHERE id = ?");
$stmt->bind_param("ssi", $title, $description, $item_id);

if (!$stmt->execute()) {
    http_response_code(500);
    echo "Ошибка при обновлении";
    exit;
}

// После успешного обновления редирект обратно на страницу элемента
header("Location: ../item.php?item_id=" . intval($item_id));
exit;
