<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Восстановление пароля</title>
    <link href="css/bootstrap.css" rel="stylesheet" />
    <link href="css/custom.css" rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
</head>
<body>
<?php include __DIR__ . '/hf/header.php'; ?>

<main class="container my-4">
    <h1 class="h3 mb-3">Восстановление пароля</h1>

    <div id="step-email">
        <form id="emailForm" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="email" class="form-label">Введите ваш email</label>
                <input type="email" class="form-control" id="email" required>
                <div class="invalid-feedback">Введите корректный email.</div>
            </div>
            <button type="submit" class="btn btn-primary">Далее</button>
        </form>
    </div>

    <div id="step-question" style="display:none;">
        <form id="questionForm" class="needs-validation" novalidate>
            <div class="mb-3">
                <label class="form-label">Секретный вопрос:</label>
                <div id="questionText" class="form-control" readonly></div>
            </div>
            <div class="mb-3">
                <label for="answer" class="form-label">Ответ на вопрос</label>
                <input type="text" class="form-control" id="answer" required>
                <div class="invalid-feedback">Введите ответ на секретный вопрос.</div>
            </div>
            <button type="submit" class="btn btn-primary">Проверить ответ</button>
            <p class="mt-2" id="remainingTokens"></p>
        </form>
    </div>

    <div id="step-reset" style="display:none;">
        <form id="resetForm" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="newPassword" class="form-label">Новый пароль</label>
                <input type="password" class="form-control" id="newPassword" required>
                <div class="invalid-feedback">Введите новый пароль.</div>
            </div>
            <button type="submit" class="btn btn-primary">Сменить пароль</button>
        </form>
    </div>
</main>

<script src="js/bootstrap.bundle.js"></script>
<script>
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

const emailForm = document.getElementById('emailForm');
const questionForm = document.getElementById('questionForm');
const resetForm = document.getElementById('resetForm');
let userId = null;

emailForm.addEventListener('submit', async e => {
    e.preventDefault();
    const email = document.getElementById('email').value;

    const res = await fetch('scr/remember.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({step: 'get_question', email})
    });
    const data = await res.json();

    if(data.success){
        userId = data.user_id;
        document.getElementById('questionText').innerText = data.question;
        document.getElementById('remainingTokens').innerText = `Осталось попыток: ${data.token}`;
        document.getElementById('step-email').style.display='none';
        document.getElementById('step-question').style.display='block';
    } else {
        alert(data.message);
    }
});

questionForm.addEventListener('submit', async e => {
    e.preventDefault();
    const answer = document.getElementById('answer').value;

    const res = await fetch('scr/remember.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({step:'check_answer', user_id:userId, answer})
    });
    const data = await res.json();

    if(data.success){
        document.getElementById('step-question').style.display='none';
        document.getElementById('step-reset').style.display='block';
    } else {
        alert(data.message);
        document.getElementById('remainingTokens').innerText = `Осталось попыток: ${data.token}`;
        if(data.token <= 0) questionForm.querySelector('button').disabled=true;
    }
});

resetForm.addEventListener('submit', async e => {
    e.preventDefault();
    const newPassword = document.getElementById('newPassword').value;

    const res = await fetch('scr/remember.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({step:'reset_password', user_id:userId, new_password:newPassword})
    });
    const data = await res.json();

    if(data.success){
        alert('Пароль успешно изменен!');
        window.location.href='login.php';
    } else {
        alert(data.message);
    }
});
</script>
</body>
</html>
