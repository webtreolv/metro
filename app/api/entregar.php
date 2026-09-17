<?php
/**
 * API - Entregar Solicitud
 */

header('Content-Type: application/json');

// Usar timezone de México
date_default_timezone_set('America/Mexico_City');

$con = mysqli_connect('db', 'root', 'root', 'mmqro');
if (!$con) { echo json_encode(['success'=>false, 'error'=>'Conexión fallida']); exit; }

$data = json_decode(file_get_contents('php://input'), true);
$folio = mysqli_real_escape_string($con, $data['folio'] ?? '');

if (!$folio) {
    echo json_encode(['success'=>false, 'error'=>'Folio requerido']);
    exit;
}

// Verificar que existe y está en estatus listo
$sql = "SELECT id, estatus FROM solicitudes WHERE folio = '$folio'";
$result = mysqli_query($con, $sql);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    echo json_encode(['success'=>false, 'error'=>'Solicitud no encontrada']);
    exit;
}

// Permitir entregar desde cualquier estatus (pendiente, proceso, listo)

// Actualizar a entregado - usar fecha de PHP directamente
$fecha = date('Y-m-d H:i:s');
$hora = date('H:i:s');
$sql = "UPDATE solicitudes SET estatus = 'entregado', fecha_entrega = '$fecha', hora_entrega = '$hora' WHERE folio = '$folio'";

if (mysqli_query($con, $sql)) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false, 'error'=>mysqli_error($con)]);
}

mysqli_close($con);
