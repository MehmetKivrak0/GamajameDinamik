<?php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception("Geçersiz ID");
    }

    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM stakeholders WHERE id = ?");
    $stmt->execute([$id]);
    $stakeholder = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$stakeholder) {
        throw new Exception("Paydaş bulunamadı");
    }

    echo json_encode([
        'success' => true,
        'stakeholder' => $stakeholder
    ]);

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