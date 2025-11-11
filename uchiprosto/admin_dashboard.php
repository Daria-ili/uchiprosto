<?php
session_start();

// Проверяем авторизацию администратора
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// Проверяем, является ли пользователь администратором
$query = "SELECT user_type FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['user_type'] != 'admin') {
    $_SESSION['error'] = 'У вас нет прав доступа к этой странице';
    header('Location: index.php');
    exit;
}

// Получаем статистику
$statsQuery = "
    SELECT 
        (SELECT COUNT(*) FROM users WHERE user_type = 'tutor') as total_tutors,
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
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора - УчиПросто</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .admin-dashboard {
            max-width: 1400px;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 1rem;
        }
        
        .section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .section h2 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
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
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
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
                    <a href="dashboard.php">Админ-панель</a>
                    <a href="admin_support.php">Поддержка</a>
                <a href="logout.php">Выйти</a>
            </div>
        </div>
    </header>

    <div class="admin-dashboard">
        <div class="welcome-section">
            <h1>Панель администратора</h1>
            <p>Добро пожаловать в систему управления УчиПросто</p>
        </div>

        <!-- Статистика -->
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

        <div class="dashboard-content">
            <!-- Последние сообщения поддержки -->
            <div class="section">
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
                                <a href="admin_support.php" class="btn-admin btn-primary">Управление</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Последние пользователи -->
            <div class="section">
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
        </div>
    </div>
</body>
</html>