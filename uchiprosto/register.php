<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - УчиПросто</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="register-page">
    <!-- Хедер -->
<header class="header">
    <div class="header-container">
        <a href="index.php" class="logo">УчиПросто</a>
        <nav class="nav-links">
            <a href="index.php">Главная</a>
            <a href="tutors.php">Анкеты</a>
            <a href="help.php">Помощь</a>
        </nav>
        <div class="auth-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['user_type'] == 'admin'): ?>
                    <a href="dashboard.php">Админ-панель</a>
                    <a href="admin_support.php">Поддержка</a>
                <?php else: ?>
                    <a href="dashboard.php">Личный кабинет</a>
                <?php endif; ?>
                <a href="logout.php">Выйти</a>
            <?php else: ?>
                <a href="login.php">Вход/Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</header>

    <!-- Основной контент -->
    <main class="main-content">
        <form class="register-form" action="auth.php" method="POST">
            <input type="hidden" name="action" value="register">
            <h1 class="register-title">Регистрация</h1>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <!-- Основная информация -->
            <div class="form-group">
                <label for="last_name">Фамилия *</label>
                <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Введите фамилию" required>
            </div>

            <div class="form-group">
                <label for="first_name">Имя *</label>
                <input type="text" id="first_name" name="first_name" class="form-control" placeholder="Введите имя" required>
            </div>

            <div class="form-group">
                <label for="patronymic">Отчество</label>
                <input type="text" id="patronymic" name="patronymic" class="form-control" placeholder="Введите отчество">
            </div>

            <div class="form-group">
                <label for="phone">Телефон</label>
                <input type="tel" id="phone" name="phone" class="form-control" placeholder="+7 (XXX) XXX-XX-XX">
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="example@mail.ru" required>
            </div>

            <div class="form-group">
                <label for="password">Пароль *</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Не менее 6 символов" required minlength="6">
            </div>

            <!-- Тип пользователя -->
            <div class="form-group">
                <label>Я хочу зарегистрироваться как</label>
                <div class="user-type-selector">
                    <div class="user-type-option">
                        <input type="radio" id="student" name="user_type" value="student" checked class="user-type-radio">
                        <label for="student" class="user-type-label">
                            <span class="user-type-title">Ученик</span>
                            <span class="user-type-description">Ищу репетитора для занятий</span>
                        </label>
                    </div>
                    <div class="user-type-option">
                        <input type="radio" id="tutor" name="user_type" value="tutor" class="user-type-radio">
                        <label for="tutor" class="user-type-label">
                            <span class="user-type-title">Репетитор</span>
                            <span class="user-type-description">Преподаю и ищу учеников</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Дополнительные поля для ученика -->
            <div id="student-fields" class="additional-fields">
                <h4 class="section-subtitle">Информация об ученике</h4>
                <div class="form-group">
                    <label for="grade">Класс/Курс</label>
                    <input type="text" id="grade" name="grade" class="form-control" placeholder="Например, 5 класс или 1 курс">
                </div>
                <div class="form-group">
                    <label for="parent_phone">Телефон родителя</label>
                    <input type="tel" id="parent_phone" name="parent_phone" class="form-control" placeholder="+7 (XXX) XXX-XX-XX">
                </div>
                <div class="form-group">
                    <label for="study_goals">Цели обучения</label>
                    <textarea id="study_goals" name="study_goals" class="form-control" placeholder="Опишите, для чего вам нужен репетитор..." rows="3"></textarea>
                </div>
            </div>

            <!-- Дополнительные поля для репетитора -->
            <div id="tutor-fields" class="additional-fields" style="display: none;">
                <h4 class="section-subtitle">Информация о репетиторе</h4>
                <div class="form-group">
                    <label for="subjects">Предметы *</label>
                    <input type="text" id="subjects" name="subjects" class="form-control" placeholder="Математика, Физика, Химия...">
                </div>
                <div class="form-group">
                    <label for="experience">Стаж (лет) *</label>
                    <input type="number" id="experience" name="experience" class="form-control" placeholder="Опыт преподавания" min="0">
                </div>
                <div class="form-group">
                    <label for="price_per_hour">Цена за час (руб.) *</label>
                    <input type="number" id="price_per_hour" name="price_per_hour" class="form-control" placeholder="500" min="1">
                </div>
                <div class="form-group">
                    <label for="about_me">О себе</label>
                    <textarea id="about_me" name="about_me" class="form-control" placeholder="Расскажите о своем опыте и подходе к обучению..." rows="3"></textarea>
                </div>
            </div>

            <button type="submit" class="btn-register">Создать аккаунт</button>

            <div class="divider"></div>

            <div class="login-link">
                Уже есть аккаунт? <a href="login.php">Войдите здесь</a>
            </div>
        </form>
    </main>

    <!-- Футер -->
    <footer class="footer">
        <p>© 2025 УчиПросто. Все права защищены.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userTypeRadios = document.querySelectorAll('input[name="user_type"]');
            const studentFields = document.getElementById('student-fields');
            const tutorFields = document.getElementById('tutor-fields');
            
            function toggleFields() {
                const isStudent = document.getElementById('student').checked;
                
                if (isStudent) {
                    studentFields.style.display = 'block';
                    tutorFields.style.display = 'none';
                    // Делаем поля репетитора необязательными
                    document.getElementById('subjects').required = false;
                    document.getElementById('experience').required = false;
                    document.getElementById('price_per_hour').required = false;
                } else {
                    studentFields.style.display = 'none';
                    tutorFields.style.display = 'block';
                    // Делаем поля репетитора обязательными
                    document.getElementById('subjects').required = true;
                    document.getElementById('experience').required = true;
                    document.getElementById('price_per_hour').required = true;
                }
            }
            
            userTypeRadios.forEach(radio => {
                radio.addEventListener('change', toggleFields);
            });
            
            // Инициализация при загрузке
            toggleFields();
        });
    </script>
</body>
</html>