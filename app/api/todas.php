<?php
/**
 * API - Todas las Solicitudes
 */

header('Content-Type: application/json');

// Usar timezone de México
date_default_timezone_set('America/Mexico_City');

$estatus = $_GET['estatus'] ?? '';
$folio = $_GET['folio'] ?? '';

$con = mysqli_connect('db', 'root', 'root', 'mmqro');
if (!$con) { echo json_encode(['success'=>false, 'error'=>'Sin conexión']); exit; }

// Verificar y agregar columnas si no existen
$cols = mysqli_query($con, "SHOW COLUMNS FROM solicitudes LIKE 'hora_lista'");
if (mysqli_num_rows($cols) == 0) {
    mysqli_query($con, "ALTER TABLE solicitudes ADD COLUMN hora_lista TIME DEFAULT NULL");
}
$cols = mysqli_query($con, "SHOW COLUMNS FROM solicitudes LIKE 'hora_entrega'");
if (mysqli_num_rows($cols) == 0) {
    mysqli_query($con, "ALTER TABLE solicitudes ADD COLUMN hora_entrega TIME DEFAULT NULL");
}
$cols = mysqli_query($con, "SHOW COLUMNS FROM solicitudes LIKE 'fecha_entrega'");
if (mysqli_num_rows($cols) == 0) {
    mysqli_query($con, "ALTER TABLE solicitudes ADD COLUMN fecha_entrega DATE DEFAULT NULL");
}
$cols = mysqli_query($con, "SHOW COLUMNS FROM solicitudes LIKE 'numero_parte'");
if (mysqli_num_rows($cols) == 0) {
    mysqli_query($con, "ALTER TABLE solicitudes ADD COLUMN numero_parte VARCHAR(100) DEFAULT NULL");
}
$cols = mysqli_query($con, "SHOW COLUMNS FROM solicitudes LIKE 'nombre_solicitante'");
if (mysqli_num_rows($cols) == 0) {
    mysqli_query($con, "ALTER TABLE solicitudes ADD COLUMN nombre_solicitante VARCHAR(200) DEFAULT NULL");
}

$where = [];
if ($estatus === 'listo') {
    // Mostrar solo solicitudes pendientes de entrega (no entregadas)
    $sql = "SELECT s.*, p.nombre as nombre_solicitante FROM solicitudes s LEFT JOIN personal p ON s.numero_e = p.numero_e";
    $sql .= " WHERE estatus != 'entregado'";
    $sql .= " ORDER BY fecha_completado DESC, fecha_creacion DESC LIMIT 50";
    $result = mysqli_query($con, $sql);
    $solicitudes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $solicitudes[] = $row;
    }
    mysqli_close($con);
    echo json_encode(['success'=>true, 'solicitudes'=>$solicitudes]);
    exit;
} elseif ($estatus) {
    $where[] = "estatus = '$estatus'";
}
if ($folio) $where[] = "folio = '$folio'";

$sql = "SELECT s.*, p.nombre as nombre_solicitante FROM solicitudes s LEFT JOIN personal p ON s.numero_e = p.numero_e";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
// Ordenar por fecha más reciente según el estatus
if ($estatus === 'entregado') {
    $sql .= " ORDER BY fecha_entrega DESC, hora_entrega DESC";
} elseif ($estatus === 'listo') {
    $sql .= " ORDER BY fecha_completado DESC, hora_lista DESC";
} else {
    $sql .= " ORDER BY fecha_creacion DESC";
}

$result = mysqli_query($con, $sql);
$solicitudes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $solicitudes[] = $row;
}

mysqli_close($con);
echo json_encode(['success'=>true, 'solicitudes'=>$solicitudes]);
