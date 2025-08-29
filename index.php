<?php
session_start();
require_once __DIR__ . '/scr/db.php';

$username = $_SESSION['user']['username'] ?? null;

$friendId = isset($_GET['friend_id']) ? (int)$_GET['friend_id'] : null;
$friendName = null;

// Если есть friend_id, попробуем получить имя друга из БД для показа
if ($friendId) {
    $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $friendId);
    $stmt->execute();
    $result = $stmt->get_result();
    $friend = $result->fetch_assoc();
    if ($friend) {
        $friendName = htmlspecialchars($friend['username']);
    } else {
        // Друг не найден, можно очистить $friendId чтобы показать обычный интерфейс
        $friendId = null;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tier List - Главная</title>
  <link href="css/bootstrap.css" rel="stylesheet" />
  <link href="css/custom.css" rel="stylesheet" />
  <link href="css/style.css" rel="stylesheet" />
</head>

<body>

  <?php include __DIR__ . '/hf/header.php'; ?>

  <main class="container text-center">
    <h1 class="mb-4">Добро пожаловать в Tier List!</h1>

    <?php if ($username): ?>

      <?php if ($friendId): ?>
        <p class="lead mb-4">
          Вы просматриваете тир-листы пользователя <strong><?= $friendName ?></strong>.
          <br>
          Выберите категорию для просмотра его тир-листов:
        </p>
        <div class="row justify-content-center g-3">
          <div class="col-12 col-md-4">
            <a href="tierlist.php?cat=game&friend_id=<?= $friendId ?>" class="btn btn-primary w-100 py-3 fs-5">Игры</a>
          </div>
          <div class="col-12 col-md-4">
            <a href="tierlist.php?cat=movie&friend_id=<?= $friendId ?>" class="btn btn-success w-100 py-3 fs-5">Фильмы</a>
          </div>
          <div class="col-12 col-md-4">
            <a href="tierlist.php?cat=series&friend_id=<?= $friendId ?>" class="btn btn-warning w-100 py-3 fs-5">Сериалы</a>
          </div>
        </div>
        <div class="mt-4">
          <a href="index.php" class="btn btn-outline-secondary">Вернуться к своим тир-листам</a>
        </div>

      <?php else: ?>
        <p class="lead">Выберите категорию для просмотра или создания тир-листа:</p>
        <div class="row justify-content-center g-3">
          <div class="col-12 col-md-4">
            <a href="tierlist.php?cat=game" class="btn btn-primary w-100 py-3 fs-5">Игры</a>
          </div>
          <div class="col-12 col-md-4">
            <a href="tierlist.php?cat=movie" class="btn btn-success w-100 py-3 fs-5">Фильмы</a>
          </div>
          <div class="col-12 col-md-4">
            <a href="tierlist.php?cat=series" class="btn btn-warning w-100 py-3 fs-5">Сериалы</a>
          </div>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <p class="lead">Пожалуйста, войдите или зарегистрируйтесь, чтобы пользоваться тир-листами.</p>
      <a href="login.php" class="btn btn-primary me-2">Войти</a>
      <a href="register.php" class="btn btn-outline-primary">Регистрация</a>
    <?php endif; ?>
  </main>

  <script src="js/bootstrap.bundle.js"></script>
</body>

</html>
