<?php
/**
 * API - Solicitudes CRUD
 * MMQRO - Sistema de Gestión de Metrología
 */

// Configurar timezone México
date_default_timezone_set('America/Mexico_City');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];
$con = mysqli_connect('db', 'root', 'root', 'mmqro');

if (!$con) {
    echo json_encode(['success' => false, 'error' => 'Sin conexión a la base de datos']);
    exit;
}

// Usar timezone de PHP directamente
$fechaActual = date('Y-m-d H:i:s');

// Obtener datos según método
if ($method === 'GET') {
    $action = $_GET['action'] ?? 'listar';
    $id = $_GET['id'] ?? null;
    $folio = $_GET['folio'] ?? null;
} else {
    $json = file_get_contents('php://input');
    $datos = json_decode($json, true);
    $action = $datos['action'] ?? ($_GET['action'] ?? 'listar');
    $id = $datos['id'] ?? $_GET['id'] ?? null;
    $folio = $datos['folio'] ?? $_GET['folio'] ?? null;
}

responder($con, $method, $action, $id, $folio, $datos);

mysqli_close($con);

/**
 * Función principal para procesar las solicitudes
 */
function responder($con, $method, $action, $id, $folio, $datos) {
    switch ($action) {
        case 'listar':
            listarSolicitudes($con);
            break;
        case 'listarPendientes':
            listarPendientes($con);
            break;
        case 'listarProceso':
            listarProceso($con);
            break;
        case 'listarListos':
            listarListos($con);
            break;
        case 'ver':
            verSolicitud($con, $id, $folio);
            break;
        case 'crear':
            crearSolicitud($con, $datos);
            break;
        case 'editar':
            editarSolicitud($con, $id, $datos);
            break;
        case 'eliminar':
            eliminarSolicitud($con, $id, $folio);
            break;
        case 'aProceso':
            cambiarEstatus($con, $id, $folio, 'proceso');
            break;
        case 'aPendiente':
            cambiarEstatus($con, $id, $folio, 'pendiente');
            break;
        case 'completar':
            cambiarEstatus($con, $id, $folio, 'listo');
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Acción no válida']);
    }
}

/**
 * Listar todas las solicitudes
 */
function listarSolicitudes($con) {
    $sql = "SELECT s.*, u.nombre as nombre_solicitante 
            FROM solicitudes s
            LEFT JOIN usuarios u ON s.numero_e = u.numero_e
            ORDER BY s.fecha_creacion DESC";
    
    $result = mysqli_query($con, $sql);
    $solicitudes = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $solicitudes[] = formatearSolicitud($row);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $solicitudes
    ]);
}

/**
 * Listar solicitudes pendientes
 */
function listarPendientes($con) {
    $sql = "SELECT s.*, u.nombre as nombre_solicitante 
            FROM solicitudes s
            LEFT JOIN usuarios u ON s.numero_e = u.numero_e
            WHERE s.estatus = 'pendiente'
            ORDER BY s.fecha_creacion ASC";
    
    $result = mysqli_query($con, $sql);
    $solicitudes = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $solicitudes[] = formatearSolicitud($row);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $solicitudes
    ]);
}

/**
 * Listar solicitudes en proceso
 */
function listarProceso($con) {
    $sql = "SELECT s.*, u.nombre as nombre_solicitante 
            FROM solicitudes s
            LEFT JOIN usuarios u ON s.numero_e = u.numero_e
            WHERE s.estatus = 'proceso'
            ORDER BY s.fecha_creacion ASC";
    
    $result = mysqli_query($con, $sql);
    $solicitudes = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $solicitudes[] = formatearSolicitud($row);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $solicitudes
    ]);
}

/**
 * Listar solicitudes listas/completadas
 */
