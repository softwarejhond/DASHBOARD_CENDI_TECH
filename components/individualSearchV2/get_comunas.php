<?php
session_start();
require_once __DIR__ . '/../../controller/conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$out = [];
$result = $conn->query("SELECT codigo, nombre FROM comunas_corregimientos WHERE nombre IS NOT NULL AND nombre <> '' ORDER BY codigo");
while ($row = $result->fetch_assoc()) {
    $value = $row['codigo'] . ' - ' . $row['nombre'];
    $out[] = [
        'codigo' => $row['codigo'],
        'nombre' => $row['nombre'],
        'value' => $value,
    ];
}

echo json_encode($out);
