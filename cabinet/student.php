<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ученик') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../database/db.php';
$student_user_id = $_SESSION['user_id'];

// Получаем ID ученика
$stmt = $pdo->prepare("SELECT id FROM ученики WHERE пользователь_id = ?");
$stmt->execute([$student_user_id]);
$student_record = $stmt->fetch();
if (!$student_record) {
    die("Учётная запись ученика не найдена");
}
$student_id = $student_record['id'];

// 1. Основные данные ученика
$stmt = $pdo->prepare("
    SELECT u.фио, u.email, u.телефон, уч.категория, уч.статус, уч.дата_начала, уч.дата_окончания
    FROM пользователи u
    JOIN ученики уч ON u.id = уч.пользователь_id
    WHERE u.id = ?
");
$stmt->execute([$student_user_id]);
$student = $stmt->fetch();

// 2. Прогресс обучения (все занятия ученика: и групповые, и индивидуальные)
$stmt = $pdo->prepare("
    SELECT 
        з.тип,
        COUNT(з.id) as всего,
        COUNT(п.id) as пройдено
    FROM занятия з
    LEFT JOIN группа_ученики гу ON з.группа_id = гу.группа_id
    LEFT JOIN посещения п ON з.id = п.занятие_id AND (п.ученик_id = гу.ученик_id OR п.ученик_id = з.ученик_id)
    WHERE (з.группа_id IS NOT NULL AND гу.ученик_id = ?) 
       OR (з.ученик_id = ?)
    GROUP BY з.тип
");
$stmt->execute([$student_id, $student_id]);
$progress = [];
while ($row = $stmt->fetch()) {
    $progress[$row['тип']] = [
        'всего' => (int)$row['всего'],
        'пройдено' => (int)$row['пройдено'],
        'процент' => $row['всего'] > 0 ? round($row['пройдено'] / $row['всего'] * 100) : 0
    ];
}

$theory = $progress['теория'] ?? ['всего' => 0, 'пройдено' => 0, 'процент' => 0];
$practice = $progress['практика'] ?? ['всего' => 0, 'пройдено' => 0, 'процент' => 0];

// 3. Расписание (только будущие занятия со статусом 'запланировано')
$stmt = $pdo->prepare("
    SELECT 
        з.*,
        г.название as группа_название,
        u2.фио as инструктор_фио,
        CASE 
            WHEN з.группа_id IS NOT NULL THEN CONCAT('Группа: ', г.название)
            WHEN з.ученик_id IS NOT NULL THEN 'Индивидуальное занятие'
        END as формат_занятия
    FROM занятия з
    LEFT JOIN группы г ON з.группа_id = г.id
    LEFT JOIN инструкторы ин ON г.инструктор_id = ин.id OR з.инструктор_id = ин.id
    LEFT JOIN пользователи u2 ON ин.пользователь_id = u2.id
    WHERE ((з.группа_id IS NOT NULL AND з.группа_id IN (SELECT группа_id FROM группа_ученики WHERE ученик_id = ?))
           OR (з.ученик_id = ?))
      AND з.статус = 'запланировано'
      AND (з.дата_занятия > CURDATE() 
           OR (з.дата_занятия = CURDATE() AND з.время_начала > CURTIME()))
    ORDER BY з.дата_занятия, з.время_начала
    LIMIT 10
");
$stmt->execute([$student_id, $student_id]);
$lessons = $stmt->fetchAll();

// 4. Статус оплаты
$stmt = $pdo->prepare("
    SELECT дата_окончания_оплаты, оплачено
    FROM статус_оплаты
    WHERE ученик_id = ?
    ORDER BY дата_окончания_оплаты DESC
    LIMIT 1
");
$stmt->execute([$student_id]);
$payment = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет ученика</title>
    <link rel="stylesheet" href="/CSS/main.css">
    <style>
        .cabinet-header { text-align: center; padding: 40px 0; }
        .student-info { display: flex; justify-content: space-around; flex-wrap: wrap; gap: 20px; margin: 30px 0; }
        .info-card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); min-width: 200px; flex: 1 1 250px; }
        .info-card h3 { color: #00334d; margin-bottom: 12px; }
        .progress-bar { height: 10px; background: #e0e0e0; border-radius: 5px; margin: 10px 0; overflow: hidden; }
        .progress-fill { height: 100%; background: #0897da; }
        .lessons-list { margin: 40px 0; }
        .lesson-item { background: #f9f9f9; padding: 16px; border-left: 4px solid #0897da; margin-bottom: 12px; border-radius: 0 8px 8px 0; }
        .lesson-date { font-weight: bold; color: #00334d; }
        .payment-status { padding: 12px; border-radius: 8px; margin: 20px 0; text-align: center; font-weight: 600; }
        .logout-btn { display: inline-block; background: #0897da; color: white; padding: 10px 20px; border-radius: 30px; text-decoration: none; margin-top: 20px; transition: background 0.3s; }
        .logout-btn:hover { background: #0066b2; }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php require_once __DIR__ . '/../blocks/header.php'; ?>

        <div class="container">
            <div class="cabinet-header">
                <h1>Личный кабинет ученика</h1>
                <p>Здравствуйте, <?= htmlspecialchars($student['фио']) ?>!</p>
            </div>

            <!-- Основная информация -->
            <div class="student-info">
                <div class="info-card">
                    <h3>Категория</h3>
                    <p><?= htmlspecialchars($student['категория']) ?></p>
                </div>
                <div class="info-card">
                    <h3>Статус</h3>
                    <p><?= htmlspecialchars($student['статус']) ?></p>
                </div>
                <div class="info-card">
                    <h3>Начало курса</h3>
                    <p><?= $student['дата_начала'] ?></p>
                </div>
            </div>

            <!-- Прогресс -->
            <div class="info-card" style="text-align: left;">
                <h3>Прогресс обучения</h3>
                
                <p>Теория: <strong><?= $theory['пройдено'] ?> из <?= $theory['всего'] ?></strong> занятий</p>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $theory['процент'] ?>%;"></div>
                </div>
                
                <p>Практика: <strong><?= $practice['пройдено'] ?> из <?= $practice['всего'] ?></strong> занятий</p>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $practice['процент'] ?>%;"></div>
                </div>
            </div>

            <!-- Статус оплаты -->
            <div class="payment-status" style="background: <?= $payment && $payment['оплачено'] ? '#e8f5e9' : '#ffebee' ?>; color: <?= $payment && $payment['оплачено'] ? '#2e7d32' : '#c62828' ?>;">
                <?php if ($payment): ?>
                    <?php if ($payment['оплачено']): ?>
                        Оплата действительна до: <?= $payment['дата_окончания_оплаты'] ?>
                    <?php else: ?>
                        ❗ Оплата просрочена! Пожалуйста, оплатите курс.
                    <?php endif; ?>
                <?php else: ?>
                    ❗ Информация об оплате не найдена.
                <?php endif; ?>
            </div>

            <!-- Расписание -->
            <div class="lessons-list">
                <h2>Ближайшие занятия</h2>
                <?php if ($lessons): ?>
                    <?php foreach ($lessons as $lesson): ?>
                        <div class="lesson-item">
                            <div class="lesson-date">
                                <?= date('d.m.Y', strtotime($lesson['дата_занятия'])) ?> 
                                в <?= $lesson['время_начала'] ?>
                            </div>
                            <div><strong><?= htmlspecialchars($lesson['тип']) ?>:</strong> <?= htmlspecialchars($lesson['тема']) ?></div>
                            <div><?= htmlspecialchars($lesson['формат_занятия']) ?></div>
                            <div>Инструктор: <?= htmlspecialchars($lesson['инструктор_фио']) ?></div>
                            <div>Место: <?= htmlspecialchars($lesson['место']) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Нет запланированных занятий. Дождитесь, пока инструктор назначит занятие.</p>
                <?php endif; ?>
            </div>

            <a href="/logout.php" class="logout-btn">Выйти</a>
        </div>

        <?php require_once __DIR__ . '/../blocks/footer.php'; ?>
    </div>
</body>
</html>