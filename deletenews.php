<?php
require_once "bd_connect1.php";
require_once "allfunc.php";
checkAuth();
$connect = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connect, "UTF8");
$post_id = $_GET['id'];
// Удаляем комментарии, связанные с этим постом
$sql_delete_comments = "UPDATE comments SET is_public = 0 WHERE id = $post_id";
if (!mysqli_query($connect, $sql_delete_comments)) {
    echo "Ошибка удаления комментариев: " . mysqli_error($connect);
    exit;
}
// Удаляем сам пост
$sql_delete_post = "UPDATE posts SET is_public = 0 WHERE id = $post_id";
if (mysqli_query($connect, $sql_delete_post)) {
    $post_id = $_GET['id'];
            $ip = findip();
            $action = "Удалил запись";
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
    header("Location: news.php");
    exit;
} else {
    echo "Ошибка удаления поста: " . mysqli_error($connect);
}