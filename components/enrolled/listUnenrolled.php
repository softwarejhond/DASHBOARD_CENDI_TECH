<?php $prefix = 'Desmatriculados'; include __DIR__ . '/filterCards.php'; ?>

<table id="listaDesmatriculados" class="table table-hover table-bordered text-nowrap" style="width:100%">
    <thead class="thead-dark text-center">
        <tr class="text-center">
            <th>Número</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Email institucional</th>
            <th>Departamento</th>
            <th>Sede</th>
            <th>Programa</th>
            <th>Modalidad</th>
            <th>Bootcamp</th>
            <th>Fecha matrícula</th>
            <th>Fecha desmatrícula</th>
            <th>Desmatriculado por</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script>
    $(document).ready(function() {
        function empty(s) {
            if (s === null || s === undefined) return '&mdash;';
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

        var table = null;

        if ($.fn.select2) {
            $('#filterSetDesmatriculados').select2({
                placeholder: 'Buscar set de curso...',
                allowClear: true,
                width: '100%',
                minimumInputLength: 0,
                ajax: {
                    url: 'components/enrolled/getSets.php',
                    dataType: 'json',
                    delay: 250,
                    cache: true,
                    data: function(params) {
                        return { q: params.term || '', page: params.page || 1 };
                    },
                    processResults: function(data, params) {
                        return {
                            results: data.results || [],
                            pagination: { more: data.more || false }
                        };
                    }
                }
            });
        }

        if (!$.fn.DataTable) return;

        if ($.fn.DataTable.isDataTable('#listaDesmatriculados')) {
            $('#listaDesmatriculados').DataTable().destroy();
        }

        table = $('#listaDesmatriculados').DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            order: [
                [10, 'desc']
            ],
            layout: {
                topStart: 'pageLength',
                topEnd: 'search',
                bottomStart: 'info',
                bottomEnd: 'paging'
            },
            ajax: {
                url: 'components/enrolled/listUnenrolled_ajax.php',
                type: 'POST',
                data: function(d) {
                    d.set_id = $('#filterSetDesmatriculados').val();
                    d.number_id = $('#searchNumberDesmatriculados').val();
                }
            },
            language: {
                url: 'controller/datatable_esp.json',
                emptyTable: 'Seleccione un filtro o busque por cédula para cargar los datos'
            },
            columns: [
                { data: 'number_id', render: function(d) { return empty(d); } },
                { data: 'full_name', render: function(d) { return empty(d); } },
                { data: 'email', render: function(d) { return empty(d); } },
                { data: 'institutional_email', render: function(d) { return empty(d); } },
                { data: 'department', render: function(d) { return empty(d); } },
                { data: 'headquarters', render: function(d) { return empty(d); } },
                { data: 'program', render: function(d) { return empty(d); } },
                { data: 'mode', render: function(d) { return empty(d); } },
                { data: 'bootcamp_name', render: function(d) { return empty(d); } },
                { data: 'enrollment_date', render: function(d) { return empty(d); } },
                { data: 'unenrollment_date', render: function(d) { return empty(d); } },
                { data: 'unenrolled_by', render: function(d) { return empty(d); } }
            ]
        });

        $('#filterSetDesmatriculados').on('change', function() { table.ajax.reload(); });
        $('#btnSearchDesmatriculados').on('click', function() { table.ajax.reload(); });
        $('#searchNumberDesmatriculados').on('keypress', function(e) {
            if (e.which === 13) { table.ajax.reload(); }
        });
    });
</script>
