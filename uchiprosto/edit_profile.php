<?php 
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'tutor') {
    header('Location: login.php');
    exit;
}

require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// Получаем данные репетитора
$query = "SELECT t.*, u.first_name, u.last_name, u.patronymic, u.phone, u.avatar 
          FROM tutors t 
          JOIN users u ON t.user_id = u.id 
          WHERE u.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$tutor = $stmt->fetch(PDO::FETCH_ASSOC);

// Получаем расписание
$scheduleQuery = "SELECT day_of_week, time_slot, is_available 
                  FROM schedule 
                  WHERE tutor_id = ? 
                  ORDER BY FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), time_slot";
$scheduleStmt = $db->prepare($scheduleQuery);
$scheduleStmt->execute([$tutor['id']]);
$schedule = $scheduleStmt->fetchAll(PDO::FETCH_ASSOC);

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Обновление профиля
        $updateUserQuery = "UPDATE users SET first_name = ?, last_name = ?, patronymic = ?, phone = ? WHERE id = ?";
        $userStmt = $db->prepare($updateUserQuery);
        $userStmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['patronymic'],
            $_POST['phone'],
            $_SESSION['user_id']
        ]);

        $updateTutorQuery = "UPDATE tutors SET subjects = ?, experience = ?, price_per_hour = ?, about_me = ?, teaching_approach = ?, is_accepting_students = ? WHERE user_id = ?";
        $tutorStmt = $db->prepare($updateTutorQuery);
        $tutorStmt->execute([
            $_POST['subjects'],
            $_POST['experience'],
            $_POST['price_per_hour'],
            $_POST['about_me'],
            $_POST['teaching_approach'],
            isset($_POST['is_accepting_students']) ? 1 : 0,
            $_SESSION['user_id']
        ]);

        $_SESSION['success'] = 'Профиль успешно обновлен';
        header('Location: edit_profile.php');
        exit;
    }

    if (isset($_POST['update_schedule'])) {
        // Обновление расписания
        $deleteQuery = "DELETE FROM schedule WHERE tutor_id = ?";
        $deleteStmt = $db->prepare($deleteQuery);
        $deleteStmt->execute([$tutor['id']]);

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $time_slots = ['15:00-16:00', '16:00-17:00', '17:00-18:00', '18:00-19:00'];

        foreach ($days as $day) {
            if (isset($_POST['days']) && in_array($day, $_POST['days'])) {
                foreach ($time_slots as $time_slot) {
                    $is_available = isset($_POST['schedule'][$day][$time_slot]) ? 1 : 0;
                    $insertQuery = "INSERT INTO schedule (tutor_id, day_of_week, time_slot, is_available) VALUES (?, ?, ?, ?)";
                    $insertStmt = $db->prepare($insertQuery);
                    $insertStmt->execute([$tutor['id'], $day, $time_slot, $is_available]);
                }
            }
        }

        $_SESSION['success'] = 'Расписание успешно обновлено';
        header('Location: edit_profile.php');
        exit;
    }
}
?>

