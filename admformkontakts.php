<?php
require_once "bd_connect1.php";
require "allfunc.php";
checkAuth();
$connect = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connect, "UTF8");
$isEditing = isset($_GET['id']);
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
        header('Location: admkontakts.php');
        exit;
    }
}

// Получаем категории
$categories = [];
$sql_cat = "SELECT id_cat_kont, name_cat_kont FROM cat_kontakt";
$result_cat = mysqli_query($connect, $sql_cat);
while ($row = mysqli_fetch_assoc($result_cat)) {
    $categories[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name_kont = mysqli_real_escape_string($connect, $_POST['name_kont']);
    $text_kont = mysqli_real_escape_string($connect, $_POST['text_kont']);
    $opis_kont = mysqli_real_escape_string($connect, $_POST['opis_kont']);
    $cat_kont = intval($_POST['cat_kont']);
    $email_kont = mysqli_real_escape_string($connect, $_POST['email_kont']);
    $urgent_kont = isset($_POST['urgent_kont']) ? 1 : 0;
    $last_editor = findip(1);
    if ($isEditing) {
        $sql_update = "UPDATE " . KONTAKTTABLE . " SET see_kont = 1, kont_lasteditor = ? WHERE id_kont = ?";
        $stmt_update = $connect->prepare($sql_update);
        $stmt_update->bind_param("si", $last_editor, $kontakt_id);
        $stmt_update->execute();
        $stmt_update->close();
    } else {
        $sql_insert = "INSERT INTO " . KONTAKTTABLE . " (name_kont, text_kont, opis_kont, email_kont, urgent_kont, kont_autor, kont_lasteditor, kont_visibility, cat_kont) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $connect->prepare($sql_insert);
        $stmt_insert->bind_param("ssssisssi", $name_kont, $text_kont, $opis_kont, $email_kont, $urgent_kont, $last_editor, $last_editor, $kont_lasteditor, $cat_kont);
        $stmt_insert->execute();
        $stmt_insert->close();
    }
    
    header('Location: admkontakts.php');
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
$navbarType = "admin";
include 'topnavbar.php';
?>
<div class="container">
    <h1><?= $isEditing ? "Редактировать" : "Добавить" ?> контакт</h1>
    <form action="<?php echo 'admformkontakts.php' . ($isEditing ? '?id=' . $kontakt_id : ""); ?>" method="post">
        <div class="form-group">
            <label for="name_kont">Контактное лицо</label>
            <input type="text" class="form-control" id="name_kont" name="name_kont" value="<?= htmlspecialchars($kont['name_kont'], ENT_QUOTES) ?>" required>
        </div>
        <div class="form-group">
            <label for="text_kont">Номер</label>
            <input type="text" class="form-control" id="text_kont" name="text_kont" value="<?= htmlspecialchars($kont['text_kont'], ENT_QUOTES) ?>" required>
        </div>
        <div class="form-group">
            <label for="email_kont">Почта</label>
            <input type="text" class="form-control" id="email_kont" name="email_kont" value="<?= htmlspecialchars($kont['email_kont'], ENT_QUOTES) ?>" required>
        </div>
        <div class="form-group">
            <label for="opis_kont">Описание</label>
            <input type="text" class="form-control" id="opis_kont" name="opis_kont" value="<?= htmlspecialchars($kont['opis_kont'], ENT_QUOTES) ?>" required>
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
