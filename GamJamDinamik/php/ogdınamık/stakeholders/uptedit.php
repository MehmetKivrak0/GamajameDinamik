<?php
session_start();
require_once '../../config.php';

try {
    // Form verilerini al ve güvenli hale getir
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $desc = filter_var($_POST['description'], FILTER_SANITIZE_STRING);

    if (!$id || !$name || !$desc) {
        throw new Exception("Geçersiz form verileri.");
    }

    // Mevcut görseli al
    $stmt = $pdo->prepare("SELECT image_filename FROM stakeholders WHERE id = ?");
    $stmt->execute([$id]);
    $currentImage = $stmt->fetchColumn();

    // Görsel kaldırma isteği varsa
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
        // Mevcut görseli sil
        if ($currentImage && file_exists("../../../php/gallery/payduplouds/$currentImage")) {
            unlink("../../../php/gallery/payduplouds/$currentImage");
        }
        
        // Veritabanında görsel alanını boşalt
        $stmt = $pdo->prepare("UPDATE stakeholders SET name=?, description=?, image_filename=NULL WHERE id=?");
        $stmt->execute([$name, $desc, $id]);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Paydaş başarıyla güncellendi ve görsel kaldırıldı.'
        ]);
        exit;
    }
    // Yeni görsel yükleme isteği varsa
    elseif (!empty($_FILES['image']['name'])) {
        // Dosya kontrolü
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            throw new Exception("Sadece JPG, PNG ve GIF dosyaları yüklenebilir.");
        }

        if ($_FILES['image']['size'] > $maxSize) {
            throw new Exception("Dosya boyutu 5MB'dan küçük olmalıdır.");
        }

        $originalName = basename($_FILES['image']['name']);
        $uploadDir = '../../../php/gallery/payduplouds';

        // Dizinin varlığını kontrol et
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception("Yükleme dizini oluşturulamadı.");
            }
        }

        // Yeni dosya adı oluştur
        $filename = uniqid() . "_" . $originalName;
        $uploadPath = "$uploadDir/$filename";

        // Dosyayı yükle
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            throw new Exception("Dosya yüklenirken bir hata oluştu.");
        }

        // Eski görseli sil
        if ($currentImage && file_exists("$uploadDir/$currentImage")) {
            unlink("$uploadDir/$currentImage");
        }

        // Veritabanını güncelle (görsel ile)
        $stmt = $pdo->prepare("UPDATE stakeholders SET name=?, description=?, image_filename=? WHERE id=?");
        $stmt->execute([$name, $desc, $filename, $id]);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Paydaş ve görsel başarıyla güncellendi.'
        ]);
        exit;
    } 
    // Sadece metin alanları güncelleniyorsa
    else {
        $stmt = $pdo->prepare("UPDATE stakeholders SET name=?, description=? WHERE id=?");
        $stmt->execute([$name, $desc, $id]);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Paydaş bilgileri başarıyla güncellendi.'
        ]);
        exit;
    }

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
} catch (PDOException $e) {
    error_log("PDO Hatası: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı işlemi sırasında bir hata oluştu.'
    ]);
    exit;
}
?>