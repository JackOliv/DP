<?php 
ini_set('error_reporting', E_ALL);ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//include "dbmain.php";
require_once "bd_connect1.php";
$db = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($db, "CP1251");

// LDAP переменные
$ldapuri = "ldap://192.168.0.101:389";  // ldap-uri

// Соединение с LDAP
$ldapconn = ldap_connect($ldapuri)
          or die("LDAP-URI некорректен");
	
?>