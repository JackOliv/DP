<?php
define('PREFIX', '/var/www/spravka.aptekavita.ru/public_html/');
ini_set('display_errors',1);
error_reporting(E_ALL);
require_once PREFIX."bd_connect1.php";
function update($code,$who, $db, $type, $uchet)
    {
		/*if ($who=="Отсутствует"){
			$whocode=0;
			$whotype=0;
			$what="Partner";
		}elseif($who=="Отсутствует"){
			$what="Stock";
		}*/
		if($uchet=="fk")
		{
			$dump_name=CSVPATH . $code .".csv";
			refresh_db($dump_name, $code, $type, $uchet);
		}
		/*else
		{
			$connect = @ibase_connect($db, FDBUSER,FDBPASSWORD);
		//	echo "update";
		// 		var_dump($connect);
			if($connect !== FALSE) {
				if($who>0)	$q="Select * from P_2017_PriceStockForSpravka(".$code.")";
				else		$q="Select * from P_2017_PricePartnerForSpravka(".$code.")";
				//echo $q;
				$run_q=ibase_query($connect, $q);
				
				//перевод в cp1251
				//$data = mb_convert_encoding($data,"Windows-1251", "UTF-8");
				//$who = mb_convert_encoding($who,"windows-1251","UTF-8");
				
				//$who = iconv("UTF-8", "windows-1251//TRANSLIT", $who);
				//echo $data;
		//	echo ibase_num_fields($run_q);
				if(ibase_num_fields($run_q) > 0) {
					$data = '';
					while ($row = ibase_fetch_object($run_q)) {
						$data .= $row->ARTICUL . ";";
						$data .= "{$who};"; //Brand
					//	$data .= ";"; //Address
						/*if($type == 'Аптека') {
							$data .= "{$code};";
						} else {
							$data .= ";";
						}
						if($type == 'Поставщик') {
			// 			echo $type;
							$data .= "{$code};";
						} else {
							$data .= ";";
						}*/
			/*			$data .= "{$code};";
						$ARTICULNAME = str_replace("\"", "'", $row->ARTICULNAME);
						$ARTICULNAME = str_replace(";", ".", $ARTICULNAME);
						$data .= $ARTICULNAME . ";";
						//$data .= " ;"; //diller
						$PRODUCERNAME = str_replace("\"", "'", $row->PRODUCERNAME);
						$PRODUCERNAME = str_replace(";", ".", $PRODUCERNAME);
						$data .= $PRODUCERNAME . ";";
						$data .= $row->QUANTITY . ";";
						$data .= $row->PRICE . ";";
						$data .= $row->ABATEDATE.";";
						$data .= $row->ARTICULNODE.";";
						$data .= $row->BARCODE.";";
						$data .= $row->ZVNL.";";
						$data .= $row->COMMERCIAL.";";
						$data .= $row->PRICEBLOCK.";";
						$data .= $row->RECEPT;
						$data .= "\n\r";
					}
				//	echo $data;
					if(isset($data) && $data != '') {
					//	$data=iconv("UTF-8", "windows-1251", "Артикул;Бренд;Адрес;Название;Поставщик;Производитель;Количество;Цена;Срок годности\n\r").$data;
						$data=iconv("UTF-8", "windows-1251", "articul;brand;partner_code;name;madeby;count;price;godendo;node;barcode;jvnl;commercial;priceblock;recept\n\r").$data;
						$data1 = iconv("windows-1251", "UTF-8//TRANSLIT", $data);
						$dump_name=CSVPATH . $code .".csv";
						file_put_contents($dump_name, $data1);
						ibase_free_result($run_q);
						ibase_close($connect);
						refresh_db($dump_name, $code, $type, $uchet);
					} else {
						$conn = mysqli_connect(HOST, USER, PW, DB) or die(mysqli_error($conn));
						mysqli_set_charset($conn, "utf8");
						$q = "UPDATE `" . PARTNERSTABLE . "` SET last_status = 3 WHERE `itemcode` = $code;";
						mysqli_query($conn, $q);
						mysqli_close($conn);
						header("Location: /".ADMINPHP);
					}
				}
			} else {
				$conn = mysqli_connect(HOST, USER, PW, DB) or die(mysqli_error($conn));
				mysqli_set_charset($conn, "utf8");
				$q = "UPDATE `" . PARTNERSTABLE . "` SET last_status = 2 WHERE `itemcode` = $code;";
				mysqli_query($conn, $q);
				mysqli_close($conn);
				//header("Location: /".ADMINPHP);
			}
		}*/
	}
function refresh_db($remfilename, $code, $type, $uchet)
    {
		mysqli_report(MYSQLI_REPORT_ERROR);
		$conn = mysqli_connect(HOST, USER, PW, DB) or die(mysqli_error($conn));
		mysqli_set_charset($conn, "utf8");
		$q = "`partner_code` = {$code};";
		
		$query = "DELETE FROM `" . PRICETABLE . "` WHERE ".$q;
		if (!mysqli_query($conn, $query))
		{
			print_r(mysqli_error_list($conn));
			exit;
		}
		if($uchet=="fk")
		{
			$insquery = "LOAD DATA LOCAL INFILE '" . $remfilename . "' INTO TABLE `" . PRICETABLE . "` CHARACTER SET UTF8 FIELDS TERMINATED BY ';' ENCLOSED BY '' LINES TERMINATED BY '\r\n' IGNORE 1 LINES;";
		//	echo $insquery;
		//	die();
		}
	/*	else
		{
			$insquery = "LOAD DATA LOCAL INFILE '" . $remfilename . "' INTO TABLE `" . PRICETABLE . "` CHARACTER SET UTF8 FIELDS TERMINATED BY ';' ENCLOSED BY '' LINES TERMINATED BY '\n\r' IGNORE 1 LINES;";
		}*/
		if (!($smth=$conn->query($insquery))){
			echo "\nQuery execute failed: ERRNO: (" . $conn->errno . ") " . $conn->error;
		} else { // Если прошло успешно, обновляем дату загрузки прайса
			$q = "UPDATE `" . PARTNERSTABLE . "` SET uploaddate = CURDATE() WHERE `itemcode` = $code;";
			mysqli_query($conn, $q);
			$q = "UPDATE `" . PARTNERSTABLE . "` SET last_status = 1 WHERE `itemcode` = $code;";
			mysqli_query($conn, $q);
			header("Location: /".ADMINPHP);
		}
		mysqli_close($conn);
    }

$connect = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connect, "utf8");
$query = "SELECT `brand`, `itemcode`, `connectionstring`, `type`, `uchet` FROM `" . PARTNERSTABLE . "` WHERE `downloadprice` = 1;";
$results = mysqli_query($connect, $query);
while ($result = mysqli_fetch_object($results)) {
	update($result->itemcode, $result->brand, $result->connectionstring, $result->type, $result->uchet);
}
mysqli_close($connect);
?>