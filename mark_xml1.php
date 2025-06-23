<?php 
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//include "dbmain.php";
require_once "bd_connect1.php";
$db = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($db, "CP1251");

//получаем список уникальных аптек
$sql_apteka = 'select distinct(sys_id) from mdlp1 where inn<>"inn" order by inn, sys_id';
$result_apteka = mysqli_query($db, $sql_apteka);
$numrows_apteka = mysqli_num_rows($result_apteka);
$close_cur_file=0;
echo "$numrows_apteka<hr><br>";
if($numrows_apteka>0)
{
	//пробегаем по каждой аптеке
	for($i=0;$i<$numrows_apteka;$i++)
	{
		//закрываем предыдущий файл
		//if($i>0)
		$close_cur_file=1;
		$row_apteka = mysqli_fetch_array($result_apteka);
		echo "-------------".$row_apteka["sys_id"]."<br>";
		//получаем список кизов с сортировкой по дате по конкретной аптеке
		$sql_sgtins = 'select * from mdlp1 where sys_id="'.$row_apteka["sys_id"].'" order by expiration_date, status';
		echo $sql_sgtins."<br>";
		$result_sgtins = mysqli_query($db, $sql_sgtins);
		$numrows_sgtins = mysqli_num_rows($result_sgtins);
		//начальная дата точно меньше тех что в базе
		$exp_date1="2019-01-01";
		//echo date_create('2011-04-24')->modify('-1 days')->format('Y-m-d');
		$new_date=0;
		$status=0; //полное списание или дележка
		$new_status=1;
		if($numrows_sgtins>0)
		{
			for($j=0;$j<$numrows_sgtins;$j++)
			{
				echo "$j из $numrows_sgtins new_date=$new_date<br>";
				$row_sgtins = mysqli_fetch_array($result_sgtins);
				echo $row_sgtins["sgtin"]." ||||| ".$row_sgtins["expiration_date"]."<br>";
				if($status!=$row_sgtins["status"]){$new_status=1;$status=$row_sgtins["status"];}else {$new_status=0;}
				if($new_date==0)
				{
					$exp_datez1=explode("-",$exp_date1);
					$exp_datez2=explode("-",$row_sgtins["expiration_date"]);
					echo $exp_datez1[0]." ".$exp_datez1[1]." ".$exp_datez1[2]." ".$exp_datez2[0]." ".$exp_datez2[1]." ".$exp_datez2[2]."<br>";
					if(($exp_datez1[0]!=$exp_datez2[0])||($exp_datez1[1]!=$exp_datez2[1])||($exp_datez1[2]!=$exp_datez2[2]))
					{
						$new_date=1;
						$exp_date1=$row_sgtins["expiration_date"];
					}
					
					//
				}
				echo "$j iz $numrows_sgtins new_date=$new_date<br>";
				if(($new_date==1)||($new_status))
				{
					
						$close_cur_file=1;
					// туду определить дату на 1 день меньше
					
					$exp_date=date_create($row_sgtins["expiration_date"])->modify('-1 days')->format('Y-m-d');
					$exp_date_doc=date_create($row_sgtins["expiration_date"])->modify('-1 days')->format('d.m.Y');
					echo $exp_date." ".$row_sgtins["expiration_date"]."<br>";
					//$exp_date=$row_sgtins["expiration_date"];
					if($close_cur_file==1)
					{
						//закрытие текущего файла
						$str_to_log.='</order_details></withdrawal></documents>';
						echo "тут будет текст файла".$str_to_log."<br>";
						fwrite($fdbc, $str_to_log);
						fclose($fdbc);
						echo "=<br>=<br>=<br>";
					}
						//начало нового файла
					$close_cur_file=0;
					echo OSTATKIPATH."mark/out/".$row_sgtins["inn"]."_".$row_sgtins["status"]."_".$row_sgtins["sys_id"]."_".$row_sgtins["expiration_date"].".xml<br>";
					$fnameaz=OSTATKIPATH."mark/out/".$row_sgtins["inn"]."_".$row_sgtins["status"]."_".$row_sgtins["sys_id"]."_".$row_sgtins["expiration_date"].".xml";
					$fdbc = fopen($fnameaz, 'w');
					$str_to_log='<documents version="1.37" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
				    $str_to_log.='<withdrawal action_id="552">';
				    $str_to_log.='<subject_id>'.$row_sgtins["sys_id"].'</subject_id>';
				    $str_to_log.='<operation_date>'.$exp_date.'T00:04:16+03:00</operation_date>';
				    $str_to_log.='<doc_num>'.$row_sgtins["sys_id"].$j.'</doc_num>';
				    $str_to_log.='<doc_date>'.$exp_date_doc.'</doc_date>';
				    if($status==1)$str_to_log.='<withdrawal_type>13</withdrawal_type>';else $str_to_log.='<withdrawal_type>16</withdrawal_type>';
				    $str_to_log.='<order_details>';
				    //echo $str_to_log."<br>";
				}
				echo "$j из $numrows_sgtins new_date=$new_date<br>";
				$new_date=0;
				$str_to_log.='<sgtin>'.$row_sgtins["sgtin"].'</sgtin>';
			}
		}
	}
	$str_to_log.='</order_details></withdrawal></documents>';
	fwrite($fdbc, $str_to_log);
	fclose($fdbc);
}
	
?>