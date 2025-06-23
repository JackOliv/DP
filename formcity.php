<?php
require_once "bd_connect1.php";
require "allfunc.php";
checkAuth();
$connection = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connection, "utf8");
// Проверяем, успешно ли установлено соединение
if (!$connection) {
    echo "Ошибка: Невозможно установить соединение с MySQL." . PHP_EOL;
    echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Текст ошибки error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}
$allCitiesQuery = "SELECT idcity, namecity, visiblecities FROM " . CITIESTABLE;
$allCitiesResult = mysqli_query($connection, $allCitiesQuery);

$districts = []; // Инициализируем массив районов пустым
$isNewCity = !isset($_GET['idcity']); // Проверяем, является ли город новым
$cityId = null; // Инициализируем переменную $cityId
// Если город не новый, то получаем информацию о нем из базы данных
if (!$isNewCity) {
    $cityId = $_GET['idcity'];
    $q = "SELECT `namecity`, `regioncity`, `ipcity`, `isenabled` FROM " . CITIESTABLE . " WHERE `idcity` = $cityId";
    $quer = mysqli_query($connection, $q);
    $res = mysqli_fetch_row($quer);
// Получаем список районов для города
    $districtQuery = "SELECT id, name, isenabled FROM district WHERE cityid = $cityId";
    $districtResult = mysqli_query($connection, $districtQuery);
    $districts = mysqli_fetch_all($districtResult, MYSQLI_ASSOC);
    $cityId = $_GET['idcity'];
    $q = "SELECT `visiblecities` FROM " . CITIESTABLE . " WHERE `idcity` = $cityId";
    $quer = mysqli_query($connection, $q);
    $res = mysqli_fetch_row($quer);
    $visibleCities = explode(',', $res[0]);
}

// Обработка POST-запроса для сохранения данных города
if (isset($_POST['submit']) && $_POST['submit'] == 'Сохранить город') {
    $namecity = mysqli_real_escape_string($connection, $_POST['namecity']);
    $regioncity = mysqli_real_escape_string($connection, $_POST['regioncity']);
    $ipcity = mysqli_real_escape_string($connection, $_POST['ipcity']);
    $isenabled = isset($_POST['isenabled']) && $_POST['isenabled'] == 'on' ? 1 : 0;

    if ($isNewCity) {
        // Добавление нового города
        $insertCityQuery = "INSERT INTO " . CITIESTABLE . " (`namecity`, `regioncity`, `ipcity`, `isenabled`) VALUES ('$namecity', '$regioncity', '$ipcity', $isenabled)";
        mysqli_query($connection, $insertCityQuery);
        $cityId = mysqli_insert_id($connection); // Получаем ID нового города
    } else {
        // Обновление существующего города
        $updateCityQuery = "UPDATE " . CITIESTABLE . " SET `namecity` = '$namecity', `regioncity` = '$regioncity', `ipcity` = '$ipcity', `isenabled` = $isenabled WHERE `idcity` = $cityId";
        mysqli_query($connection, $updateCityQuery);
        $cityId = mysqli_real_escape_string($connection, $_GET['idcity']);
        $visibleCities = implode(',', $_POST['visibleCities']); // Собираем ID выделенных городов через запятую
    
        $updateVisibleCitiesQuery = "UPDATE " . CITIESTABLE . " SET `visiblecities` = '$visibleCities' WHERE `idcity` = $cityId";
        mysqli_query($connection, $updateVisibleCitiesQuery);
    }
 // Перенаправляем пользователя на страницу городов
    header("Location:/".CITIESPHP);
    exit;
}
// Если параметр idcity передан в GET-запросе, то получаем информацию о городе и районах
if (isset($_GET['idcity'])) {
    $cityId = $_GET['idcity'];
    $q = "SELECT `namecity`, `regioncity`, `ipcity`, `isenabled` FROM " . CITIESTABLE . " WHERE `idcity` = $cityId";
    $quer = mysqli_query($connection, $q);
    $res = mysqli_fetch_row($quer);

    $districtQuery = "SELECT id, name, isenabled FROM district WHERE cityid = $cityId";
    $districtResult = mysqli_query($connection, $districtQuery);
    $districts = mysqli_fetch_all($districtResult, MYSQLI_ASSOC);
}
// Обработка POST-запроса для добавления нового района
if (isset($_POST['newDistrictName']) && !empty($_POST['newDistrictName'])) {
    $newDistrictName = mysqli_real_escape_string($connection, $_POST['newDistrictName']);
    $cityId = mysqli_real_escape_string($connection, $_GET['idcity']);
    $insertDistrictQuery = "INSERT INTO district (name, cityid, isenabled) VALUES ('$newDistrictName', $cityId,1)";
    mysqli_query($connection, $insertDistrictQuery);
// Перенаправляем пользователя на ту же страницу
    header("Location: {$_SERVER['REQUEST_URI']}");
    exit;
}
// Обновление имени района
if (isset($_POST['updateDistrict']) && !empty($_POST['districtId']) && !empty($_POST['DistrictName'])) {
    $districtId = mysqli_real_escape_string($connection, $_POST['districtId']);
    $DistrictName = mysqli_real_escape_string($connection, $_POST['DistrictName']);
    

    $updateDistrictQuery = "UPDATE district SET name = '$DistrictName' WHERE id = $districtId";
    mysqli_query($connection, $updateDistrictQuery);
    $districtId = mysqli_real_escape_string($connection, $_POST['districtId']);
    $isenabled = isset($_POST['isenabled']) && $_POST['isenabled'] == 'on' ? 1 : 0;

    $updateIsEnabledQuery = "UPDATE district SET isenabled = $isenabled WHERE id = $districtId";
    mysqli_query($connection, $updateIsEnabledQuery);
    // Перенаправляем пользователя на ту же страницу
    header("Location: {$_SERVER['REQUEST_URI']}");
    exit;
}  

