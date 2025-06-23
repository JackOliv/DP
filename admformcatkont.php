<?php

require_once "bd_connect1.php";
require "allfunc.php";
checkAuth();
$connect = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connect, "UTF8");
$isEditing = isset($_GET['id']);
$kont = [
    "name_cat_kont" => "",
    "status_cat_kont" => "",
    "can_employees_add" => "",
    "city_cat_kont" => "",
];
if ($isEditing) {
    $cat_kont_id = intval($_GET['id']);
    
    $sql = "SELECT * FROM " . CATKONTAKTTABLE . " WHERE id_cat_kont = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $cat_kont_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $kont = $result->fetch_assoc();

    if (!$kont) {
        exit;
    }
}

$cities = [];
$sql_cities = "SELECT idcity, namecity FROM cities";
$result_cities = mysqli_query($connect, $sql_cities);
while ($row = mysqli_fetch_assoc($result_cities)) {
    $cities[] = $row;
}

$selectedCities = ($kont['city_cat_kont'] === "0") ? array_column($cities, 'idcity') : explode(',', $kont['city_cat_kont']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name_cat_kont = mysqli_real_escape_string($connect, $_POST['name_cat_kont']);
    $status_cat_kont = isset($_POST['status_cat_kont']) ? 1 : 0;
    $can_employees_add = isset($_POST['can_employees_add']) ? 1 : 0;
    $selected_cities = isset($_POST['city_cat_kont']) ? $_POST['city_cat_kont'] : [];
    
    if (count($selected_cities) === count($cities)) {
        $city_cat_kont = "0";
    } else {
        $city_cat_kont = implode(",", $selected_cities);
    }
    
    $last_editor = findip(1);
    $cat_kont_autor = $_SESSION['user_id'];
    
    if ($isEditing) {
        $sql_update = "UPDATE " . CATKONTAKTTABLE . " SET name_cat_kont = ?, status_cat_kont = ?, can_employees_add = ?, city_cat_kont = ?, cat_kont_lasteditor = ? WHERE id_cat_kont = ?";
        $stmt_update = $connect->prepare($sql_update);
        $stmt_update->bind_param("siissi", $name_cat_kont, $status_cat_kont, $can_employees_add, $city_cat_kont, $last_editor, $cat_kont_id);
        $stmt_update->execute();
        $stmt_update->close();
    } else {
        $sql_insert = "INSERT INTO " . CATKONTAKTTABLE . " (name_cat_kont, status_cat_kont, can_employees_add, city_cat_kont, cat_kont_autor, cat_kont_lasteditor) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = $connect->prepare($sql_insert);
        $stmt_insert->bind_param("siisss", $name_cat_kont, $status_cat_kont, $can_employees_add, $city_cat_kont, $cat_kont_autor, $last_editor);
        $stmt_insert->execute();
        $stmt_insert->close();
    }
    
    header("Location: admcatkont.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEditing ? "Редактировать" : "Добавить" ?> категорию</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
</head>
<body>
<?php
$navbarType = "admin";
include 'topnavbar.php';
?>
<div class="container">
    <h1><?= $isEditing ? "Редактировать" : "Добавить" ?> категорию</h1>
    <form action="<?php echo 'admformcatkont.php' . ($isEditing ? '?id=' . $cat_kont_id : ""); ?>" method="post">
        <div class="form-group">
            <label for="name_cat_kont">Название категории</label>
            <input type="text" class="form-control" id="name_cat_kont" name="name_cat_kont" value="<?= htmlspecialchars($kont['name_cat_kont'], ENT_QUOTES) ?>" required>
        </div>
        <div>
            <input type="checkbox" id="status_cat_kont" name="status_cat_kont" style="width: 20px;" value="1" <?= ($kont['status_cat_kont'] || !$isEditing )? 'checked' : '' ?>>
            <label for="status_cat_kont">Категория включена?</label>
        </div>
        <div>
            <input type="checkbox" id="can_employees_add" name="can_employees_add" style="width: 20px;" value="1" <?= $kont['can_employees_add'] ? 'checked' : '' ?>>
            <label for="can_employees_add">Могут ли сотрудники добавлять?</label>
        </div>
        <div>
            <label>Города:</label>
            <div>
                <input type="checkbox" id="all_cities" style="width: 20px;" <?= $kont['city_cat_kont'] === "0" ? 'checked' : '' ?>>
                <label for="all_cities">Все города</label>
            </div>
            <?php foreach ($cities as $city): ?>
                <div style="margin-left: 20px;">
                    <input type="checkbox" class="city-checkbox" name="city_cat_kont[]" value="<?= $city['idcity'] ?>" <?= in_array($city['idcity'], $selectedCities) ? 'checked' : '' ?>>
                    <label><?= htmlspecialchars($city['namecity'], ENT_QUOTES) ?></label>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="submit" class="btn btn-primary"><?= $isEditing ? "Сохранить изменения" : "Добавить контакт" ?></button>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const allCitiesCheckbox = document.getElementById('all_cities');
    const cityCheckboxes = document.querySelectorAll('.city-checkbox');

    function updateAllCitiesCheckbox() {
        allCitiesCheckbox.checked = [...cityCheckboxes].every(c => c.checked);
    }

    allCitiesCheckbox.addEventListener('change', function() {
        cityCheckboxes.forEach(city => city.checked = allCitiesCheckbox.checked);
    });

    cityCheckboxes.forEach(city => {
        city.addEventListener('change', updateAllCitiesCheckbox);
    });

    updateAllCitiesCheckbox();
});
</script>
</body>
</html>
