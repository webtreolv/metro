<?php
require_once __DIR__ . '/../config/database.php';
/**
 * API - Login
 */

header('Content-Type: application/json');

$con = conectarDB();
if (!$con) { echo json_encode(['success'=>false, 'error'=>'Sin conexión']); exit; }

$json = file_get_contents('php://input');
$datos = json_decode($json, true);

$numero_e = mysqli_real_escape_string($con, $datos['numero_e'] ?? '');
$password = $datos['password'] ?? '';

if (!$numero_e || !$password) {
    echo json_encode(['success'=>false, 'error'=>'Usuario y contraseña requeridos']); exit; }

$sql = "SELECT * FROM usuarios WHERE numero_e = '$numero_e' AND estatus = 'activo'";
$result = mysqli_query($con, $sql);

if ($row = mysqli_fetch_assoc($result)) {
    if (password_verify($password, $row['password'])) {
        // Iniciar sesión
        session_start();
        $_SESSION['usuario_id'] = $row['id'];
        $_SESSION['numero_e'] = $row['numero_e'];
        $_SESSION['nombre'] = $row['nombre'];
        $_SESSION['rol'] = $row['rol'];
        
        $redirect = '/admin/reportes.php';
        if ($row['rol'] === 'laboratorio') $redirect = '/laboratorio/tablero.php';
        elseif ($row['rol'] === 'usuario') $redirect = '/index.html';
        
        echo json_encode(['success'=>true, 'redirect'=>$redirect]);
    } else {
        echo json_encode(['success'=>false, 'error'=>'Contraseña incorrecta']);
    }
} else {
    echo json_encode(['success'=>false, 'error'=>'Usuario no encontrado']);
}

mysqli_close($con);


