<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'админ') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../database/db.php';

$error = '';

// Загружаем учеников
$students = [];
try {
    $stmt = $pdo->query("
        SELECT u.id AS user_id, uch.id AS student_id, u.фио
        FROM пользователи u
        JOIN ученики uch ON u.id = uch.пользователь_id
        ORDER BY u.фио
    ");
    $students = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Не удалось загрузить учеников";
}

// Загружаем группы (опционально)
$groups = [];
try {
    $stmt = $pdo->query("SELECT id, название FROM группы ORDER BY название");
    $groups = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Не удалось загрузить группы";
}

if ($_POST) {
    $student_id = (int)($_POST['student_id'] ?? 0);
    $group_id = $_POST['group_id'] !== '' ? (int)$_POST['group_id'] : null;
    $amount = str_replace(',', '.', trim($_POST['amount'] ?? ''));
    $method = $_POST['method'] ?? '';
    $status = $_POST['status'] ?? 'оплачено';
    $comment = trim($_POST['comment'] ?? '');

    // Валидация
    if (!$student_id) {
        $error = "Выберите ученика";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error = "Укажите корректную сумму";
    } elseif (!in_array($method, ['наличные', 'карта', 'перевод', 'онлайн'])) {
        $error = "Выберите способ оплаты";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO платежи (ученик_id, группа_id, сумма, способ, статус, комментарий)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$student_id, $group_id, $amount, $method, $status, $comment ?: null]);

            $pdo->commit();
            header('Location: /cabinet/admin.php#payments');
            exit;
        } catch (Exception $e) {
            $pdo->rollback();
            $error = "Ошибка сохранения: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить платёж</title>
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
        .form-group select,
        .form-group textarea {
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
            <h1>Добавить платёж</h1>

            <?php if ($error): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Ученик (обязательно):</label>
                    <select name="student_id" required>
                        <option value="">Выберите ученика</option>
                        <?php foreach ($students as $s): ?>
                            <option value="<?= $s['student_id'] ?>"><?= htmlspecialchars($s['фио']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Группа (опционально):</label>
                    <select name="group_id">
                        <option value="">Не привязана к группе</option>
                        <?php foreach ($groups as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['название']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Сумма (₽):</label>
                    <input type="number" name="amount" step="0.01" min="0.01" placeholder="1000.00" required>
                </div>

                <div class="form-group">
                    <label>Способ оплаты:</label>
                    <select name="method" required>
                        <option value="">Выберите способ</option>
                        <option value="наличные">Наличные</option>
                        <option value="карта">Банковская карта</option>
                        <option value="перевод">Банковский перевод</option>
                        <option value="онлайн">Онлайн-оплата</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Статус:</label>
                    <select name="status" required>
                        <option value="оплачено">Оплачен</option>
                        <option value="ожидает">Ожидает оплаты</option>
                        <option value="отменено">Отменён</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Комментарий (опционально):</label>
                    <textarea name="comment" rows="3" placeholder="Например: частичная оплата, бонус и т.д."></textarea>
                </div>

                <button type="submit" class="btn-submit">Сохранить платёж</button>
                <a href="/cabinet/admin.php#payments" style="display: block; text-align: center; margin-top: 16px; color: #0897da;">Отмена</a>
            </form>
        </div>

        <?php require_once __DIR__ . '/../blocks/footer.php'; ?>
    </div>
</body>
</html>