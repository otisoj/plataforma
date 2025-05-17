<?php
require_once '../config/auth.php';

// Para páginas que requieren cualquier usuario autenticado
requireAuth();

// Para páginas que requieren un rol específico
requireRole('proveedor'); // o 'admin' o 'cliente'

// Para páginas de login/register que no deben ser accedidas por usuarios autenticados
requireGuest();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Administrador</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
</head>
<body>
    <?php include '../../views/partials/header.php'; ?>
    
    <div class="dashboard-container">
        <div class="sidebar">
            <ul>
                <li><a href="/admin/dashboard.php" class="active">Inicio</a></li>
                <li><a href="/admin/usuarios.php">Gestión de Usuarios</a></li>
                <li><a href="/admin/servicios.php">Gestión de Servicios</a></li>
                <li><a href="/admin/solicitudes.php">Gestión de Solicitudes</a></li>
                <li><a href="/admin/reseñas.php">Gestión de Reseñas</a></li>
                <li><a href="/admin/pagos.php">Gestión de Pagos</a></li>
                <li><a href="/admin/reportes.php">Reportes</a></li>
                <li><a href="/controllers/auth/logout.php">Cerrar Sesión</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <h2>Panel de Administración</h2>
            
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Usuarios Registrados</h3>
                    <p id="totalUsers">0</p>
                </div>
                <div class="stat-card">
                    <h3>Servicios Activos</h3>
                    <p id="totalServices">0</p>
                </div>
                <div class="stat-card">
                    <h3>Solicitudes Pendientes</h3>
                    <p id="pendingRequests">0</p>
                </div>
                <div class="stat-card">
                    <h3>Ingresos Totales</h3>
                    <p id="totalEarnings">$0</p>
                </div>
            </div>
            
            <div class="recent-activity">
                <h3>Actividad Reciente</h3>
                <table id="activityTable" class="display">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Usuario</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Datos cargados via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="/assets/js/admin.js"></script>
    <?php include '../../views/partials/footer.php'; ?>
</body>
</html>