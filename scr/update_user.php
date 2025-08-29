<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']['id'])) {
    die("Необходимо войти в систему.");
}

$user_id = (int)$_SESSION['user']['id'];

if (!isset($_POST['action'])) {
    die("Некорректный запрос.");
}

$action = $_POST['action'];

switch ($action) {
    case 'update_username':
        $new_username = trim($_POST['username']);
        $stmt = $db->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->bind_param("si", $new_username, $user_id);
        if ($stmt->execute()) {
            header("Location: ../user.php?success=username");
        } else {
            header("Location: ../user.php?error=username");
        }
        break;

    case 'update_password':
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];

        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user || !password_verify($old_password, $user['password'])) {
            header("Location: ../user.php?error=wrong_old_password");
            exit;
        }

        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_hash, $user_id);
        $stmt->execute();

        header("Location: ../user.php?success=password");
        break;

    case 'update_secret':
        $question = trim($_POST['question']);
        $answer = trim($_POST['answer']);

        $answer_hash = password_hash($answer, PASSWORD_DEFAULT);

        $stmt = $db->prepare("UPDATE users SET question = ?, answer = ?, token = 3 WHERE id = ?");
        $stmt->bind_param("ssi", $question, $answer_hash, $user_id);
        $stmt->execute();

        header("Location: ../user.php?success=secret");
        break;

    default:
        die("Неизвестное действие.");
}


exit;
