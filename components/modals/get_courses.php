<?php
session_start();
include __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

include __DIR__ . '/../moodle_api.php';

header('Content-Type: application/json');
$courses = getCoursesB();

if (isset($courses['error'])) {
    http_response_code(500);
    echo json_encode($courses);
    exit;
}

echo json_encode($courses);
