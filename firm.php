<?php
    // Подключение файла с константами для подключения к базе данных
    require_once "bd_connect1.php";
    // Подключение файла с функциями
    require "allfunc.php";
    checkAuth();
    // Подключение к базе данных
    $connect = mysqli_connect(HOST, USER, PW, DB);
    // Установка кодировки соединения
    mysqli_set_charset($connect, "UTF8");

    // SQL запрос на выборку всех записей из таблицы firm
    $querycity = "SELECT * FROM `firm` WHERE 1";
    // Выполнение запроса
    $resultcity = mysqli_query($connect, $querycity);

    // Проверка на ошибки при выполнении запроса
    if (!$resultcity) {
        die("Ошибка при выполнении запроса: " . mysqli_error($connect));
    }

    // Получение количества строк в результате
    $num_rowscity = mysqli_num_rows($resultcity);
    // Инициализация массива для хранения записей
    $firm = array();

    // Если есть записи, то добавляем их в массив
    if ($num_rowscity > 0) {
        while ($rescity = mysqli_fetch_assoc($resultcity)) {
            // Проверка на ошибки при получении данных
            if ($rescity === false) {
                die("Ошибка при получении данных: " . mysqli_error($connect));
            }
            // Добавление записи в массив
            $firm[] = $rescity;
        }
    }

    // Освобождение памяти, занятой результатами запроса
    mysqli_free_result($resultcity);

    // Проверка, была ли отправлена форма с параметром submit
    if (isset($_GET['submit'])) {
        // Проверка, был ли отправлен параметр modify
        if (isset($_GET['modify'])){
            // Получение идентификаторов для удаления
            $firm_for_delete = implode(",", $_GET['modify']);
            // Подключение к базе данных
            $connect = mysqli_connect(HOST, USER, PW, DB);
            // Установка кодировки соединения
            mysqli_set_charset($connect, "utf8");

            // Проверка, была ли нажата кнопка "Удалить"
            if ($_GET['submit'] == 'Удалить') {
                // SQL запрос на выборку идентификаторов для удаления
                $select_positions = "SELECT `id` FROM `" . FIRMTABLE ."` WHERE `id` IN (" . $firm_for_delete . ");";
                $results = mysqli_query($connect, $select_positions);
                $for_delete = [];
                // Получение идентификаторов для удаления
                while ($result = mysqli_fetch_object($results)){
                    array_push($for_delete,  $result->id);
                }
                $for_delete = implode(", ", $for_delete);
                // SQL запрос на удаление записей
                $query = "DELETE FROM `" . FIRMTABLE . "` WHERE `id` in ($for_delete);";
                // Выполнение запроса на удаление
                if (!mysqli_query($connect, $query)){
                    print_r(mysqli_error_list($connect));
                }
                // Перенаправление на предыдущую страницу
                header("Location:/".FIRMPHP);
            }
        }
    }

    // Закрытие соединения с базой данных
    mysqli_close($connect);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление Юр лицами</title>
    <link rel="stylesheet" href="/css/avstyles.css">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/bootstrap-theme.min.css">
    <link rel="stylesheet" type="text/css" href="/css/jquery.dataTables.css">
    <style>
        .btn-default, table {
            margin: 10px;
            border: medium;
        }
    </style>
</head>
<body>
<?php 
$navbarType = "admin"; 
include 'topnavbar.php';
?>
    <h3 style="text-align: center">Управление Юр лицами</h3>
    <form id="mod" method="GET" action="firm.php">
        <input type="submit" style="margin-left: 10px; margin-bottom: 10px;" class="btn btn-success add-btn" name="submit" value="Добавить Юр лицо" formaction="formfirm.php">
        <input type="submit" class="btn btn-sm btn-danger p-4" name="submit" value="Удалить" style="float: right; margin-right: 10px; margin-bottom: 10px;">
    </form>
    <table id="firm" class="table table-bordered">
        <thead>
            <tr>
                <th style="padding: auto 0; width: 0px;"></th>
                <th style="padding: auto 0; width: 0px;">Действие</th>
                <th style="padding: auto 0; width: 10%;">ID Юр лица</th>
                <th style="padding: auto 0">Название Юр лица</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($firm as $one) {
                echo "<tr>";
                echo '<td><input type="checkbox" name="modify[]" value="'.$one['id'].'" form="mod"></td>';
                echo '<td><a href="formfirm.php?id='.$one["id"].'" class="btn btn-warning btn-xs">Редактировать</td>';
                echo '<td>' . $one['id'] . '</td>';
                echo "<td>" . $one['name'] . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
    <div class="idid"></div>
    <script src="/js/jquery-3.2.1.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script type="text/javascript" charset="utf8" src="/js/jquery.dataTables.min.js"></script>
    <script>
        $(function(){
            $('#firm').DataTable({
                "searching": false,
                "stateSave": true,
                "language":{
                    info:"Показано с _START_ по _END_ из _TOTAL_ найденных",
                    "paginate": {
                        "first":      "Первая",
                        "last":       "Последняя",
                        "next":       "  Следующая",
                        "previous":   "Предыдущая  "
                    },
                    "infoEmpty":      "Записей не найдено",
                    "emptyTable":     "Записей не найдено",
                    "lengthMenu":     "Показывать по _MENU_ "
                }
            });
        });
    </script>
</body>
</html>