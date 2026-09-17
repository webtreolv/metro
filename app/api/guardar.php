<?php
require_once __DIR__ . '/../config/database.php';
/**
 * API - Guardar Solicitud
 */

// Configurar timezone México
date_default_timezone_set('America/Mexico_City');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Handle preflight
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$json = file_get_contents('php://input');
$datos = json_decode($json, true);

if (empty($datos)) { 
    echo json_encode(['success'=>false, 'error'=>'Sin datos recibidos']); 
    exit; 
}

// Validar campos requeridos
if (empty($datos['numero_e']) || empty($datos['tipo_solicitud']) || empty($datos['commodity'])) {
    echo json_encode(['success'=>false, 'error'=>'Campos requeridos']); 
    exit;
}

$con = conectarDB();
if (!$con) { 
    $error = mysqli_connect_error();
    echo json_encode(['success'=>false, 'error'=>'Sin conexión: ' . $error]); 
    exit; 
}
// Usar timezone de México directamente
$fechaActual = date('Y-m-d H:i:s');

// Usar folio del frontend o generar uno nuevo
$folio = mysqli_real_escape_string($con, $datos['folio'] ?? '');

// Si no hay folio, generarlo
if (empty($folio)) {
    $mes = date('ym');
    $sql = "SELECT MAX(CAST(SUBSTRING_INDEX(folio, '-', -1) AS UNSIGNED)) as max_val FROM solicitudes WHERE folio LIKE 'M{$mes}-%'";
    $res = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($res);
    $sig = str_pad(($row['max_val'] ?? 0) + 1, 3, '0', STR_PAD_LEFT);
    $folio = "M{$mes}-{$sig}";
}

// Escapar datos
$numeroE = mysqli_real_escape_string($con, $datos['numero_e']);
$nombreSolicitante = mysqli_real_escape_string($con, $datos['nombre_solicitante'] ?? '');
$tipoSolicitud = mysqli_real_escape_string($con, $datos['tipo_solicitud']);
$commodity = mysqli_real_escape_string($con, $datos['commodity']);
$cantidad = intval($datos['cantidad'] ?? 1);
$descripcion = mysqli_real_escape_string($con, $datos['motivo'] ?? '');
$numeroParte = mysqli_real_escape_string($con, $datos['numero_parte'] ?? '');
$maquina = mysqli_real_escape_string($con, $datos['maquina'] ?? '');
$especial = intval($datos['especial'] ?? 0);
$maquinaDetenida = intval($datos['maquina_detenida'] ?? 0);

// Insertar - usar fecha de PHP directamente
$sql = "INSERT INTO solicitudes 
    (folio, numero_e, nombre_solicitante, tipo_solicitud, commodity, cantidad, descripcion, numero_parte, maquina, especial, maquina_detenida, estatus, fecha_creacion)
    VALUES (
        '$folio', '$numeroE', '$nombreSolicitante', '$tipoSolicitud', '$commodity', $cantidad, '$descripcion', '$numeroParte', '$maquina', $especial, $maquinaDetenida, 'pendiente', '$fechaActual'
    )";

$result = mysqli_query($con, $sql);

if ($result) {
    echo json_encode(['success'=>true, 'folio'=>$folio]);
} else {
    echo json_encode(['success'=>false, 'error'=>myssqli_error($con)]);
}

mysqli_close($con);

