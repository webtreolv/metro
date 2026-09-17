<?php
/**
 * API - Catálogos
 */

header('Content-Type: application/json');

$con = mysqli_connect('db', 'root', 'root', 'mmqro');
if (!$con) { echo json_encode(['success'=>false]); exit; }

// Tipo de solicitud
$tipoSolicitud = [];
$sql = "SELECT * FROM tipo_solicitud ORDER BY nombre";
$result = mysqli_query($con, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $tipoSolicitud[] = [
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'descripcion' => $row['descripcion'] ?? '',
            'activo' => $row['activo'] ?? '1'
        ];
    }
}

// Commodity
$commodity = [];
$sql = "SELECT * FROM commodity ORDER BY nombre";
$result = mysqli_query($con, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $commodity[] = [
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'descripcion' => $row['descripcion'] ?? '',
            'activo' => $row['activo'] ?? '1'
        ];
    }
}

// Máquinas
$maquinas = [];
$sql = "SELECT * FROM maquinas ORDER BY nombre";
$result = mysqli_query($con, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $maquinas[] = [
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'ubicacion' => $row['ubicacion'] ?? '',
            'activo' => $row['activo'] ?? '1'
        ];
    }
}

// Personal
$personal = [];
$sql = "SELECT * FROM personal ORDER BY nombre";
$result = mysqli_query($con, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $personal[] = [
            'id' => $row['id'],
            'numero_e' => $row['numero_e'],
            'nombre' => $row['nombre'],
            'puesto' => $row['puesto'] ?? '',
            'activo' => $row['activo'] ?? '1'
        ];
    }
}

mysqli_close($con);
echo json_encode([
    'success' => true,
    'tipo_solicitud' => $tipoSolicitud,
    'commodity' => $commodity,
    'maquinas' => $maquinas,
    'personal' => $personal
]);
