<?php
// =====================================================================
// Endpoint AJAX para el selector de "set de curso" (select2).
// Devuelve los sets de cursos bajo demanda, con búsqueda y paginación.
// Tablas: sets_cursos + cursos.
// =====================================================================

session_start();
require_once __DIR__ . '/../../controller/conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['results' => [], 'more' => false]);
    exit;
}

$q    = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit  = 30;
$offset = ($page - 1) * $limit;

$baseFrom = "FROM sets_cursos sc LEFT JOIN cursos c ON sc.curso_tecnico_id = c.course_id";

$where  = '';
$params = [];
$types  = '';
if ($q !== '') {
    $like   = '%' . $q . '%';
    $where  = " WHERE sc.codigo_tecnico LIKE ? OR sc.serie LIKE ? OR c.course_name LIKE ?";
    $params = [$like, $like, $like];
    $types  = 'sss';
}

// ---------------------------------------------------------------------
// Total de resultados
// ---------------------------------------------------------------------
$total = 0;
$countSql = "SELECT COUNT(*) AS total " . $baseFrom . $where;
if ($types !== '') {
    $stmtCount = $conn->prepare($countSql);
    $refs = [];
    foreach ($params as $k => $v) { $refs[$k] = &$params[$k]; }
    $stmtCount->bind_param($types, ...$refs);
    $stmtCount->execute();
    $total = (int) $stmtCount->get_result()->fetch_assoc()['total'];
    $stmtCount->close();
} else {
    $res = $conn->query($countSql);
    $total = (int) $res->fetch_assoc()['total'];
}

// ---------------------------------------------------------------------
// Datos de la página actual
// ---------------------------------------------------------------------
$sql = "SELECT sc.id, sc.codigo_tecnico, sc.serie, c.course_name "
     . $baseFrom . $where
     . " ORDER BY sc.codigo_tecnico ASC, CAST(sc.serie AS UNSIGNED) ASC
         LIMIT ? OFFSET ?";

$bindTypes  = $types . 'ii';
$bindParams = $params;
$bindParams[] = $limit;
$bindParams[] = $offset;

$stmt = $conn->prepare($sql);
$refs = [];
foreach ($bindParams as $k => $v) { $refs[$k] = &$bindParams[$k]; }
$stmt->bind_param($bindTypes, ...$refs);
$stmt->execute();
$result = $stmt->get_result();

$results = [];
while ($row = $result->fetch_assoc()) {
    $results[] = [
        'id'   => (int) $row['id'],
        'text' => $row['codigo_tecnico'] . '-' . $row['serie'] . ' - ' . $row['course_name'],
    ];
}
$stmt->close();
$conn->close();

echo json_encode([
    'results' => $results,
    'more'    => ($offset + count($results)) < $total,
]);
