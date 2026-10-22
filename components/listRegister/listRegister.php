<?php
$esControlMaestro = isset($_SESSION['rol']) && (int) $_SESSION['rol'] === 12;
?>
<?php if ($esControlMaestro): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php endif; ?>

<table id="listaInscritos" class="table table-hover table-bordered text-nowrap" style="width:100%">
        <thead class="thead-dark text-center">
            <tr class="text-center">
                <th>Tipo ID</th>
                <th>Número</th>
                <th>Nombre</th>
                <th>Género</th>
                <th>Edad</th>
                <th>F. nacimiento</th>
                <th>Nacionalidad</th>
                <th>Teléfono 1</th>
                <th>Teléfono 2</th>
                <th>Email</th>
                <th>Email verificado</th>
                <th>Contacto emergencia</th>
                <th>Tel. emergencia</th>
                <th>Departamento</th>
                <th>Municipio</th>
                <th>Dirección</th>
                <th>Área</th>
                <th>Comuna/Correg.</th>
                <th>Barrio/Vereda</th>
                <th>Programa</th>
                <th>Modalidad</th>
                <th>Fecha de inscripción</th>
                <th>Matrícula: Programa</th>
                <th>Matrícula: Estado</th>
                <th>Serie</th>
                <th>Código técnico</th>
                <th>Curso técnico</th>
                <th>Curso inglés</th>
                <th>Habilidades</th>
                <th>Correo institucional</th>
                <th>Usuario Moodle</th>
                <th>Fecha de matrícula</th>
                <th>Acudiente</th>
                <th>Doc. acudiente</th>
                <th>Tel. acudiente</th>
                <th>Email acudiente</th>
                <?php if ($esControlMaestro): ?>
                <th>Acciones</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

