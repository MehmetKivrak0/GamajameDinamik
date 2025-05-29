# Paydaş Düzenleme Özelliği - Yapılan Değişiklikler

## 1. admin.php Değişiklikleri
- Düzenleme modalı eklendi (editStakeholderModal)
- Modal içerisinde form oluşturuldu
- Form alanları:
  - Gizli ID alanı
  - Paydaş adı input
  - Açıklama textarea
  - Logo yükleme (isteğe bağlı)

## 2. admin.js Değişiklikleri
```javascript
function editStakeholder(id) {
    // AJAX ile paydaş bilgilerini getir
    $.ajax({
        url: '../php/ogdınamık/stakeholders/edit.php',
        type: 'GET',
        data: { id: id },
        success: function(response) {
            var stakeholder = JSON.parse(response);
            
            // Modal alanlarını doldur
            $('#edit_id').val(stakeholder.id);
            $('#edit_name').val(stakeholder.name);
            $('#edit_description').val(stakeholder.description);
            
            // Modalı göster
            $('#editStakeholderModal').modal('show');
        }
    });
}
```
- Düzenleme butonu tıklandığında çalışacak fonksiyon eklendi
- AJAX ile paydaş bilgilerini getirir
- Modal form alanlarını otomatik doldurur

## 3. uptedit.php Değişiklikleri
- Dosya yükleme yolu düzeltildi:
  ```php
  move_uploaded_file($_FILES['image']['tmp_name'], "../../../rolweb/payduplouds/$filename");
  ```
- Yönlendirme adresi düzeltildi:
  ```php
  header("Location: ../../../rolweb/admin.php#paydas");
  ```

## 4. edit.php Dosyası
```php
<?php
require_once '../../config.php';
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM stakeholders WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode($data);
?>
```
- AJAX isteğine cevap veren PHP dosyası
- Veritabanından paydaş bilgilerini getirir
- JSON formatında döndürür

## Nasıl Çalışır?
1. Kullanıcı "Düzenle" butonuna tıklar
2. JavaScript editStakeholder() fonksiyonu çalışır
3. edit.php'den AJAX ile paydaş bilgileri alınır
4. Modal açılır ve form alanları doldurulur
5. Kullanıcı değişiklikleri yapar ve kaydeder
6. uptedit.php değişiklikleri veritabanına kaydeder
7. Sayfa paydaşlar sekmesine yönlendirilir