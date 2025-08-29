<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user']['id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$search = $_GET['q'] ?? '';
$search = trim($search);

if ($search === '') {
    echo '<div class="text-muted">Введите имя для поиска</div>';
    exit;
}

$current_user_id = (int)$_SESSION['user']['id'];
$search_esc = $db->real_escape_string($search);

$query = "
    SELECT u.id, u.username,
        (SELECT f.status FROM friends f WHERE
            (f.requester_id = $current_user_id AND f.receiver_id = u.id) OR
            (f.receiver_id = $current_user_id AND f.requester_id = u.id)
         LIMIT 1) AS status
    FROM users u
    WHERE u.username LIKE '$search_esc%'
      AND u.id != $current_user_id
    ORDER BY u.username
    LIMIT 20
";

$result = $db->query($query);

if (!$result) {
    http_response_code(500);
    exit('Ошибка запроса: ' . $db->error);
}

if ($result->num_rows === 0) {
    echo '<div class="text-muted">Пользователи не найдены</div>';
    exit;
}

while ($row = $result->fetch_assoc()) {
    $userId = (int)$row['id'];
    $username = htmlspecialchars($row['username']);
    $status = $row['status'];

    if ($status === null) {
        $button = "<button class='btn btn-sm btn-primary' onclick='sendFriendRequest($userId, this, event)'>+</button>";

    } elseif ($status === 'pending') {
        $button = "<button class='btn btn-sm btn-secondary' disabled>Запрос отправлен</button>";
    } elseif ($status === 'accepted') {
        $button = "<button class='btn btn-sm btn-success' disabled>Уже друзья</button>";
    } else {
        $button = '';
    }

    echo "
    <div class='d-flex justify-content-between align-items-center mb-2'>
        <span>$username</span>
        $button
    </div>
    ";
}
