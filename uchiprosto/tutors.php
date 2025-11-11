<?php 
session_start();
$page_title = 'Анкеты репетиторов';

// Подключение к базе данных
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// Базовый запрос
$query = "SELECT t.id, u.first_name, u.last_name, u.patronymic, t.subjects, t.experience, t.price_per_hour, t.rating, t.total_reviews, t.is_accepting_students 
          FROM tutors t 
          JOIN users u ON t.user_id = u.id 
          WHERE t.is_accepting_students = 1";

$params = [];

// Фильтр по предмету
if (isset($_GET['subject']) && !empty($_GET['subject'])) {
    $query .= " AND t.subjects LIKE ?";
    $params[] = '%' . $_GET['subject'] . '%';
}

// Фильтр по минимальной цене
if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $query .= " AND t.price_per_hour >= ?";
    $params[] = $_GET['min_price'];
}

// Фильтр по максимальной цене
if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $query .= " AND t.price_per_hour <= ?";
    $params[] = $_GET['max_price'];
}

// Фильтр по стажу
if (isset($_GET['experience']) && !empty($_GET['experience'])) {
    $query .= " AND t.experience >= ?";
    $params[] = $_GET['experience'];
}

// Фильтр по приему учеников
if (isset($_GET['accepting_students']) && $_GET['accepting_students'] == '1') {
    $query .= " AND t.is_accepting_students = 1";
}

$query .= " ORDER BY t.rating DESC, t.total_reviews DESC";

// Подготовка и выполнение запроса
$stmt = $db->prepare($query);

if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}

