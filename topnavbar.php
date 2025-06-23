<?php 
require_once "bd_connect1.php";
$connect = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connect, "UTF8");
$user_id = finduser(3);
$isansw = true; // Изначально считаем, что все посты отвечены

// Загружаем все посты-опросы
$sql = "SELECT * FROM posts WHERE post_type = '2' AND is_public = 1";
$result = $connect->query($sql);

// Загружаем все ответы пользователя
$sqlansw = "SELECT * FROM user_answers WHERE user = $user_id";
$resultansw = $connect->query($sqlansw);

// Изначально считаем, что всё пройдено
$isanswered = true;

if (isset($_SESSION['user_id']) || $user_id == 100000) {
    $isanswered = true;
} 
elseif ($result && $result->num_rows > 0) {
    $needed_posts = [];
    while ($post = $result->fetch_assoc()) {
        $selected = explode(',', $post['visibility']);
        if (in_array($user_id, $selected) || in_array(0, $selected) || in_array($user_id, [100000, 200000])) {
            $needed_posts[] = $post['id'];
        }
    }

    if (count($needed_posts) == 0) {
        $isanswered = true;
    } else {
        // Собираем все посты, на которые пользователь ответил через user_answers
        $answered_posts_by_answer = [];

        if ($resultansw && $resultansw->num_rows > 0) {
            while ($user_answer = $resultansw->fetch_assoc()) {
                $survey_id = $user_answer['survey_id'];
                $survey_query = "SELECT post_id FROM surveys WHERE id = $survey_id";
                $survey_result = $connect->query($survey_query);
                if ($survey_result && $survey_result->num_rows > 0) {
                    $survey = $survey_result->fetch_assoc();
                    $answered_posts_by_answer[] = $survey['post_id'];
                }
            }
        }

        // Теперь проверяем каждый нужный пост
        foreach ($needed_posts as $needed_post_id) {

            $has_answer = false;

            // 1. Проверка по комментариям
            $sql_comments = "SELECT * FROM comments WHERE post_id = $needed_post_id AND is_public = 1 AND user_id = $user_id";
            $result_comments = $connect->query($sql_comments);
            if ($result_comments && $result_comments->num_rows > 0) {
                $has_answer = true;
            }

            // 2. Проверка по ответам на опросы
            if (in_array($needed_post_id, $answered_posts_by_answer)) {
                $has_answer = true;
            }

            // Если нет ни комментария, ни ответа — пользователь НЕ прошёл
            if (!$has_answer) {
                $isanswered = false;
                break;
            }
        }
    }
}
?>

<link rel="stylesheet" href="/css/avstyles.css">
    <link rel="stylesheet" href="/css/avstyles.css">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/bootstrap-theme.min.css">
    <link rel="stylesheet" type="text/css" href="/css/jquery.dataTables.css">
    <style>
        .blinking {
            animation: blinkingText 2.8s infinite;
        }
        @keyframes blinkingText {
            0% { background: #ececec; }
            40% { background: #fab9b9; }
            100% { background: #ececec; }
        }
    </style>
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            
            <ul class="nav navbar-nav">
              <?php
              $currentPage = basename($_SERVER['PHP_SELF']); // Получаем имя текущего файла, например "index.php"

              if (isset($navbarType) && $navbarType === "admin") { 
                  echo '<div class="navbar-header">';
                  echo '<a class="navbar-brand" href="admin.php">Прайс-листы</a>';
                  echo '</div>';
                  echo '<li'.($currentPage == 'cities.php' ? ' class="active"' : '').'><a href="cities.php">Города</a></li>';
                  echo '<li'.($currentPage == 'brand.php' ? ' class="active"' : '').'><a href="brand.php">Бренды</a></li>';
                  echo '<li'.($currentPage == 'firm.php' ? ' class="active"' : '').'><a href="firm.php">Юр. лица</a></li>';
                  echo '<li'.($currentPage == 'links.php' ? ' class="active"' : '').'><a href="links.php">Ссылки</a></li>';
                  echo '<li'.($currentPage == 'admkontakts.php' ? ' class="active"' : '').'><a href="admkontakts.php">Контакты</a></li>';
                  echo '<li><a href="index.php" target="_blank">Главная</a></li>'; // Главное открыть в новом окне — выделять не надо
              } 
              else if (isset($navbarType) && ($navbarType === "user" || $navbarType === "usernews")) {
                  echo '<li'.($currentPage == 'news.php' ? ' class="active"' : '').'><a class="navbar-brand '. (!$isanswered ? 'blinking' : '') .'" href="news.php">Новости</a></li>';
                  echo '<li'.($currentPage == 'index.php' ? ' class="active"' : '').'><a href="index.php">Справка</a></li>';
                  echo '<li'.($currentPage == 'indexlinks.php' ? ' class="active"' : '').'><a href="indexlinks.php">Полезные ссылки</a></li>';
                  echo '<li'.($currentPage == 'kontakts.php' ? ' class="active"' : '').'><a href="kontakts.php">Контакты</a></li>';
              }
              ?>
	            
            </ul>
			<ul class="nav navbar-nav navbar-right">
            <?php 
            if (!isset($_SESSION["user_id"])) {
                echo "<li><a href='login.php'>Войти</a></li>";
            } else if (isset($navbarType) && $navbarType === "usernews"){
                echo "<li><p style='margin-top: 15px'>Пользователь: ". $_SESSION['name'] . "    </p></li>";
                echo "<li><a href='addnews.php'>Добавить</a></li>";
                echo "<li><a href='logout.php'>Выйти</a></li>";
            } 
            else {
                echo "<li><p style='margin-top: 15px'>Пользователь: ". $_SESSION['name'] . "    </p></li>";
                echo "<li><a href='logout.php'>Выйти</a></li>";
            }
            ?>
        </ul> 
        </div>
    </nav>
    <?php include "topmenu.php"; ?>