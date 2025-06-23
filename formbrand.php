<?php
// Подключение к базе данных
require_once "bd_connect1.php";
require "allfunc.php";
checkAuth();
// Создание соединения с базой данных
$connection = mysqli_connect(HOST, USER, PW, DB);

// Установка кодировки соединения
mysqli_set_charset($connection, "utf8");

// Проверка на ошибки при соединении с базой данных
if (!$connection) {
    echo "Ошибка: Невозможно установить соединение с MySQL." . PHP_EOL;
    echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Текст ошибки error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}

// Если параметр id передан в GET запросе, выполняем запрос к базе данных
if (isset($_GET['id'])) {
    $q = "SELECT `name` FROM " . BRANDTABLE . " WHERE `id` = {$_GET['id']}";
    $quer = mysqli_query($connection, $q);
    $res = mysqli_fetch_row($quer);
}

// Если результат запроса не был получен, инициализируем переменную $res
if(!isset($res)) $res = "";

// Если форма отправлена методом POST
if(isset($_POST['submit'])) {
    // Получение текущей даты
    $date = date("Y-m-d");

    // Если параметр id передан в GET запросе, формируем запрос на обновление записи
    if (isset($_GET['id'])){
        $query = "UPDATE `" . BRANDTABLE . "` SET 
            `name`='{$_POST['name']}'
            WHERE `id` = {$_GET['id']}";
    } else {
        // Если параметр id не передан, формируем запрос на добавление новой записи
        $query = "INSERT INTO `" . BRANDTABLE . "` (`name`) VALUES ('" . $_POST['name'] . "')";
        echo "<br>";
        echo $query;
        echo "<br>";
    }

    // Подготовка и выполнение запроса
    if (!($stmt = $connection->prepare($query))) {
        echo "Не удалось подготовить запрос: (" . $connection->errno . ") " . $connection->error;
    }
    if (!$stmt->execute()) {
        echo "Не удалось выполнить запрос: (" . $stmt->errno . ") " . $stmt->error;
    } else {
        // Перенаправление на страницу со списком брендов
        header("Location:/".BRANDPHP);
    }

    // Закрытие соединения с базой данных
    mysqli_close($connection);
}
?>
<!DOCTYPE html>
<html>
<head>
    
    <title>Добавление бренда</title>
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
<?php 
$navbarType = "admin"; 
include 'topnavbar.php';
?>
    <h3>Карточка бренда</h3>
    <form class="form-horizontal" method="POST">
        <div class="form-group">
            <label for="name" class="col-sm-2">Название бренда</label>
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