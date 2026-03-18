<?php


header('Content-Type: application/json; charset=utf-8');

// Подключаем базу
require_once __DIR__ . '/../database/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Только POST-запросы']);
    exit;
}

$fio = trim($_POST['fio'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$category = trim($_POST['category'] ?? '');

// Валидация
if (!$fio || !$phone || !$category) {
    echo json_encode(['success' => false, 'message' => 'Заполните все обязательные поля']);
    exit;
}

if (!in_array($category, ['A', 'A1', 'B', 'C', 'D', 'M'])) {
    echo json_encode(['success' => false, 'message' => 'Неверная категория']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO заявки (фио, телефон, email, категория, статус)
        VALUES (?, ?, ?, ?, 'новая')
    ");
    $stmt->execute([$fio, $phone, $email ?: null, $category]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("Ошибка заявки: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка при сохранении заявки']);
}