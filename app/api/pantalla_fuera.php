<?php
/**
 * API - Pantalla Fuera
 */

error_reporting(0);
header('Content-Type: application/json');

// Usar timezone de México
date_default_timezone_set('America/Mexico_City');

$con = mysqli_connect('db', 'root', 'root', 'mmqro');
if (!$con) { echo json_encode(['success'=>false]); exit; }

// Órdenes pendientes (estatus = 'pendiente')
$pendientes = [];
$sql = "SELECT folio, numero_e, tipo_solicitud, commodity, cantidad, fecha_creacion 
        FROM solicitudes 
        WHERE cantidad > 0
        ORDER BY fecha_creacion DESC LIMIT 20";
$result = mysqli_query($con, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $fecha = new DateTime($row['fecha_creacion'], new DateTimeZone('America/Mexico_City'));
    $ahora = new DateTime('now', new DateTimeZone('America/Mexico_City'));
    $diff = floor(($ahora->getTimestamp() - $fecha->getTimestamp()) / 60);
    
    if ($diff < 60) $tiempo = $diff . ' min';
    elseif ($diff < 1440) $tiempo = floor($diff / 60) . ' hr';
    else $tiempo = floor($diff / 1440) . ' días';
    
    $pendientes[] = [
        'folio' => $row['folio'],
        'solicitante' => $row['numero_e'],
        'commodity' => $row['commodity'],
        'hora_pedido' => $fecha->format('H:i'),
        'tiempo' => $tiempo
    ];
}

// Órdenes en proceso
$proceso = [];
$sql = "SELECT folio, numero_e, tipo_solicitud, commodity, cantidad, fecha_creacion 
        FROM solicitudes 
        WHERE estatus = 'proceso'
        ORDER BY fecha_creacion DESC LIMIT 20";
$result = mysqli_query($con, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $fecha = new DateTime($row['fecha_creacion'], new DateTimeZone('America/Mexico_City'));
    $ahora = new DateTime('now', new DateTimeZone('America/Mexico_City'));
    $diff = floor(($ahora->getTimestamp() - $fecha->getTimestamp()) / 60);
    
    if ($diff < 60) $tiempo = $diff . ' min';
    elseif ($diff < 1440) $tiempo = floor($diff / 60) . ' hr';
    else $tiempo = floor($diff / 1440) . ' días';
    
    $proceso[] = [
        'folio' => $row['folio'],
        'solicitante' => $row['numero_e'],
        'commodity' => $row['commodity'],
        'hora_pedido' => $fecha->format('H:i'),
        'tiempo' => $tiempo
    ];
}

// Órdenes listas para recoger (estatus = 'listo')
$listas = [];
$sql = "SELECT s.folio, s.numero_e, s.commodity, s.fecha_completado
        FROM solicitudes s 
        WHERE s.estatus = 'listo'
        ORDER BY s.fecha_completado DESC LIMIT 20";
$result = mysqli_query($con, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $fecha = new DateTime($row['fecha_completado'], new DateTimeZone('America/Mexico_City'));
    $ahora = new DateTime('now', new DateTimeZone('America/Mexico_City'));
    $diff = floor(($ahora->getTimestamp() - $fecha->getTimestamp()) / 60);
    
    if ($diff < 60) $tiempo = $diff . ' min';
    elseif ($diff < 1440) $tiempo = floor($diff / 60) . ' hr';
    else $tiempo = floor($diff / 1440) . ' días';
    
    $listas[] = [
        'folio' => $row['folio'],
        'solicitante' => $row['numero_e'],
        'commodity' => $row['commodity'],
        'tiempo_lista' => $tiempo
    ];
}

// Concurrencia por hora (últimos 7 días)
$concurrencia = ['labels' => [], 'data' => [], 'colors' => []];
$fecha7dias = date('Y-m-d H:i:s', strtotime('-7 days'));
$sql = "SELECT HOUR(fecha_creacion) as hora, SUM(cantidad) as piezas 
        FROM solicitudes 
        WHERE fecha_creacion >= '$fecha7dias'
        GROUP BY HOUR(fecha_creacion) 
        ORDER BY hora";
$result = mysqli_query($con, $sql);

$dataHora = [];
$maxPiezas = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $dataHora[intval($row['hora'])] = intval($row['piezas']);
    if (intval($row['piezas']) > $maxPiezas) $maxPiezas = intval($row['piezas']);
}

// Llenar todas las horas (0-23)
for ($h = 0; $h < 24; $h++) {
    $concurrencia['labels'][] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
    $piezas = isset($dataHora[$h]) ? $dataHora[$h] : 0;
    $concurrencia['data'][] = $piezas;
    $concurrencia['colors'][] = ($piezas === $maxPiezas && $maxPiezas > 0) ? '#C0392B' : '#3498db';
}

mysqli_close($con);
echo json_encode([
    'success' => true,
    'pendientes' => $pendientes,
    'proceso' => $proceso,
    'listas' => $listas,
    'concurrencia' => $concurrencia
]);
