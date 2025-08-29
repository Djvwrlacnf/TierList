<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'], $_POST['url'])) {
    $item_id = intval($_POST['item_id']);
    $url = trim($_POST['url']);
    $user_id = $_SESSION['user']['id'] ?? null;

    if (!$user_id) {
        header("Location: ../item.php?item_id=" . $item_id);
        exit;
    }

    // Проверка, что пользователь — владелец item
    $stmt = $db->prepare("SELECT user_id FROM items WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $owner_id = $row['user_id'] ?? null;

    if ($owner_id == $user_id) {
        $stmt = $db->prepare("INSERT INTO images (user_id, item_id, url) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $item_id, $url);
        if (!$stmt->execute()) {
            die("Ошибка вставки: " . $stmt->error);
        }
        // Для отладки:
        // echo "Изображение добавлено успешно";
        // exit;
    } else {
        die("Вы не являетесь владельцем этого элемента.");
    }
}

header("Location: ../item.php?item_id=" . $item_id);
exit;
