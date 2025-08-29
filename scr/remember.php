<?php
require 'db.php';

// Читаем JSON
$data = json_decode(file_get_contents('php://input'), true);
header('Content-Type: application/json');

if (!$data || !isset($data['step'])) {
    echo json_encode(['success'=>false, 'message'=>'Некорректный запрос.']);
    exit;
}

$step = $data['step'];

switch ($step) {

    // Шаг 1: Получение секретного вопроса по email
    case 'get_question':
        if (!isset($data['email'])) {
            echo json_encode(['success'=>false, 'message'=>'Email не указан.']);
            exit;
        }
        $email = $db->real_escape_string($data['email']);
        $sql = "SELECT id, question, token FROM users WHERE email='$email' LIMIT 1";
        $res = $db->query($sql);
        if (!$res || $res->num_rows === 0) {
            echo json_encode(['success'=>false, 'message'=>'Пользователь с таким email не найден.']);
            exit;
        }
        $user = $res->fetch_assoc();
        if ((int)$user['token'] <= 0) {
            echo json_encode(['success'=>false, 'message'=>'У вас закончились попытки. Обратитесь к администратору.']);
            exit;
        }
        echo json_encode([
            'success'=>true,
            'user_id'=>$user['id'],
            'question'=>$user['question'],
            'token'=>(int)$user['token']
        ]);
        exit;

    // Шаг 2: Проверка ответа
    case 'check_answer':
        if (!isset($data['user_id'], $data['answer'])) {
            echo json_encode(['success'=>false, 'message'=>'Некорректные данные.']);
            exit;
        }
        $user_id = (int)$data['user_id'];
        $answer = $data['answer'];

        $sql = "SELECT answer, token FROM users WHERE id=$user_id LIMIT 1";
        $res = $db->query($sql);
        if (!$res || $res->num_rows === 0) {
            echo json_encode(['success'=>false, 'message'=>'Пользователь не найден.']);
            exit;
        }
        $user = $res->fetch_assoc();
        $token = (int)$user['token'];

        if ($token <= 0) {
            echo json_encode(['success'=>false, 'message'=>'Попытки закончились.', 'token'=>0]);
            exit;
        }

        if (password_verify($answer, $user['answer'])) {
            // Верный ответ — восстанавливаем токены до 3
            $db->query("UPDATE users SET token=3 WHERE id=$user_id");
            echo json_encode(['success'=>true]);
        } else {
            // Неверный ответ — уменьшаем токены
            $token--;
            $db->query("UPDATE users SET token=$token WHERE id=$user_id");
            echo json_encode(['success'=>false, 'message'=>'Неверный ответ.', 'token'=>$token]);
        }
        exit;

    // Шаг 3: Сброс пароля
    case 'reset_password':
        if (!isset($data['user_id'], $data['new_password'])) {
            echo json_encode(['success'=>false, 'message'=>'Некорректные данные.']);
            exit;
        }
        $user_id = (int)$data['user_id'];
        $new_hash = password_hash($data['new_password'], PASSWORD_DEFAULT);

        if ($db->query("UPDATE users SET password='$new_hash' WHERE id=$user_id")) {
            echo json_encode(['success'=>true]);
        } else {
            echo json_encode(['success'=>false, 'message'=>'Ошибка при обновлении пароля.']);
        }
        exit;

    default:
        echo json_encode(['success'=>false, 'message'=>'Неизвестный шаг.']);
        exit;
}
