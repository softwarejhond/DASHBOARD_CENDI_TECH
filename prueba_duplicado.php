<?php
/**
 * Página de PRUEBA con interfaz HTML para duplicar el curso id = 29.
 * Al cargar, muestra un botón. Al hacer clic, ejecuta la duplicación
 * y muestra el resultado en la misma página.
 */

$api_url = "https://campus.cenditech.com.co/webservice/rest/server.php";
$token   = "c4bc5a8ef9d02d713c1e5283da17c29f";
$format  = "json";

function callMoodleAPIB($function, $params = []) {
    global $api_url, $token, $format;
    $params['wstoken'] = $token;
    $params['wsfunction'] = $function;
    $params['moodlewsrestformat'] = $format;
    $url = $api_url . '?' . http_build_query($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return ['error' => 'Error al conectar con Moodle: ' . curl_error($ch)];
    }
    curl_close($ch);
    return json_decode($response, true);
}

function getCourseByIdB($courseId) {
    $result = callMoodleAPIB('core_course_get_courses', [
        'options' => ['ids' => [$courseId]],
    ]);
    return $result[0] ?? null;
}

function duplicateCourseB($courseIdOrigen, $newFullname, $newShortname) {
    $original = getCourseByIdB($courseIdOrigen);
    if (!$original) {
        return ['error' => 'No se encontró el curso origen ' . $courseIdOrigen];
    }

    return callMoodleAPIB('core_course_duplicate_course', [
        'courseid'   => $courseIdOrigen,
        'fullname'   => $newFullname,
        'shortname'  => $newShortname,
        'categoryid' => $original['categoryid'],
        'visible'    => 1,
        'options'    => [
            ['name' => 'activities',       'value' => 1],
            ['name' => 'blocks',           'value' => 1],
            ['name' => 'filters',          'value' => 1],
            ['name' => 'users',            'value' => 0],
            ['name' => 'role_assignments', 'value' => 0],
            ['name' => 'comments',         'value' => 0],
            ['name' => 'userscompletion',  'value' => 0],
            ['name' => 'logs',             'value' => 0],
            ['name' => 'grade_histories',  'value' => 0],
        ],
    ]);
}

// ==========================================================
// Procesamiento: solo se ejecuta si el formulario fue enviado
// ==========================================================
$resultadoHtml = '';
$courseIdOrigen = 29;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['duplicar'])) {

    $original = getCourseByIdB($courseIdOrigen);

    if (!$original) {
        $resultadoHtml = "<div class='box error'>
            <strong>ERROR:</strong> no se encontró el curso con id={$courseIdOrigen}.
            Revisa el ID o los permisos del token.
        </div>";
    } else {
        $sufijo = date('YmdHis');
        $nuevoFullname  = $original['fullname'] . " (Copia {$sufijo})";
        $nuevoShortname = $original['shortname'] . "-COPY-{$sufijo}";

        $resultado = duplicateCourseB($courseIdOrigen, $nuevoFullname, $nuevoShortname);

        $crudoJson = htmlspecialchars(json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if (isset($resultado['error']) || isset($resultado['exception'])) {
            $detalle = htmlspecialchars($resultado['message'] ?? $resultado['error'] ?? 'Error desconocido');
            $resultadoHtml = "
                <div class='box error'>
                    <strong>RESULTADO: FALLÓ</strong><br>
                    Detalle: {$detalle}
                </div>
                <details><summary>Ver respuesta cruda de Moodle</summary><pre>{$crudoJson}</pre></details>
            ";
        } elseif (isset($resultado['id'])) {
            $nuevoId = (int) $resultado['id'];
            $link = "https://campus.cenditech.com.co/course/view.php?id={$nuevoId}";
            $resultadoHtml = "
                <div class='box ok'>
                    <strong>RESULTADO: OK</strong><br>
                    Curso original: {$original['fullname']} ({$original['shortname']})<br>
                    Nuevo curso creado con id = <strong>{$nuevoId}</strong><br>
                    Nuevo fullname: {$nuevoFullname}<br>
                    Nuevo shortname: {$nuevoShortname}<br><br>
                    <a href='{$link}' target='_blank'>Ver curso duplicado en Moodle &rarr;</a>
                </div>
                <details><summary>Ver respuesta cruda de Moodle</summary><pre>{$crudoJson}</pre></details>
            ";
        } else {
            $resultadoHtml = "
                <div class='box warn'>
                    <strong>RESULTADO: INESPERADO</strong><br>
                    Revisa la respuesta cruda abajo.
                </div>
                <details open><summary>Respuesta cruda de Moodle</summary><pre>{$crudoJson}</pre></details>
            ";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Prueba: Duplicar curso Moodle (id=29)</title>
<style>
    body {
        font-family: -apple-system, Segoe UI, Roboto, Arial, sans-serif;
        background: #f4f5f7;
        color: #1f2328;
        max-width: 720px;
        margin: 40px auto;
        padding: 0 20px;
        line-height: 1.5;
    }
    h1 { font-size: 1.4rem; }
    .card {
        background: #fff;
        border: 1px solid #d9dce1;
        border-radius: 10px;
        padding: 24px;
        margin-bottom: 20px;
    }
    .meta {
        font-size: 0.9rem;
        color: #5b6270;
        margin-bottom: 16px;
    }
    button {
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 10px 18px;
        font-size: 0.95rem;
        cursor: pointer;
    }
    button:hover { background: #1d4ed8; }
    .box {
        border-radius: 8px;
        padding: 14px 16px;
        margin-top: 10px;
    }
    .box.ok    { background: #e8f7ee; border: 1px solid #34c759; }
    .box.error { background: #fdecec; border: 1px solid #e5484d; }
    .box.warn  { background: #fff8e6; border: 1px solid #e5a638; }
    pre {
        background: #0f172a;
        color: #d7dee8;
        padding: 14px;
        border-radius: 8px;
        overflow-x: auto;
        font-size: 0.82rem;
    }
    details { margin-top: 10px; }
    summary { cursor: pointer; font-size: 0.9rem; color: #2563eb; }
    a { color: #2563eb; }
</style>
</head>
<body>

    <h1>🧪 Prueba: Duplicar curso Moodle</h1>

    <div class="card">
        <div class="meta">
            Curso origen fijo para esta prueba: <strong>id = <?= $courseIdOrigen ?></strong><br>
            Cada duplicado genera un shortname único con timestamp para evitar choques.
        </div>

        <form method="post">
            <button type="submit" name="duplicar" value="1">
                Duplicar curso id=<?= $courseIdOrigen ?>
            </button>
        </form>

        <?= $resultadoHtml ?>
    </div>

    <p style="font-size:0.8rem;color:#8a919e;">
        ⚠️ Este archivo es solo para pruebas y contiene el token de Moodle en texto plano.
        No lo dejes accesible públicamente en producción.
    </p>

</body>
</html>