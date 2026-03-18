<!DOCTYPE html>
<html lang="ru">
    
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>LADA</title>
        <link rel="stylesheet" href="/CSS/main.css">
        <style>
            .courses h2 {
                padding-top: 80px;
            }
        </style>
    </head>

    <body>
        <div class="wrapper">
            <!-- Блок "шапка" -->
            <?php require_once "blocks/header.php";?>


            <!-- Блок "курсы" -->
            <section class="courses container">
                <h2>Сдай на права с первого раза</h2>
                <p class="subtitle">Готовим водителей <strong>всех категорий</strong>:
                    <span class="category-badge">A</span>
                    <span class="category-badge">B</span>
                    <span class="category-badge">C</span>
                    <span class="category-badge">D</span>
                    <span class="category-badge">E</span>
                    <span class="category-badge">A1</span>
                    <span class="category-badge">M</span>
                </p>

                
            <!-- Сетка карточек -->
            <div class="courses-grid">
                <!-- Курс A -->
                <div class="course-card">
                    <img src="/img/A.png" alt="Мотоцикл">
                    <div class="course-content">
                        <h3>Права категории "A"</h3>
                        <p>Научись управлять мотоциклом</p>
                            <div class="course-buttons">
                                <button class="btn-primary" data-category="A">Записаться</button>
                                <button class="btn-details">Подробнее</button>
                            </div>
                        </div>
                </div>

                    <!-- Курс B -->
                    <div class="course-card">
                        <img src="/img/B.png" alt="Легковой автомобиль">
                        <div class="course-content">
                            <h3>Права категории "B"</h3>
                            <p>Сядь за руль легкового автомобиля уже сегодня</p>
                                <div class="course-buttons">
                                <button class="btn-primary" data-category="B">Записаться</button>
                                <button class="btn-details">Подробнее</button>
                            </div>
                        </div>
                    </div>

                    <!-- Курс C -->
                    <div class="course-card">
                        <img src="/img/C.png" alt="Грузовик">
                        <div class="course-content">
                            <h3>Права категории "C"</h3>
                            <p>Стать уверенным водителем грузовика</p>
                                <div class="course-buttons">
                                <button class="btn-primary" data-category="C">Записаться</button>
                                <button class="btn-details">Подробнее</button>
                            </div>
                        </div>
                    </div>

                    <!-- Курс D -->
                    <div class="course-card">
                        <img src="/img/D.png" alt="">
                        <div class="course-content">
                            <h3>Права категории "D"</h3>
                            <p>Безопасно управляй пассажирским транспортом</p>
                                <div class="course-buttons">
                                <button class="btn-primary" data-category="D">Записаться</button>
                                <button class="btn-details">Подробнее</button>
                            </div>
                        </div>
                    </div>

                    <!-- Курс M -->
                    <div class="course-card">
                        <img src="/img/M.png" alt="">
                        <div class="course-content">
                            <h3>Права категории "M"</h3>
                            <p>Сядь на мопед в 16 лет</p>
                                <div class="course-buttons">
                                <button class="btn-primary" data-category="M">Записаться</button>
                                <button class="btn-details">Подробнее</button>
                            </div>
                        </div>
                    </div>

                    <!-- Курс A1 -->
                    <div class="course-card">
                        <img src="/img/A1.png" alt="">
                        <div class="course-content">
                            <h3>Права категории "A1"</h3>
                            <p>Освой управление лёгким
                                мотоциклом, мопедом или
                                трициклом
                            </p>
                                <div class="course-buttons">
                                <button class="btn-primary" data-category="A1">Записаться</button>
                                <button class="btn-details">Подробнее</button>
                            </div>
                        </div>
                    </div>


                </div>
            </section>

            <!-- Блок "Fotter меню" -->
            <?php require_once "blocks/footer.php";?>

        </div>
        
        <!-- Модальное окно (одно на обе кнопки) -->
        <?php require_once "blocks/modal_okno.php";?>
    </body>

</html>