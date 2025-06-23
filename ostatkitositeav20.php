<?php
set_time_limit(30);
ini_set('memory_limit', '-1');
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once "bd_connect1.php";


$write_log=1;								//писать лог 1-да; 0 -нет
$write_comment=1;							//выводить коменты на странице выгрузки /1-да; 0-нет
$write_comment_podrobno=0;					//1-подробные коменты;0-основные коменты


$sep="$";									//разделитель в файле выгрузки для сайта
$sepchange="S";								//символ на который заменем резделитель в названии товара
$num_level=3; 								//количество подуровней каталога для выгрузки на сайт
$bestbefore=30;								//остаток срока годности для выгрузки товара на сайт в днях]

$av=1;								//брэнд аптека вита
$ps=2;								//брэнд первая социальная аптека


$alldata["brand"][$av]["num"]=1;								// номер бренда 1=АПТЕКА ВИТА
$alldata["brand"][$ps]["num"]=2;								// номер бренда 2=ПЕРВАЯ СОЦИАЛЬНАЯ АПТЕКА
$alldata["brand"][$av]["name"]="Аптека Вита";					// название бренда 1=АПТЕКА ВИТА
$alldata["brand"][$ps]["name"]="Первая Социальная Аптека";		// название бренда 2=ПЕРВАЯ СОЦИАЛЬНАЯ АПТЕКА
$alldata["procentvigruzki"][0][0]=1000;						// защита от выгрузки минимальный проент наценки на товар БЕЗ БРЕНДА при котором партия выгружается на сайт для ЖВНЛ
$alldata["procentvigruzki"][0][1]=1000;						// защита от выгрузки минимальный проент наценки на товар БЕЗ БРЕНДА при котором партия выгружается на сайт для НЕ ЖВНЛ
$alldata["procentvigruzki"][$av][1]=6.5;					//минимальный проент наценки на товар при котором партия выгружается на сайт АПТЕКА ВИТА для ЖВНЛ
$alldata["procentvigruzki"][$av][0]=14;						//минимальный проент наценки на товар при котором партия выгружается на сайт АПТЕКА ВИТА для НЕ ЖВНЛ
$alldata["procentvigruzki"][$ps][1]=3;						//минимальный проент наценки на товар при котором партия выгружается на сайт ПЕРВОЙ СОЦИАЛЬНОЙ для ЖВНЛ
$alldata["procentvigruzki"][$ps][0]=7;						//минимальный проент наценки на товар при котором партия выгружается на сайт ПЕРВОЙ СОЦИАЛЬНОЙ для НЕ ЖВНЛ
$alldata["result_file"][$av]="ostatkiavnacenka.csv";		//имя файла для выгрузки
$alldata["result_file"][$ps]="ostatkipsnacenka.csv";		//имя файла для выгрузки



//$brand_apteka=1;  // аптека вита
//$result_file="ostatkiavnacenka.csv";


$conn = mysqli_connect(HOST, USER, PW, DB);

$date1=getdate(time());
$date2=$date1["year"].$date1["mon"].$date1["mday"];

$logfile=LOGPATH."catalog".$date2.".log";

if($write_log)$fd = fopen($logfile, 'a+');
write_log($write_log,"Процедура запущена.",$fd);

mysqli_set_charset($conn, "utf8");
if (!$conn) {
    echo "Ошибка: Невозможно установить соединение с MySQL." . PHP_EOL;
    echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Текст ошибки error: " . mysqli_connect_error() . PHP_EOL;
    write_log($write_log,"Ошибка подключения к базе.",$fd);
    exit;
}

write_log($write_log,"К базе подключенение прошло успешно.",$fd);

