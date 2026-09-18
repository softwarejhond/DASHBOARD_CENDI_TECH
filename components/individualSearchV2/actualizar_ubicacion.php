<?php
session_start();
require_once __DIR__ . '/../../controller/conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'No autorizado.']);
    exit;
}

$number_id = isset($_POST['number_id']) ? trim($_POST['number_id']) : '';
$department = isset($_POST['department']) ? trim($_POST['department']) : '';
$municipality = isset($_POST['municipality']) ? trim($_POST['municipality']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$residence_area = isset($_POST['residence_area']) ? trim($_POST['residence_area']) : '';
$comuna = isset($_POST['comuna_corregimiento']) ? trim($_POST['comuna_corregimiento']) : '';
$barrio = isset($_POST['barrio']) ? trim($_POST['barrio']) : '';

if ($number_id === '') {
    echo json_encode(['ok' => false, 'message' => 'Falta el identificador del estudiante.']);
    exit;
}

if ($department === '' || $municipality === '') {
    echo json_encode(['ok' => false, 'message' => 'Departamento y municipio son obligatorios.']);
    exit;
}

if ($address === '') {
    echo json_encode(['ok' => false, 'message' => 'La dirección es obligatoria.']);
    exit;
}

try {
    $stmt = $conn->prepare("
        UPDATE user_register
        SET department = ?, municipality = ?, address = ?, residence_area = ?, comuna_corregimiento = ?, barrio = ?, dayUpdate = NOW()
        WHERE number_id = ?
    ");
    $stmt->bind_param('sssssss', $department, $municipality, $address, $residence_area, $comuna, $barrio, $number_id);
    $stmt->execute();

    echo json_encode(['ok' => true, 'message' => 'Ubicación actualizada.']);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'message' => 'Error al actualizar la ubicación.']);
}
