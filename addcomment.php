<?php
require_once "bd_connect1.php";
require_once "allfunc.php";
$connect = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connect, "UTF8");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION["user_id"])) {
        $post_id = $_POST['post_id'];
        $text = mysqli_real_escape_string($connect, $_POST['text']);
        $user_id = finduser(3);
        $is_public = 1; 
    }
    else{
        $post_id = $_POST['post_id'];
        $text = mysqli_real_escape_string($connect, $_POST['text']);
        $user_id = $_SESSION['user_id'];
        $is_public = 1; 
    } // Можно изменить на 0, если комментарии должны проходить модерацию
    $attachment_files = [];
    if (!empty($_FILES['attachment_files']['name'][0])) {
         foreach ($_FILES['attachment_files']['name'] as $key => $filename) {
            // Генерируем уникальное имя файла с использованием ID поста
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $new_filename = $post_id . '_' . uniqid() . '.' . $extension;
            $target_dir = "news/comments/";
            $target_file = $target_dir . $new_filename;
            if (move_uploaded_file($_FILES['attachment_files']['tmp_name'][$key], $target_file)) {
                $attachment_files[] = $target_file;
            }
        }
    }
    $attachment_files_json = json_encode($attachment_files);
    $sql_insert = "INSERT INTO comments (post_id, user_id, text, photo, is_public, date_created)
                   VALUES ('$post_id', '$user_id', '$text', '$attachment_files_json', '$is_public', NOW())";
    if ($connect->query($sql_insert) === TRUE) {
        $ip = findip();
        $action = "Оставил комментарий";

        $sql_insert = "INSERT INTO logs (postid, user, dateview, actions) VALUES (?, ?, NOW(),?)";
        $stmt_insert = $connect->prepare($sql_insert);
        if (!$stmt_insert) {
            die("Ошибка подготовки запроса: (" . $connect->errno . ") " . $connect->error);
        }
        $stmt_insert->bind_param("iss", $post_id, $ip, $action);
        if (!$stmt_insert->execute()) {
            die("Ошибка выполнения запроса: (" . $stmt_insert->errno . ") " . $stmt_insert->error);
        }

        $stmt_insert->close();
        header("Location: {$_SERVER['HTTP_REFERER']}");
        exit;
    } else {
        echo "Ошибка: " . $sql_insert . "<br>" . $connect->error;
    }
}
