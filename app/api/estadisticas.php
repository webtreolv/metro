<?php
/**
 * API - Estadísticas y Gráficas
 */

header('Content-Type: application/json');

// Usar timezone de México
date_default_timezone_set('America/Mexico_City');

$con = mysqli_connect('db', 'root', 'root', 'mmqro');
if (!$con) { echo json_encode(['success'=>false]); exit; }

// Stats básicos
$stats = ['total'=>0, 'pendientes'=>0, 'proceso'=>0, 'listos'=>0, 'piezas'=>0];
$sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN estatus='pendiente' THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN estatus='proceso' THEN 1 ELSE 0 END) as proceso,
    SUM(CASE WHEN estatus='listo' THEN 1 ELSE 0 END) as listos,
    SUM(CASE WHEN estatus='entregado' THEN 1 ELSE 0 END) as piezas
FROM solicitudes";
$result = mysqli_query($con, $sql);
if ($row = mysqli_fetch_assoc($result)) {
    $stats = [
        'total' => intval($row['total']),
        'pendientes' => intval($row['pendientes']),
        'proceso' => intval($row['proceso']),
        'listos' => intval($row['listos'] ?? 0),
        'piezas' => intval($row['piezas'] ?? 0)
    ];
}

// Chart: por tipo
$chartTipo = [];
$sql = "SELECT tipo_solicitud as tipo, COUNT(*) as cantidad FROM solicitudes GROUP BY tipo_solicitud";
$result = mysqli_query($con, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $chartTipo[] = ['tipo'=>$row['tipo'], 'cantidad'=>intval($row['cantidad'])];
}

// Chart: por commodity (top 5)
$chartCommodity = [];
$sql = "SELECT commodity, COUNT(*) as cantidad FROM solicitudes GROUP BY commodity ORDER BY cantidad DESC LIMIT 5";
$result = mysqli_query($con, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $chartCommodity[] = ['commodity'=>$row['commodity'], 'cantidad'=>intval($row['cantidad'])];
}

// Chart: últimos 7 días
$chartDias = [];
$fecha7dias = date('Y-m-d', strtotime('-7 days'));
$sql = "SELECT DATE(fecha_creacion) as dia, COUNT(*) as cantidad 
        FROM solicitudes 
        WHERE fecha_creacion >= '$fecha7dias'
        GROUP BY DATE(fecha_creacion)";
$result = mysqli_query($con, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $chartDias[] = ['dia'=>$row['dia'], 'cantidad'=>intval($row['cantidad'])];
}

mysqli_close($con);

echo json_encode([
    'success' => true,
    'stats' => $stats,
    'charts' => [
        'tipo' => $chartTipo,
        'commodity' => $chartCommodity,
        'dias' => $chartDias
    ]
]);
