<?php
// =====================================================================
// Endpoint server-side para el listado masivo de inscritos (DataTables).
// Devuelve JSON con paginación, búsqueda y ordenamiento en lotes.
// Tablas: user_register, enrollments, sets_cursos, cursos,
//         departamentos, municipios y acudientes.
// =====================================================================

session_start();
require_once __DIR__ . '/../../controller/conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'no autorizado']);
    exit;
}

// ---------------------------------------------------------------------
// Parámetros del protocolo server-side de DataTables
// ---------------------------------------------------------------------
$draw   = isset($_POST['draw']) ? (int) $_POST['draw'] : 1;
$start  = isset($_POST['start']) ? (int) $_POST['start'] : 0;
$length = isset($_POST['length']) ? (int) $_POST['length'] : 10;
if ($length < 0) {
    $length = 50; // por seguridad, nunca cargar todo de golpe
}

$search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';

$orderCol = isset($_POST['order'][0]['column']) ? (int) $_POST['order'][0]['column'] : 1;
$orderDir = (isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'desc') ? 'DESC' : 'ASC';

// ---------------------------------------------------------------------
// Campos y ordenamiento (whitelist en el mismo orden que las columnas JS)
// ---------------------------------------------------------------------
$selectFields = [
    'ur.typeID',
    'ur.number_id',
    'ur.first_name', 'ur.second_name', 'ur.first_last', 'ur.second_last',
    'ur.gender',
    'TIMESTAMPDIFF(YEAR, ur.birthdate, CURDATE()) AS age',
    'ur.birthdate',
    'ur.nationality',
    'ur.first_phone',
    'ur.second_phone',
    'ur.email',
    'ur.email_verified',
    'ur.emergency_contact_name',
    'ur.emergency_contact_number',
    'd.departamento AS departamento_nombre',
    'm.nom_municipio AS municipio_nombre',
    'ur.address',
    'ur.residence_area',
    'ur.comuna_corregimiento',
    'ur.barrio',
    'ur.program',
    'ur.mode',
    'ur.creationDate',
    'e.program_name AS mat_program_name',
    'e.status AS mat_status',
    'sc.serie AS mat_serie',
    'sc.codigo_tecnico AS mat_codigo_tecnico',
    'c1.course_name AS tecnico_name', 'c1.course_code AS tecnico_code',
    'c2.course_name AS ingles_name', 'c2.course_code AS ingles_code',
    'c3.course_name AS habilidades_name', 'c3.course_code AS habilidades_code',
    'e.institutional_email AS mat_institutional_email',
    'e.username AS mat_username',
    'e.created_at AS mat_created_at',
    'acu.guardian_full_name',
    'acu.guardian_document',
    'acu.guardian_phone',
    'acu.guardian_email',
];

$orderable = [
    'ur.typeID',
    'ur.number_id',
    "CONCAT_WS(' ', ur.first_name, ur.second_name, ur.first_last, ur.second_last)",
    'ur.gender',
    'age',
    'ur.birthdate',
    'ur.nationality',
    'ur.first_phone',
    'ur.second_phone',
    'ur.email',
    'ur.email_verified',
    'ur.emergency_contact_name',
    'ur.emergency_contact_number',
    'departamento_nombre',
    'municipio_nombre',
    'ur.address',
    'ur.residence_area',
    'ur.comuna_corregimiento',
    'ur.barrio',
    'ur.program',
    'ur.mode',
    'ur.creationDate',
    'mat_program_name',
    'mat_status',
    'mat_serie',
    'mat_codigo_tecnico',
    'tecnico_name',
    'ingles_name',
    'habilidades_name',
    'mat_institutional_email',
    'mat_username',
    'mat_created_at',
    'guardian_full_name',
    'guardian_document',
    'guardian_phone',
    'guardian_email',
];

$orderExpr = isset($orderable[$orderCol]) ? $orderable[$orderCol] : 'ur.number_id';

// ---------------------------------------------------------------------
// JOINs (última matrícula y último acudiente por persona)
// ---------------------------------------------------------------------
$joins = "
    LEFT JOIN departamentos d ON ur.department = d.id_departamento
    LEFT JOIN municipios m ON ur.municipality = m.cod_municipio
    LEFT JOIN (
        SELECT e.* FROM enrollments e
        INNER JOIN (
            SELECT number_id, MAX(id) AS max_id FROM enrollments GROUP BY number_id
        ) em ON e.id = em.max_id
    ) e ON e.number_id = ur.number_id
    LEFT JOIN sets_cursos sc ON e.set_id = sc.id
    LEFT JOIN cursos c1 ON e.course_tecnico_id = c1.course_id
    LEFT JOIN cursos c2 ON e.course_ingles_id = c2.course_id
    LEFT JOIN cursos c3 ON e.course_habilidades_id = c3.course_id
    LEFT JOIN (
        SELECT a.* FROM acudientes a
        INNER JOIN (
            SELECT number_id, MAX(id) AS max_id FROM acudientes GROUP BY number_id
        ) am ON a.id = am.max_id
    ) acu ON acu.number_id = ur.number_id
";

