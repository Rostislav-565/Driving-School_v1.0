<!DOCTYPE html>
<html lang="ru">
    
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>LADA</title>
        <link rel="stylesheet" href="/CSS/main.css">
        <style>
            .instructors-section,
            .teachers-section {
                padding: 60px 0;
            }

            .instructors-card,
            .teachers-card {
                background: #b3e5fc;
                border-radius: 30px;
                padding: 0;
                display: flex;
                flex-direction: column; /* На мобильных — колонка */
                max-width: 1200px;
                margin: 0 auto;
                box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
                overflow: hidden;
                width: 100%;
            }

            .instructors-content,
            .teachers-content {
                padding: 40px 20px;
                text-align: center;
            }

            .instructors-photo,
            .teachers-photo {
                height: 250px;
                background: #b3e5fc;
                display: flex;
                align-items: flex-end;
                justify-content: center;
                overflow: hidden;
            }

            .instructors-photo img,
            .teachers-photo img {
                width: 100%;
                height: 100%;
                object-fit: contain;
                object-position: center bottom;
            }

            /* На десктопах — в ряд */
            @media (min-width: 992px) {
                .instructors-card,
                .teachers-card {
                    flex-direction: row;
                }

                .instructors-content,
                .teachers-content {
                    padding: 80px 60px;
                    text-align: left;
                    flex: 1;
                }

                .instructors-photo,
                .teachers-photo {
                    flex: 1;
                    height: auto;
                }

                .instructors-photo img {
                    object-position: right bottom;
                }

                .teachers-photo img {
                    object-position: left bottom;
                }
            }
            </style>
    </head>

    <body>
        <div class="wrapper">
            <!-- Блок "шапка" -->
            <?php require_once "blocks/header.php";?>

            <!-- Блок "главное меню" -->
            <div class="hero container">
                <div class="hero--info">
                    <h1>Добро пожаловать в нашу автошколу! Узнайте больше о нас, наших ценностях и преимуществах обучения.</h1>
                    <p>Выберите подходящий курс обучения: от базовых навыков вождения до профессиональной подготовки водителей категории А, B и C.</p>
                    <a href="course.php" class="btn">Выбрать курс</a>
                </div>
                <img src="/img/lada_granta.png">
            </div>

            <!-- Блок "курсы" -->
            <section class="courses container">
                <h2>Сдай на права с первого раза</h2>
                <p class="subtitle">Готовим водителей <strong>всех категорий</strong>:
                    <span class="category-badge">A</span>
                    <span class="category-badge">B</span>
                    <span class="category-badge">C</span>
                    <span class="category-badge">D</span>
                    <span class="category-badge">A1</span>
                    <span class="category-badge">M</span>
                </p>

                <div class="see-all-container">
                    <a href="course.php" class="see-all">Посмотреть все</a>
                </div>
                
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
                </div>
            </section>

            <!-- Блок "инструкторы" -->
            <section class="instructors-section">
                <div class="container">
                    <div class="instructors-card">
                        <div class="instructors-content">
                            <span class="instructors-tag">Приятная компания</span>
                            <h2 class="instructors-title">Доброжелательные инструкторы</h2>
                            <p class="instructors-description">
                            Инструкторы «LADA» — это не просто учителя,
                            а ваши напарники на пути к водительскому
                            удостоверению.
                            Они не просто объясняют, как повернуть на перекрёстке, а
                            помогают преодолеть страх, найти ритм и
                            почувствовать машину. Даже если вы впервые
                            сели за руль — с ними вы не останетесь один
                            на один с дорогой.
                            </p>
                        </div>
                        <div class="instructors-photo">
                            <img src="/img/instructors.png" alt="Инструкторы автошколы LADA">
                        </div>
                    </div>
                </div>
            </section>

            <!-- Блок "инструкторы - 2" -->
            <section class="teachers-section">
                <div class="container">
                    <div class="teachers-card">
                        <div class="teachers-photo">
                            <img src="/img/instructors2.png" alt="Преподаватели автошколы LADA">
                        </div>
                        <div class="teachers-content">
                            <span class="teachers-tag">Системные знания</span>
                            <h2 class="teachers-title">Квалифицированные преподаватели</h2>
                            <p class="teachers-description">
                                Преподаватели автошколы «LADA» — эксперты 
                                в ПДД с актуальными знаниями.
                                Они регулярно проходят курсы повышения 
                                квалификации,
                                чтобы объяснять правила не как в билетах, 
                                а как в реальной жизни.
                                Ученики не зубрят — они понимают и 
                                уверенно применяют знания на экзамене и 
                                на дороге.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Блок "Качественная подготовка" -->
            <section class="quality-section">
                <h2 class="quality-title">Качественная подготовка по честной цене</h2>
                <div class="quality-wrapper">
                    <div class="quality-features">

                        <div class="feature-item">
                            <img src="/img/teoria.svg" alt="Теория">
                            <p>Теория онлайн<br>или оффлайн</p>
                        </div>

                        <div class="feature-item">
                            <img src="/img/trenerovka.svg" alt="Тренажеры">
                            <p>Тренировки<br>на автосимуляторах</p>
                        </div>

                        <div class="feature-item">
                            <img src="/img/auto_P.svg" alt="Подача автомобиля">
                            <p>Подача автомобиля<br>на экзамене в ГИБДД</p>
                        </div>

                        <div class="feature-item">
                            <img src="/img/praktika.svg" alt="Практика">
                            <p>Практика<br>на автодроме и в городе</p>
                        </div>

                        <div class="feature-item">
                            <img src="/img/benzin.svg" alt="Бензин">
                            <p>Бензин на все занятия</p>
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