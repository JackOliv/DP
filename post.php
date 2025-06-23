<?php
require_once "bd_connect1.php";
require "allfunc.php";
$connect = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connect, "UTF8");
$post_id = $_GET['id'];
$ip = findip();
$action = "Просмотрел";

// Проверка, является ли ID поста числом
if (!is_numeric($post_id)) {
    die("Неверный ID поста.");
}
function getAnswer($survey_id) {
    $connect = mysqli_connect(HOST, USER, PW, DB);
    $answer = null; // Устанавливаем в null, чтобы избежать проблем
    $stmt = $connect->prepare("SELECT answer FROM user_answers WHERE survey_id = ?");

    if (!$stmt) {
        return "Ошибка подготовки запроса: (" . $connect->errno . ") " . $connect->error;
    }

    $stmt->bind_param("i", $survey_id);
    $stmt->execute();
    $stmt->bind_result($fetched_answer);

    if ($stmt->fetch()) {
        $answer = $fetched_answer; // Получаем значение
    }

    $stmt->close();
    return $answer ?? ''; // Если ответа нет, возвращаем пустую строку
}

// Подготовка SQL-запроса для проверки существующей записи
$sql_view = "SELECT * FROM logs WHERE postid = ? AND user = ?";
$stmt_view = $connect->prepare($sql_view);
if (!$stmt_view) {
    die("Ошибка подготовки запроса: (" . $connect->errno . ") " . $connect->error);
}
$stmt_view->bind_param("is", $post_id, $ip);
$stmt_view->execute();
$result_view = $stmt_view->get_result();
if ($result_view) {
    // Подготовка SQL-запроса для вставки новой записи
    $sql_insert = "INSERT INTO logs (postid, user, dateview, actions) VALUES (?, ?, NOW(),?)";
    $stmt_insert = $connect->prepare($sql_insert);
    if (!$stmt_insert) {
        die("Ошибка подготовки запроса: (" . $connect->errno . ") " . $connect->error);
    }
    $stmt_insert->bind_param("iss", $post_id, $ip, $action);
    if (!$stmt_insert->execute()) {
        die("Ошибка выполнения запроса: (" . $stmt_insert->errno . ") " . $stmt_insert->error);
    }
} else {
    die("Ошибка выполнения запроса: (" . $stmt_view->errno . ") " . $stmt_view->error);
}
$stmt_view->close();
if (isset($stmt_insert)) {
    $stmt_insert->close();
}

// Получение данных о посте
$sql_posts = "SELECT * FROM posts WHERE id = ?";
$stmt_posts = $connect->prepare($sql_posts);
$stmt_posts->bind_param("i", $post_id);
$stmt_posts->execute();
$result_posts = $stmt_posts->get_result();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['post_id'])) {
    $user = finduser(3); // Определяем пользователя

    $null = NULL;
    $query = "INSERT INTO user_answers (user, survey_id, survey_answer_id, answer) VALUES (?, ?, ?, ?)";
    $stmt = $connect->prepare($query);
    if ($stmt === false) {
        die('Ошибка подготовки запроса: ' . $connect->error);
    }
    if(isset( $_POST['answers'])){

        foreach ($_POST['answers'] as $question_id => $answer) {
            if (is_array($answer)) { // Чекбоксы
                foreach ($answer as $answer_id) {
                    $stmt->bind_param("iiis", $user, $question_id, $answer_id, $null);

                    $stmt->execute();
                }
            } else { // Радиокнопка или текстовое поле
                if (is_numeric($answer)) {
                    $stmt->bind_param("iiis", $user, $question_id, $answer, $null);
                } else {
                    $stmt->bind_param("iiis", $user, $question_id, $null, $answer);
                }
                if ($stmt->execute()) {
                    echo "<script>alert('Опрос успешно завершен!');</script>";
                } else {
                    echo "Ошибка выполнения запроса: " . $stmt->error;
                }
            }
        }
    }
    $stmt->close();
}


