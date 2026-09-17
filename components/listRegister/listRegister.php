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
            </tr>
        </thead>
        <tbody></tbody>
    </table>

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

        function courseCell(name, code) {
            if (!name) return '<span class="text-muted">&mdash;</span>';
            var codeHtml = code ? '<span class="badge text-bg-light border ms-1 font-monospace">' + esc(code) + '</span>' : '';
            return esc(name) + codeHtml;
        }

        if (!$.fn.DataTable) return;

        if ($.fn.DataTable.isDataTable('#listaInscritos')) {
            $('#listaInscritos').DataTable().destroy();
        }

        $('#listaInscritos').DataTable({
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
            columns: [{
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
            ]
        });
    });
</script>