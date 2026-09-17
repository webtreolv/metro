<?php
/**
 * API - Consultar Solicitud
 * MMQRO - Sin sesión, sin roles
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
$con = conectarDB();

if (!$con) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión']);
    exit;
}

$folio = mysqli_real_escape_string($con, $_GET['folio'] ?? '');

if (!$folio) {
    echo json_encode(['success' => false, 'error' => 'Folio requerido']);
    exit;
}

$sql = "SELECT * FROM solicitudes WHERE folio = '$folio'";
$result = mysqli_query($con, $sql);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode([
        'success' => true,
        'solicitud' => [
            'folio' => $row['folio'],
            'nombre_solicitante' => $row['nombre_solicitante'],
            'email' => $row['email'],
            'area' => $row['area'],
            'tipo_solicitud' => $row['tipo_solicitud'],
            'commodity' => $row['commodity'],
            'descripcion' => $row['descripcion'],
            'cantidad' => $row['cantidad'],
            'estatus' => $row['estatus'],
            'fecha' => date('d/m/Y H:i', strtotime($row['fecha_creacion']))
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Folio no encontrado']);
}

mysqli_close($con);
