<?php
require_once "bd_connect1.php";
require "allfunc.php";

$connect = mysqli_connect(HOST, USER, PW, DB);
if (!$connect) {
    die("Ошибка подключения к базе данных: " . mysqli_connect_error());
}
mysqli_set_charset($connect, "UTF8");

$ispartner = isset($_GET['partnerid']);

$isEditing = isset($_GET['id']);
$linkpas = [
    "login" => "",
    "password" => "",
    "description" => ""
];  
$linkpart = "";
$partner = "";
if($ispartner){ 
        $partner = $_GET['partnerid'];
        $linkpart = "&partnerid=" . $partner;
    } 
if ($isEditing) {
    $linkpas_id = intval($_GET['id']);
    
    $sql = "SELECT * FROM " . LINKPASTABLE . " WHERE id = ?";
    $stmt = $connect->prepare($sql);
    if (!$stmt) {
        die("Ошибка подготовки запроса: " . mysqli_error($connect));
    }
    $stmt->bind_param("i", $linkpas_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $linkpas = $result->fetch_assoc();  
  
    

    if (!$linkpas) {
        header('Location: linkpas.php?id='. intval($_GET['link'] . $linkpart));
        exit;
    }
} 
    if(isset($_GET['link'])){
        $link_id = intval($_GET['link']);
    }


    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $login = mysqli_real_escape_string($connect, $_POST['login']);
        $password = mysqli_real_escape_string($connect, $_POST['password']);
        $description = mysqli_real_escape_string($connect, $_POST['description']);
        $is_visible = true;
        if($ispartner){
            $partnerId = $_GET['partnerid'];
            $querypartners = "SELECT p.id, p.itemname, p.net, p.phone, c.namecity, c.ipcity FROM `" . PARTNERSTABLE . "` p 
                          JOIN `" . CITIESTABLE . "` c ON p.city = c.idcity
                          WHERE p.id = '" . $partnerId .     "'";
            $resultpartners = mysqli_query($connect, $querypartners);
            if (!$resultpartners) {
                die("Ошибка запроса: " . mysqli_error($connect));
            }
            $row = mysqli_fetch_assoc($resultpartners);
            $last_editor = $row['ipcity'] . $row['net'] . ".100";
        }
        else{
            $last_editor = findip(1);
        }
        
        if ($isEditing) {
            // Редактирование контакта
            $sql_update = "UPDATE " . LINKPASTABLE . " SET login = ?, password = ?, description = ?, last_editor = ? WHERE id = ?";
            $stmt_update = $connect->prepare($sql_update);
            $stmt_update->bind_param("ssssi", $login, $password, $description, $last_editor, $linkpas_id);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            // Проверяем, что link_id существует
            if (!isset($link_id)) {
                die("Ошибка: Параметр 'link' не передан!");
            }
            // Создание нового контакта
            $sql_insert = "INSERT INTO " . LINKPASTABLE . " (link_id, login, password, description, author, last_editor, is_visible) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $connect->prepare($sql_insert);
            $stmt_insert->bind_param("isssssi", $link_id, $login, $password, $description, $last_editor, $last_editor, $is_visible);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
    
        header('Location: linkpas.php?id='. intval($_GET['link']). $linkpart);
        exit;
    }
    

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEditing ? "Редактировать" : "Добавить" ?> данные для входа</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<?php
$navbarType = "user";
include 'topnavbar.php';
?>
<div class="header-container">
        <a href="javascript:history.back()" class="btn btn-primary" style="margin: 10px;">
            <i class="fas fa-arrow-left"></i> Назад
        </a>
    <h3 style="text-align: center;"><?= $isEditing ? "Редактировать" : "Добавить" ?> данные для входа</h3>
</div>
<div class="container">
    <form action="<?php echo 'formlinkpas.php?link=' . $link_id . ($isEditing ? '&id=' . $linkpas_id : '') . ($ispartner ? '&partnerid=' . $partner : ''); ?>" method="post">
        <div class="form-group">
            <label for="login">Логин</label>
            <input type="text" class="form-control" id="login" name="login" value="<?= htmlspecialchars($linkpas['login'], ENT_QUOTES) ?>" >
        </div>
        <div class="form-group">
            <label for="password">Пароль</label>
            <input type="text" class="form-control" id="password" name="password" value="<?= htmlspecialchars($linkpas['password'], ENT_QUOTES) ?>" >
        </div>
        <div class="form-group">
            <label for="description">Описание</label>
            <input type="text" class="form-control" id="description" name="description" value="<?= htmlspecialchars($linkpas['description'], ENT_QUOTES) ?>" >
        </div>
        <button type="submit" class="btn btn-primary"><?= $isEditing ? "Сохранить изменения" : "Добавить данные для входа" ?></button>
        </div>
    </form>
</div>
</body>
</html>
