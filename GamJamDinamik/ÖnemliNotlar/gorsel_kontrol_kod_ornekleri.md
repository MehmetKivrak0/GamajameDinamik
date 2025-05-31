# Görsel Kontrol Sistemi Kod Örnekleri

## 1. add.php'deki Değişiklikler

```javascript
// Görsel kontrolü
let isImageValid = false;

$('#image').on('change', function() {
    const file = this.files[0];
    if (!file) {
        isImageValid = false;
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            const width = this.width;
            const height = this.height;
            const aspectRatio = width / height;
            let errorMessage = '';

            // Boyut kontrolü (500x500 piksel)
            if (width !== 500 || height !== 500) {
                errorMessage = `Görsel boyutu (${width}x${height}) uygun değil.\n` +
                             `Lütfen tam olarak 500x500 piksel boyutunda bir görsel kullanın.`;
            }
            // En-boy oranı kontrolü (1:1)
            else if (aspectRatio < 0.95 || aspectRatio > 1.05) {
                errorMessage = 'Görsel oranı uygun değil!\n' +
                             'Lütfen tam kare formatta (1:1) bir görsel seçin.';
            }

            const $message = $('#message');
            if (errorMessage) {
                $message
                    .removeClass('d-none alert-success')
                    .addClass('alert-danger')
                    .html(errorMessage.replace(/\n/g, '<br>'))
                    .fadeIn();

                $('#image').val('');
                isImageValid = false;

                setTimeout(() => {
                    $message.fadeOut(() => {
                        $message.addClass('d-none');
                    });
                }, 5000);
            } else {
                isImageValid = true;
                $message
                    .removeClass('d-none alert-danger')
                    .addClass('alert-success')
                    .html('Görsel boyutu ve oranı uygun!')
                    .fadeIn();

                setTimeout(() => {
                    $message.fadeOut(() => {
                        $message.addClass('d-none');
                    });
                }, 3000);
            }
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
});
```

## 2. admin.js'deki Değişiklikler

```javascript
// Görsel boyut ve oran kontrolü fonksiyonu
function checkImageDimensions(file) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        const imageUrl = URL.createObjectURL(file);
        
        img.onload = function() {
            URL.revokeObjectURL(imageUrl);
            const width = this.width;
            const height = this.height;
            const aspectRatio = width / height;

            if (width !== 500 || height !== 500) {
                reject([
                    "❌ Görsel Boyutu Hatası:",
                    `→ Mevcut boyut: ${width}x${height} piksel`,
                    "→ Olması gereken: 500x500 piksel",
                    "",
                    "✅ Çözüm Önerileri:",
                    "1. Görseli tam olarak 500x500 piksel yapın",
                    "2. Photoshop, GIMP veya online araçlar kullanabilirsiniz",
                    "3. Yeniden boyutlandırma sonrası tekrar deneyin"
                ].join('\n'));
            } else if (aspectRatio < 0.95 || aspectRatio > 1.05) {
                reject([
                    "❌ Görsel Oranı Hatası:",
                    "→ Görsel tam kare formatta değil",
                    "",
                    "✅ Çözüm Önerileri:",
                    "1. Görseli 1:1 oranında kırpın",
                    "2. Kare formatta yeni bir görsel hazırlayın",
                    "3. Düzenleme sonrası tekrar deneyin"
                ].join('\n'));
            } else {
                resolve();
            }
        };
        
        img.onerror = function() {
            URL.revokeObjectURL(imageUrl);
            reject([
                "❌ Görsel Yükleme Hatası:",
                "→ Dosya okunamadı veya hasarlı",
                "",
                "✅ Çözüm Önerileri:",
                "1. Geçerli bir görsel dosyası seçin",
                "2. Desteklenen formatlar: JPG, PNG, GIF",
                "3. Farklı bir görsel ile tekrar deneyin"
            ].join('\n'));
        };

        img.src = imageUrl;
    });
}

// Görsel seçildiğinde kontrol
imageInput.addEventListener('change', async function() {
    const file = this.files[0];
    if (!file) return;

    try {
        await checkImageDimensions(file);
        showNotification('success', '✅ Görsel boyutu ve oranı uygun!');
    } catch (error) {
        this.value = '';
        showNotification('error', error);
    }
});

// Form gönderiminde kontrol
form.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const imageInput = this.querySelector('#image');
    if (!imageInput || !imageInput.files[0]) {
        showNotification('error', 'Lütfen bir görsel seçin');
        return;
    }

    try {
        await checkImageDimensions(imageInput.files[0]);
        // ... form gönderimi devam eder
    } catch (error) {
        showNotification('error', error);
        return;
    }
});
```

## Kullanım

Bu kodlar sayesinde:

1. Görsel yüklendiğinde anında kontrol edilir
2. 500x500 piksel boyutunda olması gerekir
3. En-boy oranı 1:1'e çok yakın olmalıdır (0.95-1.05 arası)
4. Hata durumunda detaylı mesaj ve çözüm önerileri gösterilir
5. Başarılı yüklemede olumlu geri bildirim verilir
6. Form gönderiminde tekrar kontrol yapılır

## Not

Bu kodları kendi projenize entegre ederken:
- jQuery kütüphanesinin yüklü olduğundan emin olun
- showNotification fonksiyonunu kendi bildirim sisteminize göre uyarlayın
- Hata mesajlarını ihtiyacınıza göre özelleştirin
