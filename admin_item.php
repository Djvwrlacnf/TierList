<?php
session_start();
require_once 'scr/db.php';

// Проверка прав администратора
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$result = $db->query("SELECT id, title, image_url, description, category, user_id FROM items ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <title>Admin — Объекты</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="css/bootstrap.css" rel="stylesheet" />
    <link href="css/custom.css" rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
</head>

<body class="admin-page">

    <?php include __DIR__ . '/hf/header.php'; ?>

    <!-- Мобильное боковое меню (по умолчанию скрыто) -->
    <nav id="mobileSidebar" class="mobile-sidebar custom-sidebar d-md-none">

        <div class="p-3">
            <h5>Админ-панель</h5>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link active" href="admin_item.php">Объекты</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_users.php">Пользователи</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_comments.php">Комментарии</a></li>
            </ul>
            <button id="mobileSidebarClose" class="btn btn-sm btn-outline-danger mt-3">Закрыть</button>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Десктопное меню -->
            <nav class="col-md-2 d-none d-md-block custom-sidebar py-3">

                <div class="position-sticky">
                    <h5 class="px-3">Админ-панель</h5>
                    <ul class="nav flex-column px-2">
                        <li class="nav-item"><a class="nav-link active" href="admin_item.php">Объекты</a></li>
                        <li class="nav-item"><a class="nav-link" href="admin_users.php">Пользователи</a></li>
                        <li class="nav-item"><a class="nav-link" href="admin_comments.php">Комментарии</a></li>
                    </ul>
                </div>
            </nav>

            <!-- Контент -->
            <main class="col-md-10 ms-sm-auto px-md-4 py-4">

                <!-- Кнопка для мобильного меню — ВНУТРИ main и только для маленьких экранов -->
                <button id="mobileSidebarToggle" class="btn btn-outline-secondary d-md-none mb-3">
                    &#9776; Меню
                </button>

                <h2 class="mb-4">Список объектов</h2>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Изображение</th>
                                <th>Описание</th>
                                <th>Категория</th>
                                <th>Пользователь</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $item['id'] ?></td>
                                    <td><?= htmlspecialchars($item['title']) ?></td>
                                    <td>
                                        <?php if ($item['image_url']): ?>
                                            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="Image" style="max-height: 60px;">
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($item['description']) ?></td>
                                    <td><?= htmlspecialchars($item['category']) ?></td>
                                    <td><?= $item['user_id'] ?></td>
                                    <td>
                                        <div class="d-flex gap-2 align-items-center">
                                            <a href="item.php?item_id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary">Перейти</a>
                                            <a href="scr/delete_item.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Удалить этот объект?')">Удалить</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script src="js/bootstrap.bundle.js"></script>

    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const mobileSidebar = document.getElementById('mobileSidebar');
        const toggleBtn = document.getElementById('mobileSidebarToggle');

        // Создаём overlay элемент для затемнения
        const overlay = document.createElement('div');
        overlay.className = 'mobile-sidebar-overlay';
        document.body.appendChild(overlay);

        const closeSidebar = () => {
          mobileSidebar.classList.remove('show');
          overlay.classList.remove('show');
          document.body.style.overflow = '';
        };

        toggleBtn.addEventListener('click', () => {
          mobileSidebar.classList.add('show');
          overlay.classList.add('show');
          document.body.style.overflow = 'hidden';
        });

        document.getElementById('mobileSidebarClose').addEventListener('click', closeSidebar);
        overlay.addEventListener('click', closeSidebar);
      });
    </script>

</body>
</html>
