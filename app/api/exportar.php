<?php
require_once __DIR__ . '/../config/database.php';
/**
 * API - Exportar Reportes (Excel/PDF)
 */

$con = conectarDB();
if (!$con) { echo json_encode(['success'=>false]); exit; }

// Guardar reporte
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $nombre = mysqli_real_escape_string($con, $data['nombre'] ?? 'Reporte');
    $filtros = json_encode($data['filtros'] ?? []);
    $campos = json_encode($data['campos'] ?? []);
    
    mysqli_query($con, "INSERT INTO reportes (nombre, filtros, campos) VALUES ('$nombre', '$filtros', '$campos')");
    
    echo json_encode(['success'=>true, 'id'=>mysqli_insert_id($con)]);
    mysqli_close($con);
    exit;
}

$accion = $_GET['accion'] ?? '';

// Listar reportes guardados
if ($accion === 'listar') {
    $result = mysqli_query($con, "SELECT * FROM reportes ORDER BY fecha_creacion DESC");
    $reportes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Contar registros
        $filtros = json_decode($row['filtros'], true);
        $where = "1=1";
        if (!empty($filtros['tipo'])) $where .= " AND tipo_solicitud = '{$filtros['tipo']}'";
        if (!empty($filtros['estatus'])) $where .= " AND estatus = '{$filtros['estatus']}'";
        
        $cnt = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM solicitudes WHERE $where"));
        $row['registros'] = $cnt['c'];
        $reportes[] = $row;
    }
    echo json_encode(['success'=>true, 'reportes'=>$reportes]);
    mysqli_close($con);
    exit;
}

// Eliminar reporte
if ($accion === 'eliminar') {
    $id = intval($_GET['id']);
    mysqli_query($con, "DELETE FROM reportes WHERE id = $id");
    echo json_encode(['success'=>true]);
    mysqli_close($con);
    exit;
}

// exportar desde reporte guardado
$id = intval($_GET['id'] ?? 0);

// Valores por defecto
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$estatus = $_GET['estatus'] ?? '';
$commodity = $_GET['commodity'] ?? '';
$solicitante = $_GET['solicitante'] ?? '';
$area = $_GET['area'] ?? '';
$min_piezas = intval($_GET['min_piezas'] ?? 0);
$ordenar = $_GET['ordenar'] ?? 'fecha_creacion DESC';
$formato = $_GET['formato'] ?? 'excel';
$nombre = $_GET['nombre'] ?? 'Reporte';
$campos_get = $_GET['campos'] ?? '';

if ($id > 0) {
    $res = mysqli_query($con, "SELECT * FROM reportes WHERE id = $id");
    if ($rep = mysqli_fetch_assoc($res)) {
        $nombre = $rep['nombre'];
        $filtros = json_decode($rep['filtros'], true);
        $fecha_inicio = $filtros['fecha_inicio'] ?? $fecha_inicio;
        $fecha_fin = $filtros['fecha_fin'] ?? $fecha_fin;
        $tipo = $filtros['tipo'] ?? $tipo;
        $estatus = $filtros['estatus'] ?? $estatus;
        $commodity = $filtros['commodity'] ?? $commodity;
        $solicitante = $filtros['solicitante'] ?? $solicitante;
        
        $campos_json = json_decode($rep['campos'], true);
        if (is_array($campos_json) && !empty($campos_json)) {
            $campos_get = implode(',', $campos_json);
        }
    }
}

$where = "1=1";
if ($fecha_inicio) $where .= " AND fecha_creacion >= '$fecha_inicio'";
if ($fecha_fin) $where .= " AND fecha_creacion <= '$fecha_fin 23:59:59'";
if ($tipo) $where .= " AND tipo_solicitud = '$tipo'";
if ($estatus) $where .= " AND estatus = '$estatus'";
if ($commodity) $where .= " AND commodity = '$commodity'";
if ($solicitante) $where .= " AND numero_e LIKE '%$solicitante%'";
if ($area) $where .= " AND area = '$area'";
if ($min_piezas) $where .= " AND cantidad >= $min_piezas";

