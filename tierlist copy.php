<?php
session_start();
require_once __DIR__ . '/scr/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$currentUserId = $_SESSION['user']['id'];
$currentUsername = $_SESSION['user']['username'];

$allowed_cats = ['game' => 'Игры', 'movie' => 'Фильмы', 'series' => 'Сериалы'];
$cat = $_GET['cat'] ?? '';
if (!array_key_exists($cat, $allowed_cats)) {
    http_response_code(400);
    echo "Неверная категория";
    exit;
}

// Проверяем, есть ли friend_id и валиден ли он
$friendId = isset($_GET['friend_id']) ? (int)$_GET['friend_id'] : null;
$viewingUserId = $currentUserId; // по умолчанию — свои тир-листы
$viewingUsername = $currentUsername;
$isFriendView = false;

if ($friendId && $friendId !== $currentUserId) {
    // Проверим, существует ли такой пользователь
    $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $friendId);
    $stmt->execute();
    $result = $stmt->get_result();
    $friendData = $result->fetch_assoc();
    if (!$friendData) {
        // Друг не найден — ошибка 404 или вернём к своим
        http_response_code(404);
        echo "Пользователь не найден";
        exit;
    }
    $viewingUserId = $friendId;
    $viewingUsername = $friendData['username'];
    $isFriendView = true;
}

// Инициализируем массивы для айтемов
$ranks = ['S', 'A', 'B', 'C', 'D', 'E', 'F'];
$items_by_rank = [];
foreach ($ranks as $r) {
    $items_by_rank[$r] = [];
}
$unranked_items = [];

$sql = "
    SELECT id, title, image_url, description, tier 
    FROM items 
    WHERE user_id = ? AND category = ? 
    ORDER BY FIELD(tier, 'S','A','B','C','D','E','F','unranked'), title
";

$stmt = $db->prepare($sql);
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $db->error);
}
$stmt->bind_param("is", $viewingUserId, $cat);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    if ($row['tier'] === null || $row['tier'] === 'unranked') {
        $unranked_items[] = $row;
    } elseif (in_array($row['tier'], $ranks)) {
        $items_by_rank[$row['tier']][] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Тир-лист — <?= htmlspecialchars($allowed_cats[$cat]) ?><?= $isFriendView ? ' пользователя ' . htmlspecialchars($viewingUsername) : '' ?></title>
    <link href="css/bootstrap.css" rel="stylesheet" />
    <link href="css/custom.css" rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
</head>

<body>
    <?php include __DIR__ . '/hf/header.php'; ?>

    <main class="container">
        <h1 class="mb-4">
            Тир-лист — <?= htmlspecialchars($allowed_cats[$cat]) ?>
            <?php if ($isFriendView): ?>
                пользователя <strong><?= htmlspecialchars($viewingUsername) ?></strong>
            <?php endif; ?>
        </h1>
        <div class="mb-3">
            <?php if ($isFriendView): ?>
                <a href="index.php?friend_id=<?= $viewingUserId ?>" class="btn btn-secondary mb-3">
                    ← Назад к профилю <?= htmlspecialchars($viewingUsername) ?>
                </a>
            <?php else: ?>
                <a href="index.php" class="btn btn-secondary mb-3">
                    ← Назад к выбору категории
                </a>
            <?php endif; ?>
        </div>

        <?php if (!$isFriendView): ?>
            <div class="mb-3">
                <a href="add_item.php?cat=<?= urlencode($cat) ?>" class="btn btn-primary">
                    Добавить <?= htmlspecialchars(mb_strtolower($allowed_cats[$cat], 'UTF-8')) ?>
                </a>
            </div>

            <!-- -------НА ПОТОМ. КНОПКА ВЫХОДА ИЗ ПРОФИЛЯ ДРУГА НА СТРАНИЦЕ ТИР-ЛИСТА--------- -->
        <?php else: ?>
            <!-- <div class="mb-3">
                <a href="index.php" class="btn btn-secondary">Вернуться к своим тир-листам</a>
            </div> -->
        <?php endif; ?> 
        
            <!-- -------КОНЕЦ. КНОПКА ВЫХОДА ИЗ ПРОФИЛЯ ДРУГА НА СТРАНИЦЕ ТИР-ЛИСТА--------- -->

        <table class="table table-dark table-striped">
            <tbody>
                <?php foreach ($ranks as $rank): ?>
                    <tr>
                        <td class="tier-rank align-middle rank-<?= $rank ?>">
                            <span class="rank-letter-<?= $rank ?>"><?= htmlspecialchars($rank) ?></span>
                        </td>
                        <td class="tier-items" data-tier="<?= $rank ?>">
                            <?php if (!empty($items_by_rank[$rank])): ?>
                                <?php foreach ($items_by_rank[$rank] as $item): ?>
                                    <div
                                        class="item-card"
                                        data-item-id="<?= intval($item['id']) ?>"
                                        draggable="true"
                                        onclick="window.location.href='item.php?item_id=<?= intval($item['id']) ?><?= $isFriendView ? '&friend_id=' . $viewingUserId : '' ?>'">
                                        <img src="<?= htmlspecialchars($item['image_url'] ?: 'images/default.jpg') ?>" alt="<?= htmlspecialchars($item['title']) ?>" />
                                        <div><?= htmlspecialchars($item['title']) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3 class="mt-5">Неразмещённые элементы (Unranked)</h3>
        <div id="unranked-items" data-tier="unranked">
            <?php if (empty($unranked_items)): ?>
                <p><em>Нет неразмещённых элементов</em></p>
            <?php else: ?>
                <?php foreach ($unranked_items as $item): ?>
                    <div
                        class="item-card"
                        data-item-id="<?= intval($item['id']) ?>"
                        draggable="true"
                        onclick="window.location.href='item.php?item_id=<?= intval($item['id']) ?><?= $isFriendView ? '&friend_id=' . $viewingUserId : '' ?>'">
                        <img src="<?= htmlspecialchars($item['image_url'] ?: 'images/default.jpg') ?>" alt="<?= htmlspecialchars($item['title']) ?>" />
                        <div><?= htmlspecialchars($item['title']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <script src="js/bootstrap.bundle.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            <?php if (!$isFriendView): ?>
                const draggableItems = document.querySelectorAll(".item-card");
                const dropZones = document.querySelectorAll(".tier-items, #unranked-items");

                draggableItems.forEach(item => {
                    item.addEventListener("dragstart", e => {
                        e.dataTransfer.setData("text/plain", item.dataset.itemId);
                        item.classList.add("dragging");
                    });
                    item.addEventListener("dragend", e => {
                        item.classList.remove("dragging");
                    });
                });

                dropZones.forEach(zone => {
                    zone.addEventListener("dragover", e => e.preventDefault());

                    zone.addEventListener("drop", e => {
                        e.preventDefault();
                        const itemId = e.dataTransfer.getData("text/plain");
                        const item = document.querySelector(`.item-card[data-item-id='${itemId}']`);
                        if (!item) return;

                        zone.appendChild(item);

                        const newTier = zone.dataset.tier;

                        fetch("scr/update_tier.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: `item_id=${encodeURIComponent(itemId)}&tier=${encodeURIComponent(newTier)}`
                        }).then(res => {
                            if (!res.ok) {
                                console.error("Ошибка обновления tier");
                            }
                        });
                    });
                });
            <?php else: ?>
                // Запрет перетаскивания при просмотре чужого тир-листа
            <?php endif; ?>
        });
    </script>
</body>

</html>