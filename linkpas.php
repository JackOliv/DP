<?php 
    // Подключение файла с константами для подключения к базе данных
    require_once "bd_connect1.php";
    // Подключение файла с функциями
    require "allfunc.php";
    // Подключение к базе данных
    $connect = mysqli_connect(HOST, USER, PW, DB);
    // Установка кодировки соединения
    mysqli_set_charset($connect, "UTF8");
    $ispartner = isset($_GET['partnerid']);
    $autor = findip(1);
    $partnerlink = "";
    if($ispartner){
        $partner = $_GET['partnerid'];
        $partnerId = $_GET['partnerid'];
        $querypartners = "SELECT p.id, p.itemname, p.net, p.phone, c.namecity, c.ipcity FROM `" . PARTNERSTABLE . "` p 
                      JOIN `" . CITIESTABLE . "` c ON p.city = c.idcity
                      WHERE p.id = '" . $partnerId .     "'";

        $resultpartners = mysqli_query($connect, $querypartners);
        if (!$resultpartners) {
            die("Ошибка запроса: " . mysqli_error($connect));
        }
        $row = mysqli_fetch_assoc($resultpartners);
        $autor = $row['ipcity'] . $row['net'] . ".100";
        $partnerlink = "&partnerid=" . $partner;
    }
    $autor_like = preg_replace('/\d+$/', '%', $autor);
    // SQL запрос на выборку всех записей из таблицы linkpas
    $querylinkpas = "SELECT * FROM `" . LINKPASTABLE . "` WHERE is_visible = 1 AND link_id = " . intval($_GET['id']) . " AND author LIKE  '" . $autor_like . "'";
    $querylinkpas_deleted = "SELECT * FROM `" . LINKPASTABLE . "` WHERE is_visible = 0 AND link_id = " . intval($_GET['id']) . " AND author LIKE  '" . $autor_like . "'";
    // Выполнение запроса
    $resultlinkpas = mysqli_query($connect, $querylinkpas);

    $resultlinkpas_deleted = mysqli_query($connect, $querylinkpas_deleted);
    // Проверка на ошибки при выполнении запроса
    if (!$resultlinkpas || !$resultlinkpas_deleted) {
        die("Ошибка при выполнении запроса: " . mysqli_error($connect));
    }
    
    // Получение количества строк в результате
    $num_rowslinkpas = mysqli_num_rows($resultlinkpas);

    $resultlinkpas_deleted = mysqli_query($connect, $querylinkpas_deleted);
    // Инициализация массива для хранения записей
    $linkpas = array();

    // Если есть записи, то добавляем их в массив
    if ($num_rowslinkpas > 0) {
        while ($reslinkpas = mysqli_fetch_assoc($resultlinkpas)) {
            // Проверка на ошибки при получении данных
            if ($reslinkpas === false) {
                die("Ошибка при получении данных: " . mysqli_error($connect));
            }
            // Добавление записи в массив
            $linkpas[] = $reslinkpas;
        }
    }
    $linkpas_deleted = array();
    while ($reslinkpas_deleted = mysqli_fetch_assoc($resultlinkpas_deleted)) {
        $linkpas_deleted[] = $reslinkpas_deleted;
    }
    // Освобождение памяти, занятой результатами запроса
    mysqli_free_result($resultlinkpas);

    $querylinkname = "SELECT name FROM `". LINKSTABLE . "` WHERE id = " . intval($_GET['id']) ;
    $resultlinkname = mysqli_query($connect, $querylinkname);
    if (!$resultlinkname) {
        die("Ошибка при выполнении запроса: " . mysqli_error($connect));
    }

    $num_rowslinkname = mysqli_num_rows($resultlinkname);

    $linkname = "";

    if ($num_rowslinkname > 0) {
        while ($reslinkname = mysqli_fetch_assoc($resultlinkname)) {
            // Проверка на ошибки при получении данных
            if ($reslinkname === false) {
                die("Ошибка при получении данных: " . mysqli_error($connect));
            }
            // Добавление записи в массив
            $linkname = $reslinkname['name'];
        }
    }
    mysqli_free_result($resultlinkname);
    if (isset($_GET['delete_id'])) {
        $delete_id = intval($_GET['delete_id']);
        $query = "UPDATE `" . LINKPASTABLE . "` SET is_visible = 0 WHERE id = $delete_id";
        mysqli_query($connect, $query);
        header("Location: /linkpas.php?id=". intval($_GET['id']) . $partnerlink );
        exit;
    }
    if (isset($_GET['return_id'])) {
        $delete_id = intval($_GET['return_id']);
        $query = "UPDATE `" . LINKPASTABLE . "` SET is_visible = 1 WHERE id = $delete_id";
        mysqli_query($connect, $query);
        header("Location: /linkpas.php?id=". intval($_GET['id']) . $partnerlink );
        exit;
    }
    // Закрытие соединения с базой данных
    mysqli_close($connect);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Данные для входа в <?= htmlspecialchars($linkname) ?></title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container {
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .password-card {
            border-left: 5px solid #28a4c9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background:rgba(248, 249, 250, 1);
            box-shadow: 0 4px 4px rgba(0, 0, 0, 0.1);
        }
        .password-card strong {
            color:#28a4c9;
            -webkit-user-select: none;
            -ms-user-select: none; 
            user-select: none; 
        }
        .password-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;  
        }
        .btn {
            border-radius: 5px;
        }
        .copy-btn {
            border: none;
            background: none;
            cursor: pointer;
            color:rgb(145, 145, 145);
            font-size: 16px;
        }
        .deleted {
            border-left: 5px solid rgba(40, 163, 201, 0.6);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background:rgba(209, 209, 209, 0.56);
            box-shadow: 0 4px 4px rgba(0, 0, 0, 0.1);
        }
        .deleted strong {
            color: rgba(40, 163, 201, 0.6);
            -webkit-user-select: none;
            -ms-user-select: none; 
            user-select: none; 
        }
        .deleted p {
            color: rgba(85, 85, 85, 0.64);
        }
    </style>
