<?php
session_start();
include_once('../../controller/conexion.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

$sql_total = "SELECT COUNT(*) as total FROM user_register WHERE statusAdmin NOT IN (2, 7, 11)";
$result_total = $conn->query($sql_total);
$total_registrados = $result_total->fetch_assoc()['total'];

$sql_presencial = "SELECT COUNT(DISTINCT g.number_id) as total_presencial 
                    FROM groups g 
                    INNER JOIN user_register ur ON g.number_id = ur.number_id 
                    WHERE g.mode = 'Presencial'";
$result_presencial = $conn->query($sql_presencial);
$total_presencial = $result_presencial->fetch_assoc()['total_presencial'];

$sql_virtual = "SELECT COUNT(DISTINCT g.number_id) as total_virtual 
                 FROM groups g 
                 INNER JOIN user_register ur ON g.number_id = ur.number_id 
                 WHERE g.mode = 'Virtual'";
$result_virtual = $conn->query($sql_virtual);
$total_virtual = $result_virtual->fetch_assoc()['total_virtual'];

$data = [
    'labels' => ['Registrados', 'Matriculados Presencial', 'Matriculados Virtual'],
    'data' => [$total_registrados, $total_presencial, $total_virtual]
];

header('Content-Type: application/json');
echo json_encode($data);
