<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'админ') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../database/db.php';

try {
    $stmt = $pdo->query("
        SELECT id, фио, телефон, email, сообщение, ответ, дата
        FROM обращения
        WHERE отвечено = 1
        ORDER BY дата DESC
    ");
    $history = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Ошибка загрузки истории обращений");
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>История обращений</title>
    <link rel="stylesheet" href="/CSS/main.css">
    <style>
        .form-container {
            max-width: 1000px;
            margin: 30px auto;
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .form-container h1 {
            color: #00334d;
            margin-bottom: 20px;
            text-align: center;
            font-family: "Playpen Sans", cursive;
        }
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .history-table th,
        .history-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-family: "Playpen Sans", cursive;
            vertical-align: top;
        }
        .history-table th {
            background-color: #f0f7fc;
            font-weight: 600;
        }
        .msg-text {
            max-width: 250px;
            word-wrap: break-word;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #0897da;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .history-table, .history-table thead, .history-table tbody, .history-table th, .history-table td, .history-table tr {
                display: block;
            }
            .history-table tr {
                border: 1px solid #ccc;
                margin-bottom: 10px;
                border-radius: 8px;
                padding: 10px;
            }
            .history-table td {
                border: none;
                position: relative;
                padding-left: 35% !important;
            }
            .history-table td:before {
                content: attr(data-label);
                position: absolute;
                left: 6px;
                width: 34%;
                font-weight: 600;
                font-family: "Playpen Sans", cursive;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php require_once __DIR__ . '/../blocks/header.php'; ?>

        <div class="form-container">
            <h1>История обращений</h1>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>ФИО</th>
                        <th>Телефон</th>
                        <th>Email</th>
                        <th>Сообщение</th>
                        <th>Ответ</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($history): ?>
                        <?php foreach ($history as $c): ?>
                            <tr>
                                <td data-label="ФИО"><?= htmlspecialchars($c['фио']) ?></td>
                                <td data-label="Телефон"><?= htmlspecialchars($c['телефон']) ?></td>
                                <td data-label="Email"><?= htmlspecialchars($c['email'] ?? '—') ?></td>
                                <td data-label="Сообщение" class="msg-text"><?= nl2br(htmlspecialchars($c['сообщение'])) ?></td>
                                <td data-label="Ответ" class="msg-text"><?= $c['ответ'] ? nl2br(htmlspecialchars($c['ответ'])) : '<i>Ответ не сохранён</i>' ?></td>
                                <td data-label="Дата"><?= date('d.m.Y H:i', strtotime($c['дата'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">Нет отвеченных обращений</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <a href="/cabinet/admin.php" class="back-link">← Назад в панель администратора</a>
        </div>

        <?php require_once __DIR__ . '/../blocks/footer.php'; ?>
    </div>
</body>
</html>