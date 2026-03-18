<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'админ') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../database/db.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("Неверный ID");
}

if ($id == $_SESSION['user_id']) {
    die("Нельзя удалить себя");
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("DELETE FROM пользователи WHERE id = ?");
    $stmt->execute([$id]);

    $pdo->commit();
    header('Location: /cabinet/admin.php#users');
    exit;

} catch (Exception $e) {
    $pdo->rollback();
    die("Ошибка удаления: " . $e->getMessage());
}
?>