// ---------------------------------------------------------------------
// Búsqueda global
// ---------------------------------------------------------------------
$where = '';
$searchParams = [];
$searchTypes = '';
if ($search !== '') {
    $like = '%' . $search . '%';
    $fields = [
        'ur.number_id',
        "CONCAT_WS(' ', ur.first_name, ur.second_name, ur.first_last, ur.second_last)",
        'ur.email',
        'ur.first_phone',
        'ur.second_phone',
        'ur.program',
        'ur.typeID',
    ];
    $where = ' WHERE (' . implode(' LIKE ? OR ', $fields) . ' LIKE ?)';
    $searchTypes = str_repeat('s', count($fields));
    for ($i = 0, $n = count($fields); $i < $n; $i++) {
        $searchParams[] = $like;
    }
}

// ---------------------------------------------------------------------
// Totales
// ---------------------------------------------------------------------
$stmtTotal = $conn->prepare("SELECT COUNT(*) AS total FROM user_register");
$stmtTotal->execute();
$recordsTotal = (int) $stmtTotal->get_result()->fetch_assoc()['total'];
$stmtTotal->close();

$recordsFiltered = $recordsTotal;
$sqlFiltered = "SELECT COUNT(*) AS total FROM user_register ur" . $where;
if ($searchTypes !== '') {
    $stmtFiltered = $conn->prepare($sqlFiltered);
    $refs = [];
    foreach ($searchParams as $k => $v) { $refs[$k] = &$searchParams[$k]; }
    $stmtFiltered->bind_param($searchTypes, ...$refs);
    $stmtFiltered->execute();
    $recordsFiltered = (int) $stmtFiltered->get_result()->fetch_assoc()['total'];
    $stmtFiltered->close();
} else {
    $recordsFiltered = $recordsTotal;
}

// ---------------------------------------------------------------------
// Consulta de datos (paginada)
// ---------------------------------------------------------------------
$sql = "SELECT " . implode(', ', $selectFields) . "
    FROM user_register ur
    " . $joins . "
    " . $where . "
    ORDER BY " . $orderExpr . " " . $orderDir . "
    LIMIT ? OFFSET ?";

$bindTypes = $searchTypes . 'ii';
$bindParams = $searchParams;
$bindParams[] = $length;
$bindParams[] = $start;

$stmt = $conn->prepare($sql);
$refs = [];
foreach ($bindParams as $k => $v) { $refs[$k] = &$bindParams[$k]; }
$stmt->bind_param($bindTypes, ...$refs);
$stmt->execute();
$result = $stmt->get_result();

function fmtDate($v)
{
    if (empty($v) || $v === '0000-00-00' || $v === '0000-00-00 00:00:00') {
        return null;
    }
    $t = strtotime($v);
    return $t ? date('d/m/Y', $t) : null;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'typeID'                  => $row['typeID'],
        'number_id'               => $row['number_id'],
        'full_name'               => trim(implode(' ', array_filter([
            $row['first_name'], $row['second_name'], $row['first_last'], $row['second_last']
        ], 'strlen'))),
        'gender'                  => $row['gender'],
        'age'                     => ($row['age'] !== null) ? (int) $row['age'] : null,
        'birthdate'               => fmtDate($row['birthdate']),
        'nationality'             => $row['nationality'],
        'first_phone'             => $row['first_phone'],
        'second_phone'            => $row['second_phone'],
        'email'                   => $row['email'],
        'email_verified'          => (int) $row['email_verified'],
        'emergency_contact_name'  => $row['emergency_contact_name'],
        'emergency_contact_number'=> $row['emergency_contact_number'],
        'departamento'            => $row['departamento_nombre'],
        'municipio'               => $row['municipio_nombre'],
        'address'                 => $row['address'],
        'residence_area'          => $row['residence_area'],
        'comuna_corregimiento'    => $row['comuna_corregimiento'],
        'barrio'                  => $row['barrio'],
        'program'                 => $row['program'],
        'mode'                    => $row['mode'],
        'creationDate'            => fmtDate($row['creationDate']),
        'mat_program'             => $row['mat_program_name'],
        'mat_status'              => $row['mat_status'],
        'mat_serie'               => $row['mat_serie'],
        'mat_codigo_tecnico'      => $row['mat_codigo_tecnico'],
        'tecnico_name'            => $row['tecnico_name'],
        'tecnico_code'            => $row['tecnico_code'],
        'ingles_name'             => $row['ingles_name'],
        'ingles_code'             => $row['ingles_code'],
        'habilidades_name'        => $row['habilidades_name'],
        'habilidades_code'        => $row['habilidades_code'],
        'mat_institutional_email' => $row['mat_institutional_email'],
        'mat_username'            => $row['mat_username'],
        'mat_created_at'          => fmtDate($row['mat_created_at']),
        'guardian_full_name'      => $row['guardian_full_name'],
        'guardian_document'       => $row['guardian_document'],
        'guardian_phone'          => $row['guardian_phone'],
        'guardian_email'          => $row['guardian_email'],
    ];
}
$stmt->close();
$conn->close();

echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $recordsTotal,
    'recordsFiltered' => $recordsFiltered,
    'data'            => $data,
]);