function listarListos($con) {
    $sql = "SELECT s.*, u.nombre as nombre_solicitante 
            FROM solicitudes s
            LEFT JOIN usuarios u ON s.numero_e = u.numero_e
            WHERE s.estatus = 'listo'
            ORDER BY s.fecha_completado DESC
            LIMIT 50";
    
    $result = mysqli_query($con, $sql);
    $solicitudes = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $solicitudes[] = formatearSolicitud($row);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $solicitudes
    ]);
}

/**
 * Ver una solicitud específica
 */
function verSolicitud($con, $id, $folio) {
    $id = intval($id);
    $folio = mysqli_real_escape_string($con, $folio ?? '');
    
    $where = "";
    if ($id > 0) {
        $where = "WHERE s.id = $id";
    } elseif ($folio) {
        $where = "WHERE s.folio = '$folio'";
    }
    
    if (!$where) {
        echo json_encode(['success' => false, 'error' => 'ID o Folio requerido']);
        return;
    }
    
    $sql = "SELECT s.*, u.nombre as nombre_solicitante 
            FROM solicitudes s
            LEFT JOIN usuarios u ON s.numero_e = u.numero_e
            $where
            LIMIT 1";
    
    $result = mysqli_query($con, $sql);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode([
            'success' => true,
            'data' => formatearSolicitud($row)
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Solicitud no encontrada']);
    }
}

/**
 * Crear nueva solicitud
 */
function crearSolicitud($con, $datos) {
    // Validar campos requeridos
    $requeridos = ['numero_e', 'codigo_tarjeta', 'tipo_solicitud', 'commodity'];
    foreach ($requeridos as $campo) {
        if (empty($datos[$campo])) {
            echo json_encode(['success' => false, 'error' => "Campo requerido: $campo"]);
            return;
        }
    }
    
    // Generar folio automático
    global $fechaActual;
    $mes = date('m');
    $anio = date('y');
    $sql = "SELECT COUNT(*) + 1 as sig FROM solicitudes WHERE MONTH(fecha_creacion) = MONTH(NOW()) AND YEAR(fecha_creacion) = YEAR(NOW())";
    $res = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($res);
    $sig = str_pad($row['sig'] ?? 1, 3, '0', STR_PAD_LEFT);
    $folio = "M{$anio}{$mes}-{$sig}";
    
    // Insertar
    $sql = "INSERT INTO solicitudes 
        (folio, numero_e, codigo_tarjeta, tipo_solicitud, commodity, maquina, cantidad, motivo, estatus, fecha_creacion)
        VALUES (
            '$folio',
            '" . mysqli_real_escape_string($con, $datos['numero_e']) . "',
            '" . mysqli_real_escape_string($con, $datos['codigo_tarjeta']) . "',
            '" . mysqli_real_escape_string($con, $datos['tipo_solicitud']) . "',
            '" . mysqli_real_escape_string($con, $datos['commodity']) . "',
            '" . mysqli_real_escape_string($con, $datos['maquina'] ?? '') . "',
            " . intval($datos['cantidad'] ?? 1) . ",
            '" . mysqli_real_escape_string($con, $datos['motivo'] ?? '') . "',
            'pendiente',
            '$fechaActual'
        )";
    
    if (mysqli_query($con, $sql)) {
        echo json_encode([
            'success' => true,
            'message' => 'Solicitud creada correctamente',
            'folio' => $folio
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
    }
}

/**
 * Editar solicitud
 */
function editarSolicitud($con, $id, $datos) {
    $folio = $datos['folio'] ?? null;
    $id = intval($id);
    
    // Soporta edición por ID o por folio
    $where = "";
    if ($id > 0) {
        $where = "id = $id";
    } elseif ($folio) {
        $folio = mysqli_real_escape_string($con, $folio);
        $where = "folio = '$folio'";
    }
    
    if (!$where) {
        echo json_encode(['success' => false, 'error' => 'ID o Folio requerido']);
        return;
    }
    
    // Verificar que existe
    $sql = "SELECT id FROM solicitudes WHERE $where LIMIT 1";
    $result = mysqli_query($con, $sql);
    if (!mysqli_fetch_assoc($result)) {
        echo json_encode(['success' => false, 'error' => 'Solicitud no encontrada']);
        return;
    }
    
    // Construir actualización dinámica
    $campos = [];
    $permiteEditar = ['numero_e', 'codigo_tarjeta', 'tipo_solicitud', 'commodity', 'maquina', 'cantidad', 'motivo', 'estatus'];
    
    foreach ($permiteEditar as $campo) {
        if (isset($datos[$campo])) {
            $valor = mysqli_real_escape_string($con, $datos[$campo]);
            $campos[] = "$campo = '$valor'";
        }
    }
    
    if (empty($campos)) {
        echo json_encode(['success' => false, 'error' => 'No hay campos para actualizar']);
        return;
    }
    
    $sql = "UPDATE solicitudes SET " . implode(', ', $campos) . " WHERE $where";
    
    if (mysqli_query($con, $sql)) {
        echo json_encode([
            'success' => true,
            'message' => 'Solicitud actualizada correctamente'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
    }
}

/**
 * Eliminar solicitud
 */
function eliminarSolicitud($con, $id, $folio) {
    $id = intval($id);
    $folio = mysqli_real_escape_string($con, $folio ?? '');
    
    if ($id > 0) {
        $sql = "DELETE FROM solicitudes WHERE id = $id";
    } elseif ($folio) {
        $sql = "DELETE FROM solicitudes WHERE folio = '$folio'";
    } else {
        echo json_encode(['success' => false, 'error' => 'ID o Folio requerido']);
        return;
    }
    
    if (mysqli_query($con, $sql)) {
        if (mysqli_affected_rows($con) > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Solicitud eliminada correctamente'
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Solicitud no encontrada']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
    }
}

/**
 * Cambiar estatus de solicitud
 */
function cambiarEstatus($con, $id, $folio, $estatus) {
    $id = intval($id);
    $folio = mysqli_real_escape_string($con, $folio ?? '');
    
    if ($id <= 0 && !$folio) {
        echo json_encode(['success' => false, 'error' => 'ID o Folio requerido']);
        return;
    }
    
    $where = $id > 0 ? "id = $id" : "folio = '$folio'";
    
    // Si va a listo, agregar fecha_completado
    global $fechaActual;
    $camposAdicionales = '';
    if ($estatus === 'listo') {
        $camposAdicionales = ", fecha_completado = '$fechaActual'";
    } elseif ($estatus === 'pendiente') {
        $camposAdicionales = ", fecha_completado = NULL";
    }
    
    $sql = "UPDATE solicitudes SET estatus = '$estatus' $camposAdicionales WHERE $where";
    
    if (mysqli_query($con, $sql)) {
        if (mysqli_affected_rows($con) > 0) {
            $mensajes = [
                'pendiente' => 'Solicitud regresada a pendiente',
                'proceso' => 'Solicitud marcada en proceso',
                'listo' => 'Solicitud completada'
            ];
            echo json_encode([
                'success' => true,
                'message' => $mensajes[$estatus] ?? 'Estatus actualizado'
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Solicitud no encontrada o ya estaba en ese estatus']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
    }
}

/**
 * Formatear solicitud para respuesta
 */
function formatearSolicitud($row) {
    return [
        'id' => intval($row['id']),
        'folio' => $row['folio'],
        'numero_e' => $row['numero_e'],
        'nombre_solicitante' => $row['nombre_solicitante'] ?? $row['numero_e'],
        'codigo_tarjeta' => $row['codigo_tarjeta'],
        'tipo_solicitud' => $row['tipo_solicitud'],
        'commodity' => $row['commodity'],
        'maquina' => $row['maquina'],
        'cantidad' => intval($row['cantidad']),
        'motivo' => $row['motivo'],
        'estatus' => $row['estatus'],
        'fecha_creacion' => $row['fecha_creacion'],
        'fecha_completado' => $row['fecha_completado']
    ];
}
