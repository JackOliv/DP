<?php
require "bd_connect1.php";
require "allfunc.php";
$searching_type = ["Аптекам", "Поставщикам", "Складам"];
$link = mysqli_connect(HOST, USER, PW, DB);

$seller[0]['id']=0;$seller[0]['name']="Все аптеки";
$querycity = "SELECT * FROM `brand` WHERE 1 order by id";
$resultcity = mysqli_query($link, $querycity);
$num_rowscity=mysqli_num_rows($resultcity);

if ($num_rowscity> 0) {
    for($i=0;$i<$num_rowscity;$i++) {
        $i1=$i+1;
        $resseller = mysqli_fetch_array($resultcity);
        $seller[$i1]=$resseller;
		
    }
}

$cities[0]['idcity']=0;$cities[0]['namecity']="Все города";
$querycity = "SELECT * FROM `cities` WHERE 1 order by idcity";
$resultcity = mysqli_query($link, $querycity);
$num_rowscity=mysqli_num_rows($resultcity);

if ($num_rowscity> 0) {
    for($i=0;$i<$num_rowscity;$i++) {
        $i1=$i+1;
        $rescity = mysqli_fetch_array($resultcity);
        $cities[$i1]=$rescity;
    }
}

$cities_check=0;
$district_check=0;
ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);


$alldata["procentvigruzki"][0][1]=0;						//минимальный проент наценки на товар при котором партия выгружается на сайт АПТЕКА ВИТА для ЖВНЛ
$alldata["procentvigruzki"][0][0]=0;						//минимальный проент наценки на товар при котором партия выгружается на сайт АПТЕКА ВИТА для НЕ ЖВНЛ
$alldata["procentvigruzki"][1][1]=6.5;						//минимальный проент наценки на товар при котором партия выгружается на сайт АПТЕКА ВИТА для ЖВНЛ
$alldata["procentvigruzki"][1][0]=14;						//минимальный проент наценки на товар при котором партия выгружается на сайт АПТЕКА ВИТА для НЕ ЖВНЛ
$alldata["procentvigruzki"][2][1]=3;						//минимальный проент наценки на товар при котором партия выгружается на сайт ПЕРВОЙ СОЦИАЛЬНОЙ для ЖВНЛ
$alldata["procentvigruzki"][2][0]=7;						//минимальный проент наценки на товар при котором партия выгружается на сайт ПЕРВОЙ СОЦИАЛЬНОЙ для НЕ ЖВНЛ
$alldata["procentvigruzki"][3][1]=6.5;						//минимальный проент наценки на товар при котором партия выгружается на сайт АПТЕКА НИКА для ЖВНЛ
$alldata["procentvigruzki"][3][0]=14;						//минимальный проент наценки на товар при котором партия выгружается на сайт АПТЕКА НИКА для НЕ ЖВНЛ
$alldata["procentvigruzki"][4][1]=6.5;						//минимальный проент наценки на товар при котором партия выгружается на сайт Добротека для ЖВНЛ
$alldata["procentvigruzki"][4][0]=14;						//минимальный проент наценки на товар при котором партия выгружается на сайт ДОБРОТЕКА для НЕ ЖВНЛ

$bestbefore=30;		//остаток срока годности для выгрузки товара на сайт в днях]




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

