<?php
// =====================================================================
// Exportación a Excel del listado de inscritos (user_register + matrícula).
// Misma información que el listado de inscritos, con fecha de inscripción
// (user_register.creationDate) y fecha de matrícula (enrollments.created_at)
// en formato numérico de Excel (serial).
// =====================================================================

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

error_reporting(E_ALL);
ini_set('display_errors', 0);

if (isset($_GET['action']) && $_GET['action'] === 'export') {
    exportListadoInscritos($conn);
    exit;
}

function fechaAExcel($fecha)
{
    if (empty($fecha) || strpos($fecha, '0000-00-00') === 0) {
        return '';
    }
    try {
        return (new DateTime($fecha))->diff(new DateTime('1899-12-30'))->days;
    } catch (Exception $e) {
        return '';
    }
}

function limpiarTelefono($v)
{
    $v = trim((string) $v);
    if ($v === '') {
        return '';
    }
    $v = preg_replace('/^\s*\+\s*57\s*/', '', $v);
    $digitos = preg_replace('/\D+/', '', $v);
    if (strlen($digitos) === 12 && strpos($digitos, '57') === 0) {
        $digitos = substr($digitos, 2);
    }
    return $digitos;
}

function splitComuna($v)
{
    $v = trim((string) $v);
    if ($v === '') {
        return ['', ''];
    }
    $partes = explode(' - ', $v, 2);
    if (count($partes) === 2) {
        return [trim($partes[0]), trim($partes[1])];
    }
    if (preg_match('/^(\d+)\s*[-.]?\s*(.+)$/', $v, $m)) {
        return [$m[1], trim($m[2])];
    }
    return ['', $v];
}

function matStatusLabel($status)
{
    if (empty($status)) {
        return '';
    }
    $map = [
        'enrolled' => 'Matriculado',
        'pending'  => 'Pendiente',
        'active'   => 'Activo',
    ];
    return isset($map[$status]) ? $map[$status] : $status;
}

function cursoLabel($name, $code)
{
    if (empty($name)) {
        return '';
    }
    return $name . (!empty($code) ? ' (' . $code . ')' : '');
}

