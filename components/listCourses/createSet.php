<?php
// =====================================================================
// Creación manual de un set de cursos (técnico + Inglés + Habilidades
// blandas) a partir de un código técnico seleccionado.
// - Duplica las plantillas en Moodle respetando serie y codificación.
// - Registra los 3 cursos en `cursos` y el set en `sets_cursos`.
// =====================================================================

session_start();
require_once __DIR__ . '/../../controller/conexion.php';
require_once __DIR__ . '/../moodle_api.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'No autorizado']);
    exit;
}

// Ampliar el tiempo de ejecución: se duplican 3 cursos en Moodle.
@set_time_limit(180);

// Cursos técnicos con su categoría y plantilla en Moodle.
const CATEGORIAS_TECNICAS_SET = [
    'IA'    => ['categoryid' => 16, 'nombre' => 'Inteligencia Artificial',      'template_courseid' => 32],
    'PDS'   => ['categoryid' => 17, 'nombre' => 'Programación y Desarrollo',    'template_courseid' => 31],
    'DT'    => ['categoryid' => 18, 'nombre' => 'Análisis de Datos',            'template_courseid' => 37],
    'CIBER' => ['categoryid' => 19, 'nombre' => 'Ciberseguridad',               'template_courseid' => 36],
    'CN'    => ['categoryid' => 20, 'nombre' => 'Computación en la Nube',       'template_courseid' => 38],
    'BLO'   => ['categoryid' => 21, 'nombre' => 'Blockchain',                   'template_courseid' => 34],
    'RA'    => ['categoryid' => 22, 'nombre' => 'Robótica y Automatización',    'template_courseid' => 35],
    'IOT'   => ['categoryid' => 23, 'nombre' => 'Internet de las Cosas',        'template_courseid' => 33],
];

const CATEGORIAS_OBLIGATORIAS_SET = [
    'ING' => ['categoryid' => 14, 'nombre' => 'Inglés',              'template_courseid' => 28],
    'BH'  => ['categoryid' => 15, 'nombre' => 'Habilidades Blandas', 'template_courseid' => 29],
];

function getCursosCategoriaM($categoryId) {
    $r = callMoodleAPIB('core_course_get_courses_by_field', [
        'field' => 'category',
        'value' => $categoryId,
    ]);
    return $r['courses'] ?? [];
}

function siguienteSerieM($categoryId, $codigo) {
    $cursos = getCursosCategoriaM($categoryId);
    $max = 0;
    foreach ($cursos as $c) {
        if (preg_match('/^' . preg_quote($codigo, '/') . '-(\d+)$/', $c['shortname'], $m)) {
            $max = max($max, (int) $m[1]);
        }
    }
    return $max + 1;
}

