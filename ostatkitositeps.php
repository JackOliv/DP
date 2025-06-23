<?php
set_time_limit(2300);
ini_set('memory_limit', '-1');
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once "bd_connect1.php";

$sep="$";

$brand_apteka=2;  // первая социальная
$result_file="ostatkips.csv";

$conn = mysqli_connect(HOST, USER, PW, DB);

$date1=getdate(time());
$date2=$date1["year"].$date1["mon"].$date1["mday"];
$logfile=LOGPATH."catalog".$date2.".log";
$fd = fopen($logfile, 'a+');
$str_to_log=showtime(0,time())."Процедура запущена.\r\n";
fwrite($fd, $str_to_log);
mysqli_set_charset($conn, "utf8");
if (!$conn) {
    echo "Ошибка: Невозможно установить соединение с MySQL." . PHP_EOL;
    echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Текст ошибки error: " . mysqli_connect_error() . PHP_EOL;
    $str_to_log=showtime(0,time())."Ошибка подключения к базе.\r\n";
	fwrite($fd, $str_to_log);
    exit;
}
$str_to_log=showtime(0,time())."К базе подключенение прошло успешно.\r\n";fwrite($fd, $str_to_log);
$num_level=3; //количество уровней каталога
$sqlpartners="select itemcode, siteid from partners where type>0 and brand=$brand_apteka and load_to_site=1 order by siteid";
$resultpartners = mysqli_query($conn, $sqlpartners);
$num_rowspartners=mysqli_num_rows($resultpartners);  // количество аптек для выгрузки

$str_to_log=showtime(0,time())."Количество аптек для выгрузки на сайт: $num_rowspartners.\r\n";fwrite($fd, $str_to_log);
echo showtime(0,time())."Количество аптек для выгрузки на сайт: $num_rowspartners.<br>";
echo showtime(0,time())."разделитель: $sep.<br>";
echo showtime(0,time())."файл выгрузки: $result_file.<br>";
if($num_rowspartners>0)
{
	//echo "<table>";
	for($i=0;$i<$num_rowspartners;$i++)
	{
		$rowpartners = mysqli_fetch_array($resultpartners);
		$partners[$rowpartners["itemcode"]]=$rowpartners["siteid"];
		$partners1[$i]=$rowpartners["siteid"];
	}
}


$sqla = "select *  from `articuls` where 1 order by articul";
		// echo $sqla;
$resulta = mysqli_query($conn, $sqla);
$num_rowsa=mysqli_num_rows($resulta);
if($num_rowsa>0)
{

	for($ia=0;$ia<$num_rowsa;$ia++)
	{
		$rowa = mysqli_fetch_array($resulta);
		$articulssite[$rowa["articul"]]["sitename"]=str_replace("$","S",$rowa["sitename"]);  //убираем разделитель значений для выгрузки из названий товара
	//	echo $rowa["articul"]." ".$rowa["sitename"]."<br>";
	}
}


