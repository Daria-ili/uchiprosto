<?php
session_start();

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// Если пользователь администратор - показываем админ-панель
if ($_SESSION['user_type'] == 'admin') {
    // Получаем статистику для админа
    $statsQuery = "
    SELECT 
        (SELECT COUNT(DISTINCT t.user_id) FROM tutors t JOIN users u ON t.user_id = u.id WHERE u.user_type = 'tutor') as total_tutors,
            (SELECT COUNT(*) FROM users WHERE user_type = 'student') as total_students,
            (SELECT COUNT(*) FROM contacts) as total_contacts,
            (SELECT COUNT(*) FROM support_messages WHERE status = 'new') as new_support_messages,
            (SELECT COUNT(*) FROM reviews) as total_reviews
    ";
    $statsStmt = $db->prepare($statsQuery);
    $statsStmt->execute();
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    // Получаем последние сообщения поддержки
    $supportQuery = "SELECT * FROM support_messages ORDER BY created_at DESC LIMIT 5";
    $supportStmt = $db->prepare($supportQuery);
    $supportStmt->execute();
    $recent_support = $supportStmt->fetchAll(PDO::FETCH_ASSOC);

    // Получаем последние регистрации
    $recentUsersQuery = "
        SELECT u.*, 
               CASE 
                   WHEN u.user_type = 'tutor' THEN t.subjects 
                   WHEN u.user_type = 'student' THEN s.grade 
                   ELSE 'Администратор' 
               END as additional_info
        FROM users u 
        LEFT JOIN tutors t ON u.id = t.user_id 
        LEFT JOIN students s ON u.id = s.user_id 
        ORDER BY u.created_at DESC 
        LIMIT 5
    ";
    $recentUsersStmt = $db->prepare($recentUsersQuery);
    $recentUsersStmt->execute();
    $recent_users = $recentUsersStmt->fetchAll(PDO::FETCH_ASSOC);
    
} else if ($_SESSION['user_type'] == 'tutor') {
    // Личный кабинет репетитора
    $query = "SELECT c.*, u.first_name, u.last_name, u.patronymic, s.grade 
              FROM contacts c 
              JOIN students s ON c.student_id = s.id 
              JOIN users u ON s.user_id = u.id 
              WHERE c.tutor_id IN (SELECT id FROM tutors WHERE user_id = ?) 
              ORDER BY c.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} else {
    // Личный кабинет студента
    $query = "SELECT c.*, u.first_name, u.last_name, u.patronymic, t.subjects 
              FROM contacts c 
              JOIN tutors t ON c.tutor_id = t.id 
              JOIN users u ON t.user_id = u.id 
              WHERE c.student_id IN (SELECT id FROM students WHERE user_id = ?) 
              ORDER BY c.created_at DESC";
    $studentQuery = "SELECT id FROM students WHERE user_id = ?";
    $studentStmt = $db->prepare($studentQuery);
    $studentStmt->execute([$_SESSION['user_id']]);
    $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
    $stmt = $db->prepare($query);
    $stmt->execute([$student['id']]);
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - УчиПросто</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard {
            max-width: 1200px;
            margin: 100px auto 2rem;
            padding: 0 1rem;
        }
        
        .welcome-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .requests-section, .contacts-section, .admin-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .request-item, .contact-item {
            border: 1px solid #e1e5e9;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .status-pending { color: #f39c12; }
        .status-approved { color: #27ae60; }
        .status-rejected { color: #e74c3c; }
        
        /* Стили для админ-панели */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #667eea;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .message-item {
            border: 1px solid #e1e5e9;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .message-item:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .message-meta {
            color: #666;
            font-size: 0.9rem;
        }
        
        .message-status {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-new {
            background: #ffeaa7;
            color: #e17055;
        }
        
        .status-in_progress {
            background: #81ecec;
            color: #00cec9;
        }
        
        .status-resolved {
            background: #55efc4;
            color: #00b894;
        }
        
        .admin-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .btn-admin {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .user-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e1e5e9;
        }
        
        .user-info h4 {
            margin: 0;
            color: #2c3e50;
        }
        
        .user-meta {
            color: #666;
            font-size: 0.9rem;
        }
        
        .user-type {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            background: #e9ecef;
            color: #495057;
        }
        
        .user-type.tutor {
            background: #d4edda;
            color: #155724;
        }
        
        .user-type.student {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .user-type.admin {
            background: #f8d7da;
            color: #721c24;
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
                <?php if ($_SESSION['user_type'] == 'admin'): ?>
                    <a href="dashboard.php">Админ-панель</a>
                    <a href="admin_support.php">Поддержка</a>
                <?php else: ?>
                    <a href="dashboard.php">Личный кабинет</a>
                <?php endif; ?>
                <a href="logout.php">Выйти</a>
            </div>
        </div>
    </header>

    <div class="dashboard">
        <div class="welcome-section">
            <h1>Добро пожаловать, <?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?>!</h1>
            <p>Вы вошли как: <strong>
                <?php 
                if ($_SESSION['user_type'] == 'admin') {
                    echo 'Администратор';
                } else if ($_SESSION['user_type'] == 'tutor') {
                    echo 'Репетитор';
                } else {
                    echo 'Ученик';
                }
                ?>
            </strong></p>
        </div>

        <?php if ($_SESSION['user_type'] == 'admin'): ?>
            <!-- Панель администратора -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_tutors']; ?></div>
                    <div class="stat-label">Репетиторов</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_students']; ?></div>
                    <div class="stat-label">Учеников</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_contacts']; ?></div>
                    <div class="stat-label">Запросов на обучение</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['new_support_messages']; ?></div>
                    <div class="stat-label">Новых сообщений поддержки</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_reviews']; ?></div>
                    <div class="stat-label">Отзывов</div>
                </div>
            </div>

            <!-- Последние сообщения поддержки -->
            <div class="admin-section">
                <h2>Последние сообщения поддержки</h2>
                <?php if (empty($recent_support)): ?>
                    <p>Сообщений нет</p>
                <?php else: ?>
                    <?php foreach ($recent_support as $message): ?>
                        <div class="message-item">
                            <div class="message-header">
                                <div>
                                    <h4><?php echo htmlspecialchars($message['subject']); ?></h4>
                                    <div class="message-meta">
                                        От: <?php echo htmlspecialchars($message['name']); ?> 
                                        (<?php echo htmlspecialchars($message['email']); ?>)
                                        - <?php echo date('d.m.Y H:i', strtotime($message['created_at'])); ?>
                                    </div>
                                </div>
                                <span class="message-status status-<?php echo $message['status']; ?>">
                                    <?php 
                                    $status_text = [
                                        'new' => 'Новое',
                                        'in_progress' => 'В работе', 
                                        'resolved' => 'Решено'
                                    ];
                                    echo $status_text[$message['status']];
                                    ?>
                                </span>
                            </div>
                            <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                            <div class="admin-actions">
                                <a href="admin_support.php" class="btn-admin btn-primary">Управление сообщениями</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Последние пользователи -->
            <div class="admin-section">
                <h2>Последние регистрации</h2>
                <?php if (empty($recent_users)): ?>
                    <p>Пользователей нет</p>
                <?php else: ?>
                    <?php foreach ($recent_users as $user): ?>
                        <div class="user-item">
                            <div class="user-info">
                                <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                                <div class="user-meta">
                                    <?php echo htmlspecialchars($user['email']); ?> 
                                    - <?php echo date('d.m.Y', strtotime($user['created_at'])); ?>
                                    <?php if ($user['additional_info']): ?>
                                        - <?php echo htmlspecialchars($user['additional_info']); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="user-type <?php echo $user['user_type']; ?>">
                                <?php 
                                $type_text = [
                                    'tutor' => 'Репетитор',
                                    'student' => 'Ученик', 
                                    'admin' => 'Администратор'
                                ];
                                echo $type_text[$user['user_type']];
                                ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <?php elseif ($_SESSION['user_type'] == 'tutor'): ?>
            <!-- Личный кабинет репетитора -->
            <div class="requests-section">
                <h2>Заявки от учеников</h2>
                <?php if (empty($requests)): ?>
                    <p>Заявок пока нет</p>
                <?php else: ?>
                    <?php foreach ($requests as $request): ?>
                        <div class="request-item">
                            <h3><?php echo $request['first_name'] . ' ' . $request['last_name']; ?></h3>
                            <p>Класс: <?php echo $request['grade']; ?></p>
                            <p>Сообщение: <?php echo htmlspecialchars($request['message']); ?></p>
                            <p>Статус: <span class="status-<?php echo $request['status']; ?>">
                                <?php 
                                $status_text = [
                                    'pending' => 'Ожидает',
                                    'approved' => 'Одобрена', 
                                    'rejected' => 'Отклонена'
                                ];
                                echo $status_text[$request['status']];
                                ?>
                            </span></p>
                            <?php if ($request['status'] == 'pending'): ?>
                                <div style="margin-top: 1rem;">
                                    <a href="process_request.php?contact_id=<?php echo $request['id']; ?>&action=approve" class="tutor-btn-primary">Принять</a>
                                    <a href="process_request.php?contact_id=<?php echo $request['id']; ?>&action=reject" class="btn-outline">Отклонить</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- Личный кабинет ученика -->
            <div class="contacts-section">
                <h2>Мои контакты с репетиторами</h2>
                <?php if (empty($contacts)): ?>
                    <p>У вас пока нет контактов с репетиторами</p>
                <?php else: ?>
                    <?php foreach ($contacts as $contact): ?>
                        <div class="contact-item">
                            <h3><?php echo $contact['first_name'] . ' ' . $contact['last_name'] . ' ' . $contact['patronymic']; ?></h3>
                            <p>Предмет: <?php echo $contact['subjects']; ?></p>
                            <p>Статус: <span class="status-<?php echo $contact['status']; ?>">
                                <?php 
                                $status_text = [
                                    'pending' => 'Ожидает ответа',
                                    'approved' => 'Одобрена', 
                                    'rejected' => 'Отклонена'
                                ];
                                echo $status_text[$contact['status']];
                                ?>
                            </span></p>
                            <?php if ($contact['status'] == 'approved'): ?>
                                <a href="messenger.php?contact_id=<?php echo $contact['id']; ?>" class="tutor-btn-primary">Написать сообщение</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>