<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'tutor') {
    header('Location: login.php');
    exit;
}

if (isset($_GET['contact_id']) && isset($_GET['action'])) {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    // Проверяем, принадлежит ли заявка этому репетитору
    $checkQuery = "SELECT id FROM contacts WHERE id = ? AND tutor_id IN (SELECT id FROM tutors WHERE user_id = ?)";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$_GET['contact_id'], $_SESSION['user_id']]);
    
    if ($checkStmt->rowCount() > 0) {
        $status = ($_GET['action'] == 'approve') ? 'approved' : 'rejected';
        
        $updateQuery = "UPDATE contacts SET status = ? WHERE id = ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([$status, $_GET['contact_id']]);
        
        $_SESSION['success'] = 'Заявка ' . ($status == 'approved' ? 'одобрена' : 'отклонена');
    }
}

header('Location: dashboard.php');
exit;
?>