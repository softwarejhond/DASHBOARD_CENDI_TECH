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
$result = $conn->query("SELECT id_departamento, departamento FROM departamentos ORDER BY departamento");
while ($row = $result->fetch_assoc()) {
    $out[] = [
        'id_departamento' => $row['id_departamento'],
        'departamento' => $row['departamento'],
    ];
}

echo json_encode($out);
