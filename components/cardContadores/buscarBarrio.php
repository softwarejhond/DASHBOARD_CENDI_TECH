<?php
session_start();
require_once '../../controller/conexion.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'no autorizado']);
    exit;
}

header('Content-Type: application/json');

$barrio = isset($_GET['barrio']) ? trim($_GET['barrio']) : '';

if ($barrio === '') {
    echo json_encode(['total' => 0]);
    exit;
}

$total = 0;
$stmt = $conn->prepare("SELECT COUNT(*) FROM user_register WHERE barrio = ?");
$stmt->bind_param('s', $barrio);
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

echo json_encode(['total' => (int) $total]);
