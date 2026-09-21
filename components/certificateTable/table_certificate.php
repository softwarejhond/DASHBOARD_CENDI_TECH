<?php $prefix = 'Certificados'; include __DIR__ . '/../enrolled/filterCards.php'; ?>

<style>
    #listaCertificados td,
    #listaCertificados th {
        white-space: nowrap;
    }
</style>

<div class="table-responsive">
    <table id="listaCertificados" class="table table-hover table-bordered text-nowrap" style="width:100%">
        <thead class="thead-dark text-center">
            <tr class="text-center">
                <th>Número de Identificación</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Set</th>
                <th>Estado</th>
                <th>Fecha de envío</th>
                <th>Token</th>
                <th>Diploma</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

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

        var table = null;

        if ($.fn.select2) {
            $('#filterSetCertificados').select2({
                placeholder: 'Buscar set de curso...',
                allowClear: true,
                width: '100%',
                minimumInputLength: 0,
                ajax: {
                    url: 'components/certificateTable/getSets.php',
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

        if ($.fn.DataTable) {
            if ($.fn.DataTable.isDataTable('#listaCertificados')) {
                $('#listaCertificados').DataTable().destroy();
            }

            table = $('#listaCertificados').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],
                order: [
                    [5, 'desc']
                ],
                ajax: {
                    url: 'components/certificateTable/listCertificates_ajax.php',
                    type: 'POST',
                    data: function(d) {
                        d.set_id = $('#filterSetCertificados').val();
                        d.number_id = $('#searchNumberCertificados').val();
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
                    { data: 'set_nombre', render: function(d) { return empty(d); } },
                    {
                        data: 'estado',
                        render: function(d) {
                            if (!d) return '<span class="text-muted">&mdash;</span>';
                            var map = {
                                generado: { t: 'Generado', c: 'secondary' },
                                enviado: { t: 'Enviado', c: 'success' },
                                error: { t: 'Error', c: 'danger' }
                            };
                            var m = map[d] || { t: d, c: 'secondary' };
                            return '<span class="badge text-bg-' + m.c + '">' + esc(m.t) + '</span>';
                        }
                    },
                    { data: 'fecha_envio', render: function(d) { return empty(d); } },
                    { data: 'token', render: function(d) { return empty(d); } },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            if (!row.token) {
                                return '<span class="text-muted">&mdash;</span>';
                            }
                            return '<a class="btn btn-primary btn-sm" target="_blank" rel="noopener" ' +
                                'href="components/certificateTable/download_diploma.php?token=' + encodeURIComponent(row.token) + '">' +
                                '<i class="bi bi-file-earmark-pdf"></i> Ver diploma</a>';
                        }
                    }
                ]
            });
        }

        $('#filterSetCertificados').on('change', function() { if (table) table.ajax.reload(); });
        $('#btnSearchCertificados').on('click', function() { if (table) table.ajax.reload(); });
        $('#searchNumberCertificados').on('keypress', function(e) {
            if (e.which === 13) { if (table) table.ajax.reload(); }
        });
    });
</script>
