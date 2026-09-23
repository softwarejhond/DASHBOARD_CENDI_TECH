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
            'birthdate_raw' => !empty($u['birthdate']) && $u['birthdate'] !== '0000-00-00' ? $u['birthdate'] : null,
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
            'department' => $u['department'],
            'municipality' => $u['municipality'],
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

    /* Botón de edición en el título de tarjeta */
    .isv2-card__title { position: relative; }
    .isv2-edit-btn {
        margin-left: auto;
        border: 0; background: transparent; color: var(--isv-muted);
        cursor: pointer; font-size: 1rem; line-height: 1; padding: .1rem .2rem;
    }
    .isv2-edit-btn:hover { color: var(--isv-magenta); }

    /* Formularios de edición */
    .isv2-edit-row { padding: .4rem 0; border-bottom: 1px dashed var(--isv-border); }
    .isv2-edit-row:last-child { border-bottom: 0; }
    .isv2-edit-row label { display: block; color: var(--isv-muted); font-size: .78rem; margin-bottom: .15rem; }
    .isv2-edit-row input, .isv2-edit-row select {
        width: 100%; border: 1px solid var(--isv-border); border-radius: 8px;
        padding: .35rem .55rem; font-size: .85rem;
    }
    .isv2-edit-actions { display: flex; gap: .5rem; margin-top: .75rem; }
    .isv2-edit-actions .btn { font-size: .82rem; padding: .3rem .8rem; }
    .isv2-btn-save { background: var(--isv-green); color: #fff; border: 1px solid var(--isv-green); }
    .isv2-btn-save:hover { background: #007a56; color: #fff; }
    .isv2-btn-cancel { background: #fff; color: var(--isv-muted); border: 1px solid var(--isv-border); }

    /* Tarjeta de cursos y notas */
    .isv2-card--cursos { border: 0; box-shadow: 0 10px 25px rgba(24, 30, 147, .12); }
    .isv2-card--cursos .isv2-card__title {
        background: linear-gradient(120deg, #30336B 0%, #181E93 60%, #ec008c 140%);
        color: #fff; border-bottom: 0;
    }
    .isv2-card--cursos .isv2-card__title i { color: #fff; }
    .isv2-curso {
        display: flex; align-items: center; justify-content: space-between; gap: .6rem;
        border-radius: 12px; padding: .55rem .75rem; margin-bottom: .5rem;
        border-left: 5px solid var(--isv-muted);
    }
    .isv2-curso__info { min-width: 0; }
    .isv2-curso__nombre { font-weight: 700; font-size: .86rem; word-break: break-word; }
    .isv2-curso__code { font-size: .72rem; color: var(--isv-muted); font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
    .isv2-curso__nota { font-size: .95rem; font-weight: 700; white-space: nowrap; text-align: right; }
    .isv2-curso--tecnico { background: #eceefb; border-left-color: var(--isv-blue); }
    .isv2-curso--ingles { background: #e6f5ef; border-left-color: var(--isv-green); }
    .isv2-curso--habilidades { background: #fef5e6; border-left-color: var(--isv-amber); }
    .isv2-curso__sinnota { font-size: .72rem; font-weight: 600; color: #b45309; }
    .isv2-promedio {
        margin-top: .4rem; border-radius: 12px; padding: .7rem .9rem; text-align: center;
        background: linear-gradient(120deg, #fdeef6, #eceefb);
        border: 1px dashed var(--isv-magenta);
    }
    .isv2-promedio__label { font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; color: var(--isv-magenta); font-weight: 700; }
    .isv2-promedio__value { font-size: 1.6rem; font-weight: 800; color: var(--isv-ink); }
    .isv2-promedio__sub { font-size: .72rem; color: var(--isv-muted); }
    .isv2-promedio__estado { margin-top: .45rem; }

    /* Acordeón de acudiente */
    .isv2-acc-btn {
        width: 100%; display: flex; align-items: center; gap: .4rem;
        background: #fdeef6; color: var(--isv-magenta); border: 0; border-radius: 10px;
        padding: .45rem .7rem; font-size: .82rem; font-weight: 700; margin-top: .3rem;
    }
    .isv2-acc-btn .isv2-acc-chevron { margin-left: auto; transition: transform .2s; }
    .isv2-acc-btn[aria-expanded="true"] .isv2-acc-chevron { transform: rotate(180deg); }
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    (function () {
        var form = document.getElementById('isv2-form');
        var input = document.getElementById('isv2-input');
        var result = document.getElementById('isv2-result');

        var currentData = null;
        var notasData = null;
        var notasLoading = false;
        var editing = null;
        var LIMITE_DEPARTAMENTO = 'ANTIOQUIA';
        var LIMITE_MUNICIPIO = 'MEDELLÍN';

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

        function card(title, icon, bodyHtml, opts) {
            opts = opts || {};
            var editBtn = '';
            if (opts.editable) {
                var isEditing = editing === opts.section;
                editBtn = '<button type="button" class="isv2-edit-btn" title="' + (isEditing ? 'Cancelar' : 'Editar') + '" ' +
                    'onclick="' + (isEditing ? 'isv2Cancel()' : "isv2Edit('" + opts.section + "')") + '">' +
                    '<i class="bi ' + (isEditing ? 'bi-x-lg' : 'bi-pencil') + '"></i></button>';
            }
            return '<div class="' + (opts.colClass || 'col-12 col-md-6 col-lg-4') + '"><div class="isv2-card ' + (opts.cardClass || '') + '">' +
                '<div class="isv2-card__title"><i class="' + icon + '"></i>' + esc(title) + editBtn + '</div>' +
                '<div class="isv2-card__body">' + bodyHtml + '</div></div></div>';
        }

        function badge(text, cls) {
            return '<span class="isv2-badge ' + cls + '">' + esc(text) + '</span>';
        }

        function initials(name) {
            var parts = String(name || '').trim().split(/\s+/).filter(Boolean);
            if (!parts.length) return '?';
            var a = parts[0].charAt(0);
            var b = parts.length > 1 ? parts[parts.length - 1].charAt(0) : (parts[0].charAt(1) || '');
            return (a + b).toUpperCase();
        }

        function val(id) {
            var el = document.getElementById(id);
            return el ? el.value : '';
        }

        function editRow(label, inputHtml) {
            return '<div class="isv2-edit-row"><label>' + esc(label) + '</label>' + inputHtml + '</div>';
        }

        function editActions(saveFn) {
            return '<div class="isv2-edit-actions">' +
                '<button type="button" class="btn isv2-btn-save" onclick="' + saveFn + '"><i class="bi bi-check2"></i> Guardar</button>' +
                '<button type="button" class="btn isv2-btn-cancel" onclick="isv2Cancel()">Cancelar</button>' +
                '</div>';
        }

        function selectHtml(id, options, current, onChange) {
            var html = '<select id="' + id + '"' + (onChange ? ' onchange="' + onChange + '"' : '') + '>';
            html += '<option value="">Seleccione</option>';
            options.forEach(function (o) {
                html += '<option value="' + esc(o) + '"' + (o === current ? ' selected' : '') + '>' + esc(o) + '</option>';
            });
            html += '</select>';
            return html;
        }

        function viewPersonal(p) {
            return row('Género', p.gender) +
                row('Edad', p.age !== null ? p.age + ' años' : null) +
                row('Fecha de nacimiento', p.birthdate) +
                row('Nacionalidad', p.nationality);
        }

        function editPersonal(p) {
            var genders = ['Hombre', 'Mujer'];
            if (p.gender && genders.indexOf(p.gender) === -1) genders.unshift(p.gender);
            return editRow('Género', selectHtml('isv2-edit-gender', genders, p.gender)) +
                editRow('Fecha de nacimiento', '<input type="date" id="isv2-edit-birthdate" value="' + esc(p.birthdate_raw || '') + '">') +
                row('Edad', p.age !== null ? p.age + ' años' : null) +
                row('Nacionalidad', p.nationality) +
                editActions('isv2SavePersonal()');
        }

        function acudienteAccordion(bodyHtml, open) {
            return '<div class="isv2-divider"></div>' +
                '<button class="isv2-acc-btn" type="button" data-bs-toggle="collapse" data-bs-target="#isv2-acudiente-body" aria-expanded="' + (open ? 'true' : 'false') + '" aria-controls="isv2-acudiente-body">' +
                    '<i class="bi bi-person-hearts"></i> Acudiente (menor de edad)' +
                    '<i class="bi bi-chevron-down isv2-acc-chevron"></i>' +
                '</button>' +
                '<div class="collapse' + (open ? ' show' : '') + '" id="isv2-acudiente-body">' + bodyHtml + '</div>';
        }

        function viewContacto(c, a) {
            var html = row('Teléfono 1', c.first_phone) +
                row('Teléfono 2', c.second_phone) +
                row('Email', c.email) +
                row('Contacto de emergencia', c.emergency_contact_name) +
                row('Tel. de emergencia', c.emergency_contact_number);
            if (a) {
                html += acudienteAccordion(
                    row('Nombre', a.guardian_full_name) +
                    row('Documento', a.guardian_document) +
                    row('Teléfono', a.guardian_phone) +
                    row('Email', a.guardian_email), false);
            }
            return html;
        }

        function editContacto(c, a) {
            var html = editRow('Teléfono 1', '<input type="text" id="isv2-edit-first_phone" inputmode="numeric" value="' + esc(c.first_phone) + '">') +
                editRow('Teléfono 2', '<input type="text" id="isv2-edit-second_phone" inputmode="numeric" value="' + esc(c.second_phone) + '">') +
                row('Email', c.email) +
                editRow('Contacto de emergencia', '<input type="text" id="isv2-edit-emergency_contact_name" value="' + esc(c.emergency_contact_name) + '">') +
                editRow('Tel. de emergencia', '<input type="text" id="isv2-edit-emergency_contact_number" inputmode="numeric" value="' + esc(c.emergency_contact_number) + '">');
            if (a) {
                html += acudienteAccordion(
                    editRow('Nombre', '<input type="text" id="isv2-edit-guardian_full_name" value="' + esc(a.guardian_full_name) + '">') +
                    editRow('Documento', '<input type="text" id="isv2-edit-guardian_document" value="' + esc(a.guardian_document) + '">') +
                    editRow('Teléfono', '<input type="text" id="isv2-edit-guardian_phone" inputmode="numeric" value="' + esc(a.guardian_phone) + '">') +
                    editRow('Email', '<input type="text" id="isv2-edit-guardian_email" value="' + esc(a.guardian_email) + '">'), true);
            }
            html += editActions('isv2SaveContacto()');
            return html;
        }

        function viewUbicacion(ub) {
            return row('Departamento', ub.departamento) +
                row('Municipio', ub.municipio) +
                row('Dirección', ub.address) +
                row('Área', ub.residence_area) +
                row('Comuna / Corregimiento', ub.comuna_corregimiento) +
                row('Barrio / Vereda', ub.barrio);
        }

        function editUbicacion(ub) {
            var areas = ['Urbana', 'Rural'];
            if (ub.residence_area && areas.indexOf(ub.residence_area) === -1) areas.unshift(ub.residence_area);
            return editRow('Departamento', '<select id="isv2-edit-department" data-current="' + esc(ub.department || '') + '" onchange="isv2LoadMunicipios()"><option value="">Cargando...</option></select>') +
                editRow('Municipio', '<select id="isv2-edit-municipality" data-current="' + esc(ub.municipality || '') + '"><option value="">Cargando...</option></select>') +
                editRow('Dirección', '<input type="text" id="isv2-edit-address" value="' + esc(ub.address) + '">') +
                editRow('Área', selectHtml('isv2-edit-residence_area', areas, ub.residence_area)) +
                editRow('Comuna / Corregimiento', '<select id="isv2-edit-comuna" data-current="' + esc(ub.comuna_corregimiento || '') + '" onchange="isv2LoadBarrios()"><option value="">Cargando...</option></select>') +
                editRow('Barrio / Vereda', '<select id="isv2-edit-barrio" data-current="' + esc(ub.barrio || '') + '"><option value="">Cargando...</option></select>') +
                editActions('isv2SaveUbicacion()');
        }

        function cursosBody(d) {
            if (!d.matricula) {
                return row('Estado', 'Sin matrícula registrada');
            }
            var cursos = d.matricula.cursos || {};
            var defs = [
                ['tecnico', 'Curso técnico'],
                ['ingles', 'Curso de inglés'],
                ['habilidades', 'Habilidades blandas']
            ];
            var html = '';
            defs.forEach(function (def) {
                var key = def[0], label = def[1];
                var c = cursos[key] || {};
                var nombre = c.name || label;
                var code = c.code ? '<span class="isv2-curso__code">' + esc(c.code) + '</span>' : '';
                html += '<div class="isv2-curso isv2-curso--' + key + '">' +
                    '<div class="isv2-curso__info"><div class="isv2-curso__nombre">' + esc(nombre) + '</div>' + code + '</div>' +
                    '<div class="isv2-curso__nota" id="isv2-nota-' + key + '"><span class="spinner-border spinner-border-sm text-secondary"></span></div>' +
                    '</div>';
            });
            html += '<div class="isv2-promedio">' +
                '<div class="isv2-promedio__label">Promedio</div>' +
                '<div class="isv2-promedio__value" id="isv2-promedio"><span class="spinner-border spinner-border-sm text-secondary"></span></div>' +
                '<div class="isv2-promedio__sub" id="isv2-promedio-sub">Ponderado según configuración</div>' +
                '<div class="isv2-promedio__estado" id="isv2-promedio-estado"></div>' +
                '</div>';
            return html;
        }

        function applyNotas() {
            if (!notasData) return;
            ['tecnico', 'ingles', 'habilidades'].forEach(function (k) {
                var el = document.getElementById('isv2-nota-' + k);
                if (!el) return;
                var c = notasData.cursos[k];
                if (!c) { el.innerHTML = '<span class="isv2-empty">&mdash;</span>'; return; }
                if (!c.presento) { el.innerHTML = '<span class="isv2-curso__sinnota">No ha presentado la prueba</span>'; return; }
                el.innerHTML = '<b>' + esc(Number(c.nota).toFixed(2)) + '</b>';
            });
            var pel = document.getElementById('isv2-promedio');
            if (pel) {
                pel.innerHTML = (notasData.promedio === null || notasData.promedio === undefined)
                    ? '<span class="isv2-empty">&mdash;</span>'
                    : '<b>' + esc(Number(notasData.promedio).toFixed(2)) + '</b>';
            }
            var psub = document.getElementById('isv2-promedio-sub');
            if (psub && notasData.pesos) {
                var txt = 'Técnico ' + notasData.pesos.tecnico + '% · Inglés ' + notasData.pesos.ingles + '% · Habilidades ' + notasData.pesos.habilidades + '%';
                if (notasData.nota_minima !== undefined && notasData.nota_minima !== null) {
                    txt += ' · Aprueba con ' + Number(notasData.nota_minima).toFixed(2);
                }
                psub.textContent = txt;
            }
            var est = document.getElementById('isv2-promedio-estado');
            if (est) {
                if (notasData.estado === 'aprobado') est.innerHTML = badge('Aprobado', 'isv2-badge--green');
                else if (notasData.estado === 'no_aprobado') est.innerHTML = badge('No aprobado', 'isv2-badge--magenta');
                else if (notasData.estado === 'sin_completar') est.innerHTML = badge('Sin completar', 'isv2-badge--amber');
                else est.innerHTML = '';
            }
        }

        function loadNotas(number_id) {
            notasLoading = true;
            fetch('components/individualSearchV2/get_notas_cursos.php?number_id=' + encodeURIComponent(number_id))
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    notasData = (data && data.ok) ? data : { cursos: {}, promedio: null, pesos: null };
                })
                .catch(function () {
                    notasData = { cursos: {}, promedio: null, pesos: null };
                })
                .then(function () {
                    notasLoading = false;
                    applyNotas();
                });
        }

        function initUbicacionSelects() {
            var depSel = document.getElementById('isv2-edit-department');
            if (depSel) {
                fetch('components/individualSearchV2/get_departamentos.php')
                    .then(function (r) { return r.json(); })
                    .then(function (list) {
                        list = list.filter(function (o) {
                            return o.departamento && o.departamento.toUpperCase().indexOf(LIMITE_DEPARTAMENTO) !== -1;
                        });
                        var cur = depSel.getAttribute('data-current') || '';
                        depSel.innerHTML = '<option value="">Seleccione</option>' + list.map(function (o) {
                            return '<option value="' + esc(o.id_departamento) + '">' + esc(o.departamento) + '</option>';
                        }).join('');
                        depSel.value = cur;
                        if (!depSel.value && list.length) { depSel.value = list[0].id_departamento; }
                        isv2LoadMunicipios();
                    })
                    .catch(function () {});
            }

            var comSel = document.getElementById('isv2-edit-comuna');
            if (comSel) {
                fetch('components/individualSearchV2/get_comunas.php')
                    .then(function (r) { return r.json(); })
                    .then(function (list) {
                        var cur = comSel.getAttribute('data-current') || '';
                        comSel.innerHTML = '<option value="">Seleccione</option>' + list.map(function (o) {
                            return '<option value="' + esc(o.value) + '">' + esc(o.value) + '</option>';
                        }).join('');
                        comSel.value = cur;
                        isv2LoadBarrios();
                    })
                    .catch(function () {});
            }
        }

        window.isv2Edit = function (section) {
            editing = section;
            render(currentData);
        };

        window.isv2Cancel = function () {
            editing = null;
            render(currentData);
        };

        window.isv2LoadMunicipios = function () {
            var depSel = document.getElementById('isv2-edit-department');
            var sel = document.getElementById('isv2-edit-municipality');
            if (!depSel || !sel) return;
            var dep = depSel.value;
            var cur = sel.getAttribute('data-current');
            if (!dep) { sel.innerHTML = '<option value="">Seleccione</option>'; return; }
            fetch('components/individualSearchV2/get_municipios.php?department_id=' + encodeURIComponent(dep))
                .then(function (r) { return r.json(); })
                .then(function (list) {
                    list = list.filter(function (o) {
                        return o.nom_municipio && o.nom_municipio.toUpperCase().indexOf(LIMITE_MUNICIPIO) !== -1;
                    });
                    sel.innerHTML = '<option value="">Seleccione</option>' + list.map(function (o) {
                        return '<option value="' + esc(o.cod_municipio) + '">' + esc(o.nom_municipio) + '</option>';
                    }).join('');
                    if (cur) { sel.value = cur; sel.removeAttribute('data-current'); }
                    if (!sel.value && list.length) { sel.value = list[0].cod_municipio; }
                })
                .catch(function () {});
        };

        window.isv2LoadBarrios = function () {
            var comSel = document.getElementById('isv2-edit-comuna');
            var sel = document.getElementById('isv2-edit-barrio');
            if (!comSel || !sel) return;
            var cur = sel.getAttribute('data-current');
            var com = comSel.value;
            var cod = com ? com.split(' - ')[0] : '';
            if (!cod) { sel.innerHTML = '<option value="">Seleccione</option>'; return; }
            fetch('components/individualSearchV2/get_barrios.php?comuna=' + encodeURIComponent(cod))
                .then(function (r) { return r.json(); })
                .then(function (list) {
                    sel.innerHTML = '<option value="">Seleccione</option>' + list.map(function (o) {
                        return '<option value="' + esc(o.nombre) + '">' + esc(o.nombre) + '</option>';
                    }).join('');
                    if (cur) { sel.value = cur; sel.removeAttribute('data-current'); }
                })
                .catch(function () {});
        };

        function toast(icon, text) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: icon,
                    title: icon === 'success' ? 'Éxito' : 'Error',
                    text: text,
                    timer: icon === 'success' ? 2200 : undefined,
                    showConfirmButton: icon !== 'success'
                });
            } else {
                alert(text);
            }
        }

        function post(file, fd) {
            fetch('components/individualSearchV2/' + file, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res && res.ok) {
                        toast('success', res.message || 'Actualizado correctamente.');
                        editing = null;
                        buscar(currentData.personal.number_id);
                    } else {
                        toast('error', (res && res.message) || 'No se pudo actualizar.');
                    }
                })
                .catch(function () {
                    toast('error', 'Error de conexión.');
                });
        }

        window.isv2SavePersonal = function () {
            var fd = new FormData();
            fd.append('number_id', currentData.personal.number_id);
            fd.append('gender', val('isv2-edit-gender'));
            fd.append('birthdate', val('isv2-edit-birthdate'));
            post('actualizar_personal.php', fd);
        };

        window.isv2SaveContacto = function () {
            var fd = new FormData();
            fd.append('number_id', currentData.personal.number_id);
            fd.append('first_phone', val('isv2-edit-first_phone'));
            fd.append('second_phone', val('isv2-edit-second_phone'));
            fd.append('emergency_contact_name', val('isv2-edit-emergency_contact_name'));
            fd.append('emergency_contact_number', val('isv2-edit-emergency_contact_number'));
            if (document.getElementById('isv2-edit-guardian_full_name')) {
                fd.append('guardian_full_name', val('isv2-edit-guardian_full_name'));
                fd.append('guardian_document', val('isv2-edit-guardian_document'));
                fd.append('guardian_phone', val('isv2-edit-guardian_phone'));
                fd.append('guardian_email', val('isv2-edit-guardian_email'));
            }
            post('actualizar_contacto.php', fd);
        };

        window.isv2SaveUbicacion = function () {
            var fd = new FormData();
            fd.append('number_id', currentData.personal.number_id);
            fd.append('department', val('isv2-edit-department'));
            fd.append('municipality', val('isv2-edit-municipality'));
            fd.append('address', val('isv2-edit-address'));
            fd.append('residence_area', val('isv2-edit-residence_area'));
            fd.append('comuna_corregimiento', val('isv2-edit-comuna'));
            fd.append('barrio', val('isv2-edit-barrio'));
            post('actualizar_ubicacion.php', fd);
        };

        function render(d) {
            if (!d.encontrado) {
                currentData = null;
                result.innerHTML = '<div class="isv2-empty-box"><i class="bi bi-person-x"></i>No se encontró ningún estudiante con esa identificación.</div>';
                return;
            }

            currentData = d;

            var p = d.personal, c = d.contacto, ub = d.ubicacion, ac = d.academico;

            var badges = '';
            if (ac && ac.program) badges += badge(ac.program, 'isv2-badge--blue');
            if (c) {
                if (c.email_verified === 1) badges += badge('Email verificado', 'isv2-badge--green');
                else badges += badge('Email sin verificar', 'isv2-badge--amber');
            }
            if (d.matricula) {
                badges += badge(d.matricula.status === 'enrolled' ? 'Matriculado' : 'Pendiente', 'isv2-badge--magenta');
            }

            var header =
                '<div class="isv2-person">' +
                    '<div class="isv2-avatar">' + initials(p.nombre_completo) + '</div>' +
                    '<div class="isv2-person__info">' +
                        '<h3>' + esc(p.nombre_completo) + '</h3>' +
                        '<div class="isv2-person__sub">' + esc(p.typeID) + ' &middot; ' + esc(p.number_id) + '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="isv2-badges">' + badges + '</div>' +
                '<div class="isv2-divider"></div>';

            var html = '<div class="row g-3">';

            html += card('Información personal', 'bi bi-person-badge',
                header + (editing === 'personal' ? editPersonal(p) : viewPersonal(p)),
                { editable: true, section: 'personal' });

            html += card('Contacto', 'bi bi-telephone',
                editing === 'contacto' ? editContacto(c, d.acudiente) : viewContacto(c, d.acudiente),
                { editable: true, section: 'contacto' });

            html += card('Ubicación y origen', 'bi bi-geo-alt',
                editing === 'ubicacion' ? editUbicacion(ub) : viewUbicacion(ub),
                { editable: true, section: 'ubicacion' });

            var m = d.matricula;
            var matriculaBody =
                row('Programa', (m && m.program_name) ? m.program_name : ac.program) +
                row('Modalidad', ac.mode) +
                row('Fecha de inscripción', ac.fecha_inscripcion);
            if (m) {
                matriculaBody +=
                    row('Correo institucional', m.institutional_email) +
                    row('Usuario Moodle', m.username) +
                    row('Serie', m.serie) +
                    row('Fecha de matrícula', m.fecha_matricula);
            } else {
                matriculaBody += row('Estado', 'Sin matrícula registrada');
            }
            html += card('Matrícula y académico', 'bi bi-journal-check', matriculaBody);

            html += card('Cursos y notas', 'bi bi-clipboard2-check', cursosBody(d), { cardClass: 'isv2-card--cursos', colClass: 'col-12 col-lg-8' });

            html += '</div>';
            result.innerHTML = html;

            if (editing === 'ubicacion') {
                initUbicacionSelects();
            }

            applyNotas();

            if (d.matricula && !notasData && !notasLoading) {
                loadNotas(p.number_id);
            }
        }

        function buscar(q) {
            if (!q) return;
            notasData = null;
            notasLoading = false;
            editing = null;
            input.value = q;
            result.innerHTML = '<div class="isv2-loading"><span class="spinner-border spinner-border-sm me-2"></span> Buscando...</div>';
            fetch('components/individualSearchV2/individualV2.php?ajax=1&search=' + encodeURIComponent(q))
                .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                .then(render)
                .catch(function (err) {
                    console.error(err);
                    result.innerHTML = '<div class="isv2-empty-box"><i class="bi bi-exclamation-triangle"></i>Ocurrió un error al buscar.</div>';
                });
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                buscar(input.value.trim());
            });
        }
    })();
</script>
