<?php
require_once "bd_connect1.php";
require "allfunc.php";

$connect = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connect, "UTF8");
$isEditing = isset($_GET['id']);
$ispartner = isset($_GET['partner']);
$kont = [
    "name_kont" => "",
    "text_kont" => "",
    "opis_kont" => "",
    "email_kont" => "",
    "cat_kont" => ""
];
if ($isEditing) {
    $kontakt_id = intval($_GET['id']);
    
    $sql = "SELECT * FROM " . KONTAKTTABLE . " WHERE id_kont = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $kontakt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $kont = $result->fetch_assoc();

    if (!$kont) {
        header('Location: kontakts.php'. $retlink);
        exit;
    }
}
if ($ispartner) {
    $partner_id = intval($_GET['partner']);
    
    $sql = "SELECT p.net, c.ipcity FROM `" . PARTNERSTABLE . "` p 
            JOIN `" . CITIESTABLE . "` c ON p.city = c.idcity
            WHERE type = 1 AND id = ?";
    
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $partner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $part = $result->fetch_assoc();

    if (!$part) {
        header('Location: kontakts.php'. $retlink );
        exit;
    }

    $ip_address = htmlspecialchars($part['ipcity'] . $part['net'] . '.100', ENT_QUOTES);
    $retlink = "?partnerid=" . $_GET['partner'];
}else{
    $retlink = "";
}

$params = [];

if ($isEditing) {
    $params['id'] = $kontakt_id;
}
if ($ispartner) {
    $params['partner'] = $partner_id;
}

$actionUrl = 'formkontakts.php' . (!empty($params) ? '?' . http_build_query($params) : '');
$categories = [];
$sql_cat = "SELECT id_cat_kont, name_cat_kont FROM cat_kontakt WHERE can_employees_add = 1";
$result_cat = mysqli_query($connect, $sql_cat);
while ($row = mysqli_fetch_assoc($result_cat)) {
    $categories[] = $row;
}

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name_kont = mysqli_real_escape_string($connect, $_POST['name_kont']);
        $text_kont = mysqli_real_escape_string($connect, $_POST['text_kont']);
        $opis_kont = mysqli_real_escape_string($connect, $_POST['opis_kont']);
        $email_kont = mysqli_real_escape_string($connect, $_POST['email_kont']);
        $cat_kont = intval($_POST['cat_kont']);
        // Проверяем, был ли установлен чекбокс для экстренного контакта
        $urgent_kont = isset($_POST['urgent_kont']) ? 1 : 0;
        $status = 1; 
        $see = 0; 
        $last_editor = findip(1);
        if($ispartner){
            $visibility = finduserbyip($ip_address);
        }
        else{
            $visibility = finduser(3); 
        }
        if ($isEditing) {
            // Редактирование контакта
            $sql_update = "UPDATE " . KONTAKTTABLE . " SET see_kont = 1, kont_lasteditor = ? WHERE id_kont = ?";
            $stmt_update = $connect->prepare($sql_update);
            
            
            $stmt_update->bind_param("si", $last_editor, $kontakt_id);
            $stmt_update->execute();
            $stmt_update->close();
        } 
        
        // Создание нового контакта
        $sql_insert = "INSERT INTO " . KONTAKTTABLE . " (name_kont, text_kont, opis_kont, email_kont, urgent_kont, kont_autor, kont_lasteditor, kont_visibility, cat_kont, status_kont, see_kont) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $connect->prepare($sql_insert);
            
        if (!$stmt_insert) {
            die("Ошибка в prepare(): " . $connect->error);
        }
        
        $stmt_insert->bind_param("ssssisssiii", $name_kont, $text_kont, $opis_kont, $email_kont, $urgent_kont, $last_editor, $last_editor, $visibility, $cat_kont, $status, $see);
        
        if (!$stmt_insert->execute()) {
            die("Ошибка при выполнении execute(): " . $stmt_insert->error);
        } else {
            echo "✅ Успешно отправлено в базу.";
        }

        $stmt_insert->close();
        
        header('Location: kontakts.php'. $retlink);
        exit;
    }
    
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEditing ? "Редактировать" : "Добавить" ?> контакт</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
</head>
<body>
<?php
$navbarType = "user";
include 'topnavbar.php';
?>
<a href="kontakts.php <?=$retlink?>" class="btn btn-primary">
        <i class="fas fa-arrow-left"></i> Назад
</a>
<div class="container">
    <h1><?= $isEditing ? "Редактировать" : "Добавить" ?> контакт</h1>
    <form action="<?= htmlspecialchars($actionUrl) ?>" method="post">
        <div class="form-group">
            <label for="name_kont">Контактное лицо</label>
            <input type="text" class="form-control" id="name_kont" name="name_kont" value="<?= htmlspecialchars($kont['name_kont'], ENT_QUOTES) ?>" >
        </div>
        <div class="form-group">
            <label for="text_kont">Номер</label>
            <input type="text" class="form-control" id="text_kont" name="text_kont" value="<?= htmlspecialchars($kont['text_kont'], ENT_QUOTES) ?>" >
        </div>
        <div class="form-group">
            <label for="email_kont">Почта</label>
            <input type="text" class="form-control" id="email_kont" name="email_kont" value="<?= htmlspecialchars($kont['email_kont'], ENT_QUOTES) ?>" >
        </div>
        <div class="form-group">
            <label for="opis_kont">Описание</label>
            <input type="text" class="form-control" id="opis_kont" name="opis_kont" value="<?= htmlspecialchars($kont['opis_kont'], ENT_QUOTES) ?>" >
        </div>
        <div class="form-group">
            <label for="cat_kont">Категория</label>
            <select class="form-control" id="cat_kont" name="cat_kont" required>
                <option value="">Выберите категорию</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id_cat_kont'] ?>" <?= ($kont['cat_kont'] == $category['id_cat_kont']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name_cat_kont'], ENT_QUOTES) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <input type="checkbox" id="urgent_kont" name="urgent_kont" style="width: 20px;" value="<?= htmlspecialchars($kont['urgent_kont'], ENT_QUOTES) ?>">
            <label for="urgent_kont">Экстренный контакт?</label>
        </div>
        <button type="submit" class="btn btn-primary"><?= $isEditing ? "Сохранить изменения" : "Добавить контакт" ?></button>
    </form>
</div>
</body>
</html>
