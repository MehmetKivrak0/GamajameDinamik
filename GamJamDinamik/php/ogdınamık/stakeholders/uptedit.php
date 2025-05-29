<?php


require_once '../../config.php';

$id = $_POST['id'];
$name = $_POST['name'];
$desc = $_POST['description'];

if (!empty($_FILES['image']['name'])) {
    $originalName = basename($_FILES['image']['name']);
    $uploadDir = '../../../php/gallery/payduplouds';

    // Dizindeki dosyaları kontrol et
    $files = scandir($uploadDir);
    $existingFile = null;

    foreach ($files as $file) {
        if (strpos($file, $originalName) !== false) {
            $existingFile = $file;
            break;
        }
    }

    if ($existingFile) {
        $filename = $existingFile;
    } else {
        $filename = uniqid() . "_" . $originalName;
    }
    
    move_uploaded_file($_FILES['image']['tmp_name'], "$uploadDir/$filename");

    $stmt = $pdo->prepare("UPDATE stakeholders SET name=?, description=?, image_filename=? WHERE id=?");
    $stmt->execute([$name, $desc, $filename, $id]);
} else {
    $stmt = $pdo->prepare("UPDATE stakeholders SET name=?, description=? WHERE id=?");
    $stmt->execute([$name, $desc, $id]);
}

$_SESSION['success'] = 'Paydaş başarıyla güncellendi.';
header("Location: ../../../rolweb/admin.php#paydas");
exit;
?>