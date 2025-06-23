<?php
require_once "bd_connect1.php";
require "allfunc.php";
checkAuth();
$connect = mysqli_connect(HOST, USER, PW, DB);
mysqli_set_charset($connect, "UTF8");

if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    $cat_kont = mysqli_real_escape_string($connect, $_POST['cat_kont']);
    $nane_kont = mysqli_real_escape_string($connect, $_POST['nane_kont']);
    $text_kont = mysqli_real_escape_string($connect, $_POST['text_kont']);
    $urgent_kont = mysqli_real_escape_string($connect, $_POST['urgent_kont']);
    $opis_kont = mysqli_real_escape_string($connect, $_POST['opis_kont']);

    if($_POST['cities'][0] == 0) 
    {
        $selected_pharmacies = 0;
    }
    else
    {
        $selected_pharmacies = isset($_POST['partners']) ? implode(',', $_POST['partners']) : '';
    }
    
    // Исправленный запрос на вставку данных в таблицу posts
    $query = "INSERT INTO kontakt (`id_kont`, `cat_kont`, `urgent_kont`, `nane_kont`, `text_kont`, `status_kont`, `kont_autor`, `kont_lasteditor`, `kont_visibility`, `opis_kont`)
            VALUES (NULL,". $cat_kont.",".$urgent_kont.", \"".$nane_kont."\", \"".$text_kont."\", '1',  ".$_SESSION["user_id"].", ".$_SESSION["user_id"].", \"".$selected_pharmacies."\", \"".$opis_kont."\")";
            echo $query;
    if (!($stmt = $connect->prepare($query))) {
        echo "Не удалось подготовить запрос: (" . $connect->errno . ") " . $connect->error;
    }
    if (!$stmt->execute()) {
        echo "Не удалось выполнить запрос: (" . $stmt->errno . ") " . $stmt->error;
    }else{
        header("Location:/".ADMINPHP);
    }
    mysqli_close($connect);
    

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
    </style>
</head>
<body>
<?php 
$navbarType = "admin"; 
include 'topnavbar.php';
?>
<div class="container">
    <h1>Добавить контакт</h1>
    <form action="addkont.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nane_kont">Контактное лицо</label>
            <input type="text" class="form-control" id="nane_kont" name="nane_kont" required>
        </div>
        <div class="form-group">
            <label for="short_description">Данные контакта</label>
            <input type="text" class="form-control" id="text_kont" name="text_kont" required>
        </div>
        <div class="form-group">
            <label for="short_description">Описание контакта</label>
            <input type="text" class="form-control" id="opis_kont" name="opis_kont" required>
        </div>
        <div class="form-group" style="display: flex; align-items: center;">
            <div>
                <label for="urgent_kont">Важность</label>
                <select class="form-control" id="urgent_kont" name="urgent_kont" required>
                    <option value="0">Текущий контакт</option>
                    <option value="1">Экстренный контакт</option>
                </select>
            </div>
        </div>
        
<?php
        //запрашиваем данные по категориям контактов
                
                $cat_kont_mas = [];
                
                $query_cat_kont = "SELECT * FROM cat_kontakt where status_cat_kont=1";
                $result_cat_kont = mysqli_query($connect, $query_cat_kont);
                $num_cat_kont = mysqli_num_rows($result_cat_kont);
				for($icat=0;$icat<$num_cat_kont;$icat++)
				{
					$res_cat_kont = mysqli_fetch_array($result_cat_kont);
					$cat_kont_mas[$icat]["id"] = $res_cat_kont["id_cat_kont"];
					$cat_kont_mas[$icat]["name"] = $res_cat_kont["name_cat_kont"];
				}
			?>	
        <div class="form-group">
            <label for="cat_kont">Категория контакта</label>
            <select class="form-control" id="cat_kont" name="cat_kont" required>          
<?php
            for($icat=0;$icat<$num_cat_kont;$icat++)
			{
				if($icat==1)
				{
					echo "<option value=\"".$cat_kont_mas[$icat]['id']."\" selected>".$cat_kont_mas[$icat]['name']."</option>";	
					
				}
				else
				{
					echo "<option value=\"".$cat_kont_mas[$icat]['id']."\">".$cat_kont_mas[$icat]['name']."</option>";	
					
				}
			}
?>
            </select>
        </div>
       
        <div class="form-group">
            <label for="лщте_visibility">Кому видна запись</label>
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
                $query_cities = "SELECT * FROM cities";
                $result_cities = mysqli_query($connect, $query_cities);
                while ($row_city = mysqli_fetch_assoc($result_cities)) {
                    $city_id = $row_city['idcity'];
                    $city_name = $row_city['namecity'];
                
                    // Запрос на получение аптек в данном городе
                    $query_pharmacies = "SELECT * FROM partners WHERE city = ? and type = 1 and net > 0";
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
        
        <button type="submit" class="btn btn-primary">Добавить контакт</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
<script>
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
    const allCheckbox = document.getElementById('Все'); // Checkbox for "Все"

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
        updateAllCheckboxState(); // Call this function to update the "Все" checkbox state
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
        updateAllCheckboxState(); // Call this function to update the "Все" checkbox state
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
        updateAllCheckboxState(); // Call this function to update the "Все" checkbox state
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
        updateAllCheckboxState(); // Call this function to update the "Все" checkbox state
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
            updateAllCheckboxState(); // Call this function to update the "Все" checkbox state
        });
    });

    firmpharmaciesCheckboxes.forEach(pharmacyCheckbox => {
        pharmacyCheckbox.addEventListener('change', function() {
            updateFirmPharmaciesWithSameValue();
            updateAllCheckboxState(); // Call this function to update the "Все" checkbox state
        });
    });

    citiespharmaciesCheckboxes.forEach(pharmacyCheckbox => {
        pharmacyCheckbox.addEventListener('change', function() {
            updateCityPharmaciesWithSameValue();
            updateAllCheckboxState(); // Call this function to update the "Все" checkbox state
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

