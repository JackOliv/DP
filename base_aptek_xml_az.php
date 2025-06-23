<?php 
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//include "dbmain.php";
require_once "bd_connect1.php";
$db = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($db, "CP1251");
$sql = "select * from partners where type=1 and num_term>0 order by brand, itemcode";
$result = mysqli_query($db, $sql);
$numrows = mysqli_num_rows($result);
if($numrows>0)
{
	$curdate=date("Ymd");
	$fnameaz=LOCALFTP."/astrazeneka/out/to_send/AZ_PL_691_".$curdate.".xml";
	$fnameel=LOCALFTP."/eli_lilly/out/to_send/VO_PL_691_".$curdate.".xml";
	$fdbc = fopen($fnameaz, 'w');
	$fdbcel = fopen($fnameel, 'w');
	$str_to_log="<?xml version=\"1.0\" encoding=\"windows-1251\" ?>\r\n";
	$str_to_log.="<PHARMACIES>\r\n";
	$str_to_log.="<CHAINCODE>691</CHAINCODE>\r\n";
	$str_to_log.="<COUNT>".$numrows."</COUNT>\r\n";
	fwrite($fdbc, $str_to_log);
	fwrite($fdbcel, $str_to_log);

	for($i=0;$i<$numrows;$i++)
	{
		$i1=$i+1;
		$row = mysqli_fetch_array($result);
		$str_to_log="<PHARMACY>\r\n";

		if($row["uchet"]=="fk")
		{
			$str_to_log.="<CODE>".$row["fk_kod"]."</CODE>\r\n";
		}
		else
		{
			$str_to_log.="<CODE>".$row["itemcode"]."</CODE>\r\n";	
		}
		
		if($row["firm"]==1)
		{

			$str_to_log.="<INN>7019027721</INN>\r\n";
			$str_to_log.="<LEGALENTITY>ООО \"Аптека Вита\"</LEGALENTITY>\r\n";
		}
		elseif($row["firm"]==2)
		{

			$str_to_log.="<INN>7017124864</INN>\r\n";
			$str_to_log.="<LEGALENTITY>ООО \"Чирчик\"</LEGALENTITY>\r\n";
		}
		elseif($row["firm"]==3)
		{
			$str_to_log.="<INN>7017073419</INN>\r\n";
			$str_to_log.="<LEGALENTITY>ООО \"Аптека 36,6\"</LEGALENTITY>\r\n";
		}
		elseif($row["firm"]==4)
		{

			$str_to_log.="<INN>7017154315</INN>\r\n";
			$str_to_log.="<LEGALENTITY>ООО \"АртЛана\"</LEGALENTITY>\r\n";
		}

		$str_to_log.="<NUM>$i1</NUM>\r\n";	
		$str_to_log.="<NAME>".$row["itemname"]."</NAME>\r\n";
		if($row["brand"]==1)
		{

			$str_to_log.="<BRAND>Аптека Вита</BRAND>\r\n";
		
		}
		elseif($row["brand"]==2)
		{

			$str_to_log.="<BRAND>Первая социальная аптека</BRAND>\r\n";
		
		}
		
		
		
		$str_to_log.="<CITY>".$row["city"]."</CITY>\r\n";	
		$str_to_log.="<REGION>Томская область</REGION>\r\n";	
		$str_to_log.="<ADDRESS>".$row["itemname"]."</ADDRESS>\r\n";
		$str_to_log.="<CONTACTPERSON>Милкина Елена Александровна</CONTACTPERSON>\r\n";	
		$str_to_log.="<PHONES>".$row["phone"]."</PHONES>\r\n";
		$str_to_log.="<WORKTIME>".$row["worktime"]."</WORKTIME>\r\n";
		$str_to_log.="<IS_ACTUAL>Y</IS_ACTUAL>\r\n";
		$str_to_log.="<WORK_MODE>give+take</WORK_MODE>\r\n";
		$str_to_log.="<DESCRIPTION>г. ".$row["city"]." ".$row["itemname"]."</DESCRIPTION>\r\n";
		if($row["uchet"]=="fk")
		{
			$str_to_log.="<MODE>online</MODE>\r\n";
		}
		else
		{
			$str_to_log.="<MODE>offline</MODE>\r\n";
		}		
		
		
		$str_to_log.="<POSES>\r\n";
		for($poses=1;$poses<($row["num_term"]+1);$poses++)
		{
			$str_to_log.="<POS>\r\n";
			$str_to_log.="<CODE>".$row["fk_kod"].$poses."</CODE>\r\n";				
			$str_to_log.="</POS>\r\n";
		}
		
		$str_to_log.="</POSES>\r\n";
		$str_to_log.="</PHARMACY>\r\n";
		fwrite($fdbc, $str_to_log);
		fwrite($fdbcel, $str_to_log);
	}
	$str_to_log="</PHARMACIES>\r\n";
	fwrite($fdbc, $str_to_log);
	fclose($fdbc);
	fwrite($fdbcel, $str_to_log);
	fclose($fdbcel);

}
	
?>