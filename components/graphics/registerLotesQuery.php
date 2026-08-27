<?php
session_start();
include_once('../../controller/conexion.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

$query = "SELECT COUNT(*) as cantidad FROM user_register";
$result = $conn->query($query);
$cantidad = $result->fetch_assoc()['cantidad'] ?? 0;

if (isset($_GET['json']) && ($_GET['json'] == 'lote1' || $_GET['json'] == 'lote2')) {
    header('Content-Type: application/json');
    echo json_encode([
        'labels' => ['Registrados'],
        'data' => [$cantidad]
    ]);
    exit;
}

if (isset($_GET['json']) && $_GET['json'] == 'total') {
    header('Content-Type: application/json');
    echo json_encode(['total' => $cantidad]);
    exit;
}
