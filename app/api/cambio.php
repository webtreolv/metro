<?php
/**
 * API - Cambio (Update)
 */

header('Content-Type: application/json');

$con = mysqli_connect('db', 'root', 'root', 'mmqro');
if (!$con) { echo json_encode(['success'=>false, 'error'=>'Conexion fallida']); exit; }

$data = json_decode(file_get_contents('php://input'), true);
$tipo = $data['tipo'] ?? '';
$id = intval($data['id'] ?? 0);

if (!$tipo || !$id) {
    echo json_encode(['success'=>false, 'error'=>'Datos incompletos']);
    exit;
}

$success = false;
$error = '';

switch ($tipo) {
    case 'commodity':
        $nombre = mysqli_real_escape_string($con, $data['nombre'] ?? '');
        $descripcion = mysqli_real_escape_string($con, $data['descripcion'] ?? '');
        $sql = "UPDATE commodity SET nombre='$nombre', descripcion='$descripcion' WHERE id=$id";
        $success = mysqli_query($con, $sql);
        break;
        
    case 'maquina':
        $nombre = mysqli_real_escape_string($con, $data['nombre'] ?? '');
        $ubicacion = mysqli_real_escape_string($con, $data['ubicacion'] ?? '');
        $sql = "UPDATE maquinas SET nombre='$nombre', ubicacion='$ubicacion' WHERE id=$id";
        $success = mysqli_query($con, $sql);
        break;
        
    case 'personal':
        $numero_e = mysqli_real_escape_string($con, $data['numero_e'] ?? '');
        $nombre = mysqli_real_escape_string($con, $data['nombre'] ?? '');
        $puesto = mysqli_real_escape_string($con, $data['puesto'] ?? '');
        $sql = "UPDATE personal SET numero_e='$numero_e', nombre='$nombre', puesto='$puesto' WHERE id=$id";
        $success = mysqli_query($con, $sql);
        break;
        
    case 'tipo_solicitud':
        $nombre = mysqli_real_escape_string($con, $data['nombre'] ?? '');
        $descripcion = mysqli_real_escape_string($con, $data['descripcion'] ?? '');
        $sql = "UPDATE tipo_solicitud SET nombre='$nombre', descripcion='$descripcion' WHERE id=$id";
        $success = mysqli_query($con, $sql);
        break;
        
    default:
        $error = 'Tipo no válido';
}

mysqli_close($con);
echo json_encode(['success'=>$success, 'error'=>$error ?: '']);
