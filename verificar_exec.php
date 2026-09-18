<?php
// Verificador temporal de exec / disable_functions.
// Web:  abrir /dashboard/verificar_exec.php con sesión iniciada.
// CLI:  php verificar_exec.php
// BORRAR este archivo después de usarlo.

if (PHP_SAPI !== 'cli') {
    session_start();
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        http_response_code(401);
        exit('No autorizado.');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

echo 'PHP SAPI: ' . PHP_SAPI . "\n";
echo 'PHP version: ' . PHP_VERSION . "\n";
echo 'function_exists(exec): ' . var_export(function_exists('exec'), true) . "\n";

$disable = (string) ini_get('disable_functions');
echo 'disable_functions: ' . ($disable !== '' ? $disable : '(ninguna)') . "\n";

if (function_exists('exec')) {
    $out = [];
    $code = 0;
    @exec('echo exec_ok', $out, $code);
    echo 'Prueba exec("echo exec_ok"): code=' . $code . ' salida=' . implode(' ', $out) . "\n";

    if ($code !== 0 || empty($out)) {
        echo "AVISO: exec existe pero no produjo salida; puede estar restringido por el sistema.\n";
    } else {
        echo "RESULTADO: exec está HABILITADO.\n";
    }
} else {
    echo "RESULTADO: exec está DESHABILITADO.\n";
}
