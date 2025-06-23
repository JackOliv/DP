<?php
// Подключение к базе данных
require_once "bd_connect1.php";
require "allfunc.php";
checkAuth();

// Создание соединения
$connection = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connection, "utf8");

if (!$connection) {
    echo "Ошибка: Невозможно установить соединение с MySQL." . PHP_EOL;
    echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Текст ошибки error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}

// Получаем все типы ссылок
$queryTypes = "SELECT * FROM `linktypes`";
$resultTypes = mysqli_query($connection, $queryTypes);

$linkTypes = [];
while ($row = mysqli_fetch_assoc($resultTypes)) {
    $linkTypes[$row['id']] = $row['name'];
}
mysqli_free_result($resultTypes);

// Если редактируем ссылку, получаем данные о ней
$res = ["", "", "", "", ""]; // Массив по умолчанию
$linktype_id = "";

if (isset($_GET['id'])) {
    $q = "SELECT `name`,`url`,`description`, `can_employees_add`, `linktype_id` FROM `" . LINKSTABLE . "` WHERE `id` = {$_GET['id']}";
    $quer = mysqli_query($connection, $q);
    if ($quer) {
        $res = mysqli_fetch_row($quer);
        $linktype_id = $res[4] ?? ""; // ID типа ссылки
    }
}

if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($connection, $_POST['name']);
    $url = mysqli_real_escape_string($connection, $_POST['url']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $linktype_id = intval($_POST['linktype_id']);
    $canemp = isset($_POST['can_employees_add']) ? 1 : 0;

    if (isset($_GET['id'])) {
        $query = "UPDATE `" . LINKSTABLE . "` 
                  SET name='$name', url='$url', description='$description', linktype_id=$linktype_id, can_employees_add='$canemp'
                  WHERE id={$_GET['id']}";
    } else {
        $query = "INSERT INTO `" . LINKSTABLE . "` (name, url, description, linktype_id, can_employees_add) 
                  VALUES ('$name', '$url', '$description', $linktype_id, $canemp)";
    }

    if (!mysqli_query($connection, $query)) {
        echo "Ошибка при сохранении: " . mysqli_error($connection);
    } else {
        header("Location: /" . LINKSPHP);
        exit;
    }
}

mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <title>Добавление ссылки</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 900px;
            padding: 20px;
            margin-top: 30px;
        }
        h3 {
            text-align: center;
            margin-bottom: 20px;
            color: #343a40;
        }
        .form-group label {
            font-weight: bold;
        }
        #subm {
            display: block;
            width: 100%;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php 
    $navbarType = "admin"; 
    include 'topnavbar.php';
    ?>
    
    <div class="container">
        <h3>Карточка ссылки</h3>
        <form class="form-horizontal" method="POST">
            <div class="form-group">
                <label for="name">Название ссылки</label>
                <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($res[0]) ?>">
            </div>
            <div class="form-group">
                <label for="url">Ссылка</label>
                <input type="text" id="url" name="url" class="form-control" value="<?= htmlspecialchars($res[1]) ?>">
            </div>
            <div class="form-group">
                <label for="description">Описание</label>
                <input type="text" id="description" name="description" class="form-control" value="<?= htmlspecialchars($res[2]) ?>">
            </div>
            <div class="form-group">
                <label for="linktype_id">Тип ссылки</label>
                <select class="form-control" name="linktype_id" id="linktype_id" required>
                    <option value="" disabled <?= empty($linktype_id) ? 'selected' : '' ?>>Выберите тип</option>
                    <?php foreach ($linkTypes as $id => $name): ?>
                        <option value="<?= $id ?>" <?= ($id == $linktype_id) ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" id="can_employees_add" name="can_employees_add" <?= $res[3] ? 'checked' : '' ?>>
                <label class="form-check-label" for="can_employees_add">Сотрудники могут сохранять пароли?</label>
            </div>
            <button class="btn btn-primary" type="submit" name="submit" id="subm">Сохранить</button>
        </form>
    </div>
</body>
</html>
