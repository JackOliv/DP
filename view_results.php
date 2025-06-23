<?php
require_once "bd_connect1.php";
require "allfunc.php";
checkAuth();
$conn = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($conn, "UTF8");
$post_id = '';
if(isset($_GET['post_id'])){
    $post_id = $_GET['post_id'];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты опросов</title>
    <link rel="stylesheet" href="/css/avstyles.css">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/bootstrap-theme.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        h1 {
            font-weight: 700;
            color: #343a40;
        }

        .card {
            border-radius: 12px;
            border: none;
            transition: box-shadow 0.2s ease;
        }

        .card:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #0d6efd !important;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 1rem 1.25rem;
        }

        .card-header h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #ffffff;
        }

        .card-body {
            background-color: #ffffff;
            padding: 1.5rem;
        }

        .list-group-item {
            font-size: 14px;
            border: none;
            border-bottom: 1px solid #dee2e6;
            background-color: #fdfdfd;
        }

        .list-group-item:last-child {
            border-bottom: none;
        }

        .badge {
            font-size: 14px ;
            padding: 0.5em 0.75em;
        }

        .tx {
            font-size: 14px;
        }

        .text-muted {
            font-size: 14px;
            color: #6c757d !important;
        }

        strong {
            color: #212529;
        }
        .badge{
            background-color: #5cb85c !important;
        }
        .badge-container {
            margin-top: -20px;
            flex-grow: 1;
            display: flex;
            justify-content: flex-end;
        }

    </style>
</head>
<body class="bg-light">
    <?php 
        $navbarType = "user"; 
        include 'topnavbar.php';

        if($post_id != ''){
           echo "<a class='btn-primary btn' href='post.php?id=".$post_id."' style='margin-left:20px'>Назад</a>";
        }
    ?>
<div class="container py-5">
    <h1 class="mb-4 text-center">Результаты опроса</h1>

    <?
    if ($post_id != ''){
        $surveyQuery = $conn->query("SELECT * FROM surveys WHERE post_id = $post_id");
    }
    else{
        $surveyQuery = $conn->query("SELECT * FROM surveys");
    }
// Получаем список всех пользователей, ответивших на данный пост

