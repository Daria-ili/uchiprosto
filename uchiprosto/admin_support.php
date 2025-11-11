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

// Обработка изменения статуса сообщения
if (isset($_POST['update_status'])) {
    $message_id = $_POST['message_id'];
    $new_status = $_POST['status'];
    $admin_notes = $_POST['admin_notes'] ?? '';
    
    $updateQuery = "UPDATE support_messages SET status = ?, admin_notes = ? WHERE id = ?";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->execute([$new_status, $admin_notes, $message_id]);
    
    $_SESSION['success'] = 'Статус сообщения обновлен';
    header('Location: admin_support.php');
    exit;
}

// Обработка удаления сообщения
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    $deleteQuery = "DELETE FROM support_messages WHERE id = ?";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->execute([$delete_id]);
    
    $_SESSION['success'] = 'Сообщение удалено';
    header('Location: admin_support.php');
    exit;
}

// Обработка массовых действий
if (isset($_POST['bulk_action_submit']) && isset($_POST['bulk_action'])) {
    $action = $_POST['bulk_action'];
    $selected_messages = $_POST['selected_messages'] ?? [];
    
    if (!empty($selected_messages) && !empty($action)) {
        $placeholders = str_repeat('?,', count($selected_messages) - 1) . '?';
        
        if ($action == 'delete') {
            $bulkQuery = "DELETE FROM support_messages WHERE id IN ($placeholders)";
            $bulkStmt = $db->prepare($bulkQuery);
            $bulkStmt->execute($selected_messages);
            $_SESSION['success'] = 'Выбранные сообщения удалены';
        } elseif (in_array($action, ['new', 'in_progress', 'resolved'])) {
            $bulkQuery = "UPDATE support_messages SET status = ? WHERE id IN ($placeholders)";
            $bulkStmt = $db->prepare($bulkQuery);
            $bulkStmt->execute(array_merge([$action], $selected_messages));
            $_SESSION['success'] = 'Статус выбранных сообщений обновлен';
        }
    } else {
        $_SESSION['error'] = 'Выберите сообщения и действие';
    }
    
    header('Location: admin_support.php');
    exit;
}

// Получаем фильтры
$status_filter = $_GET['status'] ?? 'all';
$search_query = $_GET['search'] ?? '';

// Базовый запрос для сообщений
$query = "SELECT * FROM support_messages WHERE 1=1";
$params = [];

// Применяем фильтр по статусу
if ($status_filter != 'all') {
    $query .= " AND status = ?";
    $params[] = $status_filter;
}

