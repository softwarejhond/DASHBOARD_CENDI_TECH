<?php
/**
 * Script de prueba para listar cursos desde la API de Moodle
 * Usa las mismas credenciales que components/modals/get_courses.php
 *
 * Ejecutar: php test_api_courses.php
 */

$api_url = "https://campus.cenditech.com.co/webservice/rest/server.php";
$token   = "c4bc5a8ef9d02d713c1e5283da17c29f";
$format  = "json";

function callMoodleAPI($function, $params = []) {
    global $api_url, $token, $format;
    $params['wstoken'] = $token;
    $params['wsfunction'] = $function;
    $params['moodlewsrestformat'] = $format;

    $url = $api_url . '?' . http_build_query($params);

    echo "[DEBUG] Llamando: $function\n";
    echo "[DEBUG] URL: $url\n\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        echo "[ERROR] cURL Error: $error\n";
        return null;
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "[DEBUG] HTTP Code: $http_code\n";

    $decoded = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "[ERROR] JSON decode error: " . json_last_error_msg() . "\n";
        echo "[RAW RESPONSE]: $response\n";
        return null;
    }

    return $decoded;
}

echo "========================================\n";
echo "  TEST DE API CENDITECH - CURSOS\n";
echo "========================================\n\n";

echo "1. Probando core_course_get_courses...\n";
$courses = callMoodleAPI('core_course_get_courses');

if ($courses === null) {
    echo "\n[FALLÓ] No se pudo conectar a la API.\n";
    exit(1);
}

if (isset($courses['error']) || isset($courses['exception'])) {
    echo "\n[FALLÓ] La API devolvió un error:\n";
    echo json_encode($courses, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    exit(1);
}

$count = is_array($courses) ? count($courses) : 0;
echo "\n[ÉXITO] Se encontraron $count curso(s).\n\n";

echo "========================================\n";
echo "  LISTA DE CURSOS\n";
echo "========================================\n\n";

foreach ($courses as $i => $course) {
    $num = $i + 1;
    $id       = $course['id']       ?? 'N/A';
    $short    = $course['shortname'] ?? 'N/A';
    $full     = $course['fullname']  ?? 'N/A';
    $category = $course['categoryid'] ?? 'N/A';

    echo "--- Curso #$num ---\n";
    echo "  ID:           $id\n";
    echo "  Shortname:    $short\n";
    echo "  Fullname:     $full\n";
    echo "  Category ID:  $category\n";
    echo "\n";
}

echo "========================================\n";
echo "  RESUMEN\n";
echo "========================================\n";
echo "Total de cursos: $count\n";
echo "Credenciales: FUNCIONANDO\n";
