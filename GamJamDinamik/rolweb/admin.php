<?php
session_start();
require_once '../php/config.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: ../WebSayfası/index.html");
    exit();
}
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
</head>
<body>
    <!-- Arka Plan -->
    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100vh; z-index: 0;">
        <!-- Gradient overlay -->
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%); opacity: 0.85; z-index: 1;"></div>
        <!-- Arka plan resmi -->
        <div style="background-image: url('../WebSayfası/resim/rg6.png'); position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-size: cover; background-position: center; z-index: 0; opacity: 0.95;"></div>
    </div>

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
                   <span onclick="window.location='../WebSayfası/index.html'" class="nav-link logout" style="cursor: pointer;">
                        <i class="fas fa-right-from-bracket"></i>
                        <span>Güvenli Çıkış</span>
                    </span>
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
                    <div class="section-header" style="background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(10px); padding: 20px; border-radius: 15px; margin-bottom: 20px; border: 1px solid rgba(201, 208, 17, 0.3);">
                        <div class="header-top">
                            <div class="header-title">
                                <h4 style="color: #c9d011; font-family: 'Press Start 2P', system-ui; font-size: 1.5rem; margin: 0;">Paydaşlar</h4>
                                <?php
                                $sql ="SELECT COUNT(*) as count FROM stakeholders";
                                $stmt = $pdo->query($sql);
                                $stakeholderCount = $stmt->fetchColumn();
                                ?>
                                <p style="color: rgba(201, 208, 17, 0.8); margin: 5px 0 0 0;">Toplam <?php echo $stakeholderCount; ?> paydaş</p>
                            </div>
                        </div>
                        <div class="header-bottom">
                            <div class="input-group" style="max-width: 500px;">
                                <input type="text" id="stakeholderSearch" class="form-control" placeholder="Paydaş adına göre ara...">
                                <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                    <i class="fas fa-search"></i> Ara
                                </button>
                                <button class="btn btn-outline-secondary" type="button" id="clearSearchButton">
                                    <i class="fas fa-times"></i> Temizle
                                </button>
                            </div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStakeholderModal">
                                <i class="fas fa-plus"></i>
                                <span>Yeni Paydaş Ekle</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="partner-grid mt-4">
                    <?php
                    $sql = "SELECT id, name, description, image_filename FROM stakeholders ORDER BY id DESC";
                    $stmt = $pdo->query($sql);
                    while ($stakeholder = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                        <div class="partner-card" data-id="<?php echo $stakeholder['id']; ?>">
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

    <!-- Ekleme Modal -->
    <div class="modal fade" id="addStakeholderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="background: rgba(0, 0, 0, 0.9); border: 1px solid rgba(201, 208, 17, 0.3);">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStakeholderModalLabel" style="color: #c9d011; font-family: 'Press Start 2P', system-ui; font-size: 1.2rem;">Yeni Paydaş Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addStakeholderForm" action="../php/ogdınamık/stakeholders/insert.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-building me-2"></i>Paydaş Adı
                            </label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-2"></i>Açıklama
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">
                                <i class="fas fa-image me-2"></i>Logo (500x500)
                            </label>
                            <div class="image-upload-container">
                                <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                                <div id="imagePreview" class="image-preview mt-2" style="display: none;">
                                    <img src="" alt="Preview" style="max-width: 200px;">
                                </div>
                                <div class="alert alert-info mt-2">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Lütfen tam olarak 500x500 piksel boyutunda bir görsel yükleyin.
                                </div>
                                <div id="imageError" class="alert alert-danger mt-2" style="display: none;"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Düzenleme Modal -->
    <div class="modal fade" id="editStakeholderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="background: rgba(0, 0, 0, 0.9); border: 1px solid rgba(201, 208, 17, 0.3);">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStakeholderModalLabel" style="color: #c9d011; font-family: 'Press Start 2P', system-ui; font-size: 1.2rem;">Paydaş Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editStakeholderForm" action="../php/ogdınamık/stakeholders/uptedit.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">
                                <i class="fas fa-building me-2"></i>Paydaş Adı
                            </label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">
                                <i class="fas fa-align-left me-2"></i>Açıklama
                            </label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-image me-2"></i>Mevcut Logo
                            </label>
                            <div id="currentImageContainer" class="text-center mb-3"></div>
                            <label for="edit_image" class="form-label">
                                <i class="fas fa-upload me-2"></i>Yeni Logo (İsteğe bağlı)
                            </label>
                            <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                            <div class="alert alert-info mt-2">
                                <i class="fas fa-info-circle me-2"></i>
                                Yeni bir görsel yüklerseniz, 500x500 piksel boyutunda olmalıdır.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="scripts/admin.js"></script>
</body>
</html>