$sql = "SELECT * FROM solicitudes WHERE $where ORDER BY $ordenar";
$result = mysqli_query($con, $sql);

$solicitudes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $solicitudes[] = $row;
}

mysqli_close($con);

if (empty($solicitudes)) {
    echo json_encode(['success'=>false, 'error'=>'No hay datos']);
    exit;
}

// Definir nombres de columnas
$mapa_campos = [
    'folio' => 'Folio',
    'fecha' => 'Fecha',
    'solicitante' => 'Solicitante',
    'tipo' => 'Tipo',
    'commodity' => 'Commodity',
    'cantidad' => 'Cantidad',
    'estatus' => 'Estatus',
    'descripcion' => 'Descripción',
    'numero_parte' => 'Número de Parte',
    'maquina' => 'Máquina'
];

$campos_arr = $campos_get ? explode(',', $campos_get) : ['folio','fecha','solicitante','tipo','commodity','cantidad','estatus'];

if ($formato === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="'.$nombre.'.xls"');
    
    echo '<table border="1">';
    echo '<tr style="background:#C0392B;color:white;">';
    foreach ($campos_arr as $c) {
        if (isset($mapa_campos[$c])) echo '<th>'.$mapa_campos[$c].'</th>';
    }
    echo '</tr>';
    
    foreach ($solicitudes as $s) {
        $bg = $s['estatus'] === 'listo' ? '#d4edda' : ($s['estatus'] === 'pendiente' ? '#fff3cd' : '#cce5ff');
        echo '<tr style="background:'.$bg.';">';
        foreach ($campos_arr as $c) {
            if ($c === 'fecha') {
                echo '<td>'.date('d/m/Y H:i', strtotime($s['fecha_creacion'])).'</td>';
            } elseif ($c === 'solicitante') {
                echo '<td>'.$s['numero_e'].'</td>';
            } else {
                echo '<td>'.htmlspecialchars($s[$c] ?? '').'</td>';
            }
        }
        echo '</tr>';
    }
    echo '</table>';
    exit;
}

// Generar HTML para PDF
$html = '<html><head>
<style>
body { font-family: Arial, sans-serif; }
table { width: 100%; border-collapse: collapse; font-size: 10px; }
th { background: #C0392B; color: white; padding: 8px; text-align: left; }
td { padding: 6px; border-bottom: 1px solid #ddd; }
tr:nth-child(even) { background: #f9f9f9; }
h1 { color: #C0392B; }
</style>
</head><body>';
$html .= '<h1>'.$nombre.'</h1>';
$html .= '<p>Generado: '.date('d/m/Y H:i').'</p>';
$html .= '<p>Total de registros: '.count($solicitudes).'</p>';

$html .= '<table><tr>';
foreach ($campos_arr as $c) {
    if (isset($mapa_campos[$c])) $html .= '<th>'.$mapa_campos[$c].'</th>';
}
$html .= '</tr>';

foreach ($solicitudes as $s) {
    $bg = $s['estatus'] === 'listo' ? '#d4edda' : ($s['estatus'] === 'pendiente' ? '#fff3cd' : '#cce5ff');
    $html .= '<tr style="background:'.$bg.';">';
    foreach ($campos_arr as $c) {
        if ($c === 'fecha') {
            $html .= '<td>'.date('d/m/Y H:i', strtotime($s['fecha_creacion'])).'</td>';
        } elseif ($c === 'solicitante') {
            $html .= '<td>'.$s['numero_e'].'</td>';
        } else {
            $html .= '<td>'.htmlspecialchars($s[$c] ?? '').'</td>';
        }
    }
    $html .= '</tr>';
}
$html .= '</table></body></html>';

// Guardar HTML temporal
$file = tempnam(sys_get_temp_dir(), 'reporte_');
file_put_contents($file.'.html', $html);

// Descargar como archivo HTML
header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="'.$nombre.'.html"');
readfile($file.'.html');
unlink($file);
unlink($file.'.html');
exit;

