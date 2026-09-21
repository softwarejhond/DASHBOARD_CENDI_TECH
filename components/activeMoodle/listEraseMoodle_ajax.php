<?php
// =====================================================================
// Endpoint server-side para el listado de desmatriculación múltiple
// (DataTables). Devuelve JSON con paginación, búsqueda y ordenamiento
// en lotes. Tabla principal: enrollments (última matrícula por
// number_id), con LEFT JOIN a cursos para el nombre del curso técnico.
// Filtros adicionales: set_id (set de curso) y number_id (cédula).
// Requiere sesión activa para dar seguimiento a las desmatriculaciones.
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

$orderCol = isset($_POST['order'][0]['column']) ? (int) $_POST['order'][0]['column'] : 2;
$orderDir = (isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'desc') ? 'DESC' : 'ASC';

// ---------------------------------------------------------------------
// Campos y ordenamiento (whitelist en el mismo orden que las columnas JS)
// ---------------------------------------------------------------------
$selectFields = [
    'e.type_id',
    'e.number_id',
    'e.full_name',
    'e.email',
    'e.institutional_email',
    'e.username',
    'e.program',
    'e.program_name',
    'e.status',
    'c1.course_name AS tecnico_name',
    'e.created_at',
];

// Índice 0 = columna de selección (checkbox, no ordenable): se reserva el
// lugar para que el índice coincida con la columna JS.
$orderable = [
    'e.number_id',
    'e.type_id',
    'e.number_id',
    'e.full_name',
    'e.email',
    'e.institutional_email',
    'e.username',
    'e.program_name',
    'e.status',
    'tecnico_name',
    'e.created_at',
];

$orderExpr = isset($orderable[$orderCol]) ? $orderable[$orderCol] : 'e.number_id';

// ---------------------------------------------------------------------
// FROM base: última matrícula por number_id + join a cursos
// ---------------------------------------------------------------------
$baseFrom = "(
        SELECT e.* FROM enrollments e
        INNER JOIN (
            SELECT number_id, MAX(id) AS max_id FROM enrollments GROUP BY number_id
        ) em ON e.id = em.max_id
    ) e
    LEFT JOIN cursos c1 ON e.course_tecnico_id = c1.course_id";

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
        'e.number_id',
        'e.full_name',
        'e.email',
        'e.institutional_email',
        'e.username',
        'e.program_name',
        'e.type_id',
    ];
    $conds[] = '(' . implode(' LIKE ? OR ', $fields) . ' LIKE ?)';
    for ($i = 0, $n = count($fields); $i < $n; $i++) {
        $params[] = $like;
        $types .= 's';
    }
}

if ($setId > 0) {
    $conds[] = 'e.set_id = ?';
    $params[] = $setId;
    $types .= 'i';
}

if ($numberId !== '') {
    $conds[] = 'e.number_id = ?';
    $params[] = $numberId;
    $types .= 's';
}

$where = !empty($conds) ? ' WHERE ' . implode(' AND ', $conds) : '';

// ---------------------------------------------------------------------
// Totales
// ---------------------------------------------------------------------
$stmtTotal = $conn->prepare("SELECT COUNT(DISTINCT number_id) AS total FROM enrollments");
$stmtTotal->execute();
$recordsTotal = (int) $stmtTotal->get_result()->fetch_assoc()['total'];
$stmtTotal->close();

$recordsFiltered = $recordsTotal;
if ($types !== '') {
    $sqlFiltered = "SELECT COUNT(*) AS total FROM " . $baseFrom . $where;
    $stmtFiltered = $conn->prepare($sqlFiltered);
    $refs = [];
    foreach ($params as $k => $v) { $refs[$k] = &$params[$k]; }
    $stmtFiltered->bind_param($types, ...$refs);
    $stmtFiltered->execute();
    $recordsFiltered = (int) $stmtFiltered->get_result()->fetch_assoc()['total'];
    $stmtFiltered->close();
}

// ---------------------------------------------------------------------
// Consulta de datos (paginada)
// ---------------------------------------------------------------------
$sql = "SELECT " . implode(', ', $selectFields) . "
    FROM " . $baseFrom . "
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

function fmtDateErase($v)
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
        'type_id'             => $row['type_id'],
        'number_id'           => $row['number_id'],
        'full_name'           => $row['full_name'],
        'email'               => $row['email'],
        'institutional_email' => $row['institutional_email'],
        'username'            => $row['username'],
        'program'             => $row['program'],
        'program_name'        => $row['program_name'],
        'status'              => $row['status'],
        'tecnico_name'        => $row['tecnico_name'],
        'created_at'          => fmtDateErase($row['created_at']),
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
