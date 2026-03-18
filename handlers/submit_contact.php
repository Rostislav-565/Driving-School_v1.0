<?php
session_start();

// Подключаем базу данных
require_once __DIR__ . '/../database/db.php';

// Только POST-запросы
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Метод не разрешён');
}

// Получаем данные
$fio = trim($_POST['фио'] ?? '');
$phone = trim($_POST['телефон'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['сообщение'] ?? '');

// Валидация
if (!$fio || !$phone || !$message) {
    $_SESSION['contact_error'] = 'Заполните все обязательные поля';
    header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/contact.php');
    exit;
}

// Проверка email (если указан)
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['contact_error'] = 'Неверный формат email';
    header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/contact.php');
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO обращения (фио, телефон, email, сообщение, отвечено)
        VALUES (?, ?, ?, ?, 0)
    ");
    $stmt->execute([
        $fio,
        $phone,
        $email ?: null,
        $message
    ]);

    $pdo->commit();

    $_SESSION['contact_success'] = 'Ваше обращение отправлено. Мы свяжемся с вами в ближайшее время.';
    header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/contact.php');
    exit;

} catch (Exception $e) {
    $pdo->rollback();
    error_log("Ошибка обращения: " . $e->getMessage());
    $_SESSION['contact_error'] = 'Не удалось отправить обращение. Попробуйте позже.';
    header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/contact.php');
    exit;
}