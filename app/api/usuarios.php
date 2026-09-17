<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$con = conectarDB();

if (!$con) {
    echo json_encode(['success' => false, 'error' => 'No connection']);
    exit;
}

// GET - Listar o buscar por ID
if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $result = mysqli_query($con, "SELECT * FROM usuarios WHERE id = $id");
        $row = mysqli_fetch_assoc($result);
        echo json_encode(['success' => true, 'usuarios' => $row ? [$row] : []]);
    } else {
        $result = mysqli_query($con, "SELECT id, numero_e, nombre, rol, estatus FROM usuarios ORDER BY id");
        $usuarios = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $usuarios[] = $row;
        }
        echo json_encode(['success' => true, 'usuarios' => $usuarios]);
    }
    mysqli_close($con);
    exit;
}

// POST - Crear o actualizar
if ($method === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $numero_e = mysqli_real_escape_string($con, $_POST['numero_e'] ?? '');
    $nombre = mysqli_real_escape_string($con, $_POST['nombre'] ?? '');
    $rol = mysqli_real_escape_string($con, $_POST['rol'] ?? 'usuario');
    $estatus = mysqli_real_escape_string($con, $_POST['estatus'] ?? 'activo');
    $password = $_POST['password'] ?? '';
    
    if (!$numero_e || !$nombre) {
        echo json_encode(['success' => false, 'error' => 'Faltan datos']);
        mysqli_close($con);
        exit;
    }
    
    if ($id > 0) {
        // Actualizar
        $sql = "UPDATE usuarios SET numero_e='$numero_e', nombre='$nombre', rol='$rol', estatus='$estatus'";
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ", password='$hash'";
        }
        $sql .= " WHERE id = $id";
    } else {
        // Crear
        if (!$password) {
            echo json_encode(['success' => false, 'error' => 'Password requerido']);
            mysqli_close($con);
            exit;
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (numero_e, nombre, rol, estatus, password) VALUES ('$numero_e', '$nombre', '$rol', '$estatus', '$hash')";
    }
    
    if (mysqli_query($con, $sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
    }
    mysqli_close($con);
    exit;
}

// DELETE - Eliminar
if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if ($id > 0) {
        mysqli_query($con, "DELETE FROM usuarios WHERE id = $id");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'ID requerido']);
    }
    mysqli_close($con);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Método no soportado']);

