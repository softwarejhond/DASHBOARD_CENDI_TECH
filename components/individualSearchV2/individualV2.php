<?php
// =====================================================================
// Consulta individual V2
// - Modo AJAX: devuelve JSON con la información básica del estudiante.
// - Modo UI: renderiza el buscador y el contenedor de resultados.
// Tablas: user_register, enrollments, departamentos, municipios,
//         sets_cursos, cursos y acudientes (si es menor de edad).
// =====================================================================

if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    session_start();
    require_once __DIR__ . '/../../controller/conexion.php';
    header('Content-Type: application/json');

    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        http_response_code(401);
        echo json_encode(['encontrado' => false, 'error' => 'no autorizado']);
        exit;
    }

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    if ($search === '') {
        echo json_encode(['encontrado' => false, 'error' => 'sin búsqueda']);
        exit;
    }

    // Datos personales + ubicación (departamento y municipio por nombre)
    $stmt = $conn->prepare("
        SELECT ur.*,
               TIMESTAMPDIFF(YEAR, ur.birthdate, CURDATE()) AS age,
               d.departamento AS departamento_nombre,
               m.nom_municipio AS municipio_nombre
        FROM user_register ur
        LEFT JOIN departamentos d ON ur.department = d.id_departamento
        LEFT JOIN municipios m ON ur.municipality = m.cod_municipio
        WHERE ur.number_id = ?
        LIMIT 1
    ");
    $stmt->bind_param('s', $search);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['encontrado' => false]);
        exit;
    }

    $u = $result->fetch_assoc();

    $nombreCompleto = trim(implode(' ', array_filter([
        $u['first_name'], $u['second_name'], $u['first_last'], $u['second_last']
    ], 'strlen')));

    $age = ($u['age'] !== null) ? (int) $u['age'] : null;

    // Matrícula (enrollments + sets_cursos + cursos), la más reciente
    $matricula = null;
    $stmtM = $conn->prepare("
        SELECT e.*,
               sc.codigo_tecnico,
               sc.serie,
               c1.course_name AS tecnico_name,
               c1.course_code AS tecnico_code,
               c2.course_name AS ingles_name,
               c2.course_code AS ingles_code,
               c3.course_name AS habilidades_name,
               c3.course_code AS habilidades_code
        FROM enrollments e
        LEFT JOIN sets_cursos sc ON e.set_id = sc.id
        LEFT JOIN cursos c1 ON e.course_tecnico_id = c1.course_id
        LEFT JOIN cursos c2 ON e.course_ingles_id = c2.course_id
        LEFT JOIN cursos c3 ON e.course_habilidades_id = c3.course_id
        WHERE e.number_id = ?
        ORDER BY e.id DESC
        LIMIT 1
    ");
    $stmtM->bind_param('s', $search);
    $stmtM->execute();
    $resM = $stmtM->get_result();
    if ($resM->num_rows > 0) {
        $matricula = $resM->fetch_assoc();
    }

    // Acudiente (solo si es menor de edad)
    $acudiente = null;
    if ($age !== null && $age < 18) {
        $stmtA = $conn->prepare("SELECT * FROM acudientes WHERE number_id = ? ORDER BY id DESC LIMIT 1");
        $stmtA->bind_param('s', $search);
        $stmtA->execute();
        $resA = $stmtA->get_result();
        if ($resA->num_rows > 0) {
            $acudiente = $resA->fetch_assoc();
        }
    }

    $respuesta = [
        'encontrado' => true,
        'personal' => [
            'nombre_completo' => $nombreCompleto,
            'typeID' => $u['typeID'],
            'number_id' => $u['number_id'],
            'gender' => $u['gender'],
            'age' => $age,
            'birthdate' => !empty($u['birthdate']) && $u['birthdate'] !== '0000-00-00' ? date('d/m/Y', strtotime($u['birthdate'])) : null,
            'nationality' => $u['nationality'],
        ],
        'contacto' => [
            'first_phone' => $u['first_phone'],
            'second_phone' => $u['second_phone'],
            'email' => $u['email'],
            'email_verified' => (int) $u['email_verified'],
            'emergency_contact_name' => $u['emergency_contact_name'],
            'emergency_contact_number' => $u['emergency_contact_number'],
        ],
        'ubicacion' => [
            'departamento' => $u['departamento_nombre'],
            'municipio' => $u['municipio_nombre'],
            'address' => $u['address'],
            'residence_area' => $u['residence_area'],
            'comuna_corregimiento' => $u['comuna_corregimiento'],
            'barrio' => $u['barrio'],
        ],
        'academico' => [
            'program' => $u['program'],
            'mode' => $u['mode'],
            'fecha_inscripcion' => !empty($u['creationDate']) ? date('d/m/Y', strtotime($u['creationDate'])) : null,
        ],
        'matricula' => $matricula ? [
            'program_name' => $matricula['program_name'],
            'institutional_email' => $matricula['institutional_email'],
            'username' => $matricula['username'],
            'status' => $matricula['status'],
            'serie' => $matricula['serie'],
            'codigo_tecnico' => $matricula['codigo_tecnico'],
            'cursos' => [
                'tecnico' => ['code' => $matricula['tecnico_code'], 'name' => $matricula['tecnico_name']],
                'ingles' => ['code' => $matricula['ingles_code'], 'name' => $matricula['ingles_name']],
                'habilidades' => ['code' => $matricula['habilidades_code'], 'name' => $matricula['habilidades_name']],
            ],
            'fecha_matricula' => !empty($matricula['created_at']) ? date('d/m/Y', strtotime($matricula['created_at'])) : null,
        ] : null,
        'acudiente' => $acudiente ? [
            'guardian_full_name' => $acudiente['guardian_full_name'],
            'guardian_document' => $acudiente['guardian_document'],
            'guardian_phone' => $acudiente['guardian_phone'],
            'guardian_email' => $acudiente['guardian_email'],
        ] : null,
    ];

    echo json_encode($respuesta);
    exit;
}
?>
<!-- =====================================================================
     Modo UI: buscador + resultados (render por AJAX en el mismo archivo)
     ===================================================================== -->
