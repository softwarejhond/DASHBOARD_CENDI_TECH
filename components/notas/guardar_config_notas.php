<?php
session_start();
require_once __DIR__ . '/../../controller/conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'No autorizado.']);
    exit;
}

$pesoTecnico = isset($_POST['peso_tecnico']) ? trim($_POST['peso_tecnico']) : '';
$pesoIngles = isset($_POST['peso_ingles']) ? trim($_POST['peso_ingles']) : '';
$pesoHabilidades = isset($_POST['peso_habilidades']) ? trim($_POST['peso_habilidades']) : '';
$notaMinima = isset($_POST['nota_minima_aprobacion']) ? trim($_POST['nota_minima_aprobacion']) : '';

if ($pesoTecnico === '' || $pesoIngles === '' || $pesoHabilidades === '' || $notaMinima === '') {
    echo json_encode(['ok' => false, 'message' => 'Todos los campos son obligatorios.']);
    exit;
}

if (!is_numeric($pesoTecnico) || !is_numeric($pesoIngles) || !is_numeric($pesoHabilidades) || !is_numeric($notaMinima)) {
    echo json_encode(['ok' => false, 'message' => 'Los valores deben ser numéricos.']);
    exit;
}

$pesoTecnico = (float) $pesoTecnico;
$pesoIngles = (float) $pesoIngles;
$pesoHabilidades = (float) $pesoHabilidades;
$notaMinima = (float) $notaMinima;

if ($pesoTecnico < 0 || $pesoTecnico > 100 || $pesoIngles < 0 || $pesoIngles > 100 || $pesoHabilidades < 0 || $pesoHabilidades > 100) {
    echo json_encode(['ok' => false, 'message' => 'Los porcentajes deben estar entre 0 y 100.']);
    exit;
}

if (round($pesoTecnico + $pesoIngles + $pesoHabilidades, 2) !== 100.0) {
    echo json_encode(['ok' => false, 'message' => 'Los porcentajes deben sumar 100%.']);
    exit;
}

if ($notaMinima < 0 || $notaMinima > 5) {
    echo json_encode(['ok' => false, 'message' => 'La nota mínima para aprobar debe estar entre 0.0 y 5.0.']);
    exit;
}

try {
    $stmt = $conn->prepare("
        INSERT INTO notas_pesos (id, peso_tecnico, peso_ingles, peso_habilidades, nota_minima_aprobacion)
        VALUES (1, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            peso_tecnico = VALUES(peso_tecnico),
            peso_ingles = VALUES(peso_ingles),
            peso_habilidades = VALUES(peso_habilidades),
            nota_minima_aprobacion = VALUES(nota_minima_aprobacion)
    ");
    $stmt->bind_param('dddd', $pesoTecnico, $pesoIngles, $pesoHabilidades, $notaMinima);
    $stmt->execute();

    echo json_encode([
        'ok' => true,
        'message' => 'Configuración de notas actualizada.',
        'config' => [
            'peso_tecnico' => $pesoTecnico,
            'peso_ingles' => $pesoIngles,
            'peso_habilidades' => $pesoHabilidades,
            'nota_minima_aprobacion' => $notaMinima,
        ],
    ]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'message' => 'Error al guardar la configuración.']);
}
