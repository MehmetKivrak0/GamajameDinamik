<?php
define('MAX_WIDTH', 500);
define('MAX_HEIGHT', 500);

/**
 * Görsel boyutunun geçerli olup olmadığını kontrol eder
 * @param string $imagePath Görsel dosyasının yolu
 * @param int $maxWidth İzin verilen maksimum genişlik
 * @param int $maxHeight İzin verilen maksimum yükseklik
 * @return bool Görsel boyutu uygunsa true, değilse false döner
 */
function isImageSizeValid($imagePath, $maxWidth, $maxHeight) {
    // Görsel bilgilerini al
    $imageInfo = getimagesize($imagePath);
    
    if ($imageInfo === false) {
        return false; // Geçersiz görsel
    }
    
    $width = $imageInfo[0];
    $height = $imageInfo[1];
    
    // Boyut kontrolü
    return ($width <= $maxWidth && $height <= $maxHeight);
}

// Görsel yükleme kontrolü için örnek kullanım
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $tempPath = $_FILES['image']['tmp_name'];
    
    if (!isImageSizeValid($tempPath, MAX_WIDTH, MAX_HEIGHT)) {
        echo "<script>alert('Görsel boyutu en fazla " . MAX_WIDTH . "x" . MAX_HEIGHT . " piksel olabilir.'); window.history.back();</script>";
        exit;
    }
    
    // Görsel boyutu uygunsa, diğer işlemlere devam edilebilir...
}
?>
