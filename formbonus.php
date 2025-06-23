<?php
require_once "bd_connect_bonus.php";
$connection = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connection, "utf8");
if (!$connection) {
    echo "Ошибка: Невозможно установить соединение с MySQL." . PHP_EOL;
    echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Текст ошибки error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}

	$sqlidcard="select * from bonuscards where 1 order by idcard";
	$residcard = mysqli_query($connection, $sqlidcard);
	//$residcard = mysqli_fetch_row($queridcard);
	$num_rowsidcard=mysqli_num_rows($residcard);
	if($num_rowsidcard==0)
	{
		echo " нет введенных в систему карт<br>";
	}
	else
	{
		$strcards="";
		for($i=0;$i<$num_rowsidcard;$i++)
		{
			$rowidcard = mysqli_fetch_array($residcard);
			$strcards.=$rowidcard["idcard"]." ".$rowidcard["cardnumber"]." ".$rowidcard["telnumber"]."<br>";
			
		}
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
        <form class="form-horizontal" action="show.php" method="GET">
        
         	<div class="form-group">
                <label for="cardnumber" class="col-sm-2">Номер карты</label>
                <div class="col-sm-3">
                    <input type="text" id="cardnumber" name="cardnumber" class="form-control" value="610300643001000001">
                </div>
            </div>
        	<div class="form-group">
                <label for="telnumber" class="col-sm-2">Номер телефона</label>
                <div class="col-sm-3">
                    <input type="text" id="telnumber" name="telnumber" class="form-control" value="89138278898">
                </div>
            </div>
            <div class="form-group">
                <label for="valbonus" class="col-sm-2">Сумма баллов</label>
                <div class="col-sm-3">
                    <input type="text" id="valbonus" name="valbonus" class="form-control" value="5">
                </div>
            </div>
            <div class="form-group">
                <label for="sumorder" class="col-sm-2">сумма чека</label>
                <div class="col-sm-3">
                    <input type="text" id="sumorder" name="sumorder" class="form-control" value="1000">
                </div>
            </div>
            <div class="form-group">
                <label for="apteka" class="col-sm-2">сумма чека</label>
                <div class="col-sm-3">
                    <input type="text" id="apteka" name="apteka" class="form-control" value="10056">
                </div>
            </div>
            <div class="form-group">
                <label for="cash" class="col-sm-2">сумма чека</label>
                <div class="col-sm-3">
                    <input type="text" id="cash" name="cash" class="form-control" value="1">
                </div>
            </div>
            <div class="form-group">
                <label for="typebonus" class="col-sm-2">Тип действия</label>
                <div class="col-sm-3">
                    <select class="form-control" id="typebonus" name = "typebonus">
                   		<option value = "2">Просто посмотреть</option>
                        <option selected value = "1">начислить баллы</option>
                        <option value = "0">Списать баллы</option>
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

<?php
echo "$strcards";
?>
