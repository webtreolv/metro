<?php
/**
 * Configuración de Base de Datos
 * MMQRO - Sistema de Gestión de Solicitudes de Metrología
 * 
 * @author MMQRO
 * @version 1.0
 */

require_once __DIR__ . '/azure_db.php';
if (!defined('DB_PASS')) {
    define('DB_PASS', DB_PASSWORD);
}

/**
 * Conectar a la base de datos
 * @return mysqli|false
 */
function conectarDB() {
    try {
        $con = mysqli_init();
        
        $certificado = __DIR__ . "/../DigiCertGlobalRootG2.crt.pem";
        
        if (is_readable($certificado) && strpos(strtolower(DB_HOST), "mysql.database.azure.com") !== false) {
            mysqli_ssl_set($con, NULL, NULL, $certificado, NULL, NULL);
            mysqli_real_connect($con, DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, 3306, NULL, MYSQLI_CLIENT_SSL);
        } else {
            mysqli_real_connect($con, DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        }
        
        if (mysqli_connect_errno()) {
            error_log("Error de conexiA3n: " . mysqli_connect_error());
            return false;
        }
        
        mysqli_set_charset($con, DB_CHARSET);
        mysqli_query($con, "SET time_zone = '-05:00'");
        return $con;
    } catch (Exception $e) {
        error_log("ExcepciA3n de conexiA3n: " . $e->getMessage());
        return false;
    }
}

/**
 * Cerrar conexión a la base de datos
 * @param mysqli $con
 */
function cerrarDB($con) {
    if ($con) {
        mysqli_close($con);
    }
}

/**
 * Iniciar sesión segura
 */
function iniciarSesionSegura() {
    // Configurar cookies seguras
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_strict_mode', 1);
    
    // Nombre de sesión personalizado
    session_name('MMQRO_SESSION');
    
    // Iniciar sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Regenerar ID de sesión periódicamente
    if (!isset($_SESSION['inicio_sesion'])) {
        session_regenerate_id(true);
        $_SESSION['inicio_sesion'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
    }
    
    // Verificar IP (opcional - puede causar problemas en proxies)
    // if ($_SESSION['ip_address'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
    //     session_destroy();
    //     return false;
    // }
    
    return true;
}

/**
 * Verificar si el usuario está logueado
 * @return bool
 */
function estaLogueado() {
    return isset($_SESSION['usuario_id']) && 
           isset($_SESSION['numero_e']) && 
           isset($_SESSION['rol']);
}

/**
 * Obtener el rol del usuario actual
 * @return string|null
 */
function obtenerRol() {
    return $_SESSION['rol'] ?? null;
}

/**
 * Obtener datos del usuario actual
 * @return array|null
 */
function obtenerUsuarioActual() {
    if (!estaLogueado()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['usuario_id'] ?? null,
        'numero_e' => $_SESSION['numero_e'] ?? null,
        'nombre' => $_SESSION['nombre'] ?? null,
        'rol' => $_SESSION['rol'] ?? null
    ];
}

/**
 * Verificar acceso por rol
 * @param array $roles permitidos
 * @return bool
 */
function verificarAcceso($roles) {
    if (!estaLogueado()) {
        return false;
    }
    
    $rol = obtenerRol();
    return in_array($rol, $roles);
}

/**
 * Generar folio automático
 * @return string
 */
function generarFolio() {
    $con = conectarDB();
    if (!$con) {
        return 'M' . date('ym') . '-001';
    }
    
    $mes = date('ym');
    
    $sql = "SELECT MAX(CAST(SUBSTRING_INDEX(folio, '-', -1) AS UNSIGNED)) as max_val 
            FROM solicitudes 
            WHERE folio LIKE 'M{$mes}-%'";
    
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($result);
    
    $siguiente = ($row['max_val'] ?? 0) + 1;
    cerrarDB($con);
    
    return 'M' . $mes . '-' . str_pad($siguiente, 3, '0', STR_PAD_LEFT);
}

/**
 * Redireccionar según rol
 */
function redireccionarPorRol() {
    if (!estaLogueado()) {
        header('Location: index.php');
        exit;
    }
    
    $rol = obtenerRol();
    
    switch ($rol) {
        case 'admin':
            header('Location: modulos/admin/reportes.php');
            break;
        case 'laboratorio':
            header('Location: modulos/laboratorio/tablero.php');
            break;
        case 'usuario':
        default:
            header('Location: modulos/usuario/dashboard.php');
            break;
    }
    exit;
}

/**
 * Sanitizar entrada de usuario
 * @param string $input
 * @return string
 */
function sanitizar($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

/**
 * Validar número E
 * @param string $numeroE
 * @return bool
 */
function validarNumeroE($numeroE) {
    return preg_match('/^[A-Za-z0-9\-]+$/', $numeroE) && strlen($numeroE) <= 20;
}

/**
 * Validar código de tarjeta
 * @param string $codigo
 * @return bool
 */
function validarCodigoTarjeta($codigo) {
    return !empty($codigo) && strlen($codigo) <= 50;
}

/**
 * Mensaje de alerta
 * @param string $mensaje
 * @param string $tipo (success, danger, warning, info)
 * @return string
 */
function mostrarAlerta($mensaje, $tipo = 'info') {
    $colores = [
        'success' => 'alert-success',
        'danger' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ];
    
    $clase = $colores[$tipo] ?? 'alert-info';
    
    return "<div class='alert $clase alert-dismissible fade show' role='alert'>
            $mensaje
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

