<div class="cnt-dashboard">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="cnt-last-update"><i class="bi bi-hourglass-split"></i> Última actualización: <span id="cnt-last-update">--:--:--</span></span>
    </div>

    <!-- Fila 1: KPIs principales -->
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-lg-3">
            <article class="stat-card stat-card--magenta">
                <div class="stat-card__icon"><i class="bi bi-people-fill"></i></div>
                <div class="stat-card__meta">
                    <span class="stat-card__label">Total registrados a la fecha
                        <input type="date" id="fecha-filter" class="cnt-date-input" min="2026-09-10" title="Filtrar por fecha">
                    </span>
                    <span class="stat-card__value" id="kpi-registrados">0</span>
                    <span class="stat-card__sub" id="sub-registrados">&mdash;</span>
                </div>
            </article>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <article class="stat-card stat-card--blue">
                <div class="stat-card__icon"><i class="bi bi-mortarboard-fill"></i></div>
                <div class="stat-card__meta">
                    <span class="stat-card__label">Matriculados</span>
                    <span class="stat-card__value" id="kpi-matriculados">0</span>
                    <span class="stat-card__sub" id="sub-matriculados">&mdash;</span>
                </div>
            </article>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <article class="stat-card stat-card--amber">
                <div class="stat-card__icon"><i class="bi bi-envelope-exclamation"></i></div>
                <div class="stat-card__meta">
                    <span class="stat-card__label">Por verificar correo</span>
                    <span class="stat-card__value" id="kpi-por-verificar-correo">0</span>
                    <span class="stat-card__sub" id="sub-por-verificar-correo">&mdash;</span>
                </div>
            </article>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <article class="stat-card stat-card--green">
                <div class="stat-card__icon"><i class="bi bi-tree-fill"></i></div>
                <div class="stat-card__meta">
                    <span class="stat-card__label">Residencia rural</span>
                    <span class="stat-card__value" id="kpi-rural">0</span>
                    <span class="stat-card__sub" id="sub-rural">&mdash;</span>
                </div>
            </article>
        </div>
    </div>

    <!-- Fila 2: avance por componentes -->
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-lg-3">
            <article class="stat-card stat-card--blue">
                <div class="stat-card__icon"><i class="bi bi-mortarboard-fill"></i></div>
                <div class="stat-card__meta">
                    <span class="stat-card__label">Componente principal</span>
                    <span class="stat-card__value" id="kpi-comp-principal">0</span>
                    <span class="stat-card__sub" id="sub-comp-principal">&mdash;</span>
                </div>
            </article>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <article class="stat-card stat-card--magenta">
                <div class="stat-card__icon"><i class="bi bi-translate"></i></div>
                <div class="stat-card__meta">
                    <span class="stat-card__label">Componente inglés</span>
                    <span class="stat-card__value" id="kpi-comp-ingles">0</span>
                    <span class="stat-card__sub" id="sub-comp-ingles">&mdash;</span>
                </div>
            </article>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <article class="stat-card stat-card--amber">
                <div class="stat-card__icon"><i class="bi bi-person-hearts"></i></div>
                <div class="stat-card__meta">
                    <span class="stat-card__label">Componente habilidades</span>
                    <span class="stat-card__value" id="kpi-comp-habilidades">0</span>
                    <span class="stat-card__sub" id="sub-comp-habilidades">&mdash;</span>
                </div>
            </article>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <article class="stat-card stat-card--green">
                <div class="stat-card__icon"><i class="bi bi-patch-check-fill"></i></div>
                <div class="stat-card__meta">
                    <span class="stat-card__label">Todos los componentes</span>
                    <span class="stat-card__value" id="kpi-comp-todos">0</span>
                    <span class="stat-card__sub" id="sub-comp-todos">&mdash;</span>
                </div>
            </article>
        </div>
    </div>

    <!-- Fila 3: gráficas -->
    <div class="row g-3">
        <div class="col-12 col-md-6 col-lg-4">
            <section class="panel-card">
                <div class="panel-card__header">
                    <h3 class="panel-card__title"><i class="bi bi-gender-ambiguous"></i> Registros por género</h3>
                </div>
                <div class="panel-card__body">
                    <div class="chart-box"><canvas id="chart-genero"></canvas></div>
                </div>
            </section>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <section class="panel-card">
                <div class="panel-card__header">
                    <h3 class="panel-card__title"><i class="bi bi-globe2"></i> Nacionalidad</h3>
                </div>
                <div class="panel-card__body">
                    <div class="chart-box"><canvas id="chart-nacionalidad"></canvas></div>
                </div>
            </section>
        </div>
        <div class="col-12 col-lg-4">
            <section class="panel-card">
                <div class="panel-card__header">
                    <h3 class="panel-card__title"><i class="bi bi-diagram-3"></i> Programas</h3>
                </div>
                <div class="panel-card__body">
                    <div class="chart-box chart-box--bar"><canvas id="chart-programas"></canvas></div>
                </div>
            </section>
        </div>
    </div>

    <!-- Fila 4: rangos de edad + comuna + barrio -->
    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <section class="panel-card">
                <div class="panel-card__header">
                    <h3 class="panel-card__title"><i class="bi bi-bar-chart-fill"></i> Rangos de edad</h3>
                </div>
                <div class="panel-card__body">
                    <div class="chart-box"><canvas id="chart-edades"></canvas></div>
                </div>
            </section>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <section class="panel-card">
                <div class="panel-card__header">
                    <h3 class="panel-card__title"><i class="bi bi-geo-alt-fill"></i> Comuna</h3>
                </div>
                <div class="panel-card__body">
                    <label class="stat-card__label d-block mb-2" for="comuna-select">Comuna o corregimiento</label>
                    <select id="comuna-select" class="form-select mb-3">
                        <option value="">Todas</option>
                    </select>
                    <div class="mini-stat">
                        <span class="mini-stat__label">Registros</span>
                        <span class="mini-stat__value" id="comuna-total">0</span>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <section class="panel-card">
                <div class="panel-card__header">
                    <h3 class="panel-card__title"><i class="bi bi-signpost-split-fill"></i> Barrio / Vereda</h3>
                </div>
                <div class="panel-card__body">
                    <label class="stat-card__label d-block mb-2" for="barrio-select">Barrio o vereda</label>
                    <select id="barrio-select" class="form-select mb-3">
                        <option value="">Todos</option>
                    </select>
                    <div class="mini-stat">
                        <span class="mini-stat__label">Registros</span>
                        <span class="mini-stat__value" id="barrio-total">0</span>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="js/contadores.js?v=1.8"></script>