function search($key, $searching, $stock, $serchcities, $serchdistrict)
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
    
	if ($searching == 0)
	{
		if($serchcities==0 && $serchdistrict ==0)
		{
			$query = "SELECT pr.brand, pr.partner_code, pr.name, pr.madeby, pr.count, pr.price, pr.godendo , pr.distprice , pr.nacenka , pr.PRICEBLOCK , pr.JVNL , pr.type 
			FROM " . PRICETABLE . " pr 
			LEFT JOIN " . PARTNERSTABLE . " par ON pr.partner_code=par.itemcode 
			where par.type = 1 AND pr.name like '$key'";
			if ($stock != 0){
				if($stock == 1) {
					$query .= " and pr.type=1";
				} else if($stock == 2) {
					$query .= " and pr.type=2";
				}
				else if($stock == 3) {
					$query .= " and pr.type=3";
				}
				else if($stock == 4) {
					$query .= " and pr.type=4";
				}
			}
		}
		else if ($serchdistrict ==0){
			$query = "SELECT pr.brand, pr.partner_code, pr.name, pr.madeby, pr.count, pr.price, pr.godendo , pr.distprice , pr.nacenka , pr.PRICEBLOCK , pr.JVNL , pr.type 
			FROM " . PRICETABLE . " pr 
			LEFT JOIN " . PARTNERSTABLE . " par ON pr.partner_code=par.itemcode 
			where par.type = 1 AND pr.name like '$key'";
			if ($stock != 0){
				if($stock == 1) {
					$query .= " and pr.type=1";
				} else if($stock == 2) {
					$query .= " and pr.type=2";
				}
				else if($stock == 3) {
					$query .= " and pr.type=3";
				}
				else if($stock == 4) {
					$query .= " and pr.type=4";
				}
			}
					$query .= " and par.city='$serchcities'";
		}
		else
		{
			$query = "SELECT pr.brand, pr.partner_code, pr.name, pr.madeby, pr.count, pr.price, pr.godendo , pr.distprice , pr.nacenka , pr.PRICEBLOCK , pr.JVNL , pr.type 
			FROM " . PRICETABLE . " pr 
			LEFT JOIN " . PARTNERSTABLE . " par ON pr.partner_code=par.itemcode 
			where par.type = 1 AND pr.name like '$key'";
			if ($stock != 0){
				if($stock == 1) {
					$query .= " and pr.type=1";
				} else if($stock == 2) {
					$query .= " and pr.type=2";
				}
				else if($stock == 3) {
					$query .= " and pr.type=3";
				}
				else if($stock == 4) {
					$query .= " and pr.type=4";
				}
			}
					$query .= " and par.city='$serchcities'";
					$query .= " and par.districtid='$serchdistrict'";


		}
	} 
	elseif($searching == 2)
	{
		$query = "SELECT pr.brand, pr.partner_code, pr.name, pr.madeby, pr.count, pr.price, pr.godendo , pr.distprice , pr.nacenka , pr.PRICEBLOCK , pr.JVNL , pr.type 
		FROM " . PRICETABLE . " pr 
		LEFT JOIN " . PARTNERSTABLE . " par ON pr.partner_code=par.itemcode 
		where par.type = 2 AND pr.name like '$key'";

	}
	
	else {
		$query = "SELECT pr.brand, pr.partner_code, pr.name, pr.madeby, pr.count, pr.price, pr.godendo , pr.distprice , pr.nacenka , pr.PRICEBLOCK , pr.JVNL , pr.type 
		FROM " . PRICETABLE . " pr 
		LEFT JOIN " . PARTNERSTABLE . " par ON pr.partner_code=par.itemcode 
		where par.type = 0 AND pr.name like '$key'";
	}
	$result = mysqli_query($link, $query);
    
	$query = "SELECT `brand`, `itemcode`, `itemname`, `uploaddate`, `connectionstring`, `namecity`, `name` 
          FROM `" . PARTNERSTABLE . "` par 
          LEFT JOIN cities ct ON par.city = ct.idcity 
          LEFT JOIN district dis ON par.districtid = dis.id";
	$results = mysqli_query($link, $query);
	$pricesArr = array();
	while ($resultPrice = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
		$pricesArr[$resultPrice['itemcode']] = $resultPrice;
	}
	echo "<div style='border: medium'>";
	if (mysqli_num_rows($result) == 0) {
		echo "<strong>Совпадений не найдено</strong>";
	} else {
		if($searching == 0)
		{
			echo "<table class='table table-striped' id='Farma'>";
			echo "<thead>";
			echo <<<'COL'
					<tr><th style="background-position: 0% 50%">Дата прайса</th>
					<th style="background-position: 0% 50%">Бренд</th>
					<th style="background-position: 0% 50%;">Аптека</th>
					<th style="background-position: 0% 50%;">Город</th>
					<th style="background-position: 0% 50%;">Район</th>
					<th style="background-position: 0% 50%;">Наименование</th>
					<th style="background-position: 0% 50%">Производитель</th>
					<th style="background-position: 0% 50%;">Остаток</th>
					<th style="background-position: 0% 50%;">Цена</th>
					<th style="background-position: 0% 50%;">Срок годности</th></tr></thead>
COL;
		}
		elseif ($searching==1) 
		{
			echo "<table class='table table-striped' id='Diller'>";
			echo "<thead style='background-color: white'>";
			echo <<<'COL'
					<tr><th style="background-position: 0% 50%">Дата прайса</th>
					<th style="background-position: 0% 50%">Поставщик</th>
					<th style="background-position: 0% 50%;">Наименование</th>
					<th style="background-position: 0% 50%">Производитель</th>
					<th style="background-position: 0% 50%;">Остаток</th>
					<th style="background-position: 0% 50%;">Цена</th>
					<th><img src="images\1.png" style="width: 20px;height: 20px; padding-right: 5px"/>Цена</th>
					<th><img src="images\2.png" style="width: 20px;height: 15px ;padding-right: 5px"/>Цена</th>
					<th style="background-position: 0% 50%;">Срок годности</th></tr>
					</thead>
COL;
		}
		elseif($searching == 2)
		{
			echo "<table class='table table-striped' id='Farma'>";
			echo "<thead>";
			echo <<<'COL'
					<tr><th style="background-position: 0% 50%">Дата прайса</th>
					<th style="background-position: 0% 50%;">Склад</th>
					<th style="background-position: 0% 50%;">Наименование</th>
					<th style="background-position: 0% 50%">Производитель</th>
					<th style="background-position: 0% 50%;">Остаток</th>
					<th style="background-position: 0% 50%;">Цена</th>
					<th style="background-position: 0% 50%;">Срок годности</th></tr></thead>
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
			$uploadDate = ($pricesArr[$code]['uploaddate'] != '') ? $pricesArr[$code]['uploaddate'] : '1900-01-01';
			$ud = DateTime::createFromFormat("Y-m-d", $uploadDate);
			
			echo "<td style='margin-top: -15px'>" . $ud->format('d.m.Y') . "</td>";
			if ($searching == 0){
				// Бренд
				
					echo "<td style='margin-top: -15px'><div hidden='true'>".$pricesArr[$res['partner_code']]['brand']."</div><img src='images/".$pricesArr[$res['partner_code']]['brand'].".png'></td>";
				
				// Аптека (адрес)
				$addr = ($pricesArr[$code]['itemname'] != '') ? $pricesArr[$code]['itemname'] : '-';
				echo "<td style='margin-top: -15px'>" . $addr . "</td>";
				$city=$pricesArr[$code]["namecity"];
				echo "<td style='margin-top: -15px'>" . $city . "</td>";
				$districtid=$pricesArr[$code]["name"];
				echo "<td style='margin-top: -15px'>" . $districtid . "</td>";
			}
			if ($searching == 2){
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
			if(($searching==0)||($searching==2))
			{
				
				$price0=$res['price']*1;
				$price3=round($res['price']*0.97, 2);
				$price4=round($res['price']*0.96, 2);
				$price5=round($res['price']*0.95, 2);
				$price7=round($res['price']*0.93, 2);
			echo "<td style='margin-top: -15px'>$price0</td>";
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
				echo "<td style='margin-top: -15px'><div id='pricebar'>" .$price0 . "</div></td>";
				$price0 = cost("2", $res['price']);
				$price3 = round($price0*0.97, 2);
				$price4 = round($price0*0.96, 2);
				$price5 = round($price0*0.95, 2);
				$price7 = round($price0*0.93, 2);
				echo "<td style='margin-top: -15px'>" . $price0 . "</div></td>";
			}
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
	<link rel="stylesheet" href="/css/avstyles.css">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/bootstrap-theme.min.css">
    <link rel="stylesheet" type="text/css" href="/css/jquery.dataTables.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style> td.Vita
           {
           background-color: #72d9e0;
           }
            td.Social
           {
           background-color:#f263b0;
           }
		   .blinking {
        animation: blinkingText 2.8s infinite;
        }
        
        @keyframes blinkingText {
            0% { background: #ececec; }
            40% { background: #fab9b9; }
            100% { background: #ececec; }
        }
		.headerfont{
			font-size: 16px;
			text-align: center;
			
		}	
    </style>
    <title>Справка Аптека Вита</title>
</head>
<body>
<table width=100% border=0>
	<tr>
		<td width=100% >
			<?php 
			$navbarType = "user"; 
			include 'topnavbar.php';
			?>
		    <form class="form-inline" method="POST">
			    <div class="searcharea2" id="searcharea2" style="width: 100%; align-content: center; margin: 0 auto;" >
					
					<div style="text-align: center;" >
						<label class="headerfont">Справочная служба "Аптека Вита"</label>
			            <div style="text-align: center;">
							<div1 style='text-align: center;'>
			                    <label>Поиск по: </label>
			                </div1>
			                <?php
			                $i = 0;
			                foreach ($searching_type as $k => $v) {
			                    echo "<div class='radio-inline' style='text-align: center;'><input type='radio' name='searching_type' id='$v' value='$k'";
			                    if (isset($_POST['searching_type']) && ($_POST['searching_type'] == $k)) {
			                        echo 'checked';
			                    }
			                    if (!isset($_POST['searching_type']) && $i == 0) {
			                        echo 'checked';
			                    }
			                    echo "><label style='cursor: pointer; font-weight: normal;' for='$v'>$v</label></div>";
			                    $i++;
			                }
			                ?>
			            </div>
			        </div>
			        <div style='text-align: center;'>
			            <div1 style='text-align: center;'>
			                <label>Город: </label>
			            </div1>
			            <div1 style='text-align: center; '>
			                <?php
			                $visibleCities = 0;
			                $usercity = 0;
			                // Если пользователь не в офисе, по умолчанию выбираем первый город в списке
			                if (isset($cityuser[1])) {
			                    $cities_check = $cityuser[1];
			                    $usercity = $cityuser[1];
			                    $q = "SELECT `visiblecities` FROM " . CITIESTABLE . " WHERE `idcity` = $usercity";
			                    $quer = mysqli_query($link, $q);
			                    $res = mysqli_fetch_row($quer);
			                    $visibleCities = explode(',', $res[0]);
			                    $visibleCities[] = $cities_check;
			                    // Проверяем, была ли отправлена форма
			                    if (isset($_POST['searching_city'])) {
			                        $cities_check = $_POST['searching_city'];
			                    }
			                } else if (isset($_POST['searching_city'])) {
			                    $cities_check = $_POST['searching_city'];
			                }
			                // Если пользователь в офисе, отображаем все города
			                if ($usercity == 0) {
			                    foreach ($cities as $k => $v) {
			                        echo "<div style='text-align: center;' class='radio-inline'>";
			                        echo "<input type='radio' name='searching_city' id='$k' value='$k'";
			                        if ($cities_check == $k) {
			                            echo 'checked';
			                        }
			                        echo "><label style='cursor: pointer; font-weight: normal;' for='$k'>";
			                        echo $v['namecity'] . "</label></div>";
			                    }
			                }
			                // Если пользователь не в офисе, отображаем только города из $visibleCities
			                else {
			                    foreach ($cities as $k => $v) {
			                        if ($k == 0 || in_array($k, $visibleCities)) {
			                            echo "<div style='text-align: center;' class='radio-inline'>";
			                            echo "<input type='radio'  name='searching_city' id='$k' value='$k'";
			                            if ($cities_check == $k) {
			                                echo 'checked';
			                            }
			                            echo "><label style='cursor: pointer; font-weight: normal;'  for='$k'>";
			                            echo $v['namecity'] . "</label></div>";
			                        }
			                    }
			                }
			                ?>
			            </div1>
			        </div>
					<div style='text-align: center;' id="searching_district"> 	
						<?php 
						if (isset($_POST['searching_district'])) {
							$district_check = $_POST['searching_district'];
						}
						?>
					</div>
			        <div style='text-align: center;'>
						<div1 style='text-align: center; '>
			                <label>Бренд: </label>
			            </div1>
			            <div1 style='text-align: center; '>
			                <?php
			                $j = 0;
			                foreach ($seller as $k => $v) {
								$n = $v['name'];
			                    echo "<div style='text-align: center;' class='radio-inline'>";
			                    echo "<input type='radio' name='seller' id='$n' value='$k'";
			                    if (isset($_POST['seller']) && ($_POST['seller'] == $k)) {
			                        echo 'checked';
			                    }
			                    if (!isset($_POST['seller']) && $j == 0) {
			                        echo 'checked';
			                    }
			                    echo "><label style='cursor: pointer; font-weight: normal;' for='$n'>";
			                    echo $n . "</label></div>";
			                    $j++;
			                }
			                ?>
			            </div1>
			            <div style=" text-align: center">
			                <div1 class="input-group" style="width: 45%; text-align: center">
			                    <input style="margin-top: 20px; " type="text" class="form-control" name="search"
			                        placeholder="Строка поиска"
			                        value="<?php if (isset($_POST['search'])) { echo($_POST['search']); } else { echo ""; } ?>">
			                </div1>
			                <div1>
			                    <button style="margin-top: 20px;" type="submit" name="submit" id="submit" class="btn btn-primary">Поиск</button>
			                </div1>
			            </div>
			        </div>
			    </div>
			</form>
		</td>
	</tr>
</table>




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
	if(isset($_POST["searching_district"])){
		search($key, $_POST["searching_type"], $stock, $_POST["searching_city"], $_POST["searching_district"]);
		
	}else{
		search($key, $_POST["searching_type"], $stock, $_POST["searching_city"], 0);
	}
} else {
		echo "<div style='border: medium'>";
		echo "<strong>Минимальная строка запроса - 3 символа</strong>";
		echo '</div>';
}
}
// SQL-запрос для получения записей, сортированных по важности и дате
$sql_posts = "SELECT * 
FROM posts where is_public = 1
ORDER BY 
	CASE 
		WHEN dateimportance >= CURRENT_DATE THEN 0 
		ELSE 1 
	END,
	CASE 
		WHEN dateimportance >= CURRENT_DATE THEN dateimportance 
	END ASC,
	CASE 
		WHEN dateimportance < CURRENT_DATE THEN date_created 
	END DESC";

$result_posts = $link->query($sql_posts); // Выполнение запроса

// SQL-запрос для получения последних новостей
$sql_latest_posts = "SELECT * FROM posts ORDER BY date_created DESC";
$result_latest_posts = $link->query($sql_latest_posts); // Выполнение запроса

// Проверка, является ли пользователь администратором
$isadmin = 0;
if(isset($_SESSION['login'])){
	if($_SESSION['login'] == 'admin'){
		$isadmin = 1;
	}
}
?>
<div class="container" style='display:block' id='newsdiv'>
<h1 class="text-center"><a href="news.php" style="outline: none;"> Последние новости</a></h1>
<?php
$i = 0;
$current_user_id = finduser(3); // Получение текущего ID пользователя

// Сначала собираем все опросы
$survey_map = []; // post_id => survey_id
$survey_query = "SELECT id, post_id FROM surveys";
$survey_result = $link->query($survey_query);
if ($survey_result && $survey_result->num_rows > 0) {
    while ($row = $survey_result->fetch_assoc()) {
        $survey_map[$row['post_id']] = $row['id'];
    }
}

// Потом загружаем все ответы пользователя
$user_answers = []; // survey_id => true
$user_answer_query = "SELECT survey_id FROM user_answers WHERE user = $current_user_id";
$user_answer_result = $link->query($user_answer_query);
if ($user_answer_result && $user_answer_result->num_rows > 0) {
    while ($row = $user_answer_result->fetch_assoc()) {
        $user_answers[$row['survey_id']] = true;
    }
}

if ($result_posts && $result_posts->num_rows > 0) {
    while ($post = $result_posts->fetch_assoc()) {
        $isanswered = false; // Флаг ответа
        $isviewed = false;   // Флаг просмотра
        $enable = false;     // Флаг видимости поста

        $selected_pharmacies = explode(',', $post['visibility']); // Список видимых аптек

        foreach ($selected_pharmacies as $pharmacy) {
            if ($current_user_id == $pharmacy || $current_user_id == 100000 || $current_user_id == 200000 || $pharmacy == 0) {
                $enable = true;
                break;
            }
        }

        if ((($enable && $post['is_public'] == 1) || $isadmin == 1) && $i < 5) {
            $i++;

            $post_id = $post['id'];

            // Получение комментариев
            $sql_comments = "SELECT * FROM comments WHERE post_id = $post_id AND is_public = 1";
            $result_comments = $link->query($sql_comments);

            // Получение логов (просмотров)
            $sql_logs = "SELECT * FROM logs WHERE postid = $post_id";
            $result_logs = $link->query($sql_logs);

            $has_comment = false;
            $has_answer = false;

            // Проверка комментариев
            if ($result_comments && $result_comments->num_rows > 0) {
                while ($comment = $result_comments->fetch_assoc()) {
                    $user_id_to_check = $comment['user_id'];
                    if (($user_id_to_check == $current_user_id && !isset($_SESSION['user_id'])) || (isset($_SESSION['user_id'])) || ($current_user_id == 100000)) {
                        $has_comment = true;
                        break;
                    }
                }
            }

            // Проверка ответа на опрос
            if ($post['post_type'] == '2') {
                if (isset($survey_map[$post_id])) {
                    $survey_id = $survey_map[$post_id];
                    if (isset($user_answers[$survey_id])) {
                        $has_answer = true;
                    }
                }
            }

            // Итоговая установка флага ответа
            if ($has_comment || $has_answer || (isset($_SESSION['user_id'])) || ($current_user_id == 100000)) {
                $isanswered = true;
            }

            // Проверка просмотров
            if ($result_logs && $result_logs->num_rows > 0) {
                while ($log = $result_logs->fetch_assoc()) {
                    if ($log['user'] == findip()) {
                        $isviewed = true;
                        break;
                    }
                }
            } elseif ((isset($_SESSION['user_id'])) || ($current_user_id == 100000)) {
                $isviewed = true;
            }
?>
			<div class="panel panel-default">
				<div class="panel-heading <?php if (($post['post_type'] == '2'  && !$isanswered) || (($post['importance'] == 'important'||  $post['dateimportance'] >= date("Y-m-d")) && !$isviewed)) echo ' blinking'; ?>">
					<?php if (isset($_SESSION["user_id"]) && $post['is_public'] == 1) { ?>
						<div class="btn-group pull-right" style="margin-top:-6px">
							<a href="deletenews.php?id=<?php echo $post_id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены, что хотите удалить эту запись?');">Удалить</a>
						</div>
						
					<?php } 
					if($post['is_public'] == 0 && $isadmin == 1){
						$postid = $post['id'];
						echo "<div class='btn-group pull-right' style='margin-top:-6px'>";
						echo "<a href='retnews.php?id=$postid' class='btn btn-warning btn-sm' onclick='return confirmReturn();'>Вернуть новость</a>";
						echo "</div>";
					}
					?>
					<?php 
						// Отображение имени редактора, если запись была отредактирована
						if ($post['autor'] != $post['lasteditor']) {
							echo "<div class='btn-group pull-right' style='margin-top:-3px; margin-right: 5px'>Отредактирован:";
							$sql_user = "SELECT name FROM users WHERE id = " . $post['lasteditor']; 
							$result_user = $link->query($sql_user);
							if ($result_user && $result_user->num_rows > 0) {
								$user = $result_user->fetch_assoc();
								echo htmlspecialchars($user['name']);
							}
							echo "</div>";
						}
					?>
					<div class="btn-group pull-right" style="margin-top:-3px; margin-right: 5px">Автор: <?php
						$sql_user = "SELECT name FROM users WHERE id = " . $post['autor']; 
						$result_user = $link->query($sql_user);
						if ($result_user && $result_user->num_rows > 0) {
							$user = $result_user->fetch_assoc();
							echo htmlspecialchars($user['name']);
						}
					?></div>
					<h3 class="panel-title">
						<a href='post.php?id=<?php echo $post_id; ?>'><b><?php if ($post['dateimportance'] >= date("Y-m-d")) echo 'Актуально '; if ($post['post_type'] == '2') echo 'Опрос '; if ($post['importance'] == 'important') echo 'Важный ';?></b><?php echo htmlspecialchars($post['title']); ?></a>
					</h3>
				</div>
				<div class="panel-body">
					<div ><a style="word-wrap: break-word; word-break: break-word;" href='post.php?id=<?php echo $post_id; ?>'><?php echo htmlspecialchars_decode(stripslashes($post['short_description'])); ?></a></div>
					
					<div class='btn-group pull-right'>
						<div>Дата записи: <?php echo $post['date_created']; ?></div>
						<?php 
						if ($post['date_created'] != $post['date_updated']) {
							echo "<div>Отредактирован в: " . htmlspecialchars($post['date_updated']) . "</div>";
						}
						if ($post['dateimportance'] >= date("Y-m-d")) {
							echo "<div>Актуально до: " . htmlspecialchars($post['dateimportance']) . "</div>";
						}
						?>
					</div>
				</div>
			</div>
<?php
		}
	}
} else {
?>
	<p>Нет записей</p>
<?php
}

?>
</body>
</html>
<script>
$(document).ready(function() {
	 // Находим кнопку по её ID
	 const table1 = document.getElementById('Farma_wrapper');
	 const table2 = document.getElementById('Diller_wrapper');
	 const newsdiv = document.getElementById('newsdiv');
	 if(table1 ||  table2){
		newsdiv.style.display = 'none';
	 }else{
		newsdiv.style.display = 'block';
	 }
    var district_check = '<?php echo $district_check ?>';
    // Выбираем первый город из списка
    var firstCity = $('input[name="searching_city"]:checked').val();

    // Если есть выбранный город, загружаем районы для него
    if (firstCity) {
        $.ajax({
            url: 'get_districts_index.php', // Путь к файлу, который будет возвращать список районов
            type: 'POST',
            data: {cityId: firstCity, districtCheck: district_check}, // Передаем district_check
            success: function(response) {
                $('#searching_district').html(response);
            }
        });
    }

    // Обработчик события на изменение выбранного города
    $(document).on('change', 'input[name="searching_city"]', function() {
		district_check = 0;
        var cityId = $(this).val();
        $.ajax({
            url: 'get_districts_index.php',
            type: 'POST',
            data: {cityId: cityId, districtCheck: district_check}, // Передаем district_check
            success: function(response) {
                $('#searching_district').html(response);
            }
        });
    });

    // Обработчик события на изменение выбранного района
    $(document).on('change', 'input[name="searching_district"]', function() {
        var districtId = $(this).val();
        district_check = $(districtId).is(':checked') ? 1 : 0; // Обновляем district_check
        localStorage.setItem('district_check', district_check); // Сохраняем district_check в localStorage
        $.ajax({
            url: 'get_districts_index.php',
            type: 'POST',
            data: {cityId: firstCity, districtCheck: district_check}, // Передаем district_check
            success: function(response) {
                // Обновляем содержимое, если необходимо
            }
        });
    });
});
</script>