for($br=1;$br<3;$br++)
{
	
	unset($vigruz);
	unset($mas);
	unset($partners);
	unset($partners1);
	unset($nodecatalog);
	unset($articuls);
	$brand=$alldata["brand"][$br]["num"];							//текущий бренд	

	$sqlpartners="select itemcode, siteid from partners where type>0 and brand=$brand and load_to_site=1 order by siteid";
	$resultpartners = mysqli_query($conn, $sqlpartners);
	$num_rowspartners=mysqli_num_rows($resultpartners);  // количество аптек для выгрузки


	write_log($write_log,"Количество аптек для выгрузки на сайт: $num_rowspartners.",$fd);
	write_comment(0,"Количество аптек для выгрузки на сайт: $num_rowspartners.");
	write_comment(0,"разделитель: $sep. заменитель разделителя: $sepchange.");
	write_comment(0,"файл выгрузки: ".$alldata["result_file"][$brand]);

	if($num_rowspartners>0)
	{
		for($i=0;$i<$num_rowspartners;$i++)
		{
			$rowpartners = mysqli_fetch_array($resultpartners);
			$partners[$rowpartners["itemcode"]]=$rowpartners["siteid"];
			$partners1[$i]=$rowpartners["siteid"];
		}
	}



	//запрос в таблицу за данными для выгрузки по бренду, доступные для выгрузки на сайт и имеющие номер для сайта
	$sql = "select * from `pricelist` where partner_code in (select itemcode from partners where type>0 and brand = $brand and load_to_site=1 and siteid>1) ORDER BY partner_code,articul ASC";
	$result = mysqli_query($conn, $sql);
	$num_rows=mysqli_num_rows($result);
	write_log($write_log,"Количество строк с остатками по всем аптекам: $num_rows.",$fd);
	write_comment(0,"Количество строк с остатками по всем аптекам: $num_rows.");
	$colnacenka=0;									//количество партий не выгруженных из-за низкой наценки
	$colsrok=0;										//количество партий не выгруженных из-за срока годности
	if($num_rows>0)
	{
		//$num_rows=100;
		for($i=0;$i<$num_rows;$i++)
		{
			$loadtosite=1;									//флаг выгрузки конкретной партии в файл выгрузки на сайт, по умолчанию да
			$ignorenacenka=0;								//флаг учета величины наценки активна, по умолчанию не учитывается. 
			$isjvnl=0;										//является ли товар ЖВНЛ по умолчанию нет
			$row = mysqli_fetch_array($result);


			//----------------------------------------------блок определения необходимости выгрузки товара на сайт.наценка
			
			//write_comment($write_comment,"<hr>");
			if(isset($row["PRICEBLOCK"]))$ignorenacenka=$row["PRICEBLOCK"];									//если установлен блок скидки то зависимость от величины наценки отключается
			if((isset($row["COMMERCIAL"]))&&($row["COMMERCIAL"]<>0))$ignorenacenka=1;						//для коммерческих товаров зависимость от величины наценки так же игнорируется
			if(isset($row["JVNL"])){$isjvnl=$row["JVNL"];}
			
			$predelnacenki=1.00;
			$distprice=$row["distprice"]*1.00;
			if($distprice>0)
			{
				$nacenka=$row["nacenka"]*1.00;
				$predelnacenki=$nacenka-$alldata["procentvigruzki"][$brand][$isjvnl];
			}
			if(($ignorenacenka==0)&&($predelnacenki<0))
			{
				write_log($write_log,$row["partner_code"]." ".$row["articul"]." ".$row["name"]." ".$row["price"]." ".$row["distprice"]." ".$row["nacenka"]." "."Низкая наценка партия не должна включаться в выгрузку на сайт!",$fd);
				write_comment(1,$row["partner_code"]." ".$row["articul"]." ".$row["name"]." ".$row["price"]." ".$row["distprice"]." ".$row["nacenka"]." "."Низкая наценка партия не должна включаться в выгрузку на сайт!");
				$loadtosite=0;								//флаг выгрузки конкретной партии в файл выгрузки на сайт, устанавливаем НЕТ
				$colnacenka++;
			}
			//! конец    ------------   блок определения необходимости выгрузки товара на сайт.
			
			//----------------------------------------------блок определения необходимости выгрузки товара на сайт.срок годности
			
			//write_comment($write_comment,"<hr>");
			//if($loadtosite)
			{
				
			
				if(isset($row["godendo"])) $godendo=$row["godendo"];									//срок годности партии
			
				
				$dt=explode("-",$godendo);
				//print_r($dt);
				$intgodendo=mktime(23,59,59,$dt[1],$dt[2],$dt[0]);
				$diftime=$bestbefore*24*60*60;
				$dtime=$intgodendo-$diftime-time();
				if(($dtime<0)&&($intgodendo!=61199))
				{
					write_log($write_log,$row["partner_code"]." ".$row["articul"]." ".$row["name"]." ".$row["price"]." ".$row["distprice"]." ".$row["nacenka"]." "."Срок годности менее $bestbefore дней. Партия не должна включаться в выгрузку на сайт!",$fd);
					write_comment(1,$dtime." ".$intgodendo." ".$godendo." ".$row["partner_code"]." ".$row["articul"]." ".$row["name"]." ".$row["price"]." "."Срок годности менее $bestbefore дней. Партия не должна включаться в выгрузку на сайт!");
					$loadtosite=0;								//флаг выгрузки конкретной партии в файл выгрузки на сайт, устанавливаем НЕТ
					$colsrok++;
					//echo $godendo."<br>";
				}
			}
			//! конец    ------------   блок определения необходимости выгрузки товара на сайт.
			
			
			//--------------------------блок формирования данных по текущей строке
			if($loadtosite)
			{
				if(!isset($mas[$row["node"]])){$nodecatalog=foundcatalog($row["node"]);}
				else{$nodecatalog=$mas[$row["node"]];}
				
				for($n=0;$n<$num_level;$n++)
				{
					if(isset($nodecatalog[$n]))
					{
						$vigruz[$row["articul"]]["kodcatalog".$n]=$nodecatalog[$n]["id"];
						$vigruz[$row["articul"]]["namecatalog".$n]=$nodecatalog[$n]["name"];
					}
					else
					{
						$n=100;										//выходим из цикла
					}
				}	
				

				$vigruz[$row["articul"]]["name"]=str_replace($sep,$sepchange,$row["name"]);								//удалем разделители из названия товара
				

				
				if(isset($vigruz[$row["articul"]]["maxprice"]))
				{
					if($loadtosite)$maxprice=$vigruz[$row["articul"]]["maxprice"];
				}
				
				else $maxprice=0;

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
				if($loadtosite)$vigruz[$row["articul"]]["sumcount"]+=$row["count"];
				if(!isset($vigruz[$row["articul"]]["numcount"]))$vigruz[$row["articul"]]["numcount"]=0;
				if($loadtosite)$vigruz[$row["articul"]]["numcount"]+=1;
				if($loadtosite)$maxprice1=$maxprice;
				if($loadtosite)$price=$row["price"];
				if($loadtosite)$price1=$price*1;
				
				
				
				//echo "<br>articul=".$row["articul"].";maxprice=$maxprice;price=$price<br>";
				if($loadtosite){
					if($maxprice1<$price1)
					{
						$maxprice1=$price1;
					}
					$vigruz[$row["articul"]]["maxprice"]=$maxprice1;
				}
				
				if(!isset($vigruz[$row["articul"]]["jvnl"]))$vigruz[$row["articul"]]["jvnl"]=0;
				if($loadtosite)$vigruz[$row["articul"]]["jvnl"]=
				max($vigruz[$row["articul"]]["jvnl"],
				$row["JVNL"]);
				if($loadtosite)$vigruz[$row["articul"]]["recept"]=max($vigruz[$row["articul"]]["recept"],$row["RECEPT"]);
				if($loadtosite)$vigruz[$row["articul"]]["priceblock"]=max($vigruz[$row["articul"]]["priceblock"],$row["PRICEBLOCK"]);
				//$vigruz[$row["articul"]]["maxprice"]=$row["maxprice"];
				if($loadtosite)$vigruz[$row["articul"]]["kodsklada".$partners[$row["partner_code"]]]=$partners[$row["partner_code"]];
				
				if(!isset($vigruz[$row["articul"]]["ostatok".$partners[$row["partner_code"]]]))$vigruz[$row["articul"]]["ostatok".$partners[$row["partner_code"]]]=0;
				if($loadtosite)$vigruz[$row["articul"]]["ostatok".$partners[$row["partner_code"]]]+=$row["count"]; //суммируем остатки по разным партиям одного товара
				
				if(!isset($vigruz[$row["articul"]]["price".$partners[$row["partner_code"]]]))$vigruz[$row["articul"]]["price".$partners[$row["partner_code"]]]=0;
				if($loadtosite)if($vigruz[$row["articul"]]["price".$partners[$row["partner_code"]]]<$row["price"])$vigruz[$row["articul"]]["price".$partners[$row["partner_code"]]]=$row["price"]; //выбираем максимальную цену из партий товара
				if($loadtosite)$articuls[]=$row["articul"];
			}
			//!конец--------------------------блок формирования данных по текущей строке
		}
		write_log($write_log,"Количество партий не выгруженных на сайт из-за низкой наценки: ".$colnacenka,$fd);
		write_comment(0,"Количество партий не выгруженных на сайт из-за низкой наценки: ".$colnacenka);
		write_log($write_log,"Количество партий не выгруженных на сайт из-за маленького срока госдности: ".$colsrok,$fd);
		write_comment(0,"Количество партий не выгруженных на сайт из-за маленького срока госдности: ".$colsrok);
	}
	unset($articuls_unik);
	$articuls_unik=array_unique($articuls);

	$num_articuls=count($vigruz)-1;

	write_log($write_log,"формируем строку вывода в файл остаков.",$fd);

	$str="";
	write_comment(0,"Количество уникальных строк товара по всем аптекам: $num_articuls.");
	for($i=0;$i<$num_articuls;$i++)
	{
		$vigruzat=1;
		if(isset($articuls_unik[$i]))
		{
			for($j=0;$j<$num_level;$j++)
			{
				$j1=$j+1;
				if($vigruzat==1)
				{
					if(isset($vigruz[$articuls_unik[$i]]["kodcatalog".$j]))
					{
						if(($j==0)&&(($vigruz[$articuls_unik[$i]]["kodcatalog".$j]==147353)||($vigruz[$articuls_unik[$i]]["kodcatalog".$j]==129182)||($vigruz[$articuls_unik[$i]]["kodcatalog".$j]==141079)))
						{
							$vigruzat=0;
						}
						else
						{
							$str.= $vigruz[$articuls_unik[$i]]["kodcatalog".$j].$sep.html_entity_decode($vigruz[$articuls_unik[$i]]["namecatalog".$j]).$sep;
						}
					}
					else
					{
						if($j==0)$vigruzat=0;
						else {if($vigruzat==1)$str.=$sep.$sep;}
					}
				}
			}
			if($vigruzat==1)
			{
				$vigruz[$articuls_unik[$i]]["maxprice"]=preg_replace("[\,]","[\.]",$vigruz[$articuls_unik[$i]]["maxprice"]);
				$str.=$articuls_unik[$i].$sep;
				if(isset($articulssite[$articuls_unik[$i]]["sitename"]))
				{
					$str.=html_entity_decode($articulssite[$articuls_unik[$i]]["sitename"]);
				}
				else
				{
					$str.=html_entity_decode($vigruz[$articuls_unik[$i]]["name"]);
				}
				$str.=$sep.$vigruz[$articuls_unik[$i]]["maxprice"].$sep."RUB".$sep.$vigruz[$articuls_unik[$i]]["maxprice"].$sep.$vigruz[$articuls_unik[$i]]["sumcount"].$sep.$vigruz[$articuls_unik[$i]]["jvnl"].$sep.$vigruz[$articuls_unik[$i]]["priceblock"].$sep.$vigruz[$articuls_unik[$i]]["commercial"].$sep;
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


	$dump_name=OSTATKIPATH . $alldata["result_file"][$brand];
	write_log($write_log,"формирование завершено.".$alldata["brand"][$brand]["name"],$fd);
	unlink ($dump_name);
	file_put_contents($dump_name, $str);
	write_log($write_log,"файл сохранен. ".$alldata["result_file"][$brand],$fd);
	write_comment(0,"Выполнено ".$alldata["result_file"][$brand]);
}

function write_log($type_log,$data_log,$fd)
{
	if($type_log)
	{
		$str_to_log=showtime(0,time()).$data_log."\r\n";
		fwrite($fd, $str_to_log);
	}
	
}

function write_comment($write_comment1,$comment)
{
	global $write_comment;
	global $write_comment_podrobno;
	if($write_comment)	if($write_comment_podrobno>=$write_comment1)echo $comment."<br>";
}


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


