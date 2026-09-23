<?php
session_start();
require_once __DIR__ . '/../../controller/conexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$especificacion = isset($_POST['especificacion']) ? trim($_POST['especificacion']) : '';

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de PQR inválido']);
    exit;
}

$stmt = $conn->prepare("UPDATE pqr SET especificacion = ? WHERE id = ?");

if (!$stmt) {
    error_log("Error al preparar update_especificacion: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
    exit;
}

$stmt->bind_param("si", $especificacion, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Especificación guardada correctamente']);
} else {
    error_log("Error al ejecutar update_especificacion: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Error al guardar la especificación']);
}

$stmt->close();
$conn->close();
