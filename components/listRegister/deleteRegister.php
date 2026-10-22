<?php
// =====================================================================
// Endpoint para eliminar una inscripción desde el listado de inscritos.
// SOLO disponible para el rol "Control maestro" (rol 12).
// Si el inscrito está matriculado, primero lo desmatricula (Moodle +
// historial) reutilizando el helper compartido desmatricularUsuario.php;
// luego elimina el registro de user_register.
// =====================================================================

session_start();
require_once __DIR__ . '/../../controller/conexion.php';
require_once __DIR__ . '/../activeMoodle/desmatricularUsuario.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado: sesión no iniciada']);
    exit;
}

// Restricción: solo Control maestro (rol 12)
if (!isset($_SESSION['rol']) || (int) $_SESSION['rol'] !== 12) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado: solo Control maestro puede eliminar inscripciones']);
    exit;
}

// Obtener el identificador del usuario (JSON o formulario)
$input = json_decode(file_get_contents('php://input'), true);
if ($input && isset($input['number_id'])) {
    $number_id = $input['number_id'];
} else if (isset($_POST['number_id'])) {
    $number_id = $_POST['number_id'];
} else {
    echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado']);
    exit;
}

$username = $_SESSION['username'] ?? 'Sistema';

try {
    // Verificar que la inscripción exista antes de cualquier acción
    $existsStmt = $conn->prepare("SELECT COUNT(*) AS total FROM user_register WHERE number_id = ?");
    $existsStmt->bind_param("s", $number_id);
    $existsStmt->execute();
    $existe = (int) $existsStmt->get_result()->fetch_assoc()['total'] > 0;
    $existsStmt->close();

    if (!$existe) {
        echo json_encode(['success' => false, 'message' => 'La inscripción no existe o ya fue eliminada']);
        $conn->close();
        exit;
    }

    // Detectar si el usuario tiene matrícula activa (enrollments o groups)
    $stmt = $conn->prepare("SELECT
        (SELECT COUNT(*) FROM enrollments WHERE number_id = ?) AS total_enrollments,
        (SELECT COUNT(*) FROM groups WHERE number_id = ?) AS total_groups");
    $stmt->bind_param("ss", $number_id, $number_id);
    $stmt->execute();
    $counts = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $matriculado = ((int) $counts['total_enrollments'] > 0) || ((int) $counts['total_groups'] > 0);
    $desmatriculado = false;

    // Si está matriculado, desmatricular primero. Si falla, no se borra la inscripción.
    if ($matriculado) {
        $result = desmatricularUsuario($conn, $number_id, $username, false);
        if (!$result['success']) {
            echo json_encode($result);
            $conn->close();
            exit;
        }
        $desmatriculado = true;
    }

    // Eliminar la inscripción (user_register) registrando el cambio
    $conn->begin_transaction();

    $deleteStmt = $conn->prepare("DELETE FROM user_register WHERE number_id = ?");
    $deleteStmt->bind_param("s", $number_id);
    $deleteStmt->execute();
    $afectadas = $deleteStmt->affected_rows;
    $deleteStmt->close();

    if ($afectadas === 0) {
        throw new Exception('La inscripción no existe o ya fue eliminada');
    }

    $descripcion = $desmatriculado
        ? 'Se elimina inscripción y matrícula'
        : 'Se elimina inscripción';

    $historialStmt = $conn->prepare("INSERT INTO change_history (student_id, user_change, change_made) VALUES (?, ?, ?)");
    $historialStmt->bind_param('iss', $number_id, $username, $descripcion);
    $historialStmt->execute();
    $historialStmt->close();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => $desmatriculado
            ? 'Inscripción eliminada y usuario desmatriculado correctamente'
            : 'Inscripción eliminada correctamente',
        'desmatriculado' => $desmatriculado,
    ]);
} catch (Exception $e) {
    @$conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
