<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['image_id'])) {
    $image_id = intval($_POST['image_id']);
    $user_id = $_SESSION['user']['id'] ?? null;

    if (!$user_id) {
        // Неавторизованный пользователь
        header("Location: ../index.php");
        exit;
    }

    // Получаем item_id и user_id владельца картинки
    $stmt = $db->prepare("SELECT item_id, user_id FROM images WHERE id = ?");
    $stmt->bind_param("i", $image_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $image = $result->fetch_assoc();

    if ($image && intval($image['user_id']) === $user_id) {
        $item_id = $image['item_id'];

        // Удаляем изображение
        $stmt = $db->prepare("DELETE FROM images WHERE id = ?");
        $stmt->bind_param("i", $image_id);
        $stmt->execute();

        header("Location: ../item.php?item_id=" . intval($item_id));
        exit;
    }
}

// Если что-то не так, редирект на главную
header("Location: ../index.php");
exit;
