<?php
// Подключение файла с константами для подключения к базе данных
require_once "bd_connect1.php";
// Подключение файла с функциями
require "allfunc.php";

// Подключение к базе данных
$connect = mysqli_connect(HOST, USER, PW, DB);
// Установка кодировки соединения
mysqli_set_charset($connect, "UTF8");

// Получение ID поста из параметра URL
$post_id = isset($_GET['id']) ? $_GET['id'] : 0;


// Запрос для получения пользователей, просмотревших пост
$sql_viewers = "SELECT user, dateview, actions FROM logs WHERE postid = ?";
$stmt_viewers = $connect->prepare($sql_viewers);

if (!$stmt_viewers) {
    die("Ошибка подготовки запроса: (" . $connect->errno . ") " . $connect->error);
}

$stmt_viewers->bind_param("i", $post_id);
$stmt_viewers->execute();
$result_viewers = $stmt_viewers->get_result();



// Закрытие соединения с базой данных
mysqli_close($connect);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лог записи</title>
    <link rel="stylesheet" href="/css/avstyles.css">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/bootstrap-theme.min.css">
    <link rel="stylesheet" type="text/css" href="/css/jquery.dataTables.css">
    
</head>
<body>
    
<body>
<?php 
$navbarType = "user"; 
include 'topnavbar.php';
?>
    <?php if ($result_viewers): ?>
        <h3 style="text-align: center">Лог записи</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Действие</th>
                    <th>Дата просмотра</th>
                    <th>Ip</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_viewers->fetch_assoc()): 
                    $who = finduserfromip($row['user'])?>
                    <tr>
                        <td><?php echo htmlspecialchars($who); ?></td>
                        <td><?php echo htmlspecialchars($row['actions']); ?></td>
                        <td><?php echo htmlspecialchars($row['dateview']); ?></td>
                        <td><?php echo htmlspecialchars($row['user']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="idid"></div>
    <script src="/js/jquery-3.2.1.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script type="text/javascript" charset="utf8" src="/js/jquery.dataTables.min.js"></script>
    <script>
        $(function(){
            $('.table').DataTable({
                "searching": false,
                "stateSave": true,
                "language": {
                    "info": "Показано с _START_ по _END_ из _TOTAL_ найденных",
                    "paginate": {
                        "first": "Первая",
                        "last": "Последняя",
                        "next": "Следующая",
                        "previous": "Предыдущая"
                    },
                    "infoEmpty": "Записей не найдено",
                    "emptyTable": "Записей не найдено",
                    "lengthMenu": "Показывать по _MENU_"
                }
            });
        });
    </script>
</body>
</html>
