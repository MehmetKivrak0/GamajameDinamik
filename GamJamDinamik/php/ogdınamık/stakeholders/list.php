<?php
// Veritabanı bağlantısını al
require_once '../../config.php';

try {
    // Paydaşları çek
    $stmt = $pdo->prepare("
        SELECT id, name, description, image_filename, created_by as username, is_active
        FROM stakeholders
        ORDER BY id DESC
    ");
    $stmt->execute();
    $stakeholders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Her paydaş için HTML üret
    foreach ($stakeholders as $stakeholder) {
        ?>
        <div class="partner-card" data-active="<?php echo $stakeholder['is_active']; ?>">
            <div class="card-logo">
            <img src="/GamJamDinamik/php/gallery/payduplouds/<?php echo htmlspecialchars($stakeholder['image_filename']); ?>" alt="<?php echo htmlspecialchars($stakeholder['name']); ?>">            </div>
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($stakeholder['name']) ?></h5>
                <p class="card-meta"><?= htmlspecialchars($stakeholder['description']) ?></p>
                <div class="card-tags">
                    <span class="tag">Teknoloji</span>
                    <span class="tag">Eğitim</span>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
    <button id="filterButton" class="btn btn-primary mt-3">
        <i class="fas fa-filter"></i>
        Tümünü Göster
    </button>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterButton = document.getElementById('filterButton');
        let filterState = 'all'; // all, active, inactive

        filterButton.addEventListener('click', function() {
            const cards = document.querySelectorAll('.partner-card');
            
            switch(filterState) {
                case 'all':
                    // Sadece aktifleri göster
                    cards.forEach(card => {
                        if(card.dataset.active === "1") {
                            card.style.display = 'flex';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                    filterButton.innerHTML = '<i class="fas fa-filter"></i> Aktif Paydaşlar';
                    filterState = 'active';
                    break;

                case 'active':
                    // Sadece pasifleri göster
                    cards.forEach(card => {
                        if(card.dataset.active === "0") {
                            card.style.display = 'flex';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                    filterButton.innerHTML = '<i class="fas fa-filter"></i> Pasif Paydaşlar';
                    filterState = 'inactive';
                    break;

                case 'inactive':
                    // Hepsini göster
                    cards.forEach(card => {
                        card.style.display = 'flex';
                    });
                    filterButton.innerHTML = '<i class="fas fa-filter"></i> Tümünü Göster';
                    filterState = 'all';
                    break;
            }
        });
    });
    </script>
} catch (PDOException $e) {
    echo "Hata oluştu: " . $e->getMessage();
}
?>
