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

$db="192.168.0.103:f:/base/vita.fdb";
		$connect = @ibase_connect($db, FDBUSER,FDBPASSWORD);
// 		var_dump($connect);
		if($connect !== FALSE) 
		{
			$q="Select * from articuls where itemtype<1 and id>0 and id<>126512";
			echo $q;
			$run_q=ibase_query($connect, $q);
			
			//перевод в cp1251
			//$data = mb_convert_encoding($data,"Windows-1251", "UTF-8");
			//$who = mb_convert_encoding($who,"windows-1251","UTF-8");
			
			//$who = iconv("UTF-8", "windows-1251//TRANSLIT", $who);
			//echo $data;
 			echo ibase_num_fields($run_q);
			if(ibase_num_fields($run_q) > 0) 
			{
				echo "1111111";
				$sqlt="truncate `gbcatalog`";
				$quert = mysqli_query($connection, $sqlt);
				while ($row = ibase_fetch_object($run_q)) 
				{
					echo "2222";
					$id= $row->ID;
					$node= $row->NODE;
					$name=  htmlentities(iconv("windows-1251", "UTF-8//TRANSLIT", $row->FULLNAME));
					$sql_m="insert into gbcatalog (`id`,`node`,`name`) values ($id,$node,'$name')";
					echo "|".$sql_m."|<br>";
					$quer = mysqli_query($connection, $sql_m);
   					// $res = mysqli_fetch_row($quer);
				}
			}
		}
// 				


?>