</head>
<body>

<?php 
$navbarType = "user"; 
include 'topnavbar.php';
?>
<div class="container">
    <div class="header-container">
        <a href="indexlinks.php<?php if($ispartner){ echo "?partnerid=" . $partner;} ?>" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Назад
        </a>
        <h3>Данные для входа в <?= htmlspecialchars($linkname) ?></h3>
        <a href="formlinkpas.php?link=<?php echo $_GET['id']; ?><?php if($ispartner){ echo "&partnerid=" . $partner;} ?>" class="btn btn-success">
            <i class="fas fa-plus"></i> Добавить
        </a>
    </div>
    <?php foreach ($linkpas as $one): ?>
        <div class="password-card">
            <p><strong>Логин:</strong> <?= htmlspecialchars($one['login']) ?>
                <button class="copy-btn" onclick="copyText('login-<?= $one['id'] ?>')">
                    <i class="fas fa-copy"></i>
                </button>
            </p>
            <p id="login-<?= $one['id'] ?>" style="display:none;"><?= htmlspecialchars($one['login']) ?></p>
            <p><strong>Пароль:</strong> <?= htmlspecialchars($one['password']) ?>
                <button class="copy-btn" onclick="copyText('password-<?= $one['id'] ?>')">
                    <i class="fas fa-copy"></i>
                </button>
            </p>
            <p id="password-<?= $one['id'] ?>" style="display:none;"><?= htmlspecialchars($one['password']) ?></p>
            <p><strong>Описание:</strong> <?= htmlspecialchars($one['description']) ?></p>
            <div class="password-actions">
                <a href="formlinkpas.php?link=<?= $_GET['id'] ?>&id=<?= $one['id'] ?><?php if($ispartner){ echo "&partnerid=" . $partner;} ?>" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Редактировать
                </a>
                <button onclick="deletelinkpass(<?= $one['id']?>, <?=intval($_GET['id'])?>)" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i> Удалить
                </button>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($linkpas) && empty($linkpas_deleted)): ?>
        <p class="text-center" style="font-size: 16px">Нет данных для входа</p>
    <?php endif; ?>
    <?php if (!(empty($linkpas) && empty($linkpas_deleted))): ?>
        <h4>Удаленные данные</h4>
    <?php endif; ?>
    
    <?php foreach ($linkpas_deleted as $one): ?>
        <div class="password-card deleted">
            <p><strong>Логин:</strong> <?= htmlspecialchars($one['login']) ?>
                <button class="copy-btn" onclick="copyText('login-<?= $one['id'] ?>')">
                    <i class="fas fa-copy"></i>
                </button>
            </p>
            <p id="login-<?= $one['id'] ?>" style="display:none;"><?= htmlspecialchars($one['login']) ?></p>
            <p><strong>Пароль:</strong> <?= htmlspecialchars($one['password']) ?>
                <button class="copy-btn" onclick="copyText('password-<?= $one['id'] ?>')">
                    <i class="fas fa-copy"></i>
                </button>
            </p>
            <p id="password-<?= $one['id'] ?>" style="display:none;"><?= htmlspecialchars($one['password']) ?></p>
            <p><strong>Описание:</strong> <?= htmlspecialchars($one['description']) ?></p>
            <div class="password-actions">
                <button onclick="returnlinkpass(<?= $one['id']?>, <?=intval($_GET['id'])?>)" class="btn btn-success btn-sm">
                    <i class="fas fa-trash"></i> Восстановить
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script src="/js/jquery-3.2.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script>
    function deletelinkpass(id, linkid) {
        if (confirm("Вы уверены, что хотите удалить эти данные для входа?")) {
            let partnerid = new URLSearchParams(window.location.search).get('partnerid');
            let partnerParam = partnerid ? "&partnerid=" + partnerid : "";
            window.location.href = "?id=" + linkid + "&delete_id=" + id + partnerParam;
        }
    }
    function returnlinkpass(id, linkid) {
        if (confirm("Вы уверены, что хотите вернуть эти данные для входа?")) {
            let partnerid = new URLSearchParams(window.location.search).get('partnerid');
            let partnerParam = partnerid ? "&partnerid=" + partnerid : "";
            window.location.href = "?id=" + linkid + "&return_id=" + id + partnerParam;
        }
    }
    function copyText(elementId) {
        var textElement = document.getElementById(elementId);
        var tempInput = document.createElement("input");
        document.body.appendChild(tempInput);
        tempInput.value = textElement.textContent;
        tempInput.select();
        document.execCommand("copy");
        document.body.removeChild(tempInput);
    }
</script>
</body>
</html>
