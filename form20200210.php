<?php
require_once "bd_connect1.php";
$connection = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connection, "utf8");
if (!$connection) {
    echo "Ошибка: Невозможно установить соединение с MySQL." . PHP_EOL;
    echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Текст ошибки error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}
if(isset($_GET['itemcode'])){
    $q="SELECT `type`, `brand`, `itemcode`, `itemname`,`downloadprice`, `connectionstring`, `load_to_site`, `tominfarm`, `lekvapteke`, `s09`, `status`, `siteid`, `fk_kod`,`num_term`,`city`, `phone`, `worktime`, `firm`, `uchet` from " . PRICETABLE . " 
            where `itemcode`={$_GET['itemcode']}";
    $quer = mysqli_query($connection, $q);
    $res = mysqli_fetch_row($quer);
}
if(isset($_POST['submit'])) {
    $date = date("Y-m-d");
   /* if ($_POST['downloadprice'] != "да"){
        $_POST['downloadprice'] = "";
    }*/
    if (isset($_GET['itemcode'])){
        $query="UPDATE `" . PRICETABLE . "` SET 
            `type`='{$_POST['type']}',
            `brand`='{$_POST['brand']}',
            `itemcode`='{$_POST['itemcode']}',
            `itemname`='{$_POST['itemname']}',
            `downloadprice`='{$_POST['downloadprice']}',
            `load_to_site`='{$_POST['load_to_site']}',
            `tominfarm`='{$_POST['tominfarm']}',
            `lekvapteke`='{$_POST['lekvapteke']}',
            `s09`='{$_POST['s09']}',
            `siteid`='{$_POST['siteid']}',
            `fk_kod`='{$_POST['fk_kod']}',
            `num_term`='{$_POST['num_term']}',
            `city`='{$_POST['city']}',
            `phone`='{$_POST['phone']}',
            `worktime`='{$_POST['worktime']}',
            `firm`='{$_POST['firm']}',
            `uchet`='{$_POST['uchet']}',
            `connectionstring`='".mysqli_real_escape_string($connection, $_POST['connectionstring'])."' 
            WHERE `itemcode` = {$_GET['itemcode']}";
    }else {
    	$_POST['fk_kod']+=0;
    	
       $query = "INSERT INTO `" . PRICETABLE . "` (`type`,`brand`,`itemcode`,`itemname`,`uploaddate`,`downloadprice`,`load_to_site`,`tominfarm`,`lekvapteke`,`s09`,`connectionstring`,`siteid`, `fk_kod`,`num_term`,`city`, `phone`, `worktime`, `firm`, `uchet`) VALUES (".$_POST['type'].", ".$_POST['brand'].", ".$_POST['itemcode'].", \"".$_POST['itemname']."\", NULL, ".$_POST['downloadprice'].", ".$_POST['load_to_site'].", ".$_POST['tominfarm'].", ".$_POST['lekvapteke'].", ".$_POST['s09'].",  \"".mysqli_real_escape_string($connection, $_POST['connectionstring'])."\", ".$_POST['siteid'].", ".$_POST['fk_kod'].", ".$_POST['num_term'].", \"".$_POST['city']."\", \"".$_POST['phone']."\", \"".$_POST['worktime']."\", ".$_POST['firm'].", \"".$_POST['uchet']."\");";
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
        header("Location:/".ADMINPHP);
    }
    mysqli_close($connection);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Добавление аптеки (поставщика)</title>
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
            form, h3, #subm{
                margin-left: 10px;
            }

        </style>
    </head>
    <body>
        <h3>Карточка аптеки (поставщика)</h3>
        <form class="form-horizontal"  method="POST">
            <div class="form-group">
                <label for="type" class="col-sm-2">Тип</label>
                <div class="col-sm-3">
                    <select class="form-control" id="type" name = "type">
                        <option <?php if($res[0]=="1"){echo "selected";}?> value = "1">Аптека</option>
                        <option <?php if($res[0]=="0"){echo "selected";}?> value = "0">Поставщик</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="brand" class="col-sm-2">Бренд</label>
                <div class="col-sm-3">
                    <select class="form-control" id="brand" name="brand">
                   		<option <?php if($res[1]=="0"){echo "selected";}?> value = "0">Отсутствует</option>
                        <option <?php if($res[1]=="1"){echo "selected";}?> value = "1">Аптека Вита</option>
                        <option <?php if($res[1]=="2"){echo "selected";}?> value = "2">Первая социальная аптека</option>
                    </select>
                </div>
            </div>
             <div class="form-group">
                <label for="brand" class="col-sm-2">Юрлицо</label>
                <div class="col-sm-3">
                    <select class="form-control" id="firm" name="firm">
                   		<option <?php if($res[17]=="0"){echo "selected";}?> value = "0">Отсутствует</option>
                        <option <?php if($res[17]=="1"){echo "selected";}?> value = "1">ООО Аптека Вита</option>
                        <option <?php if($res[17]=="2"){echo "selected";}?> value = "2">ООО Чирчик</option>
                        <option <?php if($res[17]=="3"){echo "selected";}?> value = "3">ООО Аптека 36,6</option>
                        <option <?php if($res[17]=="4"){echo "selected";}?> value = "4">ООО Арт Лана</option>
                    </select>
                </div>
            </div>
             <div class="form-group">
                <label for="uchet" class="col-sm-2">Учетная система</label>
                <div class="col-sm-3">
                    <select class="form-control" id="uchet" name="uchet">
                   		<option <?php if($res[18]==""){echo "selected";}?> value = "">Отсутствует</option>
                        <option <?php if($res[18]=="gb"){echo "selected";}?> value = "gb">Граф Алексей Бестужев</option>
                        <option <?php if($res[18]=="fk"){echo "selected";}?> value = "fk">1С Фарм Капитан</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="itemcode" class="col-sm-2">Код</label>
                <div class="col-sm-3">
                    <input type="text" id="itemcode" name="itemcode" class="form-control" value="<?php echo $res[2];?>">
                </div>
            </div>
             <div class="form-group">
                <label for="itemcode" class="col-sm-2">Код Фарм капитан</label>
                <div class="col-sm-3">
                    <input type="text" id="fk_kod" name="fk_kod" class="form-control" value="<?php echo $res[12];?>">
                </div>
            </div>
             <div class="form-group">
                <label for="itemcode" class="col-sm-2">Колво касс в аптеке</label>
                <div class="col-sm-3">
                    <input type="text" id="num_term" name="num_term" class="form-control" value="<?php echo $res[13];?>">
                </div>
            </div>
            <div class="form-group">
                <label for="itemcode" class="col-sm-2">Город</label>
                <div class="col-sm-3">
                    <input type="text" id="city" name="city" class="form-control" value="<?php echo $res[14];?>">
                </div>
            </div>
            <div class="form-group">
                <label for="itemcode" class="col-sm-2">Телефон</label>
                <div class="col-sm-3">
                    <input type="text" id="phone" name="phone" class="form-control" value="<?php echo $res[15];?>">
                </div>
            </div>
            <div class="form-group">
                <label for="itemcode" class="col-sm-2">Рабочее время</label>
                <div class="col-sm-3">
                    <input type="text" id="worktime" name="worktime" class="form-control" value="<?php echo $res[16];?>">
                </div>
            </div>
            <div class="form-group">
                <label for="itemname" class="col-sm-2">Наименование</label>
                <div class="col-sm-3">
                    <input type="text" name="itemname" class="form-control" value="<?php echo $res[3];?>">
                </div>
            </div>
         
             <div class="form-group">
                <label for="downloadprice" class="col-sm-2">Загружать прайс автоматически?</label>
            	<div class="col-sm-3">
                    <select class="form-control" id="downloadprice" name = "downloadprice">
                        <option <?php if($res[4]=="0"){echo "selected";}?> value = "0">Нет</option>
                        <option <?php if($res[4]=="1"){echo "selected";}?> value = "1">Да</option>
                    </select>
                </div>
            </div>            
            
            
            <div class="form-group">
                <label for="connectionstring" class="col-sm-2">Строка подключения</label>
                <div class="col-sm-3">
                    <input type="text" name="connectionstring" class="form-control" value="<?php echo $res[5];?>">
                </div>
            </div>
            <div class="form-group">
                <label for="load_to_site" class="col-sm-2">выгружать на сайт?</label>
            	<div class="col-sm-3">
                    <select class="form-control" id="load_to_site" name = "load_to_site">
                        <option <?php if($res[6]=="0"){echo "selected";}?> value = "0">Нет</option>
                        <option <?php if($res[6]=="1"){echo "selected";}?> value = "1">Выгружать</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="load_to_site" class="col-sm-2">номер аптеки на сайте</label>
            	<div class="col-sm-3">
                     <input type="text" name="siteid" class="form-control" value="<?php echo $res[11];?>">
                </div>
            </div>
            <div class="form-group">
                <label for="connectionstring" class="col-sm-2">томинфарм</label>
            	 <div class="col-sm-3">
                    <select class="form-control" id="tominfarm" name = "tominfarm">
                        
                        <option <?php if($res[7]=="0"){echo "selected";}?> value = "0">Нет</option>
                        <option <?php if($res[7]=="1"){echo "selected";}?> value = "1">Выгружать</option>
                    </select>
               </div>
            </div>
            <div class="form-group">
                <label for="connectionstring" class="col-sm-2">lecvapteke</label>
            	 <div class="col-sm-3">
                    <select class="form-control" id="lekvapteke" name = "lekvapteke">
                       
                        <option <?php if($res[8]=="0"){echo "selected";}?> value = "0">Нет</option>
                         <option <?php if($res[8]=="1"){echo "selected";}?> value = "1">Выгружать</option>
                    </select>
               </div>
            </div>
            <div class="form-group">
                <label for="connectionstring" class="col-sm-2">s09</label>
            	 <div class="col-sm-3">
                    <select class="form-control" id="s09" name = "s09">
                       
                        <option <?php if($res[9]=="0"){echo "selected";}?> value = "0">Нет</option>
                         <option <?php if($res[9]=="1"){echo "selected";}?> value = "1">Выгружать</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <div>
                    <input type="submit" name="submit" value="Сохранить" id="subm">
                </div>
            </div>
        </form>
    </body>
</html>


