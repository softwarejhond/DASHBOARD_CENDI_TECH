<?php
session_start();
require_once __DIR__ . '/../../controller/conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$department_id = isset($_GET['department_id']) ? trim($_GET['department_id']) : '';
if ($department_id === '') {
    echo json_encode([]);
    exit;
}

$out = [];
$stmt = $conn->prepare("SELECT cod_municipio, nom_municipio FROM municipios WHERE cod_departamento = ? ORDER BY nom_municipio");
$stmt->bind_param('s', $department_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $out[] = [
        'cod_municipio' => $row['cod_municipio'],
        'nom_municipio' => $row['nom_municipio'],
    ];
}

echo json_encode($out);
