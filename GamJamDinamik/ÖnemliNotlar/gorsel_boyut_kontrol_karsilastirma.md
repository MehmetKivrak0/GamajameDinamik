# Görsel Boyut Kontrolü Fonksiyonu Karşılaştırması

## Sizin Kodunuz
```php
function isImageSizeValid(string $imagePath, int $maxWidth, int $maxHeight): bool {
    [$width, $height] = getimagesize($imagePath);
    return $width <= $maxWidth && $height <= $maxHeight;
}
```

## Benim Önerdiğim Kod
```php
function isImageSizeValid(string $imagePath, int $maxWidth, int $maxHeight): bool {
    $imageInfo = getimagesize($imagePath);
    if ($imageInfo === false) {
        return false;
    }
    return $imageInfo[0] <= $maxWidth && $imageInfo[1] <= $maxHeight;
}
```

## Farklar ve Açıklamalar

1. **Array Erişim Farkı:**
   - Sizin kodunuz: Dizi parçalama (`[$width, $height]`) kullanıyor
   - Benim kodum: Dizi indeksleri (`$imageInfo[0]`, `$imageInfo[1]`) kullanıyor
   - İkisi de aynı işi yapıyor, sadece yazım şekli farklı

2. **Hata Kontrolü:**
   - Sizin kodunuz: Direkt dizi parçalama yaptığı için, geçersiz görsel durumunda PHP hatası verebilir
   - Benim kodum: `getimagesize()` fonksiyonunun false dönme durumunu kontrol ediyor
   - Bu kontrol sayesinde geçersiz görsel yüklemelerinde hata almadan işlemi sonlandırabiliyoruz

## Sonuç
İki kod da aynı şekilde çalışır, sadece benim önerdiğim versiyonda ekstra hata kontrolü var. getimagesize() fonksiyonu her iki kodda da aynı şekilde çalışıyor ve görsel boyutlarını aynı şekilde alıyor.

## ImagePath Nereden Geliyor?

Form üzerinden yüklenen görselin geçici dosya yolu `$_FILES['image']['tmp_name']` ile alınıyor. Yani:

1. HTML formunda görseli seçiyoruz:
```html
<input type="file" name="image">
```

2. Form gönderildiğinde PHP bu görseli önce geçici bir dizine kaydeder

3. Bu geçici dosyanın yolunu fonksiyona gönderiyoruz:
```php
if (!isImageSizeValid($_FILES['image']['tmp_name'], MAX_WIDTH, MAX_HEIGHT))
```

Böylece görsel daha kalıcı konumuna taşınmadan önce boyutlarını kontrol edebiliyoruz.

## tmp_name Ne Demek?

`$_FILES['image']['tmp_name']` veritabanından bir şey almıyor. Bu PHP'nin kendi dosya yükleme sisteminin bir parçası:

- `tmp_name`: "temporary name" yani "geçici isim" anlamına gelir
- Form ile dosya yüklendiğinde PHP bu dosyayı önce sunucunun geçici dizinine kaydeder
- Bu geçici dosyanın sistem yolunu `tmp_name` ile alırız
- Örnek bir tmp_name değeri: "C:/xampp/tmp/php1234.tmp"

Yani:
1. Kullanıcı görseli seçip formu gönderdiğinde
2. PHP bu görseli önce geçici bir dizine kaydeder
3. Biz bu geçici dosyanın yolunu `tmp_name` ile alırız
4. Kontrolleri yaptıktan sonra bu görseli kalıcı konumuna taşırız

Bu geçici sistem sayesinde dosyayı kalıcı olarak kaydetmeden önce boyut gibi kontrolleri yapabiliyoruz.

## Form Gönderiminden tmp_name Kullanımına Kadar Kod Akışı

1. HTML Form:
```html
<form action="insert.php" method="POST" enctype="multipart/form-data">
    <input type="file" name="image">
    <button type="submit">Gönder</button>
</form>
```

2. Form Gönderildiğinde insert.php'de:
```php
// Form POST metoduyla mı gönderilmiş kontrol et
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Geçersiz istek metodu.';
    redirect('/rolweb/admin.php#paydas');
}

// Dosya yüklendi mi kontrol et
if(!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = 'Lütfen bir resim seçin.';
    redirect('/rolweb/admin.php#paydas');
}

// Geçici dosya yolunu al
$tempPath = $_FILES['image']['tmp_name'];

// Görsel boyutunu kontrol et
if (!isImageSizeValid($tempPath, MAX_WIDTH, MAX_HEIGHT)) {
    $_SESSION['error'] = 'Görsel boyutu en fazla ' . MAX_WIDTH . 'x' . MAX_HEIGHT . ' piksel olabilir.';
    redirect('/rolweb/admin.php#paydas');
}

// Eğer buraya kadar geldiyse, artık görseli kalıcı konuma taşıyabiliriz
$uploadDir = '../../../php/gallery/payduplouds';
$image = uniqid() . '_' . basename($_FILES['image']['name']);
$imagePath = $uploadDir . '/' . $image;

// Görseli kalıcı konuma taşı
if(!move_uploaded_file($tempPath, $imagePath)) {
    $_SESSION['error'] = 'Resim yüklenirken bir hata oluştu.';
    redirect('/rolweb/admin.php#paydas');
}
```

Bu kod akışında:
1. Form multipart/form-data ile gönderiliyor
2. PHP dosyayı otomatik olarak geçici dizine kaydediyor
3. `$_FILES['image']['tmp_name']` ile geçici dosya yolunu alıyoruz
4. Bu geçici dosya üzerinde boyut kontrolü yapıyoruz
5. Kontroller başarılıysa dosyayı kalıcı konuma taşıyoruz