//$sql = "select *  from `pricelist` where partner_code in (select itemcode from partners where type>0 and load_to_site=1) ORDER BY partner_code, articul ASC";
$sql = "select * from `pricelist` where partner_code in (select itemcode from partners where type>0 and brand = $brand_apteka and load_to_site=1 and siteid<>1) ORDER BY partner_code,articul ASC";
// echo $sql;
$result = mysqli_query($conn, $sql);
$num_rows=mysqli_num_rows($result);
$str_to_log=showtime(0,time())."Количество строк с остатками по всем аптекам: $num_rows.\r\n";fwrite($fd, $str_to_log);
echo showtime(0,time())."Количество строк с остатками по всем аптекам: $num_rows.<br>";
if($num_rows>0)
{
	//echo "<table>";
	for($i=0;$i<$num_rows;$i++)
	{
		$row = mysqli_fetch_array($result);
		//КодКаталог1;ИмяКаталог1;КодКаталог2;ИмяКаталог2;КодКаталог2;ИмяКаталог3;КодТовара;ИмяТовара;Цена;КодСклада1;Остаток1;КодСклада2;Остаток2;КодСклада3;Остаток3;КодСклада4;Остаток4;КодСклада5;Остаток5;КодСклада6;Остаток6
	//	$str_to_log=showtime(0,time())."Ищем: ".$row["articul"]." с ноде: ".$row["node"].".\r\n";fwrite($fd, $str_to_log);
		if(!isset($mas[$row["node"]]))
		{
			//$str_to_log=showtime(0,time())."Ноде: ".$row["node"]."ищем.\r\n";fwrite($fd, $str_to_log);
			$nodecatalog=foundcatalog($row["node"]);
		}
		else
		{
			//$str_to_log=showtime(0,time())."Ноде: ".$row["node"]." найден ранее.\r\n";fwrite($fd, $str_to_log);
			$nodecatalog=$mas[$row["node"]];
		}
		for($n=0;$n<$num_level;$n++)
		{
			//echo "ноде=".$row['node']."н=$n";
			if(isset($nodecatalog[$n]))
			{
				$vigruz[$row["articul"]]["kodcatalog".$n]=$nodecatalog[$n]["id"];
				$vigruz[$row["articul"]]["namecatalog".$n]=$nodecatalog[$n]["name"];
				//$str_to_log=showtime(0,time())."Записываем  ".$nodecatalog[$n]["name"]." с ИД: ".$nodecatalog[$n]["id"].".\r\n";fwrite($fd, $str_to_log);
			}
			else
			{
				$str_to_log=showtime(0,time())."нет каталога уровень:$n.\r\n";fwrite($fd, $str_to_log);
				$n=100;
			}
		}	
		//$row["price"]=preg_replace("[\.]","[\,]",$row["price"]);
		//$row["price"]=(int)$row["price"];
		$vigruz[$row["articul"]]["name"]=str_replace("$","S",$row["name"]);
		
		if(isset($vigruz[$row["articul"]]["maxprice"]))
		$maxprice=$vigruz[$row["articul"]]["maxprice"];
		else $maxprice=0;
		
		if(isset($vigruz[$row["articul"]]["jvnl"]))
		$jvnl=$vigruz[$row["articul"]]["jvnl"];
		else $vigruz[$row["articul"]]["jvnl"]=0;
		
		if(isset($vigruz[$row["articul"]]["recept"]))
		$recept=$vigruz[$row["articul"]]["recept"];
		else $vigruz[$row["articul"]]["recept"]=0;
		
		if(isset($vigruz[$row["articul"]]["priceblock"]))
		$priceblock=$vigruz[$row["articul"]]["priceblock"];
		else $vigruz[$row["articul"]]["priceblock"]=0;
		
		$vigruz[$row["articul"]]["commercial"]="";
		if($row["COMMERCIAL"]=="AZ")$vigruz[$row["articul"]]["commercial"]="CP_".$row["COMMERCIAL"];
		if($row["COMMERCIAL"]=="EL")$vigruz[$row["articul"]]["commercial"]="CP_".$row["COMMERCIAL"];
		//if($row["COMMERCIAL"]=="PF")$vigruz[$row["articul"]]["commercial"]="CP_".$row["COMMERCIAL"];
		
		
		if(!isset($vigruz[$row["articul"]]["sumcount"]))$vigruz[$row["articul"]]["sumcount"]=0;
		$vigruz[$row["articul"]]["sumcount"]+=$row["count"];
		if(!isset($vigruz[$row["articul"]]["numcount"]))$vigruz[$row["articul"]]["numcount"]=0;
		$vigruz[$row["articul"]]["numcount"]+=1;
		$maxprice1=$maxprice;
		$price=$row["price"];
		$price1=$price*1;
		
		
		
		//echo "<br>articul=".$row["articul"].";maxprice=$maxprice;price=$price<br>";
		if($maxprice1<$price1)
		{
			$maxprice1=$price1;
		}
		$vigruz[$row["articul"]]["maxprice"]=$maxprice1;
		$vigruz[$row["articul"]]["jvnl"]=
		max($vigruz[$row["articul"]]["jvnl"],
		$row["JVNL"]);
		$vigruz[$row["articul"]]["recept"]=max($vigruz[$row["articul"]]["recept"],$row["RECEPT"]);
		$vigruz[$row["articul"]]["priceblock"]=max($vigruz[$row["articul"]]["priceblock"],$row["PRICEBLOCK"]);
		//$vigruz[$row["articul"]]["maxprice"]=$row["maxprice"];
		$vigruz[$row["articul"]]["kodsklada".$partners[$row["partner_code"]]]=$partners[$row["partner_code"]];
				
		if(!isset($vigruz[$row["articul"]]["ostatok".$partners[$row["partner_code"]]]))$vigruz[$row["articul"]]["ostatok".$partners[$row["partner_code"]]]=0;
		$vigruz[$row["articul"]]["ostatok".$partners[$row["partner_code"]]]+=$row["count"]; //суммируем остатки по разным партиям одного товара
		
		if(!isset($vigruz[$row["articul"]]["price".$partners[$row["partner_code"]]]))$vigruz[$row["articul"]]["price".$partners[$row["partner_code"]]]=0;
		if($vigruz[$row["articul"]]["price".$partners[$row["partner_code"]]]<$row["price"])$vigruz[$row["articul"]]["price".$partners[$row["partner_code"]]]=$row["price"]; //выбираем максимальную цену из партий товара
		$articuls[]=$row["articul"];
		
		//echo "$i.".$row["articul"]."<br>";
		//$str_to_log=showtime(0,time())."строка записана: ".$row["articul"].".\r\n";fwrite($fd, $str_to_log);
		
	}
		//	echo "</table>";
}
$articuls_unik=array_unique($articuls);
//print_r($articuls_unik);
$num_articuls=count($vigruz)-1;
//echo $num_articuls;
$str_to_log=showtime(0,time())."формируем строку вывода в файл остаков.\r\n";fwrite($fd, $str_to_log);
//$str=time()."\n\r";
$str="";
echo showtime(0,time())."Количество уникальных строк товара по всем аптекам: $num_articuls.<br>";
for($i=0;$i<$num_articuls;$i++)
{
	$vigruzat=1;
	if(isset($articuls_unik[$i]))
	{
			for($j=0;$j<$num_level;$j++)
			{
				$j1=$j+1;
				//echo "<br>".$articuls_unik[$i]."||$i<br>";
			//	echo "<br>vigruzat=$vigruzat ".$articuls_unik[$i]."||$i<br>";
				//echo "<br>".$vigruz[$articuls_unik[$i]]["kodcatalog".$j].";".$vigruz[$articuls_unik[$i]]["namecatalog".$j].";"."<br>";
				if($vigruzat==1)
				{
					if(isset($vigruz[$articuls_unik[$i]]["kodcatalog".$j]))
					{
					//	echo "<br>vigruzat=$vigruzat ".$vigruz[$articuls_unik[$i]]["kodcatalog".$j]."||$i<br>";
						if(($j==0)&&(($vigruz[$articuls_unik[$i]]["kodcatalog".$j]==147353)||($vigruz[$articuls_unik[$i]]["kodcatalog".$j]==129182)||($vigruz[$articuls_unik[$i]]["kodcatalog".$j]==141079)))
						{
							$vigruzat=0;
						}
						else
						{
							$str.= $vigruz[$articuls_unik[$i]]["kodcatalog".$j].$sep.html_entity_decode($vigruz[$articuls_unik[$i]]["namecatalog".$j]).$sep;
						}
					//	echo "<br>vigruzat=$vigruzat ".$vigruz[$articuls_unik[$i]]["kodcatalog".$j]."||$i<br>";
					}
					else
					{
						if($j==0)$vigruzat=0;
						else {if($vigruzat==1)$str.=$sep.$sep;}
					}
				}
			//	echo "<br>vigruzat=$vigruzat ||$i<br>";
				
			}
		if($vigruzat==1)
		{
			$vigruz[$articuls_unik[$i]]["maxprice"]=preg_replace("[\,]","[\.]",$vigruz[$articuls_unik[$i]]["maxprice"]);
			$str.=$articuls_unik[$i].$sep;
			if(isset($articulssite[$articuls_unik[$i]]["sitename"]))
			{
				//echo "+++++++++++++".$articulssite[$articuls_unik[$i]]["sitename"]."<BR>";;
				
				//$str.=html_entity_decode($vigruz[$articuls_unik[$i]]["name"]).";";
				$str.=html_entity_decode($articulssite[$articuls_unik[$i]]["sitename"]);
			}
			else
			{
				//echo "------------------------------".$vigruz[$articuls_unik[$i]]["name"]."<BR>";;
				$str.=html_entity_decode($vigruz[$articuls_unik[$i]]["name"]);
			}
			
			
			
			$str.=$sep.$vigruz[$articuls_unik[$i]]["maxprice"].$sep."RUB".$sep.$vigruz[$articuls_unik[$i]]["maxprice"].$sep.$vigruz[$articuls_unik[$i]]["sumcount"].$sep.$vigruz[$articuls_unik[$i]]["jvnl"].$sep.$vigruz[$articuls_unik[$i]]["priceblock"].$sep.$vigruz[$articuls_unik[$i]]["commercial"].$sep;
			
			
			//";;;";
			for($j=0;$j<$num_rowspartners;$j++)
			{
				if(isset($vigruz[$articuls_unik[$i]]["kodsklada".$partners1[$j]]))
				{
					$str.=$vigruz[$articuls_unik[$i]]["kodsklada".$partners1[$j]].$sep.$vigruz[$articuls_unik[$i]]["ostatok".$partners1[$j]].$sep.$vigruz[$articuls_unik[$i]]["price".$partners1[$j]];
				}
				else
				{
					$str.=$partners1[$j].$sep."0".$sep."0";
				}
				if($j<($num_rowspartners-1))$str.=$sep;
			}
			
			$str .= "\r\n";
		}
		
	}
	
	else
	{
		$num_articuls++;
	}
	$vigruzat=1;
}

