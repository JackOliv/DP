<?php
    require_once "bd_connect1.php";
    require "allfunc.php";
    $isUser = false;
    if (isset($_SESSION["user_id"])) {
        $isUser = true;
    }
    $connect = mysqli_connect(HOST, USER, PW, DB);
    mysqli_set_charset($connect, "UTF8");
   
    
    $_SESSION['previous_page'] = $_SERVER['HTTP_REFERER'] ?? 'news.php';
    $itemcode = finduser(3);
    if (!($itemcode == 100000 || $itemcode == 20000)){
        $queryfirm = "SELECT p.firm FROM `" . PARTNERSTABLE . "` p
                      WHERE p.itemcode = ?";
        $stmt = mysqli_prepare($connect, $queryfirm);
        mysqli_stmt_bind_param($stmt, 's', $itemcode);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $firmid);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        $querypartners = "SELECT p.id, p.itemname, p.net, p.phone, c.namecity, c.ipcity, b.name as brandname, f.name 
            FROM `" . PARTNERSTABLE . "` p 
            JOIN `" . CITIESTABLE . "` c ON p.city = c.idcity
            JOIN `" . BRANDTABLE . "` b ON p.brand = b.id
            JOIN `" . FIRMTABLE . "` f ON p.firm = f.id      
            WHERE type = 1 AND p.firm = ?
            ORDER BY f.name, c.namecity DESC, p.itemname, p.net";
        $stmt2 = mysqli_prepare($connect, $querypartners);
        mysqli_stmt_bind_param($stmt2, 'i', $firmid); // Привязываем firmid
        mysqli_stmt_execute($stmt2);
        $resultpartners = mysqli_stmt_get_result($stmt2);
    }
else{
    $querypartners = "SELECT p.id, p.itemname, p.net, p.phone, c.namecity, c.ipcity, b.name as brandname, f.name FROM `" . PARTNERSTABLE . "` p 
                      JOIN `" . CITIESTABLE . "` c ON p.city = c.idcity
                      JOIN `" . BRANDTABLE . "` b ON p.brand = b.id
                      JOIN `" . FIRMTABLE . "` f ON p.firm = f.id
                      WHERE type = 1 
                      ORDER BY  f.name, c.namecity DESC, p.itemname, p.net";
    $resultpartners = mysqli_query($connect, $querypartners);
    if (!$resultpartners) {
        die("Ошибка запроса: " . mysqli_error($connect));
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Все аптеки</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <script src="/js/jquery-3.2.1.js"></script>
</head>
<body>
<?php 
$navbarType = "user"; 
include 'topnavbar.php';
?>  
<a href="kontakts.php" class="btn btn-primary">
        <i class="fas fa-arrow-left"></i> Назад
</a>
    <div class="container">
    <h3 class="mt-4">Все аптеки</h3>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Юр. лицо</th>
                <th>Город</th>
                <th>Название</th>
                <th>Телефон</th>
                <th>Бренд</th>
                <?php if ($isUser): ?>
                    <th>visibility</th>
                    <th>IP-адрес</th>
                    <th></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($resultpartners)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['namecity'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['itemname'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['phone'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['brandname'], ENT_QUOTES) ?></td>
                    <?php if ($isUser): ?>
                    <td><?= finduserbyip(htmlspecialchars($row['ipcity'] . $row['net'] . '.100', ENT_QUOTES)) ?></td>
                    <td><?= htmlspecialchars($row['ipcity'] . $row['net'] . '.*', ENT_QUOTES) ?></td>
                    <td><a href="kontakts.php?partnerid=<?= htmlspecialchars($row['id'], ENT_QUOTES) ?>" class="btn btn-warning">Просмотреть контакты</a></td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</html>