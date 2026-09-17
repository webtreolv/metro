<?php
/**
 * Header común
 * MMQRO - Sistema de Gestión de Solicitudes de Metrología
 */

// Usar ruta relativa
$prefix = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo ?? 'MMQRO - Metrología'; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="vendor/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="vendor/css/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="vendor/js/chart.umd.js"></script>
    
    <!-- Estilos personalizados -->
    <link href="estilos/styles.css" rel="stylesheet">
    
    <style>
        :root {
            --mmq-primary: #C0392B;
            --mmq-secondary: #E74C3C;
            --mmq-success: #27ae60;
            --mmq-warning: #f39c12;
            --mmq-danger: #C0392B;
            --mmq-info: #1abc9c;
            --mmq-dark: #922B21;
        }
        
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #e8e8e8;
            background-image: 
                radial-gradient(#bbb 0.5px, transparent 0.5px),
                radial-gradient(#ccc 0.5px, #e8e8e8 0.5px);
            background-size: 16px 16px;
            background-position: 0 0, 8px 8px;
        }
        
        .navbar-brand {
            font-weight: bold;
            color: var(--mmq-secondary) !important;
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-radius: 10px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #C0392B, #E74C3C);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            border: none;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .status-pending {
            color: var(--mmq-warning);
        }
        
        .status-ready {
            color: var(--mmq-success);
        }
    </style>
</head>
<body>
    <?php if (estaLogueado()): ?>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #C0392B, #922B21);">
        <div class="container">
            <a class="navbar-brand" href="modulos/usuario/dashboard.php">
                <i class="bi bi-rulers"></i> MMQRO
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php include dirname(__DIR__) . '/includes/navbar.php'; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo $_SESSION['nombre'] ?? 'Usuario'; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text text-muted"><?php echo $_SESSION['numero_e'] ?? ''; ?></span></li>
                            <li><span class="dropdown-item-text text-muted"><?php echo ucfirst($_SESSION['rol'] ?? ''); ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="modulos/login/logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <main class="flex-grow-1 <?php echo (estaLogueado()) ? 'py-4' : ''; ?>">