<div class="container">
    <div class="edit-profile">
        <h1>Редактирование профиля</h1>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form method="POST" class="profile-form">
            <input type="hidden" name="update_profile" value="1">
            
            <div class="form-section">
                <h2>Основная информация</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>Фамилия</label>
                        <input type="text" name="last_name" value="<?php echo $tutor['last_name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Имя</label>
                        <input type="text" name="first_name" value="<?php echo $tutor['first_name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Отчество</label>
                        <input type="text" name="patronymic" value="<?php echo $tutor['patronymic']; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Телефон</label>
                        <input type="tel" name="phone" value="<?php echo $tutor['phone']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Предметы</label>
                        <input type="text" name="subjects" value="<?php echo $tutor['subjects']; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Стаж (лет)</label>
                        <input type="number" name="experience" value="<?php echo $tutor['experience']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Цена за час (руб.)</label>
                        <input type="number" name="price_per_hour" value="<?php echo $tutor['price_per_hour']; ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>О себе</h2>
                <div class="form-group">
                    <label>О себе</label>
                    <textarea name="about_me" rows="4"><?php echo $tutor['about_me']; ?></textarea>
                </div>
                <div class="form-group">
                    <label>Мой подход к обучению</label>
                    <textarea name="teaching_approach" rows="4"><?php echo $tutor['teaching_approach']; ?></textarea>
                </div>
            </div>

            <div class="form-section">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_accepting_students" <?php echo $tutor['is_accepting_students'] ? 'checked' : ''; ?>>
                    <span>Ведется набор учеников</span>
                </label>
            </div>

            <button type="submit" class="btn">Сохранить изменения</button>
        </form>

        <form method="POST" class="schedule-form">
            <input type="hidden" name="update_schedule" value="1">
            
            <div class="form-section">
                <h2>Расписание занятий</h2>
                <p>Выберите дни недели и отметьте свободные временные slots</p>
                
                <div class="days-selection">
                    <label class="checkbox-label">
                        <input type="checkbox" name="days[]" value="monday" <?php echo in_array('monday', array_column($schedule, 'day_of_week')) ? 'checked' : ''; ?>>
                        <span>Понедельник</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="days[]" value="tuesday" <?php echo in_array('tuesday', array_column($schedule, 'day_of_week')) ? 'checked' : ''; ?>>
                        <span>Вторник</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="days[]" value="wednesday" <?php echo in_array('wednesday', array_column($schedule, 'day_of_week')) ? 'checked' : ''; ?>>
                        <span>Среда</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="days[]" value="thursday" <?php echo in_array('thursday', array_column($schedule, 'day_of_week')) ? 'checked' : ''; ?>>
                        <span>Четверг</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="days[]" value="friday" <?php echo in_array('friday', array_column($schedule, 'day_of_week')) ? 'checked' : ''; ?>>
                        <span>Пятница</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="days[]" value="saturday" <?php echo in_array('saturday', array_column($schedule, 'day_of_week')) ? 'checked' : ''; ?>>
                        <span>Суббота</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="days[]" value="sunday" <?php echo in_array('sunday', array_column($schedule, 'day_of_week')) ? 'checked' : ''; ?>>
                        <span>Воскресенье</span>
                    </label>
                </div>

                <div class="schedule-grid">
                    <?php
                    $time_slots = ['15:00-16:00', '16:00-17:00', '17:00-18:00', '18:00-19:00'];
                    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                    
                    echo '<div class="time-column">';
                    echo '<div class="time-header">Время</div>';
                    foreach ($time_slots as $time_slot) {
                        echo '<div class="time-slot">' . $time_slot . '</div>';
                    }
                    echo '</div>';
                    
                    foreach ($days as $day) {
                        echo '<div class="day-column" id="' . $day . '-column" style="display: ' . (in_array($day, array_column($schedule, 'day_of_week')) ? 'block' : 'none') . '">';
                        echo '<div class="day-header">' . getDayName($day) . '</div>';
                        foreach ($time_slots as $time_slot) {
                            $is_available = false;
                            foreach ($schedule as $slot) {
                                if ($slot['day_of_week'] == $day && $slot['time_slot'] == $time_slot) {
                                    $is_available = $slot['is_available'];
                                    break;
                                }
                            }
                            echo '<label class="schedule-slot">';
                            echo '<input type="checkbox" name="schedule[' . $day . '][' . $time_slot . ']" ' . ($is_available ? 'checked' : '') . '>';
                            echo '<span class="checkmark"></span>';
                            echo '</label>';
                        }
                        echo '</div>';
                    }
                    
                    function getDayName($day) {
                        $days = [
                            'monday' => 'Пн',
                            'tuesday' => 'Вт',
                            'wednesday' => 'Ср',
                            'thursday' => 'Чт',
                            'friday' => 'Пт',
                            'saturday' => 'Сб',
                            'sunday' => 'Вс'
                        ];
                        return $days[$day];
                    }
                    ?>
                </div>
            </div>

            <button type="submit" class="btn">Сохранить расписание</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dayCheckboxes = document.querySelectorAll('input[name="days[]"]');
    
    dayCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const dayColumn = document.getElementById(this.value + '-column');
            if (dayColumn) {
                dayColumn.style.display = this.checked ? 'block' : 'none';
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>