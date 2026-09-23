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
$first_phone = isset($_POST['first_phone']) ? trim($_POST['first_phone']) : '';
$second_phone = isset($_POST['second_phone']) ? trim($_POST['second_phone']) : '';
$emergency_name = isset($_POST['emergency_contact_name']) ? trim($_POST['emergency_contact_name']) : '';
$emergency_number = isset($_POST['emergency_contact_number']) ? trim($_POST['emergency_contact_number']) : '';

$guardian_name = isset($_POST['guardian_full_name']) ? trim($_POST['guardian_full_name']) : '';
$guardian_document = isset($_POST['guardian_document']) ? trim($_POST['guardian_document']) : '';
$guardian_phone = isset($_POST['guardian_phone']) ? trim($_POST['guardian_phone']) : '';
$guardian_email = isset($_POST['guardian_email']) ? trim($_POST['guardian_email']) : '';

if ($number_id === '') {
    echo json_encode(['ok' => false, 'message' => 'Falta el identificador del estudiante.']);
    exit;
}

if (!preg_match('/^\d{10}$/', $first_phone)) {
    echo json_encode(['ok' => false, 'message' => 'El teléfono 1 debe tener 10 dígitos.']);
    exit;
}

if ($second_phone !== '' && !preg_match('/^\d{10}$/', $second_phone)) {
    echo json_encode(['ok' => false, 'message' => 'El teléfono 2 debe tener 10 dígitos.']);
    exit;
}

if ($emergency_name === '') {
    echo json_encode(['ok' => false, 'message' => 'El nombre del contacto de emergencia es obligatorio.']);
    exit;
}

$emergency_number = preg_replace('/\s+/', '', $emergency_number);
if (!preg_match('/^\+?\d{7,15}$/', $emergency_number)) {
    echo json_encode(['ok' => false, 'message' => 'El teléfono de emergencia no es válido.']);
    exit;
}

if ($guardian_phone !== '' && !preg_match('/^\+?\d{7,15}$/', $guardian_phone)) {
    echo json_encode(['ok' => false, 'message' => 'El teléfono del acudiente no es válido.']);
    exit;
}

if ($guardian_email !== '' && !filter_var($guardian_email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'message' => 'El email del acudiente no es válido.']);
    exit;
}

try {
    $stmtOld = $conn->prepare("SELECT first_phone, second_phone, emergency_contact_name, emergency_contact_number FROM user_register WHERE number_id = ? LIMIT 1");
    $stmtOld->bind_param('s', $number_id);
    $stmtOld->execute();
    $old = $stmtOld->get_result()->fetch_assoc() ?: [];

    $stmt = $conn->prepare("
        UPDATE user_register
        SET first_phone = ?, second_phone = ?, emergency_contact_name = ?, emergency_contact_number = ?, dayUpdate = NOW()
        WHERE number_id = ?
    ");
    $stmt->bind_param('sssss', $first_phone, $second_phone, $emergency_name, $emergency_number, $number_id);
    $stmt->execute();

    $descripcion = describirCambiosHistorial(
        [
            'first_phone' => 'Teléfono 1',
            'second_phone' => 'Teléfono 2',
            'emergency_contact_name' => 'Contacto emergencia',
            'emergency_contact_number' => 'Tel. emergencia',
        ],
        [
            'first_phone' => $old['first_phone'] ?? '',
            'second_phone' => $old['second_phone'] ?? '',
            'emergency_contact_name' => $old['emergency_contact_name'] ?? '',
            'emergency_contact_number' => $old['emergency_contact_number'] ?? '',
        ],
        [
            'first_phone' => $first_phone,
            'second_phone' => $second_phone,
            'emergency_contact_name' => $emergency_name,
            'emergency_contact_number' => $emergency_number,
        ]
    );

    if ($guardian_name !== '') {
        $stmtG = $conn->prepare("SELECT id, guardian_full_name, guardian_document, guardian_phone, guardian_email FROM acudientes WHERE number_id = ? ORDER BY id DESC LIMIT 1");
        $stmtG->bind_param('s', $number_id);
        $stmtG->execute();
        $guardian = $stmtG->get_result()->fetch_assoc();

        if ($guardian) {
            $descG = describirCambiosHistorial(
                [
                    'guardian_full_name' => 'Acudiente - Nombre',
                    'guardian_document' => 'Acudiente - Documento',
                    'guardian_phone' => 'Acudiente - Teléfono',
                    'guardian_email' => 'Acudiente - Email',
                ],
                $guardian,
                [
                    'guardian_full_name' => $guardian_name,
                    'guardian_document' => $guardian_document,
                    'guardian_phone' => $guardian_phone,
                    'guardian_email' => $guardian_email,
                ]
            );
            if ($descG !== '') {
                $descripcion .= ($descripcion !== '' ? ' | ' : '') . $descG;
            }

            $stmtU = $conn->prepare("
                UPDATE acudientes
                SET guardian_full_name = ?, guardian_document = ?, guardian_phone = ?, guardian_email = ?
                WHERE id = ?
            ");
            $stmtU->bind_param('ssssi', $guardian_name, $guardian_document, $guardian_phone, $guardian_email, $guardian['id']);
            $stmtU->execute();
        } else {
            $descripcion .= ($descripcion !== '' ? ' | ' : '') . 'Acudiente creado: "' . $guardian_name . '"';
            $stmtI = $conn->prepare("
                INSERT INTO acudientes (number_id, guardian_full_name, guardian_document, guardian_phone, guardian_email)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmtI->bind_param('sssss', $number_id, $guardian_name, $guardian_document, $guardian_phone, $guardian_email);
            $stmtI->execute();
        }
    }

    if ($descripcion !== '') {
        registrarCambioHistorial($conn, $number_id, 'Actualización de contacto - ' . $descripcion);
    }

    echo json_encode(['ok' => true, 'message' => 'Datos de contacto actualizados.']);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'message' => 'Error al actualizar los datos de contacto.']);
}
