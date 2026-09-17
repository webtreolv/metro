<?php
/**
 * Logout - Cerrar Sesión
 */

session_start();
session_destroy();
header('Location: /metrologia/');
exit;
