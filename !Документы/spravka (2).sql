-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Июн 23 2025 г., 13:23
-- Версия сервера: 8.0.30
-- Версия PHP: 8.3.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `spravka`
--

-- --------------------------------------------------------

--
-- Структура таблицы `brand`
--

CREATE TABLE `brand` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `brand`
--

INSERT INTO `brand` (`id`, `name`) VALUES
(1, 'Аптека Вита'),
(2, 'Первая социальная аптека'),
(3, 'Аптека Ника'),
(4, 'Добротека');

-- --------------------------------------------------------

--
-- Структура таблицы `cities`
--

CREATE TABLE `cities` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `is_enabled` tinyint(1) NOT NULL,
  `visible_cities` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `cities`
--

INSERT INTO `cities` (`id`, `name`, `ip`, `is_enabled`, `visible_cities`) VALUES
(1, 'Томск', '10.70.', 1, 2),
(2, 'Северск', '10.70.', 0, 1),
(3, 'Новосибирск', '10.54.', 1, 2),
(4, 'Мельниково', '10.70.', 0, 1),
(5, 'Колпашево', '10.70.', 0, 1),
(6, 'Асино', '10.70.', 0, 1),
(7, 'Парабель', '10.70.', 1, 6);

-- --------------------------------------------------------

--
-- Структура таблицы `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `post_id` int NOT NULL,
  `user_id` int NOT NULL,
  `text` mediumtext,
  `photo` varchar(255) DEFAULT NULL,
  `date_created` date NOT NULL,
  `is_public` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `text`, `photo`, `date_created`, `is_public`) VALUES
(69, 64, 1, '.', 'news/comments/93_665ff5da728ee.sql', '2024-06-05', 0),
(70, 64, 1, '', 'news/comments/93_665ff605997fc.sql', '2024-06-05', 0),
(71, 64, 2, 'da', NULL, '2024-06-05', 1),
(72, 64, 2, 'da', NULL, '2024-06-05', 1),
(73, 64, 2, 'da', NULL, '2024-06-06', 1),
(74, 64, 1, 'eqw', NULL, '2024-06-10', 1),
(75, 64, 2, '', NULL, '2024-06-10', 1),
(76, 64, 2, 'xj&', NULL, '2024-06-10', 0),
(77, 64, 1, 'ad', NULL, '2024-06-10', 0),
(78, 64, 1, 'da', NULL, '2024-06-10', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `district`
--

CREATE TABLE `district` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `city_id` int NOT NULL,
  `is_enabled` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `district`
--

INSERT INTO `district` (`id`, `name`, `city_id`, `is_enabled`) VALUES
(1, 'Кировский', 1, 1),
(2, 'Советский', 1, 1),
(3, 'Октябрьский', 1, 1),
(4, 'Ленинский', 1, 1),
(5, 'Заельцовский', 3, 1),
(6, 'Октябрьский', 3, 1),
(7, 'Советский', 3, 1),
(8, 'Железнодорожный', 3, 1),
(9, 'Калининский', 3, 1),
(10, 'Ленинский', 3, 1),
(11, 'Первомайский', 3, 1),
(12, 'Центральный', 3, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `firm`
--

CREATE TABLE `firm` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `firm`
--

INSERT INTO `firm` (`id`, `name`) VALUES
(1, 'ООО Аптека Вита'),
(2, 'ООО Чирчик'),
(3, 'ООО Аптека 36,6'),
(4, 'ООО Арт Лана'),
(5, 'ООО Азурит-Н'),
(6, 'ООО ТМН'),
(7, 'ООО Омела');

-- --------------------------------------------------------

--
-- Структура таблицы `importance`
--

CREATE TABLE `importance` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `importance`
--

INSERT INTO `importance` (`id`, `name`, `code`) VALUES
(1, 'Current', 'current'),
(2, 'Important', 'important'),
(3, 'Temporary', 'temporary');

-- --------------------------------------------------------

--
-- Структура таблицы `kontakts`
--

CREATE TABLE `kontakts` (
  `id` int NOT NULL,
  `type_kontakt_id` int NOT NULL,
  `urgent` tinyint(1) NOT NULL,
  `visibility` tinyint(1) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `author` int NOT NULL,
  `last_editor` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `kontakts`
--

INSERT INTO `kontakts` (`id`, `type_kontakt_id`, `urgent`, `visibility`, `name`, `phone`, `email`, `description`, `author`, `last_editor`) VALUES
(3, 2, 0, 0, 'ntcn ntcn ntcn', '440-689', 'it@aptekavita.ru', '', 2, 2),
(4, 1, 1, 0, 'Экстренный контакт один один', '222-333', '', ' ', 2, 2),
(5, 2, 1, 0, 'Экстренный контакт для всех', '+7 333 892 44-55', '', '', 2, 2),
(6, 1, 0, 0, 'Контакт обычный', '+79138278898', '', '', 2, 2),
(7, 1, 0, 0, 'АХО Антон Николаевич', '+7826736678', '', '', 2, 2),
(8, 1, 0, 0, 'Служба закупа', '44-917', '', '', 2, 2),
(9, 2, 0, 0, 'Офис новосибисрк куратор1', '+7 383 777-8888', '', '', 2, 2),
(10, 1, 0, 0, 'Куратор Северск', '+73823 444-777', '', '', 2, 2);

-- --------------------------------------------------------

--
-- Структура таблицы `links`
--

CREATE TABLE `links` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `url` mediumtext NOT NULL,
  `description` mediumtext NOT NULL,
  `type_link_id` int NOT NULL,
  `can_employees_add` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `links`
--

INSERT INTO `links` (`id`, `name`, `url`, `description`, `type_link_id`, `can_employees_add`) VALUES
(1, 'Внутренний сайт Техподдержки', 'http://hd.aptekavita.ru:88/hd/hs/lk/', 'Сайт для работы с заявками ИТ, АХЧ, Маркетинг', 3, 1),
(2, 'Диадок', 'https://diadoc.ru', 'Сайт для работы с отгрузочными документами от поставщиков', 3, 1),
(3, 'Внутренний портал', 'http://portal.aptekavita.ru', 'Сайт для работы с внутренники документами компании', 3, 1),
(4, 'Корпоративная обучающая платформа', 'https://edu.aptekavita.ru', 'Сайт с рабочими обучающими материалами', 2, 1),
(5, 'Инкассация Сбербанк', 'https://encashment.sberbank.ru/', 'Заполнение инкассации сбербанк', 4, 1),
(6, 'Протек личный кабинет', 'https://webzakaz.protek.ru/login', 'Личный кабинет Протек', 1, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `link_pas`
--

CREATE TABLE `link_pas` (
  `id` int NOT NULL,
  `link_id` int NOT NULL,
  `login` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `description` mediumtext,
  `author` int NOT NULL,
  `last_editor` int NOT NULL,
  `is_visible` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `link_pas`
--

INSERT INTO `link_pas` (`id`, `link_id`, `login`, `password`, `description`, `author`, `last_editor`, `is_visible`) VALUES
(1, 1, 'user001', 'Qz7x!pL9aV', '', 1, 1, 1),
(2, 2, 'pharma_1', '4bYt*GkM2r', '', 1, 1, 0),
(3, 3, 'worker12', 'Fm!89xZvQp', '', 1, 1, 1),
(4, 4, 'pharmacy23', '3Kj#PqRt9L', '', 1, 1, 0),
(5, 5, 'employeeX', 'Bv8*LnQz72', '', 1, 1, 1),
(6, 6, 'staff2024', 'TzQp!46MkY', '', 1, 1, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `logs`
--

CREATE TABLE `logs` (
  `id` int NOT NULL,
  `post_id` int NOT NULL,
  `user_id` int NOT NULL,
  `date_action` date NOT NULL,
  `action` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `logs`
--

INSERT INTO `logs` (`id`, `post_id`, `user_id`, `date_action`, `action`) VALUES
(19, 64, 1, '2024-06-04', 'Посмотрел'),
(20, 64, 1, '2024-06-04', 'Просмотрел'),
(21, 64, 1, '2024-06-04', 'Просмотрел'),
(22, 64, 1, '2024-06-04', 'Просмотрел'),
(23, 64, 1, '2024-06-04', 'Просмотрел'),
(24, 64, 1, '2024-06-04', 'Редактировал запись'),
(25, 64, 1, '2024-06-04', 'Просмотрел'),
(26, 64, 1, '2024-06-04', 'Просмотрел'),
(27, 64, 1, '2024-06-04', 'Просмотрел'),
(28, 64, 1, '2024-06-04', 'Просмотрел');

-- --------------------------------------------------------

--
-- Структура таблицы `partners`
--

CREATE TABLE `partners` (
  `id` int NOT NULL,
  `city_id` int NOT NULL,
  `brand_id` int NOT NULL,
  `district_id` int DEFAULT NULL,
  `firm_id` int NOT NULL,
  `type_partner_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `net` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `partners`
