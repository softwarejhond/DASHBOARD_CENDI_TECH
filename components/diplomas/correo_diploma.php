<?php
/**
 * Plantilla HTML del correo con el que se envía el diploma CENDI Tech.
 * La usa cron_generar_diplomas.php y el preview_correo.php.
 */

if (!function_exists('correoDiplomaHtml')) {
    function correoDiplomaHtml(string $nombre, string $programa): string
    {
        $e = static function ($valor): string {
            return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
        };

        $anio = date('Y');

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tu diploma CENDI Tech</title>
</head>
<body style="margin:0;padding:0;background:#eef1f6;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef1f6;">
        <tr>
            <td align="center" style="padding:24px 12px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 2px 12px rgba(15,23,42,.08);">

                    <tr>
                        <td align="center" style="background-color:#181E93;padding:22px 24px;">
                            <img src="https://cenditech.com.co/dashboard/img/cendi_blanco.png" alt="CENDI Tech" width="160" style="display:block;border:0;width:160px;max-width:160px;height:auto;">
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 28px 6px;text-align:center;">
                            <h1 style="margin:0 0 6px;font-size:22px;line-height:1.3;color:#181E93;">¡Felicitaciones, <?= $e($nombre) ?>!</h1>
                            <p style="margin:0;color:#64748b;font-size:14px;">Completaste tu programa en CENDI Tech</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 28px 4px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fef5e6;border:1px dashed #F9B233;border-radius:12px;">
                                <tr>
                                    <td style="padding:16px;text-align:center;">
                                        <div style="font-size:12px;text-transform:uppercase;letter-spacing:.06em;color:#b45309;font-weight:700;">Programa</div>
                                        <div style="font-size:20px;font-weight:800;color:#EF7F0E;margin-top:4px;"><?= $e($programa) ?></div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 28px 4px;font-size:14px;line-height:1.65;color:#334155;">
                            <p style="margin:0 0 12px;">Adjuntamos tu <b>diploma en formato PDF</b>. Guárdalo y compártelo: incluye un <b>código de verificación único</b> que lo acompaña.</p>
                            <p style="margin:0;">Si tienes alguna duda escríbenos a <a href="mailto:servicioalcliente.ut2@cendi.edu.co" style="color:#ec008c;text-decoration:none;font-weight:600;">servicioalcliente.ut2@cendi.edu.co</a>.</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:22px 28px 26px;text-align:center;">
                            <span style="display:inline-block;background:#00976a;color:#ffffff;font-weight:700;font-size:14px;padding:11px 26px;border-radius:999px;">Equipo CENDI Tech</span>
                        </td>
                    </tr>

                    <tr>
                        <td style="background:#f8fafc;padding:16px 28px;text-align:center;font-size:11px;line-height:1.6;color:#94a3b8;">
                            Este es un mensaje automático, por favor no respondas a este correo.<br>
                            &copy; <?= $e($anio) ?> CENDI Tech &middot; cendiacademico.edu.co
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
        <?php
        return (string) ob_get_clean();
    }
}
