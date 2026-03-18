<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'админ') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../database/db.php';

$message = '';
$error = '';

// Получаем список групп для выпадающего списка
$groups = [];
try {
    $stmt = $pdo->query("SELECT id, название FROM группы WHERE статус = 'набор' OR статус = 'обучение'");
    $groups = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Не удалось загрузить список групп";
}

if ($_POST) {
    $role = $_POST['role'] ?? '';
    $fio = trim($_POST['fio'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $category = $_POST['category'] ?? null;
    $group_id = $_POST['group_id'] ?? null; // ← НОВОЕ
    $car = $_POST['car'] ?? null;
    $car_number = $_POST['car_number'] ?? null;
    $experience = $_POST['experience'] ?? null;

    if (!$role || !$fio || !$email || !$password) {
        $error = "Заполните все обязательные поля";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Неверный формат email";
    } elseif (strlen($password) < 6) {
        $error = "Пароль должен быть не менее 6 символов";
    } else {
        try {
            $pdo->beginTransaction();

            $role_id = ($role === 'ученик') ? 1 : ($role === 'инструктор' ? 2 : 3);
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // 1. Создаём пользователя
            $stmt = $pdo->prepare("
                INSERT INTO пользователи (роль_id, фио, телефон, email, пароль, активен)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([$role_id, $fio, $phone, $email, $password_hash]);
            $user_id = $pdo->lastInsertId();

            // 2. Дополнительные таблицы
            if ($role === 'ученик') {
                if (!$category) {
                    throw new Exception("Выберите категорию");
                }
                if (!$group_id) {
                    throw new Exception("Выберите группу");
                }

                // Добавляем в ученики
                $stmt = $pdo->prepare("
                    INSERT INTO ученики (пользователь_id, категория, статус, дата_начала)
                    VALUES (?, ?, 'активный', CURDATE())
                ");
                $stmt->execute([$user_id, $category]);
                $student_id = $pdo->lastInsertId();

                // Зачисляем в группу
                $stmt = $pdo->prepare("
                    INSERT INTO группа_ученики (группа_id, ученик_id, дата_зачисления)
                    VALUES (?, ?, CURDATE())
                ");
                $stmt->execute([$group_id, $student_id]);
            }

            if ($role === 'инструктор') {
                $stmt = $pdo->prepare("
                    INSERT INTO инструкторы (пользователь_id, автомобиль, номер_авто, стаж_лет)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$user_id, $car, $car_number, $experience]);
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
    <title>Добавить пользователя</title>
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
        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .message.error {
            background: #ffebee;
            color: #c62828;
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
            <h1>Добавить пользователя</h1>

            <?php if ($error): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" id="userForm">
                <div class="form-group">
                    <label>Роль:</label>
                    <select name="role" id="roleSelect" required>
                        <option value="">Выберите роль</option>
                        <option value="ученик">Ученик</option>
                        <option value="инструктор">Инструктор</option>
                        <option value="админ">Администратор</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>ФИО:</label>
                    <input type="text" name="fio" required>
                </div>

                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Телефон:</label>
                    <input type="text" name="phone">
                </div>

                <div class="form-group">
                    <label>Пароль (мин. 6 символов):</label>
                    <input type="password" name="password" required>
                </div>

                <!-- Поля для ученика -->
                <div id="studentFields" class="form-group">
                    <div class="form-group">
                        <label>Категория:</label>
                        <select name="category">
                            <option value="">Выберите категорию</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="M">M</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Группа:</label>
                        <select name="group_id">
                            <option value="">Выберите группу</option>
                            <?php if ($groups): ?>
                                <?php foreach ($groups as $group): ?>
                                    <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['название']) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">Нет активных групп</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- Поля для инструктора -->
                <div id="instructorFields" class="form-group">
                    <div class="form-group">
                        <label>Автомобиль:</label>
                        <input type="text" name="car" placeholder="Hyundai Solaris">
                    </div>
                    <div class="form-group">
                        <label>Номер авто:</label>
                        <input type="text" name="car_number" placeholder="A123BC">
                    </div>
                    <div class="form-group">
                        <label>Стаж (лет):</label>
                        <input type="number" name="experience" min="0" max="50" placeholder="5">
                    </div>
                </div>

                <button type="submit" class="btn-submit">Создать пользователя</button>
                <a href="/cabinet/admin.php#users" style="display: block; text-align: center; margin-top: 16px; color: #0897da;">Отмена</a>
            </form>
        </div>

        <?php require_once __DIR__ . '/../blocks/footer.php'; ?>
    </div>

    <script>
    document.getElementById('roleSelect').addEventListener('change', function() {
        const role = this.value;
        document.getElementById('studentFields').style.display = role === 'ученик' ? 'block' : 'none';
        document.getElementById('instructorFields').style.display = role === 'инструктор' ? 'block' : 'none';
        
        // Сброс полей при смене роли
        if (role !== 'ученик') {
            document.querySelector('[name="category"]').value = '';
            document.querySelector('[name="group_id"]').value = '';
        }
        if (role !== 'инструктор') {
            document.querySelector('[name="car"]').value = '';
            document.querySelector('[name="car_number"]').value = '';
            document.querySelector('[name="experience"]').value = '';
        }
    });
    </script>
</body>
</html>