<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'db.php';

function redirectWithError($url, $error)
{
    header("Location: $url?error=" . urlencode($error));
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        redirectWithError('../login.php', 'Пожалуйста, заполните все поля');
    }

    // Проверяем пользователя в БД по email
    $stmt = $db->prepare("SELECT id, username, email, password, role, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if (!$user) {
        redirectWithError('../login.php', 'Пользователь с таким email не найден');
    }

    if (!password_verify($password, $user['password'])) {
        redirectWithError('../login.php', 'Неверный пароль');
    }

    if ($user['status'] === 'inactive') {
        redirectWithError('../login.php', 'Подождите, пока вам предоставят доступ');
    }

    if ($user['status'] !== 'active') {
        redirectWithError('../login.php', 'Аккаунт заблокирован');
    }

    // Успешный вход
    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role'],
        'status' => $user['status']
    ];

    header('Location: ../index.php');
    exit;
}

if ($action === 'register') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($username === '' || $email === '' || $password === '' || $password_confirm === '') {
        redirectWithError('../register.php', 'Пожалуйста, заполните все поля');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectWithError('../register.php', 'Некорректный email');
    }
    if ($password !== $password_confirm) {
        redirectWithError('../register.php', 'Пароли не совпадают');
    }
    if (strlen($password) < 6) {
        redirectWithError('../register.php', 'Пароль должен быть не менее 6 символов');
    }

    // Проверяем, что email не занят
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        redirectWithError('../register.php', 'Email уже зарегистрирован');
    }

    // Проверяем, что username не занят
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        redirectWithError('../register.php', 'Имя пользователя занято');
    }

    // Создаём пользователя с ролью user, статус будет по умолчанию (inactive)
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
    $stmt->bind_param("sss", $username, $email, $hash);
    if (!$stmt->execute()) {
        redirectWithError('../register.php', 'Ошибка регистрации, попробуйте позже');
    }

    // НЕ авторизуем сразу — ждём активации администратора
    header('Location: ../login.php?success=' . urlencode('Регистрация прошла успешно. Ожидайте активации аккаунта.'));
    exit;
}

// Если действие не распознано — редирект на главную
header('Location: ../index.php');
exit;