// Применяем поиск
if (!empty($search_query)) {
    $query .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $search_param = "%$search_query%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

$query .= " ORDER BY created_at DESC";

// Подготовка и выполнение запроса
$stmt = $db->prepare($query);
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Статистика для фильтров
$statsQuery = "
    SELECT 
        status,
        COUNT(*) as count
    FROM support_messages 
    GROUP BY status
";
$statsStmt = $db->prepare($statsQuery);
$statsStmt->execute();
$status_stats = $statsStmt->fetchAll(PDO::FETCH_ASSOC);

// Создаем массив статистики
$stats = [
    'all' => 0,
    'new' => 0,
    'in_progress' => 0,
    'resolved' => 0
];

foreach ($status_stats as $stat) {
    $stats[$stat['status']] = $stat['count'];
    $stats['all'] += $stat['count'];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление поддержкой - УчиПросто</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-support {
            max-width: 1200px;
            margin: 100px auto 2rem;
            padding: 0 1rem;
        }
        
        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .filters-section {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .filters-row {
            display: flex;
            gap: 1rem;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .status-filters {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        
        .status-filter {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .status-filter.all {
            background: #667eea;
            color: white;
        }
        
        .status-filter.new {
            background: #ffeaa7;
            color: #e17055;
        }
        
        .status-filter.in_progress {
            background: #81ecec;
            color: #00cec9;
        }
        
        .status-filter.resolved {
            background: #55efc4;
            color: #00b894;
        }
        
        .bulk-actions {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .messages-list {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .message-item {
            border-bottom: 1px solid #e1e5e9;
            padding: 1.5rem;
        }
        
        .message-item:last-child {
            border-bottom: none;
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
            gap: 0.8rem;
            margin-top: 1rem;
            flex-wrap: wrap;
            align-items: flex-start;
        }
        
        /* Стили для красивых кнопок */
        .btn {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #218838 0%, #1e9e8a 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, #c82333 0%, #d91a7a 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
        }
        
        .btn-outline {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
            box-shadow: 0 2px 10px rgba(102, 126, 234, 0.1);
        }
        
        .btn-outline:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .compact-btn {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
            border-radius: 8px;
        }
        
        .form-group {
            margin-bottom: 0.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.25rem;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #e1e5e9;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 60px;
        }
        
        .admin-notes {
            background: #f8f9fa;
            padding: 0.75rem;
            border-radius: 5px;
            margin-top: 0.5rem;
            font-size: 0.85rem;
        }
        
        .no-messages {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .message-content {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin: 0.5rem 0;
            line-height: 1.5;
        }
        
        .action-form {
            display: flex;
            gap: 0.8rem;
            align-items: flex-start;
            flex-wrap: wrap;
        }
        
        .compact-select {
            padding: 0.5rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 0.85rem;
            width: 130px;
            background: white;
            transition: all 0.3s ease;
        }
        
        .compact-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .compact-textarea {
            min-height: 60px;
            padding: 0.6rem;
            font-size: 0.85rem;
            width: 220px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            resize: vertical;
            transition: all 0.3s ease;
        }
        
        .compact-textarea:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        /* Стили для массовых действий */
        .bulk-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .bulk-btn:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        /* Анимации */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .message-item {
            animation: fadeIn 0.5s ease-out;
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
                    <a href="dashboard.php" class="admin-btn">Админ-панель</a>
                    <a href="admin_support.php" class="admin-btn">Поддержка</a>
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

    <div class="admin-support">
        <div class="page-header">
            <h1>Управление поддержкой</h1>
            <p>Всего сообщений: <?php echo $stats['all']; ?> (Новых: <?php echo $stats['new']; ?>, В работе: <?php echo $stats['in_progress']; ?>, Решено: <?php echo $stats['resolved']; ?>)</p>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Фильтры и поиск -->
        <div class="filters-section">
            <form method="GET" class="filters-row">
                <div class="filter-group">
                    <label>Поиск по сообщениям</label>
                    <input type="text" name="search" class="form-control" placeholder="Поиск по имени, email, теме..." value="<?php echo htmlspecialchars($search_query); ?>">
                </div>
                <div class="filter-group">
                    <label>Статус</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>Все статусы</option>
                        <option value="new" <?php echo $status_filter == 'new' ? 'selected' : ''; ?>>Новые</option>
                        <option value="in_progress" <?php echo $status_filter == 'in_progress' ? 'selected' : ''; ?>>В работе</option>
                        <option value="resolved" <?php echo $status_filter == 'resolved' ? 'selected' : ''; ?>>Решено</option>
                    </select>
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">Применить</button>
                    <a href="admin_support.php" class="btn btn-outline">Сбросить</a>
                </div>
            </form>

            <!-- Быстрые фильтры по статусу -->
            <div class="status-filters">
                <a href="?status=all" class="status-filter all">Все (<?php echo $stats['all']; ?>)</a>
                <a href="?status=new" class="status-filter new">Новые (<?php echo $stats['new']; ?>)</a>
                <a href="?status=in_progress" class="status-filter in_progress">В работе (<?php echo $stats['in_progress']; ?>)</a>
                <a href="?status=resolved" class="status-filter resolved">Решено (<?php echo $stats['resolved']; ?>)</a>
            </div>
        </div>

        <!-- Список сообщений -->
        <div class="messages-list">
            <?php if (empty($messages)): ?>
                <div class="no-messages">
                    <h3>Сообщения не найдены</h3>
                    <p>Попробуйте изменить параметры фильтрации</p>
                </div>
            <?php else: ?>
                <form method="POST" id="messages-form">
                    <!-- Массовые действия -->
                    <div class="bulk-actions">
                        <div class="checkbox-item">
                            <input type="checkbox" id="select-all" onchange="toggleSelectAll(this)">
                            <label for="select-all">Выбрать все</label>
                        </div>
                        <select name="bulk_action" class="form-control" style="width: auto;">
                            <option value="">Массовые действия</option>
                            <option value="new">Отметить как "Новые"</option>
                            <option value="in_progress">Отметить как "В работе"</option>
                            <option value="resolved">Отметить как "Решено"</option>
                            <option value="delete">Удалить выбранные</option>
                        </select>
                        <button type="submit" name="bulk_action_submit" class="bulk-btn">
                            <i class="fas fa-play-circle"></i> Применить к выбранным
                        </button>
                    </div>

                    <?php foreach ($messages as $message): ?>
                        <div class="message-item">
                            <div class="checkbox-item">
                                <input type="checkbox" name="selected_messages[]" value="<?php echo $message['id']; ?>" class="message-checkbox">
                                <label>Выбрать</label>
                            </div>
                            
                            <div class="message-header">
                                <div>
                                    <h3 style="margin: 0 0 0.5rem 0;"><?php echo htmlspecialchars($message['subject']); ?></h3>
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
                            
                            <div class="message-content">
                                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                            </div>

                            <?php if (!empty($message['admin_notes'])): ?>
                                <div class="admin-notes">
                                    <strong>Заметки администратора:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($message['admin_notes'])); ?>
                                </div>
                            <?php endif; ?>

                            <div class="admin-actions">
                                <form method="POST" class="action-form">
                                    <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                    
                                    <div class="form-group">
                                        <label>Статус</label>
                                        <select name="status" class="compact-select">
                                            <option value="new" <?php echo $message['status'] == 'new' ? 'selected' : ''; ?>>Новое</option>
                                            <option value="in_progress" <?php echo $message['status'] == 'in_progress' ? 'selected' : ''; ?>>В работе</option>
                                            <option value="resolved" <?php echo $message['status'] == 'resolved' ? 'selected' : ''; ?>>Решено</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Заметки</label>
                                        <textarea name="admin_notes" class="compact-textarea" placeholder="Заметки..."><?php echo htmlspecialchars($message['admin_notes'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group" style="align-self: flex-end;">
                                        <button type="submit" name="update_status" class="btn btn-success compact-btn">
                                            <i class="fas fa-save"></i> Сохранить
                                        </button>
                                    </div>
                                </form>
                                
                                <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: <?php echo htmlspecialchars($message['subject']); ?>" class="btn btn-primary compact-btn">
                                    <i class="fas fa-reply"></i> Ответить
                                </a>
                                
                                <a href="admin_support.php?delete_id=<?php echo $message['id']; ?>" class="btn btn-danger compact-btn" onclick="return confirm('Удалить это сообщение?')">
                                    <i class="fas fa-trash"></i> Удалить
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function toggleSelectAll(checkbox) {
        const checkboxes = document.querySelectorAll('.message-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
    }
    </script>
</body>
</html>