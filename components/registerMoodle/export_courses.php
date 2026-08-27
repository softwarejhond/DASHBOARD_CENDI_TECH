<?php
require __DIR__ . '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include __DIR__ . '/../moodle_api.php';
$courses_data = getCoursesB();

function exportCoursesToExcel($courses_data) {
    // Crear una nueva hoja de cálculo
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Establecer los encabezados
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Nombre del Curso');
    
    // Estilo para los encabezados
    $sheet->getStyle('A1:B1')->getFont()->setBold(true);
    
    // Iniciar desde la fila 2 para los datos
    $row = 2;
    
    // Llenar los datos
    foreach ($courses_data as $course) {
        $sheet->setCellValue('A' . $row, $course['id']);
        $sheet->setCellValue('B' . $row, $course['fullname']);
        $row++;
    }
    
    // Autoajustar el ancho de las columnas
    foreach(range('A','B') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Crear el archivo Excel
    $writer = new Xlsx($spreadsheet);
    
    // Establecer headers para la descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="cursos_moodle.xlsx"');
    header('Cache-Control: max-age=0');
    
    // Guardar el archivo
    $writer->save('php://output');
    exit;
}

// Para usar la función, primero obtén los cursos y luego llama a la función de exportación
if (isset($_POST['export'])) {
    $courses_data = getCourses();
    exportCoursesToExcel($courses_data);
}
