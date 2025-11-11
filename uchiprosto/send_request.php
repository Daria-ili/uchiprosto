<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tutor_id'])) {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    // Получаем ID студента
    $studentQuery = "SELECT id FROM students WHERE user_id = ?";
    $studentStmt = $db->prepare($studentQuery);
    $studentStmt->execute([$_SESSION['user_id']]);
    $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($student) {
        $query = "INSERT INTO contacts (tutor_id, student_id, message, status) VALUES (?, ?, ?, 'pending')";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $_POST['tutor_id'],
            $student['id'],
            $_POST['message'] ?? ''
        ]);
        
        $_SESSION['success'] = 'Запрос отправлен репетитору';
    }
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'tutors.php'));
exit;
?>