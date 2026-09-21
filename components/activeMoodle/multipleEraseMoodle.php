<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php $prefix = 'Desmatricular'; include __DIR__ . '/../enrolled/filterCards.php'; ?>

<div class="row mb-3">
    <div class="col-12">
        <button class="btn bg-magenta-dark text-white" type="button" data-bs-toggle="offcanvas" data-bs-target="#selectedUsersList">
            <i class="bi bi-exclamation-diamond-fill"></i> Ver Seleccionados (<span id="selectedCount">0</span>)
        </button>
    </div>
</div>

<div class="table-responsive">
    <table id="listaInscritos" class="table table-hover table-bordered text-nowrap" style="width:100%">
        <thead class="thead-dark text-center">
            <tr class="text-center">
                <th class="text-center">
                    <input type="checkbox" id="selectAllPage" class="form-check-input"
                           style="width: 22px; height: 22px; cursor: pointer;" title="Seleccionar página">
                </th>
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
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Modal de confirmación para desmatriculación múltiple -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmDeleteModalLabel">⚠️ Confirmación de Desmatriculación Masiva</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="alert alert-danger">
                        <strong>¡ADVERTENCIA!</strong><br>
                        Está a punto de desmatricular a <span id="selectedUsersCount">0</span> usuarios
                    </div>

                    <div class="card shadow-lg p-3 mb-3">
                        <p class="mb-1">Para confirmar, ingrese el siguiente código de seguridad:</p>
                        <div class="input-group mb-3">
                            <input type="text" id="securityCodeDisplay" class="form-control text-center"
                                   readonly
                                   style="font-family: monospace; letter-spacing: 3px; font-weight: bold;">
                            <button class="btn btn-outline-secondary" type="button" id="copyCodeBtn">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                        <input type="text"
                               id="securityCodeInput"
                               class="form-control text-center"
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
                <button type="button" class="btn btn-danger" id="confirmDesmatriculacionBtn">Confirmar Desmatriculación</button>
            </div>
        </div>
    </div>
</div>

