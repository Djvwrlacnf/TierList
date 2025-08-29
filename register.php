<?php
session_start();
if (isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Регистрация — Tier List</title>
  <link href="css/bootstrap.css" rel="stylesheet" />
  <link href="css/custom.css" rel="stylesheet" />
</head>

<body>

  <?php include __DIR__ . '/hf/header.php'; ?>

  <main class="container d-flex flex-column align-items-center" style="min-height: 60vh;">
    <div class="w-100" style="max-width: 400px;">
      <h2 class="mb-4 text-center">Регистрация</h2>

      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
      <?php elseif (isset($_GET['success'])): ?>
        <div class="alert alert-success">Вы успешно зарегистрировались. Теперь можете войти в аккаунт.</div>
      <?php endif; ?>

      <form action="scr/auth.php" method="post" novalidate>
        <input type="hidden" name="action" value="register" />

        <div class="mb-3">
          <label for="username" class="form-label">Имя пользователя</label>
          <input id="username" name="username" type="text" class="form-control" required autofocus />
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input id="email" name="email" type="email" class="form-control" required />
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Пароль</label>
          <input id="password" name="password" type="password" class="form-control" required minlength="6" />
        </div>

        <div class="mb-3">
          <label for="password_confirm" class="form-label">Подтвердите пароль</label>
          <input id="password_confirm" name="password_confirm" type="password" class="form-control" required minlength="6" />
        </div>

        <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
      </form>

      <p class="mt-3 text-center">
        Уже есть аккаунт? <a href="login.php" class="link-primary">Войти</a>
      </p>
      <p class="mt-3 text-center">
        Забыли пароль? <a href="remember.php" class="link-primary">Восстановить</a>
      </p>
    </div>
  </main>

  <script src="js/bootstrap.bundle.js"></script>
</body>

</html>