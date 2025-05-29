<?php
session_start();

$response = [
    'success' => null,
    'error' => null
];

// Başarı mesajını al ve sil
if (isset($_SESSION['success'])) {
    $response['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Hata mesajını al ve sil
if (isset($_SESSION['error'])) {
    $response['error'] = $_SESSION['error'];
    unset($_SESSION['error']);
}

// JSON yanıt döndür
header('Content-Type: application/json');
echo json_encode($response);