<?php if ($esControlMaestro): ?>
<!-- Modal de confirmación para eliminar una inscripción -->
<div class="modal fade" id="confirmDeleteRegisterModal" tabindex="-1" aria-labelledby="confirmDeleteRegisterModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmDeleteRegisterModalLabel">Confirmación de eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="mb-3">
                        <h5>Nombre: <strong id="deleteRegisterUserName"></strong></h5>
                        <h6>Número ID: <strong id="deleteRegisterUserId"></strong></h6>
                    </div>
                    <div class="alert alert-danger">
                        <strong>¡ADVERTENCIA!</strong><br>
                        Está a punto de eliminar la inscripción de este usuario. Si está matriculado, también será desmatriculado.
                    </div>
                    <div class="card shadow-lg p-3 mb-3">
                        <p class="mb-1">Para confirmar, ingrese el siguiente código de seguridad:</p>
                        <div class="input-group mb-3">
                            <input type="text" id="deleteRegisterSecurityCodeDisplay" class="form-control text-center" readonly
                                   style="font-family: monospace; letter-spacing: 3px; font-weight: bold;">
                            <button class="btn btn-outline-secondary" type="button" id="deleteRegisterCopyCodeBtn">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                        <input type="text" id="deleteRegisterSecurityCodeInput" class="form-control text-center"
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
                <button type="button" class="btn btn-danger" id="confirmDeleteRegisterBtn">Eliminar registro</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    $(document).ready(function() {
        var puedeEliminar = <?php echo $esControlMaestro ? 'true' : 'false'; ?>;

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

        function courseCell(name, code) {
            if (!name) return '<span class="text-muted">&mdash;</span>';
            var codeHtml = code ? '<span class="badge text-bg-light border ms-1 font-monospace">' + esc(code) + '</span>' : '';
            return esc(name) + codeHtml;
        }

        if (!$.fn.DataTable) return;

        if ($.fn.DataTable.isDataTable('#listaInscritos')) {
            $('#listaInscritos').DataTable().destroy();
        }

        var columns = [{
                data: 'typeID'
            },
            {
                data: 'number_id'
            },
            {
                data: 'full_name',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'gender',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'age',
                render: function(d) {
                    return d !== null && d !== undefined ? d : '&mdash;';
                }
            },
            {
                data: 'birthdate',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'nationality',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'first_phone',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'second_phone',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'email',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'email_verified',
                render: function(d) {
                    if (d === 1) return '<span class="badge text-bg-success">Verificado</span>';
                    return '<span class="badge text-bg-warning">Sin verificar</span>';
                }
            },
            {
                data: 'emergency_contact_name',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'emergency_contact_number',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'departamento',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'municipio',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'address',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'residence_area',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'comuna_corregimiento',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'barrio',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'program',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'mode',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'creationDate',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'mat_program',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'mat_status',
                render: function(d, type, row) {
                    if (!d) return '<span class="text-muted">Sin matrícula</span>';
                    var map = {
                        enrolled: {
                            t: 'Matriculado',
                            c: 'success'
                        },
                        pending: {
                            t: 'Pendiente',
                            c: 'warning'
                        },
                        active: {
                            t: 'Activo',
                            c: 'success'
                        }
                    };
                    var m = map[d] || {
                        t: d,
                        c: 'secondary'
                    };
                    return '<span class="badge text-bg-' + m.c + '">' + esc(m.t) + '</span>';
                }
            },
            {
                data: 'mat_serie',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'mat_codigo_tecnico',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'tecnico_name',
                render: function(d, t, r) {
                    return courseCell(d, r.tecnico_code);
                }
            },
            {
                data: 'ingles_name',
                render: function(d, t, r) {
                    return courseCell(d, r.ingles_code);
                }
            },
            {
                data: 'habilidades_name',
                render: function(d, t, r) {
                    return courseCell(d, r.habilidades_code);
                }
            },
            {
                data: 'mat_institutional_email',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'mat_username',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'mat_created_at',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'guardian_full_name',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'guardian_document',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'guardian_phone',
                render: function(d) {
                    return empty(d);
                }
            },
            {
                data: 'guardian_email',
                render: function(d) {
                    return empty(d);
                }
            }
        ];

        if (puedeEliminar) {
            columns.push({
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return '<button class="btn btn-danger btn-sm btn-eliminar-registro" ' +
                        'data-id="' + esc(row.number_id) + '" data-name="' + esc(row.full_name) + '">' +
                        '<i class="bi bi-trash"></i> Eliminar</button>';
                }
            });
        }

        var table = $('#listaInscritos').DataTable({
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
                url: 'components/listRegister/listRegister_ajax.php',
                type: 'POST'
            },
            language: {
                url: 'controller/datatable_esp.json'
            },
            columns: columns
        });

        if (puedeEliminar) {
            var currentUserId = '';
            var securityCode = '';

            $(document).on('click', '.btn-eliminar-registro', function() {
                currentUserId = $(this).data('id');
                var userName = $(this).data('name');

                $('#deleteRegisterUserId').text(currentUserId);
                $('#deleteRegisterUserName').text(userName);

                var chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=[]{}|;:,.<>?";
                securityCode = '';
                for (var i = 0; i < 8; i++) {
                    securityCode += chars.charAt(Math.floor(Math.random() * chars.length));
                }

                $('#deleteRegisterSecurityCodeDisplay').val(securityCode);
                $('#deleteRegisterSecurityCodeInput').val('');
                $('#deleteRegisterSecurityCodeInput').removeClass('is-invalid');
                $('#deleteRegisterCodeErrorMsg').remove();

                var modal = new bootstrap.Modal(document.getElementById('confirmDeleteRegisterModal'));
                modal.show();

                setTimeout(function() {
                    document.getElementById('deleteRegisterSecurityCodeInput').focus();
                }, 500);
            });

            $('#deleteRegisterCopyCodeBtn').on('click', function() {
                var codeDisplay = document.getElementById('deleteRegisterSecurityCodeDisplay');
                var $btn = $(this);
                navigator.clipboard.writeText(codeDisplay.value).then(function() {
                    $btn.html('<i class="bi bi-check2"></i>');
                    setTimeout(function() {
                        $btn.html('<i class="bi bi-clipboard"></i>');
                    }, 1500);
                    document.getElementById('deleteRegisterSecurityCodeInput').focus();
                });
            });

            $('#deleteRegisterSecurityCodeInput').on('input', function() {
                $(this).removeClass('is-invalid');
                $('#deleteRegisterCodeErrorMsg').remove();
            });

            $('#confirmDeleteRegisterBtn').on('click', function() {
                var inputCode = $('#deleteRegisterSecurityCodeInput').val();

                if (!inputCode) {
                    showDeleteCodeError('Debe ingresar el código de seguridad');
                    return;
                }

                if (inputCode !== securityCode) {
                    showDeleteCodeError('El código ingresado no coincide');
                    return;
                }

                var modal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteRegisterModal'));
                modal.hide();

                Swal.fire({
                    title: 'Procesando eliminación',
                    text: 'Por favor espere...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                fetch('components/listRegister/deleteRegister.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        number_id: currentUserId
                    })
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(result) {
                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Registro eliminado',
                            text: result.message || 'La inscripción ha sido eliminada correctamente.',
                            allowOutsideClick: false
                        }).then(function() {
                            table.ajax.reload(null, false);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message || 'Ocurrió un error al eliminar el registro.'
                        });
                    }
                })
                .catch(function(err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error de conexión: ' + err.message
                    });
                });
            });

            function showDeleteCodeError(message) {
                var codeInput = document.getElementById('deleteRegisterSecurityCodeInput');
                codeInput.classList.add('is-invalid');
                var errorMsg = document.getElementById('deleteRegisterCodeErrorMsg');
                if (!errorMsg) {
                    errorMsg = document.createElement('div');
                    errorMsg.id = 'deleteRegisterCodeErrorMsg';
                    errorMsg.className = 'invalid-feedback';
                    codeInput.parentNode.appendChild(errorMsg);
                }
                errorMsg.textContent = message;
            }
        }
    });
</script>
