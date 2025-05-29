<!DOCTYPE html>
<html lang="tr">
<head>
    <base href="/GamJamDinamik/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paydaş Ekle - OTGET Yönetim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../rolweb/styles/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Mesaj kutusu stilleri */
        #message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            min-width: 300px;
            text-align: center;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-size: 14px;
            font-weight: 500;
        }
        #message.alert-success {
            background-color: rgba(40, 167, 69, 0.9);
            border-color: #28a745;
            color: white;
        }
        #message.alert-danger {
            background-color: rgba(220, 53, 69, 0.9);
            border-color: #dc3545;
            color: white;
        }
        body {
            background: transparent;
            padding: 20px;
            min-height: auto;
        }
        
        .form-control {
            background: rgba(0, 0, 0, 0.5) !important;
            border: 1px solid rgba(201, 208, 17, 0.3) !important;
            color: #c9d011 !important;
            padding: 0.75rem !important;
            border-radius: var(--border-radius) !important;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(201, 208, 17, 0.2) !important;
            border-color: #c9d011 !important;
        }

        .form-label {
            color: #c9d011;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-text {
            color: rgba(201, 208, 17, 0.7) !important;
        }

        .btn-primary {
            background: rgba(201, 208, 17, 0.3) !important;
            color: #c9d011 !important;
            border: 1px solid rgba(201, 208, 17, 0.5) !important;
            padding: 0.75rem 1.5rem !important;
            font-weight: 500 !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
        }

        .btn-primary:hover {
            background: #c9d011 !important;
            color: #000 !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(201, 208, 17, 0.3);
        }

        .btn-secondary {
            background: rgba(0, 0, 0, 0.3) !important;
            color: #c9d011 !important;
            border: 1px solid rgba(201, 208, 17, 0.3) !important;
            padding: 0.75rem 1.5rem !important;
        }

        .btn-secondary:hover {
            background: rgba(201, 208, 17, 0.1) !important;
            color: #ffff00 !important;
        }
    </style>
</head>
<body>
    <div id="message" class="alert d-none mb-4"></div>
    <form id="addStakeholderForm" action="../php/ogdınamık/stakeholders/insert.php" method="POST" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="name" class="form-label">
                <i class="fas fa-building me-2"></i>Paydaş Adı
            </label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        
        <div class="mb-4">
            <label for="description" class="form-label">
                <i class="fas fa-align-left me-2"></i>Açıklama
            </label>
            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
        </div>
        
        <div class="mb-4">
            <label for="image" class="form-label">
                <i class="fas fa-image me-2"></i>Paydaş Logosu
            </label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
        </div>
        
        <div class="d-flex justify-content-end align-items-center mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Paydaş Ekle
            </button>
        </div>
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            let isImageValid = false;

            // Görsel yükleme kontrolü
            $('#image').on('change', function() {
                var file = this.files[0];
                if (!file) return;

                var reader = new FileReader();
                
                reader.onload = function(e) {
                    var img = new Image();
                    img.onload = function() {
                        // Boyut kontrolü
                        if (this.width !== 500 || this.height !== 500) {
                            const message = `Görsel boyutu (${this.width}x${this.height}) uygun değil. Lütfen 500x500 piksel kullanın.`;
                            const $message = $('#message');
                            $message
                                .removeClass('d-none alert-success')
                                .addClass('alert-danger')
                                .html(message.replace(/\n/g, '<br>'))
                                .fadeIn();
                            setTimeout(() => {
                                $message.fadeOut();
                            }, 5000);
                            $('#image').val('');
                            isImageValid = false;
                            return;
                        }

                        // En-boy oranı kontrolü (1:1'e yakın olmalı)
                        const aspectRatio = this.width / this.height;
                        if (aspectRatio < 0.8 || aspectRatio > 1.2) {
                            const message = `Görsel oranı uygun değil! Lütfen kare formatta (1:1) bir görsel seçin.`;
                            const $message = $('#message');
                            $message
                                .removeClass('d-none alert-success')
                                .addClass('alert-danger')
                                .text(message)
                                .fadeIn();
                            setTimeout(() => {
                                $message.fadeOut();
                            }, 5000);
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

            $('#addStakeholderForm').on('submit', function(e) {
                e.preventDefault();

                // Görsel kontrolü
                if (!isImageValid) {
                    const $message = $('#message');
                    $message
                        .removeClass('d-none alert-success')
                        .addClass('alert-danger')
                        .text('Lütfen uygun boyut ve oranda bir görsel seçin!')
                        .fadeIn();
                    setTimeout(() => {
                        $message.fadeOut();
                    }, 5000);
                    return;
                }
                
                var formData = new FormData(this);
                
                $.ajax({
                    url: '../php/ogdınamık/stakeholders/insert.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        try {
                            response = typeof response === 'string' ? JSON.parse(response) : response;
                            
                            const $message = $('#message');
                            
                            if(response.success) {
                                $message
                                    .removeClass('d-none alert-danger')
                                    .addClass('alert-success')
                                    .text(response.message)
                                    .fadeIn();
                                
                                // Formu temizle
                                $('#addStakeholderForm')[0].reset();
                                
                                // 3 saniye sonra mesajı gizle
                                setTimeout(() => {
                                    $message.fadeOut();
                                }, 3000);
                                
                                isImageValid = false; // Formu sıfırla
                            } else {
                                $message
                                    .removeClass('d-none alert-success')
                                    .addClass('alert-danger')
                                    .text(response.message || 'Bir hata oluştu')
                                    .fadeIn();
                            }
                        } catch (e) {
                            $('#message')
                                .removeClass('d-none alert-success')
                                .addClass('alert-danger')
                                .text('Sunucu yanıtı işlenirken bir hata oluştu')
                                .fadeIn();
                        }
                    },
                    error: function(xhr, status, error) {
                        const $message = $('#message');
                        let errorMessage = 'Bir hata oluştu. ';
                        
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMessage += response.message || 'Lütfen tekrar deneyin.';
                        } catch (e) {
                            if (xhr.status === 413) {
                                errorMessage += 'Dosya boyutu çok büyük.';
                            } else if (xhr.status === 404) {
                                errorMessage += 'Sunucu bağlantısı kurulamadı.';
                            } else if (xhr.status === 500) {
                                errorMessage += 'Sunucu hatası oluştu.';
                            } else {
                                errorMessage += 'Lütfen tekrar deneyin.';
                            }
                        }

                        $message
                            .removeClass('d-none alert-success')
                            .addClass('alert-danger')
                            .text(errorMessage)
                            .fadeIn();
                        
                        // 5 saniye sonra hata mesajını gizle
                        setTimeout(() => {
                            $message.fadeOut();
                        }, 5000);
                    }
                });
            });
        });
    </script>
</body>
</html>