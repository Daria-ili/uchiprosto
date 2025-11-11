// Основные скрипты для сайта
document.addEventListener('DOMContentLoaded', function() {
    // Переключение между полями ученика и репетитора при регистрации
    const userTypeRadios = document.querySelectorAll('input[name="user_type"]');
    const studentFields = document.getElementById('student-fields');
    const tutorFields = document.getElementById('tutor-fields');
    
    if (userTypeRadios && studentFields && tutorFields) {
        userTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'student') {
                    studentFields.style.display = 'block';
                    tutorFields.style.display = 'none';
                } else {
                    studentFields.style.display = 'none';
                    tutorFields.style.display = 'block';
                }
            });
        });
    }

    // Плавная прокрутка для якорей
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Автоматическое скрытие alert сообщений
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });

    // Подтверждение действий
    const confirmLinks = document.querySelectorAll('a[data-confirm]');
    confirmLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm(this.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });
});

// Функция для фильтрации репетиторов
function filterTutors() {
    const subject = document.getElementById('subject-filter').value.toLowerCase();
    const maxPrice = document.getElementById('price-filter').value;
    const cards = document.querySelectorAll('.tutor-card');
    
    cards.forEach(card => {
        const cardSubject = card.getAttribute('data-subject').toLowerCase();
        const cardPrice = parseInt(card.getAttribute('data-price'));
        
        const subjectMatch = !subject || cardSubject.includes(subject);
        const priceMatch = !maxPrice || cardPrice <= parseInt(maxPrice);
        
        if (subjectMatch && priceMatch) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}