<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <title>Обновление ассортимента</title>
</head>
<body>
<a href="index.php" class="btn btn-default" style="margin:0px 10px 10px 10px; float: right;">На главную</a>
<form class="form-inline" action="" method="POST" style="margin-left:10px">
    <h4>Имя файла</h4>
    <div class="form-group">
        <input type="text" class="form-control" name="namedb" />
    </div>
    <button type="submit" class="btn btn-default" name = "submit">Обновить</button>
</form>
</body>
</html>
<?php
function refresh_db($remfilename)
{
    require_once "bd_connect.php";

    $target = fopen(LOCFILENAME, "w");
    $fconn = ftp_connect(FTPSERVER) or die("Could not connect");
    ftp_login($fconn, FNAME, FPW);
    if (ftp_fget($fconn, $target, $remfilename, FTP_ASCII)) {
        echo "File downloaded";
    } else {
        echo "ERROR";
    }

    $conn = @mysqli_connect(HOST, USER, PW, DB) or die(mysqli_error());
    $delquery = "DELETE FROM `" . TABLE . "` WHERE 1 ;";
    mysqli_query($conn, $delquery);
    $insquery = "LOAD DATA INFILE " . LOCFILENAME . " INTO TABLE `" . TABLE . "` FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\r\n'IGNORE 1 LINES;";
    echo "<br />$insquery <br />";
    mysqli_query($conn, $insquery);

    ftp_close($fconn);
    fclose($target);
    mysqli_close($conn);
}
if ($_POST["submit"]){
    refresh_db($_POST["namedb"]);
}
?>