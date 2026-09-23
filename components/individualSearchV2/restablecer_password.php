<?php
session_start();
require_once __DIR__ . '/../../controller/conexion.php';
require_once __DIR__ . '/correo_password.php';
require_once __DIR__ . '/../changeHistory/registrar_cambio.php';
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'No autorizado.']);
    exit;
}

$number_id = isset($_POST['number_id']) ? trim($_POST['number_id']) : '';
if ($number_id === '') {
    echo json_encode(['ok' => false, 'message' => 'Falta el identificador del estudiante.']);
    exit;
}

$PASSWORD_DEFECTO = 'Cendi@2026!';
$apiUrl = 'https://campus.cenditech.com.co/webservice/rest/server.php';
$token = 'c4bc5a8ef9d02d713c1e5283da17c29f';
$format = 'json';

function llamarMoodle($params)
{
    global $apiUrl, $token, $format;
    $postdata = http_build_query(['wstoken' => $token, 'moodlewsrestformat' => $format] + $params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        throw new Exception('Error de conexión con Moodle: ' . $err);
    }

    return json_decode($response, true);
}

try {
    $stmt = $conn->prepare("
        SELECT e.moodle_user_id, e.username, e.institutional_email,
               ur.first_name, ur.second_name, ur.first_last, ur.second_last,
               ur.email AS email_personal, ur.program,
               g.password AS local_password, g.id AS group_id
        FROM enrollments e
        INNER JOIN user_register ur ON ur.number_id = e.number_id
        LEFT JOIN (
            SELECT number_id, MAX(id) AS max_id FROM groups GROUP BY number_id
        ) gm ON gm.number_id = e.number_id
        LEFT JOIN groups g ON g.id = gm.max_id
        WHERE e.number_id = ?
        ORDER BY e.id DESC
        LIMIT 1
    ");
    $stmt->bind_param('s', $number_id);
    $stmt->execute();
    $info = $stmt->get_result()->fetch_assoc();

    if (!$info) {
        echo json_encode(['ok' => false, 'message' => 'El estudiante no tiene matrícula registrada.']);
        exit;
    }

    $nombre = trim(implode(' ', array_filter([
        $info['first_name'], $info['second_name'], $info['first_last'], $info['second_last'],
    ], 'strlen')));
    $email = trim((string) $info['email_personal']);
    if ($email === '') {
        $email = trim((string) $info['institutional_email']);
    }

    // Resolver usuario de Moodle
    $moodleUserId = !empty($info['moodle_user_id']) ? (int) $info['moodle_user_id'] : null;
    if (!$moodleUserId && !empty($info['username'])) {
        $userData = llamarMoodle([
            'wsfunction' => 'core_user_get_users_by_field',
            'field' => 'username',
            'values[0]' => $info['username'],
        ]);
        if (!empty($userData[0]['id'])) {
            $moodleUserId = (int) $userData[0]['id'];
        }
    }

    if (!$moodleUserId) {
        echo json_encode(['ok' => false, 'message' => 'No se pudo identificar el usuario en Moodle.']);
        exit;
    }

    // Contraseña por defecto + forzar cambio
    $resultado = llamarMoodle([
        'wsfunction' => 'core_user_update_users',
        'users[0][id]' => $moodleUserId,
        'users[0][password]' => $PASSWORD_DEFECTO,
        'users[0][preferences][0][type]' => 'auth_forcepasswordchange',
        'users[0][preferences][0][value]' => 1,
    ]);

    if (isset($resultado['exception'])) {
        echo json_encode(['ok' => false, 'message' => 'Moodle rechazó el cambio: ' . ($resultado['message'] ?? 'Error desconocido')]);
        exit;
    }

    // Actualizar contraseña local (groups)
    if (!empty($info['group_id'])) {
        $stmtG = $conn->prepare("UPDATE groups SET password = ? WHERE id = ?");
        $stmtG->bind_param('si', $PASSWORD_DEFECTO, $info['group_id']);
        $stmtG->execute();
    }

    // Enviar correo (no bloquea el resultado si falla)
    $correoEnviado = false;
    $correoError = null;
    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $smtp = null;
            $resSmtp = $conn->query("SELECT * FROM smtpConfig WHERE id=4 LIMIT 1");
            if ($resSmtp && ($rowSmtp = $resSmtp->fetch_assoc())) {
                $smtp = $rowSmtp;
            }

            if ($smtp) {
                $mail = new PHPMailer(true);
                $mail->SMTPDebug = 0;
                $mail->isSMTP();
                $mail->Host = $smtp['host'];
                $mail->SMTPAuth = true;
                $mail->Username = $smtp['email'];
                $mail->Password = $smtp['password'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $smtp['port'];
                $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];
                $mail->setFrom('no-reply@cenditech.com.co', 'CENDI Tech');
                $mail->CharSet = 'UTF-8';
                $mail->addAddress($email, $nombre);
                $mail->isHTML(true);
                $mail->Subject = 'Acceso al campus CENDI Tech - Contraseña temporal';
                $mail->Body = correoPasswordHtml($nombre, $PASSWORD_DEFECTO);
                $mail->AltBody = 'Hola ' . $nombre . '. Se restableció tu contraseña del campus CENDI Tech. Contraseña temporal: ' . $PASSWORD_DEFECTO . ' (deberás cambiarla al iniciar sesión).';
                $mail->send();
                $correoEnviado = true;
            } else {
                $correoError = 'Sin configuración SMTP (id=4).';
            }
        } catch (Throwable $e) {
            $correoError = $e->getMessage();
        }
    } else {
        $correoError = 'El estudiante no tiene un correo válido.';
    }

    registrarCambioHistorial($conn, $number_id, 'Restablecimiento de contraseña (contraseña temporal Cendi@2026! y cambio obligatorio). Correo: ' . ($correoEnviado ? 'enviado a ' . $email : 'no enviado (' . $correoError . ')'));

    echo json_encode([
        'ok' => true,
        'message' => 'Contraseña restablecida. Se forzará el cambio al iniciar sesión.' . ($correoEnviado ? ' Notificación enviada a ' . $email . '.' : ' (No se pudo enviar el correo: ' . $correoError . ')'),
        'correo_enviado' => $correoEnviado,
    ]);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
