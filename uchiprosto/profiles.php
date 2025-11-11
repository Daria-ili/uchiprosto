<?php 
session_start();
$page_title = 'Профиль репетитора';

if (!isset($_GET['id'])) {
    header('Location: tutors.php');
    exit;
}

require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// Получаем данные репетитора
$query = "SELECT t.*, u.first_name, u.last_name, u.patronymic, u.phone 
          FROM tutors t 
          JOIN users u ON t.user_id = u.id 
          WHERE t.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_GET['id']]);
$tutor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tutor) {
    header('Location: tutors.php');
    exit;
}

// Получаем расписание
$scheduleQuery = "SELECT day_of_week, time_slot, is_available 
                  FROM schedule 
                  WHERE tutor_id = ? 
                  ORDER BY FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), time_slot";
$scheduleStmt = $db->prepare($scheduleQuery);
$scheduleStmt->execute([$_GET['id']]);
$schedule = $scheduleStmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем отзывы
$reviewsQuery = "SELECT r.rating, r.comment, u.first_name, u.last_name, r.created_at 
                 FROM reviews r 
                 JOIN students s ON r.student_id = s.id 
                 JOIN users u ON s.user_id = u.id 
                 WHERE r.tutor_id = ? 
                 ORDER BY r.created_at DESC";
$reviewsStmt = $db->prepare($reviewsQuery);
$reviewsStmt->execute([$_GET['id']]);
$reviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль репетитора - УчиПросто</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

    <div class="profile-container">
        <!-- Шапка профиля -->
        <div class="profile-header">
            <div class="tutor-avatar-large">
                <?php echo mb_substr($tutor['first_name'], 0, 1) . mb_substr($tutor['last_name'], 0, 1); ?>
            </div>
            <div class="profile-header-content">
                <h1><?php echo $tutor['last_name'] . ' ' . $tutor['first_name'] . ' ' . $tutor['patronymic']; ?></h1>
                <p class="tutor-subjects"><?php echo htmlspecialchars($tutor['subjects']); ?></p>
                
                <div class="tutor-meta">
                    <div class="meta-item">
                        <i class="fas fa-briefcase"></i>
                        <span>Стаж: <?php echo $tutor['experience']; ?> лет</span>
                    </div>
                    <?php if ($tutor['rating']): ?>
                    <div class="meta-item">
                        <i class="fas fa-star"></i>
                        <span>Рейтинг: <?php echo $tutor['rating']; ?>/5 (<?php echo $tutor['total_reviews']; ?> отзывов)</span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="profile-header-actions">
                    <div class="price-tag">
                        <?php echo $tutor['price_per_hour']; ?> руб./час
                    </div>

                    <div class="accepting-status <?php echo $tutor['is_accepting_students'] ? 'accepting-true' : 'accepting-false'; ?>">
                        <i class="fas <?php echo $tutor['is_accepting_students'] ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                        <?php echo $tutor['is_accepting_students'] ? 'Ведется набор учеников' : 'Сейчас не принимает учеников'; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="profile-content">
            <!-- О себе -->
            <?php if (!empty($tutor['about_me'])): ?>
            <div class="profile-section">
                <h2 class="section-title">О себе</h2>
                <div class="about-content">
                    <?php echo nl2br(htmlspecialchars($tutor['about_me'])); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Подход к обучению -->
            <?php if (!empty($tutor['teaching_approach'])): ?>
            <div class="profile-section">
                <h2 class="section-title">Мой подход к обучению</h2>
                <div class="about-content">
                    <?php echo nl2br(htmlspecialchars($tutor['teaching_approach'])); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Расписание -->
            <div class="profile-section">
                <h2 class="section-title">Расписание занятий</h2>
                <div class="schedule-container">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th class="time-header">Время</th>
                                <th>Пн</th>
                                <th>Вт</th>
                                <th>Ср</th>
                                <th>Чт</th>
                                <th>Пт</th>
                                <th>Сб</th>
                                <th>Вс</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $time_slots = [
                                '15:00-16:00',
                                '16:00-17:00', 
                                '17:00-18:00',
                                '18:00-19:00',
                                '19:00-20:00'
                            ];
                            
                            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                            $day_names = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
                            
                            foreach ($time_slots as $time_slot) {
                                echo '<tr>';
                                echo '<td class="time-slot"><strong>' . $time_slot . '</strong></td>';
                                
                                foreach ($days as $day) {
                                    $found = false;
                                    $is_available = false;
                                    
                                    foreach ($schedule as $slot) {
                                        if ($slot['day_of_week'] == $day && $slot['time_slot'] == $time_slot) {
                                            $is_available = $slot['is_available'];
                                            $found = true;
                                            break;
                                        }
                                    }
                                    
                                    $status_class = $found ? ($is_available ? 'available' : 'busy') : 'empty';
                                    $status_text = $found ? ($is_available ? '✓' : '✗') : '—';
                                    $status_title = $found ? ($is_available ? 'Свободно' : 'Занято') : 'Недоступно';
                                    
                                    echo '<td class="schedule-cell ' . $status_class . '" title="' . $status_title . '">';
                                    echo '<span class="status-indicator">' . $status_text . '</span>';
                                    echo '</td>';
                                }
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                    
                    <div class="schedule-legend">
                        <div class="legend-item">
                            <span class="legend-color available"></span>
                            <span>Свободно</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color busy"></span>
                            <span>Занято</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color empty"></span>
                            <span>Недоступно</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Отзывы -->
            <?php if (!empty($reviews)): ?>
            <div class="profile-section">
                <h2 class="section-title">Отзывы учеников</h2>
                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-author-info">
                                    <div class="review-author"><?php echo $review['first_name'] . ' ' . $review['last_name']; ?></div>
                                    <div class="review-rating">
                                        <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                                        <span class="rating-value"><?php echo $review['rating']; ?>/5</span>
                                    </div>
                                </div>
                                <div class="review-date"><?php echo date('d.m.Y', strtotime($review['created_at'])); ?></div>
                            </div>
                            <div class="review-comment">
                                <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Форма связи -->
            <div class="contact-section">
                <h3>Хотите заниматься с этим репетитором?</h3>
                <p>Отправьте запрос на сотрудничество и обсудите детали обучения</p>
                
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'student'): ?>
                    <form action="send_request.php" method="POST" class="contact-form">
                        <input type="hidden" name="tutor_id" value="<?php echo $tutor['id']; ?>">
                        <textarea name="message" placeholder="Напишите сообщение репетитору..." required></textarea>
                        <button type="submit" class="btn-contact">
                            <i class="fas fa-paper-plane"></i> Отправить запрос на сотрудничество
                        </button>
                    </form>
                <?php else: ?>
                    <div class="login-prompt">
                        <p><a href="login.php" class="btn-contact">Войдите как ученик</a> чтобы отправить запрос</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Футер -->
    <footer class="footer">
        <p>© 2025 УчиПросто. Все права защищены.</p>
    </footer>

    <script>
        // Анимация появления элементов при скролле
        document.addEventListener('DOMContentLoaded', function() {
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

            // Наблюдаем за секциями
            document.querySelectorAll('.profile-section, .contact-section').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
                observer.observe(el);
            });
        });
    </script>
</body>
</html>