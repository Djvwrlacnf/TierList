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
            <a class="navbar-brand" href="/index.php">Tier List</a>

            <!-- Кнопка-гамбургер, видна только на мобильных -->
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Десктопное меню (слева и справа), скрываем на маленьких экранах -->
            <div class="collapse navbar-collapse d-none d-lg-flex justify-content-end">
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
                            <a class="nav-link dropdown-toggle" href="#" id="usersDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">

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
                                    <div class="tab-pane fade show active" id="searchTab" role="tabpanel" aria-labelledby="search-tab">
                                        <input type="text" id="userSearchInput" class="form-control mb-3" placeholder="Поиск по имени...">
                                        <div id="userSearchResults" class="mb-3"></div>
                                        <hr>
                                        <h6 class="dropdown-header">Входящие заявки</h6>
                                        <div id="incomingRequests"></div>
                                    </div>

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

    <!-- Offcanvas для мобильного меню -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel" style="background-color: #121212;">

        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="mobileMenuLabel">Меню</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Закрыть"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="navbar-nav">
                <?php if ($username): ?>
                    <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                        <li class="nav-item mb-2">
                            <a class="nav-link fw-bold text-danger" href="admin_item.php">Админка</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="user.php"><?= htmlspecialchars($username) ?></a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="logout.php">Выйти</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="login.php">Войти</a>
                    </li>
                <?php endif; ?>

                <?php if ($username): ?>
                    <li class="nav-item">
                        <div class="nav nav-tabs mb-3" id="mobileUserTabs" role="tablist">
                            <button class="nav-link active" id="mobile-search-tab" data-bs-toggle="tab" data-bs-target="#mobileSearchTab" type="button" role="tab" aria-controls="mobileSearchTab" aria-selected="true">Поиск</button>
                            <button class="nav-link" id="mobile-friends-tab" data-bs-toggle="tab" data-bs-target="#mobileFriendsTab" type="button" role="tab" aria-controls="mobileFriendsTab" aria-selected="false">Друзья</button>
                        </div>

                        <div class="tab-content" id="mobileUserTabsContent">
                            <div class="tab-pane fade show active" id="mobileSearchTab" role="tabpanel" aria-labelledby="mobile-search-tab">
                                <input type="text" id="mobileUserSearchInput" class="form-control mb-3" placeholder="Поиск по имени...">
                                <div id="mobileUserSearchResults" class="mb-3"></div>
                                <hr>
                                <h6>Входящие заявки</h6>
                                <div id="mobileIncomingRequests"></div>
                            </div>
                            <div class="tab-pane fade" id="mobileFriendsTab" role="tabpanel" aria-labelledby="mobile-friends-tab">
                                <div id="mobileFriendsList">Загрузка...</div>
                            </div>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

</header>

<script>
    function loadIncomingRequests() {
        const incomingBox = document.getElementById('incomingRequests');
        if (!incomingBox) return;
        fetch('scr/incoming_requests.php')
            .then(res => res.text())
            .then(html => incomingBox.innerHTML = html);
    }

    function loadMobileIncomingRequests() {
        const mobileIncomingBox = document.getElementById('mobileIncomingRequests');
        if (!mobileIncomingBox) return;
        fetch('scr/incoming_requests.php')
            .then(res => res.text())
            .then(html => mobileIncomingBox.innerHTML = html);
    }

    function sendFriendRequest(userId, button, event) {
        event.stopPropagation(); // предотвратить закрытие dropdown

        button.disabled = true;
        button.textContent = '...';

        fetch('scr/send_friend_request.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `user_id=${encodeURIComponent(userId)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                button.classList.remove('btn-primary');
                button.classList.add('btn-secondary');
                button.textContent = 'Запрос отправлен';
            } else {
                button.disabled = false;
                button.textContent = '+';
                alert(data.error || 'Произошла ошибка');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            button.disabled = false;
            button.textContent = '+';
            alert('Не удалось отправить запрос');
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Поиск пользователей — десктоп
        const input = document.getElementById('userSearchInput');
        const resultBox = document.getElementById('userSearchResults');
        if (input) {
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
        }

        // Поиск пользователей — мобильный
        const mobileInput = document.getElementById('mobileUserSearchInput');
        const mobileResultBox = document.getElementById('mobileUserSearchResults');
        if (mobileInput) {
            mobileInput.addEventListener('input', () => {
                const query = mobileInput.value.trim();
                if (!query) {
                    mobileResultBox.innerHTML = '';
                    return;
                }
                fetch(`scr/search_users.php?q=${encodeURIComponent(query)}`)
                    .then(res => res.text())
                    .then(html => mobileResultBox.innerHTML = html);
            });
        }

        // Открытие dropdown — загрузка заявок
        const dropdown = document.getElementById('usersDropdown');
        if (dropdown) {
            dropdown.addEventListener('click', () => {
                loadIncomingRequests();
            });
        }

        // Предотвращение закрытия dropdown при клике на кнопки
        const dropdownMenu = document.querySelector('#usersDropdown + .dropdown-menu');
        if (dropdownMenu) {
            let lastClicked = null;
            document.querySelector('#usersDropdown').parentElement.addEventListener('click', (e) => {
                lastClicked = e.target;
            });

            dropdownMenu.addEventListener('hide.bs.dropdown', (event) => {
                if (lastClicked &&
                    (lastClicked.classList.contains('accept-request') || lastClicked.classList.contains('decline-request'))) {
                    event.preventDefault(); // не закрываем
                }
            });
        }

        // Переключение вкладок — десктоп
        document.querySelectorAll('#userTabs button[data-bs-toggle="tab"]').forEach(tabBtn => {
            tabBtn.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                const tab = bootstrap.Tab.getOrCreateInstance(tabBtn);
                tab.show();

                if (tabBtn.id === 'friends-tab') {
                    fetch('scr/friends_list.php')
                        .then(res => res.text())
                        .then(html => document.getElementById('friendsList').innerHTML = html);
                }
            });
        });

        // Переключение вкладок — мобильный
        document.querySelectorAll('#mobileUserTabs button[data-bs-toggle="tab"]').forEach(tabBtn => {
            tabBtn.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                const tab = bootstrap.Tab.getOrCreateInstance(tabBtn);
                tab.show();

                if (tabBtn.id === 'mobile-friends-tab') {
                    fetch('scr/friends_list.php')
                        .then(res => res.text())
                        .then(html => document.getElementById('mobileFriendsList').innerHTML = html);
                }
            });
        });

        // Мобильное меню — загрузка входящих при открытии
        const offcanvasEl = document.getElementById('mobileMenu');
        if (offcanvasEl) {
            offcanvasEl.addEventListener('show.bs.offcanvas', () => {
                loadMobileIncomingRequests();
            });
        }

        // Обработка принятия/отклонения заявок (десктоп и моб.)
        document.addEventListener('click', e => {
            if (e.target.classList.contains('accept-request') || e.target.classList.contains('decline-request')) {
                e.stopPropagation();

                const btn = e.target;
                const requestId = btn.dataset.requestId;
                const action = btn.classList.contains('accept-request') ? 'accept' : 'decline';

                fetch('scr/respond_request.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `request_id=${encodeURIComponent(requestId)}&action=${encodeURIComponent(action)}`
                })
                .then(res => {
                    if (res.ok) {
                        // Удалить только эту заявку
                        const wrapper = btn.closest('.incoming-request');
                        if (wrapper) wrapper.remove();
                    }
                })
                .catch(err => console.error('Ошибка при ответе на заявку:', err));
            }
        }); 
    });
</script>
