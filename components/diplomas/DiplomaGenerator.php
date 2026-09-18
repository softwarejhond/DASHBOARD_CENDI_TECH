<?php
/**
 * Generador de diplomas CENDI Tech con Dompdf.
 *
 * Uso:
 *   $gen = new DiplomaGenerator();
 *   $gen->save(['nombre' => '...', 'cedula' => '...', 'programa' => '...'], 'diplomas/salida.pdf');
 *   // o para obtener el binario:
 *   $pdf = $gen->render(['programa' => 'Ciberseguridad']);
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/template.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class DiplomaGenerator
{
    /** Programas disponibles (una variante de diploma por cada uno). */
    private const PROGRAMAS = [
        'Análisis de datos',
        'Ciberseguridad',
        'Inteligencia Artificial',
        'Programación',
        'BlockChain',
        'Computación en la nube',
        'Robótica y automatización',
        'Internet de las cosas - IoT',
    ];

    /** Archivos de fuentes: [familia, peso, estilo, archivo]. */
    private const FUENTES = [
        ['Montserrat', '400', 'normal', 'Montserrat-Regular.ttf'],
        ['Montserrat', '600', 'normal', 'Montserrat-SemiBold.ttf'],
        ['Montserrat', '700', 'normal', 'Montserrat-Bold.ttf'],
        ['Lato', '400', 'normal', 'Lato-Regular.ttf'],
        ['Lato', '700', 'normal', 'Lato-Bold.ttf'],
        ['Lato', '400', 'italic', 'Lato-Italic.ttf'],
    ];

    private const MESES = [
        '01' => 'enero', '02' => 'febrero', '03' => 'marzo', '04' => 'abril',
        '05' => 'mayo', '06' => 'junio', '07' => 'julio', '08' => 'agosto',
        '09' => 'septiembre', '10' => 'octubre', '11' => 'noviembre', '12' => 'diciembre',
    ];

    private string $rootPath;
    private string $fontDir;

    public function __construct(?string $rootPath = null)
    {
        $this->rootPath = rtrim($rootPath ?? dirname(__DIR__, 2), "/\\");
        $this->fontDir  = $this->rootPath . '/css/fonts';
    }

    /** Devuelve la lista de los 8 programas. */
    public static function programas(): array
    {
        return self::PROGRAMAS;
    }

    /** Fecha de hoy en español, ej. "17 de septiembre de 2026". */
    public static function fechaEspanol(?string $fecha = null): string
    {
        $ts = $fecha ? strtotime($fecha) : time();
        if ($ts === false) {
            return (string) $fecha;
        }
        return date('j', $ts) . ' de ' . self::MESES[date('m', $ts)] . ' de ' . date('Y', $ts);
    }

    /** Slug seguro para nombres de archivo. */
    public static function slug(string $texto): string
    {
        $texto = strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
            'ñ' => 'n', 'Ñ' => 'n', 'ü' => 'u', 'Ü' => 'u',
        ]);
        $texto = strtolower($texto);
        $texto = preg_replace('/[^a-z0-9]+/', '_', $texto) ?? '';
        return trim($texto, '_');
    }

    /** Construye y renderiza el documento con Dompdf. */
    public function build(array $data): Dompdf
    {
        $payload = $this->normalizar($data);
        $assets  = $this->cargarAssets();
        $html    = diplomaTemplate($payload, $assets);

        $dompdf = $this->crearDompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf;
    }

    /** Genera el PDF y devuelve el binario. */
    public function render(array $data): string
    {
        return (string) $this->build($data)->output();
    }

    /** Genera el PDF y lo guarda en disco. Devuelve la ruta absoluta. */
    public function save(array $data, string $rutaDestino): string
    {
        $pdf = $this->render($data);

        $dir = dirname($rutaDestino);
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new RuntimeException("No se pudo crear el directorio: $dir");
        }

        if (file_put_contents($rutaDestino, $pdf) === false) {
            throw new RuntimeException("No se pudo escribir el PDF en: $rutaDestino");
        }

        return $rutaDestino;
    }

    private function normalizar(array $data): array
    {
        $programa = trim((string) ($data['programa'] ?? self::PROGRAMAS[0]));
        if (!in_array($programa, self::PROGRAMAS, true)) {
            throw new InvalidArgumentException("Programa no válido: '$programa'");
        }

        return [
            'nombre'           => mb_strtoupper(trim((string) ($data['nombre'] ?? 'NOMBRE DEL ESTUDIANTE'))),
            'cedula'           => trim((string) ($data['cedula'] ?? '000000000')),
            'programa'         => $programa,
            'duracion'         => trim((string) ($data['duracion'] ?? '48 Horas')),
            'fecha'            => trim((string) ($data['fecha'] ?? self::fechaEspanol())),
            'ciudad'           => trim((string) ($data['ciudad'] ?? 'Medellín')),
            'institucion'      => mb_strtoupper(trim((string) ($data['institucion'] ?? 'INSTITUCIÓN CENTROS DE DESARROLLO INTEGRADO CENDI'))),
            'sitio_web'        => $data['sitio_web'] ?? 'www.cendiacademico.edu.co',
            'token'            => trim((string) ($data['token'] ?? '')),
            'rectora'          => $data['rectora'] ?? 'Luz Miriam Hernández',
            'rectora_cargo'    => $data['rectora_cargo'] ?? 'Rectora',
            'secretaria'       => $data['secretaria'] ?? 'Karina Vásquez Mejía',
            'secretaria_cargo' => $data['secretaria_cargo'] ?? 'Secretaría Académica',
        ];
    }

    private function cargarAssets(): array
    {
        return [
            'logo'            => $this->imagen('img/cendi_color.png', 42, 40),
            'fondo'           => $this->imagen('img/fondo_diploma.jpg', 297, 210, false),
            'firma_rectora'   => $this->imagen('img/firmas/firma_rectora.png', 62, 15),
            'firma_secretaria' => $this->imagen('img/firmas/firma_secre-acad.png', 62, 15),
        ];
    }

    /**
     * Convierte una imagen a data URI y calcula su tamaño en mm respetando
     * la proporción original dentro de una caja máxima.
     */
    private function imagen(string $relativa, float $maxAncho, float $maxAlto, bool $ajustar = true): array
    {
        $ruta = $this->rootPath . '/' . ltrim(str_replace('\\', '/', $relativa), '/');
        if (!is_file($ruta)) {
            throw new RuntimeException("No se encontró la imagen: $ruta");
        }

        $info = getimagesize($ruta);
        if ($info === false) {
            throw new RuntimeException("Imagen no válida: $ruta");
        }

        [$anchoPx, $altoPx] = $info;
        $mime = $info['mime'] ?: 'image/png';

        $anchoMm = $maxAncho;
        $altoMm  = $anchoMm * ($altoPx / $anchoPx);

        if ($ajustar && $altoMm > $maxAlto) {
            $altoMm  = $maxAlto;
            $anchoMm = $altoMm * ($anchoPx / $altoPx);
        }

        return [
            'src'    => 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($ruta)),
            'width'  => round($anchoMm, 3),
            'height' => round($altoMm, 3),
        ];
    }

    private function crearDompdf(): Dompdf
    {
        if (!is_dir($this->fontDir) && !mkdir($this->fontDir, 0777, true) && !is_dir($this->fontDir)) {
            throw new RuntimeException("No se pudo crear el directorio de fuentes: {$this->fontDir}");
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->setChroot($this->rootPath);
        $options->setFontDir($this->fontDir);
        $options->setFontCache($this->fontDir);
        $options->setDefaultFont('DejaVu Sans');

        $dompdf = new Dompdf($options);
        $this->registrarFuentes($dompdf);

        return $dompdf;
    }

    private function registrarFuentes(Dompdf $dompdf): void
    {
        $metrics = $dompdf->getFontMetrics();
        foreach (self::FUENTES as [$familia, $peso, $estilo, $archivo]) {
            $ruta = $this->fontDir . '/' . $archivo;
            if (!is_file($ruta)) {
                throw new RuntimeException("Falta la fuente: $ruta");
            }
            $ok = $metrics->registerFont(
                ['family' => $familia, 'weight' => $peso, 'style' => $estilo],
                str_replace('\\', '/', $ruta)
            );
            if ($ok !== true) {
                throw new RuntimeException("No se pudo registrar la fuente: $ruta");
            }
        }
    }
}
