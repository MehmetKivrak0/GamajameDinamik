<?php
// Veritabanı bağlantısını al
require_once '../../config.php';

try {
    // Paydaşları çek
    $stmt = $pdo->prepare("
        SELECT id, name, description, image_filename, created_by as username
        FROM stakeholders
        ORDER BY id DESC
    ");
    $stmt->execute();
    $stakeholders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Her paydaş için HTML üret
    foreach ($stakeholders as $stakeholder) {
        ?>
        <div class="partner-card">
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
} catch (PDOException $e) {
    echo "Hata oluştu: " . $e->getMessage();
}
?>
