<?php
// Подключение файла с константами для подключения к базе данных
require_once "bd_connect1.php";
// Подключение файла с функциями
require "allfunc.php";
checkAuth();
// Подключение к базе данных
$connection = mysqli_connect(HOST, USER, PW, DB);
// Установка кодировки соединения
mysqli_set_charset($connection, "utf8");

// Проверка на ошибки при подключении к базе данных
if (!$connection) {
    echo "Ошибка: Невозможно установить соединение с MySQL." . PHP_EOL;
    echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Текст ошибки error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}

// Получение данных из таблицы brand
$queryBrands = "SELECT * FROM brand";
$resultBrands = mysqli_query($connection, $queryBrands);

// Получение данных из таблицы firm
$queryLegalEntities = "SELECT * FROM firm";
$resultLegalEntities = mysqli_query($connection, $queryLegalEntities);

// Проверка, был ли передан параметр itemcode в URL
if (isset($_GET['itemcode'])) {
    // SQL запрос на выборку данных по itemcode
    $q = "SELECT `type`, `brand`, `itemcode`, `itemname`, `downloadprice`, `tominfarm`, `lekvapteke`, `s09`, `status`, `num_term`, `city`, `phone`, `worktime`, `firm`, `uchet`, `net`,`districtid` FROM " . PARTNERSTABLE . " WHERE `itemcode`={$_GET['itemcode']}";
    $quer = mysqli_query($connection, $q);
    $res = mysqli_fetch_row($quer);
}
// Если переменная $res не была установлена, устанавливаем её в пустую строку
if (!isset($res)) {
    $res = "";
}

