<?php
/**
 * Plantilla HTML del diploma CENDI Tech.
 *
 * Recibe los datos ya normalizados y los assets (imágenes en base64 con sus
 * dimensiones calculadas) y devuelve el HTML que consume Dompdf.
 */

if (!function_exists('diplomaTemplate')) {
    function diplomaTemplate(array $d, array $assets): string
    {
        $e = static function ($valor): string {
            return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
        };

        $logo   = $assets['logo'];
        $fondo  = $assets['fondo'];
        $firmaR = $assets['firma_rectora'];
        $firmaS = $assets['firma_secretaria'];

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page { size: A4 landscape; margin: 0; }
    html, body { margin: 0; padding: 0; }
    body { font-family: 'Lato', 'DejaVu Sans', sans-serif; color: #333333; font-size: 11pt; }

    .marco { position: fixed; border-style: solid; }
    .marco-exterior {
        top: 5mm; left: 5mm; width: 287mm; height: 200mm;
        border-width: 1.1mm; border-color: #1B2A5B;
    }
    .marco-interior {
        top: 7.6mm; left: 7.6mm; width: 281.8mm; height: 194.8mm;
        border-width: 0.3mm; border-color: #EF7F0E;
    }

    .pagina {
        width: 297mm; height: 209.8mm;
        background-image: url(<?= $fondo['src'] ?>);
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center center;
    }
    .contenido { padding: 13mm 20mm 8mm 20mm; text-align: center; }

    .logo { width: <?= $logo['width'] ?>mm; height: <?= $logo['height'] ?>mm; }
    .institucion {
        font-family: 'Montserrat', 'DejaVu Sans', sans-serif;
        font-weight: 600; font-size: 17pt; letter-spacing: 0.4pt;
        color: #1B2A5B; margin-top: 3mm;
    }
    .filete { width: 52mm; height: 1.1mm; background-color: #EF7F0E; margin: 2.5mm auto 0 auto; }

    .lead { font-size: 12pt; color: #5A5A5A; margin-top: 5mm; }
    .nombre {
        font-family: 'Montserrat', 'DejaVu Sans', sans-serif;
        font-weight: 700; font-size: 26pt; color: #1B2A5B;
        margin-top: 2mm; letter-spacing: 0.3pt;
    }
    .banda-programa {
        width: 232mm; margin: 2.5mm auto 0 auto; padding: 3mm 0;
        background-color: #FFF4E5;
        border-top: 0.3mm solid #F3D2A6; border-bottom: 0.3mm solid #F3D2A6;
        border-radius: 2mm;
    }
    .programa {
        font-family: 'Montserrat', 'DejaVu Sans', sans-serif;
        font-weight: 700; font-size: 26pt; color: #EF7F0E;
    }

    .meta { font-size: 11pt; color: #4A4A4A; margin-top: 4mm; }
    .meta b { color: #1B2A5B; }

    .parrafo {
        width: 205mm; margin: 4mm auto 0 auto;
        font-size: 10.5pt; color: #5A5A5A; line-height: 1.5;
    }
    .expide { font-size: 11pt; color: #4A4A4A; margin-top: 4mm; }

    .firmas { width: 190mm; margin: 4mm auto 0 auto; border-collapse: collapse; }
    .firmas td { width: 50%; vertical-align: bottom; padding: 0 5mm; text-align: center; }
    .firma-caja { width: 66mm; margin: 0 auto; text-align: center; }
    .firma-img { vertical-align: bottom; }
    .firma-linea { border-top: 0.4mm solid #1B2A5B; width: 66mm; margin: 1.5mm auto 0 auto; }
    .firma-nombre {
        font-family: 'Montserrat', 'DejaVu Sans', sans-serif;
        font-weight: 600; font-size: 10.5pt; color: #1B2A5B;
        margin-top: 1.5mm; text-align: center;
    }
    .firma-cargo { font-size: 9pt; color: #6B6B6B; margin-top: 0.5mm; text-align: center; }

    .pie { font-size: 8.5pt; color: #8A8A8A; margin-top: 5mm; letter-spacing: 0.4pt; }
</style>
</head>
<body>
    <div class="marco marco-exterior"></div>
    <div class="marco marco-interior"></div>

    <div class="pagina">
        <div class="contenido">
        <img class="logo" src="<?= $logo['src'] ?>" alt="CENDI">
        <div class="institucion"><?= $e($d['institucion']) ?></div>
        <div class="filete"></div>

        <div class="lead">El certificado es otorgado a</div>
        <div class="nombre"><?= $e($d['nombre']) ?></div>
        <div class="lead">Por completar el curso</div>

        <div class="banda-programa">
            <div class="programa"><?= $e($d['programa']) ?></div>
        </div>

        <div class="meta">
            Con número de documento: <b><?= $e($d['cedula']) ?></b>
            &nbsp;&middot;&nbsp;
            Duración: <b><?= $e($d['duracion']) ?></b>
        </div>

        <div class="parrafo">
            Con este reconocimiento destacamos su esfuerzo, creatividad y aporte al
            fortalecimiento de las competencias digitales que impulsan el futuro del
            talento tecnológico en Colombia.
        </div>

        <div class="expide">
            Se expide el <?= $e($d['fecha']) ?> en la ciudad de <?= $e($d['ciudad']) ?>
        </div>

        <table class="firmas">
            <tr>
                <td>
                    <div class="firma-caja">
                        <img class="firma-img" src="<?= $firmaR['src'] ?>" style="width:<?= $firmaR['width'] ?>mm;height:<?= $firmaR['height'] ?>mm" alt="">
                        <div class="firma-linea"></div>
                        <div class="firma-nombre"><?= $e($d['rectora']) ?></div>
                        <div class="firma-cargo"><?= $e($d['rectora_cargo']) ?></div>
                    </div>
                </td>
                <td>
                    <div class="firma-caja">
                        <img class="firma-img" src="<?= $firmaS['src'] ?>" style="width:<?= $firmaS['width'] ?>mm;height:<?= $firmaS['height'] ?>mm" alt="">
                        <div class="firma-linea"></div>
                        <div class="firma-nombre"><?= $e($d['secretaria']) ?></div>
                        <div class="firma-cargo"><?= $e($d['secretaria_cargo']) ?></div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="pie"><?= $e($d['sitio_web']) ?><?php if (!empty($d['token'])): ?> &middot; <?= $e($d['token']) ?><?php endif; ?></div>
        </div>
    </div>
</body>
</html>
        <?php
        return (string) ob_get_clean();
    }
}
