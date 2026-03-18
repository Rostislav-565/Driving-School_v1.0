<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'админ') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../database/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /cabinet/admin.php#groups');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM группы WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: /cabinet/admin.php#groups');
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = "Невозможно удалить группу: возможно, есть связанные занятия или ученики.";
    header('Location: /cabinet/admin.php#groups');
    exit;
}