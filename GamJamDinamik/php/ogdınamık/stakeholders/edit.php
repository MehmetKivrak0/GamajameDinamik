<?php

require_once '../../config.php';

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM stakeholders WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($data);

?>