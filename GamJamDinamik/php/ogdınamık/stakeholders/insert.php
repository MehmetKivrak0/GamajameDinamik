<?php
session_start();
require_once '../../../php/config.php';

/**
 * Görsel boyutunu kontrol eden ve boyut bilgisini döndüren fonksiyon
 * @return array [bool $isValid, string $message]
 */
function checkImageSize(string $imagePath, int $maxWidth, int $maxHeight): array {
    // Debug: Gelen dosya yolunu ve varlığını kontrol et
    error_log("Kontrol edilen dosya: " . $imagePath);
    error_log("Dosya var mı: " . (file_exists($imagePath) ? 'Evet' : 'Hayır'));
    
    $imageInfo = getimagesize($imagePath);
    if ($imageInfo === false) {
        error_log("getimagesize başarısız oldu");
        return [false, "Geçersiz görsel dosyası"];
    }
    
    $width = $imageInfo[0];
    $height = $imageInfo[1];
    
    // Debug: Okunan boyutları logla
    error_log("Okunan boyutlar: {$width}x{$height}");
    error_log("İzin verilen maksimum: {$maxWidth}x{$maxHeight}");
    
    // Görsel tam olarak 500x500 piksel olmalı
    if ($width !== $maxWidth || $height !== $maxHeight) {
        $message = "⚠️ BOYUT UYUŞMAZLIĞI ⚠️\n\n";
        $message .= "→ Yüklenen Görsel: {$width}x{$height} piksel\n";
        $message .= "→ Olması Gereken: {$maxWidth}x{$maxHeight} piksel\n\n";
        $message .= "❌ Sorun:\n";
        $message .= ($width < $maxWidth || $height < $maxHeight) ? "- Görsel çok küçük\n" : "- Görsel çok büyük\n";
        $message .= "\n✅ Çözüm:\n";
        $message .= "- Görseli {$maxWidth}x{$maxHeight} piksel olarak düzenleyin\n";
        $message .= "- Photoshop, GIMP veya online araçlar kullanabilirsiniz\n";
        $message .= "- Görseli yeniden boyutlandırıp tekrar deneyin";
        
        error_log("Boyut kontrolü başarısız: " . $message);
        return [false, $message];
    }
    
    return [true, ""];
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Geçersiz istek metodu.';
    redirect('/rolweb/admin.php#paydas');
}

// Form verilerini al
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$user_id = $_SESSION['user_id'] ?? null;

// Kullanıcı adını veritabanından al
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$created_by = $stmt->fetchColumn();

// Debug için session bilgilerini kontrol et
error_log(print_r($_SESSION, true));
error_log("Created By: " . $created_by);

// Kullanıcı girişi kontrolü
if (!$user_id) {
    $_SESSION['error'] = 'Oturum süreniz dolmuş. Lütfen tekrar giriş yapın.';
    redirect('/rolweb/admin.php#paydas');
}

// Basit doğrulama
if(empty($name) || empty($description)) {
    $_SESSION['error'] = 'Lütfen tüm alanları doldurun.';
    redirect('/rolweb/admin.php#paydas');
}

// Maksimum görsel boyutları
define('MAX_WIDTH', 500);
define('MAX_HEIGHT', 500);

// Resim yükleme kontrolü
if(!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = 'Lütfen bir resim seçin.';
    redirect('/rolweb/admin.php#paydas');
    exit;
}

// Önce görsel boyutunu kontrol et
error_log("Görsel kontrolü başlıyor...");
[$isValid, $sizeMessage] = checkImageSize($_FILES['image']['tmp_name'], MAX_WIDTH, MAX_HEIGHT);

// Hata durumunda işlemi sonlandır
if (!$isValid) {
    error_log("Görsel kontrolü başarısız: " . $sizeMessage);
    $_SESSION['error'] = $sizeMessage;
    redirect('/rolweb/admin.php#paydas');
    exit; // İşlemi kesin olarak sonlandır
}

error_log("Görsel kontrolü başarılı");

// Resim yükleme işlemi
$uploadDir = '../../../php/gallery/payduplouds';
$originalName = basename($_FILES['image']['name']);

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
    $image = $existingFile;
} else {
    $image = uniqid() . '_' . $originalName;
}
$imagePath = $uploadDir . '/' . $image;

// Üst dizinleri oluştur
if(!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Dizin kontrolü
if(!is_dir($uploadDir)) {
    if(!mkdir($uploadDir, 0777, true)) {
        $_SESSION['error'] = 'Dosya yükleme dizini oluşturulamadı.';
        redirect('/rolweb/admin.php#paydas');
    }
}

// Resmi yükle
if(!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
    $_SESSION['error'] = 'Resim yüklenirken bir hata oluştu.';
    redirect('/rolweb/admin.php#paydas');
}

try {
    // Maksimum karakter uzunluğu kontrolü
    if (strlen($name) > 255 || strlen($description) > 600) {
        $_SESSION['error'] = 'İsim veya açıklama çok uzun.';
        redirect('/rolweb/admin.php#paydas');
    }

    // İzin verilen dosya türleri
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif','image/webp'];
    if (!in_array($_FILES['image']['type'], $allowedTypes)) {
        $_SESSION['error'] = 'Sadece JPEG, PNG , GIF Ve WEBP  formatında resimler yüklenebilir.';
        redirect('/rolweb/admin.php#paydas');
    }

    // Veritabanına kaydet
    $stmt = $pdo->prepare("INSERT INTO stakeholders (name, description, image_filename, created_by)
                      VALUES (:name, :description, :image, :created_by)");
   $stmt->execute([
    ':name' => $name,
    ':description' => $description,
    ':image' => $image,
    ':created_by' => $created_by
    ]);
    
    // Başarılı yanıt
    $_SESSION['success'] = 'Kayıt başarıyla oluşturuldu!';
    redirect('/rolweb/admin.php#paydas');
}
catch (PDOException $e) {
    // Hata logu
    error_log(date("[Y-m-d H:i:s] ") . "Veritabanı hatası: " . $e->getMessage() . PHP_EOL, 3, 'error_log.txt');
    
    // Yüklenen resmi sil
    if(file_exists($imagePath)) {
        unlink($imagePath);
    }
    
    $_SESSION['error'] = 'Veritabanı hatası oluştu: ' . $e->getMessage();
    redirect('/rolweb/admin.php#paydas');
}


?>