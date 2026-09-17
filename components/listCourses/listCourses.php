<?php
$rol = $infoUsuario['rol'];

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

// Cursos técnicos que se pueden crear (tienen plantilla en Moodle)
$crearTecnicos = [
    'IA'    => 'Inteligencia Artificial',
    'PDS'   => 'Programación y Desarrollo',
    'DT'    => 'Análisis de Datos',
    'CIBER' => 'Ciberseguridad',
    'CN'    => 'Computación en la Nube',
    'BLO'   => 'Blockchain',
    'RA'    => 'Robótica y Automatización',
    'IOT'   => 'Internet de las Cosas',
];

// Obtener los sets y series disponibles para los filtros
$sets   = [];
$series = [];
$resSets = $conn->query("SELECT codigo_tecnico, serie FROM sets_cursos ORDER BY codigo_tecnico ASC, CAST(serie AS UNSIGNED) ASC");
if ($resSets && $resSets->num_rows > 0) {
    while ($s = $resSets->fetch_assoc()) {
        $code = $s['codigo_tecnico'];
        $serie = $s['serie'];
        $key = $code . '|' . $serie;
        if (!empty($code) && !empty($serie) && !isset($sets[$key])) {
            $sets[$key] = [
                'code'   => $code,
                'serie'  => $serie,
                'nombre' => isset($tiposTecnicos[$code]) ? $tiposTecnicos[$code] : $code,
            ];
        }
        if ($serie !== null && $serie !== '' && !in_array($serie, $series)) {
            $series[] = $serie;
        }
    }
}
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .filter-card .card-body {
        display: block;
    }
    .filter-card .select2-container {
        width: 100% !important;
    }
</style>

<div class="d-flex justify-content-end mb-3">
    <button type="button" class="btn bg-magenta-dark" data-bs-toggle="modal" data-bs-target="#createCourseModal">
        <i class="bi bi-plus-circle"></i> Crear curso
    </button>
</div>

