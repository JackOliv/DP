<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once "bd_connect1.php";
$connection = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connection, "utf8");
if (!$connection) {
    echo "Ошибка: Невозможно установить соединение с MySQL." . PHP_EOL;
    echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Текст ошибки error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}

$i=0;
$root=118801;
//$root=1;
$lev=0;

$sqlroot= "select * from gbcatalog where node = $root order by name";
//echo $sqlroot;
$resroot = mysqli_query($connection, $sqlroot);
//$resroot = mysqli_fetch_row($querroot);
$num_rows_root=mysqli_num_rows($resroot);
//echo $num_rows_root;
$i=0;$j=1;
if($num_rows_root>0)
{
	for($n=0;$n<$num_rows_root;$n++)
	{
		$rowroot=mysqli_fetch_array($resroot);
		$catalog[$n]=$rowroot;
		$catalog[$n]["level"]=$j;
	}
}
//print_r($catalog);

$countc=count($catalog);
//echo "Первый уровень каталога $countc щтук <br>";
if(!isset($_GET["mode"]))$_GET["mode"]=0;
if($_GET["mode"]==1)
{
	echo "<a href='catalogshow.php?mode=0'> Показать полный каталог</a><br>";
	if(isset($_GET["level"]))$lev=$_GET["level"];
	else $lev=3;
}
else
{
	echo "<a href='catalogshow.php?mode=1&level=3'> Показать только первые три уровня каталога</a><br>";
	$lev=0;
}

for($num=0;$num<$countc;$num++)
{
	$num1=$num+1;
	//echo "$num.  ".$catalog[$num]["id"]." ".$catalog[$num]["node"]."  ".$catalog[$num]["name"]." уровень".$catalog[$num]["level"]."<br>";
	$node=$catalog[$num]["id"];	
	$j=$catalog[$num]["level"]+1;
	//echo $j."<br>";	
	foundonnode($num1,$node,$j);
	$countc=count($catalog);
}

//echo "<hr><hr><hr><hr><hr><hr>";
$countc=count($catalog);
for($num=0;$num<$countc;$num++)
{
	if($catalog[$num]["level"]>3)
	{
		if(isset($_GET["mode"])&&($_GET["mode"]==1))
		{
			
		}
		else
		{
			for($ff=1;$ff<$catalog[$num]["level"];$ff++)
			{
				echo "  -  ";
			}
			echo $catalog[$num]["name"]."(".$catalog[$num]["id"]." - ".$catalog[$num]["node"]."<br>";
		}
	}
	else
	{
		for($ff=1;$ff<$catalog[$num]["level"];$ff++)
		{
			echo "  -  ";
		}
		echo "<b>".$catalog[$num]["name"]."(".$catalog[$num]["id"]." - ".$catalog[$num]["node"]."</b><br>";
	}
}


function foundonnode($i1,$node,$j)
{
	global $catalog;
	global $connection;
	//global $i;
	$sql="select * from gbcatalog where node = $node order by name";
	//echo $sql."<br>";
	$res = mysqli_query($connection, $sql);
	//$res = mysqli_fetch_row($quer);
	$num_rows=mysqli_num_rows($res);
	//echo "к добавлению по ноде=$node строк = $num_rows номер строки $i1 <br>";
	if($num_rows>0)
	{
		movemas($i1,$num_rows);
		for($k=0;$k<$num_rows;$k++)
		{
			$row=mysqli_fetch_array($res);
			$i2=$i1+$k;
			$catalog[$i2]=$row;
			$catalog[$i2]["level"]=$j;
			$sqlu="update gbcatalog set level=$j where id=".$catalog[$i2]["id"];
			$resu = mysqli_query($connection, $sqlu);
		}
		
	}
}
function movemas($i1,$col)
{
	global $catalog;
/*	echo "<hr>";
	print_r($catalog);
	echo "<hr>";*/
	$count=count($catalog);
	$dif=$count-$i1;
	//echo "сдвигаем столбцов=$dif на $col <br>";
	if($dif>0)
	{
		$i2=$i1+0;
		for($k=$count;$k>$i2;$k--)
		{
			$k1=$k-1;
			$k2=$k+$col-1;
			$catalog[$k2]=$catalog[$k1];
		}
	}
	/*echo "<hr>";
	print_r($catalog);
	echo "<hr>";*/
}


?>


