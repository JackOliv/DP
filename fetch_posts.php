<?php
    // Подключение файла с константами для подключения к базе данных
    require_once "bd_connect1.php";

    // Подключение к базе данных
    $connect = mysqli_connect(HOST, USER, PW, DB);
    // Установка кодировки соединения
    mysqli_set_charset($connect, "UTF8");

    $sql_posts = "SELECT * FROM posts ORDER BY datepost DESC";
    $result_posts = $connect->query($sql_posts);
    
    if ($result_posts->num_rows > 0) {
        while($post = $result_posts->fetch_assoc()) {
            $post_id = $post['id'];
            echo "<h2>" . $post['title'] . "</h2>";
            echo "<p>" . $post['text'] . "</p>";
            if ($post['photo']) {
                echo "<img src='" . $post['photo'] . "' alt='Post image'>";
            }
            echo "<p>Дата поста: " . $post['datepost'] . "</p>";
    
            // Получение комментариев для текущего поста
            $sql_comments = "SELECT * FROM comments WHERE postid = $post_id ORDER BY datepost DESC";
            $result_comments = $connect->query($sql_comments);
    
            if ($result_comments->num_rows > 0) {
                echo "<h3>Комментарии:</h3>";
                while($comment = $result_comments->fetch_assoc()) {
                    echo "<div class='comment'>";
                    echo "<p>" . $comment['text'] . "</p>";
                    if ($comment['photo']) {
                        echo "<img src='" . $comment['photo'] . "' alt='Comment image'>";
                    }
                    echo "<p>Дата комментария: " . $comment['datepost'] . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p>Нет комментариев</p>";
            }
        }
    } else {
        echo "<p>Нет постов</p>";
    }

    // Закрытие соединения с базой данных
    mysqli_close($connect);
?>
