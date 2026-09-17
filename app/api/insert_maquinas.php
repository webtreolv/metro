<?php
require_once __DIR__ . '/../config/database.php';
/**
 * Insertar máquinas desde JSON
 */

header('Content-Type: application/json');

$con = conectarDB();
if (!$con) { echo json_encode(['success'=>false, 'error'=>'Sin conexión']); exit; }

$json = file_get_contents('php://input');
$maquinas = json_decode($json, true);

if (empty($maquinas)) {
    echo json_encode(['success'=>false, 'error'=>'Sin datos']);
    exit;
}

$insertadas = 0;
$errores = [];

foreach ($maquinas as $m) {
    $nombre = mysqli_real_escape_string($con, $m['nombre']);
    $ubicacion = mysqli_real_escape_string($con, $m['ubicacion']);
    
    // Verificar si ya existe
    $check = mysqli_query($con, "SELECT id FROM maquinas WHERE nombre = '$nombre'");
    if (mysqli_num_rows($check) > 0) {
        continue;
    }
    
    $sql = "INSERT INTO maquinas (nombre, ubicacion) VALUES ('$nombre', '$ubicacion')";
    if (mysqli_query($con, $sql)) {
        $insertadas++;
    } else {
        $errores[] = mysqli_error($con);
    }
}

mysqli_close($con);
echo json_encode(['success'=>true, 'insertadas'=>$insertadas, 'errores'=>$errores]);

