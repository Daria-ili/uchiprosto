<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Валидация данных
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Пожалуйста, введите ваше имя';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Пожалуйста, введите корректный email';
    }
    
    if (empty($subject)) {
        $errors[] = 'Пожалуйста, укажите тему вопроса';
    }
    
    if (empty($message)) {
        $errors[] = 'Пожалуйста, напишите ваше сообщение';
    }
    
    // Если есть ошибки, показываем их
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
        header('Location: help.php');
        exit;
    }
    
    try {
        // Сохраняем в базу данных
        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        // Сохраняем сообщение
        $query = "INSERT INTO support_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$name, $email, $subject, $message]);
        
        // Пытаемся отправить email
        $to = 'ilusonokdara@gmail.com';
        $email_subject = "Вопрос в поддержку УчиПросто: " . $subject;
        
        $email_body = "
        Новое сообщение из формы поддержки УчиПросто:
        
        Имя: $name
        Email: $email
        Тема: $subject
        
        Сообщение:
        $message
        
        ---
        Это сообщение отправлено через форму поддержки на сайте УчиПросто.
        Дата: " . date('d.m.Y H:i') . "
        ";
        
        $headers = "From: noreply@uchiprosto.ru\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
        
        // Пытаемся отправить email
        $email_sent = @mail($to, $email_subject, $email_body, $headers);
        
        if ($email_sent) {
            $_SESSION['success'] = 'Ваше сообщение успешно отправлено! Мы ответим вам в течение 24 часов на указанный email.';
        } else {
            $_SESSION['success'] = 'Сообщение сохранено! Мы свяжемся с вами в ближайшее время. (Email временно недоступен)';
        }
        
    } catch (Exception $e) {
        // Если произошла ошибка с БД
        error_log("Ошибка поддержки: " . $e->getMessage());
        $_SESSION['error'] = 'Произошла ошибка при отправке сообщения. Пожалуйста, напишите нам напрямую на ilusonokdara@gmail.com';
    }
    
    header('Location: help.php');
    exit;
} else {
    // Если обращение не через POST, перенаправляем на страницу помощи
    header('Location: help.php');
    exit;
}