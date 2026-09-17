<?php
/**
 * API - Cambiar estatus de solicitud
 */

header('Content-Type: application/json');

// Usar timezone de México
date_default_timezone_set('America/Mexico_City');

$id = intval($_POST['id'] ?? 0);
$estatus = $_POST['estatus'] ?? '';
$motivo = $_POST['motivo'] ?? '';

if ($id > 0 && in_array($estatus, ['pendiente', 'proceso', 'listo'])) {
    $con = mysqli_connect('db', 'root', 'root', 'mmqro');
    if ($con) {
        // Obtener estatus actual antes de cambiar
        $res = mysqli_query($con, "SELECT folio, estatus FROM solicitudes WHERE id = $id");
        $row = mysqli_fetch_assoc($res);
        $estatusAnterior = $row['estatus'] ?? '';
        $folio = $row['folio'] ?? '';
        
        // Si se va a pendiente desde proceso o listo, requiere motivo
        if ($estatus === 'pendiente' && ($estatusAnterior === 'proceso' || $estatusAnterior === 'listo')) {
            if (empty($motivo)) {
                echo json_encode(['success' => false, 'error' => 'Motivo requerido para regresar a pendiente']);
                mysqli_close($con);
                exit;
            }
            // Guardar motivo
            $fecha = date('Y-m-d H:i:s');
            $motivoEsc = mysqli_real_escape_string($con, $motivo);
            mysqli_query($con, "INSERT INTO motivos_cambio (solicitud_id, folio, estatus_anterior, estatus_nuevo, motivo, fecha_cambio) 
                VALUES ($id, '$folio', '$estatusAnterior', '$estatus', '$motivoEsc', '$fecha')");
        }
        
        $sql = "UPDATE solicitudes SET estatus = '$estatus'";
        if ($estatus === 'listo') {
            $fecha = date('Y-m-d H:i:s');
            $hora = date('H:i:s');
            $sql .= ", fecha_completado = '$fecha', hora_lista = '$hora'";
        } elseif ($estatus === 'pendiente') {
            $sql .= ", fecha_completado = NULL, hora_lista = NULL";
        }
        $sql .= " WHERE id = $id";
        
        if (mysqli_query($con, $sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
        }
        mysqli_close($con);
    } else {
        echo json_encode(['success' => false, 'error' => 'No connection']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
}
