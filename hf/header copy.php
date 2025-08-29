<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username = $username ?? ($_SESSION['user']['username'] ?? null);
$user_id = $_SESSION['user']['id'] ?? null;
?>

<link rel="stylesheet" href="../css/style.css">
<header>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="/">Tier List</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <?php if ($username): ?>
                    <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" href="admin_item.php" style="color: #b71c1c;">Админка</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="user.php"><?= htmlspecialchars($username) ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Выйти</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Войти</a>
                    </li>
                <?php endif; ?>

                    <?php if ($username): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="usersDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Пользователи
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end p-3" style="width: 320px; max-height: 500px; overflow-y: auto;" aria-labelledby="usersDropdown">
                                <!-- Вкладки -->
                                <div class="nav nav-tabs mb-3" id="userTabs" role="tablist">
                                    <button class="nav-link active" id="search-tab" data-bs-toggle="tab" data-bs-target="#searchTab" type="button" role="tab" aria-controls="searchTab" aria-selected="true">Поиск</button>
                                    <button class="nav-link" id="friends-tab" data-bs-toggle="tab" data-bs-target="#friendsTab" type="button" role="tab" aria-controls="friendsTab" aria-selected="false">Друзья</button>
                                </div>

                                <!-- Контент вкладок -->
                                <div class="tab-content" id="userTabsContent">
                                    <!-- Вкладка Поиск -->
                                    <div class="tab-pane fade show active" id="searchTab" role="tabpanel" aria-labelledby="search-tab">
                                        <input type="text" id="userSearchInput" class="form-control mb-3" placeholder="Поиск по имени...">
                                        <div id="userSearchResults" class="mb-3"></div>
                                        <hr>
                                        <h6 class="dropdown-header">Входящие заявки</h6>
                                        <div id="incomingRequests"></div>
                                    </div>

                                    <!-- Вкладка Друзья -->
                                    <div class="tab-pane fade" id="friendsTab" role="tabpanel" aria-labelledby="friends-tab">
                                        <div id="friendsList">Загрузка...</div>
                                    </div>
                                </div>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('userSearchInput');
        const resultBox = document.getElementById('userSearchResults');
        const incomingBox = document.getElementById('incomingRequests');

        input.addEventListener('input', () => {
            const query = input.value.trim();
            if (!query) {
                resultBox.innerHTML = '';
                return;
            }

            fetch(`scr/search_users.php?q=${encodeURIComponent(query)}`)
                .then(res => res.text())
                .then(html => resultBox.innerHTML = html);
        });

        // Загрузка входящих заявок
        function loadIncomingRequests() {
            fetch(`scr/incoming_requests.php`)
                .then(res => res.text())
                .then(html => incomingBox.innerHTML = html);
        }

        // Обработка принятия/отклонения
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('accept-request') || e.target.classList.contains('decline-request')) {
                const btn = e.target;
                const requestId = btn.dataset.requestId;
                const action = btn.classList.contains('accept-request') ? 'accept' : 'decline';

                fetch('scr/respond_request.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `request_id=${encodeURIComponent(requestId)}&action=${encodeURIComponent(action)}`
                }).then(res => {
                    if (res.ok) loadIncomingRequests();
                });
            }
        });

        // Загрузить заявки сразу при открытии
        const dropdown = document.getElementById('usersDropdown');
        dropdown.addEventListener('click', () => loadIncomingRequests());


        // Запрет закрытия дропдауна при переключении вкладок
        document.querySelectorAll('#userTabs button[data-bs-toggle="tab"]').forEach(tabBtn => {
            tabBtn.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                const tabTrigger = bootstrap.Tab.getOrCreateInstance(tabBtn);
                tabTrigger.show();

                if (tabBtn.id === 'friends-tab') {
                    fetch('scr/friends_list.php')
                        .then(res => res.text())
                        .then(html => {
                            document.getElementById('friendsList').innerHTML = html;
                        });
                }
            });
        });
    });

    // -----------ДЛЯ ДОБАВЛЕНИЯ В ДРУЗЬЯ-----------//
    function sendFriendRequest(userId, btn, event) {
        event.stopPropagation(); // ← это предотвращает закрытие меню

        btn.disabled = true;

        fetch('scr/send_friend_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `user_id=${encodeURIComponent(userId)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    btn.textContent = 'Отправлено';
                    btn.className = 'btn btn-sm btn-secondary';
                } else {
                    btn.disabled = false;
                    btn.textContent = '+';
                    alert(data.error || 'Ошибка отправки');
                }
            });
    }
    // -----------КОНЕЦ ДЛЯ ДОБАВЛЕНИЯ В ДРУЗЬЯ-----------//

    // -----------ДЛЯ ПРИНЯТИЯ ЗАПРОСА-----------//
    function respondToRequest(requestId, accept, event) {
        if (event) event.stopPropagation(); // чтобы не закрывалось меню

        const action = accept ? 'accept' : 'decline';

        fetch('scr/respond_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `request_id=${encodeURIComponent(requestId)}&action=${encodeURIComponent(action)}`
            })
            .then(res => res.ok ? res.text() : Promise.reject('Ошибка ответа'))
            .then(() => {
                // После ответа — перезагружаем список входящих заявок
                const reload = document.getElementById('usersDropdown');
                if (reload) reload.click(); // вручную "переоткрываем" меню
            })
            .catch(err => alert(err));
    }
    // -----------КОНЕЦ ДЛЯ ПРИНЯТИЯ ЗАПРОСА-----------//
</script>