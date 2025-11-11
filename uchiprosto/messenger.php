<?php 
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// Получаем список контактов
if ($_SESSION['user_type'] == 'tutor') {
    $contactsQuery = "SELECT c.*, u.first_name, u.last_name, u.patronymic, s.grade 
                     FROM contacts c 
                     JOIN students s ON c.student_id = s.id 
                     JOIN users u ON s.user_id = u.id 
                     WHERE c.tutor_id = ? AND c.status = 'approved' 
                     ORDER BY c.created_at DESC";
    $contactsStmt = $db->prepare($contactsQuery);
    $contactsStmt->execute([$_SESSION['user_id']]);
} else {
    $contactsQuery = "SELECT c.*, u.first_name, u.last_name, u.patronymic, t.subjects 
                     FROM contacts c 
                     JOIN tutors t ON c.tutor_id = t.id 
                     JOIN users u ON t.user_id = u.id 
                     WHERE c.student_id = ? AND c.status = 'approved' 
                     ORDER BY c.created_at DESC";
    $studentQuery = "SELECT id FROM students WHERE user_id = ?";
    $studentStmt = $db->prepare($studentQuery);
    $studentStmt->execute([$_SESSION['user_id']]);
    $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
    $contactsStmt = $db->prepare($contactsQuery);
    $contactsStmt->execute([$student['id']]);
}

$contacts = $contactsStmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем сообщения для выбранного контакта
$selected_contact = null;
$messages = [];

if (isset($_GET['contact_id'])) {
    $contact_id = $_GET['contact_id'];
    
    // Проверяем доступ к контакту
    $accessQuery = "SELECT c.* FROM contacts c ";
    if ($_SESSION['user_type'] == 'tutor') {
        $accessQuery .= "WHERE c.id = ? AND c.tutor_id = ?";
        $accessStmt = $db->prepare($accessQuery);
        $accessStmt->execute([$contact_id, $_SESSION['user_id']]);
    } else {
        $accessQuery .= "WHERE c.id = ? AND c.student_id = ?";
        $accessStmt = $db->prepare($accessQuery);
        $accessStmt->execute([$contact_id, $student['id']]);
    }
    
    $selected_contact = $accessStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($selected_contact) {
        // Получаем сообщения
        $messagesQuery = "SELECT m.*, u.first_name, u.last_name 
                         FROM messages m 
                         JOIN users u ON m.sender_id = u.id 
                         WHERE m.contact_id = ? 
                         ORDER BY m.created_at ASC";
        $messagesStmt = $db->prepare($messagesQuery);
        $messagesStmt->execute([$contact_id]);
        $messages = $messagesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Помечаем сообщения как прочитанные
        $updateQuery = "UPDATE messages SET is_read = 1 WHERE contact_id = ? AND sender_id != ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([$contact_id, $_SESSION['user_id']]);
    }
}

// Отправка сообщения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message']) && $selected_contact) {
    $message_text = trim($_POST['message_text']);
    if (!empty($message_text)) {
        $insertQuery = "INSERT INTO messages (contact_id, sender_id, message_text) VALUES (?, ?, ?)";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->execute([$selected_contact['id'], $_SESSION['user_id'], $message_text]);
        
        header('Location: messenger.php?contact_id=' . $selected_contact['id']);
        exit;
    }
}
?>

<div class="container">
    <div class="messenger">
        <h1>Мессенджер</h1>
        
        <div class="messenger-container">
            <div class="contacts-sidebar">
                <h3>Мои контакты</h3>
                <div class="contacts-list">
                    <?php if (empty($contacts)): ?>
                        <p class="no-contacts">Нет активных контактов</p>
                    <?php else: ?>
                        <?php foreach ($contacts as $contact): ?>
                            <a href="messenger.php?contact_id=<?php echo $contact['id']; ?>" 
                               class="contact-item <?php echo ($selected_contact && $selected_contact['id'] == $contact['id']) ? 'active' : ''; ?>">
                                <div class="contact-info">
                                    <h4>
                                        <?php if ($_SESSION['user_type'] == 'tutor'): ?>
                                            <?php echo $contact['first_name'] . ' ' . $contact['last_name']; ?>
                                        <?php else: ?>
                                            <?php echo $contact['first_name'] . ' ' . $contact['last_name'] . ' ' . $contact['patronymic']; ?>
                                        <?php endif; ?>
                                    </h4>
                                    <p>
                                        <?php if ($_SESSION['user_type'] == 'tutor'): ?>
                                            <?php echo $contact['grade']; ?>
                                        <?php else: ?>
                                            <?php echo $contact['subjects']; ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="chat-area">
                <?php if ($selected_contact): ?>
                    <div class="chat-header">
                        <h3>
                            <?php if ($_SESSION['user_type'] == 'tutor'): ?>
                                <?php echo $selected_contact['first_name'] . ' ' . $selected_contact['last_name']; ?>
                                <span class="contact-grade">(<?php echo $selected_contact['grade']; ?>)</span>
                            <?php else: ?>
                                <?php 
                                $tutorQuery = "SELECT u.first_name, u.last_name, u.patronymic, t.subjects 
                                             FROM tutors t 
                                             JOIN users u ON t.user_id = u.id 
                                             WHERE t.id = ?";
                                $tutorStmt = $db->prepare($tutorQuery);
                                $tutorStmt->execute([$selected_contact['tutor_id']]);
                                $tutor = $tutorStmt->fetch(PDO::FETCH_ASSOC);
                                echo $tutor['last_name'] . ' ' . $tutor['first_name'] . ' ' . $tutor['patronymic'];
                                ?>
                            <?php endif; ?>
                        </h3>
                    </div>

                    <div class="messages-container" id="messages-container">
                        <?php if (empty($messages)): ?>
                            <p class="no-messages">Нет сообщений. Начните общение!</p>
                        <?php else: ?>
                            <?php foreach ($messages as $message): ?>
                                <div class="message <?php echo $message['sender_id'] == $_SESSION['user_id'] ? 'outgoing' : 'incoming'; ?>">
                                    <div class="message-content">
                                        <p><?php echo htmlspecialchars($message['message_text']); ?></p>
                                        <span class="message-time">
                                            <?php echo date('H:i', strtotime($message['created_at'])); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <form method="POST" class="message-form">
                        <input type="hidden" name="send_message" value="1">
                        <div class="message-input-container">
                            <textarea name="message_text" placeholder="Введите сообщение..." required></textarea>
                            <button type="submit" class="btn">Отправить</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="no-chat-selected">
                        <p>Выберите контакт для начала общения</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const messagesContainer = document.getElementById('messages-container');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    // Автообновление сообщений каждые 5 секунд
    setInterval(function() {
        if (<?php echo $selected_contact ? 'true' : 'false'; ?>) {
            location.reload();
        }
    }, 5000);
});
</script>

<?php include 'includes/footer.php'; ?>