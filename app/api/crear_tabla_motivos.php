<?php
require_once __DIR__ . '/../config/database.php';
/**
 * API - Crear tabla de motivos de cambio de estatus
 */

header('Content-Type: application/json');

$con = conectarDB();
if (!$con) {
    echo json_encode(['success' => false, 'error' => 'Sin conexión']);
    exit;
}

// Crear tabla si no existe
$sql = "CREATE TABLE IF NOT EXISTS motivos_cambio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitud_id INT NOT NULL,
    folio VARCHAR(20) NOT NULL,
    estatus_anterior VARCHAR(20) NOT NULL,
    estatus_nuevo VARCHAR(20) NOT NULL,
    motivo TEXT NOT NULL,
    usuario VARCHAR(50) DEFAULT NULL,
    fecha_cambio DATETIME NOT NULL,
    INDEX idx_folio (folio),
    INDEX idx_solicitud (solicitud_id)
)";

if (mysqli_query($con, $sql)) {
    echo json_encode(['success' => true, 'message' => 'Tabla creada correctamente']);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
}

mysqli_close($con);

