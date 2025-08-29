<?php
session_start();
require_once 'scr/db.php';

// Проверка прав администратора
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Обработка удаления комментария
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id'])) {
    $commentId = (int)$_POST['delete_comment_id'];
    $stmt = $db->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->bind_param("i", $commentId);
    $stmt->execute();
    $stmt->close();
}

// Получение списка комментариев
$result = $db->query("SELECT id, item_id, user_id, content, datetime, answer_id FROM comments ORDER BY datetime DESC");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Admin — Комментарии</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="css/bootstrap.css" rel="stylesheet" />
    <link href="css/custom.css" rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
    <style>
        .sidebar {
            background-color: #121212 !important;
            min-height: 100vh;
        }

        .sidebar .nav-link {
            color: #ccc;
        }

        .sidebar .nav-link.active {
            color: #fff;
            font-weight: bold;
        }

        .sidebar .nav-link:hover {
            color: #fff;
        }

        .sidebar h5 {
            color: #eee;
        }
    </style>
</head>

<body class="admin-page">
    <?php include __DIR__ . '/hf/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Боковое меню -->
            <nav class="col-md-2 d-none d-md-block sidebar py-3">
                <div class="position-sticky">
                    <h5 class="px-3">Админ-панель</h5>
                    <ul class="nav flex-column px-2">
                        <li class="nav-item">
                            <a class="nav-link" href="admin_item.php">Объекты</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_users.php">Пользователи</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="admin_comments.php">Комментарии</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Контент -->
            <main class="col-md-10 ms-sm-auto px-md-4 py-4">
                <h2 class="mb-4">Список комментариев</h2>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Item ID</th>
                                <th>User ID</th>
                                <th>Содержимое</th>
                                <th>Дата/время</th>
                                <th>Ответ на</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($comment = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $comment['id'] ?></td>
                                    <td><?= $comment['item_id'] ?></td>
                                    <td><?= $comment['user_id'] ?></td>
                                    <td><?= htmlspecialchars($comment['content']) ?></td>
                                    <td><?= $comment['datetime'] ?></td>
                                    <td><?= $comment['answer_id'] ?? '—' ?></td>
                                    <td class="d-flex gap-2">
                                        <a class="btn btn-sm btn-outline-primary"
                                           href="item.php?item_id=<?= $comment['item_id'] ?>&highlight_comment=<?= $comment['id'] ?>"
                                           target="_blank">Перейти</a>
                                        <form method="POST" onsubmit="return confirm('Удалить комментарий?');">
                                            <input type="hidden" name="delete_comment_id" value="<?= $comment['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Удалить</button>
                                        </form>
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
</body>
</html>
