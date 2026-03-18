

<!DOCTYPE html>
<html lang="ru">
    
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>LADA — Часто задаваемые вопросы</title>
        <link rel="stylesheet" href="/CSS/main.css">
        <style>
           .contact-content {
                display: flex;
                gap: 40px;
                align-items: flex-start;
                flex-wrap: wrap;
            }

            .contact-map {
                flex: 1;
                min-width: 300px;
                height: auto;
            }

            .contact-info-form {
                flex: 1;
                min-width: 300px;
                display: flex;
                flex-direction: column;
                gap: 30px;
            }

            .contact-info h3,
            .contact-form h3 {
                font-size: 24px;
                color: #0d4561;
                margin-bottom: 20px;
                font-weight: 700;
            }

            .contact-form {
                background: #fff;
                padding: 24px;
                border-radius: 16px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            }

            .form-group input,
            .form-group textarea {
                width: 100%;
                padding: 12px 16px;
                border: 1px solid #ccc;
                border-radius: 8px;
                font-family: "Playpen Sans", cursive;
                font-size: 16px;
                box-sizing: border-box;
            }

            .contact-btn {
                width: 100%;
                padding: 14px;
                background: #0897da;
                color: #fff;
                border: none;
                border-radius: 8px;
                font-family: "Playpen Sans", cursive;
                font-size: 18px;
                font-weight: 600;
                cursor: pointer;
                transition: background 0.3s;
            }

            .contact-btn:hover {
                background: #0066b2;
            }

            /* Адаптивность: на мобильных — карта сверху */
            @media (max-width: 768px) {
                .contact-content {
                    flex-direction: column;
                }

                .contact-map {
                    width: 100%;
                    padding-bottom: 75%; /* для сохранения пропорций */
                    height: 0;
                    position: relative;
                }

                .contact-map > div {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                }
            }
        </style>
    </head>

    <body>

        <div class="wrapper">
            <!-- Блок "шапка" -->
            <?php require_once "blocks/header.php";?>

            <!-- Блок "Контакты" -->
            <section class="contact-section">
                <div class="container">
                    <h2 class="contact-title">Контакты</h2>

                    <div class="contact-content">
                        <div class="contact-map">
                            <div style="position: relative; padding-bottom: 125%; height: 0; overflow: hidden; border-radius: 12px;">
                                <iframe
                                    src="https://yandex.ru/map-widget/v1/?um=constructor%3Aa1065e6dbef434bc2149ddb1f79b69222fb0fdb7688a6f2340a431308f8be8df&source=constructor"
                                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"
                                    frameborder="0"
                                    allowfullscreen
                                    loading="lazy">
                                </iframe>
                            </div>
                        </div>

                        <div class="contact-info-form">
                            <div class="contact-info">
                                <h3>Автошкола «LADA»</h3>
                                <p><strong>Адрес:</strong> <a href="https://yandex.ru/maps/-/CLWu50yk" target="_blank" rel="noopener">г. Балашов, переулок Вокзальный, 6</a></p>
                                <p><strong>Телефон:</strong> <a href="tel:+78454541703">(84545) 4-17-03</a></p>
                                <p><strong>Email:</strong> <a href="mailto:btmskh@mail.ru">btmskh@mail.ru</a></p>
                                <p><strong>Режим работы:</strong> Пн–Пт, 9:00–19:00</p>
                            </div>

                            <form class="contact-form" method="POST" action="/handlers/submit_contact.php">
                                <h3>Напишите нам</h3>
                                <div class="form-group">
                                    <input type="text" name="фио" placeholder="Ваше имя" required>
                                </div>
                                <div class="form-group">
                                    <input type="tel" name="телефон" placeholder="Телефон" required>
                                </div>
                                <div class="form-group">
                                    <input type="email" name="email" placeholder="Email (опционально)">
                                </div>
                                <div class="form-group">
                                    <textarea name="сообщение" placeholder="Сообщение" rows="4" required></textarea>
                                </div>
                                <button type="submit" class="contact-btn">Отправить</button>
                            </form>
                        </div>

                        <?php if (!empty($_SESSION['contact_success'])): ?>
                            <div style="background:#e8f5e9; color:#2e7d32; padding:12px; border-radius:6px; margin-bottom:20px;">
                                <?= htmlspecialchars($_SESSION['contact_success']) ?>
                            </div>
                            <?php unset($_SESSION['contact_success']); ?>
                        <?php endif; ?>

                        <?php if (!empty($_SESSION['contact_error'])): ?>
                            <div style="background:#ffebee; color:#c62828; padding:12px; border-radius:6px; margin-bottom:20px;">
                                <?= htmlspecialchars($_SESSION['contact_error']) ?>
                            </div>
                            <?php unset($_SESSION['contact_error']); ?>
                        <?php endif; ?>

                    </div>
                </div>
            </section>

            <!-- Блок "Fotter меню" -->
            <?php require_once "blocks/footer.php";?>
        </div>
    </body>
</html>