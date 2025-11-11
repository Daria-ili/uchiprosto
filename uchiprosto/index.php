<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>УчиПросто - Главная страница</title>
    <link rel="stylesheet" href="config/css/style.css">
    <style>
        /* ВРЕМЕННЫЕ СТИЛИ ДЛЯ РАЗРАБОТКИ - ГЛАВНАЯ СТРАНИЦА */
        
        /* Сброс и базовые стили */
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
        
        /* Хедер - светлый с тенью */
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
        }
        
        /* Герой секция - белый фон с тенью */
        .hero {
            background: white;
            padding: 5rem 2rem;
            text-align: center;
            margin: 2rem;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(74, 144, 226, 0.1), transparent);
            transform: rotate(-45deg);
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            color: #2c3e50;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2.5rem;
            color: #5a6c7d;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.8;
        }
        
        .hero-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .hero-links a {
            color: #4a90e2;
            text-decoration: none;
            font-weight: 600;
            padding: 1rem 2rem;
            border: 2px solid #4a90e2;
            border-radius: 25px;
            transition: all 0.3s ease;
            background: white;
            position: relative;
            overflow: hidden;
        }
        
        .hero-links a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(74, 144, 226, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .hero-links a:hover::before {
            left: 100%;
        }
        
        .hero-links a:hover {
            background: #4a90e2;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(74, 144, 226, 0.3);
        }
        
        /* Секции */
        .section {
            padding: 5rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 4rem;
            font-size: 2.5rem;
            color: white;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        /* Как это работает - карточки с иконками */
        .how-it-works {
            position: relative;
        }
        
        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2.5rem;
        }
        
        .step {
            background: white;
            padding: 3rem 2rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .step:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .step-number {
            background: linear-gradient(135deg, #4a90e2, #667eea);
            color: white;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 1.8rem;
            font-weight: bold;
            box-shadow: 0 8px 20px rgba(74, 144, 226, 0.3);
            position: relative;
        }
        
        .step-number::after {
            content: '';
            position: absolute;
            width: 80px;
            height: 80px;
            border: 2px solid rgba(74, 144, 226, 0.3);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        .step h3 {
            margin-bottom: 1.5rem;
            color: #2c3e50;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .step p {
            color: #5a6c7d;
            line-height: 1.7;
            font-size: 1.1rem;
        }
        
        /* Наши репетиторы */
        .tutors {
            position: relative;
        }
        
        .tutors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2.5rem;
            margin-bottom: 4rem;
        }
        
        .tutor-card {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .tutor-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #4a90e2, #667eea);
        }
        
        .tutor-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .tutor-name {
            font-weight: bold;
            font-size: 1.4rem;
            margin-bottom: 1rem;
            color: #2c3e50;
        }
        
        .tutor-subjects {
            color: #5a6c7d;
            margin-bottom: 1.5rem;
            line-height: 1.6;
            font-size: 1.1rem;
        }
        
        .tutor-price {
            color: #e74c3c;
            font-weight: bold;
            font-size: 1.4rem;
            margin-bottom: 2rem;
        }
        
        .view-profile {
            display: inline-block;
            background: linear-gradient(135deg, #4a90e2, #667eea);
            color: white;
            padding: 1rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.3);
        }
        
        .view-profile:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(74, 144, 226, 0.4);
            background: linear-gradient(135deg, #357abd, #5a6fd8);
        }
        
        .view-all {
            text-align: center;
        }
        
        .view-all-button {
            display: inline-block;
            background: white;
            color: #4a90e2;
            padding: 1.2rem 2.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            border: 2px solid #4a90e2;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .view-all-button:hover {
            background: #4a90e2;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(74, 144, 226, 0.3);
        }
        
        /* Секция Почему выбирают - ПОЛНОСТЬЮ ИСПРАВЛЕННАЯ */
        .why-choose {
            padding: 4rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .why-choose .section-title {
            color: white; /* Белый текст */
            margin-bottom: 3rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .features {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature {
            background: white;
            padding: 2rem 1.5rem;
            border-radius: 15px;
            text-align: center;
            border: 2px solid rgba(255,255,255,0.3);
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 220px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .feature:hover {
            transform: translateY(-5px);
            border-color: #4a90e2;
            box-shadow: 0 12px 30px rgba(74, 144, 226, 0.15);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            margin-bottom: 1.5rem;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #4a90e2;
            position: relative;
            font-size: 2.2rem;
            flex-shrink: 0;
        }

        .feature-icon::before {
            content: '';
            position: absolute;
            width: 90px;
            height: 90px;
            border: 2px solid rgba(74, 144, 226, 0.2);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        /* Иконки для каждой фичи */
        .feature:nth-child(1) .feature-icon::after {
            content: '✓';
            color: #27ae60;
            font-weight: bold;
        }

        .feature:nth-child(2) .feature-icon::after {
            content: '👥';
        }

        .feature:nth-child(3) .feature-icon::after {
            content: '📅';
        }

        .feature:nth-child(4) .feature-icon::after {
            content: '🛡️';
        }

        .feature-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
        }

        .feature-content h3 {
            margin-bottom: 1rem;
            color: #2c3e50;
            font-size: 1.3rem;
            font-weight: 600;
            line-height: 1.3;
            text-align: center;
        }

        .feature-content p {
            color: #5a6c7d;
            line-height: 1.5;
            font-size: 1rem;
            text-align: center;
            margin: 0;
        }

        /* Адаптивность для мобильных */
        @media (max-width: 1024px) {
            .features {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }
            
            .feature {
                min-height: 200px;
                padding: 1.5rem 1rem;
            }
        }

        @media (max-width: 768px) {
            .features {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .why-choose {
                padding: 3rem 1rem;
            }
            
            .feature {
                min-height: auto;
                padding: 2rem 1rem;
            }
            
            .feature-icon {
                width: 70px;
                height: 70px;
                font-size: 1.8rem;
            }
            
            .feature-icon::before {
                width: 80px;
                height: 80px;
            }
        }
        
        /* Футер - уже */
        .footer {
            background: rgba(44, 62, 80, 0.9);
            color: white;
            text-align: center;
            padding: 1.5rem 2rem;
            margin-top: 4rem;
            backdrop-filter: blur(10px);
        }
        
        /* Анимации */
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.7;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Адаптивность */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .nav-links {
                gap: 1rem;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .hero {
                margin: 1rem;
                padding: 3rem 1rem;
            }
            
            .hero h1 {
                font-size: 2.2rem;
            }
            
            .hero-links {
                flex-direction: column;
                align-items: center;
            }
            
            .section {
                padding: 3rem 1rem;
            }
            
            .steps {
                grid-template-columns: 1fr;
            }
            
            .tutors-grid {
                grid-template-columns: 1fr;
            }
            
            .footer {
                padding: 1rem 2rem;
            }
        }

        @media (max-width: 480px) {
            .footer {
                padding: 0.8rem 1rem;
                font-size: 0.9rem;
            }

	.step, .tutor-card, .feature {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
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
        <!-- Герой секция -->
        <section class="hero">
            <h1>Найти своего идеального репетитора Просто.</h1>
            <p>Прямой контакт с преподавателем без комиссий и посредников. Начните обучение уже сегодня!</p>
            <div class="hero-links">
                <a href="#how-it-works">Как это работает</a>
                <a href="#tutors">Наши репетиторы</a>
                <a href="#why-choose">Почему именно мы</a>
            </div>
        </section>

        <!-- Как это работает -->
        <section id="how-it-works" class="section how-it-works">
            <h2 class="section-title">Как это работает</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Найдите</h3>
                    <p>Выберите репетитора по предмету, цене и рейтингу из нашей базы проверенных преподавателей</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Посмотрите</h3>
                    <p>Изучите подробную анкету, отзывы учеников и свободное расписание репетитора</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Свяжитесь</h3>
                    <p>Напишите преподавателю напрямую через удобную форму связи на сайте</p>
                </div>
            </div>
        </section>

        <!-- Наши репетиторы -->
<section id="tutors" class="section tutors">
    <h2 class="section-title">Наши репетиторы</h2>
    <div class="tutors-grid">
        <div class="tutor-card">
            <div class="tutor-name">ИЛЬЮШОНОК М.В.</div>
            <div class="tutor-subjects">Учитель начальных классов, ведет математику у 3-ых классов.</div>
            <div class="tutor-price">500 руб./час</div>
            <a href="profiles.php?id=1" class="view-profile">Изучить анкету</a>
        </div>
        <div class="tutor-card">
            <div class="tutor-name">ЛЕБЕДЕВА О.В.</div>
            <div class="tutor-subjects">Учитель математики, подготовка к ЕГЭ и ОГЭ.</div>
            <div class="tutor-price">500 руб./час</div>
            <a href="profiles.php?id=2" class="view-profile">Изучить анкету</a>
        </div>
        <div class="tutor-card">
            <div class="tutor-name">ЦАЛКО А.В.</div>
            <div class="tutor-subjects">Учитель английского языка с международным сертификатом</div>
            <div class="tutor-price">500 руб./час</div>
            <a href="profiles.php?id=3" class="view-profile">Изучить анкету</a>
        </div>
    </div>
    <div class="view-all">
        <a href="tutors.php" class="view-all-button">Смотреть всех репетиторов</a>
    </div>
</section>

        <!-- Почему выбирают - ИСПРАВЛЕННАЯ СЕКЦИЯ -->
        <section id="why-choose" class="why-choose">
            <h2 class="section-title">Почему выбирают УчиПросто</h2>
            <div class="features">
                <div class="feature">
                    <div class="feature-icon"></div>
                    <div class="feature-content">
                        <h3>Проверенные репетиторы</h3>
                        <p>Все преподаватели проходят проверку документов и опыта работы</p>
                    </div>
                </div>
                <div class="feature">
                    <div class="feature-icon"></div>
                    <div class="feature-content">
                        <h3>Прямой контакт</h3>
                        <p>Общайтесь с репетитором напрямую без посредников и комиссий</p>
                    </div>
                </div>
                <div class="feature">
                    <div class="feature-icon"></div>
                    <div class="feature-content">
                        <h3>Удобное расписание</h3>
                        <p>Выбирайте удобное время для занятий из свободных окон репетитора</p>
                    </div>
                </div>
                <div class="feature">
                    <div class="feature-icon"></div>
                    <div class="feature-content">
                        <h3>Безопасность</h3>
                        <p>Ваши данные защищены, все платежи проходят безопасно</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Футер -->
    <footer class="footer">
        <p>© 2025 УчиПросто. Все права защищены.</p>
    </footer>

    <script>
    // Плавная прокрутка для якорных ссылок
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Сначала скрываем все анимируемые элементы
    document.querySelectorAll('.step, .tutor-card, .feature').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
    });

    // Анимация появления элементов при скролле
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Наблюдаем за карточками и шагами
    document.querySelectorAll('.step, .tutor-card, .feature').forEach(el => {
        observer.observe(el);
    });
</script>
</body>
</html>