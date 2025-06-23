<?php 
//echo $_SERVER['HTTP_HOST']."<br>".$_SERVER['REQUEST_URI'];
$address1=explode("?",$_SERVER['REQUEST_URI']);
$address=$address1[0];
$masmenu[0][0]="/index.php";$masmenu[0][1]="index.php";$masmenu[0][2]="На главную";
$masmenu[1][0]="/admin.php";$masmenu[1][1]="admin.php";$masmenu[1][2]="Список фирм";
$masmenu[2][0]="/firm.php";$masmenu[2][1]="firm.php";$masmenu[2][2]="Добавление фирмы";
$masmenu[3][0]="/renew.php";$masmenu[3][1]="renew.php";$masmenu[3][2]="Обновление прайсов";
$masmenu[4][0]="/users.php";$masmenu[4][1]="users.php";$masmenu[4][2]="Пользователи";

?><div id="topmenu"><?php 
$i1=count($masmenu);
for($i=0;$i<$i1;$i++)
{
	if($address==$masmenu[$i][0])
	{
		//echo "<div id='topmenuitemactive'><a href='".$masmenu[$i][1]."'>".$masmenu[$i][2]."</a></div>";
	}
	else
	{
		//echo "<div id='topmenuitempassive'><a href='".$masmenu[$i][1]."'>".$masmenu[$i][2]."</a></div>";
	}
}
?><div id="topmenuitemlogin"><?php $cityuser=explode("||",finduser(1)); echo $cityuser[0]?></div></div>