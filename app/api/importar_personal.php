<?php
/**
 * Importar personal desde info.txt
 * Ejecutar una sola vez al acceder
 */

header('Content-Type: application/json');

// Conexión a BD
$con = mysqli_connect('db', 'root', 'root', 'mmqro');
if (!$con) {
    echo json_encode(['success' => false, 'error' => 'Sin conexión a BD']);
    exit;
}

// Vaciar tabla personal antes de importar
mysqli_query($con, "TRUNCATE TABLE personal");

// Leer archivo info.txt
$archivo = __DIR__ . '/../info.txt';
if (!file_exists($archivo)) {
    echo json_encode(['success' => false, 'error' => 'Archivo info.txt no encontrado']);
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
    if (stripos($linea, 'Número') !== false) continue;
    
    // Separar Número E y Nombre por tab
    $partes = explode("\t", $linea);
    if (count($partes) < 2) continue;
    
    $numero_e = trim($partes[0]);
    $nombre = trim($partes[1]);
    
    // Formatear número E con prefijo E
    if (is_numeric($numero_e)) {
        $numero_e = 'E' . $numero_e;
    }
    
    // Verificar si ya existe
    $check = mysqli_prepare($con, "SELECT id FROM personal WHERE numero_e = ?");
    mysqli_stmt_bind_param($check, "s", $numero_e);
    mysqli_stmt_execute($check);
    $result = mysqli_stmt_get_result($check);
    
    if (mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($check);
        continue; // Ya existe, saltar
    }
    mysqli_stmt_close($check);
    
    // Insertar
    $stmt = mysqli_prepare($con, "INSERT INTO personal (numero_e, nombre, activo) VALUES (?, ?, 1)");
    mysqli_stmt_bind_param($stmt, "ss", $numero_e, $nombre);
    
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
    'importados' => $importados,
    'errores' => $errores
]);
