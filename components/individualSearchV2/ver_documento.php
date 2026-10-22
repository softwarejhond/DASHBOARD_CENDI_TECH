<?php
// =====================================================================
// Sirve el PDF del documento de identidad de un estudiante.
// En producción la carpeta files/idFilesPDF está junto a dashboard;
// en local vive dentro de CENDITECH-FORM-REGISTER.
// =====================================================================

session_start();

function isv2_doc_error($code, $message)
{
    http_response_code($code);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"></head>'
        . '<body style="margin:0;font-family:system-ui,-apple-system,Segoe UI,sans-serif;'
        . 'display:flex;align-items:center;justify-content:center;height:100vh;background:#f8fafc;color:#64748b">'
        . '<div style="text-align:center;padding:2rem">'
        . '<div style="font-size:2rem;color:#181E93;margin-bottom:.5rem">&#128196;</div>'
        . '<p style="margin:0">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>'
        . '</div></body></html>';
    exit;
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    isv2_doc_error(401, 'No autorizado.');
}

$number_id = isset($_GET['number_id']) ? trim($_GET['number_id']) : '';
$tipo = isset($_GET['tipo']) ? strtolower(trim($_GET['tipo'])) : 'personal';
$safeNumberId = preg_replace('/[^0-9A-Za-z]/', '', $number_id);

if ($safeNumberId === '') {
    isv2_doc_error(400, 'Número de identificación inválido.');
}

$prefix = ($tipo === 'acudiente') ? 'CC_acudiente_' : 'CC_';
$fileName = $prefix . $safeNumberId . '.pdf';

// El acudiente guarda el nombre exacto del archivo en guardian_document_file.
$archivo = isset($_GET['archivo']) ? basename(trim((string) $_GET['archivo'])) : '';
if (
    $tipo === 'acudiente' && $archivo !== ''
    && preg_match('/^CC_acudiente_[0-9A-Za-z]+\.pdf$/i', $archivo)
    && strpos($archivo, '_' . $safeNumberId . '.pdf') !== false
) {
    $fileName = $archivo;
}

$candidates = [
    __DIR__ . '/../../../files/idFilesPDF/' . $fileName,
    __DIR__ . '/../../../CENDITECH-FORM-REGISTER/files/idFilesPDF/' . $fileName,
    __DIR__ . '/../../files/idFilesPDF/' . $fileName,
];

$filePath = null;
foreach ($candidates as $candidate) {
    if (is_file($candidate)) {
        $filePath = $candidate;
        break;
    }
}

if ($filePath === null) {
    isv2_doc_error(404, 'No se encontró el documento de identidad para este estudiante.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $fileName . '"');
header('Content-Length: ' . filesize($filePath));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=0, must-revalidate');

readfile($filePath);
exit;