$tutors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкеты репетиторов - УчиПросто</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Дополнительные стили для обеспечения отображения */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #2c3e50;
        }
        
        /* Стили хедера как на главной */
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
        }
        
        .nav-links a:hover {
            background: #4a90e2;
            color: white;
            transform: translateY(-2px);
        }
        
        .auth-links a {
            text-decoration: none;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            padding: 0.8rem 1.8rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        
        .auth-links a:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-2px);
        }
        
        .hero {
            background: white;
            padding: 4rem 2rem;
            text-align: center;
            margin: 2rem;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            margin-top: 111px;
        }
        
        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #2c3e50;
        }
        
        .hero p {
            font-size: 1.2rem;
            color: #666;
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

    <!-- Герой секция -->
    <section class="hero">
        <h1>Найдите своего идеального репетитора</h1>
        <p>Прямой контакт с преподавателем без комиссий и посредников. Начните обучение уже сегодня!</p>
    </section>

    <div class="tutors-container">
        <div class="filters-sidebar">
            <h3>Фильтры</h3>
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label>Предмет</label>
                    <select name="subject">
                        <option value="">Все предметы</option>
                        <option value="Математика" <?php echo (isset($_GET['subject']) && $_GET['subject'] == 'Математика') ? 'selected' : ''; ?>>Математика</option>
                        <option value="Русский язык" <?php echo (isset($_GET['subject']) && $_GET['subject'] == 'Русский язык') ? 'selected' : ''; ?>>Русский язык</option>
                        <option value="Английский язык" <?php echo (isset($_GET['subject']) && $_GET['subject'] == 'Английский язык') ? 'selected' : ''; ?>>Английский язык</option>
                        <option value="Физика" <?php echo (isset($_GET['subject']) && $_GET['subject'] == 'Физика') ? 'selected' : ''; ?>>Физика</option>
                        <option value="Химия" <?php echo (isset($_GET['subject']) && $_GET['subject'] == 'Химия') ? 'selected' : ''; ?>>Химия</option>
                        <option value="Биология" <?php echo (isset($_GET['subject']) && $_GET['subject'] == 'Биология') ? 'selected' : ''; ?>>Биология</option>
                        <option value="История" <?php echo (isset($_GET['subject']) && $_GET['subject'] == 'История') ? 'selected' : ''; ?>>История</option>
                        <option value="Обществознание" <?php echo (isset($_GET['subject']) && $_GET['subject'] == 'Обществознание') ? 'selected' : ''; ?>>Обществознание</option>
                        <option value="География" <?php echo (isset($_GET['subject']) && $_GET['subject'] == 'География') ? 'selected' : ''; ?>>География</option>
                        <option value="Литература" <?php echo (isset($_GET['subject']) && $_GET['subject'] == 'Литература') ? 'selected' : ''; ?>>Литература</option>
                        <option value="Информатика" <?php echo (isset($_GET['subject']) && $_GET['subject'] == 'Информатика') ? 'selected' : ''; ?>>Информатика</option>
                        <option value="Начальные классы" <?php echo (isset($_GET['subject']) && $_GET['subject'] == 'Начальные классы') ? 'selected' : ''; ?>>Начальные классы</option>
                        <option value="Подготовка к школе" <?php echo (isset($_GET['subject']) && $_GET['subject'] == 'Подготовка к школе') ? 'selected' : ''; ?>>Подготовка к школе</option>
                        <option value="Французский язык" <?php echo (isset($_GET['subject']) && $_GET['subject'] == 'Французский язык') ? 'selected' : ''; ?>>Французский язык</option>
                        <option value="Немецкий язык" <?php echo (isset($_GET['subject']) && $_GET['subject'] == 'Немецкий язык') ? 'selected' : ''; ?>>Немецкий язык</option>
                        <option value="Испанский язык" <?php echo (isset($_GET['subject']) && $_GET['subject'] == 'Испанский язык') ? 'selected' : ''; ?>>Испанский язык</option>
                        <option value="Музыка" <?php echo (isset($_GET['subject']) && $_GET['subject'] == 'Музыка') ? 'selected' : ''; ?>>Музыка</option>
                        <option value="Рисование" <?php echo (isset($_GET['subject']) && $_GET['subject'] == 'Рисование') ? 'selected' : ''; ?>>Рисование</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Цена за час, руб.</label>
                    <div class="price-range">
                        <input type="number" name="min_price" placeholder="От" value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
                        <span>-</span>
                        <input type="number" name="max_price" placeholder="До" value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Стаж, лет</label>
                    <select name="experience">
                        <option value="">Любой стаж</option>
                        <option value="1" <?php echo (isset($_GET['experience']) && $_GET['experience'] == '1') ? 'selected' : ''; ?>>От 1 года</option>
                        <option value="3" <?php echo (isset($_GET['experience']) && $_GET['experience'] == '3') ? 'selected' : ''; ?>>От 3 лет</option>
                        <option value="5" <?php echo (isset($_GET['experience']) && $_GET['experience'] == '5') ? 'selected' : ''; ?>>От 5 лет</option>
                        <option value="10" <?php echo (isset($_GET['experience']) && $_GET['experience'] == '10') ? 'selected' : ''; ?>>От 10 лет</option>
                    </select>
                </div>
                
                           
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="accepting_students" value="1" <?php echo (isset($_GET['accepting_students']) && $_GET['accepting_students'] == '1') ? 'checked' : ''; ?>>
                        <span>Принимает учеников</span>
                    </label>
                </div>

                <button type="submit" class="btn-filter">Применить фильтры</button>
                <a href="tutors.php" class="btn-outline">Сбросить фильтры</a>
            </form>
        </div>

        <div class="tutors-content">
            <div class="results-info">
                <p>Найдено репетиторов: <strong><?php echo count($tutors); ?></strong></p>
                <?php if (!empty($_GET)): ?>
                    <p class="active-filters">
                        Активные фильтры: 
                        <?php
                        $active_filters = [];
                        if (isset($_GET['subject']) && !empty($_GET['subject'])) $active_filters[] = "Предмет: " . htmlspecialchars($_GET['subject']);
                        if (isset($_GET['min_price']) && !empty($_GET['min_price'])) $active_filters[] = "Цена от: " . htmlspecialchars($_GET['min_price']) . " руб.";
                        if (isset($_GET['max_price']) && !empty($_GET['max_price'])) $active_filters[] = "Цена до: " . htmlspecialchars($_GET['max_price']) . " руб.";
                        if (isset($_GET['experience']) && !empty($_GET['experience'])) $active_filters[] = "Стаж от: " . htmlspecialchars($_GET['experience']) . " лет";
                        if (isset($_GET['accepting_students']) && $_GET['accepting_students'] == '1') $active_filters[] = "Принимает учеников";
                        
                        echo implode(', ', $active_filters);
                        ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <div class="tutors-list">
                <?php if (count($tutors) > 0): ?>
                    <?php foreach ($tutors as $tutor): ?>
                        <?php
                        // Форматируем имя: Фамилия И.О.
                        $full_name = $tutor['last_name'] . ' ' . mb_substr($tutor['first_name'], 0, 1) . '.' . 
                                    ($tutor['patronymic'] ? mb_substr($tutor['patronymic'], 0, 1) . '.' : '');
                        
                        // Создаем звезды рейтинга
                        $rating_stars = '';
                        if ($tutor['rating']) {
                            $full_stars = floor($tutor['rating']);
                            $has_half_star = ($tutor['rating'] - $full_stars) >= 0.5;
                            $empty_stars = 5 - $full_stars - ($has_half_star ? 1 : 0);
                            
                            $rating_stars = str_repeat('★', $full_stars);
                            if ($has_half_star) {
                                $rating_stars .= '½';
                            }
                            $rating_stars .= str_repeat('☆', $empty_stars);
                        }
                        ?>
                        <div class="tutor-item">
                            <div class="tutor-avatar">
                                <div class="avatar-placeholder"><?php echo mb_substr($tutor['last_name'], 0, 1); ?></div>
                            </div>
                            <div class="tutor-info">
                                <h3><?php echo htmlspecialchars($full_name); ?></h3>
                                <p class="tutor-subjects"><?php echo htmlspecialchars($tutor['subjects']); ?></p>
                                <div class="tutor-details">
                                    <span class="tutor-experience">Стаж: <?php echo htmlspecialchars($tutor['experience']); ?> лет</span>
                                    <?php if ($tutor['rating']): ?>
                                        <span class="tutor-rating">
                                            <?php echo $rating_stars; ?> 
                                            (<?php echo htmlspecialchars($tutor['rating']); ?>/5)
                                            <?php if ($tutor['total_reviews']): ?>
                                                <span class="reviews-count"><?php echo htmlspecialchars($tutor['total_reviews']); ?> отзывов</span>
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="tutor-price"><?php echo htmlspecialchars($tutor['price_per_hour']); ?> руб./час</p>
                                <?php if (!$tutor['is_accepting_students']): ?>
                                    <p class="not-accepting">Сейчас не принимает учеников</p>
                                <?php endif; ?>
                            </div>
                            <div class="tutor-actions">
                                <a href="profiles.php?id=<?php echo $tutor['id']; ?>" class="tutor-btn-primary">Изучить анкету</a>
                                <?php if ($tutor['is_accepting_students'] && isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'student'): ?>
                                    <a href="profiles.php?id=<?php echo $tutor['id']; ?>#contact" class="btn-outline">Написать</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-results">
                        <h3>Репетиторы не найдены</h3>
                        <p>Попробуйте изменить параметры фильтрации</p>
                        <a href="tutors.php" class="tutor-btn-primary">Сбросить фильтры</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    // Анимация появления элементов при скролле
    document.addEventListener('DOMContentLoaded', function() {
        const tutorItems = document.querySelectorAll('.tutor-item');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });
        
        tutorItems.forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(30px)';
            item.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
            observer.observe(item);
        });
    });
    </script>

</body>
</html>