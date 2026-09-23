<?php
session_start();
require_once __DIR__ . '/../../controller/conexion.php';
require_once __DIR__ . '/../changeHistory/registrar_cambio.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'No autorizado.']);
    exit;
}

$number_id = isset($_POST['number_id']) ? trim($_POST['number_id']) : '';
$gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
$birthdate = isset($_POST['birthdate']) ? trim($_POST['birthdate']) : '';

if ($number_id === '' || $gender === '' || $birthdate === '') {
    echo json_encode(['ok' => false, 'message' => 'Datos incompletos.']);
    exit;
}

if (!in_array($gender, ['Hombre', 'Mujer'], true)) {
    echo json_encode(['ok' => false, 'message' => 'Género inválido.']);
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
    echo json_encode(['ok' => false, 'message' => 'Fecha de nacimiento inválida.']);
    exit;
}

try {
    $stmtOld = $conn->prepare("SELECT gender, birthdate FROM user_register WHERE number_id = ? LIMIT 1");
    $stmtOld->bind_param('s', $number_id);
    $stmtOld->execute();
    $old = $stmtOld->get_result()->fetch_assoc() ?: [];

    $stmt = $conn->prepare("UPDATE user_register SET gender = ?, birthdate = ?, dayUpdate = NOW() WHERE number_id = ?");
    $stmt->bind_param('sss', $gender, $birthdate, $number_id);
    $stmt->execute();

    $descripcion = describirCambiosHistorial(
        ['gender' => 'Género', 'birthdate' => 'Fecha de nacimiento'],
        ['gender' => $old['gender'] ?? '', 'birthdate' => $old['birthdate'] ?? ''],
        ['gender' => $gender, 'birthdate' => $birthdate]
    );
    if ($descripcion !== '') {
        registrarCambioHistorial($conn, $number_id, 'Actualización de información personal - ' . $descripcion);
    }

    echo json_encode(['ok' => true, 'message' => 'Información personal actualizada.']);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'message' => 'Error al actualizar la información personal.']);
}
