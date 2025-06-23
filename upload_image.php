<?php
// Путь для сохранения загруженных изображений
$uploadDirectory = 'news/posts/';

// Создаем папку для загрузки, если она не существует
if (!file_exists($uploadDirectory)) {
    mkdir($uploadDirectory, 0777, true);
}

// Массив для хранения информации о загруженных файлах
$uploadedFiles = [];

// Перебираем все загруженные файлы
foreach ($_FILES['image']['tmp_name'] as $key => $tmp_name) {
    $filename = $_FILES['image']['name'][$key];
    $file_tmp = $_FILES['image']['tmp_name'][$key];
    $filetype = $_FILES['image']['type'][$key];
    $filesize = $_FILES['image']['size'][$key];
    
    // Генерируем уникальное имя для файла
    $uniqueFilename = uniqid() . '_' . $filename;
    
    // Полный путь для сохранения файла
    $targetPath = $uploadDirectory . $uniqueFilename;
    
    // Перемещаем файл в папку назначения
    if (move_uploaded_file($file_tmp, $targetPath)) {
        $uploadedFiles[] = [
            'filename' => $filename,
            'path' => $targetPath
        ];
    }
}

// Возвращаем информацию о загруженных файлах в формате JSON
header('Content-Type: application/json');
echo json_encode($uploadedFiles);
?>