// Проверка, была ли отправлена форма с параметром submit
if (isset($_POST['submit']) && !isset($_GET['itemcode'])) {
    // Получение текущей даты   
    $date = date("Y-m-d");

    // Подготовка запроса на вставку данных
    $query = "INSERT INTO `" . PARTNERSTABLE . "` (`type`, `brand`, `itemcode`, `itemname`, `uploaddate`, `downloadprice`, `tominfarm`, `lekvapteke`, `s09`, `num_term`, `city`, `phone`, `worktime`, `firm`, `uchet`, `net`, `districtid`) VALUES (?, ?, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($query);
    
    // Привязка параметров к подготовленному запросу
    $stmt->bind_param("ssssiiissssssssi", $_POST['type'], $_POST['brand'], $_POST['itemcode'], $_POST['itemname'], $_POST['downloadprice'], $_POST['tominfarm'], $_POST['lekvapteke'], $_POST['s09'], $_POST['num_term'], $_POST['city'], $_POST['phone'], $_POST['worktime'], $_POST['firm'], $_POST['uchet'], $_POST['net'], $_POST['district']);
    // Выполнение подготовленного запроса
    if (!$stmt->execute()) {
        echo "Не удалось выполнить запрос: (" . $stmt->errno . ") " . $stmt->error;
    } else {
        // Перенаправление на страницу администратора
        header("Location:/" . ADMINPHP);
    }

    // Закрытие соединения с базой данных
    mysqli_close($connection);
} elseif (isset($_POST['submit']) && isset($_GET['itemcode'])){
    // Подготовка запроса на обновление данных
    $query = "UPDATE `" . PARTNERSTABLE . "` SET `type` = ?, `brand` = ?, `itemcode` = ?, `itemname` = ?, `downloadprice` = ?, `tominfarm` = ?, `lekvapteke` = ?, `s09` = ?, `num_term` = ?, `city` = ?, `phone` = ?, `worktime` = ?, `firm` = ?, `uchet` = ?, `net` = ?, `districtid` = ? WHERE `itemcode` = ?";
    $stmt = $connection->prepare($query);
    
    // Привязка параметров к подготовленному запросу
    $stmt->bind_param("ssssiiissssssssis", $_POST['type'], $_POST['brand'], $_POST['itemcode'], $_POST['itemname'], $_POST['downloadprice'], $_POST['tominfarm'], $_POST['lekvapteke'], $_POST['s09'], $_POST['num_term'], $_POST['city'], $_POST['phone'], $_POST['worktime'], $_POST['firm'], $_POST['uchet'], $_POST['net'], $_POST['district'], $_POST['itemcode']);
    // Выполнение подготовленного запроса
    if (!$stmt->execute()) {
        echo "Не удалось выполнить запрос: (" . $stmt->errno . ") " . $stmt->error;
    } else {
        // Перенаправление на страницу администратора
        header("Location:/" . ADMINPHP);
    }

    mysqli_close($connection);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Добавление аптеки (поставщика)</title>
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
<h3>Карточка аптеки (поставщика)</h3>
<form class="form-horizontal" method="POST">
    <div class="form-group">
        <label for="type" class="col-sm-2">Тип</label>
        <div class="col-sm-3">
            <?php if (!isset($res[0])) $res[0] = "1"; ?>
            <select class="form-control" id="type" name="type">
                <option <?php if ($res[0] == "1") echo "selected"; ?> value="1">Аптека</option>
                <option <?php if ($res[0] == "0") echo "selected"; ?> value="0">Поставщик</option>
                <option <?php if ($res[0] == "2") echo "selected"; ?> value="2">Склад</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="brand" class="col-sm-2">Бренд</label>
        <div class="col-sm-3">
            <select class="form-control" id="brand" name="brand">
                <option value="">Выберите бренд</option>
                <?php
                while ($brand = mysqli_fetch_assoc($resultBrands)) {
                    echo '<option value="' . $brand['id'] . '" ' . (isset($res[1]) && $res[1] == $brand['id'] ? 'selected' : '') . '>' . $brand['name'] . '</option>';
                }
                ?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="firm" class="col-sm-2">Юрлицо</label>
        <div class="col-sm-3">
            <select class="form-control" id="firm" name="firm">
                <option value="">Выберите юрлицо</option>
                <?php
                while ($firm = mysqli_fetch_assoc($resultLegalEntities)) {
                    echo '<option value="' . $firm['id'] . '" ' . (isset($res[13]) && $res[13] == $firm['id'] ? 'selected' : '') . '>' . $firm['name'] . '</option>';
                }
                ?>
            </select>
        </div>
    </div>
             <div class="form-group">
                <label for="uchet" class="col-sm-2">Учетная система</label>
                <div class="col-sm-3">
                <?php if(!isset($res[14]))$res[14]="fk";?>
                    <select class="form-control" id="uchet" name="uchet">
                   		<option <?php if($res[14]==""){echo "selected";}?> value = "">Отсутствует</option>
                        <option <?php if($res[14]=="fk"){echo "selected";}?> value = "fk">1С Фарм Капитан</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
            <?php if(!isset($res[2]))$res[2]="";?>
                <label for="itemcode" class="col-sm-2">Код</label>
                <div class="col-sm-3">
                    <input type="text" id="itemcode" name="itemcode" class="form-control" value="<?php echo $res[2];?>">
                </div>
            </div>
             
             <div class="form-group">
                
                <label for="num_term" class="col-sm-2">Колво касс в аптеке</label>
                <?php if(!isset($res[9]))$res[9]="";?>
                <div class="col-sm-3">
                    <input type="text" id="num_term" name="num_term" class="form-control" value="<?php echo $res[9];?>">
                </div>
            </div>
            <div class="form-group">
                <label for="city" class="col-sm-2">Город</label>
                <div class="col-sm-3">
                <?php if(!isset($res[10])){$res[10]="0";}$res[10]=$res[10]+0;?>
                <select class="form-control" id="city" name="city">
                <option <?php if($res[10]==""){echo "selected";}?> value = "-1">Невыбрано</option> 
                <option <?php if($res[10]=="0"){echo "selected";}?> value = "0">Отсутствует</option> 
                	<?php 
                    
                	$querycity = "SELECT * FROM `cities` WHERE 1 order by idcity";
        			$resultcity = mysqli_query($connection, $querycity);
      				$num_rowscity=mysqli_num_rows($resultcity);
        			if ($num_rowscity> 0) 
					{
						for($i=0;$i<$num_rowscity;$i++)
						{
							$rescity = mysqli_fetch_array($resultcity);
							echo "<option ";
							if($res[10]==$rescity['idcity']){echo "selected ";}
							echo "value = ".$rescity['idcity'].">".$rescity['namecity']."</option>";
						}
					}
					?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="district" class="col-sm-2">Район</label>
                <div class="col-sm-3">
                    <select class="form-control" id="district" name="district">
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            var citySelect = document.getElementById('city');
                            var itemCode = document.getElementById('itemcode');
                            var cityId = citySelect.value;
                            var itemCode = itemCode.value;
                            if (cityId !== '' && itemCode !== '') {
                                // Отправляем AJAX-запрос на сервер для получения районов
                                var xhr = new XMLHttpRequest();
                                xhr.open('GET', 'get_districts.php?cityid=' + cityId + '&itemcode=' + itemCode, true);
                                xhr.onreadystatechange = function() {
                                    if (xhr.readyState === 4 && xhr.status === 200) {
                                        var districtSelect = document.getElementById('district');
                                        districtSelect.innerHTML = xhr.responseText;
                                    }
                                };
                                xhr.send();
                            } else if (cityId !== '') {
                                // Отправляем AJAX-запрос на сервер для получения районов
                                var xhr = new XMLHttpRequest();
                                xhr.open('GET', 'get_districts.php?cityid=' + cityId , true);
                                xhr.onreadystatechange = function() {
                                    if (xhr.readyState === 4 && xhr.status === 200) {
                                        var districtSelect = document.getElementById('district');
                                        districtSelect.innerHTML = xhr.responseText;
                                    }
                                };
                                xhr.send();
                            } else {
                                // Очищаем список районов, если город не выбран
                                var districtSelect = document.getElementById('district');
                                districtSelect.innerHTML = '<option value="0">Выберите район</option>';
                            }
                        });
                        document.getElementById("city").addEventListener('change', function() {
                            var cityId = this.value;
                            if (cityId !== '') {
                                // Отправляем AJAX-запрос на сервер для получения районов
                                var xhr = new XMLHttpRequest();
                                xhr.open('GET', 'get_districts.php?cityid=' + cityId , true);
                                xhr.onreadystatechange = function() {
                                    if (xhr.readyState === 4 && xhr.status === 200) {
                                        var districtSelect = document.getElementById('district');
                                        districtSelect.innerHTML = xhr.responseText;
                                        console.log(districtSelect.innerHTML);
                                    }
                                };
                                xhr.send();
                            } else {
                                // Очищаем список районов, если город не выбран
                                var districtSelect = document.getElementById('district');
                                districtSelect.innerHTML = '<option value="0">Выберите район</option>';
                            }
                        });
                        </script>
                    </select>
                </div>
            </div>
            
                    
			<div class="form-group">
                <label for="net" class="col-sm-2">Подсеть</label>
                <?php if(!isset($res[15]))$res[15]="255";?>
                <div class="col-sm-3">
                    <input type="text" id="net" name="net" class="form-control" value="<?php echo $res[15];?>">
                </div>
            </div>
            <div class="form-group">
            
                <label for="phone" class="col-sm-2">Телефон</label>
                <?php if(!isset($res[11]))$res[11]="";?>
                <div class="col-sm-3">
                    <input type="text" id="phone" name="phone" class="form-control" value="<?php echo $res[11];?>">
                </div>
            </div>
            <div class="form-group">
                <label for="worktime" class="col-sm-2">Рабочее время</label>
                <?php if(!isset($res[12]))$res[12]="с 08-00 до 22-00";?>
                <div class="col-sm-3">
                    <input type="text" id="worktime" name="worktime" class="form-control" value="<?php echo $res[12];?>">
                </div>
            </div>
            <div class="form-group">
                <label for="itemname" class="col-sm-2">Наименование</label>
                <?php if(!isset($res[3]))$res[3]="";?>
                <div class="col-sm-3">
                    <input type="text" name="itemname" class="form-control" value="<?php echo $res[3];?>">
                </div>
            </div>
         
             <div class="form-group">
                <label for="downloadprice" class="col-sm-2">Загружать прайс автоматически?</label>
                <?php if(!isset($res[4]))$res[4]="1";?>
            	<div class="col-sm-3">
                    <select class="form-control" id="downloadprice" name = "downloadprice">
                        <option <?php if($res[4]=="0"){echo "selected";}?> value = "0">Нет</option>
                        <option <?php if($res[4]=="1"){echo "selected";}?> value = "1">Да</option>
                    </select>
                </div>
            </div>            
            <div class="form-group">
                <label class="col-sm-2" for="tominfarm">томинфарм</label>
            	 <div class="col-sm-3">
            	 <?php if(!isset($res[5]))$res[5]="0";?>
                    <select class="form-control" id="tominfarm" name = "tominfarm">
                        
                        <option <?php if($res[5]=="0"){echo "selected";}?> value = "0">Нет</option>
                        <option <?php if($res[5]=="1"){echo "selected";}?> value = "1">Выгружать</option>
                    </select>
               </div>
            </div>
            <div class="form-group">
                <label  class="col-sm-2" for="lekvapteke">lecvapteke</label>
            	 <div class="col-sm-3">
            	 <?php if(!isset($res[6]))$res[6]="0";?>
                    <select class="form-control" id="lekvapteke" name = "lekvapteke">
                       
                        <option <?php if($res[6]=="0"){echo "selected";}?> value = "0">Нет</option>
                         <option <?php if($res[6]=="1"){echo "selected";}?> value = "1">Выгружать</option>
                    </select>
               </div>
            </div>
            <div class="form-group">
                <label  class="col-sm-2" for="s09">s09</label>
            	 <div class="col-sm-3">
            	 <?php if(!isset($res[7]))$res[7]="0";?>
                    <select class="form-control" id="s09" name = "s09">
                       
                        <option <?php if($res[7]=="0"){echo "selected";}?> value = "0">Нет</option>
                         <option <?php if($res[7]=="1"){echo "selected";}?> value = "1">Выгружать</option>
                    </select>
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
<?php
   
   
   
   
?>