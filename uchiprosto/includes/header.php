<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>УчиПросто - <?php echo $page_title ?? 'Главная'; ?></title>
    <link rel="stylesheet" href="config/css/style.css">
</head>
<body>
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
                    <a href="dashboard.php">Личный кабинет</a>
                <?php else: ?>
                    <a href="login.php">Вход/Регистрация</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Основной контент -->
    <main class="main-content">