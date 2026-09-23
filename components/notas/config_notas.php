<?php
/**
 * Configuración de notas (tabla notas_pesos, fila id=1).
 * Pesos de cada componente y nota mínima para aprobar, con valores por defecto
 * (50/25/25 y 3.00) si la tabla o la fila no existen.
 */

if (!function_exists('obtenerConfigNotas')) {
    function obtenerConfigNotas($conn)
    {
        $config = [
            'peso_tecnico' => 50.0,
            'peso_ingles' => 25.0,
            'peso_habilidades' => 25.0,
            'nota_minima_aprobacion' => 3.0,
        ];

        try {
            $result = $conn->query("SELECT peso_tecnico, peso_ingles, peso_habilidades, nota_minima_aprobacion FROM notas_pesos WHERE id = 1 LIMIT 1");
            if ($result && ($row = $result->fetch_assoc())) {
                $config['peso_tecnico'] = (float) $row['peso_tecnico'];
                $config['peso_ingles'] = (float) $row['peso_ingles'];
                $config['peso_habilidades'] = (float) $row['peso_habilidades'];
                $config['nota_minima_aprobacion'] = (float) $row['nota_minima_aprobacion'];
            }
        } catch (Exception $e) {
            // se mantienen los valores por defecto
        }

        return $config;
    }
}

if (!function_exists('obtenerNotaMinimaAprobacion')) {
    function obtenerNotaMinimaAprobacion($conn)
    {
        $config = obtenerConfigNotas($conn);
        return $config['nota_minima_aprobacion'];
    }
}
