<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'админ') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../database/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /cabinet/admin.php#users');
    exit;
}

// Получаем данные пользователя
$stmt = $pdo->prepare("
    SELECT 
        p.id, p.фио, p.email, p.телефон, p.роль_id,
        u.категория as student_category,
        i.автомобиль, i.номер_авто, i.стаж_лет,
        r.название as role_name
    FROM пользователи p
    JOIN роли r ON p.роль_id = r.id
    LEFT JOIN ученики u ON p.id = u.пользователь_id
    LEFT JOIN инструкторы i ON p.id = i.пользователь_id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    die("Пользователь не найден");
}

$error = '';

if ($_POST) {
    $fio = trim($_POST['fio'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $category = $_POST['category'] ?? null;
    $car = $_POST['car'] ?? null;
    $car_number = $_POST['car_number'] ?? null;
    $experience = $_POST['experience'] ?? null;

    if (!$fio || !$email) {
        $error = "Заполните обязательные поля";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Неверный email";
    } else {
        try {
            $pdo->beginTransaction();

            // Обновляем основные данные
            $sql = "UPDATE пользователи SET фио = ?, email = ?, телефон = ?";
            $params = [$fio, $email, $phone];

            // Обновляем пароль, только если введён новый
            if ($password) {
                if (strlen($password) < 6) {
                    throw new Exception("Пароль должен быть не менее 6 символов");
                }
                $sql .= ", пароль = ?";
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id = ?";
            $params[] = $id;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Обновляем ученика
            if ($user['роль_id'] == 1) {
                $stmt = $pdo->prepare("
                    UPDATE ученики 
                    SET категория = ? 
                    WHERE пользователь_id = ?
                ");
                $stmt->execute([$category, $id]);
            }

            // Обновляем инструктора
            if ($user['роль_id'] == 2) {
                $stmt = $pdo->prepare("
                    UPDATE инструкторы 
                    SET автомобиль = ?, номер_авто = ?, стаж_лет = ? 
                    WHERE пользователь_id = ?
                ");
                $stmt->execute([$car, $car_number, $experience, $id]);
            }

            $pdo->commit();
            header('Location: /cabinet/admin.php#users');
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
    <title>Редактировать пользователя</title>
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
        #studentFields, #instructorFields {
            display: none;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php require_once __DIR__ . '/../blocks/header.php'; ?>

        <div class="form-container">
            <h1>Редактировать: <?= htmlspecialchars($user['фио']) ?></h1>

            <?php if ($error): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>ФИО:</label>
                    <input type="text" name="fio" value="<?= htmlspecialchars($user['фио']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Телефон:</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($user['телефон']) ?>">
                </div>

                <div class="form-group">
                    <label>Новый пароль (оставьте пустым, чтобы не менять):</label>
                    <input type="password" name="password">
                </div>

                <?php if ($user['роль_id'] == 1): // Ученик ?>
                    <div class="form-group">
                        <label>Категория:</label>
                        <select name="category">
                            <option value="A" <?= $user['student_category'] === 'A' ? 'selected' : '' ?>>A</option>
                            <option value="B" <?= $user['student_category'] === 'B' ? 'selected' : '' ?>>B</option>
                            <option value="C" <?= $user['student_category'] === 'C' ? 'selected' : '' ?>>C</option>
                            <option value="D" <?= $user['student_category'] === 'D' ? 'selected' : '' ?>>D</option>
                            <option value="M" <?= $user['student_category'] === 'M' ? 'selected' : '' ?>>M</option>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if ($user['роль_id'] == 2): // Инструктор ?>
                    <div class="form-group">
                        <label>Автомобиль:</label>
                        <input type="text" name="car" value="<?= htmlspecialchars($user['автомобиль']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Номер авто:</label>
                        <input type="text" name="car_number" value="<?= htmlspecialchars($user['номер_авто']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Стаж (лет):</label>
                        <input type="number" name="experience" value="<?= htmlspecialchars($user['стаж_лет']) ?>" min="0" max="50">
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn-submit">Сохранить изменения</button>
                <a href="/cabinet/admin.php#users" style="display: block; text-align: center; margin-top: 16px; color: #0897da;">Отмена</a>
            </form>
        </div>

        <?php require_once __DIR__ . '/../blocks/footer.php'; ?>
    </div>
</body>
</html>