<!-- Modal para crear un nuevo set de cursos -->
<div class="modal fade" id="createCourseModal" tabindex="-1" aria-labelledby="createCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-magenta-dark text-white">
                <h5 class="modal-title" id="createCourseModalLabel"><i class="bi bi-plus-circle"></i> Crear curso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <div class="mb-3">
                    <label for="createCodigo" class="form-label">Curso técnico a crear</label>
                    <select id="createCodigo" class="form-select">
                        <option value="">Seleccione un curso técnico</option>
                        <?php foreach ($crearTecnicos as $code => $name): ?>
                            <option value="<?= htmlspecialchars($code) ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i>
                    Se creará el set completo (curso técnico + Inglés + Habilidades blandas),
                    respetando la serie y codificación automáticas.
                </div>

                <div id="createPreview" class="mt-3 d-none">
                    <h6 class="text-muted mb-2">Vista previa del set (serie <span id="prevSerie"></span>)</h6>
                    <ul class="list-group list-group-flush" id="createPreviewList"></ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn bg-magenta-dark" id="btnCrearCurso">Crear</button>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4 g-3">
    <!-- Filtro por set de curso -->
    <div class="col-12 col-md-4">
        <div class="card h-100 filter-card">
            <div class="card-body">
                <h6 class="card-title mb-3 text-indigo-dark">
                    <i class="bi bi-collection"></i> Filtrar por set de curso
                </h6>
                <select id="filterSet" class="form-select">
                    <option value="">Todos los sets</option>
                    <?php foreach ($sets as $key => $set): ?>
                        <option value="<?= htmlspecialchars($key) ?>">
                            <?= htmlspecialchars($set['code'] . '-' . $set['serie'] . ' - ' . $set['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Filtro por tipo de curso -->
    <div class="col-12 col-md-4">
        <div class="card h-100 filter-card">
            <div class="card-body">
                <h6 class="card-title mb-3 text-indigo-dark">
                    <i class="bi bi-tags"></i> Filtrar por tipo de curso
                </h6>
                <select id="filterTipo" class="form-select">
                    <option value="">Todos los tipos</option>
                    <?php foreach ($tiposTecnicos as $code => $name): ?>
                        <option value="<?= htmlspecialchars($code) ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                    <option value="ingles">Inglés</option>
                    <option value="habilidades">Habilidades blandas</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Filtro por serie -->
    <div class="col-12 col-md-4">
        <div class="card h-100 filter-card">
            <div class="card-body">
                <h6 class="card-title mb-3 text-indigo-dark">
                    <i class="bi bi-list-ol"></i> Filtrar por serie de curso
                </h6>
                <select id="filterSerie" class="form-select">
                    <option value="">Todas las series</option>
                    <?php foreach ($series as $serie): ?>
                        <option value="<?= htmlspecialchars($serie) ?>">Serie <?= htmlspecialchars($serie) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<table id="listaCursos" class="table table-hover table-bordered text-nowrap" style="width:100%">
    <thead class="thead-dark text-center">
        <tr class="text-center">
            <th>ID Moodle</th>
            <th>Código</th>
            <th>Nombre</th>
            <th>Tipo</th>
            <th>Set</th>
            <th>Serie</th>
            <th>Matriculados</th>
            <th>Fecha creación curso</th>
            <th>Fecha creación set</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        function esc(s) {
            if (s === null || s === undefined) return '';
            return String(s).replace(/[&<>"']/g, function(c) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c];
            });
        }

        function empty(s) {
            return (s === null || s === undefined || s === '') ? '&mdash;' : esc(s);
        }

        if (!$.fn.DataTable) return;

        if ($.fn.DataTable.isDataTable('#listaCursos')) {
            $('#listaCursos').DataTable().destroy();
        }

        // Select2 con buscador para el filtro de sets (extenso)
        if ($.fn.select2) {
            $('#filterSet').select2({
                placeholder: 'Buscar set de curso...',
                allowClear: true,
                width: '100%'
            });
        }

        var table = $('#listaCursos').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            order: [
                [0, 'asc']
            ],
            ajax: {
                url: 'components/listCourses/listCourses_ajax.php',
                type: 'POST',
                data: function(d) {
                    d.set = $('#filterSet').val();
                    d.tipo = $('#filterTipo').val();
                    d.serie = $('#filterSerie').val();
                }
            },
            language: {
                url: 'controller/datatable_esp.json'
            },
            columns: [
                { data: 'course_id', render: function(d) { return '<span class="font-monospace">' + empty(d) + '</span>'; } },
                { data: 'course_code', render: function(d) { return '<span class="font-monospace">' + empty(d) + '</span>'; } },
                { data: 'course_name', render: function(d) { return empty(d); } },
                {
                    data: 'tipo',
                    render: function(d) {
                        if (!d) return '&mdash;';
                        var cls = 'bg-indigo-dark';
                        if (d === 'Inglés') cls = 'bg-teal-dark';
                        else if (d === 'Habilidades blandas') cls = 'bg-amber-dark';
                        return '<span class="badge ' + cls + '">' + esc(d) + '</span>';
                    }
                },
                { data: 'codigo_tecnico', render: function(d) { return '<span class="font-monospace">' + empty(d) + '</span>'; } },
                { data: 'serie', render: function(d) { return empty(d); } },
                {
                    data: 'matriculados',
                    render: function(d) {
                        var n = parseInt(d, 10) || 0;
                        var cls = n > 0 ? 'bg-teal-dark' : 'bg-gray-dark';
                        return '<span class="badge ' + cls + '">' + n + '</span>';
                    }
                },
                { data: 'curso_created_at', render: function(d) { return empty(d); } },
                { data: 'set_created_at', render: function(d) { return empty(d); } }
            ]
        });

        $('#filterSet').on('change', function() { table.ajax.reload(); });
        $('#filterTipo').on('change', function() { table.ajax.reload(); });
        $('#filterSerie').on('change', function() { table.ajax.reload(); });

        // Crear un nuevo set de cursos
        $('#createCourseModal').on('show.bs.modal', function() {
            $('#createCodigo').val('');
            $('#createPreview').addClass('d-none');
        });

        // Vista previa al seleccionar un curso técnico
        $('#createCodigo').on('change', function() {
            var codigo = $(this).val();
            var $preview = $('#createPreview');
            var $list = $('#createPreviewList');

            if (!codigo) {
                $preview.addClass('d-none');
                return;
            }

            $preview.removeClass('d-none');
            $list.html('<li class="list-group-item text-muted"><span class="spinner-border spinner-border-sm me-2"></span>Calculando serie...</li>');

            $.ajax({
                url: 'components/listCourses/createSet.php',
                type: 'POST',
                data: { codigo: codigo, preview: '1' },
                dataType: 'json',
                success: function(res) {
                    if (res && res.ok) {
                        $('#prevSerie').text(res.serie);
                        var html = '';
                        res.cursos.forEach(function(c) {
                            html += '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                                '<span>' + esc(c.tipo) + ': ' + esc(c.nombre) + '</span>' +
                                '<span class="badge bg-indigo-dark font-monospace">' + esc(c.codigo) + '</span>' +
                                '</li>';
                        });
                        $list.html(html);
                    } else {
                        $list.html('<li class="list-group-item text-danger">' + esc(res && res.mensaje ? res.mensaje : 'No se pudo calcular la serie.') + '</li>');
                    }
                },
                error: function() {
                    $list.html('<li class="list-group-item text-danger">Error al calcular la serie.</li>');
                }
            });
        });

        $('#btnCrearCurso').on('click', function() {
            var codigo = $('#createCodigo').val();
            if (!codigo) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Seleccione un curso',
                    text: 'Debe elegir un curso técnico para crear.'
                });
                return;
            }
            var nombre = $('#createCodigo option:selected').text();

            Swal.fire({
                title: '¿Crear set de ' + nombre + '?',
                html: 'Se crearán 3 cursos en Moodle (técnico, Inglés y Habilidades blandas) y se registrará el set con la siguiente serie disponible.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, crear',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#ec008c'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Creando cursos...',
                    html: 'Duplicando plantillas en Moodle. Esto puede tardar unos segundos.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: function() { Swal.showLoading(); }
                });

                $.ajax({
                    url: 'components/listCourses/createSet.php',
                    type: 'POST',
                    data: { codigo: codigo },
                    dataType: 'json',
                    success: function(res) {
                        if (res && res.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Set creado!',
                                text: res.mensaje,
                                confirmButtonColor: '#30336b'
                            }).then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: (res && res.mensaje) ? res.mensaje : 'No se pudo crear el set.'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error en la conexión con el servidor.'
                        });
                    }
                });
            });
        });
    });
</script>
