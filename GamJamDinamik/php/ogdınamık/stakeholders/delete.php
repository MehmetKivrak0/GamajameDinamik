<?php
header('Content-Type: application/json');
session_start();
require_once '../../config.php';

$response = ['success' => false, 'message' => ''];

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $response['message'] = 'Geçersiz ID';
    echo json_encode($response);
    exit;
}

$id = (int) $_GET['id'];

try {
    $sql = "DELETE FROM stakeholders WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $result = $stmt->execute();

    // Tüm paydaşların sayısını yeniden hesapla
    $stmtcount = $pdo->query("SELECT COUNT(*) as total FROM stakeholders");
    $count = $stmtcount->fetch(PDO::FETCH_ASSOC)['total'];
    $_SESSION['total_stakeholders'] = $count;

    if ($result) {
        $response['success'] = true;
        $response['message'] = 'Silindi';
    } else {
        $response['message'] = 'Silme başarısız';
    }
} catch (PDOException $e) {
    error_log("Silme hatası: " . $e->getMessage());
    $response['message'] = 'Hata oluştu';
}

echo json_encode($response);
