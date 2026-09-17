<?php
require_once __DIR__ . '/../config/database.php';
/**
 * Insertar tipos de solicitud
 */

$con = conectarDB();
if (!$con) exit;

$sql = "DELETE FROM tipo_solicitud";
mysqli_query($con, $sql);

$tipos = ['Producción', 'PPAP', 'Pruebas Ingeniería', 'Incoming'];
foreach ($tipos as $t) {
    mysqli_query($con, "INSERT INTO tipo_solicitud (nombre) VALUES ('$t')");
}

echo "Datos insertados";

mysqli_close($con);

