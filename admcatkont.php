<?php 
    require_once "bd_connect1.php";
    require "allfunc.php";
    checkAuth();
    $connect = mysqli_connect(HOST, USER, PW, DB);
    mysqli_set_charset($connect, "UTF8");
    $isadmin = 0;
    if(isset($_SESSION['login'])){
        if($_SESSION['login'] == 'admin'){
            $isadmin = 1;
        }
    }
    $querycity = "SELECT * FROM `" . CATKONTAKTTABLE . "`";
    $resultkont = mysqli_query($connect, $querycity);
    if (!$resultkont) {
        die("Ошибка запроса: " . mysqli_error($connect));
    }
    $catkonts = [];
    while ($rescity = mysqli_fetch_assoc($resultkont)) {
        $catkonts[] = $rescity;
    }
    mysqli_free_result($resultkont);
    // Получаем все категории и их флаг can_employees_add
    $query_cat_kont = "SELECT * FROM " . CATKONTAKTTABLE;
    $result_cat_kont = mysqli_query($connect, $query_cat_kont);
    if (!$result_cat_kont) {
        die("Ошибка запроса: " . mysqli_error($connect));
    }
    mysqli_free_result($result_cat_kont);
    if (isset($_GET['delete_id'])) {
        $delete_id = intval($_GET['delete_id']);
        $query = "DELETE FROM `" . CATKONTAKTTABLE . "` WHERE id_cat_kont = $delete_id";
        mysqli_query($connect, $query);
        header("Location: /admcatkont.php");
        exit;
    }
    mysqli_close($connect);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление Контактами</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <script src="/js/jquery-3.2.1.js"></script>
    <script>
        function deleteContact(id) {
            if (confirm("Вы уверены, что хотите удалить этот контакт?")) {
                window.location.href = "?delete_id=" + id;
            }
        }
    </script>
</head>
<body>
<?php 
$navbarType = "admin"; 
include 'topnavbar.php';
?>
    <h3 style="text-align: center">Управление Контактами</h3>
    <a href="admformcatkont.php" class="btn btn-success" style="margin-left: 10px; margin-bottom: 10px;">Добавить категорию</a>
    <a href="admkontakts.php" style="margin-left: 10px; margin-bottom: 10px;" class="btn btn-info add-btn">
       <i class="fas fa-plus" ></i> Управление контактами
    </a>
    <table id="catkonts" class="table table-bordered">
        <thead>
            <tr>
                <th></th>
                <th>Название категории</th>
                <th>Cтатус</th>
                <th>Могут сотрудники добавлять</th>
                <th>Города которые видят</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($catkonts as $one) { ?>
                <tr>
                    <td><a href="admformcatkont.php?id=<?= $one['id_cat_kont'] ?>" class="btn btn-warning btn-xs" >Редактировать</a></td>
                    <td width=15%><?= htmlspecialchars($one['name_cat_kont']) ?></td>
                    <td width=20%><?= htmlspecialchars($one['status_cat_kont']) ?></td>
                    <td width=20%><?= htmlspecialchars($one['can_employees_add']) ?></td>
                    <td width=20%><?= htmlspecialchars($one['city_cat_kont']) ?></td>
                    <td><button onclick="deleteContact(<?= $one['id_cat_kont'] ?>)" class="btn btn-danger btn-xs">Удалить</button></td>
                </tr>
                
            <?php }  ?>
        </tbody>
    </table>
    <div class="idid"></div>
    <script src="/js/jquery-3.2.1.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script type="text/javascript" charset="utf8" src="/js/jquery.dataTables.min.js"></script>
    <script>
        $(function(){
            $('#catkonts').DataTable({
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