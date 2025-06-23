<?php
require "bd_connect1.php";

$cityId = $_POST['cityId'];
$districtCheck = isset($_POST['districtCheck']) ? $_POST['districtCheck'] : 0;

$link = mysqli_connect(HOST, USER, PW, DB);

// Используем подготовленное выражение для безопасности
$stmt = $link->prepare("SELECT * FROM `district` WHERE `cityid` = ?");
$stmt->bind_param("i", $cityId);
$stmt->execute();
$result = $stmt->get_result();    
$districts = '';
if($result && $result->num_rows > 0){
    
    $districts .= '<div style="text-align: center;" id="searching_district">';
    $districts .= '<div1 style="text-align: center; ">';
    $districts .= '<label style="margin-right: 10px">Район: </label>';
    $districts .= '</div1>';
    $districts .= '<div1 style="text-align: center;">';

    // Добавляем пункт "Все районы"
    $districts .= '<div style="text-align: center;" class="radio-inline">';
    $districts .= '<input type="radio" name="searching_district" id="Все районы" value="0"';
    if ($districtCheck == 0) {
        $districts .= ' checked';
    }
    $districts .= '><label style="cursor: pointer; font-weight: normal;" for="Все районы">Все районы</label>';
    $districts .= '</div>';
    
    while ($row = $result->fetch_assoc()) {
        $districts .= '<div style="text-align: center;" class="radio-inline">';
        $districts .= '<input type="radio" name="searching_district" id="' . $row['name'] . '" value="' . $row['id'] . '"';
        if ($districtCheck == $row['id']) {
            $districts .= ' checked';
        }
        $districts .= '><label style="cursor: pointer; font-weight: normal;" for="' . $row['name'] . '">' . $row['name'] . '</label>';
        $districts .= '</div>';
    }
    $districts .= '</div1>';
    $districts .= '</div>';
}else{
 $districts .= '<div style="text-align: center; height: 0" id="searching_district">';
 $districts .= '<input style=" visibility: collapse; type="radio" name="searching_district" value="0" checked>';
 $districts .= '</div>';
}

echo $districts;
?>