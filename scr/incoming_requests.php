<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user']['id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$user_id = $_SESSION['user']['id']; 

$query = "
    SELECT f.id AS request_id, u.id AS user_id, u.username
    FROM friends f
    JOIN users u ON f.requester_id = u.id
    WHERE f.receiver_id = $user_id AND f.status = 'pending'
";

$result = $db->query($query);

if (!$result) {
    http_response_code(500);
    exit('Ошибка запроса: ' . $db->error);
}

if ($result->num_rows === 0) {
    echo '<div class="text-muted">Нет входящих заявок</div>';
    exit;
}

while ($row = $result->fetch_assoc()) {
    $username = htmlspecialchars($row['username']);
    $request_id = intval($row['request_id']);
    echo "
        <div class='incoming-request d-flex justify-content-between align-items-center mb-2' data-request-id='$request_id'>
            <span>$username</span>
            <div>
                <button class='btn btn-sm btn-success me-1 accept-request' data-request-id='$request_id'>Принять</button>
                <button class='btn btn-sm btn-danger decline-request' data-request-id='$request_id'>Отклонить</button>
            </div>
        </div>
    ";
}
