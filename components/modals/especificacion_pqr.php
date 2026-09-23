<?php

// Recibir el ID de la PQR como variable local
if (!isset($id_pqr_actual)) {
    return;
}

$id_pqr = filter_var($id_pqr_actual, FILTER_VALIDATE_INT);

if ($id_pqr === false || $id_pqr === null) {
    return;
}

$stmt = $conn->prepare("SELECT id, numero_radicado, especificacion FROM pqr WHERE id = ?");

if ($stmt === false) {
    error_log("Error al preparar la consulta de especificación: " . $conn->error);
    return;
}

$stmt->bind_param("i", $id_pqr);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    $stmt->close();
    return;
}

$fila_especificacion = $resultado->fetch_assoc();
$stmt->close();
?>

<!-- Modal Especificación -->
<div class="modal fade" id="especificacionPQRModal-<?php echo htmlspecialchars($fila_especificacion["id"]); ?>" tabindex="-1"
    aria-labelledby="especificacionPQRModalLabel-<?php echo htmlspecialchars($fila_especificacion["id"]); ?>"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-indigo-dark text-white">
                <h5 class="modal-title"
                    id="especificacionPQRModalLabel-<?php echo htmlspecialchars($fila_especificacion["id"]); ?>">
                    <i class="fas fa-clipboard-list"></i> Especificación -
                    <?php echo htmlspecialchars($fila_especificacion["numero_radicado"]); ?>
                </h5>
                <button type="button" class="btn-close btn-close-white bg-gray-ligth" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <label for="especificacion_<?php echo htmlspecialchars($fila_especificacion["id"]); ?>"
                        class="col-sm-3 col-form-label text-start">Especificación:</label>
                    <div class="col-sm-9">
                        <textarea class="form-control"
                            id="especificacion_<?php echo htmlspecialchars($fila_especificacion["id"]); ?>" rows="4"
                            placeholder="Escriba la especificación..."><?php echo htmlspecialchars($fila_especificacion["especificacion"] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel bg-gray-ligth" data-bs-dismiss="modal"><i
                        class="fas fa-times"></i> Cerrar</button>
                <button type="button" class="btn bg-indigo-dark text-white btn-guardar-especificacion"
                    data-id="<?php echo htmlspecialchars($fila_especificacion["id"]); ?>"><i class="fas fa-save"></i>
                    Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        var boton = document.querySelector('#especificacionPQRModal-<?php echo (int) $fila_especificacion["id"]; ?> .btn-guardar-especificacion');
        if (!boton) return;

        boton.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var texto = document.getElementById('especificacion_' + id).value;
            var formData = new FormData();
            formData.append('id', id);
            formData.append('especificacion', texto);

            fetch('components/pqr/update_especificacion.php', {
                    method: 'POST',
                    body: formData
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(res) {
                    if (res.success) {
                        Swal.fire('¡Guardado!', res.message, 'success').then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                })
                .catch(function() {
                    Swal.fire('Error', 'No se pudo guardar la especificación', 'error');
                });
        });
    })();
</script>
<!-- Fin del Modal Especificación -->
