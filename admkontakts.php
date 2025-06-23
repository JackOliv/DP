<?php
    require_once "bd_connect1.php";
    require "allfunc.php";
    checkAuth();
    $connect = mysqli_connect(HOST, USER, PW, DB);
    mysqli_set_charset($connect, "UTF8");
    $isadmin = 0;
    $isReturn = isset($_GET['ret']);
    if(isset($_SESSION['login'])){
        if($_SESSION['login'] == 'admin'){
            $isadmin = 1;
        }
    }
    if($isReturn)
    {
        $querycity = "SELECT * FROM `" . KONTAKTTABLE . "` WHERE see_kont=1 ORDER BY cat_kont, urgent_kont DESC";
    } else
    {
        $querycity = "SELECT * FROM `" . KONTAKTTABLE . "` WHERE see_kont=0 ORDER BY cat_kont, urgent_kont DESC ";
    }
    $resultkont = mysqli_query($connect, $querycity);
    if (!$resultkont) {
        die("Ошибка запроса: " . mysqli_error($connect));
    }
    $konts = [];
    while ($rescity = mysqli_fetch_assoc($resultkont)) {
        $konts[] = $rescity;
    }
    mysqli_free_result($resultkont);
    // Получаем все категории и их флаг can_employees_add
    $query_cat_kont = "SELECT * FROM " . CATKONTAKTTABLE;
    $result_cat_kont = mysqli_query($connect, $query_cat_kont);
    if (!$result_cat_kont) {
        die("Ошибка запроса: " . mysqli_error($connect));
    }
    $_cat_kont = [];
    while ($res_cat_kont = mysqli_fetch_assoc($result_cat_kont)) {
        $_cat_kont[$res_cat_kont["id_cat_kont"]] = [
            "name" => $res_cat_kont["name_cat_kont"],
            "can_add" => (bool) $res_cat_kont["can_employees_add"]
        ];
    }
    mysqli_free_result($result_cat_kont);
    if (isset($_GET['delete_id'])) {
        $delete_id = intval($_GET['delete_id']);
        $query = "UPDATE `" . KONTAKTTABLE . "` SET see_kont = 1 WHERE id_kont = $delete_id";
        mysqli_query($connect, $query);
        header("Location: /admkontakts.php");
        exit;
    }
    if (isset($_GET['return_id'])) {
        $return_id = intval($_GET['return_id']);
        $query = "UPDATE `" . KONTAKTTABLE . "` SET see_kont = 0 WHERE id_kont = $return_id";
        mysqli_query($connect, $query);
        header("Location: /admkontakts.php?ret=1");
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
        function returnContact(id) {
            if (confirm("Вы уверены, что хотите вернуть этот контакт?")) {
                window.location.href = "?return_id=" + id;
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
    <?php 
        if($isReturn){
            echo '<a href="admkontakts.php" class="btn btn-success" style="margin-left: 10px; margin-bottom: 10px;">Просмотреть контакты</a>';
        }
        else{
            echo '<a href="admkontakts.php?ret=1" class="btn btn-success" style="margin-left: 10px; margin-bottom: 10px;">Вернуть контакт</a>';
        }
    ?>
    <a href="admformkontakts.php" class="btn btn-success" style="margin-left: 10px; margin-bottom: 10px;">Добавить контакт</a>
    <a href="admcatkont.php" style="margin-left: 10px; margin-bottom: 10px;" class="btn btn-info add-btn">
       <i class="fas fa-plus" ></i> Управление категориями
    </a>
    <table id="konts" class="table table-bordered">
        <thead>
            <tr>
                <th></th>
                <th>Контакт</th>
                <th>Контактное лицо</th>
                <th>Почта</th>
                <th>Описание</th>
                <th>Категория</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php $cc = 0; foreach ($konts as $one) { ?>
                <tr>
                    <?php if (!$isReturn) { ?>
                        <td><a href="admformkontakts.php?cat_kont=<?= $one['cat_kont'] ?>&id=<?= $one['id_kont'] ?>" class="btn btn-warning btn-xs" >Редактировать</a></td>
                    <?php } else { ?>
                        <td><button onclick="returnContact(<?= $one['id_kont'] ?>)" class="btn btn-danger btn-xs">Вернуть</button></td>
                    <?php } ?>
                    <td width=15%><?= htmlspecialchars($one['text_kont']) ?></td>
                    <td width=20%><?= htmlspecialchars($one['name_kont']) ?></td>
                    <td width=20%><?= htmlspecialchars($one['email_kont']) ?></td>
                    <td width=20%><?= htmlspecialchars($one['opis_kont']) ?></td>
                    <td width=15%><?= htmlspecialchars($_cat_kont[$one['cat_kont']]['name']) ?></td>
                    <?php if (!$isReturn) { ?>
                        <td><button onclick="deleteContact(<?= $one['id_kont'] ?>)" class="btn btn-danger btn-xs">Удалить</button></td>
                    <?php }?>
                </tr>
                <?php $cc = $one['cat_kont']; ?>
            <?php }  ?>
        </tbody>
    </table>
    <div class="idid"></div>
    <script src="/js/jquery-3.2.1.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script type="text/javascript" charset="utf8" src="/js/jquery.dataTables.min.js"></script>
    <script>
        $(function(){
            $('#konts').DataTable({
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