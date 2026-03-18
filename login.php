<?php
session_start();

// Если уже залогинен — сразу в кабинет
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    if ($role === 'ученик') header('Location: /cabinet/student.php');
    elseif ($role === 'инструктор') header('Location: /cabinet/instructor.php');
    else header('Location: /cabinet/admin.php');
    exit;
}

$error = '';

if ($_POST) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!$email || !$password) {
        $error = "Заполните все поля";
    } else {
        require_once 'database/db.php';

        // Ищем пользователя по email
        $stmt = $pdo->prepare("SELECT id, роль_id, пароль, фио FROM пользователи WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['пароль'])) {
            // Устанавливаем сессию
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role_id'] = $user['роль_id'];
            $_SESSION['fio'] = $user['фио'];

            // Определяем роль текстом
            $role_map = [1 => 'ученик', 2 => 'инструктор', 3 => 'админ'];
            $role = $role_map[$user['роль_id']] ?? 'ученик';
            $_SESSION['role'] = $role;

            // Редирект в кабинет
            if ($role === 'ученик') header('Location: /cabinet/student.php');
            elseif ($role === 'инструктор') header('Location: /cabinet/instructor.php');
            else header('Location: /cabinet/admin.php');
            exit;
        } else {
            $error = "Неверный email или пароль";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в личный кабинет</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom, #e0f7fa, #81d4fa);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-form {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-form h2 {
            text-align: center;
            margin-bottom: 24px;
            color: #00334d;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            color: #0d4561;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #0897da;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn:hover {
            background: #0066b2;
        }
        .error {
            color: #d32f2f;
            margin-top: 12px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-form">
        <h2>Вход в личный кабинет</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Войти</button>
        </form>
    </div>
</body>
</html>