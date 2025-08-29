<?php
session_start();
// Удаляем все данные сессии
$_SESSION = [];
session_destroy();
// Перенаправляем на главную
header('Location: index.php');
exit;
