<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Помощь - УчиПросто</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Все стили остаются такими же как в предыдущем коде */
        /* Дополнительные стили для страницы помощи */
        .help-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            width: 100%;
        }

        .help-header {
    text-align: center;
    margin-bottom: 4rem;
    padding: 3rem 0;
    background: white;
    border-radius: 20px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
}


        .help-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(102, 126, 234, 0.05) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }

        .help-header h1 {
            font-size: 3rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            font-weight: 700;
            position: relative;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .help-header p {
            font-size: 1.3rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
            position: relative;
            line-height: 1.6;
        }

        .help-content {
            display: grid;
            gap: 4rem;
        }

        /* Стили для FAQ */
        .faq-section {
    background: white;
    border-radius: 20px;
    padding: 3rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    border: 1px solid rgba(102, 126, 234, 0.1);
    position: relative;
}

        

        .faq-section h2 {
            color: #2c3e50;
            margin-bottom: 2.5rem;
            font-size: 2.2rem;
            text-align: center;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .faq-section h2 i {
            color: #667eea;
            font-size: 1.8rem;
        }

        .faq-grid {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .faq-item {
            border: 2px solid #f1f3f4;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.4s ease;
            background: white;
        }

        .faq-item:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
        }

        .faq-item.active {
            border-color: #667eea;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }

        .faq-question {
            padding: 1.75rem 2rem;
            background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .faq-question:hover {
            background: linear-gradient(135deg, #f0f2ff 0%, #e8ebff 100%);
        }

        .faq-question h3 {
            color: #2c3e50;
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .faq-question h3 i {
            color: #667eea;
            font-size: 1.1rem;
        }

        .faq-toggle {
            font-size: 1.3rem;
            font-weight: bold;
            color: #667eea;
            transition: all 0.4s ease;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(102, 126, 234, 0.1);
        }

        .faq-item.active .faq-toggle {
            transform: rotate(180deg);
            background: #667eea;
            color: white;
        }

        .faq-answer {
            padding: 0 2rem;
            display: none;
            background: white;
            animation: slideDown 0.4s ease-out;
        }

        .faq-answer p {
            color: #555;
            line-height: 1.7;
            padding: 2rem 0;
            margin: 0;
            border-top: 1px solid #f1f3f4;
            font-size: 1.05rem;
        }

        /* Стили для формы поддержки */
        .contact-support {
    background: white;
    border-radius: 20px;
    padding: 3rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    border: 1px solid rgba(102, 126, 234, 0.1);
    position: relative;
}


        .support-card h2 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-size: 2.2rem;
            text-align: center;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .support-card h2 i {
            color: #667eea;
            font-size: 1.8rem;
        }

        .support-card p {
            color: #666;
            text-align: center;
            margin-bottom: 2.5rem;
            font-size: 1.2rem;
            line-height: 1.6;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .support-form {
            max-width: 700px;
            margin: 0 auto;
        }

        .support-form .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .support-form .form-group {
            margin-bottom: 1.5rem;
        }

        .support-form label {
            display: block;
            margin-bottom: 0.75rem;
            color: #2c3e50;
            font-weight: 600;
            font-size: 1rem;
        }

        .support-form .form-control {
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fafbfc;
        }

        .support-form .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
            transform: translateY(-1px);
        }

        .support-form textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .btn-support {
            width: 100%;
            padding: 1.25rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
            position: relative;
            overflow: hidden;
        }

        .btn-support::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-support:hover::before {
            left: 100%;
        }

        .btn-support:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4);
        }

        /* Анимации */
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(180deg); }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Адаптивность для страницы помощи */
        @media (max-width: 768px) {
            .help-header {
                padding: 2rem 1rem;
                margin-bottom: 3rem;
            }

            .help-header h1 {
                font-size: 2.2rem;
            }
            
            .help-header p {
                font-size: 1.1rem;
            }
            
            .faq-section,
            .contact-support {
                padding: 2rem 1.5rem;
            }
            
            .faq-section h2,
            .support-card h2 {
                font-size: 1.8rem;
            }
            
            .support-form .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .faq-question {
                padding: 1.5rem;
            }
            
            .faq-question h3 {
                font-size: 1.1rem;
            }

            .btn-support {
                padding: 1.1rem;
                font-size: 1.1rem;
            }
        }

        @media (max-width: 480px) {
            .help-container {
                padding: 0 15px;
            }
            
            .help-header {
                padding: 1.5rem 1rem;
                margin-bottom: 2rem;
            }
            
            .help-header h1 {
                font-size: 1.8rem;
            }
            
            .faq-section,
            .contact-support {
                padding: 1.5rem;
            }
            
            .faq-question {
                padding: 1.25rem;
            }

            .faq-question h3 {
                font-size: 1rem;
            }

            .support-card p {
                font-size: 1.1rem;
            }
        }

        /* Исправления для основного контента */
        .main-content {
            margin-top: 80px;
            flex: 1;
            padding: 2rem;
            display: flex;
            justify-content: center;
            align-items: flex-start;
             background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: calc(100vh - 80px);
        }

        /* Улучшенные алерты */
        .alert {
            padding: 1.25rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            border: none;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border-left: 4px solid #dc3545;
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
        <div class="help-container">
            <div class="help-header">
                <h1><i class="fas fa-hands-helping"></i> Помощь и поддержка</h1>
                <p>Мы всегда готовы помочь вам с любыми вопросами. Найдите ответы ниже или свяжитесь с нами напрямую.</p>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="help-content">
                <div class="faq-section">
                    <h2><i class="fas fa-question-circle"></i> Часто задаваемые вопросы</h2>
                    
                    <div class="faq-grid">
                        <div class="faq-item">
                            <div class="faq-question">
                                <h3><i class="fas fa-search"></i> Как выбрать репетитора?</h3>
                                <span class="faq-toggle">+</span>
                            </div>
                            <div class="faq-answer">
                                <p>Ознакомьтесь с анкетами репетиторов, прочитайте отзывы других учеников и выберите преподавателя, который подходит вам по предмету, цене и расписанию. Используйте фильтры для быстрого поиска.</p>
                            </div>
                        </div>
                        
                        <div class="faq-item">
                            <div class="faq-question">
                                <h3><i class="fas fa-comments"></i> Как связаться с репетитором?</h3>
                                <span class="faq-toggle">+</span>
                            </div>
                            <div class="faq-answer">
                                <p>Отправьте запрос на сотрудничество через форму на странице репетитора. После одобрения заявки вы сможете общаться через встроенный мессенджер в реальном времени.</p>
                            </div>
                        </div>
                        
                        <div class="faq-item">
                            <div class="faq-question">
                                <h3><i class="fas fa-credit-card"></i> Как оплачивать занятия?</h3>
                                <span class="faq-toggle">+</span>
                            </div>
                            <div class="faq-answer">
                                <p>Оплата производится напрямую репетитору после проведения занятия. Мы не берем комиссию за платежей. Доступны различные способы оплаты по договоренности с преподавателем.</p>
                            </div>
                        </div>
                        
                        <div class="faq-item">
                            <div class="faq-question">
                                <h3><i class="fas fa-calendar-times"></i> Можно ли отменить занятие?</h3>
                                <span class="faq-toggle">+</span>
                            </div>
                            <div class="faq-answer">
                                <p>Да, вы можете отменить занятие, предупредив репетитора не менее чем за 24 часа до начала урока. В экстренных случаях свяжитесь с преподавателем для переноса занятия.</p>
                            </div>
                        </div>
                        
                        <div class="faq-item">
                            <div class="faq-question">
                                <h3><i class="fas fa-star"></i> Как оставить отзыв о репетиторе?</h3>
                                <span class="faq-toggle">+</span>
                            </div>
                            <div class="faq-answer">
                                <p>После проведения занятий вы можете оставить отзыв на странице репетитора в соответствующем разделе. Ваше мнение поможет другим ученикам сделать правильный выбор.</p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question">
                                <h3><i class="fas fa-chalkboard-teacher"></i> Как стать репетитором на платформе?</h3>
                                <span class="faq-toggle">+</span>
                            </div>
                            <div class="faq-answer">
                                <p>Зарегистрируйтесь на сайте как репетитор, заполните подробную анкету с информацией об образовании и опыте, и начните принимать заявки от учеников. Наша команда проверит вашу анкету в течение 24 часов.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="contact-support">
                    <div class="support-card">
                        <h2><i class="fas fa-headset"></i> Не нашли ответа на свой вопрос?</h2>
                        <p>Напишите в нашу службу поддержки, и мы поможем решить вашу проблема в течение 24 часов. Мы всегда рады помочь!</p>
                        
                        <form action="send_support.php" method="POST" class="support-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="support_name"><i class="fas fa-user"></i> Ваше имя *</label>
                                    <input type="text" id="support_name" name="name" class="form-control" placeholder="Введите ваше имя" required>
                                </div>
                                <div class="form-group">
                                    <label for="support_email"><i class="fas fa-envelope"></i> Email для ответа *</label>
                                    <input type="email" id="support_email" name="email" class="form-control" placeholder="example@mail.ru" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="support_subject"><i class="fas fa-tag"></i> Тема вопроса *</label>
                                <input type="text" id="support_subject" name="subject" class="form-control" placeholder="Опишите кратко вашу проблему" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="support_message"><i class="fas fa-comment-dots"></i> Сообщение *</label>
                                <textarea id="support_message" name="message" class="form-control" placeholder="Подробно опишите ваш вопрос или проблему..." rows="5" required></textarea>
                            </div>
                            
                            <button type="submit" class="btn-support">
                                <i class="fas fa-paper-plane"></i> Отправить в поддержку
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Футер -->
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2025 УчиПросто. Все права защищены.</p>
            
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                const answer = item.querySelector('.faq-answer');
                const toggle = item.querySelector('.faq-toggle');
                
                question.addEventListener('click', () => {
                    const isOpen = answer.style.display === 'block';
                    
                    // Закрываем все ответы
                    faqItems.forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.querySelector('.faq-answer').style.display = 'none';
                            otherItem.querySelector('.faq-toggle').textContent = '+';
                            otherItem.classList.remove('active');
                            otherItem.querySelector('.faq-toggle').style.background = 'rgba(102, 126, 234, 0.1)';
                            otherItem.querySelector('.faq-toggle').style.color = '#667eea';
                        }
                    });
                    
                    // Переключаем текущий ответ
                    if (isOpen) {
                        answer.style.display = 'none';
                        toggle.textContent = '+';
                        item.classList.remove('active');
                        toggle.style.background = 'rgba(102, 126, 234, 0.1)';
                        toggle.style.color = '#667eea';
                    } else {
                        answer.style.display = 'block';
                        toggle.textContent = '−';
                        item.classList.add('active');
                        toggle.style.background = '#667eea';
                        toggle.style.color = 'white';
                    }
                });
            });

            // Добавляем плавное появление элементов при скролле
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

            // Наблюдаем за карточками
            document.querySelectorAll('.faq-item, .support-card').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        });
    </script>
</body>
</html>