//$str=iconv("UTF-8", "windows-1251", "КодКаталог1;ИмяКаталог1;КодКаталог2;ИмяКаталог2;КодКаталог2;ИмяКаталог3;КодТовара;ИмяТовара;Цена;КодСклада1;Остаток1;КодСклада2;Остаток2;КодСклада3;Остаток3;КодСклада4;Остаток4;КодСклада5;Остаток5;КодСклада6;Остаток6\n\r").$str;
//$str1 = iconv("windows-1251", "UTF-8//IGNORE", $str);
//$str2="КодКаталог1;ИмяКаталог1;КодКаталог2;ИмяКаталог2;КодКаталог2;ИмяКаталог3;КодТовара;ИмяТовара;Цена;МинЦена;Количество;";
$str2="IC_XML_ID0".$sep."IC_GROUP0".$sep."IC_XML_ID1".$sep."IC_GROUP1".$sep."IC_XML_ID2".$sep."IC_GROUP2".$sep."IE_XML_ID".$sep."IE_NAME".$sep."CV_PRICE_1".$sep."CV_CURRENCY_1".$sep."IP_PROP87".$sep."CP_QUANTITY".$sep."IMPORTANT_FOR_LIVE".$sep."BLOCK_PRICE".$sep."COM_PROJEKT".$sep;

