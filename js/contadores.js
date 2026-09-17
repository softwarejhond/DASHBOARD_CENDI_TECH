(function () {
    'use strict';

    var charts = {};
    var ultimosDatos = null;

    var GENDER_COLORS = {
        'Hombre': '#181E93',
        'Mujer': '#ec008c',
        'Intersexual': '#00976a',
        'No binario': '#F9B233',
        'LGTBIQ+': '#7c3aed',
        'LGBTIQ+': '#7c3aed',
        'LGBIQ+': '#7c3aed',
        'Otro': '#64748b',
        'No reporta': '#cbd5e1'
    };

    var PROGRAMA_COLORS = ['#181E93', '#ec008c', '#00976a', '#F9B233', '#7c3aed', '#0ea5e9', '#64748b', '#d92d20'];
    var EDAD_COLORS = ['#181E93', '#00976a', '#F9B233', '#ec008c'];

    // Plugin para dibujar el total en el centro de las donas
    var centerTextPlugin = {
        id: 'centerText',
        afterDraw: function (chart) {
            var opts = chart.options.plugins.centerText;
            if (!opts || !opts.text) return;
            var area = chart.chartArea;
            if (!area) return;
            var ctx = chart.ctx;
            ctx.save();
            ctx.font = '700 22px "Open Sans", sans-serif';
            ctx.fillStyle = opts.color || '#0f172a';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(opts.text, (area.left + area.right) / 2, (area.top + area.bottom) / 2);
            ctx.restore();
        }
    };

    if (window.Chart && !window.Chart.registry.plugins.get('centerText')) {
        window.Chart.register(centerTextPlugin);
    }

    function fmt(n) {
        return (n || 0).toLocaleString('es-CO');
    }

    function pct(part, total) {
        if (!total) return '0%';
        return ((part / total) * 100).toFixed(1).replace('.', ',') + '%';
    }

    function setText(id, val) {
        var el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    function destroyChart(name) {
        if (charts[name]) { charts[name].destroy(); charts[name] = null; }
    }

    function initSelect2(selector, placeholder) {
        if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.select2) return;
        var $sel = window.jQuery(selector);
        if ($sel.data('select2')) { $sel.select2('destroy'); }
        $sel.select2({
            placeholder: placeholder,
            allowClear: true,
            minimumResultsForSearch: 0,
            width: '100%'
        });
    }

    function renderKpis(d) {
        setText('kpi-registrados', fmt(d.registrados));
        setText('kpi-matriculados', fmt(d.matriculados));
        setText('kpi-por-verificar-correo', fmt(d.por_verificar_correo));
        setText('kpi-rural', fmt(d.rural));

        setText('sub-registrados', 'Matriculados: ' + pct(d.matriculados, d.registrados));
        setText('sub-matriculados', pct(d.matriculados, d.registrados) + ' del total');
        setText('sub-por-verificar-correo', pct(d.por_verificar_correo, d.registrados) + ' del total');
        setText('sub-rural', pct(d.rural, d.registrados) + ' del total');
    }

    function renderGender(d) {
        var labels = [], values = [], colors = [];
        (d.generos || []).forEach(function (g) {
            var nombre = (g.gener === 'LGBIQ+' || g.gener === 'LGBTIQ+') ? 'LGBTIQ+' : (g.gener || 'Sin dato');
            labels.push(nombre);
            values.push(Number(g.cantidad) || 0);
            colors.push(GENDER_COLORS[g.gener] || GENDER_COLORS[nombre] || '#94a3b8');
        });
        var total = values.reduce(function (a, b) { return a + b; }, 0);

        var canvas = document.getElementById('chart-genero');
        if (!canvas || !window.Chart) return;
        destroyChart('genero');
        charts.genero = new window.Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{ data: values, backgroundColor: colors, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '68%',
                plugins: {
                    centerText: { text: fmt(total), color: '#0f172a' },
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, boxHeight: 8, padding: 14, font: { size: 11 } } },
                    tooltip: { callbacks: { label: function (c) { return ' ' + c.label + ': ' + fmt(c.parsed) + ' (' + pct(c.parsed, total) + ')'; } } }
                }
            }
        });
    }

    function renderNacionalidad(d) {
        var labels = [], values = [], colors = [];
        (d.nacionalidades || []).forEach(function (n) {
            var nombre = n.nacionalidad || 'Sin dato';
            labels.push(nombre);
            values.push(Number(n.cantidad) || 0);
            var color = '#94a3b8';
            if (nombre === 'Colombiana') color = '#181E93';
            else if (nombre === 'Venezolana') color = '#ec008c';
            colors.push(color);
        });
        var total = values.reduce(function (a, b) { return a + b; }, 0);

        var canvas = document.getElementById('chart-nacionalidad');
        if (!canvas || !window.Chart) return;
        destroyChart('nacionalidad');
        charts.nacionalidad = new window.Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{ data: values, backgroundColor: colors, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '68%',
                plugins: {
                    centerText: { text: fmt(total), color: '#0f172a' },
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, boxHeight: 8, padding: 14, font: { size: 11 } } },
                    tooltip: { callbacks: { label: function (c) { return ' ' + c.label + ': ' + fmt(c.parsed) + ' (' + pct(c.parsed, total) + ')'; } } }
                }
            }
        });
    }

    function renderProgramas(d) {
        var labels = [], values = [], colors = [];
        (d.programas || []).forEach(function (p, i) {
            labels.push(p.program || 'Sin dato');
            values.push(Number(p.cantidad) || 0);
            colors.push(PROGRAMA_COLORS[i % PROGRAMA_COLORS.length]);
        });

        var canvas = document.getElementById('chart-programas');
        if (!canvas || !window.Chart) return;
        destroyChart('programas');
        charts.programas = new window.Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{ data: values, backgroundColor: colors, borderRadius: 6, maxBarThickness: 20 }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function (c) { return ' ' + fmt(c.parsed.x || c.parsed); } } }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 } },
                    y: { ticks: { font: { size: 11 } } }
                }
            }
        });
    }

    function renderEdades(d) {
        var labels = [], values = [];
        (d.edades || []).forEach(function (e) {
            labels.push(e.rango);
            values.push(Number(e.cantidad) || 0);
        });

        var canvas = document.getElementById('chart-edades');
        if (!canvas || !window.Chart) return;
        destroyChart('edades');
        charts.edades = new window.Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{ data: values, backgroundColor: EDAD_COLORS, borderRadius: 6, maxBarThickness: 46 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function (c) { return ' ' + fmt(c.parsed.y !== undefined ? c.parsed.y : c.parsed) + ' inscritos'; } } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }

    function renderComunas(d) {
        var sel = document.getElementById('comuna-select');
        if (!sel) return;
        var comunas = d.comunas || [];
        var html = '<option value=""></option>';
        comunas.forEach(function (c) {
            var nombre = c || 'Sin dato';
            html += '<option value="' + String(nombre).replace(/"/g, '&quot;') + '">' + nombre + '</option>';
        });
        sel.innerHTML = html;
        setText('comuna-total', fmt(d.registrados));
        initSelect2('#comuna-select', 'Buscar comuna...');
    }

    function renderBarrios(d) {
        var sel = document.getElementById('barrio-select');
        if (!sel) return;
        var barrios = d.barrios || [];
        var html = '<option value=""></option>';
        barrios.forEach(function (b) {
            var nombre = b || 'Sin dato';
            html += '<option value="' + String(nombre).replace(/"/g, '&quot;') + '">' + nombre + '</option>';
        });
        sel.innerHTML = html;
        setText('barrio-total', fmt(d.registrados));
        initSelect2('#barrio-select', 'Buscar barrio...');
    }

    function actualizarHora() {
        var now = new Date();
        var h = now.getHours();
        var m = String(now.getMinutes()).padStart(2, '0');
        var s = String(now.getSeconds()).padStart(2, '0');
        var ap = h >= 12 ? 'p.m.' : 'a.m.';
        h = h % 12; h = h || 12;
        setText('cnt-last-update', h + ':' + m + ':' + s + ' ' + ap);
    }

    function actualizarContadores() {
        return fetch('components/cardContadores/actualizarContadores.php')
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function (d) {
                ultimosDatos = d;
                renderKpis(d);
                renderGender(d);
                renderNacionalidad(d);
                renderProgramas(d);
                renderEdades(d);
                renderComunas(d);
                renderBarrios(d);
                actualizarHora();
                return d;
            })
            .catch(function (err) { console.error('Error al cargar contadores:', err); });
    }

    function cargarComuna() {
        var sel = document.getElementById('comuna-select');
        var val = sel ? sel.value : '';
        if (!val) {
            setText('comuna-total', fmt(ultimosDatos ? ultimosDatos.registrados : 0));
            return;
        }
        fetch('components/cardContadores/buscarComuna.php?comuna=' + encodeURIComponent(val))
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function (d) { setText('comuna-total', fmt(d.total || 0)); })
            .catch(function (err) { console.error('Error al cargar comuna:', err); });
    }

    function cargarBarrio() {
        var sel = document.getElementById('barrio-select');
        var val = sel ? sel.value : '';
        if (!val) {
            setText('barrio-total', fmt(ultimosDatos ? ultimosDatos.registrados : 0));
            return;
        }
        fetch('components/cardContadores/buscarBarrio.php?barrio=' + encodeURIComponent(val))
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function (d) { setText('barrio-total', fmt(d.total || 0)); })
            .catch(function (err) { console.error('Error al cargar barrio:', err); });
    }

    function formatFechaISO(iso) {
        var p = String(iso || '').split('-');
        if (p.length !== 3) return iso;
        return p[2] + '/' + p[1] + '/' + p[0];
    }

    function resetFiltroFecha() {
        if (ultimosDatos) {
            setText('kpi-registrados', fmt(ultimosDatos.registrados));
            setText('sub-registrados', 'Matriculados: ' + pct(ultimosDatos.matriculados, ultimosDatos.registrados));
        }
    }

    function getHoyISO() {
        var hoy = new Date();
        return hoy.getFullYear() + '-' + String(hoy.getMonth() + 1).padStart(2, '0') + '-' + String(hoy.getDate()).padStart(2, '0');
    }

    function initFechaFilter() {
        var input = document.getElementById('fecha-filter');
        if (!input) return;
        input.value = getHoyISO();
    }

    function onFechaFilterChange() {
        var input = document.getElementById('fecha-filter');
        if (!input) return;
        var value = input.value;

        // Si se limpió o es hoy (o posterior), volver al total completo
        if (!value || value >= getHoyISO()) {
            input.value = getHoyISO();
            resetFiltroFecha();
            return;
        }

        fetch('components/cardContadores/actualizarContadores.php?date=' + encodeURIComponent(value))
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function (d) {
                var total = d.registrados_por_fecha || 0;
                setText('kpi-registrados', fmt(total));
                var sub = document.getElementById('sub-registrados');
                if (sub) {
                    sub.innerHTML = '<span class="cnt-filter">Filtrado hasta ' + formatFechaISO(value) + '</span>' +
                        ' <button type="button" id="clear-filter" class="cnt-clear-btn" title="Quitar filtro">Ver total</button>';
                    var btn = document.getElementById('clear-filter');
                    if (btn) {
                        btn.addEventListener('click', function (e) {
                            e.preventDefault();
                            document.getElementById('fecha-filter').value = getHoyISO();
                            resetFiltroFecha();
                        });
                    }
                }
            })
            .catch(function (err) {
                console.error('Error al filtrar por fecha:', err);
            });
    }

    function bind() {
        var fechaFilter = document.getElementById('fecha-filter');
        if (fechaFilter) fechaFilter.addEventListener('change', onFechaFilterChange);
        initFechaFilter();

        // Select2 dispara eventos 'change' vía jQuery; usamos delegación para que
        // sobreviva al destroy/re-init de los selectores.
        var $jq = window.jQuery;
        if ($jq) {
            $jq(document).on('change', '#comuna-select', cargarComuna);
            $jq(document).on('change', '#barrio-select', cargarBarrio);
        }

        window.addEventListener('resize', function () {
            Object.keys(charts).forEach(function (k) { if (charts[k]) charts[k].resize(); });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        bind();
        actualizarContadores();
        setInterval(actualizarHora, 1000);
    });

    // Expone la actualización manual para el botón de main.php
    window.actualizarContadoresManual = actualizarContadores;
})();
