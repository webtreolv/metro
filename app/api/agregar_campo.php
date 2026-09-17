<?php
/**
 * API - Agregar campo a tabla
 */

header('Content-Type: application/json');

$campo = $_GET['campo'] ?? '';
$tipo = $_GET['tipo'] ?? 'VARCHAR';
$tamano = $_GET['tamano'] ?? '100';

if (!$campo) {
    echo json_encode(['success'=>false, 'error'=>'Campo requerido']);
    exit;
}

$con = mysqli_connect('db', 'root', 'root', 'mmqro');
if (!$con) {
    echo json_encode(['success'=>false, 'error'=>'Sin conexión']);
    exit;
}

// Verificar si ya existe
$check = mysqli_query($con, "SHOW COLUMNS FROM solicitudes LIKE '$campo'");
if (mysqli_num_rows($check) > 0) {
    echo json_encode(['success'=>true, 'message'=>'El campo ya existe']);
    exit;
}

// Agregar campo
$sql = "ALTER TABLE solicitudes ADD $campo $tipo($tamano) NULL";
if (mysqli_query($con, $sql)) {
    echo json_encode(['success'=>true, 'message'=>'Campo agregado']);
} else {
    echo json_encode(['success'=>false, 'error'=>mysqli_error($con)]);
}

mysqli_close($con);
