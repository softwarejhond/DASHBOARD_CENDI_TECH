<?php
// =====================================================================
// Endpoint server-side para el listado de cursos (DataTables).
// Lista los cursos de la tabla `cursos` con el set y serie a los que
// pertenecen, el contador de personas matriculadas y las fechas de
// creación del curso (cursos.created_at) y del set (sets_cursos.created_at).
// Filtros: set, tipo de curso y serie.
// =====================================================================

session_start();
require_once __DIR__ . '/../../controller/conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'no autorizado']);
    exit;
}

// Tipos técnicos (codigo_tecnico => nombre)
$tiposTecnicos = [
    'IA'    => 'Inteligencia Artificial',
    'PDS'   => 'Programación y Desarrollo',
    'DT'    => 'Análisis de Datos',
    'CIBER' => 'Ciberseguridad',
    'CBS'   => 'Ciberseguridad',
    'CN'    => 'Computación en la Nube',
    'BLO'   => 'Blockchain',
    'RA'    => 'Robótica y Automatización',
    'IOT'   => 'Internet de las Cosas',
];

// Expresión SQL para el nombre del tipo técnico
$techCase = 'CASE sc.codigo_tecnico';
foreach ($tiposTecnicos as $code => $name) {
    $techCase .= " WHEN '" . $code . "' THEN '" . $name . "'";
}
$techCase .= " ELSE sc.codigo_tecnico END";

// Expresión SQL para el tipo de curso (técnico / inglés / habilidades)
$tipoSql = "CASE WHEN c.course_code LIKE 'ING-%' THEN 'Inglés' "
    . "WHEN c.course_code LIKE 'BH-%' THEN 'Habilidades blandas' "
    . "ELSE " . $techCase . " END";

// ---------------------------------------------------------------------
// Parámetros del protocolo server-side de DataTables
// ---------------------------------------------------------------------
$draw   = isset($_POST['draw']) ? (int) $_POST['draw'] : 1;
$start  = isset($_POST['start']) ? (int) $_POST['start'] : 0;
$length = isset($_POST['length']) ? (int) $_POST['length'] : 10;
if ($length < 0) {
    $length = 50;
}

$search   = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
$orderCol = isset($_POST['order'][0]['column']) ? (int) $_POST['order'][0]['column'] : 0;
$orderDir = (isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'desc') ? 'DESC' : 'ASC';

// Filtros personalizados
$filterSet   = isset($_POST['set']) ? trim($_POST['set']) : '';
$filterTipo  = isset($_POST['tipo']) ? trim($_POST['tipo']) : '';
$filterSerie = isset($_POST['serie']) ? trim($_POST['serie']) : '';

// ---------------------------------------------------------------------
// Construcción del WHERE
// ---------------------------------------------------------------------
$where   = '';
$params  = [];
$types   = '';

if ($search !== '') {
    $like = '%' . $search . '%';
    $where .= " AND (c.course_code LIKE ? OR c.course_name LIKE ? OR sc.codigo_tecnico LIKE ? OR CAST(sc.serie AS CHAR) LIKE ?)";
    $types .= 'ssss';
    for ($i = 0; $i < 4; $i++) {
        $params[] = $like;
    }
}

// Filtro por set (codigo_tecnico|serie, ej: BLO|1)
if ($filterSet !== '') {
    $setParts = explode('|', $filterSet);
    if (count($setParts) === 2) {
        $where .= ' AND sc.codigo_tecnico = ? AND sc.serie = ?';
        $types .= 'ss';
        $params[] = $setParts[0];
        $params[] = $setParts[1];
    }
}

// Filtro por tipo de curso
if ($filterTipo === 'ingles') {
    $where .= " AND c.course_code LIKE 'ING-%'";
} elseif ($filterTipo === 'habilidades') {
    $where .= " AND c.course_code LIKE 'BH-%'";
} elseif ($filterTipo !== '') {
    $where .= " AND sc.codigo_tecnico = ? AND c.course_code NOT LIKE 'ING-%' AND c.course_code NOT LIKE 'BH-%'";
    $types .= 's';
    $params[] = $filterTipo;
}

// Filtro por serie
if ($filterSerie !== '') {
    $where .= ' AND sc.serie = ?';
    $types .= 's';
    $params[] = $filterSerie;
}

// ---------------------------------------------------------------------
// Ordenamiento (whitelist en el mismo orden que las columnas JS)
// ---------------------------------------------------------------------
$orderable = [
    'c.course_id',
    'c.course_code',
    'c.course_name',
    'tipo',
    'sc.codigo_tecnico',
    'CAST(sc.serie AS UNSIGNED)',
    'matriculados',
    'c.created_at',
    'sc.created_at',
];
$orderExpr = isset($orderable[$orderCol]) ? $orderable[$orderCol] : 'c.course_id';

// ---------------------------------------------------------------------
// Totales
// ---------------------------------------------------------------------
$countBase = "FROM cursos c
    LEFT JOIN sets_cursos sc
        ON c.course_id = sc.curso_tecnico_id
        OR c.course_id = sc.curso_ingles_id
        OR c.course_id = sc.curso_habilidades_id
    WHERE 1=1";

$stmtTotal = $conn->prepare("SELECT COUNT(*) AS total " . $countBase);
$stmtTotal->execute();
$recordsTotal = (int) $stmtTotal->get_result()->fetch_assoc()['total'];
$stmtTotal->close();

$sqlFiltered = "SELECT COUNT(*) AS total " . $countBase . $where;
if ($types !== '') {
    $stmtF = $conn->prepare($sqlFiltered);
    $refs = [];
    foreach ($params as $k => $v) { $refs[$k] = &$params[$k]; }
    $stmtF->bind_param($types, ...$refs);
    $stmtF->execute();
    $recordsFiltered = (int) $stmtF->get_result()->fetch_assoc()['total'];
    $stmtF->close();
} else {
    $recordsFiltered = $recordsTotal;
}

// ---------------------------------------------------------------------
// Consulta de datos (paginada)
// ---------------------------------------------------------------------
$sql = "SELECT
        c.course_id,
        c.course_code,
        c.course_name,
        " . $tipoSql . " AS tipo,
        sc.codigo_tecnico,
        sc.serie,
        (
            SELECT COUNT(DISTINCT e.number_id)
            FROM enrollments e
            WHERE e.course_tecnico_id = c.course_id
               OR e.course_ingles_id = c.course_id
               OR e.course_habilidades_id = c.course_id
        ) AS matriculados,
        c.created_at AS curso_created_at,
        sc.created_at AS set_created_at
    " . $countBase . "
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
        'course_id'         => $row['course_id'],
        'course_code'       => $row['course_code'],
        'course_name'       => $row['course_name'],
        'tipo'              => $row['tipo'],
        'codigo_tecnico'    => $row['codigo_tecnico'],
        'serie'             => $row['serie'],
        'matriculados'      => (int) $row['matriculados'],
        'curso_created_at'  => fmtDate($row['curso_created_at']),
        'set_created_at'    => fmtDate($row['set_created_at']),
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
