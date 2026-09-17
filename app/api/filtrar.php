<?php
require_once __DIR__ . '/../config/database.php';
/**
 * API - Filtrar Solicitudes
 */

header('Content-Type: application/json');

$con = conectarDB();
if (!$con) { echo json_encode(['success'=>false]); exit; }

$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$estatus = $_GET['estatus'] ?? '';
$commodity = $_GET['commodity'] ?? '';
$solicitante = $_GET['solicitante'] ?? '';

$where = "1=1";
if ($fecha_inicio) $where .= " AND fecha_creacion >= '$fecha_inicio 00:00:00'";
if ($fecha_fin) $where .= " AND fecha_creacion <= '$fecha_fin 23:59:59'";
if ($tipo) $where .= " AND tipo_solicitud = '$tipo'";
if ($estatus) $where .= " AND estatus = '$estatus'";
if ($commodity) $where .= " AND commodity = '$commodity'";
if ($solicitante) $where .= " AND (numero_e LIKE '%$solicitante%' OR nombre_solicitante LIKE '%$solicitante%')";

$sql = "SELECT * FROM solicitudes WHERE $where ORDER BY fecha_creacion DESC";
$result = mysqli_query($con, $sql);

$solicitudes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $solicitudes[] = [
        'folio' => $row['folio'],
        'numero_e' => $row['nombre_solicitante'] ?: $row['numero_e'],
        'tipo_solicitud' => $row['tipo_solicitud'],
        'commodity' => $row['commodity'],
        'cantidad' => $row['cantidad'],
        'fecha' => date('d/m/Y H:i', strtotime($row['fecha_creacion'])),
        'estatus' => $row['estatus']
    ];
}

mysqli_close($con);
echo json_encode(['success'=>true, 'solicitudes'=>$solicitudes]);

