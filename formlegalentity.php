<?php
require_once "bd_connect1.php";
require "allfunc.php";
$connection = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connection, "utf8");
if (!$connection) {
    echo "Ошибка: Невозможно установить соединение с MySQL." . PHP_EOL;
    echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Текст ошибки error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}
if (isset($_GET['id'])) {
    $q = "SELECT `name` FROM " . LEGALENTITYTABLE . " WHERE `id` = {$_GET['id']}";
    $quer = mysqli_query($connection, $q);
    $res = mysqli_fetch_row($quer);
}

if(!isset($res))$res="";
if(isset($_POST['submit'])) {
    $date = date("Y-m-d");
   /* if ($_POST['downloadprice'] != "да"){
        $_POST['downloadprice'] = "";
    }*/
    if (isset($_GET['id'])){
        $query="UPDATE `" . LEGALENTITYTABLE . "` SET 
            `name`='{$_POST['name']}'
            WHERE `id` = {$_GET['id']}";
    } else {
        $query = "INSERT INTO `" . LEGALENTITYTABLE . "` (`name`) VALUES ('" . $_POST['name'] . "')";
        echo "<br>";
        echo $query;
        echo "<br>";
    }
    //echo "$query<BR>";
    if (!($stmt = $connection->prepare($query))) {
        echo "Не удалось подготовить запрос: (" . $connection->errno . ") " . $connection->error;
    }
    if (!$stmt->execute()) {
        echo "Не удалось выполнить запрос: (" . $stmt->errno . ") " . $stmt->error;
    }else{
        header("Location:/".LEGALENTITYPHP);
    }
    mysqli_close($connection);
}
?>
<!DOCTYPE html>
<html>
<head>
    
    <title>Добавление города</title>
    <link rel="stylesheet" href="/css/avstyles.css">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/bootstrap-theme.min.css">
    <style>
        form {
            margin-top: 10px;
            text-align: center;
        }
        div.col-sm-2, div.col-sm-3, form {
            text-align: left;
        }
        form, h3, #subm {
            margin-left: 10px;
        }
    </style>
</head>
<body>
<form>
	<a class="btn btn-info" href="admin.php">Админка</a>
	<a class="btn btn-info" href="cities.php">Города</a>
	<a class="btn btn-info" href="brand.php">Бренды</a>
	<a class="btn btn-info" href="firm.php">Юр. лица</a>
	<a class="btn btn-info" href="links.php">Ссылки</a>
    <a class="btn btn-info" href="index.php" target="_blank">Главная</a>
</form>
    <?php include "topmenu.php"; ?>
    <h3>Карточка Юр. лица</h3>
    <form class="form-horizontal" method="POST">
        <div class="form-group">
            <label for="name" class="col-sm-2">Название Юр. лица</label>
            <?php if(!isset($res[0]))$res[0]="";?>
            <div class="col-sm-3">
                <input type="text" id="name" name="name" class="form-control" value="<?php echo $res[0]; ?>">
            </div>
        </div>
        <div class="form-group">
            <div>
                <input class="btn btn-primary" type="submit" name="submit" value="Сохранить" id="subm">
            </div>
        </div>
    </form>
</body>
</html>