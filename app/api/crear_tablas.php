<?php
/**
 * Crear tabla tipo_solicitud si no existe
 */

$con = mysqli_connect('db', 'root', 'root', 'mmqro');
if (!$con) exit;

// Verificar si la tabla existe
$sql = "SHOW TABLES LIKE 'tipo_solicitud'";
$result = mysqli_query($con, $sql);

if (mysqli_num_rows($result) == 0) {
    // Crear tabla
    mysqli_query($con, "CREATE TABLE tipo_solicitud (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL,
        activo TINYINT DEFAULT 1
    )");
    
    // Insertar datos
    mysqli_query($con, "INSERT INTO tipo_solicitud (nombre) VALUES ('Producción')");
    mysqli_query($con, "INSERT INTO tipo_solicitud (nombre) VALUES ('PPAP')");
    mysqli_query($con, "INSERT INTO tipo_solicitud (nombre) VALUES ('Pruebas Ingeniería')");
    mysqli_query($con, "INSERT INTO tipo_solicitud (nombre) VALUES ('Incoming')");
    
    echo "Tabla creada";
} else {
    echo "Tabla ya existe";
}

mysqli_close($con);
