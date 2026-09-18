<?php
/**
 * Genera una variante de diploma por cada programa de CENDI Tech.
 *
 * Ejecutar por consola:  php components/diplomas/generate_all.php
 * O abrir en el navegador: /components/diplomas/generate_all.php
 */

require_once __DIR__ . '/DiplomaGenerator.php';

$esCli = PHP_SAPI === 'cli';

$datosBase = [
    'nombre'   => 'NOMBRE DEL ESTUDIANTE',
    'cedula'   => '000000000',
    'duracion' => '48 Horas',
    'ciudad'   => 'Medellín',
    'fecha'    => DiplomaGenerator::fechaEspanol(),
];

$directorioSalida = dirname(__DIR__, 2) . '/diplomas';
$generador = new DiplomaGenerator();

$resultados = [];
$errores = [];

foreach (DiplomaGenerator::programas() as $programa) {
    $archivo = $directorioSalida . '/diploma_' . DiplomaGenerator::slug($programa) . '.pdf';

    try {
        $generador->save($datosBase + ['programa' => $programa], $archivo);
        $resultados[] = ['programa' => $programa, 'archivo' => $archivo, 'peso' => filesize($archivo)];
    } catch (Throwable $e) {
        $errores[] = ['programa' => $programa, 'error' => $e->getMessage()];
    }
}

if ($esCli) {
    foreach ($resultados as $r) {
        echo "OK  {$r['programa']}  ->  {$r['archivo']}  (" . number_format($r['peso'] / 1024, 1) . " KB)\n";
    }
    foreach ($errores as $e) {
        echo "ERR {$e['programa']}  ->  {$e['error']}\n";
    }
    echo count($resultados) . " generados, " . count($errores) . " errores.\n";
    exit($errores ? 1 : 0);
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'generados' => $resultados,
    'errores'   => $errores,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
