<?php
// Подключение файла с константами для подключения к базе данных
require_once "bd_connect1.php";
// Подключение файла с функциями
require "allfunc.php";

// Подключение к базе данных
$connection = mysqli_connect(HOST, USER, PW, DB);
// Установка кодировки соединения
mysqli_set_charset($connection, "utf8");
// Получение city_id из GET-параметра
$cityId = $_GET['cityid'];
$query = "SELECT * FROM district WHERE cityid = $cityId";
$result = mysqli_query($connection, $query);

if (isset($_GET['itemcode'])){
    $itemCode = $_GET['itemcode'];
    $query2 = "SELECT districtid FROM partners WHERE itemcode = $itemCode";
    $result2 = mysqli_query($connection, $query2);
    if ($result2 && mysqli_num_rows($result2) > 0) {
        $row = mysqli_fetch_assoc($result2);
        $selectedDistrictId = $row['districtid'];
    } else {
        $selectedDistrictId = null;
    }
} else{
    $selectedDistrictId = null;
}
// SQL-запрос для получения районов по cityid

// Генерация HTML для списка районов
$districtsHtml = '<option value="0">Выберите район</option>';
while ($district = mysqli_fetch_assoc($result)) {
    $selected = ($selectedDistrictId == $district['id']) ? 'selected' : '';
    $districtsHtml .= '<option value="' . $district['id'] . '" ' . $selected . '>' . $district['name'] . '</option>';
}

// Возвращение HTML списка районов
echo $districtsHtml;
?>