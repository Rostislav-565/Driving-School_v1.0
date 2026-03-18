-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:3306
-- Время создания: Дек 15 2025 г., 11:14
-- Версия сервера: 5.7.24
-- Версия PHP: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `автошкола`
--

-- --------------------------------------------------------

--
-- Структура таблицы `инструкторы`
--

CREATE TABLE `инструкторы` (
  `id` int(11) NOT NULL,
  `пользователь_id` int(11) NOT NULL,
  `автомобиль` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `номер_авто` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `стаж_лет` int(11) DEFAULT NULL,
  `специализация` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `инструкторы`
--

INSERT INTO `инструкторы` (`id`, `пользователь_id`, `автомобиль`, `номер_авто`, `стаж_лет`, `специализация`) VALUES
(1, 2, 'Hyundai Solaris', 'A123BC', 5, NULL),
(2, 6, 'Теория', '-', 5, NULL),
(3, 7, 'Теория', '-', 10, NULL),
(4, 8, 'Lada Granta', 'A893HC', 12, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `курсы`
--

CREATE TABLE `курсы` (
  `id` int(11) NOT NULL,
  `название` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `категория` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `цена` decimal(10,2) NOT NULL,
  `длительность_дней` int(11) NOT NULL,
  `описание` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `курсы`
--

INSERT INTO `курсы` (`id`, `название`, `категория`, `цена`, `длительность_дней`, `описание`) VALUES
(1, 'Базовый курс категории B', 'B', '35000.00', 60, NULL),
(2, 'Полный курс категории A', 'A', '28000.00', 45, NULL),
(3, 'Профессиональный курс категории C', 'C', '45000.00', 75, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `платежи`
--

CREATE TABLE `платежи` (
  `id` int(11) NOT NULL,
  `ученик_id` int(11) NOT NULL,
  `сумма` decimal(10,2) NOT NULL,
  `дата_платежа` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `способ` enum('наличные','карта','перевод','онлайн') COLLATE utf8mb4_unicode_ci NOT NULL,
  `статус` enum('ожидает','оплачено','отменено') COLLATE utf8mb4_unicode_ci DEFAULT 'оплачено',
  `комментарий` text COLLATE utf8mb4_unicode_ci,
  `группа_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `платежи`
--

INSERT INTO `платежи` (`id`, `ученик_id`, `сумма`, `дата_платежа`, `способ`, `статус`, `комментарий`, `группа_id`) VALUES
(1, 1, '35000.00', '2025-12-12 00:03:01', 'онлайн', 'оплачено', NULL, 1),
(2, 2, '28000.00', '2025-12-12 00:03:01', 'карта', 'оплачено', NULL, 2),
(3, 3, '45000.00', '2025-12-12 00:03:01', 'перевод', 'оплачено', NULL, 3);

-- --------------------------------------------------------

--
-- Структура таблицы `пользователи`
--

CREATE TABLE `пользователи` (
  `id` int(11) NOT NULL,
  `роль_id` tinyint(4) NOT NULL DEFAULT '1',
  `фио` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `телефон` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `пароль` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `дата_регистрации` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `активен` tinyint(1) DEFAULT '1',
  `последний_вход` timestamp NULL DEFAULT NULL,
  `тип` enum('ученик','инструктор','админ') COLLATE utf8mb4_unicode_ci DEFAULT 'ученик'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `пользователи`
--

INSERT INTO `пользователи` (`id`, `роль_id`, `фио`, `телефон`, `email`, `пароль`, `дата_регистрации`, `активен`, `последний_вход`, `тип`) VALUES
(1, 3, 'Иванов Алексей Петрович', '+79991234567', 'admin@avto.ru', '$2y$10$i9xOzuec83s5VZ41aEYDVeXCx6M55Cb4gQCTclxTOY1h4wPjmQZ3K', '2025-12-12 00:03:01', 1, NULL, 'ученик'),
(2, 2, 'Петров Сергей Иванович', '+79992345678', 'instructor@avto.ru', '$2y$10$HweZLG04terl6w0jr12paOd4.ZuI5eJbhZr6F6iEutY7svD2IVlj2', '2025-12-12 00:03:01', 1, NULL, 'инструктор'),
(3, 1, 'Сидоров Андрей Николаевич', '+79993456789', 'student1@avto.ru', '$2y$10$o5Iy8iw9869GduhcyBnMTu288wsa2MqBztYrB3NHqccDJW5FTi8mm', '2025-12-12 00:03:01', 1, NULL, 'ученик'),
(4, 1, 'Кузнецова Мария Владимировна', '+79994567890', 'student2@avto.ru', '$2y$10$RWHZCDuKtNTKQInh53Lt.e6PIZNIKkCXDrvFQZfpM2RAlI7jSkYp.', '2025-12-12 00:03:01', 1, NULL, 'ученик'),
(5, 1, 'Васильев Павел Сергеевич', '+79995678901', 'student3@avto.ru', '$2y$10$KkEQVsTrciu50teYVdFTr.Nlf9E84rLLSGLwHZZnuri/ALRgGcet.', '2025-12-12 00:03:01', 1, NULL, 'ученик'),
(6, 2, 'Иванов Иван Иванович', '+79985768454', 'ivanov@mail.ru', '$2y$10$2Ncz5X4Ar4FI7c6Eft5i8.y18pAbmh0IJsCIvchpY/BAIQV9zITH2', '2025-12-15 10:52:49', 1, NULL, 'ученик'),
(7, 2, 'Цветков Сергей Алексеевич', '+79877345689', 'sergei@mail.ru', '$2y$10$VAno6B1oOl4x8G4JrUWzq.BrySw4ecLdaIcfviVScpXQj5HllQ6ae', '2025-12-15 10:54:06', 1, NULL, 'ученик'),
(8, 2, 'Чернов Александр Николаевич', '9847343727', 'aleksandr@mail.ru', '$2y$10$v2cDNZMYAYWXeZKJAn9hrOBiFTo6JUaBmUBdKCgk68HrmqvJrRH9K', '2025-12-15 10:56:30', 1, NULL, 'ученик'),
(9, 1, 'Иванов Дмитрий Сергеевич', '+79857658383', 'student4@avto.ru', '$2y$10$si.mlGbhJnGzxQNmXWP4qOfLLvw8dANoiC.EvbnigOjeroV8gr5o.', '2025-12-15 11:04:27', 1, NULL, 'ученик'),
(10, 1, 'Петрова Анна Олеговна', '+78576848460', 'student5@avto.ru', '$2y$10$TEySNKS2zsIYPWhZeMrfQ.38pMN.Ui6460CRa4DZJjj.cpil4zsLS', '2025-12-15 11:04:56', 1, NULL, 'ученик'),
(11, 1, 'Сидоров Максим Андреевич', '+79561284628', 'student6@avto.ru', '$2y$10$4zIdAMw/K0eFVV3PsOo/Ruh7tVYyqI2BOpKyTGJIkp6wJt1QWq1d.', '2025-12-15 11:05:21', 1, NULL, 'ученик'),
(12, 1, 'Кузнецова Екатерина Викторовна', '+76593750285', 'student7@avto.ru', '$2y$10$1NTgJMm25O/ZDTpe5b.DbuTfi5V/H9IuTJQ4sbP5arUpghfaR4iyq', '2025-12-15 11:05:52', 1, NULL, 'ученик'),
(13, 1, 'Смирнов Артём Александрович', '+78564759274', 'student8@avto.ru', '$2y$10$gHP8ir7XiI0CtYZ5y6ditO59Sg4PzWBN2smucCCtVn8k8YQmj/zGu', '2025-12-15 11:06:15', 1, NULL, 'ученик'),
(14, 1, 'Васильева Дарья Владимировна', '+78564731623', 'student9@avto.ru', '$2y$10$yFLsYMq3VZUdAtkZQwVZ6OsKk4OMTGqlZKeaIda79sluHu.IcpNT2', '2025-12-15 11:06:51', 1, NULL, 'ученик'),
(15, 1, 'Николаева София Игоревна', '+78955454545', 'student10@avto.ru', '$2y$10$j592giI5WNPG1T9QPDn4hOr0E.h7/C2clYz9PL.DAkI6YGr./Cxhy', '2025-12-15 11:07:15', 1, NULL, 'ученик');

-- --------------------------------------------------------

--
-- Структура таблицы `посещения`
--

CREATE TABLE `посещения` (
  `id` int(11) NOT NULL,
  `ученик_id` int(11) NOT NULL,
  `занятие_id` int(11) NOT NULL,
  `дата_посещения` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `посещения`
--

INSERT INTO `посещения` (`id`, `ученик_id`, `занятие_id`, `дата_посещения`) VALUES
(2, 1, 2, '2025-12-12'),
(3, 1, 3, '2025-12-15'),
(4, 1, 1, '2025-12-12'),
(5, 1, 8, '2025-12-12'),
(6, 1, 9, '2025-12-13'),
(7, 2, 10, '2025-12-13'),
(8, 2, 12, '2025-12-15'),
(9, 10, 12, '2025-12-15'),
(10, 4, 12, '2025-12-15'),
(11, 5, 12, '2025-12-15'),
(12, 7, 12, '2025-12-15');

-- --------------------------------------------------------

--
-- Структура таблицы `группа_ученики`
--

CREATE TABLE `группа_ученики` (
  `id` int(11) NOT NULL,
  `группа_id` int(11) NOT NULL,
  `ученик_id` int(11) NOT NULL,
  `дата_зачисления` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `группа_ученики`
--

INSERT INTO `группа_ученики` (`id`, `группа_id`, `ученик_id`, `дата_зачисления`) VALUES
(1, 1, 1, '2025-12-01'),
(2, 2, 2, '2025-12-01'),
(3, 3, 3, '2025-12-01'),
(4, 2, 4, '2025-12-15'),
(5, 2, 5, '2025-12-15'),
(6, 2, 6, '2025-12-15'),
(7, 2, 7, '2025-12-15'),
(8, 2, 8, '2025-12-15'),
(9, 2, 9, '2025-12-15'),
(10, 2, 10, '2025-12-15');

-- --------------------------------------------------------

--
-- Структура таблицы `группы`
--

CREATE TABLE `группы` (
  `id` int(11) NOT NULL,
  `название` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `курс_id` int(11) NOT NULL,
  `инструктор_id` int(11) DEFAULT NULL,
  `дата_начала` date DEFAULT NULL,
  `дата_окончания` date DEFAULT NULL,
  `статус` enum('набор','обучение','завершена') COLLATE utf8mb4_unicode_ci DEFAULT 'набор',
  `категория` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'B'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `группы`
--

INSERT INTO `группы` (`id`, `название`, `курс_id`, `инструктор_id`, `дата_начала`, `дата_окончания`, `статус`, `категория`) VALUES
(1, 'Группа B-01', 1, 1, '2025-12-01', NULL, 'обучение', 'B'),
(2, 'Группа A-01', 2, 2, '2025-12-01', NULL, 'обучение', 'B'),
(3, 'Группа C-01', 3, 3, '2025-12-01', NULL, 'обучение', 'B'),
(4, 'Группа A-02', 2, 4, '2025-12-20', '2026-03-20', 'набор', 'A'),
(5, 'Группа C-01', 3, 4, '2025-12-25', '2026-03-25', 'набор', 'C');

-- --------------------------------------------------------

--
-- Структура таблицы `занятия`
--

CREATE TABLE `занятия` (
  `id` int(11) NOT NULL,
  `группа_id` int(11) DEFAULT NULL,
  `ученик_id` int(11) DEFAULT NULL,
  `инструктор_id` int(11) NOT NULL,
  `тип` enum('теория','практика','экзамен') COLLATE utf8mb4_unicode_ci NOT NULL,
  `дата_занятия` date NOT NULL,
  `время_начала` time NOT NULL,
  `время_окончания` time NOT NULL,
  `тема` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `описание` text COLLATE utf8mb4_unicode_ci,
  `место` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `статус` enum('запланировано','проведено','отменено') COLLATE utf8mb4_unicode_ci DEFAULT 'запланировано'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `занятия`
--

INSERT INTO `занятия` (`id`, `группа_id`, `ученик_id`, `инструктор_id`, `тип`, `дата_занятия`, `время_начала`, `время_окончания`, `тема`, `описание`, `место`, `статус`) VALUES
(1, 1, NULL, 1, 'теория', '2025-12-10', '18:00:00', '20:00:00', 'ПДД: общие положения', NULL, 'Аудитория 1', 'проведено'),
(2, 1, NULL, 1, 'теория', '2025-12-12', '18:00:00', '20:00:00', 'Дорожные знаки', NULL, 'Аудитория 1', 'проведено'),
(3, 1, NULL, 1, 'практика', '2025-12-15', '10:00:00', '12:00:00', 'Начальное вождение', NULL, 'Автодром', 'запланировано'),
(4, 1, NULL, 1, 'практика', '2025-12-17', '10:00:00', '12:00:00', 'Парковка задним ходом', NULL, 'Автодром', 'запланировано'),
(5, 1, NULL, 1, 'теория', '2025-12-19', '18:00:00', '20:00:00', 'Безопасность и первая помощь', NULL, 'Аудитория 2', 'запланировано'),
(6, 1, NULL, 1, 'практика', '2025-12-13', '12:00:00', '13:00:00', 'Вождение', NULL, 'Парковка', 'проведено'),
(7, 2, NULL, 1, 'теория', '2025-12-14', '11:11:00', '12:12:00', '123', NULL, '123', 'запланировано'),
(8, 1, NULL, 1, 'теория', '2025-12-12', '09:00:00', '10:00:00', 'Вождение', NULL, 'Парковка', 'проведено'),
(9, 1, NULL, 1, 'теория', '2025-12-13', '11:11:00', '12:12:00', 'Теория', NULL, 'Аудитория 12', 'проведено'),
(10, NULL, 2, 1, 'практика', '2025-12-13', '12:00:00', '13:00:00', 'Вождение', NULL, 'Парковка', 'проведено'),
(11, NULL, 2, 1, 'практика', '2025-12-13', '12:00:00', '13:13:00', 'Вождение', NULL, 'Парковка', 'запланировано'),
(12, 2, NULL, 2, 'теория', '2025-12-15', '17:00:00', '18:00:00', 'Решение билетов', NULL, 'Аудитория №13', 'проведено');

-- --------------------------------------------------------

--
-- Структура таблицы `заявки`
--

CREATE TABLE `заявки` (
  `id` int(11) NOT NULL,
  `фио` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `телефон` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `категория` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `дата_заявки` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `статус` enum('новая','обработана','отклонена') COLLATE utf8mb4_unicode_ci DEFAULT 'новая'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `заявки`
--

INSERT INTO `заявки` (`id`, `фио`, `телефон`, `email`, `категория`, `дата_заявки`, `статус`) VALUES
(1, 'Николаев Дмитрий Олегович', '+79996789012', 'nikolaev@mail.ru', 'B', '2025-12-12 00:03:01', 'новая'),
(2, 'Смирнова Ольга Александровна', '+79997890123', 'smirnova@mail.ru', 'A', '2025-12-12 00:03:01', 'новая'),
(3, 'Фёдоров Артём Викторович', '+79998901234', 'fedorov@mail.ru', 'C', '2025-12-12 00:03:01', 'новая'),
(4, 'Волкова Екатерина Игоревна', '+79999012345', 'volkova@mail.ru', 'B', '2025-12-12 00:03:01', 'новая'),
(5, 'Морозов Илья Дмитриевич', '+79990123456', 'morozov@mail.ru', 'M', '2025-12-12 00:03:01', 'новая'),
(6, 'test', '+88888888888', 'test@mail.ru', 'B', '2025-12-15 10:46:03', 'новая');

-- --------------------------------------------------------

--
-- Структура таблицы `обращения`
--

CREATE TABLE `обращения` (
  `id` int(11) NOT NULL,
  `фио` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `телефон` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `сообщение` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `дата` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `отвечено` tinyint(1) DEFAULT '0',
  `ответ` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `обращения`
--

INSERT INTO `обращения` (`id`, `фио`, `телефон`, `email`, `сообщение`, `дата`, `отвечено`, `ответ`) VALUES
(1, 'Алексеев Виктор Петрович', '+79991122334', NULL, 'Здравствуйте! Есть ли утренние группы по категории B?', '2025-12-12 00:03:02', 0, NULL),
(2, 'Федорова Ирина Сергеевна', '+79992233445', NULL, 'Можно ли оплатить курс в рассрочку на 3 месяца?', '2025-12-12 00:03:02', 0, NULL),
(3, 'Громов Роман Андреевич', '+79993344556', NULL, 'Когда следующий набор в группу по категории A?', '2025-12-12 00:03:02', 0, NULL),
(4, 'Лебедева Анна Павловна', '+79994455667', NULL, 'Есть ли скидки для студентов?', '2025-12-12 00:03:02', 0, NULL),
(5, 'Козлов Максим Юрьевич', '+79995566778', NULL, 'Какие документы нужны для зачисления?', '2025-12-12 00:03:02', 0, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `отзывы`
--

CREATE TABLE `отзывы` (
  `id` int(11) NOT NULL,
  `ученик_id` int(11) DEFAULT NULL,
  `имя_автора` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `текст` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `оценка` tinyint(4) DEFAULT NULL,
  `дата` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `опубликовано` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `отзывы`
--

INSERT INTO `отзывы` (`id`, `ученик_id`, `имя_автора`, `текст`, `оценка`, `дата`, `опубликовано`) VALUES
(1, 1, NULL, 'Отличный инструктор, всё объясняет чётко и понятно!', 5, '2025-12-12 00:03:01', 1),
(2, 2, NULL, 'Машины новые, автодром современный. Рекомендую!', 4, '2025-12-12 00:03:01', 1),
(3, 3, NULL, 'Курс стоит своих денег. Уже чувствую уверенность за рулём.', 5, '2025-12-12 00:03:01', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `роли`
--

CREATE TABLE `роли` (
  `id` tinyint(4) NOT NULL,
  `название` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `роли`
--

INSERT INTO `роли` (`id`, `название`) VALUES
(3, 'админ'),
(2, 'инструктор'),
(1, 'ученик');

-- --------------------------------------------------------

--
-- Структура таблицы `статус_оплаты`
--

CREATE TABLE `статус_оплаты` (
  `ученик_id` int(11) NOT NULL,
  `группа_id` int(11) NOT NULL,
  `дата_начала_оплаты` date NOT NULL,
  `дата_окончания_оплаты` date NOT NULL,
  `оплачено` tinyint(1) DEFAULT '1',
  `напоминание_отправлено` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `статус_оплаты`
--

INSERT INTO `статус_оплаты` (`ученик_id`, `группа_id`, `дата_начала_оплаты`, `дата_окончания_оплаты`, `оплачено`, `напоминание_отправлено`) VALUES
(1, 1, '2025-12-01', '2026-01-30', 1, 0),
(2, 2, '2025-12-01', '2026-01-15', 1, 0),
(3, 3, '2025-12-01', '2026-02-14', 1, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `ученики`
--

CREATE TABLE `ученики` (
  `id` int(11) NOT NULL,
  `пользователь_id` int(11) NOT NULL,
  `категория` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `статус` enum('активный','завершил','отчислен') COLLATE utf8mb4_unicode_ci DEFAULT 'активный',
  `дата_начала` date DEFAULT NULL,
  `дата_окончания` date DEFAULT NULL,
  `номер_договора` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `ученики`
--

INSERT INTO `ученики` (`id`, `пользователь_id`, `категория`, `статус`, `дата_начала`, `дата_окончания`, `номер_договора`) VALUES
(1, 3, 'B', 'активный', '2025-12-01', NULL, NULL),
(2, 4, 'A', 'активный', '2025-12-01', NULL, NULL),
(3, 5, 'C', 'активный', '2025-12-01', NULL, NULL),
(4, 9, 'A', 'активный', '2025-12-15', NULL, NULL),
(5, 10, 'A', 'активный', '2025-12-15', NULL, NULL),
(6, 11, 'A', 'активный', '2025-12-15', NULL, NULL),
(7, 12, 'A', 'активный', '2025-12-15', NULL, NULL),
(8, 13, 'A', 'активный', '2025-12-15', NULL, NULL),
(9, 14, 'A', 'активный', '2025-12-15', NULL, NULL),
(10, 15, 'A', 'активный', '2025-12-15', NULL, NULL);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `инструкторы`
--
ALTER TABLE `инструкторы`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `пользователь_id` (`пользователь_id`);

--
-- Индексы таблицы `курсы`
--
ALTER TABLE `курсы`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `платежи`
--
ALTER TABLE `платежи`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ученик_id` (`ученик_id`),
  ADD KEY `группа_id` (`группа_id`);

--
-- Индексы таблицы `пользователи`
--
ALTER TABLE `пользователи`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `роль_id` (`роль_id`);

--
-- Индексы таблицы `посещения`
--
ALTER TABLE `посещения`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `уникальное_посещение` (`ученик_id`,`занятие_id`),
  ADD KEY `занятие_id` (`занятие_id`);

--
-- Индексы таблицы `группа_ученики`
--
ALTER TABLE `группа_ученики`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `группа_id` (`группа_id`,`ученик_id`),
  ADD KEY `ученик_id` (`ученик_id`);

--
-- Индексы таблицы `группы`
--
ALTER TABLE `группы`
  ADD PRIMARY KEY (`id`),
  ADD KEY `курс_id` (`курс_id`),
  ADD KEY `инструктор_id` (`инструктор_id`);

--
-- Индексы таблицы `занятия`
--
ALTER TABLE `занятия`
  ADD PRIMARY KEY (`id`),
  ADD KEY `группа_id` (`группа_id`),
  ADD KEY `инструктор_id` (`инструктор_id`),
  ADD KEY `fk_занятия_ученик` (`ученик_id`);

--
-- Индексы таблицы `заявки`
--
ALTER TABLE `заявки`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `обращения`
--
ALTER TABLE `обращения`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `отзывы`
--
ALTER TABLE `отзывы`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ученик_id` (`ученик_id`);

--
-- Индексы таблицы `роли`
--
ALTER TABLE `роли`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `название` (`название`);

--
-- Индексы таблицы `статус_оплаты`
--
ALTER TABLE `статус_оплаты`
  ADD PRIMARY KEY (`ученик_id`),
  ADD KEY `группа_id` (`группа_id`);

--
-- Индексы таблицы `ученики`
--
ALTER TABLE `ученики`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `пользователь_id` (`пользователь_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `инструкторы`
--
ALTER TABLE `инструкторы`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `курсы`
--
ALTER TABLE `курсы`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `платежи`
--
ALTER TABLE `платежи`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `пользователи`
--
ALTER TABLE `пользователи`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT для таблицы `посещения`
--
ALTER TABLE `посещения`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `группа_ученики`
--
ALTER TABLE `группа_ученики`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `группы`
--
ALTER TABLE `группы`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `занятия`
--
ALTER TABLE `занятия`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `заявки`
--
ALTER TABLE `заявки`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `обращения`
--
ALTER TABLE `обращения`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `отзывы`
--
ALTER TABLE `отзывы`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `ученики`
--
ALTER TABLE `ученики`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `инструкторы`
--
ALTER TABLE `инструкторы`
  ADD CONSTRAINT `инструкторы_ibfk_1` FOREIGN KEY (`пользователь_id`) REFERENCES `пользователи` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `платежи`
--
ALTER TABLE `платежи`
  ADD CONSTRAINT `платежи_ibfk_1` FOREIGN KEY (`ученик_id`) REFERENCES `ученики` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `платежи_ibfk_2` FOREIGN KEY (`группа_id`) REFERENCES `группы` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `пользователи`
--
ALTER TABLE `пользователи`
  ADD CONSTRAINT `пользователи_ibfk_1` FOREIGN KEY (`роль_id`) REFERENCES `роли` (`id`);

--
-- Ограничения внешнего ключа таблицы `посещения`
--
ALTER TABLE `посещения`
  ADD CONSTRAINT `посещения_ibfk_1` FOREIGN KEY (`ученик_id`) REFERENCES `ученики` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `посещения_ibfk_2` FOREIGN KEY (`занятие_id`) REFERENCES `занятия` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `группа_ученики`
--
ALTER TABLE `группа_ученики`
  ADD CONSTRAINT `группа_ученики_ibfk_1` FOREIGN KEY (`группа_id`) REFERENCES `группы` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `группа_ученики_ibfk_2` FOREIGN KEY (`ученик_id`) REFERENCES `ученики` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `группы`
--
ALTER TABLE `группы`
  ADD CONSTRAINT `группы_ibfk_1` FOREIGN KEY (`курс_id`) REFERENCES `курсы` (`id`),
  ADD CONSTRAINT `группы_ibfk_2` FOREIGN KEY (`инструктор_id`) REFERENCES `инструкторы` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `занятия`
--
ALTER TABLE `занятия`
  ADD CONSTRAINT `fk_занятия_ученик` FOREIGN KEY (`ученик_id`) REFERENCES `ученики` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `занятия_ibfk_1` FOREIGN KEY (`группа_id`) REFERENCES `группы` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `занятия_ibfk_2` FOREIGN KEY (`инструктор_id`) REFERENCES `инструкторы` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `отзывы`
--
ALTER TABLE `отзывы`
  ADD CONSTRAINT `отзывы_ibfk_1` FOREIGN KEY (`ученик_id`) REFERENCES `ученики` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `статус_оплаты`
--
ALTER TABLE `статус_оплаты`
  ADD CONSTRAINT `статус_оплаты_ibfk_1` FOREIGN KEY (`ученик_id`) REFERENCES `ученики` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `статус_оплаты_ibfk_2` FOREIGN KEY (`группа_id`) REFERENCES `группы` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `ученики`
--
ALTER TABLE `ученики`
  ADD CONSTRAINT `ученики_ibfk_1` FOREIGN KEY (`пользователь_id`) REFERENCES `пользователи` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
