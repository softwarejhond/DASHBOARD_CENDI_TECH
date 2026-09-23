<?php
session_start();
require_once __DIR__ . '/../../controller/conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'no autorizado']);
    exit;
}

$number_id = isset($_GET['number_id']) ? trim($_GET['number_id']) : '';
if ($number_id === '') {
    echo json_encode(['ok' => false, 'error' => 'sin number_id']);
    exit;
}

$apiUrl = 'https://campus.cenditech.com.co/webservice/rest/server.php';
$token = 'c4bc5a8ef9d02d713c1e5283da17c29f';
$format = 'json';

function llamarMoodle($params)
{
    global $apiUrl, $token, $format;

    $postdata = http_build_query(['wstoken' => $token, 'moodlewsrestformat' => $format] + $params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return null;
    }

    return json_decode($response, true);
}

function obtenerUserIdMoodle($moodle_user_id, $username)
{
    if (!empty($moodle_user_id)) {
        return (int) $moodle_user_id;
    }

    if (empty($username)) {
        return null;
    }

    $data = llamarMoodle([
        'wsfunction' => 'core_user_get_users_by_field',
        'field' => 'username',
        'values[0]' => $username,
    ]);

    return empty($data[0]['id']) ? null : (int) $data[0]['id'];
}

function obtenerNotaCurso($userid, $courseid)
{
    if (empty($userid) || empty($courseid)) {
        return null;
    }

    $data = llamarMoodle([
        'wsfunction' => 'gradereport_user_get_grade_items',
        'courseid' => $courseid,
        'userid' => $userid,
    ]);

    if (!isset($data['usergrades'][0]['gradeitems'])) {
        return null;
    }

    $notaCurso = null;
    $primeraNota = null;

    foreach ($data['usergrades'][0]['gradeitems'] as $item) {
        if (!isset($item['graderaw']) || $item['graderaw'] === null) {
            continue;
        }
        if (isset($item['itemtype']) && $item['itemtype'] === 'course') {
            $notaCurso = $item['graderaw'];
            break;
        }
        if ($primeraNota === null) {
            $primeraNota = $item['graderaw'];
        }
    }

    $valor = ($notaCurso !== null) ? $notaCurso : $primeraNota;

    return ($valor === null) ? null : (float) $valor;
}

function normalizarNota($valor)
{
    if ($valor === null) {
        return null;
    }

    $valor = (float) $valor;
    if ($valor > 5.0) {
        $valor = ($valor / 10.0) * 5.0;
    }

    return round($valor, 2);
}

function obtenerPesosNotas($conn)
{
    $pesos = ['tecnico' => 50.0, 'ingles' => 25.0, 'habilidades' => 25.0, 'nota_minima' => 3.0];

    try {
        $result = $conn->query("SELECT peso_tecnico, peso_ingles, peso_habilidades, nota_minima_aprobacion FROM notas_pesos WHERE id = 1 LIMIT 1");
        if ($result && ($row = $result->fetch_assoc())) {
            $pesos['tecnico'] = (float) $row['peso_tecnico'];
            $pesos['ingles'] = (float) $row['peso_ingles'];
            $pesos['habilidades'] = (float) $row['peso_habilidades'];
            $pesos['nota_minima'] = (float) $row['nota_minima_aprobacion'];
        }
    } catch (Exception $e) {
    }

    return $pesos;
}

$stmt = $conn->prepare("
    SELECT e.number_id, e.username, e.moodle_user_id,
           e.course_tecnico_id, e.course_ingles_id, e.course_habilidades_id,
           c1.course_name AS tecnico_name, c1.course_code AS tecnico_code,
           c2.course_name AS ingles_name, c2.course_code AS ingles_code,
           c3.course_name AS habilidades_name, c3.course_code AS habilidades_code
    FROM enrollments e
    LEFT JOIN cursos c1 ON e.course_tecnico_id = c1.course_id
    LEFT JOIN cursos c2 ON e.course_ingles_id = c2.course_id
    LEFT JOIN cursos c3 ON e.course_habilidades_id = c3.course_id
    WHERE e.number_id = ?
    ORDER BY e.id DESC
    LIMIT 1
");
$stmt->bind_param('s', $number_id);
$stmt->execute();
$matricula = $stmt->get_result()->fetch_assoc();

if (!$matricula) {
    echo json_encode(['ok' => false, 'error' => 'sin_matricula']);
    exit;
}

$userid = obtenerUserIdMoodle($matricula['moodle_user_id'], $matricula['username']);
$pesos = obtenerPesosNotas($conn);

$definicion = [
    'tecnico' => [
        'id' => $matricula['course_tecnico_id'],
        'nombre' => $matricula['tecnico_name'],
        'code' => $matricula['tecnico_code'],
    ],
    'ingles' => [
        'id' => $matricula['course_ingles_id'],
        'nombre' => $matricula['ingles_name'],
        'code' => $matricula['ingles_code'],
    ],
    'habilidades' => [
        'id' => $matricula['course_habilidades_id'],
        'nombre' => $matricula['habilidades_name'],
        'code' => $matricula['habilidades_code'],
    ],
];

$cursos = [];
$suma = 0.0;

foreach ($definicion as $key => $curso) {
    $nota = normalizarNota(obtenerNotaCurso($userid, $curso['id']));

    $cursos[$key] = [
        'id' => $curso['id'],
        'nombre' => $curso['nombre'],
        'code' => $curso['code'],
        'nota' => $nota,
        'presento' => ($nota !== null),
    ];

    $suma += (($nota === null) ? 0.0 : $nota) * ($pesos[$key] / 100);
}

$promedio = round($suma, 2);
$todosPresentes = $cursos['tecnico']['presento'] && $cursos['ingles']['presento'] && $cursos['habilidades']['presento'];
$aprobado = $todosPresentes && ($promedio >= $pesos['nota_minima']);
$estado = $todosPresentes ? ($aprobado ? 'aprobado' : 'no_aprobado') : 'sin_completar';

echo json_encode([
    'ok' => true,
    'cursos' => $cursos,
    'promedio' => $promedio,
    'nota_minima' => $pesos['nota_minima'],
    'aprobado' => $aprobado,
    'estado' => $estado,
    'pesos' => $pesos,
]);
