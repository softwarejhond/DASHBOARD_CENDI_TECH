<?php
session_start();
require_once __DIR__ . '/../../controller/conexion.php';
require_once __DIR__ . '/config_notas.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'No autorizado.']);
    exit;
}

echo json_encode(['ok' => true, 'config' => obtenerConfigNotas($conn)]);
