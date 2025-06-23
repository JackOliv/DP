<?php
require_once "bd_connect1.php";
require "allfunc.php";
// Если пользователь уже авторизован — редирект на сохранённую страницу
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['previous_page'] ?? 'index.php'));
    exit;
}
// Сохраняем предыдущую страницу
if (!isset($_SESSION['previous_page']) && !empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'login.php') === false) {
    $_SESSION['previous_page'] = $_SERVER['HTTP_REFERER'];
} elseif (!isset($_SESSION['previous_page']) && strpos($_SERVER['REQUEST_URI'], 'login.php') === false) {
    $_SESSION['previous_page'] = $_SERVER['REQUEST_URI'];
}
$connect = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connect, "UTF8");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = trim($_POST["login"]);
    $password = trim($_POST["password"]);
    if (!empty($login) && !empty($password)) {
        $sql = "SELECT id, login, name, password FROM users WHERE login = ? AND password = ?";
        if ($stmt = mysqli_prepare($connect, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $login, $password);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $login, $name, $password);
                    if (mysqli_stmt_fetch($stmt)) {
                        $_SESSION["user_id"] = $id;
                        $_SESSION["login"] = $login;
                        $_SESSION["name"] = $name;
                        // Проверяем, есть ли сохранённая страница
                        $redirectPage = $_SESSION['previous_page'] ?? 'index.php';
                        unset($_SESSION['previous_page']); // Удаляем, чтобы не зациклить
                        header("Location: " . $redirectPage);
                        exit;
                    } else {
                        $login_err = "Неправильный логин или пароль";
                    }
                } else {
                    $login_err = "Неправильный логин или пароль";
                }
            } else {
                echo "Что-то пошло не так. Пожалуйста, повторите попытку позже.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $login_err = "Пожалуйста, введите логин и пароль";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
</head>
<body>
    <?php
        $navbarType = "user"; 
        include 'topnavbar.php';
    ?>
    <div class="container">
        <h2>Вход</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Логин</label>
                <input type="text" name="login" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Войти">
            </div>
            <?php
            if (!empty($login_err)) {
                echo '<div class="alert alert-danger">' . $login_err . '</div>';
            }
            ?>
        </form>
    </div>
</body>
</html>