<?php
require_once __DIR__ . '/../config/database.php';
/**
 * API - Alta en Catálogo
 */

header('Content-Type: application/json');

$con = conectarDB();
if (!$con) { echo json_encode(['success'=>false]); exit; }

$json = file_get_contents('php://input');
$datos = json_decode($json, true);

$nombre = mysqli_real_escape_string($con, $datos['nombre'] ?? '');
$tipo = $datos['tipo'] ?? '';

if (!$nombre || !$tipo) { echo json_encode(['success'=>false]); exit; }

if ($tipo === 'commodity') {
    $desc = mysqli_real_escape_string($con, $datos['descripcion'] ?? '');
    $sql = "INSERT INTO commodity (nombre, descripcion) VALUES ('$nombre', '$desc')";
} elseif ($tipo === 'maquina') {
    $ubi = mysqli_real_escape_string($con, $datos['ubicacion'] ?? '');
    $sql = "INSERT INTO maquinas (nombre, ubicacion) VALUES ('$nombre', '$ubi')";
} elseif ($tipo === 'personal') {
    $num = mysqli_real_escape_string($con, $datos['numero_e'] ?? '');
    $puesto = mysqli_real_escape_string($con, $datos['puesto'] ?? '');
    $sql = "INSERT INTO personal (numero_e, nombre, puesto) VALUES ('$num', '$nombre', '$puesto')";
} elseif ($tipo === 'tipo_solicitud') {
    $desc = mysqli_real_escape_string($con, $datos['descripcion'] ?? '');
    $sql = "INSERT INTO tipo_solicitud (nombre, descripcion) VALUES ('$nombre', '$desc')";
} else {
    echo json_encode(['success'=>false]); exit;
}

if (mysqli_query($con, $sql)) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false, 'error'=>mysqli_error($con)]);
}

mysqli_close($con);

