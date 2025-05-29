# Görsel Yükleme ve Boyut Kontrolü

## 1. Fonksiyon Tanımı
```php
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
    
    if ($width <= $maxWidth && $height <= $maxHeight) {
        return [true, ""];
    }
    
    $message = "Yüklenen görsel: {$width}x{$height} piksel. ";
    $message .= "Görsel boyutu en fazla {$maxWidth}x{$maxHeight} piksel olmalıdır.";
    
    // Debug: Hata durumunu logla
    error_log("Boyut kontrolü başarısız: " . $message);
    
    return [false, $message];
}
```

## 2. İşlem Sırası
```php
// 1. Önce maksimum boyutları tanımla
define('MAX_WIDTH', 500);
define('MAX_HEIGHT', 500);

// 2. Dosya yükleme kontrolü
if(!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = 'Lütfen bir resim seçin.';
    redirect('/rolweb/admin.php#paydas');
    exit; // Önemli: İşlemi sonlandır
}

// 3. Görsel boyut kontrolü
error_log("Görsel kontrolü başlıyor...");
[$isValid, $sizeMessage] = checkImageSize($_FILES['image']['tmp_name'], MAX_WIDTH, MAX_HEIGHT);

// 4. Hata durumunda işlemi sonlandır
if (!$isValid) {
    error_log("Görsel kontrolü başarısız: " . $sizeMessage);
    $_SESSION['error'] = $sizeMessage;
    redirect('/rolweb/admin.php#paydas');
    exit; // Önemli: İşlemi sonlandır
}

// 5. Kontroller başarılıysa yüklemeye devam et
$uploadDir = '../../../php/gallery/payduplouds';
$originalName = basename($_FILES['image']['name']);
```

## 3. Önemli Noktalar

1. **İşlem Sırası**: 
   - Önce maksimum boyutları tanımlıyoruz
   - Sonra dosya yükleme kontrolü
   - Daha sonra boyut kontrolü
   - En son yükleme işlemi

2. **exit() Kullanımı**:
   - Her hata durumunda `exit()` kullanıyoruz
   - Bu sayede hatalı durumlarda kod devam etmiyor
   - Hatalı görsel kesinlikle yüklenemiyor

3. **Hata Ayıklama**:
   - `error_log()` ile tüm adımları logluyoruz
   - Dosya yolunu kontrol ediyoruz
   - Okunan boyutları kontrol ediyoruz
   - Hata mesajlarını kaydediyoruz

4. **Kullanıcı Geribildirimi**:
   - Hata durumunda açıklayıcı mesaj gösteriyoruz
   - Mevcut görsel boyutunu gösteriyoruz
   - İzin verilen maksimum boyutu gösteriyoruz

Bu yapı sayesinde:
- Görsel yükleme süreci güvenli
- Kullanıcı ne olduğunu anlayabiliyor
- Hata ayıklama kolay
- Maksimum boyut kontrolü kesin olarak uygulanıyor

## 4. JavaScript ile Client-Side Kontrol

Form gönderilmeden önce görselin boyutunu kontrol ediyoruz:

```javascript
// Görsel boyutu kontrol fonksiyonu
function checkImageDimensions(file) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.src = URL.createObjectURL(file);
        
        img.onload = function() {
            URL.revokeObjectURL(img.src);
            const width = img.width;
            const height = img.height;
            
            if (width > 500 || height > 500) {
                reject(`Yüklenen görsel boyutu (${width}x${height} piksel) çok büyük. Görsel boyutu en fazla 500x500 piksel olmalıdır.`);
            } else {
                resolve();
            }
        };
        
        img.onerror = function() {
            URL.revokeObjectURL(img.src);
            reject('Görsel yüklenirken bir hata oluştu.');
        };
    });
}

// Form gönderim kontrolü
$('form[action="../php/ogdınamık/stakeholders/insert.php"]').on('submit', async function(e) {
    e.preventDefault();
    
    const imageFile = this.image.files[0];
    if (!imageFile) {
        showNotification('error', 'Lütfen bir görsel seçin');
        return;
    }

    try {
        // Görsel boyutunu kontrol et
        await checkImageDimensions(imageFile);
        
        // Kontrol başarılıysa formu gönder
        const formData = new FormData(this);
        
        $.ajax({
            url: '../php/ogdınamık/stakeholders/insert.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Form başarıyla gönderildi');
                var modal = bootstrap.Modal.getInstance(document.getElementById('addStakeholderModal'));
                modal.hide();
                location.reload();
            },
            error: function(xhr, status, error) {
                console.error('Form gönderme hatası:', error);
                showNotification('error', 'Paydaş eklenirken bir hata oluştu');
            }
        });
    } catch (error) {
        // Görsel boyutu uygun değilse hata göster
        showNotification('error', error);
    }
});
```

Bu JavaScript kodu sayesinde:
1. Görsel seçilir seçilmez boyut kontrolü yapılır
2. Uygun olmayan görseller sunucuya gönderilmeden engellenir
3. Kullanıcı anında görsel geri bildirim alır
4. Gereksiz sunucu yükü ve bant genişliği kullanımı önlenir

## 5. İyileştirilmiş Görsel Kontrolü

Son geliştirmeler ile görsel kontrolü daha interaktif hale getirildi:

1. **Anında Kontrol**:
```javascript
// Görsel seçildiğinde anında kontrol et
imageInput.addEventListener('change', async function() {
    const file = this.files[0];
    if (!file) return;

    try {
        await checkImageDimensions(file);
        showNotification('success', 'Görsel boyutu uygun');
    } catch (error) {
        this.value = ''; // Görsel seçimini temizle
        showNotification('error', error);
    }
});
```

2. **Form Gönderimi**:
```javascript
form.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    try {
        await checkImageDimensions(imageInput.files[0]);
        const formData = new FormData(this);
        
        const response = await fetch('../php/ogdınamık/stakeholders/insert.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error('Server hatası: ' + response.status);
        }

        // Başarılı
        const modal = bootstrap.Modal.getInstance(document.getElementById('addStakeholderModal'));
        modal.hide();
        window.location.reload();

    } catch (error) {
        showNotification('error', error.message || 'Bir hata oluştu');
    }
});
```

Bu geliştirmeler ile:
- Görsel seçilir seçilmez boyut kontrolü yapılır
- Uygun olmayan görseller anında temizlenir
- Form gönderimi öncesi tekrar kontrol yapılır
- Sunucu yanıtları daha iyi yönetilir
- Kullanıcı deneyimi iyileştirilir