<?php
session_start();
require_once '../php/config.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTGET Yönetim Paneli</title>
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="scripts/admin.js"></script>
</head>
<body>
    <!-- Bildirimler için konteyner -->
    <div id="notifications" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3>OTGET Yönetim</h3>
            </div>
            <ul class="list-unstyled components">
                <li>
                    <a href="#istatistikler" class="nav-link active">
                        <i class="fas fa-chart-line"></i>
                        <span>Gösterge Paneli</span>
                    </a>
                </li>
                <li>
                    <a href="#paydas" class="nav-link">
                        <i class="fas fa-handshake-angle"></i>
                        <span>Paydaşlar</span>
                    </a>
                </li>
                <li>
                    <a href="#sponsor" class="nav-link">
                        <i class="fas fa-coins"></i>
                        <span>Sponsorlar</span>
                    </a>
                </li>
                <li>
                    <a href="#juri" class="nav-link">
                        <i class="fas fa-scale-balanced"></i>
                        <span>Jüri Üyeleri</span>
                    </a>
                </li>
                <li>
                    <a href="#program" data-toggle="tab" class="nav-link">
                        <i class="fas fa-calendar-days"></i>
                        <span>Etkinlik Programı</span>
                    </a>
                </li>
                <li class="mt-auto">
                    <a href="../WebSayfası/index.html" class="nav-link logout">
                        <i class="fas fa-right-from-bracket"></i>
                        <span>Güvenli Çıkış</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- İçerik -->
        <div id="content">
            <!-- Üst Navbar -->
            <nav class="navbar">
                <button type="button" id="sidebarCollapse">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>

            <!-- Tab İçerikleri -->
            <div class="tab-content" id="tabContent">
                <!-- Paydaş Paneli -->
                <div class="tab-pane" id="paydas">
                    <div class="section-header">
                        <div class="header-top">
                            <div class="header-title">
                                <h4>Paydaşlar</h4>
                                <?php
                                $sql ="SELECT COUNT(*) as count FROM stakeholders";
                                $stmt = $pdo->query($sql);
                                $stakeholderCount = $stmt->fetchColumn();
                                ?>
                                <p>Toplam <?php echo $stakeholderCount; ?> paydaş</p>
                            </div>
                        </div>
                        <div class="header-bottom">
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" placeholder="Paydaş ara...">
                            </div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStakeholderModal">
                                <i class="fas fa-plus"></i>
                                <span>Yeni Paydaş Ekle</span>
                            </button>
                        </div>
                    </div>
                    
                    <?php if(isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="partner-grid">
                    <?php
                    $sql = "SELECT id, name, description, image_filename FROM stakeholders ORDER BY id DESC";
                    $stmt = $pdo->query($sql);
                    while ($stakeholder = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                        <div class="partner-card">
                            <div class="card-logo">
                                <img src="../php/gallery/payduplouds/<?php echo htmlspecialchars($stakeholder['image_filename']); ?>"
                                     alt="<?php echo htmlspecialchars($stakeholder['name']); ?>">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($stakeholder['name']); ?></h5>
                                <p class="card-meta"><?php echo htmlspecialchars($stakeholder['description']); ?></p>
                            </div>
                            <div class="card-footer">
                                <div class="btn-group">
                                    <button class="btn btn-icon" title="Düzenle" onclick="editStakeholder(<?php echo $stakeholder['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-icon" title="Sil" onclick="deleteStakeholder(<?php echo $stakeholder['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                    if ($stmt->rowCount() == 0) {
                        echo "<p>Henüz paydaş bulunmamaktadır.</p>";
                    }
                    ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
          
    <!-- Modal -->
    <div class="modal fade" id="addStakeholderModal" tabindex="-1" aria-labelledby="addStakeholderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStakeholderModalLabel">Yeni Paydaş Ekle</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <div class="modal-body">
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
                            <div id="imageError" class="alert alert-danger mt-2 d-none"></div>
                        </div>
                        
                        <div class="d-flex justify-content-end align-items-center mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Paydaş Ekle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Yukarı çık butonu kontrolü
        const scrollToTopBtn = document.getElementById("scrollToTop");
        
        window.addEventListener("scroll", function() {
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                scrollToTopBtn.classList.remove("d-none");
            } else {
                scrollToTopBtn.classList.add("d-none");
            }
        });

        scrollToTopBtn.addEventListener("click", function() {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });

        // Görsel boyut kontrolü
        document.getElementById('image').addEventListener('change', function() {
            var file = this.files[0];
            if (!file) return;

            var reader = new FileReader();
            reader.onload = function(e) {
                var img = new Image();
                img.onload = function() {
                    // En-boy oranı kontrolü
                    const aspectRatio = this.width / this.height;
                    if (aspectRatio < 0.8 || aspectRatio > 1.2) {
                        const message = "Görsel oranı uygun değil! Lütfen kare formatta (1:1) bir görsel seçin.";
                        const errorDiv = document.getElementById('imageError');
                        errorDiv.textContent = message;
                        errorDiv.classList.remove('d-none');
                        document.getElementById('image').value = '';
                        setTimeout(() => {
                            errorDiv.classList.add('d-none');
                        }, 5000);
                        return;
                    }

                    // Boyut kontrolü
                    if (this.width !== 500 || this.height !== 500) {
                        const message = `Görsel boyutu (${this.width}x${this.height}) uygun değil. Lütfen 500x500 piksel kullanın.`;
                        const errorDiv = document.getElementById('imageError');
                        errorDiv.textContent = message;
                        errorDiv.classList.remove('d-none');
                        document.getElementById('image').value = '';
                        setTimeout(() => {
                            errorDiv.classList.add('d-none');
                        }, 5000);
                    }
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });

        // URL hash değişimini ve mesajları yönet
        function initPage() {
            if(window.location.hash === '#paydas') {
                $('.nav-link').removeClass('active');
                $('.tab-pane').removeClass('show active');
                $('a[href="#paydas"]').addClass('active');
                $('#paydas').addClass('show active');
            }         
        }
         
        document.addEventListener('DOMContentLoaded', initPage);
        window.addEventListener('hashchange', initPage);
    </script>

    <!-- Yukarı Çık Butonu -->
    <button id="scrollToTop" class="position-fixed d-none" style="bottom: 30px; right: 30px; z-index: 9999; width: 50px; height: 50px; border: none; background: none; opacity: 0;">
        <div class="scroll-btn-wrapper">
            <div class="scroll-btn-circle">
                <i class="fas fa-rocket"></i>
            </div>
            <div class="scroll-btn-rays"></div>
        </div>
    </button>

    <script>
        // Yukarı çık butonu kontrolü
        window.onscroll = function() {
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                document.getElementById("scrollToTop").style.display = "block";
            } else {
                document.getElementById("scrollToTop").style.display = "none";
            }
        };

        // Yukarı çık butonu tıklama olayı
        document.getElementById("scrollToTop").addEventListener("click", function() {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });
    </script>
</body>
</html>