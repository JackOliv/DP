<?php
// Путь для сохранения загруженных изображений
$targetDir  = 'news/posts/';

$fileName = basename($_FILES["file"]["name"]);
$targetFilePath = $targetDir . $fileName;
$fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

// Проверяем, действительно ли файл изображения
$allowTypes = array('jpg', 'jpeg', 'png', 'gif');
if (in_array($fileType, $allowTypes)) {
    // Загружаем файл на сервер
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {
        echo $targetFilePath; // Возвращаем путь к загруженному изображению
    } else {
        echo "Ошибка при загрузке файла.";
    }
} else {
    echo "Допустимы только файлы JPG, JPEG, PNG и GIF.";
}
?>