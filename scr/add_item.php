<?php
require_once 'db.php';

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$category = trim($_POST['category'] ?? '');
$image_url = trim($_POST['image_url'] ?? '');
$user_id = intval($_POST['user_id'] ?? 0);

// Проверка обязательных полей
if (empty($title) || empty($category) || empty($image_url) || $user_id === 0) {
    echo "Пожалуйста, заполните все обязательные поля.";
    exit;
}

// Валидация URL
if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
    echo "Некорректная ссылка на изображение.";
    exit;
}

$description = ($description === '') ? null : $description;

$sql = "INSERT INTO items (title, description, category, image_url, user_id)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $db->prepare($sql);
if (!$stmt) {
    echo "Ошибка подготовки запроса: " . $db->error;
    exit;
}

$stmt->bind_param('ssssi', $title, $description, $category, $image_url, $user_id);

if ($stmt->execute()) {
    $encoded_category = urlencode($category);
    header("Location: ../tierlist.php?cat=$encoded_category");
    exit;
} else {
    echo "Ошибка при добавлении элемента: " . $stmt->error;
}
