<?php
// =====================================================================
// Filtros comunes (set de curso + cédula) para las tablas de
// matriculados y desmatriculados.
// El selector de "set de curso" carga sus opciones bajo demanda (AJAX).
// Espera: $prefix (string) usado para generar IDs únicos.
// =====================================================================
?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .filter-card .select2-container { width: 100% !important; }
    .filter-card .select2-container--default .select2-selection--single { height: 38px; }
    .filter-card .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px; padding-top: 0; padding-bottom: 0; }
    .filter-card .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
    .filter-title { font-weight: 600; margin-bottom: 0.35rem; }
</style>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-6">
        <div class="filter-title"><i class="bi bi-collection"></i> Set de curso</div>
        <div class="card filter-card" data-icon="🎓">
            <div class="card-body p-2">
                <select id="filterSet<?= htmlspecialchars($prefix) ?>" class="form-select"></select>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="filter-title"><i class="bi bi-search"></i> Cédula</div>
        <div class="card filter-card" data-icon="🔎">
            <div class="card-body p-2">
                <div class="input-group">
                    <input type="text" id="searchNumber<?= htmlspecialchars($prefix) ?>" class="form-control"
                           placeholder="Número de cédula" maxlength="15"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                    <button class="btn bg-indigo-dark" type="button" id="btnSearch<?= htmlspecialchars($prefix) ?>">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
