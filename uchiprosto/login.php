<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - УчиПросто</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #2c3e50;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Хедер - как на главной */
        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 1rem 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }
        
        .logo {
            font-size: 2.2rem;
            font-weight: bold;
            color: #6600FF;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            text-decoration: none;
        }
        
         .nav-links {
            display: flex;
            gap: 2.5rem;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #2c3e50;
            font-weight: 600;
            padding: 0.7rem 1.2rem;
            border-radius: 25px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-links a:hover {
            background: #4a90e2;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.3);
        }
        
        .auth-links a {
            text-decoration: none;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            padding: 0.8rem 1.8rem;
            border-radius: 25px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            display: inline-block;
            text-align: center;
            min-width: 120px;
            border: none;
            cursor: pointer;
        }
        
        .auth-links a:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(74, 144, 226, 0.4);
        }

        /* Основной контент */
        .main-content {
            margin-top: 80px;
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        /* Форма входа */
        .login-form {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
        }

        .login-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fff;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .form-links {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .form-links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .form-links a:hover {
            text-decoration: underline;
        }

        .divider {
            height: 1px;
            background: #e1e5e9;
            margin: 1.5rem 0;
        }

        .register-link {
            text-align: center;
        }

        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        /* Футер - уже */
        .footer {
            background: rgba(44, 62, 80, 0.9);
            color: white;
            text-align: center;
            padding: 1.5rem 2rem;
            margin-top: auto;
            backdrop-filter: blur(10px);
        }

        /* Сообщения об ошибках */
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .alert-error {
            background: #ffe6e6;
            color: #d63031;
            border: 1px solid #ffcccc;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
                padding: 0 1rem;
            }
            
            .logo {
                font-size: 1.8rem;
                order: 1;
            }
            
            .nav-links {
                order: 3;
                gap: 1rem;
                flex-wrap: wrap;
                justify-content: center;
                max-width: 100%;
                margin: 0.5rem 0;
            }
            
            .nav-links a {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }

            .auth-links {
                order: 2;
                margin: 0.5rem 0;
            }

            .auth-links a {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
                min-width: 100px;
            }
            
            .login-form {
                padding: 2rem;
                margin: 1rem;
            }
            
            .login-title {
                font-size: 1.5rem;
            }

            .footer {
                padding: 1rem 2rem;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 0.8rem 0;
            }

            .header-container {
                gap: 0.8rem;
            }

            .nav-links {
                gap: 0.5rem;
            }

            .nav-links a {
                padding: 0.4rem 0.8rem;
                font-size: 0.85rem;
            }

            .auth-links a {
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
                min-width: 90px;
            }

            .login-form {
                padding: 1.5rem;
            }

            .footer {
                padding: 0.8rem 1rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 360px) {
            .nav-links {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .nav-links a {
                width: 100%;
                text-align: center;
            }
        }
    </style>
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
        <form class="login-form" action="auth.php" method="POST">
            <input type="hidden" name="action" value="login">
            <h1 class="login-title">Войти</h1>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="email">Логин (Email)</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="oksan@gmail.ru" required>
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Введите ваш пароль" required>
            </div>

            <button type="submit" class="btn-login">Войти</button>

            <div class="form-links">
                <a href="#" onclick="showComingSoon('Восстановление пароля')">Забыли пароль? Восстановить доступ</a>
            </div>

            <div class="divider"></div>

            <div class="register-link">
                Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
            </div>
        </form>
    </main>

    <!-- Футер -->
    <footer class="footer">
        <p>© 2025 УчиПросто. Все права защищены.</p>
    </footer>

    <script>
        function showComingSoon(feature) {
            alert(`Функция "${feature}" находится в разработке. Скоро будет доступна!`);
        }
    </script>
</body>
</html>