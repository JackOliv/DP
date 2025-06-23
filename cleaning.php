<?php
define('PREFIX', '/var/www/spravka.aptekavita.ru/public_html/');
function clean($days)
{
    require_once PREFIX."bd_connect1.php";
    $connect = mysqli_connect(HOST, USER, PW, DB);
    $data = DateTime::createFromFormat("Y-m-d", date("Y-m-d"));
    $datetime1 = new DateTime(date("Y-m-d"));
    print_r($data);
    $q = "SELECT `itemcode`, `uploaddate`  FROM `" . PRICETABLE . "`;";
    $results = mysqli_query($connect, $q);
    $old = array();
    while ($result = mysqli_fetch_object($results)) {
    
        $a = DateTime::createFromFormat("Y-m-d", $result->uploaddate);
        $datetime2 = new DateTime($result->uploaddate);
        $diff = $datetime1->diff($datetime2);
        echo abs(intval($diff->format('%R%a')));
        $d = abs(intval($diff->format('%R%a')));
        if ($d > $days) {
            array_push($old, $result->itemcode);
        }
    }
    if(count($old) > 0) {
		$old = implode(", ", $old);
		$query = "DELETE FROM `" . TABLE . "` where `stock_code` in ({$old})";
		echo $query;
		mysqli_query($connect, $query);
		$delquery="UPDATE `" . PRICETABLE . "` SET uploaddate = NULL, last_status = NULL WHERE `itemcode` in ($old);";
		mysqli_query($connect, $delquery);
	}
    mysqli_close($connect);
}

if (file_exists(PREFIX."timer.txt")) {
    $file = file_get_contents(PREFIX."timer.txt");
    $content = explode(",",$file);
    if ($content[0]=="true"){
        $days = $content[1];
        clean($days);
    }
}

?>