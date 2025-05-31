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
                    showNotification('danger', 'Bir hata oluştu');
                });
        }
    };

    // Paydaş düzenleme
    window.editStakeholder = function(id) {
        fetch('../php/ogdınamık/stakeholders/edit.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    $('#edit_id').val(data.stakeholder.id);
                    $('#edit_name').val(data.stakeholder.name);
                    $('#edit_description').val(data.stakeholder.description);
                    
                    const currentImage = `../php/gallery/payduplouds/${data.stakeholder.image_filename}`;
                    $('#currentImageContainer').html(`
                        <img src="${currentImage}" alt="Mevcut Logo" style="max-width: 200px;">
                    `);

                    $('#editStakeholderModal').modal('show');
                } else {
                    showNotification('danger', data.message || 'Paydaş bilgileri alınamadı');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('danger', 'Bir hata oluştu');
            });
    };
});
