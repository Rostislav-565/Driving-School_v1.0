<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'админ') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../database/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /cabinet/admin.php#payments');
    exit;
}

// Загружаем текущий платёж
$stmt = $pdo->prepare("
    SELECT 
        p.id, p.ученик_id, p.группа_id, p.сумма, p.способ, p.статус, p.комментарий,
        u.фио AS ученик_фио,
        g.название AS группа_название
    FROM платежи p
    JOIN ученики уч ON p.ученик_id = уч.id
    JOIN пользователи u ON уч.пользователь_id = u.id
    LEFT JOIN группы g ON p.группа_id = g.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$payment = $stmt->fetch();

if (!$payment) {
    die("Платёж не найден");
}

// Загружаем списки
$students = $pdo->query("
    SELECT u.id AS user_id, uch.id AS student_id, u.фио
    FROM пользователи u
    JOIN ученики uch ON u.id = uch.пользователь_id
    ORDER BY u.фио
")->fetchAll();

$groups = $pdo->query("SELECT id, название FROM группы ORDER BY название")->fetchAll();

$error = '';

if ($_POST) {
    $student_id = (int)($_POST['student_id'] ?? 0);
    $group_id = $_POST['group_id'] !== '' ? (int)$_POST['group_id'] : null;
    $amount = str_replace(',', '.', trim($_POST['amount'] ?? ''));
    $method = $_POST['method'] ?? '';
    $status = $_POST['status'] ?? 'оплачено';
    $comment = trim($_POST['comment'] ?? '');

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
                UPDATE платежи 
                SET ученик_id = ?, группа_id = ?, сумма = ?, способ = ?, статус = ?, комментарий = ?
                WHERE id = ?
            ");
            $stmt->execute([$student_id, $group_id, $amount, $method, $status, $comment ?: null, $id]);
            $pdo->commit();
            header('Location: /cabinet/admin.php#payments');
            exit;
        } catch (Exception $e) {
            $pdo->rollback();
            $error = "Ошибка обновления: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать платёж</title>
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
            <h1>Редактировать платёж</h1>

            <?php if ($error): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Ученик (обязательно):</label>
                    <select name="student_id" required>
                        <option value="">Выберите ученика</option>
                        <?php foreach ($students as $s): ?>
                            <option value="<?= $s['student_id'] ?>" <?= $s['student_id'] == $payment['ученик_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['фио']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Группа (опционально):</label>
                    <select name="group_id">
                        <option value="">Не привязана к группе</option>
                        <?php foreach ($groups as $g): ?>
                            <option value="<?= $g['id'] ?>" <?= ($g['id'] == $payment['группа_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g['название']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Сумма (₽):</label>
                    <input type="number" name="amount" step="0.01" min="0.01" value="<?= $payment['сумма'] ?>" required>
                </div>

                <div class="form-group">
                    <label>Способ оплаты:</label>
                    <select name="method" required>
                        <option value="">Выберите способ</option>
                        <option value="наличные" <?= $payment['способ'] === 'наличные' ? 'selected' : '' ?>>Наличные</option>
                        <option value="карта" <?= $payment['способ'] === 'карта' ? 'selected' : '' ?>>Банковская карта</option>
                        <option value="перевод" <?= $payment['способ'] === 'перевод' ? 'selected' : '' ?>>Банковский перевод</option>
                        <option value="онлайн" <?= $payment['способ'] === 'онлайн' ? 'selected' : '' ?>>Онлайн-оплата</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Статус:</label>
                    <select name="status" required>
                        <option value="оплачено" <?= $payment['статус'] === 'оплачено' ? 'selected' : '' ?>>Оплачен</option>
                        <option value="ожидает" <?= $payment['статус'] === 'ожидает' ? 'selected' : '' ?>>Ожидает оплаты</option>
                        <option value="отменено" <?= $payment['статус'] === 'отменено' ? 'selected' : '' ?>>Отменён</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Комментарий (опционально):</label>
                    <textarea name="comment" rows="3" placeholder="Например: частичная оплата, бонус и т.д."><?= htmlspecialchars($payment['комментарий'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn-submit">Сохранить изменения</button>
                <a href="/cabinet/admin.php#payments" style="display: block; text-align: center; margin-top: 16px; color: #0897da;">Отмена</a>
            </form>
        </div>

        <?php require_once __DIR__ . '/../blocks/footer.php'; ?>
    </div>
</body>
</html>