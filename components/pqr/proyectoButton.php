<?php
// Botón + modal para la gestión del listado "Proyecto" de PQRS.
?>
<button type="button" class="btn bg-indigo-dark text-white w-100 mt-3" data-bs-toggle="modal" data-bs-target="#proyectoModal">
    <i class="fas fa-folder-open"></i> Proyecto
</button>

<div class="modal fade" id="proyectoModal" tabindex="-1" aria-labelledby="proyectoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-indigo-dark text-white">
                <h5 class="modal-title" id="proyectoModalLabel"><i class="fas fa-folder-open"></i> Proyecto</h5>
                <button type="button" class="btn-close btn-close-white bg-gray-ligth" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="proyectoForm" class="mb-3" autocomplete="off">
                    <input type="hidden" id="proyecto_id" value="">
                    <div class="input-group">
                        <input type="text" class="form-control" id="proyecto_nombre"
                            placeholder="Nombre del proyecto" required>
                        <button type="submit" class="btn bg-indigo-dark text-white" id="proyectoSubmitBtn">
                            <i class="fas fa-plus"></i> Agregar
                        </button>
                        <button type="button" class="btn btn-secondary d-none" id="proyectoCancelBtn">Cancelar</button>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="tablaProyecto">
                        <thead class="thead-dark">
                            <tr>
                                <th>Proyecto</th>
                                <th class="text-center" style="width: 140px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="proyectoBody">
                            <tr>
                                <td colspan="2" class="text-center">Cargando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel bg-gray-ligth" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        var endpoint = 'components/pqr/proyecto_process.php';

        function escapeHtml(valor) {
            var div = document.createElement('div');
            div.textContent = valor == null ? '' : valor;
            return div.innerHTML;
        }

        function resetForm() {
            document.getElementById('proyecto_id').value = '';
            document.getElementById('proyecto_nombre').value = '';
            document.getElementById('proyectoSubmitBtn').innerHTML = '<i class="fas fa-plus"></i> Agregar';
            document.getElementById('proyectoCancelBtn').classList.add('d-none');
        }

        function editarProyecto(id, nombre) {
            document.getElementById('proyecto_id').value = id;
            document.getElementById('proyecto_nombre').value = nombre;
            document.getElementById('proyectoSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Guardar';
            document.getElementById('proyectoCancelBtn').classList.remove('d-none');
            document.getElementById('proyecto_nombre').focus();
        }

        function eliminarProyecto(id) {
            Swal.fire({
                title: '¿Está seguro?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (!result.isConfirmed) return;
                var formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                fetch(endpoint, {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(res) {
                        if (res.success) {
                            cargarProyectos();
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    })
                    .catch(function() {
                        Swal.fire('Error', 'No se pudo eliminar el proyecto', 'error');
                    });
            });
        }

        function cargarProyectos() {
            fetch(endpoint + '?action=list')
                .then(function(r) {
                    return r.json();
                })
                .then(function(res) {
                    var tbody = document.getElementById('proyectoBody');
                    tbody.innerHTML = '';
                    if (!res.success || !res.data.length) {
                        tbody.innerHTML = '<tr><td colspan="2" class="text-center">No hay proyectos registrados</td></tr>';
                        return;
                    }
                    res.data.forEach(function(p) {
                        var tr = document.createElement('tr');
                        tr.innerHTML = '<td>' + escapeHtml(p.nombre) + '</td>' +
                            '<td class="text-center">' +
                            '<button type="button" class="btn bg-orange-dark btn-sm me-1 btn-editar-proyecto" title="Editar"><i class="fas fa-edit"></i></button>' +
                            '<button type="button" class="btn btn-danger btn-sm btn-eliminar-proyecto" title="Eliminar"><i class="fas fa-trash"></i></button>' +
                            '</td>';
                        tr.querySelector('.btn-editar-proyecto').addEventListener('click', function() {
                            editarProyecto(p.id, p.nombre);
                        });
                        tr.querySelector('.btn-eliminar-proyecto').addEventListener('click', function() {
                            eliminarProyecto(p.id);
                        });
                        tbody.appendChild(tr);
                    });
                })
                .catch(function() {
                    document.getElementById('proyectoBody').innerHTML =
                        '<tr><td colspan="2" class="text-center text-danger">Error al cargar los proyectos</td></tr>';
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            var modalEl = document.getElementById('proyectoModal');
            if (modalEl) modalEl.addEventListener('show.bs.modal', cargarProyectos);

            document.getElementById('proyectoForm').addEventListener('submit', function(e) {
                e.preventDefault();
                var nombre = document.getElementById('proyecto_nombre').value.trim();
                var id = document.getElementById('proyecto_id').value;
                if (!nombre) {
                    Swal.fire('Atención', 'Ingrese el nombre del proyecto', 'warning');
                    return;
                }
                var formData = new FormData();
                formData.append('action', id ? 'update' : 'create');
                if (id) formData.append('id', id);
                formData.append('nombre', nombre);
                fetch(endpoint, {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(res) {
                        if (res.success) {
                            resetForm();
                            cargarProyectos();
                            Swal.fire({
                                icon: 'success',
                                title: id ? 'Actualizado' : 'Creado',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    })
                    .catch(function() {
                        Swal.fire('Error', 'No se pudo guardar el proyecto', 'error');
                    });
            });

            document.getElementById('proyectoCancelBtn').addEventListener('click', resetForm);
        });

        cargarProyectos();
    })();
</script>
