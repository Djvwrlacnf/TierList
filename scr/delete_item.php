<?php
session_start();
require_once 'db.php';

// Проверка авторизации
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

$currentUserId = $_SESSION['user']['id'];

// Получение ID элемента
$itemId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($itemId <= 0) {
    die("Некорректный ID элемента.");
}

// Проверим, существует ли элемент и принадлежит ли он текущему пользователю
$stmt = $db->prepare("SELECT user_id FROM items WHERE id = ?");
$stmt->bind_param("i", $itemId);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    die("Элемент не найден.");
}

// Для админа можно убрать проверку ownership, если это админский интерфейс
// if ($item['user_id'] !== $currentUserId) {
//     die("Вы не можете удалить этот элемент.");
// }

// Удаляем комментарии к элементу
$stmt = $db->prepare("DELETE FROM comments WHERE item_id = ?");
$stmt->bind_param("i", $itemId);
$stmt->execute();

// Удаляем сам элемент
$stmt = $db->prepare("DELETE FROM items WHERE id = ?");
$stmt->bind_param("i", $itemId);
if ($stmt->execute()) {
    header("Location: ../admin_item.php?msg=deleted");
    exit;
} else {
    die("Ошибка при удалении элемента: " . $stmt->error);
}
