<?php
session_start();
require_once __DIR__ . '/../../controller/conexion.php';
require_once __DIR__ . '/../changeHistory/registrar_cambio.php';

function nombreDepartamentoUbic($conn, $id)
{
    if ($id === '' || $id === null) {
        return '';
    }
    $s = $conn->prepare("SELECT departamento FROM departamentos WHERE id_departamento = ? LIMIT 1");
    $s->bind_param('s', $id);
    $s->execute();
    $r = $s->get_result()->fetch_assoc();
    return $r ? $r['departamento'] : $id;
}

function nombreMunicipioUbic($conn, $cod)
{
    if ($cod === '' || $cod === null) {
        return '';
    }
    $s = $conn->prepare("SELECT nom_municipio FROM municipios WHERE cod_municipio = ? LIMIT 1");
    $s->bind_param('s', $cod);
    $s->execute();
    $r = $s->get_result()->fetch_assoc();
    return $r ? $r['nom_municipio'] : $cod;
}
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
    $stmtOld = $conn->prepare("SELECT department, municipality, address, residence_area, comuna_corregimiento, barrio FROM user_register WHERE number_id = ? LIMIT 1");
    $stmtOld->bind_param('s', $number_id);
    $stmtOld->execute();
    $old = $stmtOld->get_result()->fetch_assoc() ?: [];

    $stmt = $conn->prepare("
        UPDATE user_register
        SET department = ?, municipality = ?, address = ?, residence_area = ?, comuna_corregimiento = ?, barrio = ?, dayUpdate = NOW()
        WHERE number_id = ?
    ");
    $stmt->bind_param('sssssss', $department, $municipality, $address, $residence_area, $comuna, $barrio, $number_id);
    $stmt->execute();

    $oldDepartamento = $old['department'] ?? '';
    $oldMunicipio = $old['municipality'] ?? '';

    $descripcion = describirCambiosHistorial(
        [
            'department' => 'Departamento',
            'municipality' => 'Municipio',
            'address' => 'Dirección',
            'residence_area' => 'Área',
            'comuna_corregimiento' => 'Comuna/Corregimiento',
            'barrio' => 'Barrio/Vereda',
        ],
        [
            'department' => nombreDepartamentoUbic($conn, $oldDepartamento),
            'municipality' => nombreMunicipioUbic($conn, $oldMunicipio),
            'address' => $old['address'] ?? '',
            'residence_area' => $old['residence_area'] ?? '',
            'comuna_corregimiento' => $old['comuna_corregimiento'] ?? '',
            'barrio' => $old['barrio'] ?? '',
        ],
        [
            'department' => nombreDepartamentoUbic($conn, $department),
            'municipality' => nombreMunicipioUbic($conn, $municipality),
            'address' => $address,
            'residence_area' => $residence_area,
            'comuna_corregimiento' => $comuna,
            'barrio' => $barrio,
        ]
    );

    if ($descripcion !== '') {
        registrarCambioHistorial($conn, $number_id, 'Actualización de ubicación - ' . $descripcion);
    }

    echo json_encode(['ok' => true, 'message' => 'Ubicación actualizada.']);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'message' => 'Error al actualizar la ubicación.']);
}
