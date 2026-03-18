<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isAuth = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? null;
?>

<header class="container">
    <a class="lada" href="/">LADA</a>
    <nav>
        <ul>
            <li><a href="/course.php">Курсы</a></li>
            <li><a href="/faq.php">FAQ</a></li>
            <li><a href="/reviews.php">Отзывы</a></li>
            <li><a href="/contact.php">Контакты</a></li>
            <?php if ($isAuth): ?>
                <li class="btn">
                    <?php if ($role === 'ученик'): ?>
                        <a href="/cabinet/student.php">Личный кабинет</a>
                    <?php elseif ($role === 'инструктор'): ?>
                        <a href="/cabinet/instructor.php">Кабинет</a>
                    <?php elseif ($role === 'админ'): ?>
                        <a href="/cabinet/admin.php">Панель</a>
                    <?php endif; ?>
                </li>
            <?php else: ?>
                <li class="btn"><a href="/login.php">Личный кабинет</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>