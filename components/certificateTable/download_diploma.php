<?php
// =====================================================================
// Sirve el PDF de un diploma emitido a partir de su token.
// Valida sesión y que el archivo esté dentro del directorio /diplomas.
// =====================================================================

session_start();
require_once __DIR__ . '/../../controller/conexion.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo 'No autorizado';
    exit;
}

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
if ($token === '') {
    http_response_code(404);
    echo 'Diploma no encontrado';
    exit;
}

$stmt = $conn->prepare("SELECT archivo FROM diplomas_emitidos WHERE token = ? LIMIT 1");
$stmt->bind_param('s', $token);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$row || empty($row['archivo'])) {
    http_response_code(404);
    echo 'Diploma no encontrado';
    exit;
}

$baseDir = realpath(__DIR__ . '/../../diplomas');
$safePath = realpath($row['archivo']);

if ($baseDir === false || $safePath === false || !is_file($safePath)
    || strpos($safePath, $baseDir) !== 0) {
    http_response_code(404);
    echo 'Archivo no encontrado';
    exit;
}

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="diploma_' . basename($safePath) . '"');
header('Content-Length: ' . filesize($safePath));
readfile($safePath);
exit;
