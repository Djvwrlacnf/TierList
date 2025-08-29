<?php
session_start();
require_once 'db.php';

header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION['user']['id'])) {
    http_response_code(401);
    echo 'Вы не авторизованы.';
    exit;
}

$userId = (int)$_SESSION['user']['id'];

$sql = "
    SELECT u.id, u.username
    FROM friends f
    JOIN users u ON (
        (f.requester_id = ? AND f.receiver_id = u.id)
        OR (f.receiver_id = ? AND f.requester_id = u.id)
    )
    WHERE f.status = 'accepted'
";

$stmt = $db->prepare($sql);
if (!$stmt) {
    die('Ошибка подготовки запроса: ' . $db->error);
}

$stmt->bind_param('ii', $userId, $userId);
$stmt->execute();

$result = $stmt->get_result();
$friends = $result->fetch_all(MYSQLI_ASSOC);

if (empty($friends)) {
    echo '<div class="text-muted">У вас пока нет друзей.</div>';
    exit;
}

foreach ($friends as $friend) {
    $username = htmlspecialchars($friend['username']);
    $friendId = (int)$friend['id'];

    echo <<<HTML
<div class="d-flex align-items-center justify-content-between mb-2 border-bottom pb-2">
    <div>
        <strong>$username</strong>
    </div>
    <a href="/index.php?friend_id=$friendId" class="btn btn-outline-primary btn-sm">Открыть</a>
</div>
HTML;
}
