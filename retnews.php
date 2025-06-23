<?php
require_once "bd_connect1.php"; // Подключаем ваш файл с соединением с базой данных
require_once "allfunc.php"; 
$connect = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connect, "UTF8");
// Проверяем, предоставлен ли идентификатор комментария в URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Обрабатываем недопустимый идентификатор комментария
    die("Неверный идентификатор поста.");
}

// Получаем идентификатор комментария из URL
$post_id = $_GET['id'];

// Подготавливаем SQL-запрос для удаления комментария
$sql_delete_post = "UPDATE posts SET is_public = 1 WHERE id = ?";
$stmt_delete_post = $connect->prepare($sql_delete_post);

if (!$stmt_delete_post) {
    // Обрабатываем ошибку подготовки запроса
    die("Ошибка подготовки запроса на удаление: (" . $connect->errno . ") " . $connect->error);
}

// Привязываем параметры и выполняем запрос
$stmt_delete_post->bind_param("i", $post_id);
if (!$stmt_delete_post->execute()) {
    // Обрабатываем ошибку выполнения запроса
    die("Ошибка удаления комментария: (" . $stmt_delete_post->errno . ") " . $stmt_delete_post->error);
}

// Закрываем запрос
$stmt_delete_post->close();

// Перенаправляем пользователя обратно на предыдущую страницу (или куда угодно еще)
$post_id = $_GET['id'];
            $ip = findip();
            $action = "Вернул запись";
            if (!is_numeric($post_id)) {
                die("Неверный ID поста.");
            }
                 $sql_insert = "INSERT INTO logs (postid, user, dateview, actions) VALUES (?, ?, NOW(),?)";
                 $stmt_insert = $connect->prepare($sql_insert);
                 if (!$stmt_insert) {
                     die("Ошибка подготовки запроса: (" . $connect->errno . ") " . $connect->error);
                 }
                 $stmt_insert->bind_param("iss", $post_id, $ip, $action);
                 if (!$stmt_insert->execute()) {
                     die("Ошибка выполнения запроса: (" . $stmt_insert->errno . ") " . $stmt_insert->error);  
                 }
            if (isset($stmt_insert)) {
                $stmt_insert->close();
            }
header("Location: {$_SERVER['HTTP_REFERER']}");
exit();

