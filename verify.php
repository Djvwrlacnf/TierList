<?php
require_once __DIR__ . '/scr/db.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    exit('Неверная ссылка подтверждения');
}

$stmt = $db->prepare("SELECT id FROM users WHERE email_token = ? AND status = 'inactive'");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user) {
    exit('Ссылка недействительна или аккаунт уже подтверждён.');
}

// Активируем аккаунт
$stmt = $db->prepare("UPDATE users SET status = 'active', email_token = NULL WHERE id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();

echo "Ваш email подтверждён! <a href='login.php'>Войти</a>";