<style>
    :root {
        --isv-magenta: #ec008c;
        --isv-blue: #181E93;
        --isv-green: #00976a;
        --isv-amber: #F9B233;
        --isv-ink: #0f172a;
        --isv-muted: #64748b;
        --isv-border: #e2e8f0;
        --isv-surface: #f8fafc;
    }
    .isv2 { color: var(--isv-ink); padding-bottom: 4rem; }

    /* Buscador (ancho completo, fondo de color) */
    .isv2-search { width: 100%; margin-bottom: 1.5rem; }
    .isv2-search__card {
        width: 100%;
        background: #30336B;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .06);
    }
    .isv2-search__title { color: #fff; font-size: 1.15rem; font-weight: 700; margin: 0 0 .3rem; }
    .isv2-search__hint { color: rgba(255, 255, 255, .78); font-size: .85rem; margin: 0 0 1rem; }
    .isv2-search .input-group { width: 100%; }
    .isv2-search .form-control {
        border: 0;
        font-size: 1.05rem;
        text-align: center;
        height: 46px;
    }
    .isv2-search .form-control:focus { box-shadow: none; }
    .isv2-btn-search {
        background: var(--isv-magenta);
        border: 1px solid var(--isv-magenta);
        color: #fff;
        padding: .5rem 1.3rem;
        font-weight: 600;
    }
    .isv2-btn-search:hover { background: #c90078; border-color: #c90078; color: #fff; }

    /* Tarjetas de información */
    .isv2-card {
        background: #fff;
        border: 1px solid var(--isv-border);
        border-radius: 16px;
        height: 100%;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }
    .isv2-card__title {
        display: flex; align-items: center; gap: .5rem;
        padding: .85rem 1.25rem;
        border-bottom: 1px solid var(--isv-border);
        font-size: .95rem; font-weight: 700;
    }
    .isv2-card__title i { color: var(--isv-magenta); font-size: 1.05rem; }
    .isv2-card__body { padding: 1rem 1.25rem 1.15rem; }

    .isv2-row {
        display: flex; justify-content: space-between; align-items: baseline; gap: 1rem;
        padding: .5rem 0;
        border-bottom: 1px dashed var(--isv-border);
        font-size: .88rem;
    }
    .isv2-row:last-child { border-bottom: 0; }
    .isv2-row__label { color: var(--isv-muted); flex: 0 0 auto; }
    .isv2-row__value { text-align: right; font-weight: 600; word-break: break-word; }
    .isv2-empty { color: var(--isv-muted); font-weight: 400; }

    /* Cabecera interna de la tarjeta personal */
    .isv2-person { display: flex; align-items: center; gap: .85rem; margin-bottom: .6rem; }
    .isv2-avatar {
        flex: 0 0 auto;
        width: 50px; height: 50px;
        border-radius: 14px;
        background: var(--isv-magenta);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem; font-weight: 700;
    }
    .isv2-person__info { min-width: 0; }
    .isv2-person__info h3 { margin: 0 0 .15rem; font-size: 1.05rem; font-weight: 700; word-break: break-word; }
    .isv2-person__sub { color: var(--isv-muted); font-size: .85rem; }

    .isv2-badges { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: .6rem; }
    .isv2-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        background: #eef1f6; color: var(--isv-ink);
        font-size: .75rem; font-weight: 600;
        border-radius: 999px; padding: .25rem .7rem;
    }
    .isv2-badge--blue { background: #eceefb; color: var(--isv-blue); }
    .isv2-badge--green { background: #e6f5ef; color: var(--isv-green); }
    .isv2-badge--amber { background: #fef5e6; color: #b07a00; }
    .isv2-badge--magenta { background: #fdeef6; color: var(--isv-magenta); }

    .isv2-divider { border-top: 1px solid var(--isv-border); margin: .25rem 0 .4rem; }

    /* Código de curso */
    .isv2-course-code {
        display: inline-block;
        margin-left: .4rem;
        background: #eef1f6;
        color: var(--isv-muted);
        border-radius: 5px;
        padding: .05rem .45rem;
        font-size: .72rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-weight: 600;
    }

    /* Estados */
    .isv2-empty-box {
        text-align: center; color: var(--isv-muted);
        padding: 2.5rem 1rem;
        background: #fff; border: 1px dashed var(--isv-border); border-radius: 16px;
    }
    .isv2-empty-box i { font-size: 2rem; display: block; margin-bottom: .5rem; color: var(--isv-magenta); }
    .isv2-loading { text-align: center; color: var(--isv-muted); padding: 2rem; }
</style>

<div class="isv2">
    <div class="isv2-search">
        <div class="isv2-search__card">
            <h4 class="isv2-search__title">Buscar estudiante</h4>
            <p class="isv2-search__hint">Ingresa el número de identificación para consultar su información básica.</p>
            <form id="isv2-form" autocomplete="off">
                <div class="input-group">
                    <input type="text" id="isv2-input" class="form-control" inputmode="numeric"
                        placeholder="Número de identificación" maxlength="20"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <button type="submit" class="btn isv2-btn-search" title="Buscar"><i class="bi bi-search"></i> Buscar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="isv2-result"></div>
</div>

<script>
    (function () {
        var form = document.getElementById('isv2-form');
        var input = document.getElementById('isv2-input');
        var result = document.getElementById('isv2-result');

        function esc(s) {
            if (s === null || s === undefined) return '';
            return String(s).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        function row(label, value) {
            var v = (value === null || value === undefined || value === '')
                ? '<span class="isv2-empty">&mdash;</span>'
                : esc(value);
            return '<div class="isv2-row"><span class="isv2-row__label">' + esc(label) + '</span>' +
                '<span class="isv2-row__value">' + v + '</span></div>';
        }

        function card(title, icon, bodyHtml) {
            return '<div class="col-12 col-md-6 col-lg-4"><div class="isv2-card">' +
                '<div class="isv2-card__title"><i class="' + icon + '"></i>' + esc(title) + '</div>' +
                '<div class="isv2-card__body">' + bodyHtml + '</div></div></div>';
        }

        function badge(text, cls) {
            return '<span class="isv2-badge ' + cls + '">' + esc(text) + '</span>';
        }

        function courseRow(label, curso) {
            if (!curso || !curso.name) return '';
            var code = curso.code ? '<span class="isv2-course-code">' + esc(curso.code) + '</span>' : '';
            return '<div class="isv2-row"><span class="isv2-row__label">' + esc(label) + '</span>' +
                '<span class="isv2-row__value">' + esc(curso.name) + code + '</span></div>';
        }

        function initials(name) {
            var parts = String(name || '').trim().split(/\s+/).filter(Boolean);
            if (!parts.length) return '?';
            var a = parts[0].charAt(0);
            var b = parts.length > 1 ? parts[parts.length - 1].charAt(0) : (parts[0].charAt(1) || '');
            return (a + b).toUpperCase();
        }

        function render(d) {
            if (!d.encontrado) {
                result.innerHTML = '<div class="isv2-empty-box"><i class="bi bi-person-x"></i>No se encontró ningún estudiante con esa identificación.</div>';
                return;
            }
            var p = d.personal, c = d.contacto, ub = d.ubicacion, ac = d.academico;

            // Badges generales (nombre, cédula, email verificado y demás) en la tarjeta personal
            var badges = '';
            if (ac && ac.program) badges += badge(ac.program, 'isv2-badge--blue');
            if (c) {
                if (c.email_verified === 1) badges += badge('Email verificado', 'isv2-badge--green');
                else badges += badge('Email sin verificar', 'isv2-badge--amber');
            }
            if (d.matricula) {
                badges += badge(d.matricula.status === 'enrolled' ? 'Matriculado' : 'Pendiente', 'isv2-badge--magenta');
            }

            var html = '<div class="row g-3">';

            // Primera tarjeta: información personal + encabezado (nombre, cédula, badges) + nacionalidad
            var personalBody =
                '<div class="isv2-person">' +
                    '<div class="isv2-avatar">' + initials(p.nombre_completo) + '</div>' +
                    '<div class="isv2-person__info">' +
                        '<h3>' + esc(p.nombre_completo) + '</h3>' +
                        '<div class="isv2-person__sub">' + esc(p.typeID) + ' &middot; ' + esc(p.number_id) + '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="isv2-badges">' + badges + '</div>' +
                '<div class="isv2-divider"></div>' +
                row('Género', p.gender) +
                row('Edad', p.age !== null ? p.age + ' años' : null) +
                row('Fecha de nacimiento', p.birthdate) +
                row('Nacionalidad', p.nationality);

            html += card('Información personal', 'bi bi-person-badge', personalBody);

            html += card('Contacto', 'bi bi-telephone',
                row('Teléfono 1', c.first_phone) +
                row('Teléfono 2', c.second_phone) +
                row('Email', c.email) +
                row('Contacto de emergencia', c.emergency_contact_name) +
                row('Tel. de emergencia', c.emergency_contact_number));

            html += card('Ubicación y origen', 'bi bi-geo-alt',
                row('Departamento', ub.departamento) +
                row('Municipio', ub.municipio) +
                row('Dirección', ub.address) +
                row('Área', ub.residence_area) +
                row('Comuna / Corregimiento', ub.comuna_corregimiento) +
                row('Barrio / Vereda', ub.barrio));

            html += card('Académico', 'bi bi-mortarboard',
                row('Programa', ac.program) +
                row('Modalidad', ac.mode) +
                row('Fecha de inscripción', ac.fecha_inscripcion));

            if (d.matricula) {
                var m = d.matricula;
                var matRows =
                    row('Programa', m.program_name) +
                    row('Correo institucional', m.institutional_email) +
                    row('Usuario Moodle', m.username) +
                    row('Serie', m.serie) +
                    row('Fecha de matrícula', m.fecha_matricula);
                matRows += courseRow('Curso técnico', m.cursos.tecnico);
                matRows += courseRow('Curso de inglés', m.cursos.ingles);
                matRows += courseRow('Habilidades blandas', m.cursos.habilidades);
                html += card('Matrícula', 'bi bi-journal-check', matRows);
            } else {
                html += card('Matrícula', 'bi bi-journal-check', row('Estado', 'Sin matrícula registrada'));
            }

            if (d.acudiente) {
                var a = d.acudiente;
                html += card('Acudiente (menor de edad)', 'bi bi-person-hearts',
                    row('Nombre', a.guardian_full_name) +
                    row('Documento', a.guardian_document) +
                    row('Teléfono', a.guardian_phone) +
                    row('Email', a.guardian_email));
            }

            html += '</div>';
            result.innerHTML = html;
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var q = input.value.trim();
                if (!q) return;
                result.innerHTML = '<div class="isv2-loading"><span class="spinner-border spinner-border-sm me-2"></span> Buscando...</div>';
                fetch('components/individualSearchV2/individualV2.php?ajax=1&search=' + encodeURIComponent(q))
                    .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                    .then(render)
                    .catch(function (err) {
                        console.error(err);
                        result.innerHTML = '<div class="isv2-empty-box"><i class="bi bi-exclamation-triangle"></i>Ocurrió un error al buscar.</div>';
                    });
            });
        }
    })();
</script>
