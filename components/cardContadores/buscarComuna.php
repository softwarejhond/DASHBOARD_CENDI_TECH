<?php
session_start();
require_once '../../controller/conexion.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'no autorizado']);
    exit;
}

header('Content-Type: application/json');

$comuna = isset($_GET['comuna']) ? trim($_GET['comuna']) : '';

if ($comuna === '') {
    echo json_encode(['total' => 0]);
    exit;
}

$total = 0;
$stmt = $conn->prepare("SELECT COUNT(*) FROM user_register WHERE comuna_corregimiento = ?");
$stmt->bind_param('s', $comuna);
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

echo json_encode(['total' => (int) $total]);
