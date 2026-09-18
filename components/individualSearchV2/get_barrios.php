<?php
session_start();
require_once __DIR__ . '/../../controller/conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$comuna = isset($_GET['comuna']) ? trim($_GET['comuna']) : '';
if ($comuna === '') {
    echo json_encode([]);
    exit;
}

$out = [];
$stmt = $conn->prepare("SELECT nombre FROM barrios WHERE limite_comuna_corregimiento_id = ? AND nombre IS NOT NULL AND nombre <> '' ORDER BY nombre");
$stmt->bind_param('s', $comuna);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $out[] = ['nombre' => $row['nombre']];
}

echo json_encode($out);
