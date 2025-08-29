<?php
session_start();
require_once 'scr/db.php';

// Проверка прав администратора
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Обработка смены статуса пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['new_status'])) {
    $userId = (int)$_POST['user_id'];
    $allowedStatuses = ['active', 'block', 'inactive'];
    $newStatus = in_array($_POST['new_status'], $allowedStatuses) ? $_POST['new_status'] : 'active';

    $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $userId);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_users.php');
    exit;
}

// Получение списка пользователей
$result = $db->query("SELECT id, username, email, role, status FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Admin — Пользователи</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="css/bootstrap.css" rel="stylesheet" />
    <link href="css/custom.css" rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
</head>

<body class="admin-page">
<?php include __DIR__ . '/hf/header.php'; ?>

<nav id="mobileSidebar" class="mobile-sidebar custom-sidebar d-md-none">
    <div class="p-3">
        <h5>Админ-панель</h5>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="admin_item.php">Объекты</a></li>
            <li class="nav-item"><a class="nav-link active" href="admin_users.php">Пользователи</a></li>
            <li class="nav-item"><a class="nav-link" href="admin_comments.php">Комментарии</a></li>
        </ul>
        <button id="mobileSidebarClose" class="btn btn-sm btn-outline-danger mt-3">Закрыть</button>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block custom-sidebar py-3">
            <div class="position-sticky">
                <h5 class="px-3">Админ-панель</h5>
                <ul class="nav flex-column px-2">
                    <li class="nav-item"><a class="nav-link" href="admin_item.php">Объекты</a></li>
                    <li class="nav-item"><a class="nav-link active" href="admin_users.php">Пользователи</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_comments.php">Комментарии</a></li>
                </ul>
            </div>
        </nav>

        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            <button id="mobileSidebarToggle" class="btn btn-outline-secondary d-md-none mb-3">
                &#9776; Меню
            </button>

            <h2 class="mb-4">Список пользователей</h2>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Имя пользователя</th>
                            <th>Email</th>
                            <th>Роль</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td>
                                <form method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <select name="new_status" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                        <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>active</option>
                                        <option value="block" <?= $user['status'] === 'block' ? 'selected' : '' ?>>block</option>
                                        <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>inactive</option>
                                    </select>
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const mobileSidebar = document.getElementById('mobileSidebar');
    const toggleBtn = document.getElementById('mobileSidebarToggle');

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
