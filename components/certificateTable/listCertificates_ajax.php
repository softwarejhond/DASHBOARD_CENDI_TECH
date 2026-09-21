<?php
// =====================================================================
// Endpoint server-side para el listado de estudiantes con diploma
// (DataTables). Devuelve JSON con paginación, búsqueda y ordenamiento.
// Tabla principal: diplomas_emitidos (una fila por programa emitido).
// Se une con la última matrícula (enrollments) para resolver el set de
// cursos del estudiante y con groups/user_register para el nombre.
// Filtros adicionales: set_id (set de programa) y number_id (cédula).
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

$orderCol = isset($_POST['order'][0]['column']) ? (int) $_POST['order'][0]['column'] : 0;
$orderDir = (isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'desc') ? 'DESC' : 'ASC';

// ---------------------------------------------------------------------
// Expresiones calculadas
// ---------------------------------------------------------------------
$nameExpr = "COALESCE(
    NULLIF(g.full_name, ''),
    NULLIF(CONCAT_WS(' ', ur.first_name, ur.second_name, ur.first_last, ur.second_last), ''),
    ''
)";

$setExpr = "COALESCE(CONCAT(sc.codigo_tecnico, '-', sc.serie, ' - ', c1.course_name), '')";

// ---------------------------------------------------------------------
// Campos y ordenamiento (whitelist en el mismo orden que las columnas JS)
// ---------------------------------------------------------------------
$selectFields = [
    'd.number_id',
    $nameExpr . ' AS full_name',
    'd.email_destino',
    $setExpr . ' AS set_nombre',
    'd.estado',
    'd.fecha_envio',
    'd.token',
];

$orderable = [
    'd.number_id',
    $nameExpr,
    'd.email_destino',
    $setExpr,
    'd.estado',
    'd.fecha_envio',
    'd.token',
];

$orderExpr = isset($orderable[$orderCol]) ? $orderable[$orderCol] : 'd.id';

// ---------------------------------------------------------------------
// FROM base: diplomas emitidos + última matrícula (set) + último grupo
// ---------------------------------------------------------------------
$baseFrom = "diplomas_emitidos d
    LEFT JOIN (
        SELECT e.* FROM enrollments e
        INNER JOIN (
            SELECT number_id, MAX(id) AS max_id FROM enrollments GROUP BY number_id
        ) em ON e.id = em.max_id
    ) en ON en.number_id = d.number_id
    LEFT JOIN sets_cursos sc ON en.set_id = sc.id
    LEFT JOIN cursos c1 ON en.course_tecnico_id = c1.course_id
    LEFT JOIN (
        SELECT number_id, MAX(id) AS max_id FROM groups GROUP BY number_id
    ) gm ON gm.number_id = d.number_id
    LEFT JOIN groups g ON g.id = gm.max_id
    LEFT JOIN user_register ur ON ur.number_id = d.number_id";

// ---------------------------------------------------------------------
// Carga bajo demanda: sin filtro ni búsqueda no se consulta la tabla
// ---------------------------------------------------------------------
$setId    = isset($_POST['set_id']) ? (int) $_POST['set_id'] : 0;
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
        'd.number_id',
        $nameExpr,
        'd.email_destino',
        $setExpr,
        'd.token',
    ];
    $conds[] = '(' . implode(' LIKE ? OR ', $fields) . ' LIKE ?)';
    for ($i = 0, $n = count($fields); $i < $n; $i++) {
        $params[] = $like;
        $types .= 's';
    }
}

if ($setId > 0) {
    $conds[] = 'en.set_id = ?';
    $params[] = $setId;
    $types .= 'i';
}

if ($numberId !== '') {
    $conds[] = 'd.number_id = ?';
    $params[] = $numberId;
    $types .= 's';
}

$where = !empty($conds) ? ' WHERE ' . implode(' AND ', $conds) : '';

// ---------------------------------------------------------------------
// Totales
// ---------------------------------------------------------------------
$resTotal = $conn->query("SELECT COUNT(*) AS total FROM diplomas_emitidos");
$recordsTotal = $resTotal ? (int) $resTotal->fetch_assoc()['total'] : 0;

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

function fmtDateDiploma($v)
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
        'number_id'   => $row['number_id'],
        'full_name'   => $row['full_name'],
        'email'       => $row['email_destino'],
        'set_nombre'  => $row['set_nombre'],
        'estado'      => $row['estado'],
        'fecha_envio' => fmtDateDiploma($row['fecha_envio']),
        'token'       => $row['token'],
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
