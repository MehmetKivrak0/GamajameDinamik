document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM yüklendi, form kontrolü başlatılıyor...');
    function checkImageDimensions(file) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.src = URL.createObjectURL(file);
            
            img.onload = function() {
                URL.revokeObjectURL(img.src);
                const width = img.width;
                const height = img.height;
                
                if (width > 500 || height > 500) {
                    reject([
                        "❌ Sorun:",
                        width < 500 || height < 500 ? "- Görsel çok küçük" : "- Görsel çok büyük",
                        "",
                        "✅ Çözüm:",
                        "- Görseli 500x500 piksel olarak düzenleyin",
                        "- Photoshop, GIMP veya online araçlar kullanabilirsiniz",
                        "- Görseli yeniden boyutlandırıp tekrar deneyin"
                    ].join('\n'));
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
    // Sidebar toggle
    $('#sidebarCollapse').on('click', function() {
        $('#sidebar').toggleClass('active');
        $('#content').toggleClass('active');
    });

    // URL hash değişirse ilgili sekmeyi aç
    function openTabFromHash() {
        var hash = window.location.hash;
        if(hash) {
            $('.nav-link').removeClass('active');
            $('.tab-pane').removeClass('show active');
            $('a[href="' + hash + '"]').addClass('active');
            $(hash).addClass('show active');
            
            // Başlığı güncelle
            var title = $('a[href="' + hash + '"]').find('span').text();
            $('#page-title').text(title);
            $('#page-subtitle').text(title + ' Yönetimi');
        }
    }

    // Sayfa yüklendiğinde ve hash değiştiğinde sekmeyi kontrol et
    $(window).on('load hashchange', openTabFromHash);

    // Paydaş ekleme butonu tıklama olayı
    $(document).on('click', '.add-stakeholder', function() {
        console.log('Add butonu tıklandı');
        var myModal = new bootstrap.Modal(document.getElementById('addStakeholderModal'));
        myModal.show();
    });

    // Form ve görsel input elementlerini bul
    const form = document.getElementById('addStakeholderForm');
    const imageInput = document.getElementById('image');
    
    if (!form || !imageInput) {
        console.error('Form veya görsel input bulunamadı!');
        return;
    }

    // Görsel seçildiğinde anında kontrol et
    imageInput.addEventListener('change', async function() {
        const file = this.files[0];
        if (!file) return;

        try {
            await checkImageDimensions(file);
        } catch (error) {
            this.value = ''; // Görsel seçimini temizle
            showNotification('error', error);
        }
    });

    // Form gönderim kontrolü
    // Form submit olayını yakala
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        console.log('Form gönderimi yakalandı');

        // Görsel kontrolü
        const imageInput = this.querySelector('#image');
        if (!imageInput || !imageInput.files[0]) {
            showNotification('error', 'Lütfen bir görsel seçin');
            return;
        }

        try {
            // Görsel boyutunu kontrol et
            await checkImageDimensions(imageInput.files[0]);
            console.log('Görsel boyutu kontrolü başarılı');

            // FormData oluştur
            const formData = new FormData(this);

            // AJAX ile gönder
            const response = await fetch('../php/ogdınamık/stakeholders/insert.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Server hatası: ' + response.status);
            }

            // Başarılı ise modalı kapat ve sayfayı yenile
            const modal = bootstrap.Modal.getInstance(document.getElementById('addStakeholderModal'));
            modal.hide();
            window.location.reload();

        } catch (error) {
            console.error('Hata:', error);
            showNotification('error', error.message || 'Bir hata oluştu');
        }
    });

    // Düzenleme butonu tıklama olayı
    $(document).on('click', '.edit-stakeholder', function() {
        var id = $(this).data('id');
        console.log('Edit butonu tıklandı, id:', id);
        
        // AJAX ile paydaş bilgilerini getir
        $.ajax({
            url: '../php/ogdınamık/stakeholders/edit.php',
            type: 'GET',
            data: { id: id },
            success: function(response) {
                console.log('AJAX yanıtı:', response);
                var stakeholder = JSON.parse(response);
                
                // Modal alanlarını doldur
                $('#edit_id').val(stakeholder.id);
                $('#edit_name').val(stakeholder.name);
                $('#edit_description').val(stakeholder.description);
                
                // Bootstrap 5 Modal
                var myModal = new bootstrap.Modal(document.getElementById('editStakeholderModal'));
                myModal.show();
            },
            error: function(xhr, status, error) {
                console.error('AJAX hatası:', error);
                showNotification('error', 'Paydaş bilgileri alınırken bir hata oluştu');
            }
        });
        
        // Debug için global hata yakalayıcı
        window.onerror = function(msg, url, line) {
            console.error(`Hata: ${msg}\nDosya: ${url}\nSatır: ${line}`);
            return false;
        };
    });

    // Bildirim gösterme fonksiyonu
    function showNotification(type, message) {
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        var icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
        
        var alert = $(`
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas fa-${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);
        
        $('#notifications').append(alert);
        
        // 3 saniye sonra bildirimi kaldır
        setTimeout(function() {
            alert.alert('close');
        }, 3000);
    }
});

// Sayfa yüklendiğinde bildirimler için div oluştur
$('<div id="notifications" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>').appendTo('body');

// CSS animasyonu
$('<style>')
    .text(`
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
    `)
    .appendTo('head');
