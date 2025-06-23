<?php 
    require_once "bd_connect1.php";
    require "allfunc.php";
    $connect = mysqli_connect(HOST, USER, PW, DB);
    mysqli_set_charset($connect, "UTF8");
    $querycity = "SELECT * FROM `legalentity` WHERE 1";
    $resultcity = mysqli_query($connect, $querycity);

    if (!$resultcity) {
        die("Ошибка при выполнении запроса: " . mysqli_error($connect));
    }

    $num_rowscity = mysqli_num_rows($resultcity);
    $legalentity = array();

    if ($num_rowscity > 0) {
        while ($rescity = mysqli_fetch_assoc($resultcity)) {
            if ($rescity === false) {
                die("Ошибка при получении данных: " . mysqli_error($connect));
            }
            $legalentity[] = $rescity;
        }
    }

    mysqli_free_result($resultcity);

    if (isset($_GET['submit'])) {
        if (isset($_GET['modify'])){
			$legalentity_for_delete=implode(",", $_GET['modify']);
			$connect = mysqli_connect(HOST, USER, PW, DB);
			mysqli_set_charset($connect, "utf8");

        if ($_GET['submit'] == 'Удалить') {
            $select_positions = "SELECT `id` FROM `" . LEGALENTITYTABLE ."` WHERE `id` IN (" . $legalentity_for_delete . ");";
				$results = mysqli_query($connect, $select_positions);
				$for_delete = [];
				while ($result = mysqli_fetch_object($results)){
					array_push($for_delete,  $result->id);
				}
				$for_delete = implode(", ", $for_delete);
				$query = "DELETE FROM `" . LEGALENTITYTABLE . "` WHERE `id` in ($for_delete);";
				//echo "$query";
				if (!mysqli_query($connect, $query)){
					print_r(mysqli_error_list($connect));
				}
				
            $legalentity_for_delete = $_GET['modify'];
                $delquery = "DELETE FROM `" . LEGALENTITYTABLE . "` WHERE `id` IN ($legalentity_for_delete)";
				mysqli_query($connect, $delquery);
                header("Location:/".LEGALENTITYPHP);
            
        }
    }
}

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
<form>
  	<input type="submit" class="btn btn-info" name="submit  " value="Главная" formaction="admin.php">
    <input type="submit" class="btn btn-info" name="submit" value="Города" formaction="cities.php">
	<input type="submit" class="btn btn-info " name="submit" value="Бренды" formaction="brand.php">
    <input type="submit" class="btn btn-primary " name="submit" value="Юр лица" formaction="legalentity.php">
</form>
    <?php include "topmenu.php"; ?>
    <h3 style="text-align: center">Управление Юр лицами</h3>
    <form id="mod" method="GET" action="legalentity.php">
        <input type="submit" class="btn btn-default" name="submit" value="Добавить Юр лицо" formaction="formlegalentity.php">
        <input type="submit" class="btn btn-sm btn-danger p-4" name="submit" value="Удалить" style="float: right; margin-right: 10px">
    </form>
    <table id="legalentity" class="table table-bordered">
        <thead>
            <tr>
                <th></th>
                <th>ID Юр лица</th>
                <th>Название Юр лица</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($legalentity as $one) {
                echo "<tr>";
                echo '<td><input type="checkbox" name="modify[]" value="'.$one['id'].'" form="mod"></td>';
                echo '<td><a href="formlegalentity.php?id='.$one["id"].'">' . $one['id'] . '</td>';
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
            $('#legalentity').DataTable({
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