<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php $prefix = 'Matriculados'; include __DIR__ . '/filterCards.php'; ?>

<table id="listaMatriculados" class="table table-hover table-bordered text-nowrap" style="width:100%">
    <thead class="thead-dark text-center">
        <tr class="text-center">
            <th>Tipo ID</th>
            <th>Número</th>
            <th>Nombre</th>
            <th>Email personal</th>
            <th>Email institucional</th>
            <th>Usuario Moodle</th>
            <th>Programa</th>
            <th>Estado</th>
            <th>Curso técnico</th>
            <th>Fecha de matrícula</th>
            <th>Desmatricular</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<!-- Modal de confirmación de desmatriculación -->
<div class="modal fade" id="confirmUnenrollModal" tabindex="-1" aria-labelledby="confirmUnenrollModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmUnenrollModalLabel">Confirmación de Desmatriculación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="mb-3">
                        <h5>Nombre: <strong id="unenrollUserName"></strong></h5>
                        <h6>Número ID: <strong id="unenrollUserId"></strong></h6>
                    </div>
                    <div class="alert alert-danger">
                        <strong>¡ADVERTENCIA!</strong><br>
                        Está a punto de desmatricular a este usuario
                    </div>
                    <div class="card shadow-lg p-3 mb-3">
                        <p class="mb-1">Para confirmar, ingrese el siguiente código de seguridad:</p>
                        <div class="input-group mb-3">
                            <input type="text" id="unenrollSecurityCodeDisplay" class="form-control text-center" readonly
                                   style="font-family: monospace; letter-spacing: 3px; font-weight: bold;">
                            <button class="btn btn-outline-secondary" type="button" id="unenrollCopyCodeBtn">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                        <input type="text" id="unenrollSecurityCodeInput" class="form-control text-center"
                               placeholder="Ingrese el código aquí"
                               style="font-family: monospace; letter-spacing: 2px;">
                    </div>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Esta acción es irreversible
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmUnenrollBtn">Confirmar Desmatriculación</button>
            </div>
        </div>
    </div>
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
            $('#filterSetMatriculados').select2({
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

        if ($.fn.DataTable) {
            if ($.fn.DataTable.isDataTable('#listaMatriculados')) {
                $('#listaMatriculados').DataTable().destroy();
            }

            table = $('#listaMatriculados').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],
                order: [
                    [1, 'asc']
                ],
                layout: {
                    topStart: 'pageLength',
                    topEnd: 'search',
                    bottomStart: 'info',
                    bottomEnd: 'paging'
                },
                ajax: {
                    url: 'components/enrolled/listEnrolled_ajax.php',
                    type: 'POST',
                    data: function(d) {
                        d.set_id = $('#filterSetMatriculados').val();
                        d.number_id = $('#searchNumberMatriculados').val();
                    }
                },
                language: {
                    url: 'controller/datatable_esp.json',
                    emptyTable: 'Seleccione un filtro o busque por cédula para cargar los datos'
                },
                columns: [
                    { data: 'type_id', render: function(d) { return empty(d); } },
                    { data: 'number_id', render: function(d) { return empty(d); } },
                    { data: 'full_name', render: function(d) { return empty(d); } },
                    { data: 'email', render: function(d) { return empty(d); } },
                    { data: 'institutional_email', render: function(d) { return empty(d); } },
                    { data: 'username', render: function(d) { return empty(d); } },
                    { data: 'program_name', render: function(d) { return empty(d); } },
                    {
                        data: 'status',
                        render: function(d) {
                            if (!d) return '<span class="text-muted">&mdash;</span>';
                            var map = {
                                enrolled: { t: 'Matriculado', c: 'success' },
                                pending: { t: 'Pendiente', c: 'warning' }
                            };
                            var m = map[d] || { t: d, c: 'secondary' };
                            return '<span class="badge text-bg-' + m.c + '">' + esc(m.t) + '</span>';
                        }
                    },
                    { data: 'tecnico_name', render: function(d) { return empty(d); } },
                    { data: 'created_at', render: function(d) { return empty(d); } },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return '<button class="btn btn-danger btn-sm btn-desmatricular" ' +
                                'data-id="' + esc(row.number_id) + '" data-name="' + esc(row.full_name) + '">' +
                                '<i class="bi bi-trash"></i> Desmatricular</button>';
                        }
                    }
                ]
            });
        }

        $('#filterSetMatriculados').on('change', function() { if (table) table.ajax.reload(); });
        $('#btnSearchMatriculados').on('click', function() { if (table) table.ajax.reload(); });
        $('#searchNumberMatriculados').on('keypress', function(e) {
            if (e.which === 13) { if (table) table.ajax.reload(); }
        });

        // ---- Lógica de desmatriculación con código de seguridad ----
        let currentUserId = '';
        let securityCode = '';

        $(document).on('click', '.btn-desmatricular', function() {
            currentUserId = $(this).data('id');
            const userName = $(this).data('name');

            $('#unenrollUserId').text(currentUserId);
            $('#unenrollUserName').text(userName);

            const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=[]{}|;:,.<>?";
            securityCode = '';
            for (let i = 0; i < 8; i++) {
                securityCode += chars.charAt(Math.floor(Math.random() * chars.length));
            }

            $('#unenrollSecurityCodeDisplay').val(securityCode);
            $('#unenrollSecurityCodeInput').val('');
            $('#unenrollSecurityCodeInput').removeClass('is-invalid');
            $('#unenrollCodeErrorMsg').remove();

            const modal = new bootstrap.Modal(document.getElementById('confirmUnenrollModal'));
            modal.show();

            setTimeout(() => {
                document.getElementById('unenrollSecurityCodeInput').focus();
            }, 500);
        });

        $('#unenrollCopyCodeBtn').on('click', function() {
            const codeDisplay = document.getElementById('unenrollSecurityCodeDisplay');
            navigator.clipboard.writeText(codeDisplay.value).then(() => {
                const btn = $(this);
                btn.html('<i class="bi bi-check2"></i>');
                setTimeout(() => {
                    btn.html('<i class="bi bi-clipboard"></i>');
                }, 1500);
                document.getElementById('unenrollSecurityCodeInput').focus();
            });
        });

        $('#unenrollSecurityCodeInput').on('input', function() {
            $(this).removeClass('is-invalid');
            $('#unenrollCodeErrorMsg').remove();
        });

        $('#confirmUnenrollBtn').on('click', function() {
            const inputCode = $('#unenrollSecurityCodeInput').val();

            if (!inputCode) {
                showCodeError('Debe ingresar el código de seguridad');
                return;
            }

            if (inputCode !== securityCode) {
                showCodeError('El código ingresado no coincide');
                return;
            }

            const modal = bootstrap.Modal.getInstance(document.getElementById('confirmUnenrollModal'));
            modal.hide();

            Swal.fire({
                title: 'Procesando desmatriculación',
                text: 'Por favor espere...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('components/activeMoodle/deleteMatricula.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    number_id: currentUserId,
                    isMultiple: false
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Desmatriculado',
                        text: 'La matrícula ha sido eliminada correctamente.',
                        allowOutsideClick: false
                    }).then(() => {
                        if (table) table.ajax.reload(null, false);
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message || 'Ocurrió un error al desmatricular.'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de conexión: ' + err.message
                });
            });
        });

        function showCodeError(message) {
            const codeInput = document.getElementById('unenrollSecurityCodeInput');
            codeInput.classList.add('is-invalid');
            let errorMsg = document.getElementById('unenrollCodeErrorMsg');
            if (!errorMsg) {
                errorMsg = document.createElement('div');
                errorMsg.id = 'unenrollCodeErrorMsg';
                errorMsg.className = 'invalid-feedback';
                codeInput.parentNode.appendChild(errorMsg);
            }
            errorMsg.textContent = message;
        }
    });
</script>
