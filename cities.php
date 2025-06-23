<?php
    // Подключение файла с настройками подключения к базе данных
    require_once "bd_connect1.php";
    // Подключение файла с дополнительными функциями
    require "allfunc.php";
    checkAuth();
    // Установка соединения с базой данных
    $connect = mysqli_connect(HOST, USER, PW, DB);
    // Установка кодировки соединения
    mysqli_set_charset($connect, "UTF8");
    // SQL-запрос для выборки городов и связанных с ними районов и видимых городов
    $query = "SELECT c.*, GROUP_CONCAT(d.name SEPARATOR '<br/> ') AS district_names,
              (SELECT GROUP_CONCAT(vc.namecity SEPARATOR '<br/> ') 
               FROM cities vc WHERE FIND_IN_SET(vc.idcity, c.visiblecities)) AS visible_city_names
              FROM cities c
              LEFT JOIN district d ON c.idcity = d.cityid
              GROUP BY c.idcity";
    // Выполнение запроса и получение результата
    $result = mysqli_query($connect, $query);
    // Получение количества строк в результате запроса
    $num_rowscity = mysqli_num_rows($result);
    // Инициализация массива для хранения городов
    $cities = array();
    // Цикл для получения данных из результата запроса и добавления их в массив $cities
    while ($row = mysqli_fetch_assoc($result)) {
        $cities[] = $row;
    }
    // Если есть города, то дополнительно получаем данные из результата запроса и добавляем их в массив $cities
    if ($num_rowscity > 0) {
        while ($rescity = mysqli_fetch_assoc($result)) {
            if ($rescity === false) {
                die("Ошибка при получении данных: " . mysqli_error($connect));
            }
            $cities[] = $rescity;   
        }
    }
    // Освобождение памяти, занятой результатом запроса
    mysqli_free_result($result);
    // Проверка, была ли отправлена форма с параметром submit
    if (isset($_GET['submit'])) {
        // Проверка, был ли отправлен параметр modify
        if (isset($_GET['modify'])){
            // Получение идентификаторов городов для удаления
            $cities_for_delete = implode(",", $_GET['modify']);
            // Установка соединения с базой данных
            $connect = mysqli_connect(HOST, USER, PW, DB);
            mysqli_set_charset($connect, "utf8");
             // Проверка, была ли нажата кнопка "Удалить"
             if ($_GET['submit'] == 'Удалить') {
                // SQL-запрос для удаления районов, связанных с выбранными городами
                $delete_districts_query = "DELETE FROM district WHERE cityid IN ($cities_for_delete)";
                mysqli_query($connect, $delete_districts_query);
                // SQL-запрос для удаления выбранных городов
                $delete_cities_query = "DELETE FROM cities WHERE idcity IN ($cities_for_delete)";
                mysqli_query($connect, $delete_cities_query);
                // Перенаправление на страницу управления городами
                header("Location:/".CITIESPHP);
                exit;
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
    <title>Управление городами</title>
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
<form>
<h3 style="text-align: center">Управление городами</h3>
<form id="mod" method="GET" action="cities.php">
    <input type="submit" style="margin-left: 10px; margin-bottom: 10px;" class="btn btn-success add-btn" name="submit" value="Добавить город" formaction="formcity.php">
    <input type="submit" class="btn btn-sm btn-danger p-4" name="submit" value="Удалить" style="float: right; margin-right: 10px; margin-bottom: 10px;">
</form>
<table id="cities" class="table table-bordered table-condensed">
    <thead>
    <tr>
        <th style="width: 0;"></th>
        <th style="width: 0;" >Действие</th>
        <th style="width: 8%;" >ID Города</th>
        <th style="width: 20%;">Название Города</th>
        <th style="width: 0;">Регион</th>
        <th style="width: 8%;">Ip региона</th>
        <th style="width: 16%;">Видимые города</th>
        <th style="width: 8%;">Вкл. районы</th>
        <th>Районы</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($cities as $city) {
        echo "<tr>";
        echo '<td><input type="checkbox" name="modify[]" value="' . $city['idcity'] . '" form="mod"></td>';
        echo '<td><a href="formcity.php?idcity=' . $city["idcity"] . '" class="btn btn-warning btn-xs">Редактировать</td>';
        echo '<td>' . $city['idcity'] . '</td>';
        echo "<td>" . $city['namecity'] . "</td>";
        echo "<td>" . $city['regioncity'] . "</td>";
        echo "<td>" . $city['ipcity'] . "</td>";
        echo "<td>" . (empty($city['visible_city_names']) ? 'Нет видимых городов' : $city['visible_city_names']) . "</td>";
        if ($city['isenabled']==1)
				{
					$val = "<img src='images/da.jpg' width=20 height=20>";
				}
				else
				{
					$val = "<img src='images/net.jpg' width=20 height=20>";
				}
        echo "<td>$val</td>";	
        echo "<td>" . (empty($city['district_names']) ? 'Нет районов' : $city['district_names']) . "</td>";
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
        $('#cities').DataTable({
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
