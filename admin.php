<?php

    require_once "bd_connect1.php";
	require "allfunc.php";
	checkAuth();

    //вывод списка прайсов
    function results()
    {
		// Подключение к базе данных
        $connect = mysqli_connect(HOST, USER, PW, DB);
        mysqli_set_charset($connect, "UTF8");
		// Если есть записи, то добавляем их в массив
         $querycity = "SELECT * FROM `cities` WHERE 1";
        $resultcity = mysqli_query($connect, $querycity);
        $num_rowscity=mysqli_num_rows($resultcity);
        if ($num_rowscity> 0) 
		{
			for($i=0;$i<$num_rowscity;$i++)
			{
				$rescity = mysqli_fetch_array($resultcity);
			}
		}
		// SQL запрос на выборку всех записей из таблицы PARTNERSTABLE
        $query = "SELECT `id`, `type`,`brand`,`itemcode`,`itemname`,`uploaddate`,`last_status`,`downloadprice`, `lekvapteke`, `tominfarm`, `s09`,`num_term`,`city`, `phone`, `worktime`,`firm`,`uchet`,`districtid`,`net` FROM `" . PARTNERSTABLE . "` WHERE 1";
       // echo "$query";
        $result = mysqli_query($connect, $query);
        $num_rows=mysqli_num_rows($result);
		if ($num_rows> 0) 
		{// Если есть записи, то добавляем их в массив
			for($i=0;$i<$num_rows;$i++)
			{
				echo "<tr>";
				$res = mysqli_fetch_array($result);
				echo '<td><input type="checkbox" name="modify[]" value="'.$res['id'].'" form="mod"></td>';
				echo '<td><a href="form.php?itemcode='.$res["itemcode"].'" class="btn btn-warning btn-xs">Редактировать</td>';
				echo '<td>'.$res["uchet"].'</td>';
				echo '<td>'.$res["itemcode"].'</a></td>';
				echo '<td>'.$res["num_term"].'</td>';
				echo '<td>'.cityfound($connect,($res["city"]+0)).'</td>';
				echo '<td>'.districtfound($connect,($res["districtid"]+0)).'</td>';
				echo '<td>'.$res["phone"].'</td>';
				echo '<td>'.$res["worktime"].'</td>';
				echo '<td>'.$res["firm"].'</td>';
				if($res['brand']==1 || $res["brand"]==2 || $res["brand"]==3 || $res["brand"]==4){echo '<td><img src="images/'.$res['brand'].'.png"></td>';}else echo '<td>&nbsp;</td>';
				echo "<td>".$res["uploaddate"]."</td>";$val="";
				echo '<td><a href="form.php?itemcode='.$res["itemcode"].'">'.$res["itemname"].'</a></td>';
				echo '<td>'.$res["net"].'</td>';
				if ($res['last_status'] == 0)
				{
					$val='<font color="darkred"><b>Не загружено</b></font>';
				}		
				elseif($res['last_status'] == 1) 
				{
					$val='<font color="darkgreen"><b>Успешно</b></font>';
				} 
				elseif($res['last_status'] == 2) 
				{
					$val='<font color="darkred"><b>Ошибка подключения к FB</b></font>';
				} 
				elseif($res['last_status'] == 3) 
				{
					$val='<font color="darkred"><b>Пустой прайс</b></font>';
				}
				else
				{
					$val='<font color="black"><b>Неопределено</b></font>';
				}
				echo "<td>$val</td>";$val="";	
				if ($res['downloadprice']==1)
				{
					$val = "<img src='images/da.jpg' width=20 height=20>";
				}
				else
				{
					$val = "<img src='images/net.jpg' width=20 height=20>";
				}
				
				echo "<td>$val</td>";	
				$val="";
				if ($res['lekvapteke']==1)
				{
					$val = "<img src='images/da.jpg' width=20 height=20>";
				}
				else
				{
					$val = "<img src='images/net.jpg' width=20 height=20>";
				}
				echo "<td>$val</td>";
				$val="";	
				if ($res['tominfarm']==1)
				{
					$val = "<img src='images/da.jpg' width=20 height=20>";
				}
				else
				{
					$val = "<img src='images/net.jpg' width=20 height=20>";
				}
				echo "<td>$val</td>";$val="";	
				if ($res['s09']==1)
				{
					$val = "<img src='images/da.jpg' width=20 height=20>";
				}
				else
				{
					$val = "<img src='images/net.jpg' width=20 height=20>";
				}
				echo "<td>$val</td>";	$val="";
					
				
				echo "</tr>";
			}
		}
        mysqli_close($connect);
    }
    
    // загрузка прайса из файла
    function refresh_db($remfilename, $code, $type, $uchet)
    {
		mysqli_report(MYSQLI_REPORT_ERROR);
		$conn = mysqli_connect(HOST, USER, PW, DB);
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
			//echo $insquery;
		}
		else
		{
			$insquery = "LOAD DATA LOCAL INFILE '" . $remfilename . "' INTO TABLE `" . PRICETABLE . "` CHARACTER SET UTF8 FIELDS TERMINATED BY ';' ENCLOSED BY '' LINES TERMINATED BY '\n\r' IGNORE 1 LINES;";
		}
		
		
		if (!($smth=$conn->query($insquery))){
			echo "\nQuery execute failed: ERRNO: (" . $conn->errno . ") " . $conn->error;
		} else { // Если прошло успешно, обновляем дату загрузки прайса
			$q = "UPDATE `" . PARTNERSTABLE . "` SET uploaddate = CURDATE() WHERE `itemcode` = $code;";
			mysqli_query($conn, $q);
			$q = "UPDATE `" . PARTNERSTABLE . "` SET last_status = 1 WHERE `itemcode` = $code;";
			mysqli_query($conn, $q);
			//header("Location: /".ADMINPHP);
		}
		
    }
    // выгрузка прайса из firebird в csv
    function update($code,$who, $type, $uchet)
    {
		
		$uchet="fk";
		if($uchet=="fk")
		{
			$dump_name=CSVPATH . $code .".csv";
			refresh_db($dump_name, $code, $type, $uchet);
		}/*
		else
		{
			$connect = @ibase_connect($db, FDBUSER,FDBPASSWORD);
		
			if($connect !== FALSE)
			{

				if($who>0)	$q="Select * from P_2017_PriceStockForSpravka(".$code.")";
				else		$q="Select * from P_2017_PricePartnerForSpravka(".$code.")";
				
				$run_q=ibase_query($connect, $q);
				
				if(ibase_num_fields($run_q) > 0) 
				{
					$data = '';
					while ($row = ibase_fetch_object($run_q)) 
					{
						$data .= $row->ARTICUL . ";";
						$data .= "{$who};"; 
						$data .= "{$code};";
						$ARTICULNAME = str_replace("\"", "'", $row->ARTICULNAME);
						$ARTICULNAME = str_replace(";", ".", $ARTICULNAME);
						$data .= $ARTICULNAME . ";";
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
					if(isset($data) && $data != '') 
					{
						$data=iconv("UTF-8", "windows-1251", "articul;brand;partner_code;name;madeby;count;price;godendo;node;barcode;jvnl;commercial;priceblock;recept\n\r").$data;
						$data1 = iconv("windows-1251", "UTF-8//TRANSLIT", $data);
						$dump_name=CSVPATH . $code .".csv";
						file_put_contents($dump_name, $data1);
						ibase_free_result($run_q);
						ibase_close($connect);
						refresh_db($dump_name, $code, $type, $uchet);
					} 
					else 
					{
						$conn = mysqli_connect(HOST, USER, PW, DB) or die(mysqli_error($conn));
						mysqli_set_charset($conn, "utf8");
						$q = "UPDATE `" . PARTNERSTABLE . "` SET last_status = 3 WHERE `itemcode` = $code;";
						mysqli_query($conn, $q);
						mysqli_close($conn);
						header("Location: /".ADMINPHP);
					}
				}
			} 
			else 
			{
				$conn = mysqli_connect(HOST, USER, PW, DB) or die(mysqli_error($conn));
				mysqli_set_charset($conn, "utf8");
				$q = "UPDATE `" . PARTNERSTABLE . "` SET last_status = 2 WHERE `itemcode` = $code;";
				mysqli_query($conn, $q);
				mysqli_close($conn);
			}
		}*/
	}
	// Обработка формы с параметром submit
	if (isset($_GET['submit']))
	{
		//echo "submit";
	//	print_r($_GET);echo "<hr>";
	//	print_r($_POST);
		if (isset($_GET['modify']))
		{
		//	echo "modifi";
			$prices_for_refresh=implode(",", $_GET['modify']);
			$connect = mysqli_connect(HOST, USER, PW, DB);
			mysqli_set_charset($connect, "utf8");
			if($_GET['submit']=='priceupdate')
			{
				$query = "SELECT `brand`, `itemcode`, `type`, `uchet` FROM `" . PARTNERSTABLE . "` WHERE `id` IN (" . $prices_for_refresh . ");";
				echo  "$query";
				$results = mysqli_query($connect, $query);
				while ($result = mysqli_fetch_object($results)) {
					update($result->itemcode, $result->brand, $result->type, $result->uchet);
				}
			}
			else{
				$select_positions = "SELECT `itemcode` FROM `" . PARTNERSTABLE ."` WHERE `id` IN (" . $prices_for_refresh . ");";
				$results = mysqli_query($connect, $select_positions);
				$for_delete = [];
				while ($result = mysqli_fetch_object($results)){
					array_push($for_delete,  $result->itemcode);
				}
				$for_delete = implode(", ", $for_delete);
				$query = "DELETE FROM `" . PRICETABLE . "` WHERE `partner_code` in ($for_delete);";
				if (!mysqli_query($connect, $query)){
					print_r(mysqli_error_list($connect));
				}
				
				if($_GET['submit']=="Удалить"){
					$delquery="DELETE FROM `" . PARTNERSTABLE . "` WHERE `id` in ($prices_for_refresh);";
					mysqli_query($connect, $delquery);
				} else { // Удаляем дату прайса
					$delquery="UPDATE `" . PARTNERSTABLE . "` SET uploaddate = NULL WHERE `id` in ($prices_for_refresh);";
					mysqli_query($connect, $delquery);
					$q = "UPDATE `" . PARTNERSTABLE . "` SET last_status = NULL WHERE `id` in ($prices_for_refresh);";
					mysqli_query($connect, $q);
				}
				//header("Location: /".ADMINPHP);
			}  
			mysqli_close($connect);
		}
	}// Обработка POST запроса для сохранения настроек
    if (isset($_POST['check_clean']) && isset($_POST['days_count'])){
        file_put_contents("timer.txt", implode(",", array($_POST["check_clean"], $_POST['days_count'])));
    }// Обработка POST запроса для удаления настроек
    if (isset($_POST['del_timer'])){
        file_put_contents("timer.txt", "false, '0'");
    }// Чтение настроек из файла
    if(file_exists("timer.txt")){
        $content = explode(",", file_get_contents("timer.txt"));
        $to_clean=$content[0];
        $days_count=$content[1];
    }else{
        $days_count="0";
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
    <style>
        .btn-default, table{
            margin: 10px 10px 10px 10px;
            border: medium;
        }
    </style>
    <title>Прайс-листы</title>
</head>
<body>		
<?php 
$navbarType = "admin"; 
include 'topnavbar.php';
?>
    <h3 style="text-align: center">Управление прайс-листами аптек и поставщиков</h3>
    <div class="checkbox" style="margin-left: 10px">
        <label>
            <input id="checkclean" type="checkbox" name ="checkclean" value="true" <?php if($to_clean=="true"){echo "checked";}?>>Очищать прайсы старше 
            <input id="cleaning" name="cleaning" type="number" style="width: 50px" value=<?php echo $days_count;?>> дней (ежедневно в 03:00)
        </label>
    </div>
    <div>
        <form id="mod" method="GET" action="admin.php">
			<input type="submit" style="margin-left: 10px; margin-bottom: 10px;" class="btn btn-success add-btn" name="submit" value="Добавить" formaction="form.php">
            <input type="submit" style="margin-left: 10px; margin-bottom: 10px;" class="btn btn-success add-btn" name="submit" value="priceupdate">
            <input type="submit" style="margin-left: 10px; margin-bottom: 10px;" class="btn btn-success add-btn" name="submit" value="cleanprice" style="width: 150px">
            <input type="submit" class="btn btn-sm btn-danger p-4" name="submit" value="Удалить" style="float: right; margin-right: 10px; margin-bottom: 10px;">
        </form>
    </div>

    <table id = "prices" class="table table-bordered">
        <thead>
            <tr>
                <th style="padding: auto 0; width: 0px;"></th>
				<th style="padding: auto 0; width: 0px;">Действие</th>
                <th style="padding: auto 0;">УС</th>
                <th style="padding: auto 0;">Код</th>
                <th style="padding: auto 0;">Кол-во касс</th>
                <th style="padding: auto 0;">Город</th>
				<th style="padding: auto 0;">Район</th>
                <th style="padding: auto 0;">Телефон</th>
                <th style="padding: auto 0;">Рабочее время</th>
                <th style="padding: auto 0;">Юрлицо</th>
                <th style="padding: auto 0;">Бренд</th>
                <th style="padding: auto 0;">Дата прайса</th>
                <th style="padding: auto 0;">Наименование</th>
				<th style="padding: auto 0;">Net</th>
                <th style="padding: auto 0;">Последний статус</th>
                <th style="padding: auto 0;">Загр. прайс</th>
                <th style="padding: auto 0;">Лекваптеке</th>
                <th style="padding: auto 0;">томинформ</th>
                <th style="padding: auto 0;">С09</th>
            </tr>
        </thead>
        <tbody>
            <?php results()?>
        </tbody>
    </table>
    <div class="idid"></div>
</body>
</html>
<script src="/js/jquery-3.2.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script type="text/javascript" charset="utf8" src="/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf8" src="/js/moment.min.js"></script>
<script type="text/javascript" charset="utf8" src="/js/datetime-moment.js"></script>
<script>
    $(function(){
		$.fn.dataTable.moment( 'DD.MM.YYYY');
        $('#prices').DataTable({
            "searching": false,
            "stateSave": true,
            "language":{
                info:"Показано с _START_ по _END_ из _TOTAL_ найденных",
                "paginate": {
                    "first":      "Первая",
                    "last":       "Последняя",
                    "next":       "  Следующая",
                    "previous":   "Предыдущая  "
                },
                "infoEmpty":      "Записей не найдено",
                "emptyTable":     "Записей не найдено",
                "lengthMenu":     "Показывать по _MENU_ "
            }
        });
    });
    
    function somesth(){
        daysCount=$('#cleaning').val();
        checking=$('#checkclean').val();
        $.post('admin.php',{
            check_clean: checking, days_count: daysCount
        });
    }
    $('#cleaning').change(function () {
        somesth()
    });
    $('#checkclean').change(function(){
        if(!($('#checkclean').is(':checked'))){
            $.post('admin.php', {
                del_timer:"delete"
            });
            $('#cleaning').val('');
        }else{
            somesth();
        }
    });
</script>



