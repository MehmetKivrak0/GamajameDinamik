<?php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Oturum zaman aşımına uğradı'
    ]);
    exit;
}

try {
    // Gelen verileri kontrol et
    if (!isset($_POST['id'], $_POST['name'], $_POST['description'])) {
        throw new Exception("Gerekli form verileri eksik");
    }
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $name = htmlspecialchars(trim($_POST['name']));
    $desc = htmlspecialchars(trim($_POST['description']));

    if (!$id || !$name || !$desc) {
        throw new Exception("Geçersiz form verileri.");
    }

    // Mevcut görseli al
    $stmt = $pdo->prepare("SELECT image_filename FROM stakeholders WHERE id = ?");
    $stmt->execute([$id]);
    $currentImage = $stmt->fetchColumn();

    // Görsel kaldırma isteği varsa (sadece veritabanı bağlantısını kaldır, dosyayı silme)
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
        // Veritabanında görsel alanını boşalt, ama dosyayı silme
        $stmt = $pdo->prepare("UPDATE stakeholders SET name=?, description=?, image_filename=NULL WHERE id=?");
        $stmt->execute([$name, $desc, $id]);
        
        error_log("Görsel referansı kaldırıldı, dosya korundu: " . ($currentImage ?? 'null'));
        
        echo json_encode([
            'success' => true,
            'message' => 'Paydaş başarıyla güncellendi.'
        ]);
        exit;
    }
    // Yeni görsel yükleme isteği varsa
    elseif (!empty($_FILES['image']['name'])) {
        // Dosya kontrolü
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif','image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            throw new Exception("Sadece JPG, PNG , GIF ve WEBP dosyaları yüklenebilir.");
        }

        if ($_FILES['image']['size'] > $maxSize) {
            throw new Exception("Dosya boyutu 5MB'dan küçük olmalıdır.");
        }

        $uploadDir = '../../../php/gallery/payduplouds';
        error_log("Dosya yükleme isteği başladı");
        error_log("POST verisi: " . print_r($_POST, true));
        error_log("FILES verisi: " . print_r($_FILES, true));

        // Dizinin varlığını kontrol et
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception("Yükleme dizini oluşturulamadı.");
            }
        }

        // Transaction başlat
        $pdo->beginTransaction();

        try {
            // Önce mevcut kaydı kontrol et
            $stmt = $pdo->prepare("SELECT id, image_filename FROM stakeholders WHERE id = ?");
            $stmt->execute([$id]);
            $currentRecord = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("Mevcut kayıt: " . print_r($currentRecord, true));

            if (!$currentRecord) {
                throw new Exception("Güncellenecek kayıt bulunamadı (ID: $id)");
            }

            // Yeni resim yükleme kontrolü
            if (!empty($_FILES['image']['name'])) {
                error_log("Yeni resim yükleniyor: " . $_FILES['image']['name']);
                
                $filename = basename($_FILES['image']['name']);
                $uploadPath = "$uploadDir/$filename";
                error_log("Yükleme yolu: $uploadPath");

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    throw new Exception("Dosya yüklenemedi: " . error_get_last()['message']);
                }
                error_log("Dosya başarıyla yüklendi");
            } else {
                error_log("Yeni resim yüklenmedi, mevcut resim kullanılacak");
                $filename = $currentRecord['image_filename'];
            }

            // Veritabanını güncelle
            $stmt = $pdo->prepare("UPDATE stakeholders SET name = ?, description = ?, image_filename = ? WHERE id = ?");
            $updateResult = $stmt->execute([$name, $desc, $filename, $id]);
            error_log("Veritabanı güncelleme sonucu: " . ($updateResult ? "Başarılı" : "Başarısız"));

            if (!$updateResult) {
                throw new Exception("Veritabanı güncellenemedi: " . implode(", ", $stmt->errorInfo()));
            }

            // Tüm görseller kalıcı olarak saklanıyor
            error_log("Eski görsel korundu: " . ($currentImage ?? 'null'));

            $pdo->commit();
            error_log("Transaction commit edildi - Güncelleme tamamlandı");

            // Başarılı sonuç döndür
            http_response_code(200);
            $result = [
                'success' => true,
                'message' => 'Güncelleme başarılı',
                'filename' => $filename
            ];
            
            // Başarılı durumda sonucu döndür
            echo json_encode($result);
            exit;
        } catch (Exception $e) {
            error_log("Hata oluştu: " . $e->getMessage());
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e; // Ana try-catch bloğuna yönlendir
        }
    } 
    // Sadece metin alanları güncelleniyorsa
    else {
        $stmt = $pdo->prepare("UPDATE stakeholders SET name=?, description=? WHERE id=?");
        $stmt->execute([$name, $desc, $id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Paydaş bilgileri başarıyla güncellendi.'
        ]);
        exit;
    }

} catch (Exception | PDOException $e) {
    error_log("Hata: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'İşlem sırasında bir hata oluştu.'
    ]);
    exit;
}
?>