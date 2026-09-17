<?php
require_once __DIR__ . '/../config/database.php';
/**
 * Crear tabla reportes
 */

$con = conectarDB();
if (!$con) exit;

$sql = "SHOW TABLES LIKE 'reportes'";
$result = mysqli_query($con, $sql);

if (mysqli_num_rows($result) == 0) {
    mysqli_query($con, "CREATE TABLE reportes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        filtros TEXT,
        campos TEXT,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Tabla creada";
} else {
    echo "Tabla ya existe";
}

mysqli_close($con);

