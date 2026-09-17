<?php
/**
 * API - Generar Folio
 * Formato: MYYMM-CONSECUTIVO (ej: M2606-001)
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

$con = mysqli_connect('db', 'root', 'root', 'mmqro');
if (!$con) { echo json_encode(['folio'=>'M001001']); exit; }
mysqli_query($con, "SET time_zone = '-05:00'");

// Obtener año y mes actuales (2 dígitos cada uno)
$anio = date('y');       // 26 para 2026
$mes = date('m');        // 06 para junio

// Contar registros del mes y año actual
$anio = date('y');       // 26 para 2026
$mes = date('m');        // 06 para junio

$prefix = "M{$anio}{$mes}-";

$sql = "SELECT MAX(CAST(SUBSTRING_INDEX(folio, '-', -1) AS UNSIGNED)) as max_val FROM solicitudes WHERE folio LIKE '{$prefix}%'";
$result = mysqli_query($con, $sql);
$row = mysqli_fetch_assoc($result);
$sig = ($row['max_val'] ?? 0) + 1;
$sig = str_pad($sig, 3, '0', STR_PAD_LEFT);

// Generar folio dinámico: M + AA + MM + - + consecutivo
$folio = $prefix . $sig;

mysqli_close($con);
echo json_encode(['folio' => $folio]);
