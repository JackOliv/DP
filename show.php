<?php
require_once "bd_connect_bonus.php";
$connection = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connection, "utf8");
if (!$connection) {
    echo "Ошибка: Невозможно установить соединение с MySQL." . PHP_EOL;
    echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Текст ошибки error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}
echo "<a href=form.php>ввод данных</a><br>";
if(isset($_GET['typebonus']))
{
	unset($telnumber);
	if(isset($_GET['telnumber'])) 
	{
		$telnumber=$_GET['telnumber'];
	}
	unset($cardnumber);
	if(isset($_GET['cardnumber'])) 
	{
		$num_rowsidcardfromtel=0;
		$cardnumber=$_GET['cardnumber'];
		$sqlidcardfromnum="select * from bonuscards where cardnumber=\"$cardnumber\"";
		echo $sqlidcardfromnum."<br>";
		$residcardfromnum = mysqli_query($connection, $sqlidcardfromnum);
		//$residcardfromnum = mysqli_fetch_row($queridcardfromnum);
		$num_rowsidcardfromnum=mysqli_num_rows($residcardfromnum);
		if($num_rowsidcardfromnum==0)
		{
			echo "Карта по номеру карты (".$cardnumber.") не найдена<br>";
		}
		elseif($num_rowsidcardfromnum==1)
		{
			$rowidcardfromnum = mysqli_fetch_array($residcardfromnum);
			$idcard=$rowidcardfromnum["idcard"];
			$cardnumber=$rowidcardfromnum["cardnumber"];
			$telnumber=$rowidcardfromnum["telnumber"];
			$valbonus=$rowidcardfromnum["valbonus"];
			$regdate=$rowidcardfromnum["regdate"];
			$lastdate=$rowidcardfromnum["lastdate"];
			
			echo "Найдена карта id=(".$idcard.") по номеру карты cardnumber=(".$cardnumber.") номер телефона (".$telnumber.")<br>";
			echo "Величина бонуса bonus=(".$idcard.") дата регистрации cardnumber=(".$regdate.") последние данные (".$lastdate.")<br>";
		}
		else
		{
			echo "более одной карты зарегистрировано с одним и темже номеров карты. todo как это обрабатываем?";
		}
	}
	else 
	{
		if(isset($telnumber))
		{
			$sqlidcardfromtel="select * from bonuscards where telnumber=".$telnumber;
			$queridcardfromtel = mysqli_query($connection, $sqlidcardfromtel);
			$residcardfromtel = mysqli_fetch_row($queridcardfromtel);
			$num_rowsidcardfromtel=mysqli_num_rows($residcardfromtel);
			if($num_rowsidcardfromtel==0)
			{
				echo "Карта по номеру телефона (".$telnumber.") не найдена<br>";
			}
			elseif($num_rowsidcardfromtel==1)
			{
				$rowidcardfromtel = mysqli_fetch_array($residcardfromtel);
				$idcard=$rowidcardfromtel["idcard"];
				$cardnumber=$rowidcardfromtel["cardnumber"];
				$valbonus=$rowidcardfromtel["valbonus"];
				$regdate=$rowidcardfromtel["regdate"];
				$lastdate=$rowidcardfromtel["lastdate"];
				
				echo "Найдена карта id=(".$idcard.") cardnumber=(".$cardnumber.") по номеру телефона (".$telnumber.")<br>";
				echo "Величина бонуса bonus=(".$idcard.") дата регистрации cardnumber=(".$regdate.") последние данные (".$lastdate.")<br>";
			}
			else
			{
				echo "более одной карты привязано к телефонному номеру. todo как это обрабатываем?";
			}
		}
		else
		{
			echo " не введены ни номер карты ни номер телефона<br>";
		}
	}
	if(($num_rowsidcardfromtel==0)&&($num_rowsidcardfromnum==0))
	{
		$idcard=addcardtobase($_GET['cardnumber'],$_GET['telnumber']);
		$num_rowsidcardfromnum=1;
	}
	if(($num_rowsidcardfromtel==1)||($num_rowsidcardfromnum==1))
	{
		echo $_GET['typebonus']."<br>";
		if((isset($_GET['typebonus']))&&($_GET['typebonus']==1))
		{
			addbonus($idcard,$_GET);
		}
		elseif((isset($_GET['typebonus']))&&($_GET['typebonus']==0))
		{
			addbonus($idcard,$_GET);
		}
		else
		{
			showbonus($idcard);
		}
	}
}


function showbonus($idcard)
{
	global $connection;
	$sqlidcard="select * from bonuscards where idcard=".$idcard;
	echo "$sqlidcard<br>";
	$residcard = mysqli_query($connection, $sqlidcard);
	//$residcard = mysqli_fetch_row($queridcard);
	$num_rowsidcard=mysqli_num_rows($residcard);
	if($num_rowsidcard==0)
	{
		echo "Карта по ид карты (".$idcard.") не найдена<br>";
	}
	elseif($num_rowsidcard==1)
	{
		$rowidcard = mysqli_fetch_array($residcard);
		$sqlidcardmove="select * from bonusmoves where idcard=".$idcard." order by idbonus asc";
		echo "$sqlidcardmove<br>";
		$residcardmove = mysqli_query($connection, $sqlidcardmove);
		//$residcardmove = mysqli_fetch_row($queridcardmove);
		$num_rowsidcardmove=mysqli_num_rows($residcardmove);
		if($num_rowsidcardmove==0)
		{
			echo "Движение по ид карты (".$idcard.") не найдено<br>";
		}
		else
		{
			echo "Данные карты: ".$rowidcard["idcard"]." ".$rowidcard["cardnumber"]." ".$rowidcard["telnumber"]." <b>".$rowidcard["valbonus"]."</b> ".$rowidcard["regdate"]." ".$rowidcard["lastdate"]."<br>";
			echo "Движение по карте: <br>";
			for($i=0;$i<$num_rowsidcardmove;$i++)
			{
				$rowidcardmove = mysqli_fetch_array($residcardmove);
				echo $rowidcardmove["idbonus"]." ".$rowidcardmove["idcard"]." ".$rowidcard["cardnumber"]." ".$rowidcard["telnumber"]." ";
				if($rowidcardmove["typebonus"])echo "<font color=green>".$rowidcardmove["valbonus"]."</font>";else echo "<font color=red>".$rowidcardmove["valbonus"]."</font>";
				echo " ".$rowidcardmove["sumorder"]." ".$rowidcardmove["ipaddress"]." ".$rowidcardmove["apteka"]." ".$rowidcardmove["cash"]." ".$rowidcardmove["datebonus"]."<br>";
			}
		}
		
	}
}

