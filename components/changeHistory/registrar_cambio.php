<?php
/**
 * Registro de cambios en la tabla change_history.
 * Columnas: student_id, user_change, change_made, date.
 */

if (!function_exists('registrarCambioHistorial')) {
    function registrarCambioHistorial($conn, $student_id, $change_made)
    {
        $student = is_numeric($student_id) ? (int) $student_id : 0;
        if ($student <= 0 || trim((string) $change_made) === '') {
            return false;
        }

        $user = 0;
        if (isset($_SESSION['username']) && is_numeric($_SESSION['username'])) {
            $user = (int) $_SESSION['username'];
        }

        try {
            $stmt = $conn->prepare("INSERT INTO change_history (student_id, user_change, change_made) VALUES (?, ?, ?)");
            $stmt->bind_param('iis', $student, $user, $change_made);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('describirCambiosHistorial')) {
    /**
     * Construye una descripción legible de los campos que cambiaron.
     * $labels: [campo => "Etiqueta"], $old y $new: [campo => valor].
     */
    function describirCambiosHistorial(array $labels, array $old, array $new)
    {
        $partes = [];
        foreach ($labels as $campo => $label) {
            $antes = array_key_exists($campo, $old) ? $old[$campo] : null;
            $ahora = array_key_exists($campo, $new) ? $new[$campo] : null;
            if ((string) $antes !== (string) $ahora) {
                $partes[] = $label . ': "' . $antes . '" -> "' . $ahora . '"';
            }
        }
        return implode(' | ', $partes);
    }
}
