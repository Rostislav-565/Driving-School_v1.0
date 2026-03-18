<!-- Модальное окно (одно на обе кнопки) -->
        <div id="modal" class="modal">

            <div class="modal-overlay"></div>

            <div class="modal-content">
                <span class="modal-close">&times;</span>
                <div id="modal-body">

                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('modal');
                const modalBody = document.getElementById('modal-body');
                const closeModalBtn = document.querySelector('.modal-close');
                const overlay = document.querySelector('.modal-overlay');

                // Функция открытия модалки
                function openModal(content) {
                    modalBody.innerHTML = content;
                    modal.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                }

                // Функция закрытия
                function closeModal() {
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                }

                // Закрытие по клику на крестик
                closeModalBtn.addEventListener('click', closeModal);
                overlay.addEventListener('click', closeModal);

                // Назначаем обработчики на все кнопки "Записаться"
                document.querySelectorAll('.btn-primary').forEach((btn, index) => {
                    btn.addEventListener('click', function () {
                        const category = this.dataset.category || 'X'; // ← используем data-category

                        const content = `
                            <form id="requestForm" method="POST" action="/handlers/submit_request.php">
                                <input type="hidden" name="category" value="${category}">
                                <div class="modal-form">
                                    <h3>Запись на курс категории "${category}"</h3>
                                    <div class="form-group">
                                        <label for="name">Ваше имя</label>
                                        <input type="text" name="fio" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">Телефон</label>
                                        <input type="tel" name="phone" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email (опционально)</label>
                                        <input type="email" name="email">
                                    </div>
                                    <button type="submit" class="btn-submit">Отправить заявку</button>
                                </div>
                            </form>
                        `;
                        openModal(content);

                        // Добавляем обработчик на отправку формы через AJAX (чтобы не перезагружать страницу)
                        document.getElementById('requestForm')?.addEventListener('submit', function(e) {
                            e.preventDefault();
                            
                            const formData = new FormData(this);
                            
                            fetch('/handlers/submit_request.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    modalBody.innerHTML = '<div class="modal-success"><h3>Спасибо!</h3><p>Ваша заявка отправлена. Мы свяжемся с вами в ближайшее время.</p></div>';
                                    setTimeout(() => closeModal(), 3000);
                                } else {
                                    modalBody.innerHTML = '<div class="modal-error"><h3>Ошибка</h3><p>' + (data.message || 'Не удалось отправить заявку') + '</p></div>';
                                }
                            })
                            .catch(() => {
                                modalBody.innerHTML = '<div class="modal-error"><h3>Ошибка</h3><p>Не удалось подключиться к серверу.</p></div>';
                            });
                        });
                    });
                });

                // Назначаем обработчики на все кнопки "Подробнее"
                document.querySelectorAll('.btn-details').forEach((btn, index) => {
                    btn.addEventListener('click', function () {
                        const courseInfo = [
                            { 
                                title: 'Категория "A"', 
                                desc: 'Курс по управлению мотоциклами и мопедами. Включает теорию ПДД, практику на автодроме и в городе. Продолжительность — 2 месяца.' 
                            },
                            { 
                                title: 'Категория "B"', 
                                desc: 'Обучение вождению легковых автомобилей. Программа включает 32 часа теории и 56 часов практики. Экзамен в ГИБДД входит в стоимость.' 
                            },
                            { 
                                title: 'Категория "C"', 
                                desc: 'Подготовка водителей грузовых автомобилей. Для записи требуется наличие прав категории "B". Курс длится 2.5 месяца.' 
                            },
                            { 
                                title: 'Категория "D"', 
                                desc: 'Обучение управлению автобусами и другим пассажирским транспортом. Необходимо наличие прав категории "B" и стаж вождения от 1 года.' 
                            },
                            { 
                                title: 'Категория "M"', 
                                desc: 'Курс для начинающих водителей мопедов и скутеров. Можно сдавать с 16 лет. Включает упрощённую теорию и практические занятия на закрытой площадке.' 
                            },
                            { 
                                title: 'Категория "A1"', 
                                desc: 'Права на лёгкие мотоциклы до 125 куб. см. Идеальный старт для подростков и новичков. Обучение занимает 1.5 месяца.' 
                            }
                                ][index] || { title: 'Информация', desc: 'Данные временно недоступны.' };

                        const content = `
                            <div class="course-details">
                                <h3>${courseInfo.title}</h3>
                                <p>${courseInfo.desc}</p>
                            </div>
                        `;
                        openModal(content);
                    });
                });
            });
        </script>