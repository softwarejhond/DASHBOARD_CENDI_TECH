<?php
session_start();
require_once __DIR__ . '/../../controller/conexion.php';
header('Content-Type: application/json');

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

try {
    switch ($action) {
        case 'list':
            $result = $conn->query("SELECT id, nombre, DATE_FORMAT(fecha_creacion, '%d/%m/%Y %H:%i') AS fecha_creacion FROM proyecto ORDER BY nombre ASC");
            $proyectos = [];
            while ($row = $result->fetch_assoc()) {
                $proyectos[] = $row;
            }
            echo json_encode(['success' => true, 'data' => $proyectos]);
            break;

        case 'create':
            $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
            if ($nombre === '') {
                echo json_encode(['success' => false, 'message' => 'El nombre del proyecto es obligatorio']);
                break;
            }
            $stmt = $conn->prepare("INSERT INTO proyecto (nombre) VALUES (?)");
            $stmt->bind_param("s", $nombre);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Proyecto creado exitosamente', 'id' => $conn->insert_id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear el proyecto']);
            }
            $stmt->close();
            break;

        case 'update':
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
            if ($id <= 0 || $nombre === '') {
                echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
                break;
            }
            $stmt = $conn->prepare("UPDATE proyecto SET nombre = ? WHERE id = ?");
            $stmt->bind_param("si", $nombre, $id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Proyecto actualizado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el proyecto']);
            }
            $stmt->close();
            break;

        case 'delete':
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                break;
            }
            $stmt = $conn->prepare("DELETE FROM proyecto WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Proyecto eliminado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el proyecto']);
            }
            $stmt->close();
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Throwable $e) {
    error_log("Error en proyecto_process.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}

$conn->close();
