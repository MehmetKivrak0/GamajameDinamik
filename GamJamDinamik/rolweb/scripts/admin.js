
// Yardımcı fonksiyonları tanımla
function showNotification(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.getElementById('notifications').appendChild(alertDiv);

    setTimeout(() => {
        if (document.body.contains(alertDiv)) {
            alertDiv.remove();
        }
    }, 3000);
}

// Global toggle fonksiyonu
window.toggleStakeholderStatus = function(id) {
    $.ajax({
        url: '../php/ogdınamık/stakeholders/toggle_status.php',
        type: 'POST',
        data: { id: id },
        success: function(response) {
            console.log('Toggle yanıtı:', response);
            if (response.success) {
                showNotification('success', response.message);
                window.location.reload();
            } else {
                showNotification('danger', response.message || 'İşlem başarısız oldu');
            }
        },
        error: function() {
            showNotification('danger', 'Sunucu ile iletişim sırasında bir hata oluştu');
        }
    });
};

// DOM yüklendiğinde çalışacak kodlar
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM yüklendi, uygulama başlatılıyor...');
    
    // Yardımcı fonksiyonlar
    function showNotification(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.getElementById('notifications').appendChild(alertDiv);

        setTimeout(() => {
            if (document.body.contains(alertDiv)) {
                alertDiv.remove();
            }
        }, 3000);
    }

    // Sidebar toggle
    $('#sidebarCollapse').on('click', function() {
        $('#sidebar').toggleClass('active');
        $('#content').toggleClass('active');
    });

    // Sekme yönetimi
    function activateTab(hash) {
        if (!hash) return;
        
        $('.nav-link').removeClass('active');
        $('.tab-pane').removeClass('show active');
        
        $('a[href="' + hash + '"]').addClass('active');
        $(hash).addClass('show active');
    }

    window.addEventListener('hashchange', function() {
        const hash = window.location.hash;
        if (hash) {
            activateTab(hash);
        } else {
            activateTab('#istatistikler');
        }
    });
    
    activateTab(window.location.hash || '#istatistikler');

    // Arama işlevselliği
    const $searchInput = $('#stakeholderSearch');
    const $searchButton = $('#searchButton');
    const $clearButton = $('#clearSearchButton');
    const $cards = $('.partner-card');

    function performSearch() {
        const searchTerm = $searchInput.val().trim().toLowerCase();
        if (!searchTerm) return;

        let found = false;
        $cards.each(function() {
            const $card = $(this);
            const id = $card.data('id').toString();
            const title = $card.find('.card-title').text().toLowerCase();

            if (title.includes(searchTerm) || id.includes(searchTerm)) {
                $card.fadeIn();
                found = true;
            } else {
                $card.fadeOut();
            }
        });

        if (!found) {
            showNotification('warning', 'Arama sonucunda eşleşme bulunamadı');
        }
    }

    $searchButton.on('click', performSearch);
    $searchInput.on('keypress', function(e) {
        if (e.which === 13) performSearch();
    });
    $clearButton.on('click', function() {
        $searchInput.val('');
        $cards.fadeIn();
    });

    // Görsel kontrolü
    let isImageValid = false;
    let isEditImageValid = true; // Düzenleme için başlangıçta true, çünkü görsel yükleme zorunlu değil

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

                if (width !== 500 || height !== 500) {
                    showNotification('danger', `Görsel boyutu (${width}x${height}) uygun değil.\nLütfen tam olarak 500x500 piksel boyutunda bir görsel kullanın.`);
                    $('#image').val('');
                    $('#imagePreview').hide();
                    isImageValid = false;
                    return;
                }

                if (aspectRatio !== 1) {
                    showNotification('danger', 'Görsel oranı uygun değil!\nLütfen tam kare formatta (1:1) bir görsel seçin.');
                    $('#image').val('');
                    $('#imagePreview').hide();
                    isImageValid = false;
                    return;
                }

                isImageValid = true;
                $('#imagePreview').show().find('img').attr('src', e.target.result);
                showNotification('success', 'Görsel boyutu ve oranı uygun!');
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });

    // Form gönderimi
    $('#addStakeholderForm').on('submit', function(e) {
        if (!isImageValid) {
            e.preventDefault();
            showNotification('danger', 'Lütfen 500x500 piksel boyutunda ve kare formatta bir görsel seçin!');
            return false;
        }
        // Form gönderimi başarılı olduğunda
        showNotification('success', 'Paydaş başarıyla eklendi. Yönlendiriliyorsunuz...');
    });

    // Paydaş silme
    window.deleteStakeholder = function(id) {
        const button = document.querySelector(`button[onclick*="deleteStakeholder(${id})"]`);
        if (button.disabled) {
            showNotification('warning', 'Pasif durumdaki paydaşlar silinemez. Önce aktif hale getirin.');
            return;
        }
        if (confirm('Bu paydaşı silmek istediğinizden emin misiniz?')) {
            fetch('../php/ogdınamık/stakeholders/delete.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('success', 'Paydaş başarıyla silindi');
                        setTimeout(() => {
                            window.location.href = window.location.pathname + "#paydas";
                            window.location.reload();
                        }, 1000);
                    } else {
                        showNotification('danger', data.message || 'Silme işlemi başarısız oldu');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('danger', 'Paydaş bilgileri yüklenirken bir hata oluştu');
                    document.getElementById('editStakeholderModal').querySelector('.btn-close').click();
                });
        }
    };

    // Paydaş düzenleme
    window.editStakeholder = function(id) {
        const button = document.querySelector(`button[onclick*="editStakeholder(${id})"]`);
        if (button.disabled) {
            showNotification('warning', 'Pasif durumdaki paydaşlar düzenlenemez. Önce aktif hale getirin.');
            return;
        }
        // Bootstrap modal'ı başlangıçta göster
        const editModal = new bootstrap.Modal(document.getElementById('editStakeholderModal'));
        editModal.show();

        // Form içeriğini temizle ve yükleniyor göstergesi ekle
        $('#edit_id').val('');
        $('#edit_name').val('');
        $('#edit_description').val('');
        $('#currentImageContainer').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Yükleniyor...</p></div>');

        // Paydaş bilgilerini getir
        fetch('../php/ogdınamık/stakeholders/edit.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    $('#edit_id').val(data.stakeholder.id);
                    $('#edit_name').val(data.stakeholder.name);
                    $('#edit_description').val(data.stakeholder.description);
                    
                    if (data.stakeholder.image_filename) {
                        const currentImage = `../php/gallery/payduplouds/${data.stakeholder.image_filename}`;
                        $('#currentImageContainer').html(`
                            <img src="${currentImage}" alt="Mevcut Logo" class="img-fluid" style="max-width: 200px; border-radius: 5px;">
                            <p class="text-muted mt-2">Mevcut Logo</p>
                        `);
                    } else {
                        $('#currentImageContainer').html(`
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Henüz logo yüklenmemiş
                            </div>
                        `);
                    }

                    // $('#editStakeholderModal').modal('show'); // Bu satırı kaldırıyoruz çünkü modal zaten gösterildi
                    
                    // Görsel seçildiğinde önizleme ve kontrol
                    $('#edit_image').off('change').on('change', function() {
                        const file = this.files[0];
                        if (!file) {
                            isEditImageValid = true; // Dosya seçilmediğinde geçerli kabul et
                            $('#editImagePreview').hide();
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = new Image();
                            img.onload = function() {
                                const width = this.width;
                                const height = this.height;
                                const aspectRatio = width / height;

                                if (width !== 500 || height !== 500) {
                                    showNotification('danger', `Görsel boyutu (${width}x${height}) uygun değil.\nLütfen tam olarak 500x500 piksel boyutunda bir görsel kullanın.`);
                                    $('#edit_image').val('');
                                    $('#editImagePreview').hide();
                                    isEditImageValid = false;
                                    return;
                                }

                                if (aspectRatio !== 1) {
                                    showNotification('danger', 'Görsel oranı uygun değil!\nLütfen tam kare formatta (1:1) bir görsel seçin.');
                                    $('#edit_image').val('');
                                    $('#editImagePreview').hide();
                                    isEditImageValid = false;
                                    return;
                                }

                                isEditImageValid = true;
                                $('#editImagePreview').show().find('img').attr('src', e.target.result);
                                showNotification('success', 'Görsel boyutu ve oranı uygun!');
                            };
                            img.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    });
                } else {
                    showNotification('danger', data.message || 'Paydaş bilgileri alınamadı');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('danger', 'Bir hata oluştu');
            });
    };

    // Düzenleme formu gönderimi
    $('#editStakeholderForm').on('submit', function(e) {
        e.preventDefault();
        const imageInput = $('#edit_image')[0];
        if (imageInput.files.length > 0 && !isEditImageValid) {
            showNotification('danger', 'Lütfen 500x500 piksel boyutunda ve kare formatta bir görsel seçin!');
            return false;
        }

        // Form verilerini al
        const formData = new FormData(this);

        // Modal'ı kapat
        const editModal = bootstrap.Modal.getInstance(document.getElementById('editStakeholderModal'));
        editModal.hide();

        // AJAX ile gönder
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Server yanıtı:', response);
                if (response.success) {
                    showNotification('success', response.message || 'Paydaş başarıyla güncellendi');
                    
                    // Modal'ı kapat
                    const editModal = bootstrap.Modal.getInstance(document.getElementById('editStakeholderModal'));
                    if (editModal) {
                        editModal.hide();
                    }

                    // Sayfayı yenile
                    setTimeout(() => {
                        window.location.href = window.location.pathname + '#paydas';
                        window.location.reload(true);
                    }, 1000);
                } else {
                    showNotification('danger', response.message || 'Güncelleme sırasında bir hata oluştu');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX hatası:', xhr.responseText);
                let errorMessage = 'Sunucu ile iletişim sırasında bir hata oluştu';
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    console.error('JSON parse hatası:', e);
                }
                
                showNotification('danger', errorMessage);
            }
        });
    });
});