if (isset($_GET['user_id'])) {
    $selected_user = $_GET['user_id'];
    $stmtUsersAll = $conn->prepare("
        SELECT sa.id, sa.answer, COUNT(ua.id) AS votes
        FROM survey_answers sa
        LEFT JOIN user_answers ua ON sa.id = ua.survey_answer_id AND ua.user = ?
        WHERE sa.question_id = ?
        GROUP BY sa.id
    ");
    $stmtUsersAll->bind_param("ii", $selected_user, $survey_id);
} else {
    $selected_user = '';
    $stmtUsersAll = $conn->prepare("
        SELECT sa.id, sa.answer, COUNT(ua.id) AS votes
        FROM survey_answers sa
        LEFT JOIN user_answers ua ON sa.id = ua.survey_answer_id
        WHERE sa.question_id = ?
        GROUP BY sa.id
    ");
    $stmtUsersAll->bind_param("i", $survey_id);
}


$stmtUsersAll->execute();
$usersAll = $stmtUsersAll->get_result();


// Получаем всех пользователей, ответивших на этот пост
$stmtUsersList = $conn->prepare("
    SELECT DISTINCT ua.user, p.itemname 
    FROM user_answers ua 
    LEFT JOIN partners p ON ua.user = p.itemcode 
    WHERE ua.survey_id IN (SELECT id FROM surveys WHERE post_id = ?)
");
$stmtUsersList->bind_param("i", $post_id);
$stmtUsersList->execute();
$usersAll = $stmtUsersList->get_result();
?>


<form method="get" class="mb-4 d-flex align-items-center justify-content-center gap-2">
    <input type="hidden" name="post_id" value="<?= htmlspecialchars($post_id) ?>">
    <label for="userSelect" class="form-label mb-0 me-2">Показать ответы пользователя:</label>
    <select name="user_id" id="userSelect" class="form-select w-auto">
        <option value="">— Все пользователи —</option>
        <?php while ($u = $usersAll->fetch_assoc()): ?>
            <?php 
                $uid = $u['user']; 
                $uname = ($uid == '100000') ? 'Офис' : htmlspecialchars($u['itemname'] ?? 'Неизвестный');
            ?>
            <option value="<?= $uid ?>" <?= ($selected_user == $uid ? 'selected' : '') ?>>
                <?= $uname ?>
            </option>
        <?php endwhile; ?>
    </select>
    <button type="submit" class="btn btn-primary">Показать</button>
</form>
<?php
    while ($survey = $surveyQuery->fetch_assoc()) {
        $survey_id = $survey['id'];
        ?>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><?= htmlspecialchars($survey['question']) ?></h5>
            </div>
            <div class="card-body">

                <?php
                // Варианты ответов
                $stmt = $conn->prepare("
                    SELECT sa.id, sa.answer, COUNT(ua.id) AS votes
                    FROM survey_answers sa
                    LEFT JOIN user_answers ua ON sa.id = ua.survey_answer_id
                    WHERE sa.question_id = ?
                    GROUP BY sa.id
                ");
                $stmt->bind_param("i", $survey_id);
                $stmt->execute();
                $result = $stmt->get_result();

                echo "<ul class='list-group mb-3'>";
                while ($row = $result->fetch_assoc()) {
                    echo "<li class='list-group-item'>";
                    echo "<div class='d-flex justify-content-between align-items-center'>";
                    echo "<span>" . htmlspecialchars($row['answer']) . "</span>";
                    echo "<div class='badge-container'>";
                    echo "<span class='badge bg-success rounded-pill'>" . $row['votes'] . "</span>";
                    echo "</div>";
                    echo "</div>";
                      
                    // Получаем пользователей, выбравших этот ответ
                    $answer_id = $row['id'];
                    if($selected_user){
                        $stmtUsers = $conn->prepare("SELECT ua.user, p.itemname FROM user_answers ua LEFT JOIN partners p ON ua.user = p.itemcode WHERE ua.survey_answer_id = ? and ua.user = $selected_user");
                    }else
                    {
                        $stmtUsers = $conn->prepare("SELECT ua.user, p.itemname FROM user_answers ua LEFT JOIN partners p ON ua.user = p.itemcode WHERE ua.survey_answer_id = ? ");
                    }
                    $stmtUsers->bind_param("i", $answer_id);
                    $stmtUsers->execute();
                    $usersResult = $stmtUsers->get_result();
                
                    $userNames = [];
                    while ($userRow = $usersResult->fetch_assoc()) {
                        $user_id = $userRow['user'];
                        $name = ($user_id == '100000') ? 'Офис' : htmlspecialchars($userRow['itemname'] ?? 'Неизвестный пользователь');
                        $userNames[] = $name;
                    }
                
                    if (!empty($userNames)) {
                        echo "<div class='tx mt-1'><strong>Ответили:</strong><small> " . implode(", ", $userNames) . "</small></div>";
                    }
                
                    echo "</li>";
                }
                
?>
            <?php if ($survey['type_answer_id'] == 1): ?>
                <?php
                if ($selected_user) {
                    $stmtText = $conn->prepare("
                        SELECT `answer`, `user` FROM user_answers
                        WHERE survey_id = ? AND survey_answer_id IS NULL AND answer != '' AND user = ?
                    ");
                    $stmtText->bind_param("ii", $survey_id, $selected_user);
                } else {
                    $stmtText = $conn->prepare("
                        SELECT `answer`, `user` FROM user_answers
                        WHERE survey_id = ? AND survey_answer_id IS NULL AND answer != ''
                    ");
                    $stmtText->bind_param("i", $survey_id);
                }
                $stmtText->execute();
                $textResults = $stmtText->get_result();
            
                if ($textResults->num_rows > 0) {
                    echo "<p class='tx'>Текстовые ответы:</p><ul class='list-group'>";
                    while ($textRow = $textResults->fetch_assoc()) {
                        $user_id = $textRow['user'];
                        $stmtName = $conn->prepare("SELECT itemname FROM partners WHERE itemcode = ?");
                        $stmtName->bind_param("i", $user_id);
                        $stmtName->execute();
                        $nameResult = $stmtName->get_result();
                        $nameRow = $nameResult->fetch_assoc();
                    
                        $username = ($textRow['user'] == '100000') ? 'Офис' : htmlspecialchars($nameRow['itemname'] ?? 'Неизвестный пользователь');
                    
                        echo "<li class='list-group-item'><strong>$username:</strong> " . htmlspecialchars($textRow['answer']) . "</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p class='text-muted'>Нет текстовых ответов.</p>";
                }
                ?>
            <?php endif; ?>

              

            </div>
        </div>

        <?php
    }

    $conn->close();
    ?>
</div>
</body>
</html>