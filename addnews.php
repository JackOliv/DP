<?php

require_once "bd_connect1.php";
require "allfunc.php";
checkAuth();
$connect = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connect, "UTF8");
$sql = "SELECT id, name FROM posttypes";
$resulttypes = mysqli_query($connect, $sql);
if (!$resulttypes) {
    die("Ошибка при выполнении запроса: " . mysqli_error($connect));
}
$surveyTypeQuery = "SELECT id FROM posttypes WHERE code = 'survey'";
$surveyTypeResult = mysqli_query($connect, $surveyTypeQuery);
$surveyType = mysqli_fetch_assoc($surveyTypeResult)['id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {   
    $title = mysqli_real_escape_string($connect, $_POST['title']);
    $short_description = mysqli_real_escape_string($connect, $_POST['short_description']);
    $full_description = mysqli_real_escape_string($connect, $_POST['full_description']);
    $importance = mysqli_real_escape_string($connect, $_POST['importance']);
    $allow_comments = isset($_POST['allow_comments']) ? 1 : 0;
    $comment_visibility = mysqli_real_escape_string($connect, $_POST['comment_visibility']);
    $post_type = mysqli_real_escape_string($connect, $_POST['post_type']);
    if($importance == "temporary"){
        $dateimportance = mysqli_real_escape_string($connect, $_POST['dateimportance']);
    }
    else{
        $dateimportance = "0001-01-01";    
    }
    if ($post_type == '2') {
        $allow_comments = 1;
    }
    if($_POST['cities'][0] == 0) {
        $selected_pharmacies = 0;
    }
    else{
        $selected_pharmacies = isset($_POST['partners']) ? implode(',', $_POST['partners']) : '';
    }
    
    // Исправленный запрос на вставку данных в таблицу posts
    $sql = "INSERT INTO posts (autor, lasteditor, title, short_description, full_description, importance, dateimportance, allow_comments, comment_visibility, visibility, post_type)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $connect->prepare($sql);
    if ($stmt === false) {
        die("Ошибка подготовки запроса: " . $connect->error);
    }

    $stmt->bind_param("iisssssisss", $_SESSION["user_id"], $_SESSION["user_id"], $title, $short_description, $full_description, $importance, $dateimportance, $allow_comments, $comment_visibility, $selected_pharmacies, $post_type);

    if ($stmt->execute()) {
        if ($post_type === 'survey' && !empty($_POST['questions'])) {
            foreach ($_POST['questions'] as $index => $question_text) {
                $stmt = $conn->prepare("INSERT INTO surveys (post_id, question) VALUES (?, ?)");
                $stmt->bind_param("is", $post_id, $question_text);
                $stmt->execute();
                $question_id = $stmt->insert_id;
                
                foreach ($_POST['answers'][$index] as $answer_text) {
                    $stmt = $conn->prepare("INSERT INTO survey_answers (question_id, answer) VALUES (?, ?)");
                    $stmt->bind_param("is", $question_id, $answer_text);
                    $stmt->execute();
                }
            }
        }
        $post_id = $stmt->insert_id;

        // Обработка файлов для прикрепления
        $attachment_fotos = [];
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
        
        $attachment_fotos_json = json_encode($attachment_fotos);
        $sql_update_fotos = "UPDATE posts SET attachment_fotos = ? WHERE id = ?";
        $stmt_update_fotos = $connect->prepare($sql_update_fotos);
        $stmt_update_fotos->bind_param("si", $attachment_fotos_json, $post_id);

        $attachment_files = [];
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

        $attachment_files_json = json_encode($attachment_files);
        $sql_update_files = "UPDATE posts SET attachment_files = ? WHERE id = ?";
        $stmt_update_files = $connect->prepare($sql_update_files);
        $stmt_update_files->bind_param("si", $attachment_files_json, $post_id);

        if ($stmt_update_fotos->execute() && $stmt_update_files->execute()) {
            $ip = findip();
            $action = "Добавил запись";

            $sql_insert = "INSERT INTO logs (postid, user, dateview, actions) VALUES (?, ?, NOW(),?)";
            $stmt_insert = $connect->prepare($sql_insert);
            if (!$stmt_insert) {
                die("Ошибка подготовки запроса: (" . $connect->errno . ") " . $connect->error);
            }
            $stmt_insert->bind_param("iss", $post_id, $ip, $action);
            if (!$stmt_insert->execute()) {
                die("Ошибка выполнения запроса: (" . $stmt_insert->errno . ") " . $stmt_insert->error);
            }

            if (!empty($_POST['questions'])) {
                foreach ($_POST['questions'] as $index => $question) {
                    $type_answer_id = isset($_POST['answer_types'][$index]) ? (int)$_POST['answer_types'][$index] : 2; // По умолчанию - "Выбор одного варианта"
            
                    // Вставка вопроса с указанием типа ответа
                    $query = "INSERT INTO surveys (post_id, question, type_answer_id) VALUES (?, ?, ?)";
                    $stmt = $connect->prepare($query);
                    $stmt->bind_param("isi", $post_id, $question, $type_answer_id);
                    $stmt->execute();
                    $question_id = $stmt->insert_id; // Получаем ID вопроса
                    $stmt->close();
            
                    // Если вопрос не текстовый (ID типа ответа != 1), обрабатываем варианты ответов
                    if ($type_answer_id != 1 && isset($_POST['answers'][$index]) && is_array($_POST['answers'][$index])) {
                        foreach ($_POST['answers'][$index] as $answer) {
                            // Вставка ответов для данного вопроса
                            $query = "INSERT INTO survey_answers (question_id, answer) VALUES (?, ?)";
                            $stmt = $connect->prepare($query);
                            $stmt->bind_param("is", $question_id, $answer);
                            $stmt->execute();
                            $stmt->close();
                        }
                    }
                }
            }

            $stmt_insert->close();
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
    <title>Добавить пост</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <style>
        .editor-container {
            height: 200px;
        }
        input[type="text"], select {
            width: 100%;
            padding: 5px;
            display: block;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            transition: 0.3s;
            outline: none;
        }

        input[type="text"]:focus, select:focus {
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.5);
        }
        .d-flex{
            margin-top: 10px;
            display: flex;
            align-self: center;
            align-items: center;
            justify-content: space-around;
        }
        

    </style>
</head>
<body>
<?php 
$navbarType = "user"; 
include 'topnavbar.php';
?>
<div class="container">
    <h1>Добавить пост</h1>
    <form action="addnews.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Заголовок новости</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="short_description">Краткое описание новости</label>
            <input type="text" class="form-control" id="short_description" name="short_description" required>
        </div>
        <div class="form-group">
            <label for="full_description">Полное описание новости</label>
            <div id="full_description_editor" class="editor-container"></div>
            <input type="hidden" id="full_description" name="full_description" required>
        </div>
        <div class="form-group">
            <label for="attachment_fotos">Фото для прикрепления</label>
            <input type="file" class="form-control" id="attachment_fotos" name="attachment_fotos[]" multiple>
        </div>
        <div class="form-group">
            <label for="attachment_files">Файлы для прикрепления</label>
            <input type="file" class="form-control" id="attachment_files" name="attachment_files[]" multiple>
        </div>
        <div class="form-group" style="display: flex; align-items: center;">
            <div>
                <label for="importance">Важность</label>
                <select class="form-control" id="importance" name="importance" required>
                    <option value="current">Текущая новость</option>
                    <option value="important">Важная новость</option>
                    <option value="temporary">Временная новость</option>
                </select>
            </div>
            <div style="margin-left: 40px; display:none; flex-direction: column;" id="divdate" >
                <label>Выбирите дату до которой актуальна запись</label>
                <input type="date" id="dateimportance" name="dateimportance">
            </div>
        </div>
        <div class="form-group">
            <label for="allow_comments">Возможность писать комментарии</label>
            <input type="checkbox" id="allow_comments" name="allow_comments" checked>
        </div>
        <div class="form-group">
            <label for="comment_visibility">Публичность комментария</label>
            <select class="form-control" id="comment_visibility" name="comment_visibility" required>
                <option value="public" selected>Видно всем</option>
                <option value="private">Видно только создателю статьи</option>
            </select>
        </div>
        <div class="form-group">
            <label for="post_type">Тип записи</label>
            <select class="form-control" id="post_type" name="post_type" required >
                <?php
                // Проходим по результатам запроса и генерируем <option> для каждого типа
                while ($row = mysqli_fetch_assoc($resulttypes)) {
                    $post_type_id = $row['id'];
                    $post_type_name = htmlspecialchars($row['name']);
                    echo "<option value='$post_type_id'>$post_type_name</option>";
                }
                ?>
            </select>
        </div>

        <div id="survey_section" style="display: none;">
            <h3>Опрос:</h3>
            <div id="questions"></div>
            <button id="add_question" type="button" style="margin: 10px 0;" class="btn btn-success">Добавить вопрос</button>
        </div>
       
        <div class="form-group">
            <label for="visibility">Кому видна запись</label>
            <div >
                
                <input style='cursor:pointer;' type='checkbox' data-partners="0" id="Все" name='cities[]' value='0' data-city='0' checked>
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
                    echo "<label style='cursor:pointer' for='$i'><input type='checkbox' id='$i' name='cities[]' checked value='$i'>$cities_name</label>";
                    echo "<input type='button' id='$visibleb' name='visible' style='margin-left:12px' class='visible-checkbox'  value='Развернуть'>";
                    echo "<ul >";
                    foreach ($pharmacies as $pharmacy_id => $pharmacy_name) {
                        $j++;
                        echo "<li style='list-style-type: none; display:none;' name='$visible'>";
                        echo "<input checked type='checkbox' id='$j' data-partners='$i$j' name='partners[]' value='$pharmacy_id' data-city='$i'>";
                        echo "<label style='cursor:pointer; font-weight: normal;' for='$j'>$pharmacy_name</label></li>";
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
                $query_firm = "SELECT * FROM firm  ";
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
                    echo "<label style='cursor:pointer' for='$i'><input type='checkbox' checked id='$i' name='firm[]' value='$i'>$firm_name</label>";
                    echo "<input type='button'  id='$visibleb' name='visible' style='margin-left:12px' class='visible-checkbox' value='Развернуть'>";
                    echo "<ul >";
                    foreach ($pharmacies as $pharmacy_id => $pharmacy_name) {
                        $j++;
                        echo "<li style='list-style-type: none; display:none;' name='$visible'>";
                        echo "<input type='checkbox' checked id='$j' data-partners='$i$j' value='$pharmacy_id' data-firm='$i'>";
                        echo "<label style='cursor:pointer; font-weight: normal;' for='$j'>$pharmacy_name</label></li>";
                        }
                    echo "</ul>";
                }
                echo "</div>";
                ?>
                </div>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Добавить пост</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
<script defer>
document.addEventListener('DOMContentLoaded', function() {
    const citiesCheckboxes = document.querySelectorAll('input[name="cities[]"]');
    const firmCheckboxes = document.querySelectorAll('input[name="firm[]"]');
    const pharmaciesCheckboxes = document.querySelectorAll('input[data-partners]');
    const citiespharmaciesCheckboxes = document.querySelectorAll('input[data-city]');
    const firmpharmaciesCheckboxes = document.querySelectorAll('input[data-firm]');
    const visibilityCheckboxes = document.querySelectorAll('input[name="visible"]');
    const datepicker = document.querySelector('input[name="dateimportance"]');
    const combobox = document.getElementById('importance');
    const divdate = document.getElementById('divdate');
    const allCheckbox = document.getElementById('Все');
    const surveyTypeId = <?php echo $surveyType; ?>;


    let postTypeSelect = document.getElementById('post_type');
    postTypeSelect.addEventListener('change', toggleSurveyFields);
    
    function toggleSurveyFields() {
        let surveySection = document.getElementById('survey_section');
        let postType = postTypeSelect.value;
        let surveyTypeId = "2";
        surveySection.style.display = postType === surveyTypeId ? 'block' : 'none';
    }

    let addQuestionButton = document.getElementById('add_question');

    if (addQuestionButton) {
        addQuestionButton.addEventListener('click', addQuestion);
    }

    function addQuestion() {
        let container = document.getElementById('questions');
        
        let div = document.createElement('div');
        let questionIndex = container.children.length; // Получаем индекс текущего вопроса
        
        div.classList.add('question');
        div.innerHTML = `
            <label>Вопрос:</label>
            <div class='d-flex'>
                <input type='text' name='questions[]' required>
                <button type='button' style='margin-left: 10px' class='btn btn-danger remove_question'>Удалить вопрос</button>
            </div>
            <br>
            <label>Формат ответа:</label>
            <select name='answer_types[${questionIndex}]' required>
                <option value='1'>Ответ текстом</option>
                <option value='2' selected>Выбор одного варианта</option>
                <option value='3'>Выбор нескольких вариантов</option>
            </select>
            <div class='answers'></div>
            <button type='button' style='margin: 10px 0' class='btn btn-xs btn-success add_answer'>Добавить ответ</button>
            
        `;
        
        container.appendChild(div);
        
        // Вешаем обработчик события на кнопку "Добавить ответ"
        div.querySelector('.add_answer').addEventListener('click', function () {
            addAnswer(this, questionIndex);
        });
    
        // Вешаем обработчик события на кнопку "Удалить вопрос"
        div.querySelector('.remove_question').addEventListener('click', function () {
            removeQuestion(this, questionIndex);
        });
    }

    function addAnswer(button, questionIndex) {
        let container = button.previousElementSibling; // Получаем .answers
        let answerType = document.querySelector(`select[name='answer_types[${questionIndex}]']`).value; // Получаем тип ответа
    
        // Если тип ответа "текст", не добавляем варианты
        if (answerType === '1') return;
    
        let div = document.createElement('div');
        div.classList.add('answer');
        if (answerType === '2' || answerType === '3') {
            div.innerHTML = `
            <div class='d-flex'>
                <label>Ответ:</label>
                <input type='text' name='answers[${questionIndex}][]' required>
                <button type='button' style='margin-left: 10px' class='btn  btn-warning remove_answer'>Удалить ответ</button>
            </div>
            `;

        } else {
            div.innerHTML = `
            <div class='d-flex'>
                <label>Ответ:</label>
                <input type='text' name='answers[${questionIndex}][]' required>
                <button type='button' style='margin-left: 10px' class='btn  btn-warning remove_answer'>Удалить ответ</button>
            </div>
            `;
        }
    
        container.appendChild(div);
    
        // Вешаем обработчик события на кнопку "Удалить ответ"
        div.querySelector('.remove_answer').addEventListener('click', function () {
            removeAnswer(this);
        });
    }
    
    function removeAnswer(button) {
        button.closest('.answer').remove(); // Удаляем ответ
    }
    
    function removeQuestion(button, questionIndex) {
        button.closest('.question').remove(); // Удаляем вопрос и все ответы, связанные с ним
    }


    visibilityCheckboxes.forEach(button => {
        button.addEventListener('click', function() {
            let firmId = this.id;
            firmId = firmId.replace(/\D/g, '');
            const pharmaciesList = document.getElementsByName(firmId + 'visible');
            if (this.click && this.value === "Развернуть") {
                pharmaciesList.forEach(pharmacy => pharmacy.style.display = 'block');
                this.value = "Свернуть";
            } else {
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
        updateCityAndFirmStates();
        updateCityPharmaciesWithSameValue();
        updateAllCheckboxState(); 
    }

    function updatePharmaciesByFirm(firmCheckbox) {
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
        updateCityAndFirmStates();
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

        updateCityAndFirmStates();
        updateAllCheckboxState(); 
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

        updateCityAndFirmStates();
        updateAllCheckboxState(); 
    }

    function updateCityAndFirmStates() {
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
            updatePharmaciesByFirm(this);
            updateFirmPharmaciesWithSameValue();
        });
    });

    pharmaciesCheckboxes.forEach(pharmacyCheckbox => {
        pharmacyCheckbox.addEventListener('change', function() {
            updateCityAndFirmStates();
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

    combobox.addEventListener('change', function() {
        if (combobox.value === "temporary") {
            divdate.style.display = 'flex';
        } else {
            divdate.style.display = 'none';
        }
    });
    var quillFull = new Quill('#full_description_editor', {
        theme: 'snow',
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

    document.querySelector('form').onsubmit = function() {
        document.querySelector('#full_description').value = quillFull.root.innerHTML;
    };
});

</script>

</body>
</html>

