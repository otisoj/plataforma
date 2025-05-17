<?php
// Configuración básica
session_start();
date_default_timezone_set('America/Mexico_City');

// Mostrar errores (solo en desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Constantes de configuración
define('DB_HOST', 'localhost');
define('DB_NAME', 'servicios_de_plataforma');
define('DB_USER', 'root');
define('DB_PASS', '');