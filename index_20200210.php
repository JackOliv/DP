<?php
require "bd_connect1.php";

$searching_type = array("Аптекам", "Поставщикам");
$seller = array("Все аптеки", "Аптека Вита", "Первая социальная аптека");
ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);

$alldata["procentvigruzki"][1][1]=6.5;					//минимальный проент наценки на товар при котором партия выгружается на сайт АПТЕКА ВИТА для ЖВНЛ
$alldata["procentvigruzki"][1][0]=14;						//минимальный проент наценки на товар при котором партия выгружается на сайт АПТЕКА ВИТА для НЕ ЖВНЛ
$alldata["procentvigruzki"][2][1]=3;						//минимальный проент наценки на товар при котором партия выгружается на сайт ПЕРВОЙ СОЦИАЛЬНОЙ для ЖВНЛ
$alldata["procentvigruzki"][2][0]=7;						//минимальный проент наценки на товар при котором партия выгружается на сайт ПЕРВОЙ СОЦИАЛЬНОЙ для НЕ ЖВНЛ

$bestbefore=30;												//остаток срока годности для выгрузки товара на сайт в днях]

function cost($stock, $price)
{
    if ($stock == "1"){
        switch ($price) {
            case $price > 0 && $price <= 10:
                $price *= 1.8;
                break;
            case $price > 10 && $price <= 20:
                $price *= 1.7;
                break;
            case $price > 20 && $price <= 30:
                $price *= 1.55;
                break;
            case $price > 30 && $price <= 50:
                $price *= 1.5;
                break;
            case $price > 50 && $price <= 100:
                $price *= 1.45;
                break;
            case $price > 100 && $price <= 200:
                $price *= 1.35;
                break;
            case $price > 200 && $price <= 300:
                $price *= 1.32;
                break;
            case $price > 300 && $price <= 400:
                $price *= 1.28;
                break;
            case $price > 400 && $price <= 500:
                $price *= 1.26;
                break;
            case $price > 500 && $price <= 600:
                $price *= 1.24;
                break;
            case $price > 600 && $price <= 700:
                $price *= 1.22;
                break;
            case $price > 700 && $price <= 800:
                $price *= 1.19;
                break;
            case $price > 800 && $price <= 900:
                $price *= 1.18;
                break;
            case $price > 900 && $price <= 1000:
                $price *= 1.17;
                break;
            default:
                $price *= 1.15;
                break;
        }
    }else{
        switch ($price) {
            case $price > 0 && $price <= 10:
                $price *= 1.5;
                break;
            case $price > 10 && $price <= 20:
                $price *= 1.45;
                break;
            case $price > 20 && $price <= 30:
                $price *= 1.40;
                break;
            case $price > 30 && $price <= 50:
                $price *= 1.30;
                break;
            case $price > 50 && $price <= 100:
                $price *= 1.20;
                break;
            case $price > 100 && $price <= 200:
                $price *= 1.19;
                break;
            case $price > 200 && $price <= 300:
                $price *= 1.18;
                break;
            case $price > 300 && $price <= 400:
                $price *= 1.17;
                break;
            case $price > 400 && $price <= 500:
                $price *= 1.16;
                break;
            case $price > 500 && $price <= 600:
                $price *= 1.15;
                break;
            case $price > 600 && $price <= 700:
                $price *= 1.14;
                break;
            case $price > 700 && $price <= 800:
                $price *= 1.13;
                break;
            case $price > 800 && $price <= 900:
                $price *= 1.12;
                break;
            case $price > 900 && $price <= 1000:
                $price *= 1.11;
                break;
            default:
                $price *= 1.10;
                break;
        }
    }
    return round($price, 2);
}

