<?php

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'админ') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../database/db.php';

$error = '';

// Получаем список инструкторов
$instructors = [];
try {
    $stmt = $pdo->query("
        SELECT i.id, u.фио 
        FROM инструкторы i
        JOIN пользователи u ON u.id = i.пользователь_id
        ORDER BY u.фио
    ");
    $instructors = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Не удалось загрузить инструкторов";
}

// Получаем список курсов  ← УДАЛЕН ЛИШНИЙ <?php
$courses = [];
try {
    $stmt = $pdo->query("SELECT id, название, категория FROM курсы ORDER BY категория, название");
    $courses = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Не удалось загрузить курсы";
}

if ($_POST) {
    $name = trim($_POST['name'] ?? '');
    $category = $_POST['category'] ?? '';
    $course_id = (int)($_POST['course_id'] ?? 0);
    $instructor_id = (int)($_POST['instructor_id'] ?? 0); // ← теперь обязательно
    $status = $_POST['status'] ?? 'набор';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    // Проверка инструктора
    if (!$instructor_id) {
        $error = "Выберите инструктора";
    } else {
        // Проверка существования инструктора
        $check = $pdo->prepare("SELECT 1 FROM инструкторы WHERE id = ?");
        $check->execute([$instructor_id]);
        if (!$check->fetch()) {
            $error = "Инструктор не найден";
        }
    }

    if ($error) {
        // оставить форму для повторного ввода
    } else if (!$name || !$category || !$course_id || !$start_date) {
        $error = "Заполните все обязательные поля";
    } else {
        // ... вставка
    }

    if (!$name || !$category || !$course_id || !$start_date) {
        $error = "Заполните все обязательные поля";
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("
                INSERT INTO группы (название, категория, курс_id, инструктор_id, статус, дата_начала, дата_окончания)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $category, $course_id, $instructor_id, $status, $start_date, $end_date ?: null]);
            $pdo->commit();
            header('Location: /cabinet/admin.php#groups');
            exit;
        } catch (Exception $e) {
            $pdo->rollback();
            $error = "Ошибка: " . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить группу</title>
    <link rel="stylesheet" href="/CSS/main.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .form-container h1 {
            text-align: center;
            color: #00334d;
            margin-bottom: 24px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #0d4561;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-family: "Playpen Sans", cursive;
            font-size: 16px;
            box-sizing: border-box;
        }
        .btn-submit {
            background: #0897da;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            font-family: "Playpen Sans", cursive;
        }
        .btn-submit:hover {
            background: #0066b2;
        }
        .message.error {
            background: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php require_once __DIR__ . '/../blocks/header.php'; ?>

        <div class="form-container">
            <h1>Добавить группу</h1>

            <?php if ($error): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Название группы:</label>
                    <input type="text" name="name" placeholder="Группа B-01/2025" required>
                </div>

                <div class="form-group">
                    <label>Категория:</label>
                    <select name="category" required>
                        <option value="">Выберите категорию</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="M">M</option>
                    </select>
                </div>

                <!-- НОВОЕ ПОЛЕ: Курс -->
                <div class="form-group">
                    <label>Курс:</label>
                    <select name="course_id" required>
                        <option value="">Выберите курс</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['id'] ?>">
                                <?= htmlspecialchars($course['название']) ?> (<?= htmlspecialchars($course['категория']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Инструктор (обязательно):</label>
                    <select name="instructor_id" required>
                        <option value="">Выберите инструктора</option>
                        <?php foreach ($instructors as $inst): ?>
                            <option value="<?= $inst['id'] ?>"><?= htmlspecialchars($inst['фио']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Статус:</label>
                    <select name="status">
                        <option value="набор">Набор</option>
                        <option value="обучение">Обучение</option>
                        <option value="завершена">Завершена</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Дата начала:</label>
                    <input type="date" name="start_date" required>
                </div>

                <div class="form-group">
                    <label>Дата окончания (необязательно):</label>
                    <input type="date" name="end_date">
                </div>

                <button type="submit" class="btn-submit">Создать группу</button>
                <a href="/cabinet/admin.php#groups" style="display: block; text-align: center; margin-top: 16px; color: #0897da;">Отмена</a>
            </form>
        </div>

        <?php require_once __DIR__ . '/../blocks/footer.php'; ?>
    </div>
</body>
</html>