// Обработка POST-запроса для удаления района
if (isset($_POST['deleteDistrict'])) {
    $districtId = mysqli_real_escape_string($connection, $_POST['deleteDistrict']);
    $deleteDistrictQuery = "DELETE FROM district WHERE id = $districtId";
    mysqli_query($connection, $deleteDistrictQuery);
// Перенаправляем пользователя на ту же страницу
    header("Location: {$_SERVER['REQUEST_URI']}");
    exit;
}
// Закрываем соединение с базой данных
mysqli_close($connection);
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
<body style="display: flex; flex-direction: column; ">

    <form class="form-horizontal" method="POST">
    <?php 
$navbarType = "admin"; 
include 'topnavbar.php';
?>
<form>
        <div class="form-group" style="margin: 10px">
            <div>
                <input class="btn btn-primary" type="submit" name ="submit" value="Сохранить город" id="subm">
            </div>
        </div>
        <h3>Карточка города</h3>
        <div class="form-group">
            <label for="namecity" class="col-sm-2">Название города</label>
            <?php if(!isset($res[0]))$res[0]="";?>
            <div class="col-sm-3">  
                <input type="text" id="namecity" name="namecity" class="form-control" value="<?php echo $res[0]; ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="regioncity" class="col-sm-2">Регион</label>
            <?php if(!isset($res[1]))$res[1]="";?>
            <div class="col-sm-3">
                <input type="text" id="regioncity" name="regioncity" class="form-control" value="<?php echo $res[1]; ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="ipcity" class="col-sm-2">IP-адрес</label>
            <?php if(!isset($res[2]))$res[2]="";?>
            <div class="col-sm-3">
                <input type="text" id="ipcity" name="ipcity" class="form-control" value="<?php echo $res[2]; ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="isenabled" class="col-sm-2">Использование районов</label>
            <?php if(!isset($res[3]))$res[3]=0;?>
            <div class="col-sm-3">
                <input type="checkbox" id="isenabled" name="isenabled" <?php echo $res[3] ? 'checked' : ''; ?> value="on">
            </div>
        </div>
        
        <?php if (!$isNewCity): ?>
        <form class="form-horizontal" method="POST">
            <h3>Управление видимостью городов</h3>
            <div class="form-group">
                <label for="visibleCities" class="col-sm-2">Видимые города</label>
                <div class="col-sm-3">
                    <?php while ($allCity = mysqli_fetch_assoc($allCitiesResult)): ?>
                        <?php if (isset($_GET['idcity']) && $allCity['idcity'] == $_GET['idcity']): continue; endif; ?>
                        <div class="form-group">
                            <input style="cursor:pointer;" type="checkbox" id="<?php echo $allCity['namecity']; ?>" name="visibleCities[]"    value="<?php echo $allCity['idcity']; ?>" <?php echo in_array($allCity['idcity'], $visibleCities) ? 'checked' : ''; ?>>
                            <label style="cursor:pointer;" for="<?php echo $allCity['namecity']; ?>"><?php echo $allCity['namecity']; ?></label>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </form>
        <script>
            var visibleCities = <?php echo json_encode($visibleCities); ?>;
            var checkboxes = document.querySelectorAll('input[name="visibleCities[]"]');
                            
            checkboxes.forEach(function(checkbox) {
                if (visibleCities.includes(parseInt(checkbox.value))) {
                    checkbox.checked = true;
                }
            });
        </script>
        <h3>Управление районами</h3>
        <div class="form-group">
                <label for="newDistrictName" class="col-sm-2">Добавить новый район</label>
                <div class="col-sm-3">
                    <input type="text" id="newDistrictName" name="newDistrictName" class="form-control">
                </div>
                <div class="col-sm-1">
                    <button type="submit" class="btn btn-primary add-district" name="addDistrict">Добавить</button>
                </div>
            </div>
        <script>
            document.querySelector('.add-district').addEventListener('click', function(event) {
            event.preventDefault(); // Предотвращаем отправку формы
            var newDistrictName = document.querySelector('#newDistrictName').value;
            var cityId = '<?php echo isset($_GET['idcity']) ? $_GET['idcity'] : ''; ?>';
            var isenabled = 1; // Начальное значение для isenabled
                    
            if (newDistrictName.trim() !== '') {
                var newInput = document.createElement('input');
                newInput.type = 'hidden';
                newInput.name = 'newDistrictName';
                newInput.value = newDistrictName;
            
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                form.style.display = 'none';
                form.appendChild(newInput);
            
                document.body.appendChild(form);
                form.submit();
            
                document.querySelector('#newDistrictName').value = ''; // Очищаем поле ввода
            }
        });
        </script>
        <?php endif; ?>
    </form>
    <?php if (!$isNewCity): $index = 0; ?>
        <div class="form-group">
            <label for="districts" class="col-sm-2">Районы</label>
            <div class="col-sm-3">
                <?php foreach ($districts as $district): ?>
                    <div class="form-group">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="districtId" value="<?php echo $district['id']; ?>">
                            <input type="text" name="DistrictName" value="<?php echo $district['name']; ?>">
                            <input type="checkbox" name="isenabled" <?php echo $district['isenabled'] ? 'checked' : ''; ?> value="on">
                            <input type="submit" class="btn btn-primary btn-xs" name="updateDistrict" value="Обновить">
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="deleteDistrict" value="<?php echo $district['id']; ?>">
                            <input type="submit" class="btn btn-danger btn-xs" value="Удалить">
                        </form>
                    </div>
                    <?php $index++; // Увеличиваем индекс после каждой итерации ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>