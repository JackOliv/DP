<?php
// Подключение файла с константами для подключения к базе данных
require_once "bd_connect1.php";
// Подключение файла с функциями
require "allfunc.php";

// Подключение к базе данных
$connect = mysqli_connect(HOST, USER, PW, DB);
// Установка кодировки соединения
mysqli_set_charset($connect, "UTF8");

// Проверка на успешное подключение к базе данных
if (!$connect) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

// Получение ID поста из параметра URL
$post_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Проверка на корректность ID поста
if (!is_numeric($post_id) || $post_id <= 0) {
    die("Некорректный ID поста.");
}

// Запрос для получения видимости поста
$sql_list = "SELECT visibility FROM posts WHERE id = ?";
$stmt_list = $connect->prepare($sql_list);
if (!$stmt_list) {
    die("Ошибка подготовки запроса visibility: (" . $connect->errno . ") " . $connect->error);
}
$stmt_list->bind_param("i", $post_id);
$stmt_list->execute();
$result_list = $stmt_list->get_result();
$row = $result_list->fetch_assoc();
$visibility = $row['visibility'];

// Запрос для получения пользователей, просмотревших пост
$sql_viewers = "SELECT user_id FROM comments WHERE post_id = ? AND is_public = 1";
$stmt_viewers = $connect->prepare($sql_viewers);
if (!$stmt_viewers) {
    die("Ошибка подготовки запроса viewers: (" . $connect->errno . ") " . $connect->error);
}
$stmt_viewers->bind_param("i", $post_id);
$stmt_viewers->execute();
$result_viewers = $stmt_viewers->get_result();

// Преобразование результата запроса viewers в массив
$viewers = [];
while ($viewer = $result_viewers->fetch_assoc()) {
    $viewers[] = $viewer['user_id'];
}

// Закрытие соединения с базой данных
mysqli_close($connect);

// Получаем аптеки из таблицы partners
$connect = mysqli_connect(HOST, USER, PW, DB);
$sql_partners = "SELECT itemcode, itemname FROM partners where type = 1";
$result_partners = mysqli_query($connect, $sql_partners);

// Преобразование результата запроса partners в ассоциативный массив
$partners = [];
while ($partner = mysqli_fetch_assoc($result_partners)) {
    $partners[$partner['itemcode']] = $partner['itemname'];
}
mysqli_close($connect);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Кто ответил</title>
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
    <?php if ($visibility != 0 || $result_viewers->num_rows > 0): ?>
        <h3 style="text-align: center">Кто ответил</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th style="width:100px">Статус</th>
                    <th>Аптека</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($visibility != 0) {
                    // Разделяем строку visibility в массив
                    $selected_pharmacies = explode(',', $visibility);
                    foreach ($selected_pharmacies as $pharmacy) {
                        $pharmacy = trim($pharmacy);
                        $status = in_array($pharmacy, $viewers) ? 'Отвечено' : 'Не отвечено';
                        $itemname = isset($partners[$pharmacy]) ? $partners[$pharmacy] : $pharmacy;
                ?>
                        <tr>
                            <td><?php echo htmlspecialchars($status); ?></td>
                            <td><?php echo htmlspecialchars($itemname); ?></td>
                        </tr>
                <?php
                    }
                } else {
                    // Выводим все аптеки из таблицы partners
                    foreach ($partners as $itemcode => $itemname) {
                        echo "<tr>";
                        echo "<td>Не отвечено</td>";
                        echo "<td>" . htmlspecialchars($itemname) . "</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center">Нет данных для отображения.</p>
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