--

INSERT INTO `partners` (`id`, `city_id`, `brand_id`, `district_id`, `firm_id`, `type_partner_id`, `name`, `code`, `phone`, `net`) VALUES
(25, 1, 2, NULL, 2, 1, 'Мира, 19', '20032', '8 (3822) 765-930', '14'),
(26, 1, 1, NULL, 1, 1, 'Ботанический, 3', '10010', '8 (3822) 411-013', '3'),
(28, 1, 1, NULL, 1, 1, 'Иркутский, 102', '10012', '8 (3822) 668-854', '9'),
(29, 1, 1, NULL, 1, 1, 'Иркутский, 162', '10013', '8 (3822) 646-641', '19'),
(30, 1, 1, NULL, 1, 1, 'Киевская, 13', '10014', '8 (3822) 445-513', '22');

-- --------------------------------------------------------

--
-- Структура таблицы `posts`
--

CREATE TABLE `posts` (
  `id` int NOT NULL,
  `author` int NOT NULL,
  `last_editor` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `short_description` varchar(255) NOT NULL,
  `full_description` mediumtext NOT NULL,
  `attachment_fotos` varchar(255) DEFAULT NULL,
  `attachment_files` varchar(255) DEFAULT NULL,
  `importance_id` int NOT NULL,
  `importance_date` date DEFAULT NULL,
  `allow_comments` tinyint(1) NOT NULL,
  `comment_visibility` tinyint(1) NOT NULL,
  `visibility` tinyint(1) NOT NULL,
  `type_post_id` int NOT NULL,
  `date_created` date NOT NULL,
  `date_updated` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `posts`
--

INSERT INTO `posts` (`id`, `author`, `last_editor`, `title`, `short_description`, `full_description`, `attachment_fotos`, `attachment_files`, `importance_id`, `importance_date`, `allow_comments`, `comment_visibility`, `visibility`, `type_post_id`, `date_created`, `date_updated`) VALUES
(1, 2, 2, 'Обновление сайта справка Аптека Вита', 'Сегодня сделали небольшое обновление для справки. Добавили на него несколько новых модулей...', '<p>Добрый день.</p><p>Сегодня сделали обновление для сайта справки добавили несколько новых модулей...</p>', '[]', '[\"news/posts/1_file_667b80d3a7457.pdf\"]', 2, NULL, 0, 1, 1, 1, '2024-06-13', '2025-03-11'),
(2, 1, 2, 'Краткий стандарт поведения сотрудника аптеки 2024', 'Уважаемые коллеги, прикрепила краткий стандарт поведения сотрудника аптеки.', '<p>Уважаемые коллеги, прикрепила краткий стандарт поведения сотрудника аптеки для наглядности.</p>', '[]', '[]', 1, NULL, 1, 0, 1, 1, '2024-06-14', '2025-03-11'),
(3, 1, 2, 'Осторожно! Мошенники!', 'Самые частые виды мошенничества.', '<p>Уважаемые коллеги, будьте внимательны со звонками или сообщениями с незнакомых номеров.</p>', '[]', '[]', 2, NULL, 1, 1, 1, 1, '2024-06-18', '2025-03-11'),
(4, 2, 2, 'Добавлен раздел с Полезные ссылки', 'Добавлен раздел по сохранению полезных ссылок', '<p>На сайте добавлен раздел в котором можно сохранять нужные в работе ссылки...</p>', '[]', '[]', 1, NULL, 1, 1, 1, 1, '2024-06-27', '2025-03-11'),
(64, 2, 2, 'Замена фискального накопителя в кассе в вашей аптеке', 'Замена фискального накопителя в кассе в вашей аптеке', '<p>ЗАВТРА (10.07.2024) планируем замену ФН в Зоркальцево...</p>', '[]', '[]', 3, '2024-07-11', 1, 1, 1, 1, '2024-07-09', '2025-03-11');

-- --------------------------------------------------------

--
-- Структура таблицы `surveys`
--

CREATE TABLE `surveys` (
  `id` int NOT NULL,
  `post_id` int NOT NULL,
  `question` varchar(255) NOT NULL,
  `type_answer_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `surveys`
--

INSERT INTO `surveys` (`id`, `post_id`, `question`, `type_answer_id`) VALUES
(1, 64, 'Вопрос 1', 2),
(2, 64, 'Вопрос 2', 2),
(3, 64, 'Вопрос 3', 3),
(4, 64, 'Вопрос 4', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `survey_answers`
--

CREATE TABLE `survey_answers` (
  `id` int NOT NULL,
  `survey_id` int NOT NULL,
  `answer` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `survey_answers`
--

INSERT INTO `survey_answers` (`id`, `survey_id`, `answer`) VALUES
(4, 1, 'Ответ к вопросу 1 №1'),
(5, 1, 'Ответ к вопросу 1 №2'),
(6, 1, 'Ответ к вопросу 1 №3'),
(7, 1, 'Ответ к вопросу 1 №4'),
(8, 2, 'Ответ к вопросу 2 №1'),
(9, 2, 'Ответ к вопросу 2 №2'),
(10, 2, 'Ответ к вопросу 2 №3'),
(11, 2, 'Ответ к вопросу 2 №4'),
(12, 3, 'Ответ к вопросу 3 №1'),
(13, 3, 'Ответ к вопросу 3 №2'),
(14, 3, 'Ответ к вопросу 3 №3'),
(15, 3, 'Ответ к вопросу 3 №4');

-- --------------------------------------------------------

--
-- Структура таблицы `type_answer`
--

CREATE TABLE `type_answer` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `type_answer`
--

INSERT INTO `type_answer` (`id`, `name`) VALUES
(1, 'text'),
(2, 'radio'),
(3, 'checkbox');

-- --------------------------------------------------------

--
-- Структура таблицы `type_kontakt`
--

CREATE TABLE `type_kontakt` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `visibility` tinyint(1) NOT NULL,
  `can_employees_add` tinyint(1) NOT NULL,
  `city_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `type_kontakt`
--

INSERT INTO `type_kontakt` (`id`, `name`, `visibility`, `can_employees_add`, `city_id`) VALUES
(1, 'Офис Томск', 0, 0, 1),
(2, 'Офис Новосибирск', 0, 0, 3),
(3, 'Поставщики', 0, 0, 1),
(4, 'Служебные', 1, 1, 1),
(5, 'Внутренние', 1, 1, 1),
(10, 'Медицинский представитель', 1, 1, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `type_link`
--

CREATE TABLE `type_link` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `type_link`
--

INSERT INTO `type_link` (`id`, `name`) VALUES
(1, 'Личные кабинеты'),
(2, 'Обучение'),
(3, 'Внутренние ресурсы'),
(4, 'Прочее');

-- --------------------------------------------------------

--
-- Структура таблицы `type_partner`
--

CREATE TABLE `type_partner` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `type_partner`
--

INSERT INTO `type_partner` (`id`, `name`) VALUES
(1, 'Аптека'),
(2, 'Склад'),
(3, 'Поставщик');

-- --------------------------------------------------------

--
-- Структура таблицы `type_post`
--

CREATE TABLE `type_post` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `type_post`
--

INSERT INTO `type_post` (`id`, `name`, `code`) VALUES
(1, 'Новость', 'news'),
(2, 'Объявление', 'announcement'),
(3, 'Опрос', 'survey');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `login` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `name`, `login`, `password`) VALUES
(1, 'Ит отдел', 'admin', 'admin'),
(2, 'Тимофеев', 'da', 'da');

-- --------------------------------------------------------

--
-- Структура таблицы `user_answers`
--

CREATE TABLE `user_answers` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `survey_answer_id` int DEFAULT NULL,
  `survey_id` int NOT NULL,
  `answer` mediumtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `user_answers`
--

INSERT INTO `user_answers` (`id`, `user_id`, `survey_answer_id`, `survey_id`, `answer`) VALUES
(1, 2, 4, 1, NULL),
(2, 1, NULL, 4, 'Ответ на Вопрос 4'),
(3, 1, 4, 2, NULL),
(4, 2, NULL, 4, 'Ответ на Вопрос 4');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `brand`
--
ALTER TABLE `brand`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Индексы таблицы `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `cities_index_0` (`visible_cities`);

--
-- Индексы таблицы `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `comments_index_0` (`post_id`),
  ADD KEY `comments_index_1` (`user_id`);

--
-- Индексы таблицы `district`
--
ALTER TABLE `district`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `district_index_0` (`city_id`);

--
-- Индексы таблицы `firm`
--
ALTER TABLE `firm`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Индексы таблицы `importance`
--
ALTER TABLE `importance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Индексы таблицы `kontakts`
--
ALTER TABLE `kontakts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `kontakts_index_0` (`type_kontakt_id`),
  ADD KEY `kontakts_index_1` (`author`),
  ADD KEY `kontakts_index_2` (`last_editor`);

--
-- Индексы таблицы `links`
--
ALTER TABLE `links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `links_index_0` (`type_link_id`);

--
-- Индексы таблицы `link_pas`
--
ALTER TABLE `link_pas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `link_pas_index_0` (`link_id`),
  ADD KEY `link_pas_index_1` (`author`),
  ADD KEY `link_pas_index_2` (`last_editor`);

--
-- Индексы таблицы `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `partners`
--
ALTER TABLE `partners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `partners_index_0` (`city_id`),
  ADD KEY `partners_index_1` (`brand_id`),
  ADD KEY `partners_index_2` (`district_id`),
  ADD KEY `partners_index_3` (`firm_id`),
  ADD KEY `partners_index_4` (`type_partner_id`);

--
-- Индексы таблицы `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `posts_index_0` (`author`),
  ADD KEY `posts_index_1` (`last_editor`),
  ADD KEY `posts_index_2` (`importance_id`),
  ADD KEY `posts_index_3` (`type_post_id`);

--
-- Индексы таблицы `surveys`
--
ALTER TABLE `surveys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `surveys_index_0` (`post_id`),
  ADD KEY `surveys_index_1` (`type_answer_id`);

--
-- Индексы таблицы `survey_answers`
--
ALTER TABLE `survey_answers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `survey_answers_index_0` (`survey_id`);

--
-- Индексы таблицы `type_answer`
--
ALTER TABLE `type_answer`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Индексы таблицы `type_kontakt`
--
ALTER TABLE `type_kontakt`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `type_kontakt_index_0` (`city_id`);

--
-- Индексы таблицы `type_link`
--
ALTER TABLE `type_link`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Индексы таблицы `type_partner`
--
ALTER TABLE `type_partner`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Индексы таблицы `type_post`
--
ALTER TABLE `type_post`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Индексы таблицы `user_answers`
--
ALTER TABLE `user_answers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `user_answers_index_0` (`user_id`),
  ADD KEY `user_answers_index_1` (`survey_answer_id`),
  ADD KEY `user_answers_index_2` (`survey_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `brand`
--
ALTER TABLE `brand`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT для таблицы `district`
--
ALTER TABLE `district`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `firm`
--
ALTER TABLE `firm`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `importance`
--
ALTER TABLE `importance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `kontakts`
--
ALTER TABLE `kontakts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `links`
--
ALTER TABLE `links`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `link_pas`
--
ALTER TABLE `link_pas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT для таблицы `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT для таблицы `partners`
--
ALTER TABLE `partners`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT для таблицы `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT для таблицы `surveys`
--
ALTER TABLE `surveys`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `survey_answers`
--
ALTER TABLE `survey_answers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT для таблицы `type_answer`
--
ALTER TABLE `type_answer`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `type_kontakt`
--
ALTER TABLE `type_kontakt`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `type_link`
--
ALTER TABLE `type_link`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `type_partner`
--
ALTER TABLE `type_partner`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `type_post`
--
ALTER TABLE `type_post`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `user_answers`
--
ALTER TABLE `user_answers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `cities`
--
ALTER TABLE `cities`
  ADD CONSTRAINT `cities_ibfk_1` FOREIGN KEY (`visible_cities`) REFERENCES `cities` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `district`
--
ALTER TABLE `district`
  ADD CONSTRAINT `district_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `kontakts`
--
ALTER TABLE `kontakts`
  ADD CONSTRAINT `kontakts_ibfk_1` FOREIGN KEY (`type_kontakt_id`) REFERENCES `type_kontakt` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `kontakts_ibfk_2` FOREIGN KEY (`author`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `kontakts_ibfk_3` FOREIGN KEY (`last_editor`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `links`
--
ALTER TABLE `links`
  ADD CONSTRAINT `links_ibfk_1` FOREIGN KEY (`type_link_id`) REFERENCES `type_link` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `link_pas`
--
ALTER TABLE `link_pas`
  ADD CONSTRAINT `link_pas_ibfk_1` FOREIGN KEY (`link_id`) REFERENCES `links` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `link_pas_ibfk_2` FOREIGN KEY (`author`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `link_pas_ibfk_3` FOREIGN KEY (`last_editor`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `partners`
--
ALTER TABLE `partners`
  ADD CONSTRAINT `partners_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `partners_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brand` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `partners_ibfk_3` FOREIGN KEY (`district_id`) REFERENCES `district` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `partners_ibfk_4` FOREIGN KEY (`firm_id`) REFERENCES `firm` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `partners_ibfk_5` FOREIGN KEY (`type_partner_id`) REFERENCES `type_partner` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`author`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`last_editor`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `posts_ibfk_3` FOREIGN KEY (`importance_id`) REFERENCES `importance` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `posts_ibfk_4` FOREIGN KEY (`type_post_id`) REFERENCES `type_post` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `surveys`
--
ALTER TABLE `surveys`
  ADD CONSTRAINT `surveys_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `surveys_ibfk_2` FOREIGN KEY (`type_answer_id`) REFERENCES `type_answer` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `survey_answers`
--
ALTER TABLE `survey_answers`
  ADD CONSTRAINT `survey_answers_ibfk_1` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `type_kontakt`
--
ALTER TABLE `type_kontakt`
  ADD CONSTRAINT `type_kontakt_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_answers`
--
ALTER TABLE `user_answers`
  ADD CONSTRAINT `user_answers_ibfk_1` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_answers_ibfk_2` FOREIGN KEY (`survey_answer_id`) REFERENCES `survey_answers` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_answers_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
