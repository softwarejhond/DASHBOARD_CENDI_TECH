<?php
// Script para ser ejecutado por una tarea programada (cron job)

set_time_limit(0);
ignore_user_abort(true);

require_once __DIR__ . '/conexion.php';

if (!isset($conn) || $conn->connect_error) {
    $error_message = isset($conn) ? $conn->connect_error : "La variable de conexión no está definida en conexion.php";
    file_put_contents(__DIR__ . '/cron_log.txt', date('Y-m-d H:i:s') . " - Error de conexión: " . $error_message . "\n", FILE_APPEND);
    die("Error de conexión: " . $error_message);
}

$lockResult = $conn->query("SELECT GET_LOCK('cron_notas', 0) AS l");
$lockRow = $lockResult ? $lockResult->fetch_assoc() : null;
if (!$lockRow || (int) $lockRow['l'] !== 1) {
    file_put_contents(__DIR__ . '/cron_log.txt', date('Y-m-d H:i:s') . " - Otra ejecución de notas está en curso; se omite esta corrida.\n", FILE_APPEND);
    $conn->close();
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

    if (empty($data[0]['id'])) {
        return null;
    }

    return (int) $data[0]['id'];
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
        return 0.0;
    }

    $valor = (float) $valor;
    if ($valor > 5.0) {
        $valor = ($valor / 10.0) * 5.0;
    }

    return round($valor, 2);
}

function obtenerPesosNotas($conn)
{
    $pesos = ['tecnico' => 50.0, 'ingles' => 25.0, 'habilidades' => 25.0];

    try {
        $result = $conn->query("SELECT peso_tecnico, peso_ingles, peso_habilidades FROM notas_pesos WHERE id = 1 LIMIT 1");
        if ($result && ($row = $result->fetch_assoc())) {
            $pesos['tecnico'] = (float) $row['peso_tecnico'];
            $pesos['ingles'] = (float) $row['peso_ingles'];
            $pesos['habilidades'] = (float) $row['peso_habilidades'];
        }
    } catch (Exception $e) {
        file_put_contents(__DIR__ . '/cron_log.txt', date('Y-m-d H:i:s') . " - No se pudo leer notas_pesos, se usan 50/25/25: " . $e->getMessage() . "\n", FILE_APPEND);
    }

    return $pesos;
}

function calcularNotaFinal($notaTecnico, $notaIngles, $notaHabilidades, $pesos)
{
    $final = ((float) $notaTecnico * $pesos['tecnico'] / 100)
        + ((float) $notaIngles * $pesos['ingles'] / 100)
        + ((float) $notaHabilidades * $pesos['habilidades'] / 100);

    return round($final, 2);
}

function guardarNotas($number_id, $id_tecnico, $nota_tecnico, $presento_tecnico, $id_ingles, $nota_ingles, $presento_ingles, $id_habilidades, $nota_habilidades, $presento_habilidades, $nota_final)
{
    global $conn;

    try {
        $sql = "INSERT INTO notas_estudiantes
                    (number_id, id_tecnico, nota_tecnico, presento_tecnico, id_ingles, nota_ingles, presento_ingles, id_habilidades, nota_habilidades, presento_habilidades, nota_final)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    id_tecnico = VALUES(id_tecnico),
                    nota_tecnico = VALUES(nota_tecnico),
                    presento_tecnico = VALUES(presento_tecnico),
                    id_ingles = VALUES(id_ingles),
                    nota_ingles = VALUES(nota_ingles),
                    presento_ingles = VALUES(presento_ingles),
                    id_habilidades = VALUES(id_habilidades),
                    nota_habilidades = VALUES(nota_habilidades),
                    presento_habilidades = VALUES(presento_habilidades),
                    nota_final = VALUES(nota_final)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sidiidiidid",
            $number_id,
            $id_tecnico,
            $nota_tecnico,
            $presento_tecnico,
            $id_ingles,
            $nota_ingles,
            $presento_ingles,
            $id_habilidades,
            $nota_habilidades,
            $presento_habilidades,
            $nota_final
        );

        return $stmt->execute();
    } catch (Exception $e) {
        file_put_contents(__DIR__ . '/cron_log.txt', date('Y-m-d H:i:s') . " - Error al guardar notas para $number_id: " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}

// --- LÓGICA PRINCIPAL DEL SCRIPT DE CRON ---

$logMessage = date('Y-m-d H:i:s') . " - Inicia el proceso de obtención de notas.\n";
file_put_contents(__DIR__ . '/cron_log.txt', $logMessage, FILE_APPEND);

$sql = "SELECT e.number_id, e.username, e.moodle_user_id,
               e.course_tecnico_id, e.course_ingles_id, e.course_habilidades_id
        FROM enrollments e
        INNER JOIN (
            SELECT number_id, MAX(id) AS max_id
            FROM enrollments
            WHERE status = 'enrolled'
            GROUP BY number_id
        ) em ON e.id = em.max_id";
$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    $logMessage = date('Y-m-d H:i:s') . " - No se encontraron estudiantes matriculados para procesar.\n";
    file_put_contents(__DIR__ . '/cron_log.txt', $logMessage, FILE_APPEND);
    exit;
}

