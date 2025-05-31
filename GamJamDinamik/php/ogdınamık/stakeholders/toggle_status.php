<?php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Oturum zaman aşımına uğradı'
    ]);
    exit;
}

try {
    if (!isset($_POST['id'])) {
        throw new Exception("Geçersiz ID");
    }

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        throw new Exception("Geçersiz ID formatı");
    }

    // Transaction başlat
    $pdo->beginTransaction();

    try {
        // Mevcut durumu kontrol et
        $stmt = $pdo->prepare("SELECT is_active FROM stakeholders WHERE id = ? FOR UPDATE");
        $stmt->execute([$id]);
        $currentStatus = $stmt->fetchColumn();

        if ($currentStatus === false) {
            throw new Exception("Paydaş bulunamadı");
        }

        // Durumu tersine çevir (toggle)
        $newStatus = $currentStatus ? 0 : 1;

        // Durumu güncelle
        $stmt = $pdo->prepare("UPDATE stakeholders SET is_active = ? WHERE id = ?");
        if (!$stmt->execute([$newStatus, $id])) {
            throw new Exception("Güncelleme başarısız oldu");
        }

        // Transaction'ı tamamla
        $pdo->commit();

        // Başarılı sonuç döndür
        echo json_encode([
            'success' => true,
            'message' => $newStatus ? 'Paydaş aktif hale getirildi' : 'Paydaş pasif hale getirildi',
            'new_status' => $newStatus
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    error_log("PDO Hatası: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası oluştu'
    ]);
}
?>