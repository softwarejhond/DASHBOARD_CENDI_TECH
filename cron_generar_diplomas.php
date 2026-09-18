<?php
// Script para generar, guardar y enviar por correo los diplomas CENDI Tech.
// Se ejecuta en segundo plano desde cron_obtener_notas.php y/o por su propio cron.

set_time_limit(0);
ignore_user_abort(true);

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/components/diplomas/DiplomaGenerator.php';
require_once __DIR__ . '/components/diplomas/correo_diploma.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/components/cron/cron_log.php';

use PHPMailer\PHPMailer\PHPMailer;

cronLogInit('cron_diplomas');

function logDiploma($mensaje)
{
    cronLog($mensaje);
}

function generarTokenUnico($conn)
{
    do {
        $token = 'CENDI-' . strtoupper(bin2hex(random_bytes(6)));
        $stmt = $conn->prepare("SELECT COUNT(*) FROM diplomas_emitidos WHERE token = ?");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();
    } while ($total > 0);

    return $token;
}

function enviarDiploma($smtp, $email, $nombre, $programa, $archivo)
{
    if (!$smtp) {
        throw new Exception('No hay configuración SMTP (id=4).');
    }

    $mail = new PHPMailer(true);
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host = $smtp['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtp['email'];
    $mail->Password = $smtp['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
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
    $mail->addAddress($email, $nombre);
    $mail->isHTML(true);
    $mail->Subject = 'Tu diploma CENDI Tech - ' . $programa;

    $mail->Body = correoDiplomaHtml($nombre, $programa);
    $mail->AltBody = 'Felicitaciones ' . $nombre . '. Adjuntamos tu diploma del programa ' . $programa . ' de CENDI Tech.';

    $mail->addAttachment($archivo, 'diploma_' . DiplomaGenerator::slug($programa) . '.pdf');

    if (!$mail->send()) {
        throw new Exception('Mailer Error: ' . $mail->ErrorInfo);
    }
}

if (!isset($conn) || $conn->connect_error) {
    logDiploma('Error de conexión a la base de datos.');
    die('Error de conexión a la base de datos.');
}

$lockResult = $conn->query("SELECT GET_LOCK('cron_diplomas', 0) AS l");
$lockRow = $lockResult ? $lockResult->fetch_assoc() : null;
if (!$lockRow || (int) $lockRow['l'] !== 1) {
    logDiploma('Otra ejecución de diplomas está en curso; se omite esta corrida.');
    $conn->close();
    echo 'Omitido.';
    exit;
}

logDiploma('Inicia el proceso de generación de diplomas.');

$smtp = null;
$resSmtp = $conn->query("SELECT * FROM smtpConfig WHERE id=4 LIMIT 1");
if ($resSmtp && ($rowSmtp = $resSmtp->fetch_assoc())) {
    $smtp = $rowSmtp;
}

$generador = new DiplomaGenerator();
$directorio = __DIR__ . '/diplomas';

$sql = "SELECT n.number_id, n.nota_final,
               ur.first_name, ur.second_name, ur.first_last, ur.second_last,
               ur.email AS email_personal, ur.program,
               g.email AS email_groups,
               d.id AS d_id, d.estado AS d_estado, d.intentos AS d_intentos,
               d.archivo AS d_archivo, d.token AS d_token
        FROM notas_estudiantes n
        INNER JOIN user_register ur ON ur.number_id = n.number_id
        LEFT JOIN (
            SELECT number_id, MAX(id) AS max_id FROM groups GROUP BY number_id
        ) gm ON gm.number_id = n.number_id
        LEFT JOIN groups g ON g.id = gm.max_id
        LEFT JOIN diplomas_emitidos d ON d.number_id = n.number_id
        WHERE n.presento_tecnico = 1
          AND n.presento_ingles = 1
          AND n.presento_habilidades = 1
          AND n.nota_final >= 3.0";

$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    logDiploma('No hay estudiantes que cumplan el criterio para diploma.');
    $conn->close();
    echo 'Proceso completado.';
    exit;
}

$candidatos = $result->fetch_all(MYSQLI_ASSOC);
$generados = 0;
$enviados = 0;
$errores = 0;

foreach ($candidatos as $c) {
    $number_id = $c['number_id'];
    $programa = trim((string) $c['program']);
    $nombre = trim(implode(' ', array_filter([
        $c['first_name'], $c['second_name'], $c['first_last'], $c['second_last'],
    ], 'strlen')));
    $email = trim((string) $c['email_personal']);
    if ($email === '') {
        $email = trim((string) $c['email_groups']);
    }

    if ($programa === '' || !in_array($programa, DiplomaGenerator::programas(), true)) {
        logDiploma("SKIP $number_id: programa no válido ('$programa').");
        continue;
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        logDiploma("SKIP $number_id: email inválido ('$email').");
        continue;
    }

    $diplomaId = $c['d_id'];
    $token = $c['d_token'];
    $archivo = $c['d_archivo'];

    if ($diplomaId) {
        if ($c['d_estado'] === 'enviado' || (int) $c['d_intentos'] >= 5) {
            continue;
        }
    }

    if (!$archivo || !is_file($archivo)) {
        $token = generarTokenUnico($conn);
        $archivo = $directorio . '/' . $token . '.pdf';

        try {
            $generador->save([
                'nombre' => $nombre,
                'cedula' => $number_id,
                'programa' => $programa,
                'token' => $token,
                'fecha' => DiplomaGenerator::fechaEspanol(),
                'ciudad' => 'Medellín',
            ], $archivo);
            $generados++;
        } catch (Throwable $e) {
            logDiploma("ERROR generando PDF para $number_id: " . $e->getMessage());
            $errores++;
            continue;
        }
    }

    if (!$diplomaId) {
        $stmt = $conn->prepare("
            INSERT INTO diplomas_emitidos (number_id, programa, token, archivo, email_destino, estado)
            VALUES (?, ?, ?, ?, ?, 'generado')
        ");
        $stmt->bind_param('sssss', $number_id, $programa, $token, $archivo, $email);
        if (!$stmt->execute()) {
            logDiploma("ERROR registrando diploma para $number_id: " . $stmt->error);
            $errores++;
            continue;
        }
        $diplomaId = $conn->insert_id;
    }

    try {
        enviarDiploma($smtp, $email, $nombre, $programa, $archivo);
        $stmt = $conn->prepare("UPDATE diplomas_emitidos SET estado = 'enviado', fecha_envio = NOW(), intentos = intentos + 1, error_msg = NULL WHERE id = ?");
        $stmt->bind_param('i', $diplomaId);
        $stmt->execute();
        $enviados++;
        logDiploma("OK $number_id -> $email ($token)");
    } catch (Throwable $e) {
        $mensaje = substr($e->getMessage(), 0, 250);
        $stmt = $conn->prepare("UPDATE diplomas_emitidos SET estado = 'error', intentos = intentos + 1, error_msg = ? WHERE id = ?");
        $stmt->bind_param('si', $mensaje, $diplomaId);
        $stmt->execute();
        $errores++;
        logDiploma("ERROR enviando diploma a $number_id: $mensaje");
    }
}

logDiploma("Proceso finalizado. Generados: $generados, enviados: $enviados, errores: $errores.\n");
$conn->query("SELECT RELEASE_LOCK('cron_diplomas')");
$conn->close();

echo 'Proceso completado.';
