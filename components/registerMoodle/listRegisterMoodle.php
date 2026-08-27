<?php

include __DIR__ . '/../moodle_api.php';
$courses_data = getCoursesB();

// Consulta para obtener usuarios
$sql = "SELECT user_register.*, departamentos.departamento
        FROM user_register
        INNER JOIN departamentos ON user_register.department = departamentos.id_departamento
        WHERE departamentos.id_departamento = 11
          AND user_register.status = '1' 
          AND user_register.statusAdmin = '1'
        ORDER BY user_register.first_name ASC";

$result = $conn->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    echo '<div class="alert alert-info">No hay datos disponibles.</div>';
}

// Obtener datos únicos para los filtros
$departamentos = ['BOYACÁ', 'CUNDINAMARCA'];
$programas = [];
$modalidades = [];
$sedes = []; // Agregar array para sedes

foreach ($data as $row) {
    $depto = $row['departamento'];
    $sede = $row['headquarters'];

    // Obtener sedes únicas
    if (!in_array($sede, $sedes) && !empty($sede)) {
        $sedes[] = $sede;
    }

    // Obtener programas únicos
    if (!in_array($row['program'], $programas)) {
        $programas[] = $row['program'];
    }

    // Obtener modalidades únicas
    if (!in_array($row['mode'], $modalidades)) {
        $modalidades[] = $row['mode'];
    }
}