for($i=0;$i<$num_rowspartners;$i++)
{
		$i1=$i+1;
	$str2.="КодСклада$i1".$sep."Остаток$i1".$sep."Цена$i1";
	if($i1<$num_rowspartners)$str2.=$sep;else $str2.="\r\n";
}
//КодСклада2;Остаток2;КодСклада3;Остаток3;КодСклада4;Остаток4;КодСклада5;Остаток5;КодСклада6;Остаток6\r\n".$str;
$str=$str2.$str;
//$str1 = iconv("windows-1251", "UTF-8//IGNORE", $str);
//$str.=time()."\n\r";


$dump_name=OSTATKIPATH . $result_file;
$str_to_log=showtime(0,time())."формирование завершено.\r\n";fwrite($fd, $str_to_log);
unlink ($dump_name);
file_put_contents($dump_name, $str);
$str_to_log=showtime(0,time())."файл сохранен.\r\n";fwrite($fd, $str_to_log);
//echo $str;
echo showtime(0,time())."Выполнено";


function foundcatalog($id)
{
	global $conn;
	global $mas;
	$node=$id;
	$mas[$id][0]=0;
	while($node!=118801)
	{
		$sql="select * from gbcatalog where id=$node";
	//	echo $sql."<br>";
		$res = mysqli_query($conn, $sql);
		$num_rows=mysqli_num_rows($res);
		//echo "к добавлению по ноде=$node строк = $num_rows номер строки $i1 <br>";
		if($num_rows>0)
		{
			/*print_r($mas);
			echo "<hr>";*/
			movemas($id);
			for($k=0;$k<$num_rows;$k++)
			{
				$row=mysqli_fetch_array($res);
				$mas[$id][0]=$row;
				$node=$row["node"];
			}
		/*	print_r($mas);
			echo "<hr>";*/
		}
		else
		{
			$mas[$id][0]=0;
			$node=118801;
		}
	}
	return $mas[$id];
}


function movemas($id)
{
	global $mas;
	if(isset($mas[$id]))
	$count=count($mas[$id]);
	else $count=0;
	for($k=$count;$k>0;$k--)
	{
		$k1=$k-1;
		$mas[$id][$k]=$mas[$id][$k1];
	}
}

function showtime($type,$time)
{
	$date1=getdate($time);
	if($type==0)
	{
		$str=$date1["year"]."-".$date1["mon"]."-".$date1["mday"]." ".$date1["hours"].":".$date1["minutes"].":".$date1["seconds"]." ".$date1["weekday"]." ";
	}
	return $str;
}


?>


