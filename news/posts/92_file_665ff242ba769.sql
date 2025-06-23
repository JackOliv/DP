-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Май 28 2024 г., 10:42
-- Версия сервера: 10.0.38-MariaDB-0ubuntu0.16.04.1
-- Версия PHP: 7.0.33-0ubuntu0.16.04.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `spravkatest`
--

-- --------------------------------------------------------

--
-- Структура таблицы `cities`
--

CREATE TABLE `cities` (
  `idcity` int(3) NOT NULL,
  `namecity` varchar(50) NOT NULL,
  `regioncity` int(3) NOT NULL,
  `ipcity` varchar(50) NOT NULL,
  `isenabled` tinyint(1) NOT NULL,
  `visiblecities` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `cities`
--

INSERT INTO `cities` (`idcity`, `namecity`, `regioncity`, `ipcity`, `isenabled`, `visiblecities`) VALUES
(1, 'Томск', 70, '10.70.', 1, '2'),
(2, 'Северск', 70, '10.70.', 0, ''),
(3, 'Новосибирск', 54, '10.54.', 1, '1,2'),
(4, 'Мельниково', 70, '10.70.', 0, '1'),
(5, 'Колпашево', 70, '10.70.', 0, '1'),
(6, 'Асино', 70, '10.70.', 0, '1'),
(7, 'Парабель', 70, '10.70.', 1, '1,2,3,4,5,6');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `cities`
--
ALTER TABLE `cities`
  ADD UNIQUE KEY `idcity` (`idcity`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `cities`
--
ALTER TABLE `cities`
  MODIFY `idcity` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
