<?php
/**
 * Importar máquinas desde maquina.txt
 * Ejecutar una sola vez al acceder
 */

header('Content-Type: application/json');

// Conexión a BD
$con = mysqli_connect('db', 'root', 'root', 'mmqro');
if (!$con) {
    echo json_encode(['success' => false, 'error' => 'Sin conexión a BD']);
    exit;
}

// Leer archivo maquina.txt
$archivo = __DIR__ . '/../maquina.txt';
if (!file_exists($archivo)) {
    echo json_encode(['success' => false, 'error' => 'Archivo maquina.txt no encontrado']);
    exit;
}

$contenido = file_get_contents($archivo);
$lineas = explode("\n", $contenido);

$importados = 0;
$errores = 0;

foreach ($lineas as $linea) {
    $linea = trim($linea);
    if (empty($linea)) continue;
    
    // Saltar encabezado
    if (stripos($linea, 'Máquina') !== false) continue;
    
    // Separar por tab
    $partes = explode("\t", $linea);
    if (count($partes) < 2) continue;
    
    $nombre = trim($partes[0]); // Código ATS
    $ubicacion = trim($partes[1]); // Nombre de máquina
    
    // Verificar si ya existe
    $check = mysqli_prepare($con, "SELECT id FROM maquinas WHERE nombre = ?");
    mysqli_stmt_bind_param($check, "s", $nombre);
    mysqli_stmt_execute($check);
    $result = mysqli_stmt_get_result($check);
    
    if (mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($check);
        continue; // Ya existe
    }
    mysqli_stmt_close($check);
    
    // Insertar
    $stmt = mysqli_prepare($con, "INSERT INTO maquinas (nombre, ubicacion, activo) VALUES (?, ?, 1)");
    mysqli_stmt_bind_param($stmt, "ss", $nombre, $ubicacion);
    
    if (mysqli_stmt_execute($stmt)) {
        $importados++;
    } else {
        $errores++;
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($con);

echo json_encode([
    'success' => true,
    'importadas' => $importados,
    'errores' => $errores
]);
