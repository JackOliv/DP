<?php
// Начинаем сессию и включаем необходимые файлы
require_once "bd_connect1.php"; // Подключаем ваш файл с соединением с базой данных
require_once "allfunc.php"; 
$connect = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connect, "UTF8");
// Проверяем, предоставлен ли идентификатор комментария в URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Обрабатываем недопустимый идентификатор комментария
    die("Неверный идентификатор комментария.");
}

// Получаем идентификатор комментария из URL
$comment_id = $_GET['id'];
$sql_thispost = "SELECT post_id FROM comments where id = $comment_id";
$stmt_thispost = $connect->query($sql_thispost);
// Подготавливаем SQL-запрос для удаления комментария
$sql_delete_comment = "UPDATE comments SET is_public = 1 WHERE id = ?";
$stmt_delete_comment = $connect->prepare($sql_delete_comment);

if (!$stmt_delete_comment) {
    // Обрабатываем ошибку подготовки запроса
    die("Ошибка подготовки запроса на удаление: (" . $connect->errno . ") " . $connect->error);
}

// Привязываем параметры и выполняем запрос
$stmt_delete_comment->bind_param("i", $comment_id);
if (!$stmt_delete_comment->execute()) {
    // Обрабатываем ошибку выполнения запроса
    die("Ошибка удаления комментария: (" . $stmt_delete_comment->errno . ") " . $stmt_delete_comment->error);
}

// Закрываем запрос
$stmt_delete_comment->close();

foreach($stmt_thispost as $post){
    $thispost = $post['post_id'];
}
// Перенаправляем пользователя обратно на предыдущую страницу (или куда угодно еще)
$ip = findip();
$action = "Вернул комментарий";

$sql_insert = "INSERT INTO logs (postid, user, dateview, actions) VALUES (?, ?, NOW(),?)";
$stmt_insert = $connect->prepare($sql_insert);
if (!$stmt_insert) {
    die("Ошибка подготовки запроса: (" . $connect->errno . ") " . $connect->error);
}
$stmt_insert->bind_param("iss", $thispost, $ip, $action);
if (!$stmt_insert->execute()) {
    die("Ошибка выполнения запроса: (" . $stmt_insert->errno . ") " . $stmt_insert->error);
}
$stmt_insert->close();
header("Location: {$_SERVER['HTTP_REFERER']}");
exit();