$estudiantes = $result->fetch_all(MYSQLI_ASSOC);
$total = count($estudiantes);
$procesados = 0;

$pesos = obtenerPesosNotas($conn);

$logMessage = date('Y-m-d H:i:s') . " - Se encontraron $total estudiantes para procesar. Pesos: Tecnico {$pesos['tecnico']}%, Ingles {$pesos['ingles']}%, Habilidades {$pesos['habilidades']}%.\n";
file_put_contents(__DIR__ . '/cron_log.txt', $logMessage, FILE_APPEND);

foreach ($estudiantes as $estudiante) {
    $number_id = $estudiante['number_id'];

    $userid = obtenerUserIdMoodle($estudiante['moodle_user_id'], $estudiante['username']);

    $raw_tecnico = obtenerNotaCurso($userid, $estudiante['course_tecnico_id']);
    $raw_ingles = obtenerNotaCurso($userid, $estudiante['course_ingles_id']);
    $raw_habilidades = obtenerNotaCurso($userid, $estudiante['course_habilidades_id']);

    $presento_tecnico = ($raw_tecnico !== null) ? 1 : 0;
    $presento_ingles = ($raw_ingles !== null) ? 1 : 0;
    $presento_habilidades = ($raw_habilidades !== null) ? 1 : 0;

    $nota_tecnico = normalizarNota($raw_tecnico);
    $nota_ingles = normalizarNota($raw_ingles);
    $nota_habilidades = normalizarNota($raw_habilidades);

    $nota_final = calcularNotaFinal($nota_tecnico, $nota_ingles, $nota_habilidades, $pesos);

    $ok = guardarNotas(
        $number_id,
        $estudiante['course_tecnico_id'],
        $nota_tecnico,
        $presento_tecnico,
        $estudiante['course_ingles_id'],
        $nota_ingles,
        $presento_ingles,
        $estudiante['course_habilidades_id'],
        $nota_habilidades,
        $presento_habilidades,
        $nota_final
    );

    if ($ok) {
        $logMessage = "  - OK: Estudiante $number_id -> Tecnico: " . var_export($nota_tecnico, true)
            . ", Ingles: " . var_export($nota_ingles, true)
            . ", Habilidades: " . var_export($nota_habilidades, true)
            . ", Final: " . var_export($nota_final, true) . "\n";
    } else {
        $logMessage = "  - ERROR: Estudiante $number_id -> No se pudieron guardar las notas.\n";
    }
    file_put_contents(__DIR__ . '/cron_log.txt', $logMessage, FILE_APPEND);
    $procesados++;
}

$logMessage = date('Y-m-d H:i:s') . " - Proceso finalizado. Se procesaron $procesados de $total estudiantes.\n\n";
file_put_contents(__DIR__ . '/cron_log.txt', $logMessage, FILE_APPEND);

if (PHP_OS_FAMILY !== 'Windows') {
    $diplomaScript = __DIR__ . '/cron_generar_diplomas.php';
    if (is_file($diplomaScript)) {
        @exec('nohup php ' . escapeshellarg($diplomaScript) . ' > /dev/null 2>&1 &');
    }
}

$conn->query("SELECT RELEASE_LOCK('cron_notas')");
$conn->close();

echo "Proceso completado.";
