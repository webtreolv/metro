<?php
require_once __DIR__ . '/../config/database.php';
/**
 * API - Baja en Catálogo
 */

header('Content-Type: application/json');

$con = conectarDB();
if (!$con) { echo json_encode(['success'=>false]); exit; }

$json = file_get_contents('php://input');
$datos = json_decode($json, true);

$id = intval($datos['id'] ?? 0);
$tipo = $datos['tipo'] ?? '';

if (!$id || !$tipo) { echo json_encode(['success'=>false]); exit; }

if ($tipo === 'commodity') {
    $sql = "DELETE FROM commodity WHERE id = $id";
} elseif ($tipo === 'maquina') {
    $sql = "DELETE FROM maquinas WHERE id = $id";
} elseif ($tipo === 'personal') {
    $sql = "DELETE FROM personal WHERE id = $id";
} elseif ($tipo === 'tipo_solicitud') {
    $sql = "DELETE FROM tipo_solicitud WHERE id = $id";
} else {
    echo json_encode(['success'=>false]); exit;
}

if (mysqli_query($con, $sql)) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false]);
}

mysqli_close($con);

