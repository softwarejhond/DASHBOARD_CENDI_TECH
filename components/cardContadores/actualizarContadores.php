<?php
require '.././../controller/conexion.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

header('Content-Type: application/json');

try {
    $fecha = isset($_GET['date']) ? trim($_GET['date']) : '';

    $count = function ($sql) use ($conn) {
        $r = mysqli_query($conn, $sql);
        if (!$r) {
            throw new Exception(mysqli_error($conn));
        }
        $row = mysqli_fetch_row($r);
        return $row ? (int) $row[0] : 0;
    };

    // Totales principales
    $registrados = $count("SELECT COUNT(*) FROM user_register");
    $matriculados = $count("SELECT COUNT(DISTINCT number_id) FROM enrollments");
    $verificado_correo = $count("SELECT COUNT(*) FROM user_register WHERE email_verified = 1");
    $por_verificar_correo = $count("SELECT COUNT(*) FROM user_register WHERE email_verified = 0");
    $rural = $count("SELECT COUNT(*) FROM user_register WHERE residence_area = 'Rural'");

    // Registros por género
    $generos = [];
    $r = mysqli_query($conn, "SELECT gender, COUNT(*) AS cantidad FROM user_register GROUP BY gender ORDER BY cantidad DESC");
    while ($row = mysqli_fetch_assoc($r)) {
        $generos[] = ['gener' => $row['gender'], 'cantidad' => (int) $row['cantidad']];
    }

    // Nacionalidad de origen
    $nacionalidades = [];
    $r = mysqli_query($conn, "SELECT nationality, COUNT(*) AS cantidad FROM user_register GROUP BY nationality ORDER BY cantidad DESC");
    while ($row = mysqli_fetch_assoc($r)) {
        $nacionalidades[] = ['nacionalidad' => $row['nationality'], 'cantidad' => (int) $row['cantidad']];
    }

    // Registros por programa
    $programas = [];
    $r = mysqli_query($conn, "SELECT program, COUNT(*) AS cantidad
                              FROM user_register
                              WHERE program IS NOT NULL AND program != ''
                              GROUP BY program ORDER BY cantidad DESC");
    while ($row = mysqli_fetch_assoc($r)) {
        $programas[] = ['program' => $row['program'], 'cantidad' => (int) $row['cantidad']];
    }

    // Rangos de edad
    $rangoOrden = ['7 - 17', '18 - 26', '27 - 59', '60+'];
    $edadesMap = array_fill_keys($rangoOrden, 0);
    $r = mysqli_query($conn, "SELECT
        CASE
            WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 7 AND 17 THEN '7 - 17'
            WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 18 AND 26 THEN '18 - 26'
            WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 27 AND 59 THEN '27 - 59'
            WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= 60 THEN '60+'
        END AS rango,
        COUNT(*) AS cantidad
        FROM user_register
        WHERE birthdate IS NOT NULL AND birthdate != '0000-00-00'
        GROUP BY rango");
    while ($row = mysqli_fetch_assoc($r)) {
        if ($row['rango'] !== null && isset($edadesMap[$row['rango']])) {
            $edadesMap[$row['rango']] = (int) $row['cantidad'];
        }
    }
    $edades = [];
    foreach ($rangoOrden as $rango) {
        $edades[] = ['rango' => $rango, 'cantidad' => $edadesMap[$rango]];
    }

    // Comunas / corregimientos (valores distintos para el buscador)
    $comunas = [];
    $r = mysqli_query($conn, "SELECT DISTINCT comuna_corregimiento
                              FROM user_register
                              WHERE comuna_corregimiento IS NOT NULL AND comuna_corregimiento != ''
                              ORDER BY comuna_corregimiento");
    while ($row = mysqli_fetch_assoc($r)) {
        $comunas[] = $row['comuna_corregimiento'];
    }

    // Barrios / veredas (valores distintos para el buscador)
    $barrios = [];
    $r = mysqli_query($conn, "SELECT DISTINCT barrio
                              FROM user_register
                              WHERE barrio IS NOT NULL AND barrio != ''
                              ORDER BY barrio");
    while ($row = mysqli_fetch_assoc($r)) {
        $barrios[] = $row['barrio'];
    }

    $response = [
        'registrados'          => $registrados,
        'matriculados'         => $matriculados,
        'verificado_correo'    => $verificado_correo,
        'por_verificar_correo' => $por_verificar_correo,
        'rural'                => $rural,
        'generos'              => $generos,
        'nacionalidades'       => $nacionalidades,
        'programas'            => $programas,
        'edades'               => $edades,
        'comunas'              => $comunas,
        'barrios'              => $barrios,
    ];

    // Conteo acumulado hasta una fecha (función "registrados por fecha")
    if ($fecha !== '') {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM user_register WHERE DATE(creationDate) <= ?");
        $stmt->bind_param('s', $fecha);
        $stmt->execute();
        $stmt->bind_result($totalFecha);
        $stmt->fetch();
        $response['registrados_por_fecha'] = (int) $totalFecha;
        $stmt->close();
    }

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
