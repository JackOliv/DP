<?php
$db=mysql_connect("localhost", "root", "");
mysql_query("SET NAMES 'win1251'");
mysql_query("SET CHARACTER SET 'win1251'");
mysql_set_charset('win1251' );

mysql_select_db("av_main",$db);