function search($key, $searching, $stock)
{
	global $bestbefore;
	global $alldata;
    $link = mysqli_connect(HOST, USER, PW, DB);
    if (!$link) {
        echo "Ошибка: Невозможно установить соединение с MySQL." . PHP_EOL;
        echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL;
        echo "Текст ошибки error: " . mysqli_connect_error() . PHP_EOL;
        exit;
    }

    mysqli_set_charset($link, "utf8");
    
	if ($searching == 0){
		$query = "SELECT `brand`, `partner_code`, `name`, `madeby`, `count`, `price`, `godendo` , `distprice` , `nacenka` , `PRICEBLOCK` , `JVNL` , `type` FROM `" . TABLE . "` where `partner_code` != '' AND type >0 AND `name` like '$key'";
		if ($stock != 0){
			if($stock == 1) {
				$query .= " and `type`=1";
			} else if($stock == 2) {
				$query .= " and `type`=2";
			}
		}
	} else {
		$query = "SELECT `name`, `partner_code`, `madeby`, `count`, `price`, `godendo` , `distprice` , `nacenka` , `PRICEBLOCK` , `JVNL`, `type` FROM `" . TABLE . "` where `partner_code` != '' AND type =0 AND `name` like '$key'";
	}
 //	echo $query;
	$result = mysqli_query($link, $query);
    
	$query = "SELECT `brand`, `itemcode`, `itemname`, `uploaddate`, `connectionstring` FROM `" . PRICETABLE . "`;";
	//echo $query;
	$results = mysqli_query($link, $query);
	$pricesArr = array();
	while ($resultPrice = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
		$pricesArr[$resultPrice['itemcode']] = $resultPrice;
	}
	echo "<div style='border: medium'>";
	//$num_rows_1=mysqli_num_rows($result);
	//echo "<BR>$num_rows_1<BR>";
	if (mysqli_num_rows($result) == 0) {
		echo "<strong>Совпадений не найдено</strong>";
	} else {
		if($searching == 0){
			echo "<table class='table table-striped' id='Farma'>";
			echo "<thead>";
			echo <<<'COL'
					<tr><th style="background-position: 0% 50%">Дата прайса</th>
					<th style="background-position: 0% 50%">Бренд</th>
					<th style="background-position: 0% 50%;">Аптека</th>
					<th style="background-position: 0% 50%;">Наименование</th>
					<th style="background-position: 0% 50%">Производитель</th>
					<th style="background-position: 0% 50%;">Остаток</th>
					<th style="background-position: 0% 50%;">Цена</th>
					<th style="background-position: 0% 50%;">Срок годности</th></tr></thead>
COL;
		}elseif ($searching==1) {
			echo "<table class='table table-striped' id='Diller'>";
			echo "<thead style='background-color: white'>";
			echo <<<'COL'
					<tr><th style="background-position: 0% 50%">Дата прайса</th>
					<th style="background-position: 0% 50%">Поставщик</th>
					<th style="background-position: 0% 50%;">Наименование</th>
					<th style="background-position: 0% 50%">Производитель</th>
					<th style="background-position: 0% 50%;">Остаток</th>
					<th style="background-position: 0% 50%;">Цена</th>
					<th><img src="1.png" style="width: 20px;height: 20px; padding-right: 5px"/>Цена</th>
					<th><img src="2.png" style="width: 20px;height: 15px ;padding-right: 5px"/>Цена</th>
					<th style="background-position: 0% 50%;">Срок годности</th></tr>
					</thead>
COL;
		}
		echo '<tbody>';
		while ($res = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			echo "<tr>";
			$i = 0;
			// Код
			if($res['partner_code'] > 0) {
				$code = $res['partner_code'];
			} else {
				$code = $res['partner_code'];
			}
			// Дата прайса
			//echo "|$code|";
			$uploadDate = ($pricesArr[$code]['uploaddate'] != '') ? $pricesArr[$code]['uploaddate'] : '1900-01-01';
			$ud = DateTime::createFromFormat("Y-m-d", $uploadDate);
			
			echo "<td style='margin-top: -15px'>" . $ud->format('d.m.Y') . "</td>";
			//echo "<td style='margin-top: -15px'>".$ud."</td>";
		//	echo "3333333";
			if ($searching == 0){
				// Бренд
				
					echo "<td style='margin-top: -15px'><div hidden='true'>".$pricesArr[$res['partner_code']]['brand']."</div><img src='images/".$pricesArr[$res['partner_code']]['brand'].".png'></td>";
				
				// Аптека (адрес)
				$addr = ($pricesArr[$code]['itemname'] != '') ? $pricesArr[$code]['itemname'] : '-';
				echo "<td style='margin-top: -15px'>" . $addr . "</td>";
			}
			if ($searching==1) {
				$post = ($pricesArr[$code]['itemname'] != '') ? $pricesArr[$code]['itemname'] : '-';
				echo "<td style='margin-top: -15px'>" . $post . "</td>";
			}
			$img="";
			if(isset($res["godendo"])) 
			{
				$godendo=$res["godendo"];									//срок годности партии
				$dt=explode("-",$godendo);
				//print_r($dt);
				$intgodendo=mktime(23,59,59,$dt[1],$dt[2],$dt[0]);
				$diftime=$bestbefore*24*60*60;
				$dtime=$intgodendo-$diftime-time();
				if(($dtime<0)&&($intgodendo!=61199))
				{
					$img.="<img height=30 width=30 title='Не выгружается на сайт. Короткий срок годности.' src='images/notsrok.jpg'>";
				}
			}
			if(isset($res["PRICEBLOCK"]))$ignorenacenka=$res["PRICEBLOCK"];									//если установлен блок скидки то зависимость от величины наценки отключается
			
			if(isset($res["JVNL"])){$isjvnl=$res["JVNL"];}
			
			$predelnacenki=1.00;
			$distprice=$res["distprice"]*1.00;
			if($distprice>0)
			{
				$nacenka=$res["nacenka"]*1.00;
				$predelnacenki=$nacenka-$alldata["procentvigruzki"][$res['type']][$isjvnl];
			}
			if(($ignorenacenka==0)&&($predelnacenki<0))
			{
				$img.="<img height=20 width=20 title='Низкая наценка. Товар на сайт не выгружается.' src='images/ns.png'>";
				$loadtosite=0;								//флаг выгрузки конкретной партии в файл выгрузки на сайт, устанавливаем НЕТ
				//$colnacenka++;
			}
			elseif($ignorenacenka==1)
			{
				//$img.="<img height=30 width=30 title='Блок скидки. Скидка не предоставляется.' src='images/ns.png'>";
			}
			
			echo "<td style='margin-top: -15px'>$img" . $res['name'] . "</td>";
			
			echo "<td style='margin-top: -15px'>" . $res['madeby'] . "</td>";
			echo "<td style='margin-top: -15px'>" . $res['count'] . "</td>";
			//round($price, 2);
			if($searching==0)
			{
				
				$price0=$res['price']*1;
				$price3=round($res['price']*0.97, 2);
				$price4=round($res['price']*0.96, 2);
				$price5=round($res['price']*0.95, 2);
				$price7=round($res['price']*0.93, 2);
			//	echo "<td style='margin-top: -15px'>$price0<div id='pricebar' title='Скидка 3%: $price3 &#013;Скидка 4%: $price4 &#013;Скидка 5%: $price5 &#013;Скидка 7%: $price7'>" . $price0 . "</div></td>";
			echo "<td style='margin-top: -15px' title='Скидка 3%: $price3 &#013;Скидка 4%: $price4 &#013;Скидка 5%: $price5 &#013;Скидка 7%: $price7'>$price0</td>";
			}
			if ($searching==1) {
				$price0 = $res['price'];
				$price5 = round($price0*1.05, 2);
				$price10 = round($price0*1.1, 2);
				$price15 = round($price0*1.15, 2);
				$price20 = round($price0*1.20, 2);
				$price25 = round($price0*1.25, 2);
				$price30 = round($price0*1.3, 2);
				$price35 = round($price0*1.35, 2);
				$price40 = round($price0*1.4, 2);
				$price50 = round($price0*1.5, 2);
				echo "<td style='margin-top: -15px'   title='Наценка 5%: $price5 &#013;Наценка 10%: $price10 &#013;Наценка 15%: $price15 &#013;Наценка 20%: $price20 &#013;Наценка 25%: $price25 &#013;Наценка 30%: $price30 &#013;Наценка 35%: $price35 &#013;Наценка 40%: $price40 &#013;Наценка 50%: $price50'>" . $res['price'] . "</td>";
				$price0 = cost("1", $res['price']);
				$price3 = round($price0*0.97, 2);
				$price4 = round($price0*0.96, 2);
				$price5 = round($price0*0.95, 2);
				$price7 = round($price0*0.93, 2);
				echo "<td style='margin-top: -15px'  title='Скидка 3%: $price3 &#013;Скидка 4%: $price4 &#013;Скидка 5%: $price5 &#013;Скидка 7%: $price7'><div id='pricebar'>" .$price0 . "</div></td>";
				$price0 = cost("2", $res['price']);
				$price3 = round($price0*0.97, 2);
				$price4 = round($price0*0.96, 2);
				$price5 = round($price0*0.95, 2);
				$price7 = round($price0*0.93, 2);
				echo "<td style='margin-top: -15px' title='Скидка 3%: $price3 &#013;Скидка 4%: $price4 &#013;Скидка 5%: $price5 &#013;Скидка 7%: $price7'><div id='pricebar'>" . $price0 . "</div></td>";
			}
			//echo "<td style='margin-top: -15px'>" . $res['nacenka']. "%</td>";
			if(($res['godendo']==NULL)||($res['godendo']==""))$res['godendo']="2000-01-01";
			$srok = DateTime::createFromFormat("Y-m-d", $res['godendo']);
			echo "<td style='margin-top: -15px'>" . $srok->format('d.m.Y') . "</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
    }

    mysqli_free_result($result);
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/bootstrap-theme.min.css">
    <link rel="stylesheet" type="text/css" href="/css/jquery.dataTables.css">
    <style> td.Vita
           {
           background-color: #72d9e0;
           }
            td.Social
           {
           background-color:#f263b0};
            }
    </style>
    <title>Справка Аптека Вита</title>
</head>
<body>
<form  class="form-inline" method="POST">
    <div style="margin:10px 10px 10px 500px" class="form-group" >
        <div>
            <div style="text-align: center"><label>Справочная служба "Аптека Вита"</label></div>
            <div1>
                <div1>
                    <div1 style="margin-top:20px">
                        <label>Поиск по </label>
                    </div1>
                </div1>
                <?php
                $i = 0;
                foreach ($searching_type as $k=>$v){
                    echo "<div1 style='margin-left:10px' class='radio-inline'><input type='radio' name='searching_type' value='$k'";
                    if (isset($_POST['searching_type']) && ($_POST['searching_type']==$k)){echo 'checked';}
                    if (!isset($_POST['searching_type']) && $i == 0) echo 'checked';
                    echo ">";
                    echo "$v</div1>";
                    $i++;
                }
                ?>
            </div1>
        </div>
        <div>
            <div1 style="margin-top:10px">
                <label>Бренд</label>
            </div1>
            <div1 style="margin-left: 30px">
                <?php
                $j = 0;
                foreach ($seller as $k=>$v){
                    echo "<div1 class='radio-inline'><input type='radio' name='seller' value='$k'";
                    if (isset($_POST['seller']) && ($_POST['seller']==$k)){ echo 'checked'; }
                    if (!isset($_POST['seller']) && $j == 0) echo 'checked';
                    echo ">";
                    echo "$v</div1>";
                    $j++;
                }
                ?>
            </div1>
            <div>
                <div1 class="input-group">
                    <input style="width: 422px; margin-top: 20px" type="text" class="form-control" name = "search" placeholder="Строка поиска" value="<?php if(isset($_POST['search'])){echo($_POST['search']);} else{echo "";}?>">
                </div1>
                <div1>
                    <button style="margin-top: 20px" type="submit" name = "submit" class="btn btn-primary">Поиск</button>
                </div1>
            </div>
        </div>
    </div>
</form>


<script src="/js/jquery-3.2.1.js"></script>
<script type="text/javascript" charset="utf8" src="/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf8" src="/js/moment.min.js"></script>
<script type="text/javascript" charset="utf8" src="/js/datetime-moment.js"></script>
<!--<script src="//cdn.datatables.net/plug-ins/1.10.15/i18n/Russian.json"></script>-->
<script>
    $(function() {
        var cls = document.getElementById('Diller');
        $.fn.dataTable.moment( 'DD.MM.YYYY');
        if (!cls){
            $('#Farma').DataTable({
                "searching": false,
                "stateSave": true,
                "language":{
                    info:"Показано с _START_ по _END_ из _TOTAL_ найденных",
                    "paginate": {
                        "first":      "Первая",
                        "last":       "Последняя",
                        "next":       "Следующая",
                        "previous":   "Предыдущая"
                    },
                    "lengthMenu":     "Показывать по _MENU_ "
                }
            })
        } else {
            var a = $('#Diller').DataTable({
                "searching": false,
                "stateSave": true,
                "language":{
                    info:"Показано с _START_ по _END_ из _TOTAL_ найденных",
                    "paginate": {
                        "first":      "Первая",
                        "last":       "Последняя",
                        "next":       "Следующая",
                        "previous":   "Предыдущая"
                    },
                    "lengthMenu":     "Показывать по _MENU_ "
                },
                "columns":[
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    {className: "Vita"},
                    {className: "Social"},
                    null
                ]
            });
        };
        $("div1").css({"display": "inline-block"});
    });


</script>

<?php

if (isset($_POST["submit"])) {
    $key1 = (isset($_POST["search"])) ? $_POST["search"] : '';
    $key2 = explode(" ", $key1);
    $key = implode("%' AND `name` LIKE '%", $key2);
    $key = "%$key%";
    $stock = array_search($seller[$_POST["seller"]], $seller, true);
    
if(mb_strlen($key1) >= 3) {
		search($key, $_POST["searching_type"], $stock);
    } else {
		echo "<div style='border: medium'>";
		echo "<strong>Минимальная строка запроса - 3 символа</strong>";
		echo '</div>';
    }
}
?>

</body>
</html>