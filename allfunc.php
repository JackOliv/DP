<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once "bd_connect1.php";
function findip()
{
	$ip_address = $_SERVER["REMOTE_ADDR"];
	//$ip_address = "10.70.2.100";
	//$ip_address = "10.54.2.101";
	return $ip_address;
}

function cityfound($connect, $idcity)
{
    $querycity = "SELECT * FROM `cities` WHERE `idcity`=" . $idcity;
    $resultcity = mysqli_query($connect, $querycity);
    $num_rowscity = mysqli_num_rows($resultcity);
    $cityname = "";
    if ($num_rowscity == 1) {
        $rescity = mysqli_fetch_array($resultcity);
        $cityname = $rescity["namecity"];
    } else {
        $cityname = "Невыбран";
    }
    return $cityname;
}

function districtfound($connect, $districtid)
{
    $querycity = "SELECT * FROM `district` WHERE `id`=" .  $districtid;
    $resultcity = mysqli_query($connect, $querycity);
    $num_rowscity = mysqli_num_rows($resultcity);
    $districtname = "";
    if ($num_rowscity == 1) {
        $rescity = mysqli_fetch_array($resultcity);
        $districtname = $rescity["name"];
    } else {
        $districtname = "Невыбран";
    }
    return $districtname;
}
function finduserfromip($ip){
	global $cityuser1;
	$data=$ip;
	$ip=explode(".",$data);
	$rasp="";
	$connect = mysqli_connect(HOST, USER, PW, DB);
	mysqli_set_charset($connect, "UTF8");
	$queryc = "SELECT * FROM `cities` WHERE 1 order by idcity";
	//echo $query;
	$resultc = mysqli_query($connect, $queryc);
	$num_rowsc=mysqli_num_rows($resultc);
	if ($num_rowsc>0) 
	{
		for($i=0;$i<$num_rowsc;$i++)
		{
			$resc = mysqli_fetch_array($resultc);
			$ipdiapason[$i]=$resc['ipcity'];
			$ipdiap=$ip[0].".".$ip[1].".";
			if($ipdiap==$resc['ipcity'])
			{
					if(($ip[2]=="0")&&($ipdiap=="10.70."))
					{	
						$cityuser1="Офис";
						break;
					}
					elseif(($ip[2]=="100")&&($ipdiap=="192.168."))
					{
						$rasp="Удаленка";
						$cityuser1.="Удаленка";
						break;
					}
					else
					{
						$ip1=$ip[2];
						$query = "SELECT `itemname`, `net`, `city`, `itemcode` FROM `" . PARTNERSTABLE . "` WHERE city=".$resc['idcity']." AND net=$ip1";
						//echo $query;
						$result = mysqli_query($connect, $query);
						$num_rows=mysqli_num_rows($result);
						if ($num_rows==1) 
						{
							$res = mysqli_fetch_array($result);
							$cityuser1=$res["itemname"];
							$kto="";
							$cityuser1.=" ".cityfound($connect,($res["city"]+0));
							switch($ip[3])
							{
								case 1: {$kto="Шлюз";} break;
								case 2: {$kto="Серв";} break;
								case 3: {$kto="Серв";} break;
								case 6: {$kto="Серв";} break;
								case 11: {$kto="Серв";} break;
								case 100: {$kto="Зав";} break;
								case 101: {$kto="Опер";} break;
								case 111: {$kto="К1";} break;
								case 112: {$kto="К2";} break;
								case 113: {$kto="К3";} break;
								default: $kto="НЗ";
							}
							//$rasp=$kto." ".$rasp;
							$cityuser1=$cityuser1." ".$kto."||".$res["city"];
							break;
						}
						else
						{
							$cityuser1.="Аптека не добавлена";
						}
				}
			}
			else
			{
				$cityuser1="Нет".$i;
			}
		}
	}
	return $cityuser1;
}
function finduser($type)
{
	global $cityuser1;
	if($type==1)
	{
		$data=findip();
		$ip=explode(".",$data);
		$rasp="";
		$connect = mysqli_connect(HOST, USER, PW, DB);
		mysqli_set_charset($connect, "UTF8");
		$queryc = "SELECT * FROM `cities` WHERE 1 order by idcity";
		//echo $query;
		$resultc = mysqli_query($connect, $queryc);
		$num_rowsc=mysqli_num_rows($resultc);
		if ($num_rowsc>0) 
		{
			for($i=0;$i<$num_rowsc;$i++)
			{
				$resc = mysqli_fetch_array($resultc);
				$ipdiapason[$i]=$resc['ipcity'];
				$ipdiap=$ip[0].".".$ip[1].".";
				//echo "|".$ipdiap."|";
				if($ipdiap==$resc['ipcity'])
				{
					if(($ip[2]=="0")&&($ipdiap=="10.70."))
					{	
						$cityuser1="Офис";
						break;
					}
					elseif(($ip[2]=="100")&&($ipdiap=="192.168."))
					{
						$rasp="Удаленка";
						$cityuser1.="Удаленка";
						break;
					}
					else
					{
						$ip1=$ip[2];
						$query = "SELECT `itemname`, `net`, `city`, `itemcode` FROM `" . PARTNERSTABLE . "` WHERE city=".$resc['idcity']." AND net=$ip1";
						//echo $query;
						$result = mysqli_query($connect, $query);
						$num_rows=mysqli_num_rows($result);
						if ($num_rows==1) 
						{
							$res = mysqli_fetch_array($result);
							$cityuser1=$res["itemname"];
							$kto="";
							$cityuser1.=" ".cityfound($connect,($res["city"]+0));
							switch($ip[3])
							{
								case 1: {$kto="Шлюз";} break;
								case 2: {$kto="Серв";} break;
								case 3: {$kto="Серв";} break;
								case 6: {$kto="Серв";} break;
								case 11: {$kto="Серв";} break;
								case 100: {$kto="Зав";} break;
								case 101: {$kto="Опер";} break;
								case 111: {$kto="К1";} break;
								case 112: {$kto="К2";} break;
								case 113: {$kto="К3";} break;
								default: $kto="НЗ";
							}
							//$rasp=$kto." ".$rasp;
							$cityuser1=$cityuser1." ".$kto."||".$res["city"];
							break;
						}
						else
						{
							$cityuser1.="Аптека не добавлена";
						}
					}
				}
				else
				{
					$cityuser1="Нет".$i;
				}
			}
		}
		
	}
	elseif($type==2)
	{
		$data=findip();
		$ip=explode(".",$data);
		$rasp="";
		if($ip[2]=="0")
		{	
			$cityuser1="Не_Аптека";
		}
		elseif($ip[2]=="100")
		{
			$cityuser1="Не_Аптека";
		}
		else
		{
			$ip2=$ip[2];
			$connect = mysqli_connect(HOST, USER, PW, DB);
			mysqli_set_charset($connect, "UTF8");
			$query = "SELECT `itemname`, `net`, `city` FROM `" . PARTNERSTABLE . "` WHERE net=$ip2";
			$result = mysqli_query($connect, $query);
			$num_rows=mysqli_num_rows($result);
			if ($num_rows==1) 
			{
				$res = mysqli_fetch_array($result);
				$cityuser=$res["city"];
			}
			else
			{
				$cityuser1="Не_Аптека";
			}
		}
	}
	elseif ($type == 3) {
		$data = findip();
		$ip = explode(".", $data);
		$cityuser1 = "";
		$connect = mysqli_connect(HOST, USER, PW, DB);
		mysqli_set_charset($connect, "UTF8");
		$queryc = "SELECT * FROM `cities` WHERE 1 ORDER BY idcity";
		$resultc = mysqli_query($connect, $queryc);
		$num_rowsc = mysqli_num_rows($resultc);
		if ($num_rowsc > 0) {
			for ($i = 0; $i < $num_rowsc; $i++) {
				$resc = mysqli_fetch_array($resultc);
				$ipdiapason[$i] = $resc['ipcity'];
				$ipdiap = $ip[0] . "." . $ip[1] . ".";
				if ($ipdiap == $resc['ipcity']) {
					if (($ip[2] == "0") && ($ipdiap == "10.70.")) {
						$cityuser1 = "100000";
						break;
					} elseif (($ip[2] == "100") && ($ipdiap == "192.168.")) {
						$cityuser1 .= "200000";
						break;
					} else {
						$ip1 = $ip[2];
						$query = "SELECT `itemcode`, `city` FROM `" . PARTNERSTABLE . "` WHERE city=" . $resc['idcity'] . " AND net=$ip1";
						$result = mysqli_query($connect, $query);
						$num_rows = mysqli_num_rows($result);
						if ($num_rows == 1) {
							$res = mysqli_fetch_array($result);
							$cityuser1 = $res["itemcode"];
							break;
						} else {
							$cityuser1 = "0123";
						}
					}
				}
			}
		}
	}
	return $cityuser1;
}
function finduserbyip($urip){
	global $cityuser1;
		$data = $urip;
		$ip = explode(".", $data);
		$cityuser1 = "";
		$connect = mysqli_connect(HOST, USER, PW, DB);
		mysqli_set_charset($connect, "UTF8");
		$queryc = "SELECT * FROM `cities` WHERE 1 ORDER BY idcity";
		$resultc = mysqli_query($connect, $queryc);
		$num_rowsc = mysqli_num_rows($resultc);
		if ($num_rowsc > 0) {
			for ($i = 0; $i < $num_rowsc; $i++) {
				$resc = mysqli_fetch_array($resultc);
				$ipdiapason[$i] = $resc['ipcity'];
				$ipdiap = $ip[0] . "." . $ip[1] . ".";
				if ($ipdiap == $resc['ipcity']) {
					if (($ip[2] == "0") && ($ipdiap == "10.70.")) {
						$cityuser1 = "100000";
						break;
					} elseif (($ip[2] == "100") && ($ipdiap == "192.168.")) {
						$cityuser1 .= "200000";
						break;
					} else {
						$ip1 = $ip[2];
						$query = "SELECT `itemcode`, `city` FROM `" . PARTNERSTABLE . "` WHERE city=" . $resc['idcity'] . " AND net=$ip1";
						$result = mysqli_query($connect, $query);
						$num_rows = mysqli_num_rows($result);
						if ($num_rows == 1) {
							$res = mysqli_fetch_array($result);
							$cityuser1 = $res["itemcode"];
							break;
						} else {
							$cityuser1 = "0123";
						}
					}
				}
			}
		}
	return $cityuser1;
}
function findcity($type){
	global $cityuser1;
	if($type==1)
	{
		$data=findip();
		$ip=explode(".",$data);
		$rasp="";
		$connect = mysqli_connect(HOST, USER, PW, DB);
		mysqli_set_charset($connect, "UTF8");
		$queryc = "SELECT * FROM `cities` WHERE 1 order by idcity";
		//echo $query;
		$resultc = mysqli_query($connect, $queryc);
		$num_rowsc=mysqli_num_rows($resultc);
		if ($num_rowsc>0) 
		{
			for($i=0;$i<$num_rowsc;$i++)
			{
				$resc = mysqli_fetch_array($resultc);
				$ipdiapason[$i]=$resc['ipcity'];
				$ipdiap=$ip[0].".".$ip[1].".";
				//echo "|".$ipdiap."|";
				if($ipdiap==$resc['ipcity'])
				{
					if(($ip[2]=="0")&&($ipdiap=="10.70."))
					{	
						$cityuser1="0";
						break;
					}
					else
					{
						$ip1=$ip[2];
						$query = "SELECT `itemname`, `net`, `city`, `itemcode` FROM `" . PARTNERSTABLE . "` WHERE city=".$resc['idcity']." AND net=$ip1";
						//echo $query;
						$result = mysqli_query($connect, $query);
						$num_rows=mysqli_num_rows($result);
						if ($num_rows==1) 
						{
							$res = mysqli_fetch_array($result);
							$cityuser1=$res["city"];
							break;
						}
						else
						{
							$cityuser1.="0";
						}
					}
				}
				else
				{
					$cityuser1="0";
				}
			}
		}
	}
	return $cityuser1;
}
function checkAuth() {
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }
}
