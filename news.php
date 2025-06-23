<?php
require_once "bd_connect1.php"; // Подключение к базе данных
require "allfunc.php"; // Подключение файла с функциями

// Подключение к базе данных
$connect = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connect, "UTF8");

$sql_types = "SELECT id, name, code FROM posttypes";
$result_types = $connect->query($sql_types);

$types = [];
if ($result_types && $result_types->num_rows > 0) {
    while ($row = $result_types->fetch_assoc()) {
        $types[] = $row;
    }
}


$filter = isset($_GET['filter']) ? intval($_GET['filter']) : null;
$sql_posts = "SELECT p.*, pt.name AS post_type_name 
              FROM posts p
              LEFT JOIN posttypes pt ON p.post_type = pt.id";


if ($filter) {
    $sql_posts .= " WHERE post_type = $filter";
}


$sql_posts .= " ORDER BY 
    CASE WHEN dateimportance >= CURRENT_DATE THEN 0 ELSE 1 END,
    CASE WHEN dateimportance >= CURRENT_DATE THEN dateimportance END ASC,
    CASE WHEN dateimportance < CURRENT_DATE THEN date_created END DESC";

$result_posts = $connect->query($sql_posts);

// Проверка, является ли пользователь администратором
$isadmin = 0;
if(isset($_SESSION['login'])){
    if($_SESSION['login'] == 'admin'){
        $isadmin = 1;
    }
}
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
    <style>
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
        h3 {
            word-wrap: break-word;
            word-break: break-word;
        }
        .blinking {
            animation: blinkingText 2.8s infinite;
        }
        @keyframes blinkingText {
            0% { background: #ececec; }
            40% { background: #fab9b9; }
            100% { background: #ececec; }
        }
    </style>
</head>
<body>
    <?php 
        $navbarType = "usernews"; 
        include 'topnavbar.php';
    ?>
<div class="container">
    <h1>Записи</h1>
    <div class="btn-group">
    <a href="news.php" style="margin-bottom: 10px;" class="btn <?php echo !isset($_GET['filter']) ? 'btn-primary' : 'btn-default'; ?>">Все</a>
    <?php foreach ($types as $type): ?>
        <a href="news.php?filter=<?php echo $type['id']; ?>" style="margin-bottom: 10px;" class="btn <?php echo (isset($_GET['filter']) && $_GET['filter'] == $type['id']) ? 'btn-primary' : 'btn-default'; ?>">
            <?php echo htmlspecialchars($type['name']); ?>
        </a>
    <?php endforeach; ?>
</div>
<?php
$current_user_id = finduser(3); // Получение текущего ID пользователя
// Собераем все связи постов и опросов
$survey_map = []; // post_id => survey_id
$survey_query = "SELECT id, post_id FROM surveys";
$survey_result = $connect->query($survey_query);
if ($survey_result && $survey_result->num_rows > 0) {
    while ($row = $survey_result->fetch_assoc()) {
        $survey_map[$row['post_id']] = $row['id'];
    }
}
// Потом загрузим все ответы пользователя
$user_answers = []; // survey_id => true
$user_answer_query = "SELECT survey_id FROM user_answers WHERE user = $current_user_id";
$user_answer_result = $connect->query($user_answer_query);
if ($user_answer_result && $user_answer_result->num_rows > 0) {
    while ($row = $user_answer_result->fetch_assoc()) {
        $user_answers[$row['survey_id']] = true;
    }
}
if ($result_posts && $result_posts->num_rows > 0) {
    while ($post = $result_posts->fetch_assoc()) {
        $isanswered = true; // Флаг, указывающий на наличие ответа
        $isviewed = false;
        $selected_pharmacies = explode(',', $post['visibility']);
        $enable = false;
        foreach ($selected_pharmacies as $pharmacy) {
            if ($current_user_id == $pharmacy || $current_user_id == 100000 || $current_user_id == 200000 || $pharmacy == 0) {
                $enable = true;
                break;
            }
        }
        if (($enable && $post['is_public'] == 1) || $isadmin == 1) {
            $post_id = $post['id'];
            // SQL-запрос для получения комментариев к записи
            $sql_comments = "SELECT * FROM comments WHERE post_id = $post_id AND is_public = 1";
            $result_comments = $connect->query($sql_comments);
            // SQL-запрос для получения логов (просмотров)
            $sql_logs = "SELECT * FROM logs WHERE postid = $post_id";
            $result_logs = $connect->query($sql_logs);
            $has_comment = false;
            $has_answer = false;
            // Проверка комментариев
            if ($result_comments && $result_comments->num_rows > 0) {
                while ($comment = $result_comments->fetch_assoc()) {
                    $user_id_to_check = $comment['user_id'];
                    if (($user_id_to_check == $current_user_id && !isset($_SESSION['user_id'])) || (isset($_SESSION['user_id'])) || ($current_user_id == 100000)) {
                        $has_comment = true;
                        break;
                    }
                }
            }
            // Проверка ответа на опрос (если пост - опрос)
            if ($post['post_type'] == '2') {
                if (isset($survey_map[$post_id])) {
                    $survey_id = $survey_map[$post_id];
                    if (isset($user_answers[$survey_id])) {
                        $has_answer = true;
                    }
                }
            }
            // Итоговая проверка
            if (!$has_comment && !$has_answer) {
                if (!((isset($_SESSION['user_id'])) || ($current_user_id == 100000))) {
                    $isanswered = false;
                }
            }
            // Проверка на просмотр
            if ($result_logs && $result_logs->num_rows > 0) {
                while ($log = $result_logs->fetch_assoc()) {
                    if ($log['user'] == findip()) {
                        $isviewed = true;
                        break;
                    }
                }
            } elseif ((isset($_SESSION['user_id'])) || ($current_user_id == 100000)) {
                $isviewed = true;
            } ?>
                <div class="panel panel-default">
                    <div class="panel-heading <?php if (($post['post_type'] == '2'  && !$isanswered) || (($post['importance'] == 'important'||  $post['dateimportance'] >= date("Y-m-d")) && !$isviewed)) echo ' blinking'; ?>">
                        <?php if (isset($_SESSION["user_id"]) && $post['is_public'] == 1) { ?>
                            <div class="btn-group pull-right" style="margin-top:-6px">
                                <a href="deletenews.php?id=<?php echo $post_id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены, что хотите удалить эту запись?');">Удалить</a>
                            </div>
                        <?php } 
                        if($post['is_public'] == 0 && $isadmin == 1){
                            $postid = $post['id'];
                            echo "<div class='btn-group pull-right' style='margin-top:-6px'>";
                            echo "<a href='retnews.php?id=$postid' class='btn btn-warning btn-sm' onclick='return confirmReturn();'>Вернуть новость</a>";
                            echo "</div>";
                        }
                        ?>
                        <?php 
                            // Отображение имени редактора, если запись была отредактирована
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
                            }
                        ?></div>
                        <h3 class="panel-title">
                        <a href='post.php?id=<?php echo $post_id; ?>'>
                            <b>
                                <?php 
                                    // Проверка для актуальности
                                    if ($post['dateimportance'] >= date("Y-m-d")) echo 'Актуально ';
                                    // Проверка для типа поста, выводим название типа из базы
                                    if ($post['post_type_name'] != 'Новости') echo $post['post_type_name']. " ";
                                    // Проверка для важности
                                    if ($post['importance'] == 'important') echo 'Важный ';
                                ?>
                            </b>
                            <?php echo htmlspecialchars($post['title']); ?>
                        </a>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div ><a style="word-wrap: break-word; word-break: break-word;" href='post.php?id=<?php echo $post_id; ?>'><?php echo htmlspecialchars_decode(stripslashes($post['short_description'])); ?></a></div>
                        <div class='btn-group pull-right'>
                            <div>Дата записи: <?php echo $post['date_created']; ?></div>
                            <?php 
                            if ($post['date_created'] != $post['date_updated']) {
                                echo "<div>Отредактирован в: " . htmlspecialchars($post['date_updated']) . "</div>";
                            }
                            if ($post['dateimportance'] >= date("Y-m-d")) {
                                echo "<div>Актуально до: " . htmlspecialchars($post['dateimportance']) . "</div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
    <?php
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
    function confirmReturn() {
        return confirm('Вы уверены, что хотите вернуть этот комментарий?');
    }
</script>
</body>
</html>
