# Görsel Yükleme Kuralları

## Boyut Kısıtlamaları
- Maksimum genişlik: 500 piksel
- Maksimum yükseklik: 500 piksel
- Minimum boyut: 10x10 piksel

## En-Boy Oranı Kuralları
- Görsel kare formata yakın olmalıdır (1:1 oranı)
- İzin verilen oran aralığı: 0.8 ile 1.2 arası
- Örnek uygun oranlar:
  * 500x500 (oran: 1.0) ✅
  * 450x500 (oran: 0.9) ✅
  * 500x450 (oran: 1.1) ✅

## Uygun Olmayan Örnekler
1. Boyut Aşımı:
   * 800x600 (çok büyük) ❌
   * 1600x1200 (çok büyük) ❌

2. Orantısız Boyutlar:
   * 420x120 (oran: 3.5) ❌
   * 200x500 (oran: 0.4) ❌

## Dosya Formatları
- PNG
- JPG/JPEG

## Kontrol Mekanizması
1. Kullanıcı görsel seçtiğinde:
   * Boyut kontrolü yapılır
   * En-boy oranı kontrolü yapılır
   * Uygun olmayan görseller anında reddedilir
   * Kullanıcıya detaylı hata mesajı gösterilir

2. Form gönderiminde:
   * Sunucu tarafında tekrar kontrol yapılır
   * Güvenlik için çift kontrol sağlanır

## Hata Mesajları
- Boyut hatası: Görselin piksel boyutları gösterilir
- Oran hatası: Mevcut en-boy oranı gösterilir
- Her hatada kullanıcıya ne yapması gerektiği açıklanır

## Öneriler
1. Görseli yüklemeden önce:
   * 500x500 piksel boyutuna getirin
   * Kare formatta olmasına dikkat edin
   * Desteklenen formatlarda kaydedin

2. Online araçlar kullanarak:
   * Görsel boyutlandırma yapabilirsiniz
   * En-boy oranını düzenleyebilirsiniz

## Kod Örnekleri

### Client-Side Kontrol (JavaScript)
```javascript
// Görsel yükleme kontrolü
$('#image').on('change', function() {
    var file = this.files[0];
    if (!file) return;

    var reader = new FileReader();
    
    reader.onload = function(e) {
        var img = new Image();
        img.onload = function() {
            // Boyut kontrolü
            if (this.width > 500 || this.height > 500) {
                alert(`Yüklenen görsel boyutu: ${this.width}x${this.height} piksel\n` +
                      `İzin verilen maksimum boyut: 500x500 piksel\n\n` +
                      `Lütfen daha küçük bir görsel yükleyin!`);
                $('#image').val('');
                isImageValid = false;
                return;
            }

            // En-boy oranı kontrolü (1:1'e yakın olmalı)
            const aspectRatio = this.width / this.height;
            if (aspectRatio < 0.8 || aspectRatio > 1.2) {
                alert(`Görsel oranı uygun değil!\n` +
                      `Mevcut oran: ${this.width}x${this.height}\n` +
                      `Görsel kare formata yakın olmalıdır (1:1)`);
                $('#image').val('');
                isImageValid = false;
                return;
            }

            isImageValid = true;
        };
        img.src = e.target.result;
    };
    
    reader.readAsDataURL(file);
});
```

### Server-Side Kontrol (PHP)
```php
/**
 * Görsel boyutunu kontrol eden ve boyut bilgisini döndüren fonksiyon
 * @return array [bool $isValid, string $message]
 */
function checkImageSize(string $imagePath, int $maxWidth, int $maxHeight): array {
    $imageInfo = getimagesize($imagePath);
    if ($imageInfo === false) {
        return [false, "Geçersiz görsel dosyası"];
    }
    
    $width = $imageInfo[0];
    $height = $imageInfo[1];
    
    // Her iki boyut da 500x500 içinde olmalı
    if ($width > $maxWidth || $height > $maxHeight || $width < 10 || $height < 10) {
        $message = "Yüklenen görsel: {$width}x{$height} piksel. ";
        $message .= "Görsel boyutu {$maxWidth}x{$maxHeight} piksel arasında olmalıdır. ";
        $message .= "Her iki boyut da bu sınırlar içinde olmalıdır.";
        
        return [false, $message];
    }

    // En-boy oranı kontrolü
    $aspectRatio = $width / $height;
    if ($aspectRatio < 0.8 || $aspectRatio > 1.2) {
        $message = "Görsel oranı uygun değil. ";
        $message .= "Mevcut oran: {$width}x{$height}. ";
        $message .= "Görsel kare formata yakın olmalıdır (1:1).";
        
        return [false, $message];
    }
    
    return [true, ""];
}

// Kullanım örneği:
define('MAX_WIDTH', 500);
define('MAX_HEIGHT', 500);

[$isValid, $sizeMessage] = checkImageSize($_FILES['image']['tmp_name'], MAX_WIDTH, MAX_HEIGHT);

if (!$isValid) {
    $_SESSION['error'] = $sizeMessage;
    redirect('/rolweb/admin.php#paydas');
    exit;
}
```

Bu kod örnekleri hem client-side hem de server-side kontrolleri göstermektedir. Her iki tarafta da aynı kurallar (boyut ve oran kontrolleri) uygulanarak güvenlik sağlanmaktadır.