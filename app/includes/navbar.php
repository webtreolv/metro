<?php
// Usar timezone de México
date_default_timezone_set('America/Mexico_City');

// Iniciar sesión para obtener el rol
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
$rol = $_SESSION['rol'] ?? 'invitado';
$nombre = $_SESSION['nombre'] ?? 'Invitado';
$pagina = $pagina ?? 'reportes';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MMQRO - <?= ucfirst($pagina) ?></title>
    
    <link href="../vendor/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/css/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../estilos/styles.css?v=2" rel="stylesheet">
    
    <style>
        body {
            background-color: var(--pc-surface);
            padding-top: 60px;
        }

        .navbar-custom {
            background: #ff0000;
            border-bottom: 1px solid #cc0000;
        }

        .navbar-custom .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 8px 16px;
            font-weight: 500;
        }

        .navbar-custom .nav-link:hover, .navbar-custom .nav-link.active {
            color: #ffffff;
            background: rgba(255,255,255,0.2);
            border-radius: var(--radius-sm);
        }

        .navbar-brand {
            color: #ffffff !important;
            font-weight: 700;
        }
        
        .navbar-custom .btn-outline-light {
            color: #ffffff;
            border-color: #ffffff;
        }
        .navbar-custom .btn-outline-light:hover {
            color: #ff0000;
            background: #ffffff;
        }
        .navbar-custom .text-white {
            color: #ffffff !important;
            font-family: 'Geist Mono', monospace;
        }
    </style>
</head>
<body>

<!-- Navbar superior -->
<nav class="navbar navbar-expand-lg navbar-custom fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <i class="bi bi-rulers"></i> MMQRO
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav me-auto">
                <?php if ($rol === 'laboratorio'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $pagina === 'solicitudes' ? 'active' : '' ?>" href="../admin/solicitudes.php">
                        <i class="bi bi-file-earmark-text"></i> Solicitudes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $pagina === 'laboratorio' ? 'active' : '' ?>" href="../laboratorio/tablero.php">
                        <i class="bi bi-kanban"></i> Laboratorio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../admin/pantalla_fuera_index.html" target="_blank">
                        <i class="bi bi-display"></i> Pantalla Fuera
                    </a>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link <?= $pagina === 'solicitudes' ? 'active' : '' ?>" href="../admin/solicitudes.php">
                        <i class="bi bi-file-earmark-text"></i> Solicitudes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $pagina === 'reportes' ? 'active' : '' ?>" href="../admin/reportes.php">
                        <i class="bi bi-graph-up"></i> Reportes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $pagina === 'pantalla_fuera' ? 'active' : '' ?>" href="../admin/pantalla_fuera_index.html" target="_blank">
                        <i class="bi bi-display"></i> Pantalla Fuera
                    </a>
                </li>
                <?php if ($rol === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $pagina === 'laboratorio' ? 'active' : '' ?>" href="../laboratorio/tablero.php">
                        <i class="bi bi-kanban"></i> Laboratorio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $pagina === 'usuarios' ? 'active' : '' ?>" href="../admin/usuarios.php">
                        <i class="bi bi-people"></i> Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $pagina === 'catalogos' ? 'active' : '' ?>" href="../admin/catalogos.php">
                        <i class="bi bi-gear"></i> Catalogos
                    </a>
                </li>
                <?php endif; ?>
                <?php endif; ?>
            </ul>
            <span class="text-white me-3"><?= $nombre ?></span>
            <a href="../modulos/login/logout.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right"></i> Cerrar Sesion
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid px-4" style="margin-top: 20px;">
    <div class="row">
        <div class="col-12">
            
            <?php if(isset($contenido_extra)) echo $contenido_extra; ?>
