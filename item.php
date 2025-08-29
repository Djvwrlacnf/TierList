<?php
session_start();
require_once __DIR__ . '/scr/db.php';

$item_id = $_GET['item_id'] ?? null;
if (!$item_id || !is_numeric($item_id)) {
    http_response_code(400);
    echo "Некорректный ID элемента";
    exit;
}

// Получаем элемент из БД
$stmt = $db->prepare("SELECT id, title, image_url, description, user_id, category FROM items WHERE id = ?");

$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    http_response_code(404);
    echo "Элемент не найден";
    exit;
}

$user_id = $_SESSION['user']['id'] ?? null;
$username = $_SESSION['user']['username'] ?? null;
$is_owner = $user_id === intval($item['user_id']);
$is_admin = ($_SESSION['user']['role'] ?? '') === 'admin';

// Проверим, является ли пользователь другом владельца (если не владелец)
$is_friend = false;
if ($user_id && !$is_owner) {
    $stmt = $db->prepare("
        SELECT status FROM friends 
        WHERE ((requester_id = ? AND receiver_id = ?) OR (requester_id = ? AND receiver_id = ?))
          AND status = 'accepted'
    ");
    $stmt->bind_param("iiii", $user_id, $item['user_id'], $item['user_id'], $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $is_friend = $result->num_rows > 0;
}

// ВАЖНО: добавить $is_admin в разрешения просмотра
$can_view = $is_owner || $is_friend || $is_admin;

$category = $_GET['category'] ?? '';
$category = $_GET['category'] ?? '';

$friend_id = $_GET['friend_id'] ?? null;

// Получаем пользовательские скриншоты для этого элемента
$stmt = $db->prepare("SELECT * FROM images WHERE item_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$images_result = $stmt->get_result();
$user_images = [];
while ($row = $images_result->fetch_assoc()) {
    $user_images[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Элемент — <?= htmlspecialchars($item['title']) ?></title>
    <link href="css/bootstrap.css" rel="stylesheet" />
    <link href="css/custom.css" rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
</head>

<body>
    <?php include __DIR__ . '/hf/header.php'; ?>

    <main class="container">
        <a href="tierlist.php?cat=<?= urlencode($item['category']) ?><?= $friend_id ? '&friend_id=' . intval($friend_id) : '' ?>" class="btn btn-secondary mb-3">
            ← Назад к списку
        </a>

        <div class="row mb-4 align-items-start gx-0">
            <!-- Левая колонка — изображение -->
            <div class="col-md-3 px-0 text-start">
                <img src="<?= htmlspecialchars($item['image_url'] ?: 'images/default.jpg') ?>"
                    alt="<?= htmlspecialchars($item['title']) ?>"
                    style="max-width: 100%; max-height: 400px; border-radius: 8px; border: 2px solid #ccc;">
            </div>

            <!-- Правая колонка — заголовок и описание -->
            <div class="col-md-9">
                <h1 class="mb-4"><?= htmlspecialchars($item['title']) ?></h1>

                <?php if ($is_owner): ?>
                    <form action="scr/update_item.php" method="post" class="mb-4">
                        <input type="hidden" name="item_id" value="<?= intval($item['id']) ?>" />
                        <div class="mb-3">
                            <label for="title" class="form-label">Название</label>
                            <input type="text" class="form-control" name="title" id="title" required
                                value="<?= htmlspecialchars($item['title']) ?>" />
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Мнение</label>
                            <textarea class="form-control" name="description" id="description"
                                rows="6"><?= htmlspecialchars($item['description']) ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </form>
                <?php elseif ($can_view): ?>
                    <h4>Мнение</h4>
                    <p><?= nl2br(htmlspecialchars($item['description'] ?: 'Описание отсутствует')) ?></p>
                <?php else: ?>
                    <div class="alert alert-warning">У вас нет доступа к просмотру этого элемента.</div>
                    <?php exit; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Блок пользовательских скриншотов -->
        <?php if ($can_view): ?>
            <h4 class="mt-5 mb-3">Скриншоты</h4>

            <?php if ($is_owner): ?>
                <form action="scr/add_image.php" method="post" class="mb-3 d-flex gap-2">
                    <input type="hidden" name="item_id" value="<?= intval($item['id']) ?>">
                    <input type="url" name="url" class="form-control" placeholder="Вставьте ссылку на изображение" required>
                    <button type="submit" class="btn btn-primary">Добавить</button>
                </form>
            <?php endif; ?>

            <?php if ($user_images): ?>
    <div class="slider-container">
        <?php foreach ($user_images as $img): ?>
            <div class="slider-item">
                <img src="<?= htmlspecialchars($img['url']) ?>" alt="Скриншот">
                <?php if ($is_owner): ?>
                    <form action="scr/delete_image.php" method="post" onsubmit="return confirm('Удалить это изображение?');">
                        <input type="hidden" name="image_id" value="<?= intval($img['id']) ?>">
                        <button type="submit" class="delete-image-btn" title="Удалить">X</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>

                <p>Пользовательских скриншотов пока нет.</p>
            <?php endif; ?>
        <?php endif; ?>


        <?php
        // Комментарии
        $stmt = $db->prepare("
        SELECT c.*, u.username
        FROM comments c
        JOIN users u ON u.id = c.user_id
        WHERE c.item_id = ?
        ORDER BY c.datetime ASC
    ");

        if (!$stmt) {
            die("Ошибка подготовки запроса: " . $db->error);
        }

        $stmt->bind_param("i", $item['id']);
        $stmt->execute();
        $comments_result = $stmt->get_result();
        $all_comments = [];
        while ($row = $comments_result->fetch_assoc()) {
            $all_comments[] = $row;
        }

        // Группировка
        $grouped = [];
        foreach ($all_comments as $comment) {
            $grouped[$comment['answer_id']][] = $comment;
        }

        function render_comments($comments, $grouped, $level = 0, $user_id, $is_admin)
        {
            foreach ($comments as $comment) {
                $id = $comment['id'];
                $author = htmlspecialchars($comment['username']);
                $content = nl2br(htmlspecialchars($comment['content']));
                $date = date('d.m.Y H:i', strtotime($comment['datetime']));
                $can_edit = $user_id === (int)$comment['user_id'];
                $can_delete = $user_id === (int)$comment['user_id'] || $is_admin;
                $indent = $level * 30;

                echo "<div id='comment-{$id}' data-parent-id='{$comment['answer_id']}' style='margin-left: {$indent}px;' class='border-start ps-3 mb-2'>";
                echo "<strong>{$author}</strong> <small class='text-muted'>[$date]</small><br>";
                echo "<div id='comment-content-$id'>{$content}</div>";

                echo "<div class='mt-1'>";
                echo "<button class='btn btn-sm btn-outline-primary me-2' onclick='replyTo($id)'>Ответить</button> ";
                if ($can_edit) {
                    echo "<button class='btn btn-sm btn-outline-secondary me-2' onclick='editComment($id)'>Редактировать</button> ";
                }
                if ($can_delete) {
                    echo "<button class='btn btn-sm btn-outline-danger' onclick='deleteComment($id)'>Удалить</button>";
                }
                echo "</div>";

                if (!empty($grouped[$id])) {
                    echo "<div class='mt-2'>
                        <button class='btn btn-sm toggle-replies-btn text-danger' data-id='$id' onclick='toggleReplies($id, this)'>Показать ответы</button>
                        <div id='replies-$id' style='display: none;'>";
                    render_comments($grouped[$id], $grouped, $level + 1, $user_id, $is_admin);
                    echo "</div></div>";
                }

                echo "</div>";
            }
        }
        ?>

        <section class="mt-5">
            <h4>Комментарии</h4>

            <?php if ($can_view && $user_id): ?>
                <form method="post" action="scr/post_comment.php" class="mb-4" id="comment-form">
                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                    <input type="hidden" name="answer_id" id="answer_id" value="">

                    <div id="reply-info" class="mb-2" style="display:none;">
                        <div><span id="reply-text"></span></div>
                    </div>

                    <div class="mb-3">
                        <textarea class="form-control" name="content" id="content" rows="4" placeholder="Оставьте комментарий..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Отправить</button>
                    <button type="button" class="btn btn-secondary" onclick="cancelReply()">Отменить ответ</button>
                </form>

            <?php elseif ($can_view): ?>
                <div class="text-muted">Только автор и его друзья могут оставлять комментарии.</div>
            <?php endif; ?>

            <div id="comments">
                <?php
                $topLevel = $grouped[null] ?? $grouped[0] ?? [];
                if (!empty($topLevel)) {
                    render_comments($topLevel, $grouped, 0, $user_id, $is_admin);
                } else {
                    echo "<div class='text-muted'>Комментариев пока нет.</div>";
                }
                ?>
            </div>
        </section>
    </main>
</body>

<script src="js/bootstrap.bundle.js"></script>
<script>
    function replyTo(id) {
        const commentDiv = document.getElementById('comment-content-' + id);
        let username = 'пользователю';

        if (commentDiv) {
            const parent = commentDiv.parentNode;
            const strong = parent.querySelector('strong');
            if (strong) {
                username = strong.innerText.trim();
            }
        }

        document.getElementById('answer_id').value = id;
        document.getElementById('content').focus();

        const replyText = document.getElementById('reply-text');
        replyText.textContent = 'Ответ пользователю: ' + username;
        document.getElementById('reply-info').style.display = 'block';
    }

    function cancelReply() {
        document.getElementById('answer_id').value = '';
        document.getElementById('reply-info').style.display = 'none';
    }

    function toggleReplies(id, btn) {
        const block = document.getElementById('replies-' + id);
        if (block) {
            const isVisible = block.style.display !== 'none';
            block.style.display = isVisible ? 'none' : 'block';
            btn.textContent = isVisible ? 'Показать ответы' : 'Скрыть ответы';
        }
    }


    function editComment(id) {
        const contentDiv = document.getElementById('comment-content-' + id);
        const originalText = contentDiv.innerText.trim();

        // Если уже в режиме редактирования — не делать ничего
        if (document.getElementById('edit-input-' + id)) return;

        // Скрываем оригинальный текст
        contentDiv.style.display = 'none';

        // Создаем input + кнопки "Сохранить" и "Отмена"
        const input = document.createElement('textarea');
        input.className = 'form-control mb-2';
        input.value = originalText;
        input.id = 'edit-input-' + id;
        input.rows = 3;

        const saveBtn = document.createElement('button');
        saveBtn.className = 'btn btn-sm btn-primary me-2';
        saveBtn.textContent = 'Сохранить';
        saveBtn.onclick = () => {
            const newText = input.value.trim();
            if (newText === '') return;

            fetch('scr/edit_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + id + '&content=' + encodeURIComponent(newText)
            }).then(() => location.reload());
        };

        const cancelBtn = document.createElement('button');
        cancelBtn.className = 'btn btn-sm btn-secondary';
        cancelBtn.textContent = 'Отмена';
        cancelBtn.onclick = () => {
            contentDiv.style.display = '';
            input.remove();
            saveBtn.remove();
            cancelBtn.remove();
        };

        // Вставляем всё после оригинального блока
        contentDiv.parentNode.insertBefore(input, contentDiv.nextSibling);
        contentDiv.parentNode.insertBefore(saveBtn, input.nextSibling);
        contentDiv.parentNode.insertBefore(cancelBtn, saveBtn.nextSibling);
    }


    function deleteComment(id) {
        if (confirm('Удалить комментарий?')) {
            fetch('scr/delete_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + id
            }).then(() => location.reload());
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        const params = new URLSearchParams(window.location.search);
        const highlightId = params.get("highlight_comment");

        if (highlightId) {
            expandParentsUntil(highlightId); // 👈 раскрываем родителей

            const el = document.getElementById("comment-" + highlightId);
            if (el) {
                el.scrollIntoView({
                    behavior: "smooth",
                    block: "center"
                });
                el.classList.add("highlight-comment");
                setTimeout(() => {
                    el.classList.remove("highlight-comment");
                }, 3000);
            }
        }
    });


    function expandParentsUntil(commentId) {
        const target = document.getElementById("comment-" + commentId);
        if (!target) return;

        let current = target;
        while (current) {
            const parentId = current.dataset.parentId;
            if (!parentId || parentId === "0" || parentId === "null") break;

            const repliesBlock = document.getElementById("replies-" + parentId);
            const toggleBtn = document.querySelector(`.toggle-replies-btn[data-id='${parentId}']`);
            if (repliesBlock && toggleBtn && repliesBlock.style.display === "none") {
                repliesBlock.style.display = "block";
                toggleBtn.textContent = "Скрыть ответы";
            }

            current = document.getElementById("comment-" + parentId);
        }
    }
</script>


</html>