function duplicarCursoM($templateId, $fullname, $shortname, $categoryId) {
    return callMoodleAPIB('core_course_duplicate_course', [
        'courseid'   => $templateId,
        'fullname'   => $fullname,
        'shortname'  => $shortname,
        'categoryid' => $categoryId,
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

function registrarCursoM($conn, $courseId, $code, $name) {
    $stmt = $conn->prepare("INSERT INTO cursos (course_id, course_code, course_name) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $courseId, $code, $name);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function registrarSetM($conn, $codigo, $serie, $tecnicoId, $inglesId, $blandasId) {
    $stmt = $conn->prepare("INSERT INTO sets_cursos (codigo_tecnico, serie, curso_tecnico_id, curso_ingles_id, curso_habilidades_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('siiii', $codigo, $serie, $tecnicoId, $inglesId, $blandasId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function crearSetCursoTecnicoM($conn, $codigo) {
    $tecnicos = CATEGORIAS_TECNICAS_SET;
    $oblig    = CATEGORIAS_OBLIGATORIAS_SET;

    if (!isset($tecnicos[$codigo])) {
        return ['ok' => false, 'mensaje' => 'Código técnico no válido.'];
    }

    $tec = $tecnicos[$codigo];
    $ing = $oblig['ING'];
    $bh  = $oblig['BH'];

    $serie  = siguienteSerieM($tec['categoryid'], $codigo);
    $shortT = "{$codigo}-{$serie}";
    $shortI = "ING-{$codigo}-{$serie}";
    $shortB = "BH-{$codigo}-{$serie}";
    $fullT  = "{$tec['nombre']} {$shortT}";
    $fullI  = "{$ing['nombre']} {$shortI}";
    $fullB  = "{$bh['nombre']} {$shortB}";

    $pasos = [
        [$tec['template_courseid'], $fullT, $shortT, $tec['categoryid'], 'Técnico'],
        [$ing['template_courseid'], $fullI, $shortI, $ing['categoryid'], 'Inglés'],
        [$bh['template_courseid'],  $fullB, $shortB,  $bh['categoryid'],  'Habilidades blandas'],
    ];

    $ids = [];
    $detalles = [];
    foreach ($pasos as [$tid, $fn, $sn, $cid, $tipoCurso]) {
        $r = duplicarCursoM($tid, $fn, $sn, $cid);
        if (isset($r['error']) || isset($r['exception']) || !isset($r['id'])) {
            $msg = $r['message'] ?? $r['error'] ?? ($r['exception']['message'] ?? 'Error desconocido');
            return ['ok' => false, 'mensaje' => 'Error al duplicar el curso ' . $tipoCurso . ' (' . $sn . '): ' . $msg];
        }
        $ids[] = $r['id'];
        $detalles[] = ['tipo' => $tipoCurso, 'moodle_id' => (int) $r['id'], 'shortname' => $sn, 'fullname' => $fn];
        if (!registrarCursoM($conn, $r['id'], $sn, $fn)) {
            return ['ok' => false, 'mensaje' => 'No se pudo registrar el curso ' . $sn . ' en la base de datos.'];
        }
    }

    if (!registrarSetM($conn, $codigo, $serie, $ids[0], $ids[1], $ids[2])) {
        return ['ok' => false, 'mensaje' => 'No se pudo registrar el set en la base de datos.'];
    }

    return [
        'ok'      => true,
        'mensaje' => 'Set ' . $codigo . '-' . $serie . ' creado correctamente.',
        'serie'   => $serie,
        'set_id'  => $conn->insert_id,
        'cursos'  => $detalles,
    ];
}

$codigo = isset($_POST['codigo']) ? strtoupper(trim($_POST['codigo'])) : '';

if ($codigo === '' || !isset(CATEGORIAS_TECNICAS_SET[$codigo])) {
    echo json_encode(['ok' => false, 'mensaje' => 'Debe seleccionar un curso técnico válido.']);
    exit;
}

// Modo preview: solo devuelve el nombre y código que tendrá cada curso del set.
if (isset($_POST['preview']) && $_POST['preview'] === '1') {
    $tec   = CATEGORIAS_TECNICAS_SET[$codigo];
    $ing   = CATEGORIAS_OBLIGATORIAS_SET['ING'];
    $bh    = CATEGORIAS_OBLIGATORIAS_SET['BH'];
    $serie = siguienteSerieM($tec['categoryid'], $codigo);
    echo json_encode([
        'ok'    => true,
        'serie' => $serie,
        'cursos' => [
            ['tipo' => 'Técnico',             'nombre' => $tec['nombre'], 'codigo' => $codigo . '-' . $serie],
            ['tipo' => 'Inglés',              'nombre' => $ing['nombre'], 'codigo' => 'ING-' . $codigo . '-' . $serie],
            ['tipo' => 'Habilidades blandas', 'nombre' => $bh['nombre'],  'codigo' => 'BH-' . $codigo . '-' . $serie],
        ],
    ]);
    exit;
}

$resultado = crearSetCursoTecnicoM($conn, $codigo);
echo json_encode($resultado);
