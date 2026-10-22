<?php
// Iniciar sesión si no está iniciada
session_start();

// Verificar siempre que la persona esté logueada para dar seguimiento
// (quién ejecuta) a las desmatriculaciones.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado: sesión no iniciada']);
    exit;
}

// Obtener el contenido JSON de la solicitud
$input = json_decode(file_get_contents('php://input'), true);
if ($input && isset($input['number_id'])) {
    // Si viene en formato JSON (nueva implementación)
    $number_id = $input['number_id'];
} else if (isset($_POST['number_id'])) {
    // Mantener compatibilidad con la versión anterior
    $number_id = $_POST['number_id'];
} else {
    echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado']);
    exit;
}

// Incluir archivo de configuración de base de datos
require '../../controller/conexion.php';

// Lógica compartida de desmatriculación (Moodle + historial + limpieza local)
require_once __DIR__ . '/desmatricularUsuario.php';

$username = $_SESSION['username'] ?? 'Sistema';
$isMultiple = isset($input['isMultiple']) && $input['isMultiple'] === true;

$result = desmatricularUsuario($conn, $number_id, $username, $isMultiple);

if (isset($conn)) {
    $conn->close();
}

echo json_encode($result);
