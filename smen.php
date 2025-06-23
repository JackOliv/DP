<?php 
    // Подключение файла с константами для подключения к базе данных
    require_once "bd_connect1.php";
    // Подключение файла с функциями
    require "allfunc.php";
    session_start(); // Начало сессии
    // Подключение к базе данных
    $connect = mysqli_connect(HOST, USER, PW, DB);
    // Установка кодировки соединения
    mysqli_set_charset($connect, "UTF8");

    // SQL запрос на выборку всех записей из таблицы link
    $querycity = "SELECT c1.namecity, p1.itemname, s1.status, s1.startsmen, s1.finishsmen, s1.vahta, s1.datapodachi, s1.datasmen  FROM " . SMENTABLE ." s1 LEFT JOIN ".PARTNERSTABLE." p1 ON s1.idapteki=p1.itemcode LEFT JOIN ".CITIESTABLE." c1 ON p1.city=c1.idcity WHERE s1.status=1";
    // Выполнение запроса
   // echo $querycity;
    $resultsmen = mysqli_query($connect, $querycity);

    // Проверка на ошибки при выполнении запроса
    if (!$resultsmen) {
        die("Ошибка при выполнении запроса: " . mysqli_error($connect));
    }
    $isanswered = true; // Изначально считаем, что все посты отвечены

    // Запрос для выборки всех постов
    $sql_posts = "SELECT * FROM posts  where post_type like 'survey' and is_public = 1";
    $result_posts = $connect->query($sql_posts);
    if(isset($_SESSION['user_id']) || finduser(3) == 100000){
        $isanswered = true;
    }
    elseif ($result_posts && $result_posts->num_rows > 0) {
        while ($post = $result_posts->fetch_assoc()) {
            $selected_pharmacies = explode(',', $post['visibility']);
            $user_id = finduser(3); // Сохраняем результат вызова finduser(3) один раз, чтобы избежать избыточных вызовов

            // Проверяем, входит ли пользователь в выбранные аптеки или имеет определенные ID
            if (in_array($user_id, $selected_pharmacies) || in_array($user_id, [100000, 200000])) {
                $post_id = $post['id'];
                $sql_comments = "SELECT * FROM comments WHERE post_id = $post_id AND is_public = 1 ORDER BY date_created DESC";
                $result_comments = $connect->query($sql_comments);

                if ($result_comments && $result_comments->num_rows > 0) {
                    $answered = false;
                    while ($comment = $result_comments->fetch_assoc()) {
                        // Проверяем, является ли пост опросом и ответил ли пользователь
                        if ($post['post_type'] == 'survey' && $comment['user_id'] == $user_id) {
                            $answered = true;
                            break;
                        }
                    }
                    if (!$answered) {
                        $isanswered = false; // Если найден хотя бы один неотвеченный пост
                        break; // Прерываем оба цикла
                    }
                } else {
                    $isanswered = false; // Если нет публичных комментариев на пост
                    break;
                }
            }
        }
    }

    // Получение количества строк в результате
    $num_rowssmen = mysqli_num_rows($resultsmen);
    // Инициализация массива для хранения записей
    $links = array();

    // Если есть записи, то добавляем их в массив
    if ($num_rowssmen > 0) {
        while ($rescity = mysqli_fetch_assoc($resultsmen)) {
            // Проверка на ошибки при получении данных
            if ($rescity === false) {
                die("Ошибка при получении данных: " . mysqli_error($connect));
            }
            // Добавление записи в массив
            $links[] = $rescity;
        }
    }

    // Освобождение памяти, занятой результатами запроса
    mysqli_free_result($resultsmen);

    // Закрытие соединения с базой данных
    mysqli_close($connect);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Закрытие смен</title>
    <link rel="stylesheet" href="/css/avstyles.css">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/bootstrap-theme.min.css">
    <link rel="stylesheet" type="text/css" href="/css/jquery.dataTables.css">
    <style>
        .btn-default, table {
            margin: 10px;
            border: medium;
        }
        .blinking {
        animation: blinkingText 1.6s infinite;
        }
        
        @keyframes blinkingText {
            0% { background: #fff; }
            40% { background: #e87b7b; }
            100% { background: #fff; }
        }
    </style>
</head>
<body>
    
<body>
<?php 
$navbarType = "user"; 
include 'topnavbar.php';
?>
    <h3 style="text-align: center">Закрытие смен</h3>
    <h5 style="text-align: center">На текущий момент существует потребность закрыть смены в следующих аптеках. Чтобы записаться на дату необходимо позвонить в департамент персонала 8 3822 440-812</h5>
    <table id="links" class="table table-bordered">
        <thead>
            <tr>
                <th style="padding: auto 0;">Аптека</th>
                <th style="padding: auto 0;">Город</th>
                <th style="padding: auto 0;">Начало смены</th>
                <th style="padding: auto 0;">Окончание смены</th>
                <th style="padding: auto 0;">Дата</th>
                <th style="padding: auto 0;"></th>
                <th style="padding: auto 0;">Дата размещения заявки</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($links as $one) 
            {
            	//print_r($one);
            	if($one['status']==1)
            	{
            		// SQL запрос на выборку всех записей из таблицы link
            		
            		//c1.namecity, p1.itemname, s1.status, s1.startsmen, s1.finishsmen, s1.vahta, s1.datapodachi 

                	echo "<tr>";
                	echo "<td>" . $one['itemname'] . "</a></td>";
                	echo "<td>" . $one['namecity'] . "</a></td>";
                	echo "<td>" . $one['startsmen'] . "</td>";
                	echo "<td>" . $one['finishsmen'] . "</td>";
                	echo "<td>" . $one['datasmen'] . "</td>";
                	if($one['vahta']==1)echo "<td>Вахта</td>";else echo "<td>-</td>";
                	echo "<td>" . $one['datapodachi'] . "</td>";
                	echo "</tr>";
				}
            }
            ?>
        </tbody>
    </table>
    <div class="idid"></div>

</body>
</html>
