<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'админ') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../database/db.php';

try {
    $stmt = $pdo->query("
        SELECT id, фио, телефон, email, категория, дата_заявки, статус
        FROM заявки
        WHERE статус IN ('обработана', 'отклонена')
        ORDER BY дата_заявки DESC
    ");
    $history = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Ошибка загрузки истории заявок");
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>История заявок</title>
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
        }
        .history-table th {
            background-color: #f0f7fc;
            font-weight: 600;
        }
        .status-обработана { color: #2e7d32; }
        .status-отклонена { color: #c62828; }
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

        /* Адаптивность */
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
            <h1>История заявок</h1>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>ФИО</th>
                        <th>Телефон</th>
                        <th>Email</th>
                        <th>Категория</th>
                        <th>Дата</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($history): ?>
                        <?php foreach ($history as $req): ?>
                            <tr>
                                <td data-label="ФИО"><?= htmlspecialchars($req['фио']) ?></td>
                                <td data-label="Телефон"><?= htmlspecialchars($req['телефон']) ?></td>
                                <td data-label="Email"><?= htmlspecialchars($req['email'] ?? '—') ?></td>
                                <td data-label="Категория"><?= htmlspecialchars($req['категория']) ?></td>
                                <td data-label="Дата"><?= date('d.m.Y H:i', strtotime($req['дата_заявки'])) ?></td>
                                <td data-label="Статус">
                                    <span class="status-<?= $req['статус'] ?>">
                                        <?= $req['статус'] === 'обработана' ? 'Обработана' : 'Отклонена' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">Нет обработанных заявок</td>
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