function exportListadoInscritos($conn)
{
    $sql = "SELECT
        ur.typeID,
        ur.number_id,
        ur.first_name, ur.second_name, ur.first_last, ur.second_last,
        ur.gender,
        TIMESTAMPDIFF(YEAR, ur.birthdate, CURDATE()) AS age,
        ur.birthdate,
        ur.nationality,
        ur.first_phone,
        ur.second_phone,
        ur.email,
        ur.email_verified,
        ur.emergency_contact_name,
        ur.emergency_contact_number,
        d.departamento AS departamento_nombre,
        m.nom_municipio AS municipio_nombre,
        ur.address,
        ur.residence_area,
        ur.comuna_corregimiento,
        ur.barrio,
        ur.program,
        ur.mode,
        ur.creationDate,
        e.program_name AS mat_program_name,
        e.status AS mat_status,
        sc.serie AS mat_serie,
        sc.codigo_tecnico AS mat_codigo_tecnico,
        c1.course_name AS tecnico_name, c1.course_code AS tecnico_code,
        c2.course_name AS ingles_name, c2.course_code AS ingles_code,
        c3.course_name AS habilidades_name, c3.course_code AS habilidades_code,
        e.institutional_email AS mat_institutional_email,
        e.username AS mat_username,
        e.created_at AS mat_created_at,
        acu.guardian_full_name,
        acu.guardian_document,
        acu.guardian_phone,
        acu.guardian_email
        FROM user_register ur
        LEFT JOIN departamentos d ON ur.department = d.id_departamento
        LEFT JOIN municipios m ON ur.municipality = m.cod_municipio
        LEFT JOIN (
            SELECT e.* FROM enrollments e
            INNER JOIN (
                SELECT number_id, MAX(id) AS max_id FROM enrollments GROUP BY number_id
            ) em ON e.id = em.max_id
        ) e ON e.number_id = ur.number_id
        LEFT JOIN sets_cursos sc ON e.set_id = sc.id
        LEFT JOIN cursos c1 ON e.course_tecnico_id = c1.course_id
        LEFT JOIN cursos c2 ON e.course_ingles_id = c2.course_id
        LEFT JOIN cursos c3 ON e.course_habilidades_id = c3.course_id
        LEFT JOIN (
            SELECT a.* FROM acudientes a
            INNER JOIN (
                SELECT number_id, MAX(id) AS max_id FROM acudientes GROUP BY number_id
            ) am ON a.id = am.max_id
        ) acu ON acu.number_id = ur.number_id
        ORDER BY ur.number_id ASC";

    $result = $conn->query($sql);
    $data = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $fullName = trim(implode(' ', array_filter([
                $row['first_name'], $row['second_name'], $row['first_last'], $row['second_last']
            ], 'strlen')));

            [$comunaNumero, $comunaNombre] = splitComuna($row['comuna_corregimiento']);

            $data[] = [
                'Tipo ID'                  => $row['typeID'],
                'Número'                   => $row['number_id'],
                'Nombre'                   => $fullName,
                'Género'                   => $row['gender'],
                'Edad'                     => ($row['age'] !== null) ? (int) $row['age'] : '',
                'Fecha de nacimiento'      => fechaAExcel($row['birthdate']),
                'Nacionalidad'             => $row['nationality'],
                'Teléfono 1'               => limpiarTelefono($row['first_phone']),
                'Teléfono 2'               => limpiarTelefono($row['second_phone']),
                'Email'                    => $row['email'],
                'Email verificado'         => ($row['email_verified'] == 1) ? 'Verificado' : 'Sin verificar',
                'Contacto emergencia'      => $row['emergency_contact_name'],
                'Tel. emergencia'          => limpiarTelefono($row['emergency_contact_number']),
                'Departamento'             => $row['departamento_nombre'],
                'Municipio'                => $row['municipio_nombre'],
                'Dirección'                => $row['address'],
                'Área'                     => $row['residence_area'],
                'Número de comuna'         => $comunaNumero,
                'Nombre de comuna'         => $comunaNombre,
                'Barrio/Vereda'            => $row['barrio'],
                'Programa'                 => $row['program'],
                'Modalidad'                => $row['mode'],
                'Fecha de inscripción'     => fechaAExcel($row['creationDate']),
                'Matrícula: Programa'      => $row['mat_program_name'],
                'Matrícula: Estado'        => matStatusLabel($row['mat_status']),
                'Serie'                    => $row['mat_serie'],
                'Código técnico'           => $row['mat_codigo_tecnico'],
                'Curso técnico'            => cursoLabel($row['tecnico_name'], $row['tecnico_code']),
                'Curso inglés'             => cursoLabel($row['ingles_name'], $row['ingles_code']),
                'Habilidades'              => cursoLabel($row['habilidades_name'], $row['habilidades_code']),
                'Correo institucional'     => $row['mat_institutional_email'],
                'Usuario Moodle'           => $row['mat_username'],
                'Fecha de matrícula'       => fechaAExcel($row['mat_created_at']),
                'Acudiente'                => $row['guardian_full_name'],
                'Doc. acudiente'           => $row['guardian_document'],
                'Tel. acudiente'           => limpiarTelefono($row['guardian_phone']),
                'Email acudiente'          => $row['guardian_email'],
            ];
        }
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Listado de inscritos');

    $headers = array_keys($data[0] ?? []);
    $sheet->fromArray($headers, null, 'A1');

    $rowIndex = 2;
    foreach ($data as $row) {
        $sheet->fromArray(array_values($row), null, "A{$rowIndex}");
        $rowIndex++;
    }

    if (!empty($headers)) {
        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));

        // Encabezados en negrita con color
        $sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $lastColumn . '1')
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFDAB9');

        // Ajustar ancho de columnas según el texto del encabezado
        foreach ($headers as $colIndex => $headerText) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $width = mb_strlen($headerText) + 2;
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        // Fijar la primera fila (encabezados)
        $sheet->freezePane('A2');
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="listado_inscritos_' . date('Y-m-d') . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
