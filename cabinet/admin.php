<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Проверка авторизации и роли администратора
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'админ') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../database/db.php';

// Обработка отметки обращения как отвеченного
if ($_POST['mark_answered'] ?? null) {
    $contact_id = (int)$_POST['mark_answered'];
    try {
        $stmt = $pdo->prepare("UPDATE обращения SET отвечено = 1 WHERE id = ? AND отвечено = 0");
        $stmt->execute([$contact_id]);
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } catch (Exception $e) {
        $error = "Ошибка при обработке обращения";
    }
}

// --- Получение статистики ---
try {
    // Общее количество учеников
    $stmt = $pdo->query("SELECT COUNT(*) FROM ученики");
    $total_students = $stmt->fetchColumn();

    // Общее количество инструкторов
    $stmt = $pdo->query("SELECT COUNT(*) FROM инструкторы");
    $total_instructors = $stmt->fetchColumn();

    // Общее количество групп
    $stmt = $pdo->query("SELECT COUNT(*) FROM группы");
    $total_groups = $stmt->fetchColumn();

} catch (PDOException $e) {
    die("Ошибка получения статистики: " . $e->getMessage());
}

// --- Получение списка пользователей ---
try {
    $stmt = $pdo->prepare("
        SELECT 
            p.id AS user_id,
            p.фио,
            p.телефон,
            p.email,
            p.дата_регистрации,
            p.активен,
            r.название AS role_name,
            p.тип AS user_type, -- Используем p.тип напрямую
            CASE 
                WHEN p.тип = 'инструктор' THEN i.автомобиль 
                WHEN p.тип = 'ученик' THEN u.категория 
                ELSE NULL -- Возвращаем NULL для админа
            END AS details
        FROM пользователи p
        JOIN роли r ON p.роль_id = r.id
        LEFT JOIN инструкторы i ON p.id = i.пользователь_id AND p.тип = 'инструктор'
        LEFT JOIN ученики u ON p.id = u.пользователь_id AND p.тип = 'ученик'
        ORDER BY p.дата_регистрации DESC
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Ошибка получения списка пользователей: " . $e->getMessage());
}

if ($_POST['mark_processed'] ?? null) {
    $req_id = (int)$_POST['mark_processed'];
    try {
        $stmt = $pdo->prepare("UPDATE заявки SET статус = 'обработана' WHERE id = ? AND статус = 'новая'");
        $stmt->execute([$req_id]);
        // Обновляем страницу
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } catch (Exception $e) {
        $error = "Ошибка при обработке заявки";
    }
}

// --- Получение списка платежей ---
try {
    $stmt = $pdo->query("
        SELECT 
            p.id,
            p.сумма,
            p.дата_платежа,
            p.способ,
            p.статус,
            p.комментарий,
            p.группа_id,
            u.фио AS ученик_фио,
            g.название AS группа_название
        FROM платежи p
        JOIN ученики уч ON p.ученик_id = уч.id
        JOIN пользователи u ON уч.пользователь_id = u.id
        LEFT JOIN группы g ON p.группа_id = g.id
        ORDER BY p.дата_платежа DESC
    ");
    $payments = $stmt->fetchAll();
} catch (PDOException $e) {
    $payments = [];
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="/CSS/main.css">
    <style>
        .action-link {
            color: #0897da;
            text-decoration: none;
            margin-right: 10px;
            font-weight: 600;
        }

        .action-link:hover {
            text-decoration: underline;
        }

        .delete-link {
            color: #c62828;
        }

        .delete-link:hover {
            color: #b71c1c;
        }

        .admin-dashboard {
            padding: 20px 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: #0897da;
            font-family: "Playpen Sans", cursive;
        }

        .stat-label {
            color: #555;
            margin-top: 6px;
            font-family: "Playpen Sans", cursive;
        }

        /* Стили для кнопки "Добавить пользователя" */
        .add-user-section {
            display: flex;
            justify-content: center; /* Центрируем кнопку */
            margin: 20px 0; /* Отступы сверху и снизу */
        }

        .btn-add-user {
            background: #0897da; /* Основной цвет фона */
            color: white; /* Цвет текста */
            border: none; /* Убираем границу */
            padding: 14px 28px; /* Внутренние отступы */
            border-radius: 8px; /* Закругление углов */
            font-weight: 600; /* Жирный шрифт */
            cursor: pointer; /* Курсор указателя */
            font-size: 16px; /* Размер шрифта */
            font-family: "Playpen Sans", cursive; /* Шрифт */
            transition: background 0.3s, transform 0.2s; /* Плавные переходы */
            text-decoration: none; /* Убираем подчеркивание, если это ссылка */
            display: inline-block; /* Позволяет задать ширину и высоту */
        }

        .btn-add-user:hover {
            background: #0066b2; /* Более темный фон при наведении */
            transform: scale(1.02); /* Легкое увеличение при наведении */
        }

        .users-section {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .section-title {
            font-size: 24px;
            color: #00334d;
            font-weight: 700;
            font-family: "Playpen Sans", cursive;
            margin: 0;
        }

        .toggle-icon {
            font-size: 20px;
            transition: transform 0.3s;
        }

        .users-search {
            margin-bottom: 15px;
            display: none; /* Скрыт по умолчанию */
        }

        .users-search input {
            font-size: 16px;
            padding: 10px 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            width: 100%;
            box-sizing: border-box;
        }

        .users-search input:focus {
            outline: none;
            border-color: #81d4fa;
            box-shadow: 0 0 0 2px rgba(129, 212, 250, 0.3);
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            display: none; /* Скрыт по умолчанию */
        }

        .users-table th,
        .users-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-family: "Playpen Sans", cursive;
        }

        .users-table th {
            background-color: #f0f7fc;
            font-weight: 600;
        }

        .user-active { color: #2e7d32; }
        .user-inactive { color: #c62828; }

        .user-type-instructor { color: #0066b2; }
        .user-type-student { color: #2e7d32; }
        .user-type-admin { color: #c62828; }

        /* Адаптивность */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .toggle-icon {
                align-self: flex-end;
            }
            .users-table, .users-table thead, .users-table tbody, .users-table th, .users-table td, .users-table tr {
                display: block;
            }
            .users-table tr {
                border: 1px solid #ccc;
                margin-bottom: 10px;
                border-radius: 8px;
                padding: 10px;
            }
            .users-table td {
                border: none;
                position: relative;
                padding-left: 35% !important;
            }
            .users-table td:before {
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
        <div class="container">
            <div class="admin-dashboard">
                <h1>Панель администратора</h1>

                <!-- Кнопка выхода -->
                <div style="text-align: right; margin-bottom: 20px;">
                    <a href="/logout.php" class="btn-add-user" 
                    style="background: #c62828; padding: 8px 16px; font-size: 14px; text-decoration: none; display: inline-block;">
                        Выйти
                    </a>
                </div>

                <!-- Статистика -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $total_students; ?></div>
                        <div class="stat-label">Учеников</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $total_instructors; ?></div>
                        <div class="stat-label">Инструкторов</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $total_groups; ?></div>
                        <div class="stat-label">Групп</div>
                    </div>
                </div>

                <!-- Кнопка "Добавить пользователя" -->
                <div class="add-user-section">
                    <a href="/admin/add_user.php" class="btn-add-user">Добавить пользователя</a>
                </div>

                <!-- Раздел с пользователями -->
                <div class="users-section">
                    <div class="section-header" id="usersHeader">
                        <h2 class="section-title">Список пользователей</h2>
                        <span class="toggle-icon">+</span>
                    </div>
                    <!-- Поле поиска внутри секции -->
                    <div class="users-search" id="usersSearchContainer" style="display: none;">
                        <input type="text" id="usersSearch" placeholder="Поиск по ФИО, Email, Роли...">
                    </div>
                    <table class="users-table" id="usersTable">
                        <thead>
                            <tr>
                                <th>ФИО</th>
                                <th>Телефон</th>
                                <th>Email</th>
                                <th>Роль</th>
                                <th>Доп. инфо</th>
                                <th>Статус</th>
                                <th>Дата регистрации</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($users): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr class="user-row">
                                        <td data-label="ФИО"><?php echo htmlspecialchars($user['фио']); ?></td>
                                        <td data-label="Телефон"><?php echo htmlspecialchars($user['телефон']); ?></td>
                                        <td data-label="Email"><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td data-label="Роль"><?php echo htmlspecialchars($user['role_name']); ?></td>
                                        <td data-label="Доп. инфо"><?php echo $user['details'] !== null ? htmlspecialchars($user['details']) : ''; ?></td>
                                        <td data-label="Статус"><span class="<?php echo $user['активен'] ? 'user-active' : 'user-inactive'; ?>"><?php echo $user['активен'] ? 'Активен' : 'Неактивен'; ?></span></td>
                                        <td data-label="Дата регистрации"><?php echo date('d.m.Y H:i', strtotime($user['дата_регистрации'])); ?></td>


                                        <td data-label="Действия">
                                        <a href="/admin/edit_user.php?id=<?= $user['user_id'] ?>" class="action-link">Редактировать</a>
                                        <?php if ($user['user_type'] !== 'админ' || $user['user_id'] != $_SESSION['user_id']): ?>
                                            <a href="/admin/delete_user.php?id=<?= $user['user_id'] ?>" 
                                            class="action-link delete-link"
                                            onclick="return confirm('Удалить пользователя <?= addslashes($user['фио']) ?>?')">
                                                Удалить
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    </tr>

                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">Пользователи не найдены.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Кнопка "Добавить группу" -->
                <div class="add-user-section">
                    <a href="/admin/add_group.php" class="btn-add-user">Добавить группу</a>
                </div>

                <!-- Раздел с группами -->
                <div class="users-section">
                    <div class="section-header" id="groupsHeader">
                        <h2 class="section-title">Список групп</h2>
                        <span class="toggle-icon">+</span>
                    </div>
                    <div class="users-search" id="groupsSearchContainer" style="display: none;">
                        <input type="text" id="groupsSearch" placeholder="Поиск по названию, категории, инструктору...">
                    </div>
                    <table class="users-table" id="groupsTable" style="display: none;">
                        <thead>
                            <tr>
                                <th>Название</th>
                                <th>Категория</th>
                                <th>Инструктор</th>
                                <th>Статус</th>
                                <th>Дата начала</th>
                                <th>Дата окончания</th>
                                <th>Действия</th> <!-- Новая колонка -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $stmt = $pdo->query("
                                    SELECT 
                                        г.id,
                                        г.название,
                                        г.категория,
                                        г.статус,
                                        г.дата_начала,
                                        г.дата_окончания,
                                        u.фио as инструктор_фио
                                    FROM группы г
                                    LEFT JOIN инструкторы ин ON г.инструктор_id = ин.id
                                    LEFT JOIN пользователи u ON ин.пользователь_id = u.id
                                    ORDER BY г.дата_начала DESC
                                ");
                                $groups = $stmt->fetchAll();
                                foreach ($groups as $group):
                            ?>
                                <tr class="group-row">
                                    <td data-label="Название"><?= htmlspecialchars($group['название']) ?></td>
                                    <td data-label="Категория"><?= htmlspecialchars($group['категория']) ?></td>
                                    <td data-label="Инструктор"><?= htmlspecialchars($group['инструктор_фио'] ?? '—') ?></td>
                                    <td data-label="Статус"><?= htmlspecialchars($group['статус']) ?></td>
                                    <td data-label="Дата начала"><?= $group['дата_начала'] ? date('d.m.Y', strtotime($group['дата_начала'])) : '—' ?></td>
                                    <td data-label="Дата окончания"><?= $group['дата_окончания'] ? date('d.m.Y', strtotime($group['дата_окончания'])) : '—' ?></td>
                                    <td data-label="Действия">
                                        <a href="/admin/edit_group.php?id=<?= $group['id'] ?>" class="action-link">Редактировать</a>
                                        <a href="/admin/delete_group.php?id=<?= $group['id'] ?>" 
                                        class="action-link delete-link"
                                        onclick="return confirm('Удалить группу <?= addslashes(htmlspecialchars($group['название'])) ?>?\\n\\n⚠️ Все связанные занятия и зачисления будут удалены!')">
                                            Удалить
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach;
                            } catch (PDOException $e) { ?>
                                <tr>
                                    <td colspan="7">Ошибка загрузки групп</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>


                <!-- Кнопка перехода в историю -->
                <div class="add-user-section">
                    <a href="/admin/request_history.php" class="btn-add-user">История заявок</a>
                </div>

                <!-- Раздел с новыми заявками -->
                <div class="users-section">
                    <div class="section-header" id="requestsHeader">
                        <h2 class="section-title">Новые заявки</h2>
                        <span class="toggle-icon">+</span>
                    </div>
                    <div class="users-search" id="requestsSearchContainer" style="display: none;">
                        <input type="text" id="requestsSearch" placeholder="Поиск по ФИО, телефону, email, категории...">
                    </div>
                    <table class="users-table" id="requestsTable" style="display: none;">
                        <thead>
                            <tr>
                                <th>ФИО</th>
                                <th>Телефон</th>
                                <th>Email</th>
                                <th>Категория</th>
                                <th>Дата</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                // Только НОВЫЕ заявки
                                $stmt = $pdo->query("
                                    SELECT id, фио, телефон, email, категория, дата_заявки
                                    FROM заявки
                                    WHERE статус = 'новая'
                                    ORDER BY дата_заявки DESC
                                ");
                                $requests = $stmt->fetchAll();
                                if ($requests):
                                    foreach ($requests as $req):
                            ?>
                                <tr class="request-row">
                                    <td data-label="ФИО"><?= htmlspecialchars($req['фио']) ?></td>
                                    <td data-label="Телефон"><?= htmlspecialchars($req['телефон']) ?></td>
                                    <td data-label="Email"><?= htmlspecialchars($req['email'] ?? '—') ?></td>
                                   <td data-label="Категория"><?= htmlspecialchars($req['категория']) ?></td>
                                    <td data-label="Дата"><?= date('d.m.Y H:i', strtotime($req['дата_заявки'])) ?></td>
                                    <td data-label="Действия">
                                        <form method="POST" action="admin.php" style="display:inline;">
                                            <input type="hidden" name="mark_processed" value="<?= $req['id'] ?>">
                                            <button type="submit" class="action-link" style="background:none; border:none; padding:0; cursor:pointer; color:#0897da;">
                                                Отметить как обработанную
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php
                                    endforeach;
                                else:
                            ?>
                                <tr>
                                    <td colspan="6" style="text-align:center;">Нет новых заявок</td>
                                </tr>
                            <?php
                                endif;
                            } catch (PDOException $e) { ?>
                                <tr>
                                    <td colspan="6">Ошибка загрузки заявок</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Кнопка истории обращений -->
                <div class="add-user-section">
                    <a href="/admin/contact_history.php" class="btn-add-user">История обращений</a>
                </div>

                <!-- Раздел с новыми обращениями -->
                <div class="users-section">
                    <div class="section-header" id="contactsHeader">
                        <h2 class="section-title">Новые обращения</h2>
                        <span class="toggle-icon">+</span>
                    </div>
                    <div class="users-search" id="contactsSearchContainer" style="display: none;">
                        <input type="text" id="contactsSearch" placeholder="Поиск по ФИО, телефону, сообщению...">
                    </div>
                    <table class="users-table" id="contactsTable" style="display: none;">
                        <thead>
                            <tr>
                                <th>ФИО</th>
                                <th>Телефон</th>
                                <th>Email</th>
                                <th>Сообщение</th>
                                <th>Дата</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $stmt = $pdo->query("
                                    SELECT id, фио, телефон, email, сообщение, дата
                                    FROM обращения
                                    WHERE отвечено = 0
                                    ORDER BY дата DESC
                                ");
                                $contacts = $stmt->fetchAll();
                                if ($contacts):
                                    foreach ($contacts as $c):
                            ?>
                                <tr class="contact-row">
                                    <td data-label="ФИО"><?= htmlspecialchars($c['фио']) ?></td>
                                    <td data-label="Телефон"><?= htmlspecialchars($c['телефон']) ?></td>
                                    <td data-label="Email"><?= htmlspecialchars($c['email'] ?? '—') ?></td>
                                    <td data-label="Сообщение" style="white-space: pre-wrap; word-break: break-word; vertical-align: top; padding: 12px 8px; font-size: 14px; line-height: 1.5; background: none; border: none; text-align: left; min-width: 350px;">
                                        <?= nl2br(htmlspecialchars($c['сообщение'])) ?>
                                    </td>
                                    <td data-label="Дата"><?= date('d.m.Y H:i', strtotime($c['дата'])) ?></td>
                                    <td data-label="Действия">
                                        <form method="POST" action="admin.php" style="display:inline;">
                                            <input type="hidden" name="mark_answered" value="<?= $c['id'] ?>">
                                            <button type="submit" class="action-link" style="background:none; border:none; padding:0; cursor:pointer; color:#0897da;">
                                                Отметить как отвеченное
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php
                                    endforeach;
                                else:
                            ?>
                                <tr>
                                    <td colspan="6" style="text-align:center;">Нет новых обращений</td>
                                </tr>
                            <?php
                                endif;
                            } catch (PDOException $e) { ?>
                                <tr>
                                    <td colspan="6">Ошибка загрузки обращений</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Кнопка добавления платежа -->
                <div class="add-user-section">
                    <a href="/admin/add_payment.php" class="btn-add-user">Добавить платёж</a>
                </div>

                <!-- Раздел с платежами -->
                <div class="users-section">
                    <div class="section-header" id="paymentsHeader">
                        <h2 class="section-title">Платежи</h2>
                        <span class="toggle-icon">+</span>
                    </div>
                    <div class="users-search" id="paymentsSearchContainer" style="display: none;">
                        <input type="text" id="paymentsSearch" placeholder="Поиск по ученику, сумме, способу...">
                    </div>
                    <table class="users-table" id="paymentsTable" style="display: none;">
                        <thead>
                            <tr>
                                <th>Ученик</th>
                                <th>Группа</th>
                                <th>Сумма</th>
                                <th>Дата</th>
                                <th>Способ</th>
                                <th>Статус</th>
                                <th>Действия</th> <!-- ← Только ЗАГОЛОВОК, без $pay! -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($payments): ?>
                                <?php foreach ($payments as $pay): ?>
                                    <tr class="payment-row">
                                        <td data-label="Ученик"><?= htmlspecialchars($pay['ученик_фио']) ?></td>
                                        <td data-label="Группа"><?= htmlspecialchars($pay['группа_название'] ?? '—') ?></td>
                                        <td data-label="Сумма"><?= number_format($pay['сумма'], 2, ',', ' ') ?> ₽</td>
                                        <td data-label="Дата"><?= date('d.m.Y H:i', strtotime($pay['дата_платежа'])) ?></td>
                                        <td data-label="Способ"><?= htmlspecialchars($pay['способ']) ?></td>
                                        <td data-label="Статус">
                                            <?php
                                            $statusColors = [
                                                'ожидает' => '#d32f2f',
                                                'оплачено' => '#2e7d32',
                                                'отменено' => '#555'
                                            ];
                                            $color = $statusColors[$pay['статус']] ?? '#000';
                                            ?>
                                            <span style="color: <?= $color ?>; font-weight: 600;">
                                                <?= htmlspecialchars($pay['статус']) ?>
                                            </span>
                                        </td>
                                        <td data-label="Действия">
                                            <a href="/admin/edit_payment.php?id=<?= $pay['id'] ?>" class="action-link">Редактировать</a>
                                            <a href="/admin/delete_payment.php?id=<?= $pay['id'] ?>" 
                                            class="action-link delete-link"
                                            onclick="return confirm('Удалить платёж на <?= number_format($pay['сумма'], 2, ',', ' ') ?> ₽?')">
                                                Удалить
                                            </a>
                                            <?php if ($pay['статус'] === 'оплачено'): ?>
                                                <button type="button" class="action-link print-receipt" data-payment="<?= htmlspecialchars(json_encode([
                                                    'id' => $pay['id'],
                                                    'фио' => $pay['ученик_фио'],
                                                    'сумма' => $pay['сумма'],
                                                    'дата' => $pay['дата_платежа'],
                                                    'способ' => $pay['способ'],
                                                    'группа' => $pay['группа_название'] ?? '—'
                                                ])) ?>">
                                                    Печать чека
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align:center;">Платежи не найдены</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
        <?php require_once __DIR__ . '/../blocks/footer.php'; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.getElementById('usersHeader');
            const table = document.getElementById('usersTable');
            const icon = header.querySelector('.toggle-icon');
            const searchContainer = document.getElementById('usersSearchContainer');
            const searchInput = document.getElementById('usersSearch');

            // Функция для переключения отображения таблицы и поиска
            function toggleTableAndSearch() {
                if (table.style.display === 'table') {
                    table.style.display = 'none';
                    searchContainer.style.display = 'none';
                    icon.textContent = '+';
                } else {
                    table.style.display = 'table';
                    searchContainer.style.display = 'block'; // Показываем поиск при открытии
                    icon.textContent = '−';
                }
            }

            header.addEventListener('click', toggleTableAndSearch);

            // Функция поиска
            searchInput.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                const rows = document.querySelectorAll('.user-row'); // Правильный селектор

                rows.forEach(function(row) {
                    let match = false;

                    // Перебираем все ячейки в строке
                    for (let cell of row.cells) {
                        const cellText = cell.textContent.toLowerCase();
                        if (cellText.includes(query)) {
                            match = true;
                            break; // Если нашли совпадение в одной ячейке, можно прекращать
                        }
                    }

                    // Показываем или скрываем строку
                    row.style.display = match ? '' : 'none';
                });
            });
        });

        // --- Управление группами ---
            const groupsHeader = document.getElementById('groupsHeader');
            const groupsTable = document.getElementById('groupsTable');
            const groupsIcon = groupsHeader.querySelector('.toggle-icon');
            const groupsSearchContainer = document.getElementById('groupsSearchContainer');
            const groupsSearchInput = document.getElementById('groupsSearch');

            function toggleGroups() {
                if (groupsTable.style.display === 'table') {
                    groupsTable.style.display = 'none';
                    groupsSearchContainer.style.display = 'none';
                    groupsIcon.textContent = '+';
                } else {
                    groupsTable.style.display = 'table';
                    groupsSearchContainer.style.display = 'block';
                    groupsIcon.textContent = '−';
                }
            }

            groupsHeader.addEventListener('click', toggleGroups);

            groupsSearchInput.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                const rows = document.querySelectorAll('.group-row');
                rows.forEach(row => {
                    let match = false;
                    for (let cell of row.cells) {
                        if (cell.textContent.toLowerCase().includes(query)) {
                            match = true;
                            break;
                        }
                    }
                    row.style.display = match ? '' : 'none';
                });
            });

            // --- Управление заявками ---
            const requestsHeader = document.getElementById('requestsHeader');
            const requestsTable = document.getElementById('requestsTable');
            const requestsIcon = requestsHeader.querySelector('.toggle-icon');
            const requestsSearchContainer = document.getElementById('requestsSearchContainer');
            const requestsSearchInput = document.getElementById('requestsSearch');

            function toggleRequests() {
                if (requestsTable.style.display === 'table') {
                    requestsTable.style.display = 'none';
                    requestsSearchContainer.style.display = 'none';
                    requestsIcon.textContent = '+';
                } else {
                    requestsTable.style.display = 'table';
                    requestsSearchContainer.style.display = 'block';
                    requestsIcon.textContent = '−';
                }
            }

            requestsHeader.addEventListener('click', toggleRequests);

            requestsSearchInput.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                const rows = document.querySelectorAll('.request-row');
                rows.forEach(row => {
                    let match = false;
                    for (let cell of row.cells) {
                        if (cell.textContent.toLowerCase().includes(query)) {
                            match = true;
                            break;
                        }
                    }
                    row.style.display = match ? '' : 'none';
                });
            });
            // --- Управление обращениями ---
            const contactsHeader = document.getElementById('contactsHeader');
            const contactsTable = document.getElementById('contactsTable');
            const contactsIcon = contactsHeader.querySelector('.toggle-icon');
            const contactsSearchContainer = document.getElementById('contactsSearchContainer');
            const contactsSearchInput = document.getElementById('contactsSearch');

            function toggleContacts() {
                if (contactsTable.style.display === 'table') {
                    contactsTable.style.display = 'none';
                    contactsSearchContainer.style.display = 'none';
                    contactsIcon.textContent = '+';
                } else {
                    contactsTable.style.display = 'table';
                    contactsSearchContainer.style.display = 'block';
                    contactsIcon.textContent = '−';
                }
            }

            contactsHeader?.addEventListener('click', toggleContacts);

            contactsSearchInput?.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                const rows = document.querySelectorAll('.contact-row');
                rows.forEach(row => {
                    let match = false;
                    for (let cell of row.cells) {
                        if (cell.textContent.toLowerCase().includes(query)) {
                            match = true;
                            break;
                        }
                    }
                    row.style.display = match ? '' : 'none';
                });
            });
            // --- Управление платежами ---
            const paymentsHeader = document.getElementById('paymentsHeader');
            const paymentsTable = document.getElementById('paymentsTable');
            const paymentsIcon = paymentsHeader?.querySelector('.toggle-icon');
            const paymentsSearchContainer = document.getElementById('paymentsSearchContainer');
            const paymentsSearchInput = document.getElementById('paymentsSearch');

            function togglePayments() {
                if (paymentsTable.style.display === 'table') {
                    paymentsTable.style.display = 'none';
                    paymentsSearchContainer.style.display = 'none';
                    paymentsIcon.textContent = '+';
                } else {
                    paymentsTable.style.display = 'table';
                    paymentsSearchContainer.style.display = 'block';
                    paymentsIcon.textContent = '−';
                }
            }

            paymentsHeader?.addEventListener('click', togglePayments);

            paymentsSearchInput?.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                const rows = document.querySelectorAll('.payment-row');
                rows.forEach(row => {
                    let match = false;
                    for (let cell of row.cells) {
                        if (cell.textContent.toLowerCase().includes(query)) {
                            match = true;
                            break;
                        }
                    }
                    row.style.display = match ? '' : 'none';
                });
            });
            // Печать чека
            document.querySelectorAll('.print-receipt').forEach(btn => {
                btn.addEventListener('click', function() {
                    const data = JSON.parse(this.getAttribute('data-payment'));

                    // Форматируем дату
                    const date = new Date(data.дата);
                    const formattedDate = date.toLocaleString('ru-RU', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    // Форматируем сумму
                    const formattedSum = parseFloat(data.сумма).toLocaleString('ru-RU', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });

                    // HTML чека
                    const receiptHtml = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>Чек №${data.id}</title>
                            <style>
                                body {
                                    font-family: monospace;
                                    max-width: 400px;
                                    margin: 0 auto;
                                    padding: 20px;
                                    line-height: 1.4;
                                    font-size: 14px;
                                }
                                .receipt-header {
                                    text-align: center;
                                    border-bottom: 2px dashed #000;
                                    padding-bottom: 10px;
                                    margin-bottom: 15px;
                                }
                                .receipt-title {
                                    font-weight: bold;
                                    font-size: 18px;
                                }
                                .receipt-line {
                                    display: flex;
                                    justify-content: space-between;
                                }
                                .receipt-total {
                                    font-weight: bold;
                                    font-size: 16px;
                                    margin-top: 10px;
                                    border-top: 1px solid #000;
                                    padding-top: 5px;
                                }
                                .receipt-footer {
                                    margin-top: 20px;
                                    text-align: center;
                                    font-style: italic;
                                    font-size: 12px;
                                }
                            </style>
                        </head>
                        <body>
                            <div class="receipt-header">
                                <div class="receipt-title">Автошкола «LADA»</div>
                                <div>г. Балашов, пер. Вокзальный, 6</div>
                                <div>ИНН: 111111111111</div>
                            </div>

                            <div class="receipt-line">
                                <span>Чек №</span>
                                <span>${data.id}</span>
                            </div>
                            <div class="receipt-line">
                                <span>Дата:</span>
                                <span>${formattedDate}</span>
                            </div>
                            <div class="receipt-line">
                                <span>Ученик:</span>
                                <span>${data.фио}</span>
                            </div>
                            <div class="receipt-line">
                                <span>Группа:</span>
                                <span>${data.группа}</span>
                            </div>
                            <div class="receipt-line">
                                <span>Способ:</span>
                                <span>${data.способ}</span>
                            </div>

                            <div class="receipt-total">
                                <div class="receipt-line">
                                    <span>ИТОГО:</span>
                                    <span>${formattedSum} ₽</span>
                                </div>
                            </div>

                            <div class="receipt-footer">
                                Спасибо за обучение в автошколе «LADA»!
                            </div>
                        </body>
                        </html>
                    `;

                    // Открываем новое окно и печатаем
                    const printWindow = window.open('', '_blank');
                    printWindow.document.write(receiptHtml);
                    printWindow.document.close();
                    printWindow.focus();
                    printWindow.print();
                    // printWindow.close(); // закомментировано, чтобы браузер не блокировал
                });
            });
    </script>
</body>
</html>