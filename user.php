<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Редактирование профиля</title>
    <link href="css/bootstrap.css" rel="stylesheet" />
    <link href="css/custom.css" rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
</head>

<body>
    <?php
    session_start();
    require 'scr/db.php';

    if (!isset($_SESSION['user']['id'])) {
        die("Необходимо войти в систему.");
    }

    $user_id = (int)$_SESSION['user']['id'];

    // Загружаем данные о пользователе
    $sql = "SELECT username, question FROM users WHERE id = $user_id LIMIT 1";
    $result = $db->query($sql);

    if (!$result || $result->num_rows === 0) {
        die("Пользователь не найден.");
    }

    $user = $result->fetch_assoc();
    ?>

    <?php include __DIR__ . '/hf/header.php'; ?>

    <main class="container my-4">
        <div class="d-flex align-items-baseline justify-content-between mb-3">
            <h1 class="h3 mb-0">Редактирование профиля</h1>
        </div>

        <div class="row g-4">
            <!-- Имя пользователя -->
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        Изменение имени пользователя
                    </div>
                    <div class="card-body">
                        <form action="scr/update_user.php" method="post" class="needs-validation" novalidate>
                            <input type="hidden" name="action" value="update_username">
                            <div class="mb-3">
                                <label for="username" class="form-label">Имя пользователя</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="username"
                                    name="username"
                                    value="<?= htmlspecialchars($user['username']) ?>"
                                    required>
                                <div class="invalid-feedback">Введите имя пользователя.</div>
                            </div>
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Смена пароля -->
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        Смена пароля
                    </div>
                    <div class="card-body">
                        <form action="scr/update_user.php" method="post" class="needs-validation" novalidate>
                            <input type="hidden" name="action" value="update_password">
                            <div class="mb-3">
                                <label for="old_password" class="form-label">Старый пароль</label>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="old_password"
                                    name="old_password"
                                    required>
                                <div class="invalid-feedback">Укажите старый пароль.</div>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Новый пароль</label>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="new_password"
                                    name="new_password"
                                    required>
                                <div class="invalid-feedback">Укажите новый пароль.</div>
                            </div>
                            <button type="submit" class="btn btn-primary">Сменить пароль</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Секретный вопрос -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        Секретный вопрос для восстановления доступа
                    </div>
                    <div class="card-body">
                        <form action="scr/update_user.php" method="post" class="needs-validation" novalidate>
                            <input type="hidden" name="action" value="update_secret">
                            <div class="mb-3">
                                <label for="question" class="form-label">Секретный вопрос</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="question"
                                    name="question"
                                    value="<?= htmlspecialchars($user['question'] ?? '') ?>"
                                    placeholder="Этот вопрос будет использоваться для восстановления доступа к аккаунту в случае забытия пароля">
                            </div>
                            <div class="mb-3">
                                <label for="answer" class="form-label">Ответ на вопрос</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="answer"
                                    name="answer"
                                    placeholder="Ваш ответ"
                                    required>
                                <div class="form-text">
                                    Ответ будет сохранён в зашифрованном виде.
                                </div>
                                <div class="invalid-feedback">Введите ответ на секретный вопрос.</div>
                            </div>
                            <button type="submit" class="btn btn-primary">Сохранить вопрос</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="js/bootstrap.bundle.js"></script>
    <script>
        // Включаем клиентскую валидацию Bootstrap (по желанию)
        (() => {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>
