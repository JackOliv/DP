<?php
require_once "bd_connect1.php";
require "allfunc.php";
checkAuth();
$connect = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connect, "UTF8");

if (!isset($_GET['id'])) {
    header("Location: news.php");
    exit;
}
$sql = "SELECT id, name FROM posttypes";
$resulttypes = mysqli_query($connect, $sql);
if (!$resulttypes) {
    die("Ошибка при выполнении запроса: " . mysqli_error($connect));
}
$post_id = intval($_GET['id']);
$sql = "SELECT * FROM posts WHERE id = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$q = "SELECT `visibility` FROM " . POSTSTABLE . " WHERE `id` = $post_id";
$quer = mysqli_query($connect, $q);
$res = mysqli_fetch_row($quer);
$visibility = explode(',', $res[0]);
if (!$post) {
    header("Location: news.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($connect, $_POST['title']);
    $short_description = mysqli_real_escape_string($connect, $_POST['short_description']);
    $full_description = mysqli_real_escape_string($connect, $_POST['full_description']);
    $importance = mysqli_real_escape_string($connect, $_POST['importance']);
    if($importance == "temporary"){
        $dateimportance = mysqli_real_escape_string($connect, $_POST['dateimportance']);
    }
    else{
        $dateimportance = "0001-01-01";    
    }
    $allow_comments = isset($_POST['allow_comments']) ? 1 : 0;
    $comment_visibility = mysqli_real_escape_string($connect, $_POST['comment_visibility']);
    $visibility = mysqli_real_escape_string($connect, json_encode($_POST['visibility']));
    $post_type = mysqli_real_escape_string($connect, $_POST['post_type']);
    // Собираем выбранные ID аптек в строку через запятую
    if($_POST['cities'][0] == 0) {
        $selected_pharmacies = 0;
    }
    else{
        $selected_pharmacies = isset($_POST['partners']) ? implode(',', $_POST['partners']) : '';
    }
        
    $sql_update = "UPDATE posts SET title = ?, short_description = ?, full_description = ?, importance = ? , dateimportance = ?, allow_comments = ?, comment_visibility = ?, visibility = ?, post_type = ?, lasteditor = ? WHERE id = ?";
    $stmt_update = $connect->prepare($sql_update);
    $stmt_update->bind_param("sssssisssii", $title, $short_description, $full_description, $importance, $dateimportance, $allow_comments, $comment_visibility, $selected_pharmacies, $post_type, $_SESSION["user_id"], $post_id);

    if ($stmt_update->execute()) {
        // Handling photo deletions
        $attachment_fotos = json_decode($post['attachment_fotos'], true) ?: [];
        if (isset($_POST['delete_fotos'])) {
            foreach ($_POST['delete_fotos'] as $delete_foto) {
                if (($key = array_search($delete_foto, $attachment_fotos)) !== false) {
                    unset($attachment_fotos[$key]);
                    unlink($delete_foto); // Delete the file from the server
                }
            }
        }
        if (!empty($_FILES['attachment_fotos']['name'][0])) {
            foreach ($_FILES['attachment_fotos']['name'] as $key => $filename) {
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                $new_filename = $post_id . '_foto_' . uniqid() . '.' . $extension;
                $target_dir = "news/posts/";
                $target_file = $target_dir . $new_filename;
                if (move_uploaded_file($_FILES['attachment_fotos']['tmp_name'][$key], $target_file)) {
                    $attachment_fotos[] = $target_file;
                } else {
                    echo "Ошибка при загрузке файла: " . $_FILES['attachment_fotos']['error'][$key];
                }
            }
        }

        $attachment_fotos_json = json_encode(array_values($attachment_fotos));
        $sql_update_fotos = "UPDATE posts SET attachment_fotos = ? WHERE id = ?";
        $stmt_update_fotos = $connect->prepare($sql_update_fotos);
        $stmt_update_fotos->bind_param("si", $attachment_fotos_json, $post_id);

        // Handling file deletions
        $attachment_files = json_decode($post['attachment_files'], true) ?: [];
        if (isset($_POST['delete_files'])) {
            foreach ($_POST['delete_files'] as $delete_file) {
                if (($key = array_search($delete_file, $attachment_files)) !== false) {
                    unset($attachment_files[$key]);
                    unlink($delete_file); // Delete the file from the server
                }
            }
        }
        if (!empty($_FILES['attachment_files']['name'][0])) {
            foreach ($_FILES['attachment_files']['name'] as $key => $filename) {
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                $new_filename = $post_id . '_file_' . uniqid() . '.' . $extension;
                $target_dir = "news/posts/";
                $target_file = $target_dir . $new_filename;
                if (move_uploaded_file($_FILES['attachment_files']['tmp_name'][$key], $target_file)) {
                    $attachment_files[] = $target_file;
                } else {
                    echo "Ошибка при загрузке файла: " . $_FILES['attachment_files']['error'][$key];
                }
            }
        }
        $attachment_files_json = json_encode(array_values($attachment_files));
        $sql_update_files = "UPDATE posts SET attachment_files = ? WHERE id = ?";
        $stmt_update_files = $connect->prepare($sql_update_files);
        $stmt_update_files->bind_param("si", $attachment_files_json, $post_id);
        if ($stmt_update_fotos->execute() && $stmt_update_files->execute()) {
            $post_id = $_GET['id'];
            $ip = findip();
            $action = "Редактировал запись";
            if (!is_numeric($post_id)) {
                die("Неверный ID поста.");
            }
                 $sql_insert = "INSERT INTO logs (postid, user, dateview, actions) VALUES (?, ?, NOW(),?)";
                 $stmt_insert = $connect->prepare($sql_insert);
                 if (!$stmt_insert) {
                     die("Ошибка подготовки запроса: (" . $connect->errno . ") " . $connect->error);
                 }
                 $stmt_insert->bind_param("iss", $post_id, $ip, $action);
                 if (!$stmt_insert->execute()) {
                     die("Ошибка выполнения запроса: (" . $stmt_insert->errno . ") " . $stmt_insert->error);
                 }
             
            if (isset($stmt_insert)) {
                $stmt_insert->close();
            }
            header("Location: news.php");
            exit;
        } else {
            echo "Ошибка при обновлении записи: " . mysqli_error($connect);
        }
    } else {
        echo "Ошибка: " . mysqli_error($connect);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать пост</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <style>
        .editor-container {
            height: 200px;
        }
    </style>
</head>
<body>
<?php 
$navbarType = "user"; 
include 'topnavbar.php';
?>
<div class="container">
    <h1>Редактировать пост</h1>
    <form action="editnews.php?id=<?= $post_id ?>" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Заголовок новости</label>
            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($post['title'], ENT_QUOTES) ?>" required>
        </div>
        <div class="form-group">
            <label for="short_description">Краткое описание новости</label>
            <input type="text" class="form-control" id="short_description" name="short_description" value="<?= htmlspecialchars($post['short_description'], ENT_QUOTES) ?>" required>
        </div>
        <div class="form-group">
            <label for="full_description">Полное описание новости</label>
            <div id="full_description_editor" class="editor-container"></div>
            <input type="hidden" id="full_description" name="full_description"  required>
        </div>
        <div class="form-group">
            <label for="attachment_fotos">Фото для прикрепления</label>
            <input type="file" class="form-control" id="attachment_fotos" name="attachment_fotos[]" multiple>
            <ul>
                <?php foreach (json_decode($post['attachment_fotos'], true) as $foto): ?>
                    <li>
                        <a href="<?= htmlspecialchars($foto, ENT_QUOTES) ?>" target="_blank"><?= basename($foto) ?></a>
                        <input type="checkbox" name="delete_fotos[]" value="<?= htmlspecialchars($foto, ENT_QUOTES) ?>"> Удалить
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="form-group">
            <label for="attachment_files">Файлы для прикрепления</label>
            <input type="file" class="form-control" id="attachment_files" name="attachment_files[]" multiple>
            <ul>
                <?php foreach (json_decode($post['attachment_files'], true) as $file): ?>
                    <li>
                        <a href="<?= htmlspecialchars($file, ENT_QUOTES) ?>" target="_blank"><?= basename($file) ?></a>
                        <input type="checkbox" name="delete_files[]" value="<?= htmlspecialchars($file, ENT_QUOTES) ?>"> Удалить
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="form-group" style="display: flex; align-items: center;">
            <div class="form-group">
                <label for="importance">Важность</label>
                <select class="form-control" id="importance" name="importance" required>
                    <option value="current" <?= $post['importance'] == 'current' ? 'selected' : '' ?>>Текущая новость</option>
                    <option value="important" <?= $post['importance'] == 'important' ? 'selected' : '' ?>>Важная новость</option>
                    <option value="temporary" <?= $post['importance'] == 'temporary' ? 'selected' : '' ?>>Временная новость</option>
                </select>
                </div>
                <div style="margin-left: 40px; display:none; flex-direction: column;" id="divdate" >
                    <label for="dateimportance">Выбирите дату до которой актуальна запись</label>
                    <input type="date" id="dateimportance" name="dateimportance" value="<?= htmlspecialchars($post['dateimportance'], ENT_QUOTES) ?>">
                </div>
            </div>
        <div class="form-group">
            <label for="allow_comments">Возможность писать комментарии</label>
            <input type="checkbox" id="allow_comments" name="allow_comments" <?= $post['allow_comments'] ? 'checked' : '' ?>>
        </div>
        <div class="form-group">
            <label for="comment_visibility">Публичность комментария</label>
            <select class="form-control" id="comment_visibility" name="comment_visibility" required>
                <option value="public" <?= $post['comment_visibility'] == 'public' ? 'selected' : '' ?>>Видно всем</option>
                <option value="private" <?= $post['comment_visibility'] == 'private' ? 'selected' : '' ?>>Видно только создателю статьи</option>
            </select>
        </div>
        <div class="form-group">
            <label for="post_type">Тип записи</label>
            <select class="form-control" id="post_type" name="post_type" required>
                <?php
                // Проходим по результатам запроса и генерируем <option> для каждого типа
                while ($row = mysqli_fetch_assoc($resulttypes)) {
                    $post_type_id = $row['id'];
                    $post_type_name = htmlspecialchars($row['name']);
                    // Добавляем проверку, чтобы установить выбранный тип
                    $selected = ($post['post_type'] == $post_type_id) ? 'selected' : '';
                    echo "<option value='$post_type_id' $selected>$post_type_name</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="visibility">Кому видна запись</label>
            <div >
                <input style='cursor:pointer;' type='checkbox' data-partners="0" id="Все" name='cities[]' value='0' data-city='0' 
                <?
                if( in_array(0, $visibility)) {
                            echo ' checked ' ;
                }
                ?>
                >
                <label style='cursor:pointer;' for='Все'>Все</label><ul></ul>
                <div style="display:flex; justify-content: space-between;">
                <div class='form-group'>
                <label>Выбор по городам</label>
                <div class='form-group'>
                <?php
                // Сначала создаем ассоциативный массив, в котором ключами будут ID городов, а значениями - массивы с аптеками
                $cities_with_pharmacies = [];
                // Запрос на получение всех городов
                $query_cities = "SELECT * FROM cities  ";
                $result_cities = mysqli_query($connect, $query_cities);
                while ($row_city = mysqli_fetch_assoc($result_cities)) {
                    $city_id = $row_city['idcity'];
                    $city_name = $row_city['namecity'];
                    // Запрос на получение аптек в данном городе
                    $query_pharmacies = "SELECT * FROM partners WHERE city = ? and type = 1";
                    $stmt_pharmacies = $connect->prepare($query_pharmacies);
                    $stmt_pharmacies->bind_param("i", $city_id);
                    $stmt_pharmacies->execute();
                    $result_pharmacies = $stmt_pharmacies->get_result();
                    $pharmacy_names = [];
                    while ($row_pharmacy = $result_pharmacies->fetch_assoc()) {
                        $pharmacy_names[$row_pharmacy['itemcode']] = $row_pharmacy['itemname']; // Сохраняем ID аптеки как ключ массива
                    }
                    // Добавляем город и его аптеки в массив
                    $cities_with_pharmacies[$city_name] = $pharmacy_names;
                }
                // Теперь отображаем данные на странице
                $i = 0;
                $j = 100;
                foreach ($cities_with_pharmacies as $cities_name => $pharmacies) {
                    $i++;
                    $visible = $i . "visible";
                    $visibleb = "visible".$i;
                    // Включаем идентификатор города в идентификаторы чекбоксов аптеки
                    echo "<label style='cursor:pointer' for='$i'><input type='checkbox' id='$i' name='cities[]' value='$i'>$cities_name</label>";
                    echo "<input type='button' id='$visibleb' name='visible' style='margin-left:12px' class='visible-checkbox' value='Развернуть'>";
                    echo "<ul >";
                    foreach ($pharmacies as $pharmacy_id => $pharmacy_name) {
                        $j++;
                        echo "<li style='list-style-type: none; display:none;' name='$visible'>";
                        echo "<input type='checkbox' id='$j' data-partners='$i$j' name='partners[]' value='$pharmacy_id' data-city='$i'";
                        if(in_array($pharmacy_id, $visibility) || in_array(0, $visibility)) {
                            echo 'checked' ;
                         }
                        echo "><label style='cursor:pointer; font-weight: normal;' for='$j'>$pharmacy_name</label></li>";
                        }
                    echo "</ul>";
                }
                ?>
                </div>
                </div>
                <div class="form-group">
                <label>Выбор по юр.лицам</label>
                <?php
                // Сначала создаем ассоциативный массив, в котором ключами будут ID городов, а значениями - массивы с аптеками
                $firm_with_pharmacies = [];
                // Запрос на получение всех городов
                $query_firm = "SELECT * FROM firm";
                $result_firm = mysqli_query($connect, $query_firm);
                while ($row_firm = mysqli_fetch_assoc($result_firm)) {
                    $firm_id = $row_firm['id'];
                    $firm_name = $row_firm['name'];
                    // Запрос на получение аптек в данном городе
                    $query_pharmacies = "SELECT * FROM partners WHERE firm = ? and type = 1";
                    $stmt_pharmacies = $connect->prepare($query_pharmacies);
                    $stmt_pharmacies->bind_param("i", $firm_id);
                    $stmt_pharmacies->execute();
                    $result_pharmacies = $stmt_pharmacies->get_result();
                    $pharmacy_names = [];
                    while ($row_pharmacy = $result_pharmacies->fetch_assoc()) {
                        $pharmacy_names[$row_pharmacy['itemcode']] = $row_pharmacy['itemname']; // Сохраняем ID аптеки как ключ массива
                    }
                    // Добавляем город и его аптеки в массив
                    $firm_with_pharmacies[$firm_name] = $pharmacy_names;
                }
                // Теперь отображаем данные на странице
                echo "<div class='form-group'>";
                foreach ($firm_with_pharmacies as $firm_name => $pharmacies) {
                    $i++;
                    $visible = $i . "visible";
                    $visibleb = "visible".$i;
                    // Включаем идентификатор города в идентификаторы чекбоксов аптеки
                    echo "<label style='cursor:pointer' for='$i'><input type='checkbox' id='$i' name='firm[]' value='$i'>$firm_name</label>";
                    echo "<input type='button' id='$visibleb' name='visible' style='margin-left:12px' class='visible-checkbox' value='Развернуть'>";
                    echo "<ul >";
                    foreach ($pharmacies as $pharmacy_id => $pharmacy_name) {
                        $j++;
                        echo "<li style='list-style-type: none; display:none;' name='$visible'>";
                        echo "<input type='checkbox' id='$j' data-partners='$i$j' value='$pharmacy_id' data-firm='$i'";
                         if(in_array($pharmacy_id, $visibility) || in_array(0, $visibility)) {
                            echo 'checked' ;
                         }
                        echo "><label style='cursor:pointer; font-weight: normal;' for='$j'>$pharmacy_name</label></li>";
                        }
                    echo "</ul>";
                }
                echo "</div>";
                ?>
                </div>
            </div>
        </div>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-bottom: 30px;">Сохранить изменения</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const citiesCheckboxes = document.querySelectorAll('input[name="cities[]"]');
    const pharmaciesCheckboxes = document.querySelectorAll('input[data-partners]');
    const firmpharmaciesCheckboxes = document.querySelectorAll('input[data-firm]');
    const citiespharmaciesCheckboxes = document.querySelectorAll('input[data-city]');
    const firmCheckboxes = document.querySelectorAll('input[name="firm[]"]');
    const visibilityCheckboxes = document.querySelectorAll('input[name="visible"]');
    const datepicker = document.querySelector('input[name="dateimportance"]');
    const combobox = document.getElementById('importance');
    const divdate =document.getElementById('divdate');
    visibilityCheckboxes.forEach(button => {
        button.addEventListener('click', function() {
            firmId =this.id;
            firmId = firmId.replace(/\D/g, ''); 
            const pharmaciesList = document.getElementsByName(firmId +'visible');
            if (this.click && this.value == "Развернуть") {
                pharmaciesList.forEach(pharmacy => pharmacy.style.display = 'block');
                this.value = "Свернуть";
            } 
            else{
                pharmaciesList.forEach(pharmacy => pharmacy.style.display = 'none');
                this.value = "Развернуть";
            }
        });
    });

    function updatePharmaciesByCity(cityCheckbox) {
        const cityId = cityCheckbox.value;
        if (cityId === '0') {
            citiesCheckboxes.forEach(city => city.checked = cityCheckbox.checked);
            pharmaciesCheckboxes.forEach(pharmacy => pharmacy.checked = cityCheckbox.checked);
        } else {
            pharmaciesCheckboxes.forEach(pharmacyCheckbox => {
                if (pharmacyCheckbox.dataset.city === cityId) {
                    pharmacyCheckbox.checked = cityCheckbox.checked;
                }
            });
        }
        updateCityAndfirmStates();
        updateCityPharmaciesWithSameValue();
        updateAllCheckboxState();
    }

    function updatePharmaciesByfirm(firmCheckbox) {
        const firmId = firmCheckbox.value;
        if (firmId === '0') {
            firmCheckboxes.forEach(firm => firm.checked = firmCheckbox.checked);
            pharmaciesCheckboxes.forEach(pharmacy => pharmacy.checked = firmCheckbox.checked);
        } else {
            pharmaciesCheckboxes.forEach(pharmacyCheckbox => {
                if (pharmacyCheckbox.dataset.firm === firmId) {
                    pharmacyCheckbox.checked = firmCheckbox.checked;
                }
            });
        }
        updateCityAndfirmStates();
        updateFirmPharmaciesWithSameValue();
        updateAllCheckboxState();
    }

function updateFirmPharmaciesWithSameValue() {
    const checkedPharmacies = new Set();
    const uncheckedPharmacies = new Set();

    firmpharmaciesCheckboxes.forEach(pharmacyCheckbox => {
        const pharmacyId = pharmacyCheckbox.value;
        if (pharmacyCheckbox.checked) {
            checkedPharmacies.add(pharmacyId);
        } else {
            uncheckedPharmacies.add(pharmacyId);
        }
    });

    pharmaciesCheckboxes.forEach(pharmacyCheckbox => {
        const pharmacyId = pharmacyCheckbox.value;
        if (pharmacyCheckbox.checked) {
            if (!checkedPharmacies.has(pharmacyId)) {
                pharmacyCheckbox.checked = false;
            }
        } else {
            if (checkedPharmacies.has(pharmacyId)) {
                pharmacyCheckbox.checked = true;
            }
        }
    });
    updateAllCheckboxState();
    updateCityAndfirmStates();
}
function updateCityPharmaciesWithSameValue() {
    const checkedPharmacies = new Set();
    const uncheckedPharmacies = new Set();

    citiespharmaciesCheckboxes.forEach(pharmacyCheckbox => {
        const pharmacyId = pharmacyCheckbox.value;
        if (pharmacyCheckbox.checked) {
            checkedPharmacies.add(pharmacyId);
        } else {
            uncheckedPharmacies.add(pharmacyId);
        }
    });

    pharmaciesCheckboxes.forEach(pharmacyCheckbox => {
        const pharmacyId = pharmacyCheckbox.value;
        if (pharmacyCheckbox.checked) {
            if (!checkedPharmacies.has(pharmacyId)) {
                pharmacyCheckbox.checked = false;
            }
        } else {
            if (checkedPharmacies.has(pharmacyId)) {
                pharmacyCheckbox.checked = true;
            }
        }
    });

    updateCityAndfirmStates();
    updateAllCheckboxState();
}

function updateCityAndfirmStates() {
    citiesCheckboxes.forEach(cityCheckbox => {
        const cityId = cityCheckbox.value;
        if (cityId !== '0') {
            const relatedPharmacies = document.querySelectorAll(`[data-city="${cityId}"]`);
            cityCheckbox.checked = Array.from(relatedPharmacies).some(pharmacy => pharmacy.checked);
        }
    });
    
    firmCheckboxes.forEach(firmCheckbox => {
        const firmId = firmCheckbox.value;
        if (firmId !== '0') {
            const relatedPharmacies = document.querySelectorAll(`[data-firm="${firmId}"]`);
            firmCheckbox.checked = Array.from(relatedPharmacies).some(pharmacy => pharmacy.checked);
        }
    });
}
function updateAllCheckboxState() {
        const allChecked = Array.from(pharmaciesCheckboxes).every(pharmacy => pharmacy.checked);
        allCheckbox.checked = allChecked;
    }
    citiesCheckboxes.forEach(cityCheckbox => {
        cityCheckbox.addEventListener('change', function() {
            updatePharmaciesByCity(this);
            updateCityPharmaciesWithSameValue();
        });
    });

    firmCheckboxes.forEach(firmCheckbox => {
        firmCheckbox.addEventListener('change', function() {
            updatePharmaciesByfirm(this);
            updateFirmPharmaciesWithSameValue();
        });
    });

    pharmaciesCheckboxes.forEach(pharmacyCheckbox => {
        pharmacyCheckbox.addEventListener('change', function() {
            updateCityAndfirmStates();
            updateAllCheckboxState();
        });
    });
    
    firmpharmaciesCheckboxes.forEach(pharmacyCheckbox => {
        pharmacyCheckbox.addEventListener('change', function() {
            updateFirmPharmaciesWithSameValue();
            updateAllCheckboxState();
        });
    });
    citiespharmaciesCheckboxes.forEach(pharmacyCheckbox => {
        pharmacyCheckbox.addEventListener('change', function() {
            updateCityPharmaciesWithSameValue();
            updateAllCheckboxState();
        });
    });
    
    if(combobox.value == "temporary"){
            divdate.style.display = 'flex';
        }
        else {
            divdate.style.display = 'none';
        }
    combobox.addEventListener('change', function() {
        if(combobox.value == "temporary"){
            divdate.style.display = 'flex';
        }
        else {
            divdate.style.display = 'none';
        }
    }) ;
     updateCityAndfirmStates();
});
    var quillFull = new Quill('#full_description_editor', {
        theme: 'snow',
        placeholder: 'Введите текст...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],    
                ['blockquote'],
                ['link', 'video'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'list': 'check' }],
                [{ 'indent': '-1' }, { 'indent': '+1' }],     
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }], 
                [{ 'color': [] }, { 'background': [] }],          
                [{ 'font': [] }],
                [{ 'align': [] }],
                ['clean']          
            ]
        }
    });
    var fullDescription = '<?= $post['full_description'] ?>';
    quillFull.clipboard.dangerouslyPasteHTML(fullDescription);
    document.querySelector('form').onsubmit = function() {
        document.querySelector('#full_description').value = quillFull.root.innerHTML;
    };
</script>
</body>
</html>
