<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'админ') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../database/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /cabinet/admin.php#groups');
    exit;
}

// Загружаем данные группы
$stmt = $pdo->prepare("
    SELECT 
        g.id, g.название, g.категория, g.курс_id, g.инструктор_id,
        g.статус, g.дата_начала, g.дата_окончания,
        k.название AS курс_название, k.категория AS курс_категория,
        u.фио AS инструктор_фио
    FROM группы g
    JOIN курсы k ON g.курс_id = k.id
    LEFT JOIN инструкторы i ON g.инструктор_id = i.id
    LEFT JOIN пользователи u ON i.пользователь_id = u.id
    WHERE g.id = ?
");
$stmt->execute([$id]);
$group = $stmt->fetch();

if (!$group) {
    die("Группа не найдена");
}

// Загружаем списки для выпадающих меню
$courses = $pdo->query("SELECT id, название, категория FROM курсы ORDER BY категория, название")->fetchAll();
$instructors = $pdo->query("
    SELECT i.id, u.фио 
    FROM инструкторы i
    JOIN пользователи u ON u.id = i.пользователь_id
    ORDER BY u.фио
")->fetchAll();

$error = '';

// Обработка удаления
if ($_POST['action'] === 'delete') {
    try {
        $pdo->beginTransaction();
        // Удаление каскадом: группа → группа_ученики, занятия, платежи (если настроены)
        $stmt = $pdo->prepare("DELETE FROM группы WHERE id = ?");
        $stmt->execute([$id]);
        $pdo->commit();
        header('Location: /cabinet/admin.php#groups');
        exit;
    } catch (Exception $e) {
        $pdo->rollback();
        $error = "Ошибка при удалении: " . $e->getMessage();
    }
}

// Обработка редактирования
if ($_POST && ($_POST['action'] ?? null) === 'update') {
    $name = trim($_POST['name'] ?? '');
    $course_id = (int)($_POST['course_id'] ?? 0);
    $instructor_id = $_POST['instructor_id'] !== '' ? (int)$_POST['instructor_id'] : null;
    $status = $_POST['status'] ?? 'набор';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    if (!$name || !$course_id || !$start_date) {
        $error = "Заполните все обязательные поля";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                UPDATE группы 
                SET название = ?, курс_id = ?, инструктор_id = ?, статус = ?, дата_начала = ?, дата_окончания = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $name,
                $course_id,
                $instructor_id,
                $status,
                $start_date,
                $end_date ?: null,
                $id
            ]);

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
    <title>Редактировать группу</title>
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
        .btn-submit, .btn-delete {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
            font-family: "Playpen Sans", cursive;
            width: 100%;
            margin-top: 10px;
        }
        .btn-submit {
            background: #0897da;
            color: white;
        }
        .btn-submit:hover {
            background: #0066b2;
        }
        .btn-delete {
            background: #d32f2f;
            color: white;
        }
        .btn-delete:hover {
            background: #b71c1c;
        }
        .message.error {
            background: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 16px;
        }
        .actions a {
            flex: 1;
            text-align: center;
            padding: 12px;
            background: #e0e0e0;
            border-radius: 8px;
            color: #333;
            text-decoration: none;
            font-family: "Playpen Sans", cursive;
            font-weight: 600;
        }
        .actions a:hover {
            background: #ccc;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php require_once __DIR__ . '/../blocks/header.php'; ?>

        <div class="form-container">
            <h1>Редактировать группу</h1>

            <?php if ($error): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="action" value="update">

                <div class="form-group">
                    <label>Название группы:</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($group['название']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Курс:</label>
                    <select name="course_id" required>
                        <option value="">Выберите курс</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $group['курс_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['название']) ?> (<?= htmlspecialchars($c['категория']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Инструктор:</label>
                    <select name="instructor_id">
                        <option value="">Не назначен</option>
                        <?php foreach ($instructors as $inst): ?>
                            <option value="<?= $inst['id'] ?>" <?= $inst['id'] == $group['инструктор_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($inst['фио']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Статус:</label>
                    <select name="status">
                        <option value="набор" <?= $group['статус'] === 'набор' ? 'selected' : '' ?>>Набор</option>
                        <option value="обучение" <?= $group['статус'] === 'обучение' ? 'selected' : '' ?>>Обучение</option>
                        <option value="завершена" <?= $group['статус'] === 'завершена' ? 'selected' : '' ?>>Завершена</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Дата начала:</label>
                    <input type="date" name="start_date" value="<?= $group['дата_начала'] ?>" required>
                </div>

                <div class="form-group">
                    <label>Дата окончания:</label>
                    <input type="date" name="end_date" value="<?= $group['дата_окончания'] ?>">
                </div>

                <button type="submit" class="btn-submit">Сохранить изменения</button>
            </form>

            <!-- Кнопка удаления (отдельная форма) -->
            <form method="POST" onsubmit="return confirm('Вы уверены, что хотите удалить группу? Это действие нельзя отменить. При наличии учеников или занятий удаление может быть заблокировано.')">
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="btn-delete">Удалить группу</button>
            </form>

            <div class="actions">
                <a href="/cabinet/admin.php#groups">Отмена</a>
            </div>
        </div>

        <?php require_once __DIR__ . '/../blocks/footer.php'; ?>
    </div>
</body>
</html>