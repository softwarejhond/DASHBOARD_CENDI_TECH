<?php
// Modal de configuración de notas (pesos y nota mínima para aprobar).
// Se incluye desde components/sliderBar.php.
?>
<div class="modal fade" id="modalConfigNotas" tabindex="-1" aria-labelledby="modalConfigNotasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-indigo-dark text-white">
                <h5 class="modal-title" id="modalConfigNotasLabel"><i class="bi bi-percent"></i> Configuración de notas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Ponderación de la nota final (debe sumar 100%) y nota mínima para aprobar.</p>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label" for="cn-peso-tecnico">Peso técnico (%)</label>
                        <input type="number" step="0.01" min="0" max="100" id="cn-peso-tecnico" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="cn-peso-ingles">Peso inglés (%)</label>
                        <input type="number" step="0.01" min="0" max="100" id="cn-peso-ingles" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="cn-peso-habilidades">Peso habilidades (%)</label>
                        <input type="number" step="0.01" min="0" max="100" id="cn-peso-habilidades" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="cn-nota-minima">Nota mínima para aprobar</label>
                        <input type="number" step="0.01" min="0" max="5" id="cn-nota-minima" class="form-control">
                    </div>
                    <div class="col-12">
                        <span id="cn-sum" class="small text-muted">Suma de porcentajes: --</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn bg-magenta-dark text-white" id="cn-guardar">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var modalEl = document.getElementById('modalConfigNotas');
        if (!modalEl) return;

        var inputs = {
            tecnico: document.getElementById('cn-peso-tecnico'),
            ingles: document.getElementById('cn-peso-ingles'),
            habilidades: document.getElementById('cn-peso-habilidades'),
            minima: document.getElementById('cn-nota-minima')
        };

        function notificar(icon, text) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: icon, title: icon === 'success' ? 'Éxito' : 'Error', text: text, timer: icon === 'success' ? 2200 : undefined, showConfirmButton: icon !== 'success' });
            } else {
                alert(text);
            }
        }

        function actualizarSuma() {
            var t = parseFloat(inputs.tecnico.value) || 0;
            var i = parseFloat(inputs.ingles.value) || 0;
            var h = parseFloat(inputs.habilidades.value) || 0;
            var suma = t + i + h;
            var el = document.getElementById('cn-sum');
            if (!el) return;
            el.textContent = 'Suma de porcentajes: ' + suma.toFixed(2).replace('.', ',') + '%';
            el.className = 'small ' + (Math.abs(suma - 100) < 0.001 ? 'text-success' : 'text-danger');
        }

        Object.keys(inputs).forEach(function (k) {
            if (inputs[k]) inputs[k].addEventListener('input', actualizarSuma);
        });

        modalEl.addEventListener('show.bs.modal', function () {
            fetch('components/notas/get_config_notas.php')
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (!d || !d.ok) return;
                    inputs.tecnico.value = d.config.peso_tecnico;
                    inputs.ingles.value = d.config.peso_ingles;
                    inputs.habilidades.value = d.config.peso_habilidades;
                    inputs.minima.value = d.config.nota_minima_aprobacion;
                    actualizarSuma();
                })
                .catch(function () {});
        });

        var btn = document.getElementById('cn-guardar');
        if (btn) {
            btn.addEventListener('click', function () {
                var fd = new FormData();
                fd.append('peso_tecnico', inputs.tecnico.value);
                fd.append('peso_ingles', inputs.ingles.value);
                fd.append('peso_habilidades', inputs.habilidades.value);
                fd.append('nota_minima_aprobacion', inputs.minima.value);

                fetch('components/notas/guardar_config_notas.php', { method: 'POST', body: fd })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        if (res && res.ok) {
                            notificar('success', res.message || 'Configuración actualizada.');
                            var modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        } else {
                            notificar('error', (res && res.message) || 'No se pudo guardar.');
                        }
                    })
                    .catch(function () { notificar('error', 'Error de conexión.'); });
            });
        }
    })();
</script>
