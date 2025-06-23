<?php 
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//include "dbmain.php";
require_once "bd_connect1.php";
$db = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($db, "CP1251");

//получаем список уникальных аптек
$sql_apteka = 'select distinct(sys_id) from mdlp2 where inn<>"inn" order by inn, sys_id';
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
		$sql_sgtins = 'select * from mdlp2 where sys_id="'.$row_apteka["sys_id"].'"';
		echo $sql_sgtins."<br>";
		$result_sgtins = mysqli_query($db, $sql_sgtins);
		$numrows_sgtins = mysqli_num_rows($result_sgtins);
		
		if($numrows_sgtins>0)
		{
			for($j=0;$j<$numrows_sgtins;$j++)
			{
				$row_sgtins = mysqli_fetch_array($result_sgtins);
				echo $row_sgtins["sgtin"]."<br>";
					
				$expiration_date="2023-12-22";
				$exp_date=date_create($expiration_date)->modify('-1 days')->format('Y-m-d');
				$exp_date_doc=date_create($expiration_date)->modify('-1 days')->format('d.m.Y');
				//$exp_date=$row_sgtins["expiration_date"];
				if($i!=0)
				{
					if($j==0)
					{
						//закрытие текущего файла
						$str_to_log.='</order_details></withdrawal></documents>';
						fwrite($fdbc, $str_to_log);
						fclose($fdbc);
						echo "=<br>=<br>=<br>";
					}
				}
				//начало нового файла
				if($j==0)
					{
				echo OSTATKIPATH."mark/out/".$row_sgtins["inn"]."_".$row_sgtins["sys_id"]."_".$exp_date.".xml<br>";
				$fnameaz=OSTATKIPATH."mark/out/".$row_sgtins["inn"]."_".$row_sgtins["sys_id"]."_".$exp_date.".xml";
				$fdbc = fopen($fnameaz, 'w');
				$str_to_log='<documents version="1.37" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
			    $str_to_log.='<withdrawal action_id="552">';
			    $str_to_log.='<subject_id>'.$row_sgtins["sys_id"].'</subject_id>';
			    $str_to_log.='<operation_date>'.$exp_date.'T00:04:15+03:00</operation_date>';
			    $str_to_log.='<withdrawal_type>13</withdrawal_type>';
			    $str_to_log.='<order_details>';
			    //echo $str_to_log."<br>";
				}
				echo "$j из $numrows_sgtins<br>";
				$str_to_log.='<sgtin>'.$row_sgtins["sgtin"].'</sgtin>';
			}
		}
	}
	$str_to_log.='</order_details></withdrawal></documents>';
	fwrite($fdbc, $str_to_log);
	fclose($fdbc);
}
	
?>