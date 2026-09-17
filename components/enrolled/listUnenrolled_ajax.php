<?php
// =====================================================================
// Endpoint server-side para el historial de desmatriculación (DataTables).
// Devuelve JSON con paginación, búsqueda y ordenamiento en lotes.
// Tabla: enrollment_history.
// Filtros adicionales: set_id (via id_bootcamp) y number_id (cédula).
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
    $length = 50;
}

$search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';

$orderCol = isset($_POST['order'][0]['column']) ? (int) $_POST['order'][0]['column'] : 10;
$orderDir = (isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'desc') ? 'DESC' : 'ASC';

// ---------------------------------------------------------------------
// Campos y ordenamiento (whitelist en el mismo orden que las columnas JS)
// ---------------------------------------------------------------------
$selectFields = [
    'number_id',
    'full_name',
    'email',
    'institutional_email',
    'department',
    'headquarters',
    'program',
    'mode',
    'bootcamp_name',
    'enrollment_date',
    'unenrollment_date',
    'unenrolled_by',
];

$orderable = [
    'number_id',
    'full_name',
    'email',
    'institutional_email',
    'department',
    'headquarters',
    'program',
    'mode',
    'bootcamp_name',
    'enrollment_date',
    'unenrollment_date',
    'unenrolled_by',
];

$orderExpr = isset($orderable[$orderCol]) ? $orderable[$orderCol] : 'unenrollment_date';

// ---------------------------------------------------------------------
// Carga bajo demanda: sin filtro ni búsqueda no se consulta la tabla
// ---------------------------------------------------------------------
$setId = isset($_POST['set_id']) ? (int) $_POST['set_id'] : 0;
$numberId = isset($_POST['number_id']) ? trim($_POST['number_id']) : '';

if ($setId <= 0 && $numberId === '' && $search === '') {
    echo json_encode([
        'draw'            => $draw,
        'recordsTotal'    => 0,
        'recordsFiltered' => 0,
        'data'            => [],
    ]);
    exit;
}

// ---------------------------------------------------------------------
// Condiciones WHERE (búsqueda global + filtros adicionales)
// ---------------------------------------------------------------------
$conds = [];
$params = [];
$types = '';

if ($search !== '') {
    $like = '%' . $search . '%';
    $fields = [
        'number_id',
        'full_name',
        'email',
        'institutional_email',
        'department',
        'headquarters',
        'program',
        'bootcamp_name',
        'unenrolled_by',
    ];
    $conds[] = '(' . implode(' LIKE ? OR ', $fields) . ' LIKE ?)';
    for ($i = 0, $n = count($fields); $i < $n; $i++) {
        $params[] = $like;
        $types .= 's';
    }
}

// En el flujo nuevo, el set de cursos se guarda en id_bootcamp
if ($setId > 0) {
    $conds[] = 'id_bootcamp = ?';
    $params[] = $setId;
    $types .= 'i';
}

if ($numberId !== '') {
    $conds[] = 'number_id = ?';
    $params[] = $numberId;
    $types .= 's';
}

$where = !empty($conds) ? ' WHERE ' . implode(' AND ', $conds) : '';

// ---------------------------------------------------------------------
// Totales
// ---------------------------------------------------------------------
$stmtTotal = $conn->prepare("SELECT COUNT(*) AS total FROM enrollment_history");
$stmtTotal->execute();
$recordsTotal = (int) $stmtTotal->get_result()->fetch_assoc()['total'];
$stmtTotal->close();

$recordsFiltered = $recordsTotal;
$sqlFiltered = "SELECT COUNT(*) AS total FROM enrollment_history" . $where;
if ($types !== '') {
    $stmtFiltered = $conn->prepare($sqlFiltered);
    $refs = [];
    foreach ($params as $k => $v) { $refs[$k] = &$params[$k]; }
    $stmtFiltered->bind_param($types, ...$refs);
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
    FROM enrollment_history
    " . $where . "
    ORDER BY " . $orderExpr . " " . $orderDir . "
    LIMIT ? OFFSET ?";

$bindTypes = $types . 'ii';
$bindParams = $params;
$bindParams[] = $length;
$bindParams[] = $start;

$stmt = $conn->prepare($sql);
$refs = [];
foreach ($bindParams as $k => $v) { $refs[$k] = &$bindParams[$k]; }
$stmt->bind_param($bindTypes, ...$refs);
$stmt->execute();
$result = $stmt->get_result();

function fmtDateTime($v)
{
    if (empty($v) || $v === '0000-00-00' || $v === '0000-00-00 00:00:00') {
        return null;
    }
    $t = strtotime($v);
    return $t ? date('d/m/Y H:i', $t) : null;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'number_id'             => $row['number_id'],
        'full_name'             => $row['full_name'],
        'email'                 => $row['email'],
        'institutional_email'   => $row['institutional_email'],
        'department'            => $row['department'],
        'headquarters'          => $row['headquarters'],
        'program'               => $row['program'],
        'mode'                  => $row['mode'],
        'bootcamp_name'         => $row['bootcamp_name'],
        'enrollment_date'       => fmtDateTime($row['enrollment_date']),
        'unenrollment_date'     => fmtDateTime($row['unenrollment_date']),
        'unenrolled_by'         => $row['unenrolled_by'],
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
