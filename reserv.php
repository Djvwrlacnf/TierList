<?php
require 'scr/db.php';
session_start();

// Жёстко заданный секретный код (меняй на свой)
define('ADMIN_RESET_CODE', 'MySuperSecret123');

// Проверяем, ввёл ли админ секретный код
if (isset($_POST['secret_code'])) {
    $code = trim($_POST['secret_code']);
    if (hash_equals(ADMIN_RESET_CODE, $code)) {
        $_SESSION['admin_verified'] = true;
    } else {
        $error = "Неверный код.";
    }
}


// Если админ подтвердился и ввёл данные пользователя
if (isset($_POST['user_id'], $_POST['email'], $_POST['new_password']) && !empty($_SESSION['admin_verified'])) {
    $user_id = (int)$_POST['user_id'];
    $email = $db->real_escape_string($_POST['email']);
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $res = $db->query("SELECT id FROM users WHERE id=$user_id AND email='$email' LIMIT 1");
    if ($res && $res->num_rows > 0) {
        if ($db->query("UPDATE users SET password='$new_password' WHERE id=$user_id")) {
            $success = "Пароль успешно изменён для пользователя #$user_id.";
        } else {
            $error = "Ошибка при изменении пароля.";
        }
    } else {
        $error = "Пользователь с таким ID и email не найден.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Сброс пароля администратором</title>
</head>
<body>
<h2>Админ-сброс пароля</h2>

<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
<?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>

<?php if (empty($_SESSION['admin_verified'])): ?>
    <form method="POST">
        <label>Секретный код:</label><br>
        <input type="password" name="secret_code" required>
        <button type="submit">Подтвердить</button>
    </form>
<?php else: ?>
    <form method="POST">
        <label>ID пользователя:</label><br>
        <input type="number" name="user_id" required><br><br>

        <label>Email пользователя:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Новый пароль:</label><br>
        <input type="text" name="new_password" required><br><br>

        <button type="submit">Сбросить пароль</button>
    </form>
<?php endif; ?>

</body>
</html>
