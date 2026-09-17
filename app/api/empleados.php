<?php
/**
 * API - Empleados
 */

header('Content-Type: application/json');

$con = mysqli_connect('db', 'root', 'root', 'mmqro');
if (!$con) { echo json_encode(['success'=>false]); exit; }

$sql = "SELECT numero_e, nombre FROM personal WHERE activo = 1 ORDER BY nombre";
$result = mysqli_query($con, $sql);

$empleados = [];
while ($row = mysqli_fetch_assoc($result)) {
    $empleados[] = $row;
}

mysqli_close($con);
echo json_encode(['success'=>true, 'empleados'=>$empleados]);
