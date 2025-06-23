<?php 
require_once "bd_connect1.php";
require "allfunc.php";

$connect = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connect, "UTF8");
$ispartner = isset($_GET['partnerid']);
if($ispartner){
    $partner = $_GET['partnerid'];
}

// Получаем категории ссылок
$queryCategories = "SELECT * FROM `linktypes`";
$resultCategories = mysqli_query($connect, $queryCategories);

$categories = [];
while ($row = mysqli_fetch_assoc($resultCategories)) {
    $categories[$row['id']] = $row['name'];
}

// Получаем ссылки и сортируем по категориям
$queryLinks = "SELECT * FROM `" . LINKSTABLE . "`";
$resultLinks = mysqli_query($connect, $queryLinks);

$linksByCategory = [];
while ($row = mysqli_fetch_assoc($resultLinks)) {
    $linksByCategory[$row['linktype_id']][] = $row;
}

mysqli_free_result($resultCategories);
mysqli_free_result($resultLinks);
mysqli_close($connect);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Полезные ссылки</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <style>
        html {
        scroll-behavior: smooth;
        }
        h3 {
            text-align: center;
            font-weight: bold;
            margin-top: 20px;
        }

        .category-title {
            font-size: 22px;
            font-weight: bold;
            margin-top: 30px;
            border-bottom: 2px solid #ccc;
            padding-bottom: 5px;
        }

        .links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(500px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .link-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            background: #f9f9f9;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .link-title a {
            font-size: 18px;
            font-weight: bold;
        }

        .link-url a {
            word-break: break-word;
            margin-bottom: 10px;
        }

        .btn-group {
            margin-top: 10px;
        }
        .cont{
            margin: 10px;
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
    </style>
</head>
<body>

<?php 
$navbarType = "user"; 
include 'topnavbar.php';
?>
<div class="cont">
    <h3>Полезные ссылки</h3>
    <!-- Кнопки-якоря -->
    <div class="d-flex justify-content-between align-items-center flex-wrap" style="margin-bottom: 10px;">
        <div class="btn-group">
            <?php foreach ($categories as $categoryId => $categoryName): ?>
                <?php if (!empty($linksByCategory[$categoryId])): ?>
                    <a class="btn btn-info" href="#category-<?= $categoryId ?>"><?= htmlspecialchars($categoryName) ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php if(isset($_SESSION["user_id"])): ?>
        <div class="d-flex">
            <div class="btn-group">
                <a href="alllinks.php" style="margin-right: 10px;" class="btn btn-success add-btn">Ссылки аптек</a>
            </div>
        </div>
        <?php endif ?>
    </div>
    <?php foreach ($categories as $categoryId => $categoryName): ?>
        <?php if (!empty($linksByCategory[$categoryId])): ?>
            <div id="category-<?= $categoryId ?>" class="category-title"><?= htmlspecialchars($categoryName) ?></div>
            <div class="links-grid">
                <?php foreach ($linksByCategory[$categoryId] as $one): ?>
                    <div class="link-card">
                        <div class="link-title">
                            <a target="_blank" href="<?= htmlspecialchars($one['url']) ?>"><?= htmlspecialchars($one['name']) ?></a>
                        </div>
                        <div class="link-url">
                            <a target="_blank" href="<?= htmlspecialchars($one['url']) ?>"><?= htmlspecialchars($one['url']) ?></a>
                        </div>
                        <p><?= htmlspecialchars($one['description']) ?></p>
                        <div class="btn-group">
                            <a target="_blank" href="<?= htmlspecialchars($one['url']) ?>" style="margin-right: 20px;" class="btn btn-primary">Открыть ссылку</a>
                            <?php if ($one['can_employees_add']): ?>
                                <a href="linkpas.php?id=<?= $one['id'] ?><?php if($ispartner){ echo "&partnerid=" . $partner;}?>" class="btn btn-success">Данные для входа</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<script src="/js/jquery-3.2.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
</body>
</html>
