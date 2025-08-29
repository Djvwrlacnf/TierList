<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Метод не поддерживается.";
    exit;
}

$title = trim($_POST['title'] ?? '');
$category = $_POST['category'] ?? '';
$allowed_cats = ['game', 'movie', 'series'];

if ($title === '' || !in_array($category, $allowed_cats, true)) {
    http_response_code(400);
    echo "Неверные данные формы.";
    exit;
}

// Проверка и загрузка изображения
if (!isset($_FILES['image_url']) || $_FILES['image_url']['error'] !== UPLOAD_ERR_OK) {
    echo "Ошибка загрузки изображения.";
    exit;
}

$upload_dir = '../images/';
$ext = pathinfo($_FILES['image_url']['name'], PATHINFO_EXTENSION);
$filename = uniqid('img_', true) . '.' . strtolower($ext);
$destination = $upload_dir . $filename;

if (!move_uploaded_file($_FILES['image_url']['tmp_name'], $destination)) {
    echo "Не удалось сохранить изображение.";
    exit;
}

// Подготовка и вставка данных
$image_url = 'images/' . $filename;
$tier = 'unranked';
$description = null;

$stmt = $db->prepare("INSERT INTO items (title, image_url, description, category, tier, user_id) VALUES (?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    echo "Ошибка подготовки запроса: " . $db->error;
    exit;
}
$stmt->bind_param("sssssi", $title, $image_url, $description, $category, $tier, $user_id);

if ($stmt->execute()) {
    header("Location: ../tierlist.php?cat=" . urlencode($category));
    exit;
} else {
    echo "Ошибка добавления элемента: " . $stmt->error;
}
