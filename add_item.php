<?php
session_start();
require_once __DIR__ . '/scr/db.php';

if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}

$allowed_cats = ['game' => 'Игры', 'movie' => 'Фильмы', 'series' => 'Сериалы'];
$cat = $_GET['cat'] ?? '';

if (!array_key_exists($cat, $allowed_cats)) {
  http_response_code(400);
  echo "Неверная категория";
  exit;
}

$username = $_SESSION['user']['username'];
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Добавить элемент</title>
  <link href="css/bootstrap.css" rel="stylesheet">
  <link href="css/custom.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>

<body>

  <header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
      <div class="container">
        <a class="navbar-brand" href="index.php">Tier List</a>
        <div class="collapse navbar-collapse">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="user.php">Привет, <?= htmlspecialchars($username) ?></a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php">Выйти</a></li>
          </ul>
        </div>
      </div>
    </nav>
  </header>

  <main class="container">
    <h1 class="mb-4">Добавить <?= htmlspecialchars(mb_strtolower($allowed_cats[$cat])) ?></h1>

    <form action="scr/add_item.php" method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="title" class="form-label">Заголовок</label>
        <input type="text" class="form-control" name="title" id="title" required>
      </div>

      <div class="mb-3">
        <label for="image_url" class="form-label">Ссылка на изображение</label>
        <input class="form-control" type="url" name="image_url" id="image_url" placeholder="https://example.com/image.jpg" required oninput="previewImageUrl(event)">
      </div>

      <div class="mb-3">
        <img id="preview" src="images/default.jpg" alt="Предпросмотр" style="max-height: 200px; display: block;">
      </div>

      <input type="hidden" name="category" value="<?= htmlspecialchars($cat) ?>">
      <input type="hidden" name="user_id" value="<?= htmlspecialchars($_SESSION['user']['id']) ?>">

      <button type="submit" class="btn btn-primary">Добавить</button>
      <a href="tierlist.php?cat=<?= urlencode($cat) ?>" class="btn btn-secondary">Назад</a>
    </form>

  </main>

  <script src="js/bootstrap.bundle.js"></script>
  <script>
    function previewImageUrl(event) {
      const preview = document.getElementById('preview');
      preview.src = event.target.value || 'images/default.jpg';
    }
  </script>

</body>

</html>