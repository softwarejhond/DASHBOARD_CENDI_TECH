<?php
// Vista previa del correo del diploma.
//   /components/diplomas/preview_correo.php
//   /components/diplomas/preview_correo.php?nombre=...&programa=...
//   /components/diplomas/preview_correo.php?send=correo@dominio.com   (envía un correo de prueba real, SMTP id=4)

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    exit('No autorizado.');
}

require_once __DIR__ . '/correo_diploma.php';
require_once __DIR__ . '/DiplomaGenerator.php';

$nombre = isset($_GET['nombre']) && trim($_GET['nombre']) !== '' ? trim($_GET['nombre']) : 'JUAN PÉREZ GÓMEZ';
$programa = isset($_GET['programa']) && trim($_GET['programa']) !== '' ? trim($_GET['programa']) : 'Análisis de datos';

$enviado = null;
$error = null;

if (isset($_GET['send'])) {
    $destino = trim($_GET['send']);

    if (!filter_var($destino, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo de destino no es válido.';
    } else {
        require_once __DIR__ . '/../../conexion.php';
        require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
        require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';

        $smtp = null;
        $res = $conn->query("SELECT * FROM smtpConfig WHERE id=4 LIMIT 1");
        if ($res && ($row = $res->fetch_assoc())) {
            $smtp = $row;
        }

        if (!$smtp) {
            $error = 'No hay configuración SMTP (id=4).';
        } else {
            try {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->SMTPDebug = 0;
                $mail->isSMTP();
                $mail->Host = $smtp['host'];
                $mail->SMTPAuth = true;
                $mail->Username = $smtp['email'];
                $mail->Password = $smtp['password'];
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $smtp['port'];
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ];
                $mail->setFrom('no-reply@cenditech.com.co', 'CENDI Tech');
                $mail->CharSet = 'UTF-8';
                $mail->addAddress($destino);
                $mail->isHTML(true);
                $mail->Subject = 'Tu diploma CENDI Tech - ' . $programa . ' (prueba)';
                $mail->Body = correoDiplomaHtml($nombre, $programa);
                $mail->AltBody = 'Felicitaciones ' . $nombre . '. Adjuntamos tu diploma del programa ' . $programa . ' de CENDI Tech.';

                $tokenPrueba = 'CENDI-PRUEBA-' . strtoupper(bin2hex(random_bytes(3)));
                $tmpPdf = sys_get_temp_dir() . '/diploma_prueba_' . uniqid() . '.pdf';
                $generador = new DiplomaGenerator();
                $generador->save([
                    'nombre' => $nombre,
                    'cedula' => '000000000',
                    'programa' => $programa,
                    'token' => $tokenPrueba,
                    'fecha' => DiplomaGenerator::fechaEspanol(),
                    'ciudad' => 'Medellín',
                ], $tmpPdf);
                $mail->addAttachment($tmpPdf, 'diploma_' . DiplomaGenerator::slug($programa) . '.pdf');

                $mail->send();
                @unlink($tmpPdf);
                $enviado = $destino;
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }
    }
}

header('Content-Type: text/html; charset=utf-8');

if ($enviado !== null) {
    echo '<div style="font-family:Arial,sans-serif;background:#e6f5ef;color:#00654a;padding:10px 16px;font-size:13px;">Correo de prueba enviado a <b>' . htmlspecialchars($enviado) . '</b>.</div>';
} elseif ($error !== null) {
    echo '<div style="font-family:Arial,sans-serif;background:#fde8e8;color:#9b1c1c;padding:10px 16px;font-size:13px;">Error: ' . htmlspecialchars($error) . '</div>';
}

echo correoDiplomaHtml($nombre, $programa);