?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid px-2">
    <div class="table-responsive">

        <div class="row p-3">
            <b class="text-left mb-1"><i class="bi bi-card-checklist"></i> Seleccionar cursos</b>

            <div class="col-md-6 col-sm-12 col-lg-3">
                <div class="course-title text-indigo-dark "><i class="bi bi-laptop"></i> Bootcamp</div>
                <div class="card course-card card-bootcamp" data-icon="💻">
                    <div class="card-body">
                        <select id="bootcamp" class="form-select course-select">
                            <?php if (!empty($courses_data)): ?>
                                <?php foreach ($courses_data as $course): ?>
                                    <?php
                                    $categoryAllowed = in_array($course['categoryid'], [3, 7, 8, 9, 10, 11, 12]);
                                    if ($categoryAllowed):
                                    ?>
                                        <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                            <?php echo htmlspecialchars($course['id'] . ' - ' . $course['fullname']); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-sm-12 col-lg-3">
                <div class="course-title text-indigo-dark"><i class="bi bi-translate"></i> Inglés nivelatorio</div>
                <div class="card course-card card-ingles" data-icon="🌍">
                    <div class="card-body">
                        <select id="ingles" class="form-select course-select">
                            <?php if (!empty($courses_data)): ?>
                                <?php foreach ($courses_data as $course): ?>
                                    <?php if ($course['categoryid'] == 4): ?>
                                        <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                            <?= htmlspecialchars($course['id'] . ' - ' . $course['fullname']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-sm-12 col-lg-3">
                <div class="course-title text-indigo-dark"><i class="bi bi-code-slash"></i> English Code</div>
                <div class="card course-card card-english-code" data-icon="👨‍💻">
                    <div class="card-body">
                        <select id="english_code" class="form-select course-select">
                            <?php if (!empty($courses_data)): ?>
                                <?php foreach ($courses_data as $course): ?>
                                    <?php if ($course['categoryid'] == 5): ?>
                                        <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                            <?= htmlspecialchars($course['id'] . ' - ' . $course['fullname']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-sm-12 col-lg-3">
                <div class="course-title text-indigo-dark"><i class="bi bi-lightbulb"></i> Habilidades</div>
                <div class="card course-card card-skills" data-icon="💡">
                    <div class="card-body">
                        <select id="skills" class="form-select course-select">
                            <?php if (!empty($courses_data)): ?>
                                <?php foreach ($courses_data as $course): ?>
                                    <?php if ($course['categoryid'] == 6): ?>
                                        <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                            <?= htmlspecialchars($course['id'] . ' - ' . $course['fullname']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row p-3 mb-1">
            <b class="text-left mb-1"><i class="bi bi-filter-circle"></i> Filtrar beneficiario</b>

            <div class="col-md-6 col-sm-12 col-lg-3">
                <div class="filter-title"><i class="bi bi-map"></i> Departamento</div>
                <div class="card filter-card card-department" data-icon="📍">
                    <div class="card-body">
                        <select id="filterDepartment" class="form-select">
                            <option value="">Todos los departamentos</option>
                            <option value="BOYACÁ">BOYACÁ</option>
                            <option value="CUNDINAMARCA">CUNDINAMARCA</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-sm-12 col-lg-3">
                <div class="filter-title"><i class="bi bi-building"></i> Sede</div>
                <div class="card filter-card card-headquarters" data-icon="🏫">
                    <div class="card-body">
                        <select id="filterHeadquarters" class="form-select">
                            <option value="">Todas las sedes</option>
                            <?php foreach ($sedes as $sede): ?>
                                <option value="<?= htmlspecialchars($sede) ?>"><?= htmlspecialchars($sede) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-sm-12 col-lg-3">
                <div class="filter-title"><i class="bi bi-mortarboard"></i> Programa</div>
                <div class="card filter-card card-program" data-icon="🎓">
                    <div class="card-body">
                        <select id="filterProgram" class="form-select">
                            <option value="">Todos los programas</option>
                            <?php foreach ($programas as $programa): ?>
                                <option value="<?= htmlspecialchars($programa) ?>"><?= htmlspecialchars($programa) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-sm-12 col-lg-3">
                <div class="filter-title"><i class="bi bi-laptop"></i> Modalidad</div>
                <div class="card filter-card card-mode" data-icon="💻">
                    <div class="card-body">
                        <select id="filterMode" class="form-select">
                            <option value="">Todas las modalidades</option>
                            <option value="Virtual">Virtual</option>
                            <option value="Presencial">Presencial</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <table id="listaInscritos" class="table table-hover table-bordered">
            <button id="matricularSeleccionados" class="btn bg-magenta-dark text-white btn-lg float-end ms-2">
                <i class="bi bi-card-checklist"></i> Matricular Seleccionados
            </button>
            <div class="d-flex justify-content mb-3 p-3">
                <!-- Botón para exportar a Excel -->
                <button id="exportarExcel" class="btn btn-success btn-lg"
                    onclick="window.location.href='components/registerMoodle/export_excel_enrolled.php?action=export'">
                    <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
                </button>
                <!-- Botón para mostrar usuarios seleccionados -->
                <button class="btn bg-lime-dark text-vlack ms-2 btn-lg float-end">
                    <i class="bi bi-card-checklist"></i> Usuarios seleccionados:
                    <span id="contador">0</span>
                </button>


            </div>

            <thead class="thead-dark text-center">
                <tr class="text-center">
                    <th>Tipo ID</th>
                    <th>Número</th>
                    <th>Nombre</th>
                    <th>Modalidad</th>
                    <th class="text-center">
                        <input type="checkbox" id="selectAll" class="form-check-input"
                            style="width: 25px; height: 25px; appearance: none; background-color: white; border: 2px solid #ec008c; cursor: pointer; position: relative;"
                            onclick="toggleCheckboxes(this)">
                    </th>
                    <th>Email</th>
                    <th>Nuevo Email</th>
                    <th>Departamento</th>
                    <th>Sede</th>
                    <th>Programa</th>
                    <th>Nivel de preferencia</th>

                </tr>
            </thead>
            <tbody class="text-center">
                <?php foreach ($data as $row):
                    // Procesar datos del usuario
                    $firstName   = ucwords(strtolower(trim($row['first_name'])));
                    $secondName  = ucwords(strtolower(trim($row['second_name'])));
                    $firstLast   = ucwords(strtolower(trim($row['first_last'])));
                    $secondLast  = ucwords(strtolower(trim($row['second_last'])));
                    $fullName = $firstName . " " . $secondName . " " . $firstLast . " " . $secondLast;
                    $nuevoCorreo = strtolower(substr(trim($row['first_name']), 0, 1))
                        . strtolower(substr(trim($row['second_name']), 0, 1))
                        . substr(trim($row['number_id']), -4)
                        . strtolower(substr(trim($row['first_last']), 0, 1))
                        . strtolower(substr(trim($row['second_last']), 0, 1))
                        . '.ut@cendi.edu.co';
                ?>
                    <tr data-type-id="<?php echo htmlspecialchars($row['typeID']); ?>"
                        data-number-id="<?php echo htmlspecialchars($row['number_id']); ?>"
                        data-full-name="<?php echo htmlspecialchars($fullName); ?>"
                        data-email="<?php echo htmlspecialchars($row['email']); ?>"
                        data-institutional-email="<?php echo htmlspecialchars($nuevoCorreo); ?>"
                        data-department="<?= htmlspecialchars($row['departamento']) ?>"
                        data-headquarters="<?= htmlspecialchars($row['headquarters']) ?>"
                        data-program="<?= htmlspecialchars($row['program']) ?>"
                        data-mode="<?= htmlspecialchars($row['mode']) ?>">
                        <td><?php echo htmlspecialchars($row['typeID']); ?></td>
                        <td><?php echo htmlspecialchars($row['number_id']); ?></td>
                        <td style="width: 300px; min-width: 300px; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($fullName); ?></td>
                        <td><?php echo htmlspecialchars($row['mode']); ?></td>
                        <td>
                            <input type="checkbox" class="form-check-input usuario-checkbox"
                                style="width: 25px; height: 25px; appearance: none; background-color: white; border: 2px solid #ec008c; cursor: pointer; position: relative;"
                                onclick="this.style.backgroundColor = this.checked ? 'magenta' : 'white'">
                        </td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($nuevoCorreo); ?></td>

                        <td>
                            <?php
                            $departamento = htmlspecialchars($row['departamento']);
                            if ($departamento === 'CUNDINAMARCA') {
                                echo "<button class='btn bg-lime-light w-100'><b>{$departamento}</b></button>"; // Botón verde para CUNDINAMARCA
                            } elseif ($departamento === 'BOYACÁ') {
                                echo "<button class='btn bg-indigo-light w-100'><b>{$departamento}</b></button>"; // Botón azul para BOYACÁ
                            } else {
                                echo "<span>{$departamento}</span>"; // Texto normal para otros valores
                            }
                            ?>
                        </td>
                        <td><b class="text-center"><?php echo htmlspecialchars($row['headquarters']); ?></b></td>
                        <td><?php echo htmlspecialchars($row['program']); ?></td>
                        <td><?php echo htmlspecialchars($row['level']); ?></td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Selecciona todos los checkboxes con la clase 'usuario-checkbox'
        const checkboxes = document.querySelectorAll(".usuario-checkbox");
        const contador = document.getElementById("contador");

        function actualizarContador() {
            // Cuenta los checkboxes que están marcados
            const seleccionados = document.querySelectorAll(".usuario-checkbox:checked").length;
            contador.textContent = seleccionados;
        }

        // Agrega un evento a cada checkbox para actualizar el contador
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener("change", actualizarContador);
        });

    });
    document.getElementById('matricularSeleccionados').addEventListener('click', confirmBulkEnrollment);

    function confirmBulkEnrollment(event) {
        const selectedCheckboxes = document.querySelectorAll('#listaInscritos tbody input[type="checkbox"]:checked');
        if (selectedCheckboxes.length === 0) {
            Swal.fire('Error', 'Por favor seleccione al menos un estudiante', 'error');
            return;
        }

        Swal.fire({
            title: 'Confirmar matrícula',
            text: `¿Está seguro que desea matricular a ${selectedCheckboxes.length} estudiantes seleccionados?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, matricular',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loader con SweetAlert2
                Swal.fire({
                    title: 'Procesando matrículas',
                    html: `<div>
                    Procesando: <b>0</b> de ${selectedCheckboxes.length}<br>
                    Por favor espere...</div>`,

                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const promises = [];
                const errors = [];
                let successes = 0;
                let processed = 0;

                selectedCheckboxes.forEach(checkbox => {
                    const row = checkbox.closest('tr');
                    const formData = getFormDataFromRow(row);

                    const promise = fetch('components/registerMoodle/enroll_user.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(formData)
                        })
                        .then(response => response.json())
                        .then(data => {
                            processed++;
                            // Actualizar el contador en el loader
                            const content = Swal.getHtmlContainer();
                            if (content) {
                                const b = content.querySelector('b');
                                if (b) {
                                    b.textContent = processed;
                                }
                            }

                            if (data.success) {
                                successes++;
                                updateEnrollmentStatus(formData.number_id, true);

                                // Enviar correo de notificación
                                const emailData = {
                                    destinatario: formData.email,
                                    program: formData.program,
                                    first_name: formData.full_name,
                                    usuario: formData.email, // Asumiendo que el email es el usuario
                                    password: formData.password // Asumiendo que la contraseña es la misma que se usa para la matrícula
                                };

                                fetch('components/registerMoodle/send_email.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json'
                                        },
                                        body: JSON.stringify({
                                            email: formData.email,
                                            program: formData.program,
                                            first_name: formData.full_name,
                                            usuario: formData.email,
                                            password: formData.password
                                        })
                                    })
                                    .then(async response => {
                                        const text = await response.text();
                                        try {
                                            return JSON.parse(text);
                                        } catch (e) {
                                            console.error('Respuesta no válida:', text);
                                            throw new Error('Respuesta del servidor no válida');
                                        }
                                    })
                                    .then(data => {
                                        if (!data.success) {
                                            throw new Error(data.message || 'Error desconocido');
                                        }
                                        Swal.fire({
                                            title: '¡Éxito!',
                                            text: 'Correo enviado correctamente',
                                            icon: 'success',
                                            timer: 3000
                                        });
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        Swal.fire({
                                            title: 'Error',
                                            text: error.message || 'Error al enviar el correo',
                                            icon: 'error'
                                        });
                                    });

                            } else {
                                errors.push({
                                    student: formData.number_id,
                                    message: data.message || 'Error desconocido'
                                });
                                updateEnrollmentStatus(formData.number_id, false);
                            }
                        })
                        .catch(error => {
                            processed++;
                            errors.push({
                                student: formData.number_id,
                                message: 'Error de conexión o servidor'
                            });
                            updateEnrollmentStatus(formData.number_id, false);
                        });

                    promises.push(promise);
                });

                Promise.all(promises).then(() => {
                    let message = `Matrículas completadas: ${successes} exitosas.`;
                    if (errors.length > 0) {
                        message += '<br><br>Errores:<br>';
                        errors.forEach(err => {
                            message += `• ${err.student}: ${err.message}<br>`;
                        });
                    }

                    Swal.fire({
                        title: 'Resultado de la matrícula',
                        html: message,
                        icon: errors.length > 0 ? 'warning' : 'success',
                        confirmButtonText: 'Entendido'
                    })
                });
            }
        });
    }

    function getFormDataFromRow(row) {
        const studentId = row.dataset.numberId;

        const getCourseData = (prefix) => {
            const select = document.getElementById(prefix);
            if (!select) {
                console.error(`Select no encontrado: ${prefix}`);
                return {
                    id: '0',
                    name: 'No seleccionado'
                };
            }
            const option = select.options[select.selectedIndex];
            const fullText = option.text;
            const id = option.value;
            const name = fullText.split(' - ').slice(1).join(' - ').trim();

            console.log(`${prefix}:`, {
                id,
                name
            }); // Debug
            return {
                id,
                name
            };
        };

        const formData = {
            type_id: row.dataset.typeId,
            number_id: studentId,
            full_name: row.dataset.fullName,
            email: row.dataset.email,
            institutional_email: row.dataset.institutionalEmail,
            department: row.dataset.department,
            headquarters: row.dataset.headquarters,
            program: row.dataset.program,
            mode: row.dataset.mode,
            password: 'UTt@2025!',
            id_bootcamp: getCourseData('bootcamp').id,
            bootcamp_name: getCourseData('bootcamp').name,
            id_leveling_english: getCourseData('ingles').id,
            leveling_english_name: getCourseData('ingles').name,
            id_english_code: getCourseData('english_code').id,
            english_code_name: getCourseData('english_code').name,
            id_skills: getCourseData('skills').id,
            skills_name: getCourseData('skills').name
        };

        console.log('FormData:', formData); // Debug
        return formData;
    }

    function updateEnrollmentStatus(studentId, success) {
        const row = document.querySelector(`tr[data-number-id="${studentId}"]`);
        if (row) {
            const checkbox = row.querySelector('input[type="checkbox"]');
            checkbox.disabled = true;
            checkbox.checked = success;
            row.style.backgroundColor = success ? '#d4edda' : '#f8d7da';
        }
    }
</script>
</body>

</html>