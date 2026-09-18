<?php
/**
 * Logger con rotación diaria para los cron.
 * Escribe en logs/<prefijo>-YYYY-MM-DD.log y borra los archivos de más de N días.
 *
 * Uso:
 *   require_once __DIR__ . '/components/cron/cron_log.php';
 *   cronLogInit('cron_notas');
 *   cronLog('mensaje');
 */

$GLOBALS['CRON_LOG_FILE'] = null;

function cronLogInit(string $prefix, int $retentionDays = 14): string
{
    $dir = dirname(__DIR__, 2) . '/logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    $GLOBALS['CRON_LOG_FILE'] = $dir . '/' . $prefix . '-' . date('Y-m-d') . '.log';

    $limite = time() - ($retentionDays * 86400);
    foreach (glob($dir . '/' . $prefix . '-*.log') ?: [] as $viejo) {
        if (is_file($viejo) && filemtime($viejo) < $limite) {
            @unlink($viejo);
        }
    }

    return $GLOBALS['CRON_LOG_FILE'];
}

function cronLog(string $mensaje): void
{
    $file = $GLOBALS['CRON_LOG_FILE'] ?? null;
    if (!$file) {
        return;
    }

    @file_put_contents($file, date('Y-m-d H:i:s') . ' - ' . $mensaje . "\n", FILE_APPEND);
}
