<?php
require_once '../../config.php';

try {
    $sql = "ALTER TABLE stakeholders ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1";
    $pdo->exec($sql);
    
    // Mevcut kayıtları aktif olarak işaretle
    $sql = "UPDATE stakeholders SET is_active = 1 WHERE is_active IS NULL";
    $pdo->exec($sql);
    
    echo "is_active sütunu başarıyla eklendi ve mevcut kayıtlar aktif olarak işaretlendi.";
} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?>