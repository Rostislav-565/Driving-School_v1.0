<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'админ') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../database/db.php';

$id = $_GET['id'] ?? null;
if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM платежи WHERE id = ?");
        $stmt->execute([$id]);
    } catch (Exception $e) {
        // Игнорируем ошибку или логируем
    }
}
header('Location: /cabinet/admin.php#payments');
exit;