function addbonus($idcard,$get)
{
	global $connection;
	$sqlidcard="select * from bonuscards where idcard=".$idcard;
	$residcard = mysqli_query($connection, $sqlidcard);
	//$residcard = mysqli_fetch_row($queridcard);
	$num_rowsidcard=mysqli_num_rows($residcard);
	if($num_rowsidcard==0)
	{
		echo "Карта по ид карты (".$idcard.") не найдена<br>";
	}
	elseif($num_rowsidcard==1)
	{
		
		/**bonuscard
		 	idcardИндекс 	int(6) 
		 	cardnumber 	varchar(20) 	
		3 	telnumber 	varchar(20) 	
		4 	valbonus 	int(7) 		
		5 	regdate 	datetime 	
		6 	lastdate 	datetime 	
		
		

CREATE TABLE `bonusmoves` (
  `idbonus` int(9) NOT NULL,
  `idcard` int(6) NOT NULL,
  `datebonus` datetime NOT NULL,
  `valbonus` int(8) NOT NULL,
  `sumorder` int(8) NOT NULL,
  `ipaddress` varchar(20) NOT NULL,
  `apteka` varchar(30) NOT NULL,
  `cash` varchar(20) NOT NULL,
  `typebonus` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
		*/
		
		$rowidcard = mysqli_fetch_array($residcard);
		$cardnumber=$get["cardnumber"];
		$datebonus= date ("Y-m-d H:i:s", time());
		$ipaddress="192.168.9.23";			//todo  определение ип адреса
		$valbonus=$get["valbonus"];
		$sumorder=$get["sumorder"];
		$apteka=$get["apteka"];
		$cash=$get["cash"];
		$typebonus=$get["typebonus"];
		$sqladdmove="insert into bonusmoves (idbonus,idcard,datebonus,valbonus,sumorder,ipaddress,apteka,cash,typebonus) values (NULL,'$idcard','$datebonus','$valbonus','$sumorder','$ipaddress','$apteka','$cash','$typebonus')";
		$queraddmove = mysqli_query($connection, $sqladdmove);
		echo "movebonus added <br>";
		$valbonus=calccardbonus($idcard);
		$sqlidcard="update bonuscards set valbonus=$valbonus, lastdate='$datebonus' where idcard=$idcard";
		echo "$sqlidcard<br>";
		$queridcard = mysqli_query($connection, $sqlidcard);
		echo "bonus updated<br>";
	}
}

function addcardtobase($cardnumber,$telnumber)
{
	global $connection;
	$datebonus= date ("Y-m-d H:i:s", time());
	$sqladdmove="insert into bonuscards (idcard,cardnumber,telnumber,valbonus,regdate,lastdate) values (NULL,'$cardnumber','$telnumber',0,'$datebonus','$datebonus')";
	echo "$sqladdmove<br>";
	$queraddmove = mysqli_query($connection, $sqladdmove);
	$idcard=mysqli_insert_id($connection);
		/**bonuscard
		 	idcardИндекс 	int(6) 
		 	cardnumber 	varchar(20) 	
		3 	telnumber 	varchar(20) 	
		4 	valbonus 	int(7) 		
		5 	regdate 	datetime 	
		6 	lastdate 	datetime 	
		*/
	return $idcard;
}

function calccardbonus($idcard)
{
	global $connection;
	$sqlidcardmove="select * from bonusmoves where idcard=".$idcard." order by idbonus asc";
	$residcardmove = mysqli_query($connection, $sqlidcardmove);
	//$residcardmove = mysqli_fetch_row($queridcardmove);
	$num_rowsidcardmove=mysqli_num_rows($residcardmove);
	$valbonus=0;
	if($num_rowsidcardmove>0)
	{
		//echo $rowidcard["idcard"]." ".$rowidcard["cardnumber"]." ".$rowidcard["telnumber"]." "$rowidcard["valbonus"]." ".$rowidcard["regdate"]." ".$rowidcard["lastdate"]." ";
		for($i=0;$i<$num_rowsidcardmove;$i++)
		{
			$rowidcardmove = mysqli_fetch_array($residcardmove);
			//echo $rowidcardmove["idmove"]." ".$rowidcardmove["idcard"]." ".$rowidcard["cardnumber"]." ".$rowidcard["telnumber"]." ";
			if($rowidcardmove["typebonus"])$valbonus+=$rowidcardmove["valbonus"];else $valbonus-=$rowidcardmove["valbonus"];
			//echo " ".$rowidcardmove["sumorder"]." ".$rowidcardmove["ipaddress"]." ".$rowidcardmove["apteka"]." ".$rowidcardmove["cash"]." ".$rowidcardmove["databonus"]."<br>";
		}
	}
	return $valbonus;
	
}





die();


?>