// Проверка, является ли пользователь администратором
$isadmin = 0;
if (isset($_SESSION['login'])) {
    if ($_SESSION['login'] == 'admin') {
        $isadmin = 1;
    }
}
$connect->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новости</title>
    <link rel="stylesheet" href="/css/avstyles.css">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/bootstrap-theme.min.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        *{
            word-wrap: break-word; word-break: break-word;
        }
        body {
            margin: 0;
            padding-bottom: 20px;
        }
        .navbar {
            margin-bottom: 20px;
        }
        .comment {
            margin-left: 20px;
            border-left: 2px solid #ccc;
            padding-left: 10px;
            margin-bottom: 10px;
        }
        img {
            max-width: 100%;
            height: auto;
            margin-bottom: 10px;
        }
        .panel-body p {
            word-wrap: break-word;
            word-break: break-word;
        }
        input[type="text"], select {
            width: 100%;
            padding: 5px;
            display: block;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            transition: 0.3s;
            outline: none;
        }

        input[type="text"]:focus, select:focus {
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.5);
        }
        h3 {
           
            word-wrap: break-word;
            word-break: break-word;
        }
        .survey{
            margin: 0;
            margin-bottom: 10px;
            padding: 0;
        }
        .question {
            background:rgb(235, 235, 235);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        input[type='text'], input[type='radio'], input[type='checkbox'] {
            margin-right: 10px;
        }
        .survey-container {
            background:rgb(247, 247, 247);
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .survey-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .survey-header h3 {
            margin: 0;
        }

        .survey-header a {
            margin-left: 20px;
        }

    </style>
</head>
<body>
<?php 
        $navbarType = "user"; 
        include 'topnavbar.php';
    ?>
<a class="btn-primary btn" href="news.php" style="margin-left:20px">Назад</a>
<div class="container">
    <?php
    if ($result_posts && $result_posts->num_rows > 0) {
        while ($post = $result_posts->fetch_assoc()) {
            $post_id = $post['id'];
    ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?php if (isset($_SESSION["user_id"])) { ?>
            <div class="btn-group pull-right" style="margin-top:-6px" >
                <a href="editnews.php?id=<?php echo $post_id; ?>" style="margin-right:10px" class="btn btn-primary btn-sm">Редактировать</a>
                <a href="whoview.php?id=<?php echo $post_id; ?>" style="margin-right:10px"  class="btn btn-primary btn-sm">Просмотреть лог записи</a>
                <a href="whoansver.php?id=<?php echo $post_id; ?>" class="btn btn-primary btn-sm">Кто ответил</a>
            </div>
        <?php } ?>
        <?php
            // Отображение имени редактора, если запись была отредактирована другим пользователем
            if ($post['autor'] != $post['lasteditor']) {
                echo "<div class='btn-group pull-right' style='margin-top:-3px; margin-right: 5px'>Отредактирован:";
                $sql_user = "SELECT name FROM users WHERE id = " . $post['lasteditor'];
                $result_user = $connect->query($sql_user);
                if ($result_user && $result_user->num_rows > 0) {
                    $user = $result_user->fetch_assoc();
                echo htmlspecialchars($user['name']);
            }
            echo "</div>";
        }
     ?>
    <div class="btn-group pull-right" style="margin-top:-3px; margin-right: 5px">Автор: <?php
        $sql_user = "SELECT name FROM users WHERE id = " . $post['autor'];
        $result_user = $connect->query($sql_user);
        if ($result_user && $result_user->num_rows > 0) {
            $user = $result_user->fetch_assoc();
            echo htmlspecialchars($user['name']);
        }?></div>
        <h3 class="panel-title"><?php echo htmlspecialchars($post['title']); ?></h3>
    </div>
    <div class="panel-body">
    <div><?php echo htmlspecialchars_decode(stripslashes($post['full_description'])); ?></div>
    <div class="foto">
        <?php
        if ($post['attachment_fotos']) {
            $attachments = json_decode($post['attachment_fotos'], true);
            if ($attachments) {
                foreach ($attachments as $file) {
                    $file_name = basename($file);
                    echo "<a href='$file'  target='_blank' ><img style='max-width:250px;margin:2px' src='$file' alt='foto'></img></a>";
                }
            }
        }
        ?>
    </div>
    <?php
    $user = finduser(3);
    $user_answers = [];
    $query = "SELECT survey_id, survey_answer_id FROM user_answers WHERE user = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if (!isset($user_answers[$row['survey_id']])) {
            $user_answers[$row['survey_id']] = [];
        }
        $user_answers[$row['survey_id']][] = $row['survey_answer_id'];
    }
    $stmt->close();
    $query = "SELECT * FROM surveys WHERE post_id = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    if ($result->num_rows > 0) {
        echo "<div class='survey-container'>";
        echo "<div class='survey-header'>";
        echo "<h3 class='survey'>Опрос:</h3>";
        if(isset($_SESSION['user_id'])){
            echo "<a class='btn btn-success' href='view_results.php?post_id=" . $post['id'] . "'>Просмотреть ответы</a>";
        }
        echo "</div>";
        echo "<form action='post.php?id=$post_id' id='surveyform' method='post'>";
        echo "<input type='hidden' name='post_id' value='$post_id'>";
        while ($question = $result->fetch_assoc()) {
            echo "<div class='question'>";
            echo "<p><strong>{$question['question']}</strong></p>";
            // Получаем тип ответа
            $type_answer_id = $question['type_answer_id'];
            // Получаем ответы для текущего вопроса
            $query_answers = "SELECT * FROM survey_answers WHERE question_id = ?";
            $stmt_answers = $connect->prepare($query_answers);
            $stmt_answers->bind_param("i", $question['id']);
            $stmt_answers->execute();
            $answers = $stmt_answers->get_result();
            if ($type_answer_id == 1) {
                echo "<input type='text' name='answers[{$question['id']}]' placeholder='Введите ответ'  >";
            } if ($type_answer_id == 2) {
                // Радио-кнопки (выбор одного варианта)
                if ($answers->num_rows > 0) {
                    while ($answer = $answers->fetch_assoc()) {
                        echo "<label style='font-weight: 400;'>";
                        echo "<input type='radio' name='answers[{$question['id']}]' value='{$answer['id']}' > {$answer['answer']}";
                        echo "</label><br>";
                    }
                }
            } if ($type_answer_id == 3) {
                // Чекбоксы (множественный выбор)
                if ($answers->num_rows > 0) {
                    while ($answer = $answers->fetch_assoc()) {
                        echo "<label style='font-weight: 400;'>";
                        echo "<input type='checkbox' name='answers[{$question['id']}][]' value='{$answer['id']}'> {$answer['answer']}";
                        echo "</label><br>";
                    }
                }
            }
            $stmt_answers->close();
            echo "</div>";
        }
        echo "<button class='btn btn-success' type='submit'>Ответить</button>";
        echo "</form>";
        echo "</div>";
    }
    ?>
    <div>
        <?php
        if ($post['attachment_files']) {
            $attachments = json_decode($post['attachment_files'], true);
            if ($attachments) {
                echo "<div><b>Прикрепленные файлы</b></div>";
                foreach ($attachments as $file) {
                    $file_name = basename($file);
                    echo "<a href='$file' download>Скачать файл $file_name</a><br>";
                }
            }
        }
        ?>
    </div>
        <div class='btn-group pull-right'>
            <div>Дата записи: <?php echo $post['date_created']; ?></div>
            <?php
            if ($post['date_created'] != $post['date_updated']) {
                echo "<div>Отредактирован в:";
                echo htmlspecialchars($post['date_updated']);
                echo "</div>";
            }
            ?>
        </div>
        <?php
        // Получение и отображение комментариев к посту
        $sql_comments = "SELECT * FROM comments WHERE post_id = $post_id ORDER BY date_created DESC";
        $result_comments = $connect->query($sql_comments);
        if ($result_comments && $result_comments->num_rows > 0) {
        ?>
            <h4 style="margin-top:40px">Комментарии:</h4>
            <?php
            $strpriv = "<div><b>Комментарии видны только создателю записи</b></div>";
            while ($comment = $result_comments->fetch_assoc()) {
            if ($comment['user_id'] != finduser(3) && ($post['comment_visibility'] == "private" && !isset($_SESSION['user_id']))){
                if(isset($strpriv)){
                    echo $strpriv;
                    $strpriv = null;
                }
            }
            elseif($comment['is_public']== 1 || $isadmin == 1)
            {
            ?>
                <div class="comment">
                    <p><b><?php
                    // Отображение имени пользователя, оставившего комментарий
                    if ($comment['user_id'] < 9999) {
                        $sql_user = "SELECT name FROM users WHERE id = " . $comment['user_id'];
                        $result_user = $connect->query($sql_user);
                        if ($result_user && $result_user->num_rows > 0) {
                            $user = $result_user->fetch_assoc();
                            echo htmlspecialchars($user['name']);
                        }
                    } elseif($comment['user_id'] == 100000){
                        echo "Офис";
                    } elseif($comment['user_id'] == 200000){
                        echo "Удаленка";
                    }
                    else {
                        $sql_partner = "SELECT itemname, city FROM partners WHERE itemcode = " . $comment['user_id'];
                        $result_partner = $connect->query($sql_partner);

                        if ($result_partner && $result_partner->num_rows > 0) {
                            $partner = $result_partner->fetch_assoc();
                            $sql_city = "SELECT namecity from cities WHERE idcity =". htmlspecialchars($partner['city']);
                            $result_city = $connect->query($sql_city);
                            $city = $result_city->fetch_assoc();
                            echo htmlspecialchars($partner['itemname']) . ', ' . htmlspecialchars($city['namecity']);
                        }
                    }
                    ?></b></p>
                    <?php
                    // Отображение кнопок для удаления или восстановления комментария
                    if (($comment['user_id'] == finduser(3) || isset($_SESSION['user_id'])) && $comment['is_public'] == 1 ) {
                        $commentid = $comment['id'];
                        echo "<div class='btn-group pull-right'>";
                        echo "<a href='delcom.php?id=$commentid' class='btn btn-danger ' onclick='return confirmDelete();'><i class='fas fa-trash'></i></a>";
                        echo "</div>";
                    }
                    if($comment['is_public'] == 0 && $isadmin == 1){
                        $commentid = $comment['id'];
                        echo "<div class='btn-group pull-right'>";
                        echo "<a href='retcom.php?id=$commentid' class='btn btn-warning ' onclick='return confirmReturn();'>Вернуть комментарий</a>";
                        echo "</div>";
                    }
                    ?>
                    <p><?php echo nl2br(htmlspecialchars($comment['text'])); ?></p>
                    <?php
                    // Отображение прикрепленных файлов к комментариям
                    if ($comment['photo']) {
                        $photos = json_decode($comment['photo'], true);
                        if ($photos) {
                            foreach ($photos as $photo) {
                                $photo_name = basename($photo);
                                echo "<a href='$photo' download>Скачать файл $photo_name</a><br>";
                            }
                        }
                    }
                    ?>
                    <p>Дата комментария: <?php echo $comment['date_created']; ?></p>
                </div>
            <?php
        }
        }} else {
        ?>
            <p>Нет комментариев</p>
        <?php
        }
        // Форма для добавления нового комментария
        if ($post['allow_comments']){?>
        <h4>Добавить комментарий:</h4>
        <form action="addcomment.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
            <div class="form-group">
                <textarea name="text" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="attachment_files">Файлы для прикрепления</label>
                <input type="file" class="form-control" id="attachment_files" name="attachment_files[]" multiple>
            </div>
            <button type="submit" class="btn btn-primary">Отправить</button>
        </form>
    </div>
</div>
    <?php
            }else{
                echo "<div><b>Комментарии отключены</b></div>";
            }
        }
    } else {
    ?>
        <p>Нет записей</p>
    <?php
    }
    ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script> 
<script>
    // Подтверждение удаления комментария
    function confirmDelete() {
        return confirm('Вы уверены, что хотите удалить этот комментарий?');
    }
    // Подтверждение восстановления комментария
    function confirmReturn() {
        return confirm('Вы уверены, что хотите вернуть этот комментарий?');
    }
    

</script>
</body>
</html>
