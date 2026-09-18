<?php
// =====================================================================
// Exportación a Excel de datos académicos: cursos, notas y certificación.
// Basado en exportListadoInscritos.php, pero con la información académica
// (programa, matrícula, cursos y notas desde notas_estudiantes) y el estado
// del certificado desde diplomas_emitidos.
// =====================================================================

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

error_reporting(E_ALL);
ini_set('display_errors', 0);

const DIPLOMAS_BASE_URL = 'https://cenditech.com.co/dashboard/diplomas/';

if (isset($_GET['action']) && $_GET['action'] === 'export') {
    exportDatosAcademicos($conn);
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

function notaLabel($presento, $nota)
{
    if (!$presento) {
        return 'Sin presentar';
    }
    return number_format((float) $nota, 2, ',', '.');
}

function certificacionLabel($estado)
{
    switch ($estado) {
        case 'enviado':
            return 'Sí';
        case 'generado':
            return 'Generado (sin enviar)';
        case 'error':
            return 'Error de envío';
        default:
            return 'No';
    }
}

function exportDatosAcademicos($conn)
{
    $sql = "SELECT
        ur.typeID,
        ur.number_id,
        ur.first_name, ur.second_name, ur.first_last, ur.second_last,
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
        e.created_at AS mat_created_at,
        n.nota_tecnico, n.presento_tecnico,
        n.nota_ingles, n.presento_ingles,
        n.nota_habilidades, n.presento_habilidades,
        n.nota_final, n.fecha_fin_cursos,
        d.estado AS cert_estado, d.token AS cert_token,
        d.fecha_generacion AS cert_fecha, d.fecha_envio AS cert_envio
        FROM user_register ur
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
        LEFT JOIN notas_estudiantes n ON n.number_id = ur.number_id
        LEFT JOIN diplomas_emitidos d ON d.number_id = ur.number_id
        ORDER BY ur.number_id ASC";

    $result = $conn->query($sql);
    $data = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $fullName = trim(implode(' ', array_filter([
                $row['first_name'], $row['second_name'], $row['first_last'], $row['second_last']
            ], 'strlen')));

            $certificado = !empty($row['cert_token']);
            $enlace = $certificado ? DIPLOMAS_BASE_URL . $row['cert_token'] . '.pdf' : '';

            $data[] = [
                'Tipo ID'              => $row['typeID'],
                'Número'               => $row['number_id'],
                'Nombre'               => $fullName,
                'Programa'             => $row['program'],
                'Modalidad'            => $row['mode'],
                'Fecha de inscripción' => fechaAExcel($row['creationDate']),
                'Matrícula: Programa'  => $row['mat_program_name'],
                'Matrícula: Estado'    => matStatusLabel($row['mat_status']),
                'Serie'                => $row['mat_serie'],
                'Código técnico'       => $row['mat_codigo_tecnico'],
                'Fecha de matrícula'   => fechaAExcel($row['mat_created_at']),
                'Curso técnico'        => cursoLabel($row['tecnico_name'], $row['tecnico_code']),
                'Nota técnico'         => notaLabel($row['presento_tecnico'], $row['nota_tecnico']),
                'Curso inglés'         => cursoLabel($row['ingles_name'], $row['ingles_code']),
                'Nota inglés'          => notaLabel($row['presento_ingles'], $row['nota_ingles']),
                'Curso habilidades'    => cursoLabel($row['habilidades_name'], $row['habilidades_code']),
                'Nota habilidades'     => notaLabel($row['presento_habilidades'], $row['nota_habilidades']),
                'Nota final'           => ($row['nota_final'] !== null) ? number_format((float) $row['nota_final'], 2, ',', '.') : '',
                'Certificación'        => certificacionLabel($row['cert_estado']),
                'Enlace certificado'   => $enlace,
                'Fecha de certificado' => fechaAExcel($row['cert_fecha']),
                'Fecha fin de cursos'  => fechaAExcel($row['fecha_fin_cursos']),
            ];
        }
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Datos académicos');

    $headers = array_keys($data[0] ?? []);
    $sheet->fromArray($headers, null, 'A1');

    $rowIndex = 2;
    foreach ($data as $row) {
        $sheet->fromArray(array_values($row), null, "A{$rowIndex}");
        $rowIndex++;
    }
    $lastRow = $rowIndex - 1;

    if (!empty($headers)) {
        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));

        $sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $lastColumn . '1')
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFDAB9');

        foreach ($headers as $colIndex => $headerText) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $width = mb_strlen($headerText) + 2;
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->freezePane('A2');

        $linkIndex = array_search('Enlace certificado', $headers, true);
        if ($linkIndex !== false && $lastRow >= 2) {
            $linkColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($linkIndex + 1);
            for ($r = 2; $r <= $lastRow; $r++) {
                $cell = $sheet->getCell($linkColumn . $r);
                $url = (string) $cell->getValue();
                if ($url !== '') {
                    $cell->getHyperlink()->setUrl($url);
                    $sheet->getStyle($linkColumn . $r)->getFont()->getColor()->setARGB('FF0563C1');
                    $sheet->getStyle($linkColumn . $r)->getFont()->setUnderline(true);
                }
            }
        }
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="datos_academicos_' . date('Y-m-d') . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