<!-- Panel lateral para usuarios seleccionados -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="selectedUsersList" aria-labelledby="selectedUsersListLabel">
    <div class="offcanvas-header bg-magenta-dark text-white">
        <h5 class="offcanvas-title" id="selectedUsersListLabel">
            <i class="bi bi-person-check"></i> Beneficiarios seleccionados (<span id="offcanvasSelectedCount">0</span>)
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="m-3">
            <button id="btnDesmatricularSeleccionados" class="btn btn-danger w-100">
                <i class="bi bi-trash"></i> Desmatricular Seleccionados
            </button>
        </div>
        <div id="selectedUsersContainer">
            <!-- Aquí se mostrarán las tarjetas de usuarios seleccionados -->
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var selectedUsers = new Map();
        var table = null;
        var securityCode = '';

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

        // -------------------------------------------------------------
        // Selector de set de curso (carga bajo demanda)
        // -------------------------------------------------------------
        if ($.fn.select2) {
            $('#filterSetDesmatricular').select2({
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

        // -------------------------------------------------------------
        // Tabla de inscritos (server-side, carga bajo demanda)
        // -------------------------------------------------------------
        if ($.fn.DataTable) {
            if ($.fn.DataTable.isDataTable('#listaInscritos')) {
                $('#listaInscritos').DataTable().destroy();
            }

            table = $('#listaInscritos').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],
                order: [
                    [2, 'asc']
                ],
                layout: {
                    topStart: 'pageLength',
                    topEnd: 'search',
                    bottomStart: 'info',
                    bottomEnd: 'paging'
                },
                ajax: {
                    url: 'components/activeMoodle/listEraseMoodle_ajax.php',
                    type: 'POST',
                    data: function(d) {
                        d.set_id = $('#filterSetDesmatricular').val();
                        d.number_id = $('#searchNumberDesmatricular').val();
                    }
                },
                language: {
                    url: 'controller/datatable_esp.json',
                    emptyTable: 'Seleccione un filtro o busque por cédula para cargar los datos'
                },
                createdRow: function(row, data) {
                    row.dataset.user = JSON.stringify({
                        number_id: data.number_id,
                        full_name: data.full_name,
                        institutional_email: data.institutional_email
                    });
                },
                drawCallback: function() {
                    syncCheckboxes();
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function() {
                            return '<input type="checkbox" class="form-check-input usuario-checkbox" ' +
                                'style="width: 25px; height: 25px; appearance: none; background-color: white; ' +
                                'border: 2px solid #ec008c; cursor: pointer; position: relative;">';
                        }
                    },
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
                    { data: 'created_at', render: function(d) { return empty(d); } }
                ]
            });
        }

        $('#filterSetDesmatricular').on('change', function() { if (table) table.ajax.reload(); });
        $('#btnSearchDesmatricular').on('click', function() { if (table) table.ajax.reload(); });
        $('#searchNumberDesmatricular').on('keypress', function(e) {
            if (e.which === 13) { if (table) table.ajax.reload(); }
        });

        // -------------------------------------------------------------
        // Selección múltiple (persiste entre páginas y filtros)
        // -------------------------------------------------------------
        function syncCheckboxes() {
            $('#listaInscritos tbody tr').each(function() {
                var cb = $(this).find('.usuario-checkbox')[0];
                if (!cb) return;
                var data;
                try { data = JSON.parse(this.dataset.user); } catch (e) { return; }
                var checked = selectedUsers.has(data.number_id);
                cb.checked = checked;
                cb.style.backgroundColor = checked ? '#ec008c' : 'white';
            });
            updateSelectAllState();
        }

        function updateSelectAllState() {
            var boxes = $('#listaInscritos tbody .usuario-checkbox');
            var allChecked = boxes.length > 0 && boxes.filter(':checked').length === boxes.length;
            $('#selectAllPage').prop('checked', allChecked);
        }

        function updateSelectedCount() {
            var count = selectedUsers.size;
            $('#selectedCount, #offcanvasSelectedCount').text(count);
            $('#btnDesmatricularSeleccionados').prop('disabled', count === 0);
        }

        function updateSelectedUsersList() {
            var container = document.getElementById('selectedUsersContainer');
            if (!container) return;

            container.innerHTML = '';
            selectedUsers.forEach(function(userData, numberId) {
                var card = document.createElement('div');
                card.className = 'card mb-2';
                card.innerHTML = `
                    <div class="card-body">
                        <h6 class="card-title text-center mb-2"><b>${esc(userData.full_name)}</b></h6>
                        <div class="text-center">
                            <small class="d-block text-muted mb-1">ID: ${esc(numberId)}</small>
                            <small class="d-block text-muted mb-2">${esc(userData.institutional_email)}</small>
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                <span>En espera para desmatricular</span>
                            </div>
                            <button class="btn btn-outline-danger btn-sm w-100 remove-selection" data-id="${esc(numberId)}">
                                <i class="bi bi-x-circle"></i> Eliminar selección
                            </button>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });

            container.querySelectorAll('.remove-selection').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    removeSelectedUser(this.dataset.id);
                });
            });

            updateSelectedCount();
        }

        function removeSelectedUser(numberId) {
            selectedUsers.delete(numberId);
            $('#listaInscritos tbody tr').each(function() {
                var cb = $(this).find('.usuario-checkbox')[0];
                if (!cb) return;
                var data;
                try { data = JSON.parse(this.dataset.user); } catch (e) { return; }
                if (data.number_id === numberId) {
                    cb.checked = false;
                    cb.style.backgroundColor = 'white';
                }
            });
            updateSelectAllState();
            updateSelectedUsersList();
        }

        $(document).on('change', '.usuario-checkbox', function() {
            var row = this.closest('tr');
            if (!row || !row.dataset.user) return;

            var userData = JSON.parse(row.dataset.user);
            if (this.checked) {
                selectedUsers.set(userData.number_id, userData);
            } else {
                selectedUsers.delete(userData.number_id);
            }

            this.style.backgroundColor = this.checked ? '#ec008c' : 'white';
            updateSelectAllState();
            updateSelectedUsersList();
        });

        $('#selectAllPage').on('change', function() {
            var check = this.checked;
            $('#listaInscritos tbody tr').each(function() {
                var cb = $(this).find('.usuario-checkbox')[0];
                if (cb && cb.checked !== check) {
                    $(cb).prop('checked', check).trigger('change');
                }
            });
        });

        // -------------------------------------------------------------
        // Confirmación con código de seguridad
        // -------------------------------------------------------------
        $('#btnDesmatricularSeleccionados').on('click', function() {
            if (selectedUsers.size === 0) {
                Swal.fire('Error', 'No hay usuarios seleccionados', 'error');
                return;
            }

            var chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=[]{}|;:,.<>?";
            securityCode = '';
            for (var i = 0; i < 8; i++) {
                securityCode += chars.charAt(Math.floor(Math.random() * chars.length));
            }

            $('#selectedUsersCount').text(selectedUsers.size);
            $('#securityCodeDisplay').val(securityCode);
            $('#securityCodeInput').val('').removeClass('is-invalid');
            $('#codeErrorMsg').remove();

            var modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            modal.show();

            setTimeout(function() {
                document.getElementById('securityCodeInput').focus();
            }, 500);
        });

        $('#copyCodeBtn').on('click', function() {
            var btn = $(this);
            navigator.clipboard.writeText($('#securityCodeDisplay').val()).then(function() {
                btn.html('<i class="bi bi-check2"></i>');
                setTimeout(function() {
                    btn.html('<i class="bi bi-clipboard"></i>');
                }, 1500);
                document.getElementById('securityCodeInput').focus();
            });
        });

        $('#securityCodeInput').on('input', function() {
            $(this).removeClass('is-invalid');
            $('#codeErrorMsg').remove();
        });

        function showCodeError(message) {
            var codeInput = document.getElementById('securityCodeInput');
            $(codeInput).addClass('is-invalid');
            var errorMsg = document.getElementById('codeErrorMsg');
            if (!errorMsg) {
                errorMsg = document.createElement('div');
                errorMsg.id = 'codeErrorMsg';
                errorMsg.className = 'invalid-feedback';
                codeInput.parentNode.appendChild(errorMsg);
            }
            errorMsg.textContent = message;
        }

        $('#confirmDesmatriculacionBtn').on('click', function() {
            var inputCode = $('#securityCodeInput').val();

            if (!inputCode) {
                showCodeError('Debe ingresar el código de seguridad');
                return;
            }
            if (inputCode !== securityCode) {
                showCodeError('El código ingresado no coincide');
                return;
            }

            var modal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
            modal.hide();

            processMultipleDelete();
        });

        // -------------------------------------------------------------
        // Desmatriculación masiva
        // -------------------------------------------------------------
        function processMultipleDelete() {
            var totalUsers = selectedUsers.size;
            var processed = 0;
            var successful = 0;
            var errors = [];

            Swal.fire({
                title: 'Procesando eliminaciones',
                html: `Progreso: 0/${totalUsers}`,
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            var queue = Array.from(selectedUsers.entries());

            function next() {
                if (queue.length === 0) {
                    finalize();
                    return;
                }

                var entry = queue.shift();
                var numberId = entry[0];
                var userData = entry[1];

                fetch('components/activeMoodle/deleteMatricula.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            number_id: numberId,
                            isMultiple: true
                        })
                    })
                    .then(function(response) {
                        if (response.status === 401) {
                            throw new Error('Sesión expirada o no autorizada');
                        }
                        return response.json();
                    })
                    .then(function(result) {
                        processed++;
                        if (result.success) {
                            successful++;
                            selectedUsers.delete(numberId);
                        } else {
                            errors.push('Error con ' + esc(userData.full_name) + ': ' + esc(result.message));
                        }
                        Swal.update({
                            html: `Progreso: ${processed}/${totalUsers}<br>Exitosos: ${successful}<br>Errores: ${errors.length}`
                        });
                        next();
                    })
                    .catch(function(error) {
                        processed++;
                        errors.push('Error con ' + esc(userData.full_name) + ': ' + esc(error.message));
                        Swal.update({
                            html: `Progreso: ${processed}/${totalUsers}<br>Exitosos: ${successful}<br>Errores: ${errors.length}`
                        });
                        next();
                    });
            }

            function finalize() {
                var resultMessage = 'Proceso completado:<br>Total procesados: ' + processed +
                    '<br>Exitosos: ' + successful +
                    '<br>Errores: ' + errors.length;

                if (errors.length > 0) {
                    resultMessage += '<br><br>Errores encontrados:<br>' + errors.join('<br>');
                }

                Swal.fire({
                    title: 'Proceso completado',
                    html: resultMessage,
                    icon: errors.length === 0 ? 'success' : 'warning',
                    confirmButtonText: 'Aceptar'
                }).then(function() {
                    var offcanvasEl = document.getElementById('selectedUsersList');
                    var offcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                    if (offcanvas) offcanvas.hide();

                    updateSelectedUsersList();
                    updateSelectAllState();
                    if (table) table.ajax.reload(null, false);
                });
            }

            next();
        }

        // -------------------------------------------------------------
        // Alerta de advertencia al cargar la página
        // -------------------------------------------------------------
        Swal.fire({
            title: '⚠️ ¡ADVERTENCIA IMPORTANTE! ⚠️',
            html: `
                <div class="text-center">
                    <div class="alert alert-danger mb-3">
                        <i class="bi bi-exclamation-triangle-fill fs-1 d-block mb-2"></i>
                        <strong class="fs-5">ZONA DE ALTO RIESGO</strong>
                    </div>
                    <p class="mb-3">Está a punto de acceder a una sección donde podrá realizar eliminaciones masivas de matrículas en la plataforma.</p>
                    <div class="alert alert-warning">
                        <strong>Por favor, tenga en cuenta:</strong>
                        <ul class="text-start mt-2 mb-0">
                            <li>Esta acción es <strong class="text-danger">COMPLETAMENTE IRREVERSIBLE</strong></li>
                            <li>Verifique <strong>CUIDADOSAMENTE</strong> cada selección</li>
                            <li>Se requiere <strong>MÁXIMA ATENCIÓN</strong> en este proceso</li>
                        </ul>
                    </div>
                </div>`,
            icon: 'warning',
            confirmButtonText: 'Entiendo los riesgos',
            confirmButtonColor: '#dc3545',
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: {
                popup: 'swal2-warning-custom',
                title: 'fs-4 text-danger',
                htmlContainer: 'text-center'
            }
        }).then(function(result) {
            if (!result.isConfirmed) {
                window.location.href = 'main.php';
            }
        });

        var style = document.createElement('style');
        style.textContent = `
            .swal2-warning-custom {
                border: 3px solid #dc3545 !important;
                border-radius: 10px !important;
            }
            .swal2-warning-custom .swal2-title {
                color: #dc3545 !important;
                font-weight: bold !important;
            }
            .swal2-warning-custom .swal2-icon {
                border-color: #dc3545 !important;
                color: #dc3545 !important;
            }
        `;
        document.head.appendChild(style);

        updateSelectedCount();
    });
</script>
