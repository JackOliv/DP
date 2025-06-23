<?php
    require_once "bd_connect1.php";
    require "allfunc.php";
    $connect = mysqli_connect(HOST, USER, PW, DB);
    mysqli_set_charset($connect, "UTF8");
    $retlink = "";
    $retlinkV = "";
    $isadmin = 0;
    $ispartner = isset($_GET['partnerid']);
    $isReturn = isset($_GET['ret']);
    if($ispartner){
        $partner = $_GET['partnerid'];
        $retlink = "&partnerid=" . $partner;
        $retlinkV = "?partnerid=" . $partner;
    
       
        $partnerId = $_GET['partnerid'];
        $querypartners = "SELECT p.id, p.itemname, p.net, p.phone, c.namecity, c.ipcity FROM `" . PARTNERSTABLE . "` p 
                      JOIN `" . CITIESTABLE . "` c ON p.city = c.idcity
                      WHERE p.id = '" . $partnerId .     "'";

        $resultpartners = mysqli_query($connect, $querypartners);
        if (!$resultpartners) {
            die("Ошибка запроса: " . mysqli_error($connect));
        }
        $row = mysqli_fetch_assoc($resultpartners);
        $partnerIp = $row['ipcity'] . $row['net'] . ".100";
        $result = finduserbyip($partnerIp);
        $parts = explode("||", $result);
        $userIP = trim(end($parts));
        $partnerName = $row['namecity'] ." ". $row['itemname'];
    }
    else{
        $userIP = finduser(3);
    }
    $islogin = 0;
    if(isset($_SESSION['login'])){
        $islogin = 1;
        if($_SESSION['login'] == 'admin'){
            $isadmin = 1;
        }
    }
    $usercityid = findcity(1);
    $querycity = "SELECT * FROM `" . KONTAKTTABLE . "` 
              WHERE see_kont=" . ($isReturn ? 1 : 0) . " 
              AND (
                  kont_visibility = '0' 
                  OR FIND_IN_SET('$userIP', kont_visibility)
              ) 
              ORDER BY cat_kont, urgent_kont DESC";


    $resultkont = mysqli_query($connect, $querycity);

    if (!$resultkont) {
        die("Ошибка запроса: " . mysqli_error($connect));
    }

    $konts = [];
    while ($rescity = mysqli_fetch_assoc($resultkont)) {
        $konts[] = $rescity;
    }
    mysqli_free_result($resultkont);
    // Получаем все категории и их флаг can_employees_add
    $query_cat_kont = "SELECT * FROM " . CATKONTAKTTABLE . " 
                   WHERE '$usercityid' = '0' 
                   OR city_cat_kont = '0' 
                   OR city_cat_kont = '$usercityid' 
                   OR FIND_IN_SET('$usercityid', city_cat_kont)";

    $result_cat_kont = mysqli_query($connect, $query_cat_kont);

    if (!$result_cat_kont) {
        die("Ошибка запроса: " . mysqli_error($connect));
    }

    $_cat_kont = [];
    while ($res_cat_kont = mysqli_fetch_assoc($result_cat_kont)) {
        $_cat_kont[$res_cat_kont["id_cat_kont"]] = [
            "name" => $res_cat_kont["name_cat_kont"],
            "can_add" => (bool) $res_cat_kont["can_employees_add"]
        ];
    }
    mysqli_free_result($result_cat_kont);

    if (isset($_GET['hide_id'])) {
        $hide_id = intval($_GET['hide_id']);
        $query = "UPDATE `" . KONTAKTTABLE . "` SET see_kont = 1 WHERE id_kont = $hide_id";
        mysqli_query($connect, $query);
        header('Location: kontakts.php' . ($ispartner ? "?partnerid=" . $_GET['partnerid'] : ""));
        exit;
    }
    if (isset($_GET['delete_id'])) {
        $delete_id = intval($_GET['delete_id']);
        $query = "DELETE FROM `" . KONTAKTTABLE . "` WHERE id_kont = $delete_id";
        mysqli_query($connect, $query);
        header('Location: kontakts.php?ret=1' . ($ispartner ? "&partnerid=" . $_GET['partnerid'] : ""));
        exit;
    }
    if (isset($_GET['return_id'])) {
        $return_id = intval($_GET['return_id']);
        $query = "UPDATE `" . KONTAKTTABLE . "` SET see_kont = 0 WHERE id_kont = $return_id";
        mysqli_query($connect, $query);
        header('Location: kontakts.php?ret=1' . ($ispartner ? "&partnerid=" . $_GET['partnerid'] : ""));
        exit;
    }
    
    mysqli_close($connect);

    // Разделяем контакты на срочные и обычные
    $urgentContacts = [];
    $regularContacts = [];
    
    foreach ($konts as $one) {
        if ($one['urgent_kont'] == 1) {
            $urgentContacts[] = $one;
            $regularContacts[] = $one;
        } else {
            $regularContacts[] = $one;
        }
    }
    $urgentContacts = array_filter($urgentContacts, function ($one) use ($_cat_kont) {
        return isset($_cat_kont[$one['cat_kont']]);
    });
    $regularContacts = array_filter($regularContacts, function ($one) use ($_cat_kont) {
        return isset($_cat_kont[$one['cat_kont']]);
    }); 
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление Контактами</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <script src="/js/jquery-3.2.1.js"></script>
    <script>
        function hideContact(id) {
            if (confirm("Вы уверены, что хотите удалить этот контакт?")) {
                window.location.href = "?hide_id=" + id + "<?php echo $retlink; ?>";
            }
        }
        function deleteContact(id) {
            if (confirm("Вы уверены, что хотите окончательно удалить этот контакт?")) {
                window.location.href = "?delete_id=" + id + "<?php echo $retlink; ?>";
            }
        }
        function returnContact(id) {
            if (confirm("Вы уверены, что хотите вернуть этот контакт?")) {
                window.location.href = "?return_id=" + id + "<?php echo $retlink; ?>";
            }
        }
        function scrollToCategory(categoryId) {
            document.getElementById(categoryId).scrollIntoView({ behavior: 'smooth' });
        }
    </script>
    <style>
        html {
        scroll-behavior: smooth;
        }
        h3{
            text-align: center;
            font-weight: bold;
        }
        .category-header {
            font-size: 1.5rem;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .category-header .category-name {
            font-weight: bold;
        }

        .category-header i {
            margin-right: 8px;
        }

        .category-header .add-btn {
            font-size: 1.2rem;
        }

        .contact-item {
            padding: 15px;
            border: 1px solid #ddd;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 5px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .contact-item .contact-info i {
            margin-right: 8px;
        }

        .contact-item .contact-actions button {
            margin-left: 5px;
        }

        .contact-columns {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 5px;
        }

        .contact-column {
            width: 100%;
        }
        .d-flex {
    display: flex;
    align-items: center;
    }
    .justify-content-between {
        justify-content: space-between;
    }
    .justify-content-end {
        justify-content: flex-end;
    }
    .btn-group {
        display: flex;
        gap: 10px; /* Отступ между кнопками */
    }
    .flex-wrap {
        flex-wrap: wrap;
    }

    .btn-group a {
        text-decoration: none;
    }

    .d-flex > .btn-group {
        margin-right: 0; /* Убираем отступ справа */
    }
        @media (min-width: 568px) {
            .contact-column {
                width: 49%; 
            }
        }

        @media (min-width: 1424px) {
            .contact-column {
                width: 33%; 
            }
        }
    .cont{
        margin: 10px;
    }
    </style>
</head>
<body>
<?php 
$navbarType = "user"; 
include 'topnavbar.php';
?>  
<div class="cont">
    <h3>Контакты <?php if(isset($_GET['partnerid'])){
        echo $partnerName;
    }?></h3>
    <div class="d-flex justify-content-between align-items-center flex-wrap" style="margin-bottom: 10px;">
        <div class="btn-group">
            <?php foreach ($_cat_kont as $id => $category): ?>
                <?php 
                $hasContacts = false;
                foreach ($regularContacts as $one) {
                    if ($one['cat_kont'] == $id) {
                        $hasContacts = true;
                        break;
                    }
                }
                if ($hasContacts): ?>
                    <button class="btn btn-info" onclick="scrollToCategory('category-<?= $id ?>')">
                        <?= htmlspecialchars($category['name']) ?>
                    </button>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <div class="d-flex">
            <div class="btn-group">
                <a href="allkontakts.php" style="margin-right: 10px;" class="btn btn-success add-btn">Контакты аптек</a>
                <a href="formkontakts.php<?php if ($ispartner) { echo "?partner=" . $_GET['partnerid']; } ?>" style="margin-right: 10px;" class="btn btn-success add-btn">
                    <i class="fas fa-plus" ></i> Добавить контакт
                </a>
                <?php if ($isReturn): ?>
                    <a href="kontakts.php<?= $retlinkV ?>" class="btn btn-warning">Просмотреть контакты</a>
                <?php else: ?>
                    <a href="kontakts.php?ret=1<?= $retlink ?>" class="btn btn-warning">Вернуть контакт</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php if (count($urgentContacts) > 0): ?>
        <div class="category-header">
            <div class="category-name"><i class="fas fa-exclamation-circle"></i> Экстренные контакты</div>
        </div>
        <div class="contact-columns">
            <?php foreach ($urgentContacts as $one): ?>
                <div class="contact-column">
                    <div class="contact-item">
                        <div class="contact-info">
                            <strong><i class="fas fa-user"></i> <?= htmlspecialchars($one['name_kont']) ?></strong><br>
                            <i class="fas fa-phone"></i> <?= htmlspecialchars($one['text_kont']) ?><br>
                            <i class="fas fa-envelope"></i> <a href="mailto:<?= htmlspecialchars($one['email_kont']) ?>"><?= htmlspecialchars($one['email_kont']) ?></a><br>
                            <i class="fas fa-info-circle"></i> <?= htmlspecialchars($one['opis_kont']) ?>
                        </div>
                        <div class="contact-actions text-right">
                            <?php if ($_cat_kont[$one['cat_kont']]['can_add'] && !$isReturn): ?>
                                <a href="formkontakts.php?cat_kont=<?= $one['cat_kont'] ?>&id=<?= $one['id_kont'] ?><?php if ($ispartner) { echo "&partner=" . $_GET['partnerid']; } ?>" class="btn btn-warning btn-sm" style="margin-right: 30px;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="hideContact(<?= $one['id_kont'] ?>)" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php elseif ($isReturn): ?>
                                <button onclick="returnContact(<?= $one['id_kont'] ?>)" style="margin-right: 30px;" class="btn btn-success btn-sm">
                                    <i class="fas fa-undo"></i>
                                </button>
                                <button onclick="deleteContact(<?= $one['id_kont'] ?>)" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php 
    $currentCategory = null;
    foreach ($regularContacts as $one): 
        // Если категория изменилась, выводим заголовок категории
        if ($one['cat_kont'] != $currentCategory): 
            // Закрываем предыдущий контейнер контактов
            if ($currentCategory !== null) {
                echo '</div>';
            }
            $currentCategory = $one['cat_kont'];
    ?>
            <div id="category-<?= $one['cat_kont'] ?>" class="category-header">
                <div class="category-name">
                    <i class="fas fa-folder"></i> <?= $_cat_kont[$currentCategory]['name'] ?>
                </div>
            </div>
        <div class="contact-columns">
        <?php endif; ?>
            <div class="contact-column">
                <div class="contact-item">
                    <div class="contact-info">
                        <strong><i class="fas fa-user"></i> <?= htmlspecialchars($one['name_kont']) ?></strong><br>
                        <i class="fas fa-phone"></i> <?= htmlspecialchars($one['text_kont']) ?><br>
                        <i class="fas fa-envelope"></i> <a href="mailto:<?= htmlspecialchars($one['email_kont']) ?>"><?= htmlspecialchars($one['email_kont']) ?></a><br>
                        <i class="fas fa-info-circle"></i> <?= htmlspecialchars($one['opis_kont']) ?>
                    </div>
                    <div class="contact-actions text-right">
                        <?php if ($_cat_kont[$one['cat_kont']]['can_add'] && !$isReturn): ?>
                            <a href="formkontakts.php?cat_kont=<?= $one['cat_kont'] ?>&id=<?= $one['id_kont'] ?><?php if ($ispartner) { echo "&partner=" . $_GET['partnerid']; } ?>" class="btn btn-warning btn-sm" style="margin-right: 20px;">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="hideContact(<?= $one['id_kont'] ?>)" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        <?php elseif ($isReturn): ?>
                            <button onclick="returnContact(<?= $one['id_kont'] ?>)" style="margin-right: 30px;" class="btn btn-success btn-sm">
                                <i class="fas fa-undo"></i>
                            </button>
                            <button onclick="deleteContact(<?= $one['id_kont'] ?>)" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
