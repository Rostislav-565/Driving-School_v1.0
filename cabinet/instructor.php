<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'инструктор') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../database/db.php';
$instructor_user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id FROM инструкторы WHERE пользователь_id = ?");
$stmt->execute([$instructor_user_id]);
$instructor = $stmt->fetch();
if (!$instructor) die("Инструктор не найден");
$instructor_id = $instructor['id'];

// === Обработка форм ===

// 1. Добавление занятия
if (($_POST['action'] ?? null) === 'add_lesson') {
    $lesson_type = $_POST['lesson_mode'] ?? 'group'; // Исправлено на правильное имя
    
    // Исправление: имя радиокнопки — lesson_type
    $lesson_type = $_POST['lesson_type'] ?? 'group';

    $group_id = null;
    $student_id = null;

    if ($lesson_type === 'group') {
        $group_id = (int)($_POST['group_id'] ?? 0);
        if (!$group_id) {
            die("Ошибка: выберите группу");
        }
    } else {
        $student_id = (int)($_POST['student_id'] ?? 0);
        if (!$student_id) {
            die("Ошибка: выберите ученика");
        }
    }

    $type = $_POST['type'] ?? 'теория';
    $date = $_POST['date'] ?? '';
    $start = $_POST['start'] ?? '';
    $end = $_POST['end'] ?? '';
    $topic = trim($_POST['topic'] ?? '');
    $place = trim($_POST['place'] ?? '');

    if (!$date || !$start || !$end || !$topic) {
        die("Ошибка: заполните все поля");
    }

    $stmt = $pdo->prepare("
        INSERT INTO занятия (группа_id, ученик_id, инструктор_id, тип, дата_занятия, время_начала, время_окончания, тема, место, статус)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'запланировано')
    ");
    $stmt->execute([$group_id, $student_id, $instructor_id, $type, $date, $start, $end, $topic, $place]);

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// 2. Отметка посещения
if (($_POST['action'] ?? null) === 'mark_attendance') {
    $lesson_id = (int)($_POST['lesson_id'] ?? 0);
    $student_id = (int)($_POST['student_id'] ?? 0);
    $attended = !empty($_POST['attended']);

    if ($lesson_id && $student_id) {
        if ($attended) {
            $stmt = $pdo->prepare("
                INSERT INTO посещения (ученик_id, занятие_id, дата_посещения)
                VALUES (?, ?, CURDATE())
                ON DUPLICATE KEY UPDATE дата_посещения = CURDATE()
            ");
            $stmt->execute([$student_id, $lesson_id]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM посещения WHERE ученик_id = ? AND занятие_id = ?");
            $stmt->execute([$student_id, $lesson_id]);
        }
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// 3. Изменение статуса занятия
if (($_POST['action'] ?? null) === 'update_lesson_status') {
    $lesson_id = (int)($_POST['lesson_id'] ?? 0);
    $status = $_POST['status'] ?? 'запланировано';
    if ($lesson_id && in_array($status, ['запланировано', 'проведено', 'отменено'])) {
        $stmt = $pdo->prepare("UPDATE занятия SET статус = ? WHERE id = ? AND инструктор_id = ?");
        $stmt->execute([$status, $lesson_id, $instructor_id]);
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// === Получение данных ===

// Группы инструктора
$stmt = $pdo->prepare("SELECT id, название, категория FROM группы WHERE инструктор_id = ? ORDER BY дата_начала DESC");
$stmt->execute([$instructor_id]);
$groups = $stmt->fetchAll();

// ТОЛЬКО запланированные занятия (любая дата)
$stmt = $pdo->prepare("
    SELECT 
        з.*,
        г.название as группа_название,
        u.фио as ученик_фио
    FROM занятия з
    LEFT JOIN группы г ON з.группа_id = г.id
    LEFT JOIN ученики уч ON з.ученик_id = уч.id
    LEFT JOIN пользователи u ON уч.пользователь_id = u.id
    WHERE з.инструктор_id = ?
      AND з.статус = 'запланировано'
    ORDER BY з.дата_занятия ASC, з.время_начала ASC
");
$stmt->execute([$instructor_id]);
$lessons = $stmt->fetchAll();

// История: только проведённые и отменённые
$stmt = $pdo->prepare("
    SELECT 
        з.*,
        г.название as группа_название,
        u.фио as ученик_фио
    FROM занятия з
    LEFT JOIN группы г ON з.группа_id = г.id
    LEFT JOIN ученики уч ON з.ученик_id = уч.id
    LEFT JOIN пользователи u ON уч.пользователь_id = u.id
    WHERE з.инструктор_id = ?
      AND з.статус IN ('проведено', 'отменено')
    ORDER BY з.дата_занятия DESC, з.время_начала DESC
    LIMIT 30
");
$stmt->execute([$instructor_id]);
$history_lessons = $stmt->fetchAll();

// Все ученики инструктора (с id ученика)
$stmt = $pdo->prepare("
    SELECT 
        u.фио, 
        u.email, 
        уч.категория, 
        уч.id as ученик_id,   -- ← ДОБАВЛЕНО
        г.название as группа
    FROM группа_ученики гу
    JOIN ученики уч ON гу.ученик_id = уч.id
    JOIN пользователи u ON уч.пользователь_id = u.id
    JOIN группы г ON гу.группа_id = г.id
    WHERE г.инструктор_id = ?
    ORDER BY г.название, u.фио
");
$stmt->execute([$instructor_id]);
$students = $stmt->fetchAll();

// === ИСПРАВЛЕННАЯ СТАТИСТИКА ===
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as всего_занятий,
        COUNT(CASE WHEN з.статус = 'проведено' THEN 1 END) as проведено
    FROM занятия з
    JOIN группы г ON з.группа_id = г.id
    WHERE г.инструктор_id = ?
");
$stmt->execute([$instructor_id]);
$stats = $stmt->fetch();

// Расчёт посещаемости: доля посещений от общего числа возможных на проведённых занятиях
$stmt = $pdo->prepare("
    SELECT 
        COUNT(п.id) as посещено,
        COUNT(гу.id) as всего_возможно
    FROM занятия з
    JOIN группы г ON з.группа_id = г.id
    JOIN группа_ученики гу ON г.id = гу.группа_id
    LEFT JOIN посещения п ON з.id = п.занятие_id AND гу.ученик_id = п.ученик_id
    WHERE г.инструктор_id = ?
      AND з.статус = 'проведено'
");
$stmt->execute([$instructor_id]);
$attendance_data = $stmt->fetch();

$attendance_rate = 0;
if ($attendance_data['всего_возможно'] > 0) {
    $attendance_rate = round($attendance_data['посещено'] / $attendance_data['всего_возможно'] * 100);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Кабинет инструктора</title>
    <link rel="stylesheet" href="/CSS/main.css">

    <style>
        .radio-group {
            display: flex;
            gap: 20px;
            margin-top: 8px;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            font-weight: normal;
        }

        .instructor-dashboard {
            display: flex;
            gap: 30px;
            padding: 20px 0;
            flex-wrap: wrap;
        }

        .instructor-dashboard .sidebar {
            width: 220px;
            background: #f0f7fc;
            border-radius: 12px;
            padding: 20px;
            height: fit-content;
            flex-shrink: 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .instructor-dashboard .sidebar a {
            display: block;
            padding: 10px 15px;
            color: #0897da;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 8px;
            font-weight: 600;
            transition: all 0.2s;
            font-family: "Playpen Sans", cursive;
        }

        .instructor-dashboard .sidebar a:hover,
        .instructor-dashboard .sidebar a.active {
            background: #b3e5fc;
            color: #0066b2;
        }

        .instructor-dashboard .main-content {
            flex: 1;
            min-width: 0;
        }

        .instructor-dashboard .card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .instructor-dashboard .card h2 {
            color: #00334d;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 700;
            font-family: "Playpen Sans", cursive;
        }

        /* Группы */
        .group-list {
            list-style: none;
            padding: 0;
        }
        .group-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            font-family: "Playpen Sans", cursive;
        }
        .group-list li:last-child {
            border-bottom: none;
        }

        /* Занятия */
        .lesson-item {
            padding: 16px;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            margin-bottom: 16px;
            background: #fafafa;
            font-family: "Playpen Sans", cursive;
        }

        .lesson-status {
            margin: 10px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            min-width: 100px;
            text-align: center;
            font-family: "Playpen Sans", cursive;
        }

        .status-запланировано {
            background: #e0f7fa;
            color: #0066b2;
        }

        .status-проведено {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-отменено {
            background: #ffebee;
            color: #c62828;
        }

        .attendance-section {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px dashed #ddd;
        }

        .attendance-form {
            display: inline-block;
            margin-right: 15px;
            margin-top: 6px;
        }

        .attendance-form label {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            font-family: "Playpen Sans", cursive;
        }

        /* Форма добавления занятия */
        .add-lesson-form .form-group {
            margin-bottom: 16px;
        }

        .add-lesson-form label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #0d4561;
            font-family: "Playpen Sans", cursive;
        }

        .add-lesson-form input,
        .add-lesson-form select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-family: "Playpen Sans", cursive;
            font-size: 16px;
            box-sizing: border-box;
        }

        .add-lesson-form input:focus,
        .add-lesson-form select:focus {
            outline: none;
            border-color: #81d4fa;
        }

        /* Кнопка "Добавить занятие" */
        .btn-primary {
            background: #0897da;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
            font-family: "Playpen Sans", cursive;
            transition: background 0.3s;
        }

        .btn-primary:hover {
            background: #0066b2;
        }

        /* Ученики */
        .students-table {
            width: 100%;
        }

        .students-header,
        .student-row {
            display: grid;
            grid-template-columns: 2fr 1.5fr 1fr 1.5fr;
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-family: "Playpen Sans", cursive;
        }

        .students-header {
            font-weight: bold;
            background: #f0f7fc;
            border-radius: 8px;
        }

        .student-row:last-child {
            border-bottom: none;
        }

        /* Статистика */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-item {
            text-align: center;
            padding: 20px;
            background: #f0f7fc;
            border-radius: 12px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #0897da;
            font-family: "Playpen Sans", cursive;
        }

        .stat-label {
            color: #555;
            margin-top: 6px;
            font-family: "Playpen Sans", cursive;
        }

        .progress-bar-container {
            margin-top: 20px;
        }

        .progress-label {
            margin-bottom: 8px;
            font-weight: 600;
            color: #0d4561;
            font-family: "Playpen Sans", cursive;
        }

        .progress-bar {
            height: 12px;
            background: #e0e0e0;
            border-radius: 6px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: #0897da;
            transition: width 0.5s;
        }

        /* Кнопка переключения истории */
        .toggle-history-btn {
            background: #e0f7fa;
            border: 1px solid #81d4fa;
            color: #0897da;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            cursor: pointer;
            margin-left: 16px;
            transition: all 0.2s;
            font-family: "Playpen Sans", cursive;
        }

        .toggle-history-btn:hover {
            background: #b3e5fc;
            color: #0066b2;
        }

        .history-search input {
        font-size: 16px;
        padding: 10px 15px;
        border: 1px solid #ccc;
        border-radius: 8px;
        width: 100%;
        box-sizing: border-box;
        }

        .history-search input:focus {
            outline: none;
            border-color: #81d4fa;
            box-shadow: 0 0 0 2px rgba(129, 212, 250, 0.3);
        }

        /* Кнопка "Выйти" в кабинете */
        .instructor-dashboard .sidebar a[href="/logout.php"] {
            background: #ffebee;
            color: #c62828;
            margin-top: 20px;
            text-align: center;
        }

        .instructor-dashboard .sidebar a[href="/logout.php"]:hover {
            background: #ffcdd2;
            color: #b71c1c;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .instructor-dashboard {
                flex-direction: column;
            }

            .instructor-dashboard .sidebar {
                width: 100%;
                display: flex;
                overflow-x: auto;
                background: #e0f7fa;
                padding: 10px;
                border-radius: 12px;
            }

            .instructor-dashboard .sidebar a {
                white-space: nowrap;
                margin-right: 10px;
                flex-shrink: 0;
            }

            .students-header,
            .student-row {
                grid-template-columns: 1fr;
                gap: 8px;
                text-align: left;
            }

            .instructor-dashboard .card {
                padding: 16px;
            }

            .instructor-dashboard .card h2 {
                font-size: 20px;
            }
        }
    </style>

</head>
<body>
    <div class="wrapper">
        <?php require_once __DIR__ . '/../blocks/header.php'; ?>

        <div class="container">
            <div class="instructor-dashboard">
                <div class="sidebar">
                    <a href="#groups" class="nav-link">Мои группы</a>
                    <a href="#lessons" class="nav-link">Расписание</a>
                    <a href="#add-lesson" class="nav-link">Добавить занятие</a>
                    <a href="#students" class="nav-link">Ученики</a>
                    <a href="#stats" class="nav-link">Статистика</a>
                    <a href="#history" class="nav-link">История</a>
                    <a href="/logout.php" class="nav-link">Выйти</a>
                </div>

                <div class="main-content">
                    <!-- Мои группы -->
                    <div id="groups" class="card">
                        <h2>Мои учебные группы</h2>
                        <?php if ($groups): ?>
                            <ul class="group-list">
                                <?php foreach ($groups as $group): ?>
                                    <li><?= htmlspecialchars($group['название']) ?> (<?= htmlspecialchars($group['категория']) ?>)</li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>У вас пока нет групп.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Расписание -->
                    <div id="lessons" class="card">
                        <h2>Запланированные занятия</h2>
                        <?php if ($lessons): ?>
                            <?php foreach ($lessons as $lesson): ?>
                                <div class="lesson-item">
                                    <div>
                                        <strong><?= date('d.m.Y', strtotime($lesson['дата_занятия'])) ?></strong> 
                                        в <?= $lesson['время_начала'] ?>–<?= $lesson['время_окончания'] ?>
                                        — <?= htmlspecialchars($lesson['тип']) ?>
                                    </div>
                                        <?php if (!empty($lesson['группа_id'])): ?>
                                            <div>Группа: <?= htmlspecialchars($lesson['группа_название']) ?></div>
                                        <?php elseif (!empty($lesson['ученик_id'])): ?>
                                            <div>Ученик: <?= htmlspecialchars($lesson['ученик_фио']) ?></div>
                                        <?php endif; ?>
                                    <div>Тема: <?= htmlspecialchars($lesson['тема']) ?></div>
                                    <div>Место: <?= htmlspecialchars($lesson['место']) ?></div>
                                    
                                    <div class="lesson-status">
                                        Статус: 
                                        <span class="status-badge status-запланировано">Запланировано</span>
                                        <form method="POST" style="display: inline; margin-left: 10px;">
                                            <input type="hidden" name="action" value="update_lesson_status">
                                            <input type="hidden" name="lesson_id" value="<?= $lesson['id'] ?>">
                                            <select name="status" onchange="this.form.submit()">
                                                <option value="запланировано" selected>Запланировано</option>
                                                <option value="проведено">Проведено</option>
                                                <option value="отменено">Отменено</option>
                                            </select>
                                        </form>
                                    </div>

                                    <div class="attendance-section">
                                        <strong>Посещаемость:</strong>
                                        <?php
                                        if (!empty($lesson['группа_id'])) {
                                            // Групповое занятие
                                            $stmt_att = $pdo->prepare("
                                                SELECT уч.id as ученик_id, u.фио
                                                FROM группа_ученики гу
                                                JOIN ученики уч ON гу.ученик_id = уч.id
                                                JOIN пользователи u ON уч.пользователь_id = u.id
                                                WHERE гу.группа_id = ?
                                            ");
                                            $stmt_att->execute([$lesson['группа_id']]);
                                            $students_list = $stmt_att->fetchAll();
                                        } else {
                                            // Индивидуальное занятие
                                            $students_list = [[
                                                'ученик_id' => $lesson['ученик_id'],
                                                'фио' => $lesson['ученик_фио']
                                            ]];
                                        }

                                        foreach ($students_list as $student) {
                                            $stmt_att2 = $pdo->prepare("SELECT 1 FROM посещения WHERE ученик_id = ? AND занятие_id = ?");
                                            $stmt_att2->execute([$student['ученик_id'], $lesson['id']]);
                                            $attended = $stmt_att2->fetch();
                                            ?>
                                            <form method="POST" class="attendance-form">
                                                <input type="hidden" name="action" value="mark_attendance">
                                                <input type="hidden" name="lesson_id" value="<?= $lesson['id'] ?>">
                                                <input type="hidden" name="student_id" value="<?= $student['ученик_id'] ?>">
                                                <label>
                                                    <input type="checkbox" name="attended" value="1" 
                                                        <?= $attended ? 'checked' : '' ?>
                                                        onchange="this.form.submit()">
                                                    <?= htmlspecialchars($student['фио']) ?>
                                                </label>
                                            </form>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Нет запланированных занятий.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Добавить занятие -->
                    <div id="add-lesson" class="card">
                        <h2>Добавить новое занятие</h2>
                        <form method="POST" class="add-lesson-form">
                            <input type="hidden" name="action" value="add_lesson">
                            
                            <!-- Переключатель -->
                            <div class="form-group">
                                <label>Тип занятия:</label><br>
                                <div class="radio-group">
                                    <label>
                                        <input type="radio" name="lesson_type" value="group" checked onchange="toggleLessonType()"> 
                                        Групповое
                                    </label>
                                    <label>
                                        <input type="radio" name="lesson_type" value="individual" onchange="toggleLessonType()"> 
                                        Индивидуальное
                                    </label>
                                </div>
                            </div>

                            <!-- Выбор группы (виден по умолчанию) -->
                            <div class="form-group" id="groupField">
                                <label>Группа:</label>
                                <select name="group_id" required>
                                    <option value="">Выберите группу</option>
                                    <?php foreach ($groups as $group): ?>
                                        <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['название']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Выбор ученика (скрыт) -->
                            <div class="form-group" id="studentField" style="display: none;">
                                <label>Ученик:</label>
                                <select name="student_id">
                                    <option value="">Выберите ученика</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?= $student['ученик_id'] ?>">
                                            <?= htmlspecialchars($student['фио']) ?> 
                                            (<?= htmlspecialchars($student['группа']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Остальные поля -->
                            <div class="form-group">
                                <label>Тип занятия:</label>
                                <select name="type" required>
                                    <option value="теория">Теория</option>
                                    <option value="практика">Практика</option>
                                    <option value="экзамен">Экзамен</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Дата:</label>
                                <input type="date" name="date" min="<?= date('Y-m-d') ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Время начала:</label>
                                <input type="time" name="start" required>
                            </div>

                            <div class="form-group">
                                <label>Время окончания:</label>
                                <input type="time" name="end" required>
                            </div>

                            <div class="form-group">
                                <label>Тема:</label>
                                <input type="text" name="topic" placeholder="Тема занятия" required>
                            </div>

                            <div class="form-group">
                                <label>Место:</label>
                                <input type="text" name="place" placeholder="Место проведения" required>
                            </div>

                            <button type="submit" class="btn-primary">Добавить занятие</button>
                        </form>
                    </div>

                    <!-- Ученики -->
                    <div id="students" class="card">
                        <h2>Мои ученики</h2>
                        <?php if ($students): ?>
                            <div class="students-table">
                                <div class="students-header">
                                    <div>ФИО</div>
                                    <div>Email</div>
                                    <div>Категория</div>
                                    <div>Группа</div>
                                </div>
                                <?php foreach ($students as $student): ?>
                                    <div class="student-row">
                                        <div><?= htmlspecialchars($student['фио']) ?></div>
                                        <div><?= htmlspecialchars($student['email']) ?></div>
                                        <div><?= htmlspecialchars($student['категория']) ?></div>
                                        <div><?= htmlspecialchars($student['группа']) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>Нет учеников.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Статистика -->
                    <div id="stats" class="card">
                        <h2>Статистика</h2>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-value"><?= $stats['всего_занятий'] ?></div>
                                <div class="stat-label">Всего занятий</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?= $stats['проведено'] ?></div>
                                <div class="stat-label">Проведено</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?= $attendance_rate ?>%</div>
                                <div class="stat-label">Посещаемость</div>
                            </div>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-label">Общая посещаемость</div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $attendance_rate ?>%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- История занятий (с поиском и сворачиванием) -->
                    <div id="history" class="card">
                        <h2>
                            История занятий
                            <button type="button" class="toggle-history-btn" id="toggleHistoryBtn">
                                Показать историю
                            </button>
                        </h2>
                        
                        <div class="history-content" id="historyContent" style="display: none;">
                            <div class="history-search">
                                <input 
                                    type="text" 
                                    id="historySearch" 
                                    placeholder="Поиск по дате, теме, группе, типу или месту..."
                                    style="width: 100%; padding: 10px; margin-bottom: 16px; border: 1px solid #ccc; border-radius: 8px; font-family: 'Playpen Sans', cursive;"
                                >
                            </div>

                            <div class="history-list" id="historyList">
                                <?php if ($history_lessons): ?>
                                    <?php foreach ($history_lessons as $lesson): ?>
                                        <div class="history-item" 
                                            data-date="<?= date('d.m.Y', strtotime($lesson['дата_занятия'])) ?>"
                                            data-topic="<?= htmlspecialchars($lesson['тема']) ?>"
                                            data-group="<?= htmlspecialchars($lesson['группа_название'] ?? $lesson['ученик_фио'] ?? '') ?>"
                                            data-type="<?= htmlspecialchars($lesson['тип']) ?>"
                                            data-place="<?= htmlspecialchars($lesson['место']) ?>">
                                            
                                            <div class="lesson-item" style="background:#f9f9f9; opacity:0.9;">
                                                <div>
                                                    <strong><?= date('d.m.Y', strtotime($lesson['дата_занятия'])) ?></strong> 
                                                    в <?= $lesson['время_начала'] ?>–<?= $lesson['время_окончания'] ?>
                                                    — <?= htmlspecialchars($lesson['тип']) ?>
                                                    <span class="status-badge status-<?= htmlspecialchars($lesson['статус']) ?>">
                                                        <?= htmlspecialchars($lesson['статус']) ?>
                                                    </span>
                                                </div>
                                                
                                                <!-- ЗАМЕНИТЕ ЭТОТ БЛОК: -->
                                                <?php if (!empty($lesson['группа_id'])): ?>
                                                    <div>Группа: <?= htmlspecialchars($lesson['группа_название']) ?></div>
                                                <?php elseif (!empty($lesson['ученик_id'])): ?>
                                                    <div>Ученик: <?= htmlspecialchars($lesson['ученик_фио']) ?></div>
                                                <?php endif; ?>
                                                <!-- КОНЕЦ ЗАМЕНЫ -->

                                                <div>Тема: <?= htmlspecialchars($lesson['тема']) ?></div>
                                                <div>Место: <?= htmlspecialchars($lesson['место']) ?></div>
                                                
                                                <div class="attendance-section">
                                                    <strong>Посещаемость:</strong>
                                                    <?php
                                                    if (!empty($lesson['группа_id'])) {
                                                        $stmt_hist = $pdo->prepare("
                                                            SELECT уч.id, u.фио
                                                            FROM группа_ученики гу
                                                            JOIN ученики уч ON гу.ученик_id = уч.id
                                                            JOIN пользователи u ON уч.пользователь_id = u.id
                                                            WHERE гу.группа_id = ?
                                                        ");
                                                        $stmt_hist->execute([$lesson['группа_id']]);
                                                        $students_hist = $stmt_hist->fetchAll();
                                                    } else {
                                                        $students_hist = [[
                                                            'id' => $lesson['ученик_id'],
                                                            'фио' => $lesson['ученик_фио']
                                                        ]];
                                                    }

                                                    foreach ($students_hist as $student) {
                                                        $stmt_hist2 = $pdo->prepare("SELECT 1 FROM посещения WHERE ученик_id = ? AND занятие_id = ?");
                                                        $stmt_hist2->execute([$student['id'], $lesson['id']]);
                                                        $attended = $stmt_hist2->fetch();
                                                        echo '<span style="color:' . ($attended ? '#2e7d32' : '#c62828') . ';">●</span> ' . htmlspecialchars($student['фио']) . ' ';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>Нет завершённых занятий.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php require_once __DIR__ . '/../blocks/footer.php'; ?>
    </div>

    
</body>

    <script>
            document.querySelectorAll('.nav-link').forEach(link => {
            const href = link.getAttribute('href');
            // Пропускаем внешние ссылки и logout
            if (href && (href.startsWith('http') || href === '/logout.php')) {
                return; // не мешаем переходу
            }
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = href.substring(1);
                const target = document.getElementById(targetId);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        // Переключение типа занятия
        function toggleLessonType() {
            const groupField = document.getElementById('groupField');
            const studentField = document.getElementById('studentField');
            const groupSelect = groupField.querySelector('select');
            const studentSelect = studentField.querySelector('select');
            
            if (document.querySelector('input[name="lesson_type"]:checked').value === 'group') {
                groupField.style.display = 'block';
                studentField.style.display = 'none';
                groupSelect.setAttribute('required', 'required');
                studentSelect.removeAttribute('required');
            } else {
                groupField.style.display = 'none';
                studentField.style.display = 'block';
                groupSelect.removeAttribute('required');
                studentSelect.setAttribute('required', 'required');
            }
        }

        // История занятий
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('toggleHistoryBtn');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const historyContent = document.getElementById('historyContent');
                    const isHidden = historyContent.style.display === 'none';
                    historyContent.style.display = isHidden ? 'block' : 'none';
                    this.textContent = isHidden ? 'Скрыть историю' : 'Показать историю';
                });
            }

            const searchInput = document.getElementById('historySearch');
            if (searchInput) {
                const historyItems = document.querySelectorAll('.history-item');
                searchInput.addEventListener('input', function() {
                    const query = this.value.trim().toLowerCase();
                    historyItems.forEach(item => {
                        const date = item.dataset.date.toLowerCase();
                        const topic = item.dataset.topic.toLowerCase();
                        const group = item.dataset.group.toLowerCase();
                        const type = item.dataset.type.toLowerCase();
                        const place = item.dataset.place.toLowerCase();
                        const match = date.includes(query) || topic.includes(query) || 
                                    group.includes(query) || type.includes(query) || 
                                    place.includes(query);
                        item.style.display = match ? '' : 'none';
                    });
                });
            }
        });
    </script>

    
</html>