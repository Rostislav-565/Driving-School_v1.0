<!DOCTYPE html>
<html lang="ru">
    
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>LADA — Часто задаваемые вопросы</title>
        <link rel="stylesheet" href="/CSS/main.css">
    </head>

    <body>

        <div class="wrapper">
            <!-- Блок "шапка" -->
            <?php require_once "blocks/header.php";?>

            <!-- Блок "Отзывов" -->
            <section class="reviews-section">
                <div class="container">
                    <h2 class="rewiews-title">Отзывы наших учеников</h2>
                    <div class="reviews-grid">

                        <div class="review-card">
                            <div class="review-header">
                                <img src="/img/account.png" alt="Анна К.">
                                <div>
                                    <h3>Анна К.</h3>
                                    <p class="review-date">Июнь 2025</p>
                                </div>
                            </div>
                            <p class="review-text">Записалась с нулевым опытом — боялась даже сесть за руль. Инструктор Алексей терпеливо объяснял всё по 10 раз, шутил, поддерживал. Сдала с первого раза! Спасибо за уверенность и добрую атмосферу.</p>
                        </div>

                        <div class="review-card">
                            <div class="review-header">
                                <img src="/img/account.png" alt="Дмитрий П.">
                                <div>
                                    <h3>Дмитрий П.</h3>
                                    <p class="review-date">Май 2025</p>
                                </div>
                            </div>
                            <p class="review-text">Не ожидал, что теория может быть интересной! Преподаватель связала ПДД с реальными ситуациями — теперь всё логично. Практика на современной машине, без давления. Отличная автошкола!</p>
                        </div>

                        <div class="review-card">
                            <div class="review-header">
                                <img src="/img/account.png" alt="Елена С.">
                                <div>
                                    <h3>Елена С.</h3>
                                    <p class="review-date">Апрель 2025</p>
                                </div>
                            </div>
                            <p class="review-text">Обучалась в декрете — индивидуальный график спас меня. Инструктор приезжал в удобное время, даже вечером. Очень благодарна за гибкость и профессионализм!</p>
                        </div>

                        <div class="review-card">
                            <div class="review-header">
                                <img src="/img/account.png" alt="Иван М.">
                                <div>
                                    <h3>Иван М.</h3>
                                    <p class="review-date">Март 2025</p>
                                </div>
                            </div>
                            <p class="review-text">Цены честные, без скрытых платежей. Всё прозрачно с самого начала. Машина в отличном состоянии, инструктор — как старший брат. Рекомендую!</p>
                        </div>

                        <div class="review-card">
                            <div class="review-header">
                                <img src="/img/account.png" alt="Марина Л.">
                                <div>
                                    <h3>Марина Л.</h3>
                                    <p class="review-date">Февраль 2025</p>
                                </div>
                            </div>
                            <p class="review-text">Спасибо за подачу автомобиля на экзамен! Это сняло половину стресса. А ещё за то, что не заставляют «платить за пересдачи». Чувствуешь, что тебя уважают.</p>
                        </div>

                        <div class="review-card">
                            <div class="review-header">
                                <img src="/img/account.png" alt="Сергей В.">
                                <div>
                                    <h3>Сергей В.</h3>
                                    <p class="review-date">Январь 2025</p>
                                </div>
                            </div>
                            <p class="review-text">Прошёл курс за 2 месяца — всё чётко по расписанию. Никаких задержек. Инструктор не кричал, а учил думать. Теперь езжу уверенно даже в пробках.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Блок "Fotter меню" -->
            <?php require_once "blocks/footer.php";?>
        </div>
    </body>
</html>