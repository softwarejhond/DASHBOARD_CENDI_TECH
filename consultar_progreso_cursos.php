<?php
// Script de prueba: consulta el progreso de avance por curso en Moodle

require_once 'conexion.php';

$logFile = 'progreso_cursos_log.txt';

if (!isset($conn) || $conn->connect_error) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Error de conexión a la base de datos.\n", FILE_APPEND);
    die("Error de conexión a la base de datos.");
}

$apiUrl = 'https://campus.cenditech.com.co/webservice/rest/server.php';
$token = 'c4bc5a8ef9d02d713c1e5283da17c29f';
$format = 'json';

$number_id = '12345';

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

$stmt = $conn->prepare("SELECT moodle_user_id, username FROM enrollments WHERE number_id = ? AND status = 'enrolled' ORDER BY id DESC LIMIT 1");
$stmt->bind_param("s", $number_id);
$stmt->execute();
$enrollment = $stmt->get_result()->fetch_assoc();

if (!$enrollment) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - No se encontró matrícula para number_id $number_id.\n", FILE_APPEND);
    die("No se encontró matrícula para number_id $number_id.");
}

$userid = obtenerUserIdMoodle($enrollment['moodle_user_id'], $enrollment['username']);

if (empty($userid)) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - No se pudo resolver el usuario de Moodle para number_id $number_id.\n", FILE_APPEND);
    die("No se pudo resolver el usuario de Moodle.");
}

$cursos = llamarMoodle([
    'wsfunction' => 'core_enrol_get_users_courses',
    'userid' => $userid,
]);

if (!is_array($cursos)) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Error al consultar los cursos en Moodle para number_id $number_id.\n", FILE_APPEND);
    die("Error al consultar los cursos en Moodle.");
}

$lineas = [];
$lineas[] = date('Y-m-d H:i:s') . " - Progreso de cursos para number_id $number_id (moodle_user_id $userid):";

foreach ($cursos as $curso) {
    $progreso = ($curso['progress'] === null) ? 'N/A' : round((float) $curso['progress'], 2) . '%';
    $completado = !empty($curso['completed']) ? 'Sí' : 'No';
    $lineas[] = "  - [" . $curso['id'] . "] " . $curso['fullname'] . " (" . $curso['shortname'] . ") -> Progreso: $progreso, Completado: $completado";
}

$lineas[] = "";

file_put_contents($logFile, implode("\n", $lineas) . "\n", FILE_APPEND);

echo "Listo. Revisa $logFile";
