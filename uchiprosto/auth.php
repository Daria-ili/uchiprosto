<?php
session_start();
require_once 'config/database.php';

if ($_POST['action'] == 'register') {
    // Регистрация нового пользователя
    $database = new Database();
    $db = $database->getConnection();
    
    // Проверяем, нет ли уже пользователя с таким email
    $checkQuery = "SELECT id FROM users WHERE email = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$_POST['email']]);
    
    if ($checkStmt->rowCount() > 0) {
        $_SESSION['error'] = 'Пользователь с таким email уже существует';
        header('Location: register.php');
        exit;
    }
    
    // Создаем пользователя
    $query = "INSERT INTO users (email, password, user_type, first_name, last_name, patronymic, phone) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    
    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $success = $stmt->execute([
        $_POST['email'],
        $hashed_password,
        $_POST['user_type'],
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['patronymic'] ?? '',
        $_POST['phone'] ?? ''
    ]);
    
    if ($success) {
        $user_id = $db->lastInsertId();
        
        // Создаем запись в соответствующей таблице
        if ($_POST['user_type'] == 'student') {
            $studentQuery = "INSERT INTO students (user_id, grade, study_goals, parent_phone) 
                            VALUES (?, ?, ?, ?)";
            $studentStmt = $db->prepare($studentQuery);
            $studentStmt->execute([
                $user_id,
                $_POST['grade'] ?? '',
                $_POST['study_goals'] ?? '',
                $_POST['parent_phone'] ?? ''
            ]);
        } else {
            $tutorQuery = "INSERT INTO tutors (user_id, subjects, experience, price_per_hour, about_me) 
                          VALUES (?, ?, ?, ?, ?)";
            $tutorStmt = $db->prepare($tutorQuery);
            $tutorStmt->execute([
                $user_id,
                $_POST['subjects'] ?? '',
                $_POST['experience'] ?? 0,
                $_POST['price_per_hour'] ?? 0,
                $_POST['about_me'] ?? ''
            ]);
        }
        
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_type'] = $_POST['user_type'];
        $_SESSION['first_name'] = $_POST['first_name'];
        $_SESSION['last_name'] = $_POST['last_name'];
        
        header('Location: dashboard.php');
        exit;
    }
} else {
    // АВТОРИЗАЦИЯ 
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT id, password, user_type, first_name, last_name FROM users WHERE email = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_POST['email']]);
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // ДЕБАГ: посмотрим что приходит
        error_log("Email: " . $_POST['email']);
        error_log("Введенный пароль: " . $_POST['password']);
        error_log("Хэш в БД: " . $user['password']);
        
        // Проверяем пароль
        if (password_verify($_POST['password'], $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            
            // Если администратор - перенаправляем в админ-панель
            if ($user['user_type'] == 'admin') {
                header('Location: admin_dashboard.php');
            } else {
                header('Location: dashboard.php');
            }
            exit;
        } else {
            $_SESSION['error'] = 'Неверный пароль';
        }
    } else {
        $_SESSION['error'] = 'Пользователь с таким email не найден';
    }
    
    header('Location: login.php');
    exit;
}
?>