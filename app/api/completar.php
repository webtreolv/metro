<?php
require_once __DIR__ . '/../config/database.php';
/**
 * API - Completar Solicitud
 */

header('Content-Type: application/json');

// Usar timezone de México
date_default_timezone_set('America/Mexico_City');

$con = conectarDB();
if (!$con) { echo json_encode(['success'=>false]); exit; }

$json = file_get_contents('php://input');
$datos = json_decode($json, true);

$folio = mysqli_real_escape_string($con, $datos['folio'] ?? '');

if (!$folio) { echo json_encode(['success'=>false]); exit; }

$fecha = date('Y-m-d H:i:s');
$hora = date('H:i:s');
$sql = "UPDATE solicitudes SET estatus = 'listo', fecha_completado = '$fecha', hora_lista = '$hora' WHERE folio = '$folio'";

if (mysqli_query($con, $sql)) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false, 'error'=>mysqli_error($con)]);
}

mysqli_close($con);

