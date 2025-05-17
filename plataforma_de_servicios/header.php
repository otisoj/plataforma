<?php
// Inicia sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Plataforma de Servicios'; ?></title>
    <link rel="stylesheet" href="/plataforma_de_servicios/assets/css/main.css">
</head>
<body>
    <header class="header">
        <div class="header-container">
            <!-- Logo -->
            <a href="/plataforma_de_servicios/" class="logo">HomeServices</a>
            
            <!-- Menú principal -->
            <nav class="nav-menu">
                <ul>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Usuario logueado -->
                        <li>
                            <a href="/plataforma_de_servicios/<?php echo $_SESSION['user_type'] === 'admin' ? 'admin/dashboard.php' : ($_SESSION['user_type'] === 'proveedor' ? 'panel_proveedores.php' : 'panel_cliente.php'); ?>">
                                Mi Panel
                            </a>
                        </li>
                        <li>
                            <a href="login.php">
                                Cerrar Sesión
                            </a>
                        </li>
                        <li class="user-greeting">
                            ¡Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
                        </li>
                    <?php else: ?>
                        <!-- Usuario invitado -->
                        <li><a href="login.php">Iniciar Sesión</a></li>
                        <li><a href="registro.php">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Contenedor principal (se cierra en footer.php) -->
    <div class="main-wrapper"></div>