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

            <!-- Блок "FAQ" -->
            <section class="faq-section">
                <div class="container">
                    <h2 class="faq-title">Часто задаваемые вопросы</h2>
                    <div class="faq-list">
                        <details class="faq-item">
                            <summary>Сколько длится обучение?</summary>
                            <p>Полный курс занимает от 2 до 3 месяцев — 
                            в зависимости от интенсивности занятий. 
                            Теория проходит параллельно с практикой, 
                            чтобы вы могли сразу применять знания
                            на дороге.</p>
                        </details>

                        <details class="faq-item">
                            <summary>Нужно ли иметь медицинскую справку при записи?</summary>
                                <p>
                                Медицинскую справку можно принести позже
                                — на первое занятие она не обязательна. 
                                Но без неё вы не сможете сдавать экзамен 
                                в ГИБДД, так что лучше оформить её в 
                                течение первой недели обучения.</p>
                        </details>

                        <details class="faq-item">
                            <summary>Можно ли оплатить обучение частями?</summary>
                                <p>
                                    Да! Мы предлагаем удобную рассрочку без процентов:
                                    50% при зачислении, 50% — перед началом 
                                    практических занятий.
                                </p>
                        </details>

                        <details class="faq-item">
                            <summary>Сколько раз можно сдавать внутренний экзамен?</summary>
                            <p>
                            Без ограничений! Сдаем до тех пор, пока вы 
                            не почувствуете уверенность. Главное — усвоить
                            материал, а не просто «отстреляться».</p>
                        </details>

                        <details class="faq-item">
                            <summary>Подаете ли вы документы в ГИБДД за ученика?</summary>
                                <p>
                                Да, мы полностью сопровождаем вас: от 
                                подачи заявления до получения водительского 
                                удостоверения. Вам не нужно стоять в очередях — 
                                этим занимаемся мы.
                                </p>
                        </details>
                    </div>
                </div>
            </section>

            <!-- Блок "Fotter меню" -->
            <?php require_once "blocks/footer.php";?>
        </div>

    </body>

</html>