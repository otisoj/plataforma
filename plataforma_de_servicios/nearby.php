<?php
require_once __DIR__ . '/auth.php';

// Verificar autenticación y rol
requireAuth();
requireRole('cliente');

// No necesitas requireGuest() aquí porque ya estás requiriendo autenticación
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cliente</title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="main.css" />
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="dashboard-container">
        <div class="sidebar">
            <ul>
                <li><a href="/panel_cliente.php" class="active">Inicio</a></li>
                <li><a href="/cliente/solicitudes.php">Mis Solicitudes</a></li>
                <li><a href="/cliente/pagos.php">Pagos</a></li>
                <li><a href="/cliente/reseñas.php">Reseñas</a></li>
                <li><a href="/cliente/perfil.php">Mi Perfil</a></li>
                <li><a href="/controllers/auth/login.php">Cerrar Sesión</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h2>Bienvenido, <?php echo $_SESSION['user_name']; ?></h2>

            <div class="search-services">
                <h3>Buscar Servicios</h3>
                <form id="searchForm">
                    <select id="serviceType" name="serviceType">
                        <option value="">Todos los servicios</option>
                        <option value="plomeria">Plomería</option>
                        <option value="electricidad">Electricidad</option>
                        <option value="limpieza">Limpieza</option>
                        <option value="jardineria">Jardinería</option>
                        <!-- Más opciones -->
                    </select>
                    <button type="submit" class="btn-primary">Buscar</button>
                </form>
            </div>

            <div class="map-container">
                <h3>Proveedores Cercanos</h3>
                <div id="map" style="height: 400px; width: 100%;"></div>
            </div>
            <script src="script.js"></script>
            <script
                src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBDaeWicvigtP9xPv919E-RNoxfvC-Hqik&callback=iniciarMap"
                async defer></script>


            <div class="service-request">
                <h3>Solicitar Servicio</h3>
                <form id="serviceRequestForm">
                    <div class="form-group">
                        <label for="serviceTypeRequest">Tipo de Servicio</label>
                        <select id="serviceTypeRequest" name="serviceTypeRequest" required>
                            <option value="">Seleccione un servicio</option>
                            <option value="plomeria">Plomería</option>
                            <option value="electricidad">Electricidad</option>
                            <option value="limpieza">Limpieza</option>
                            <option value="jardineria">Jardinería</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="serviceDescription">Descripción del Trabajo</label>
                        <textarea id="serviceDescription" name="serviceDescription" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="serviceAddress">Dirección del Servicio</label>
                        <input type="text" id="serviceAddress" name="serviceAddress" required>
                    </div>
                    <div class="form-group">
                        <label for="serviceDate">Fecha Requerida</label>
                        <input type="datetime-local" id="serviceDate" name="serviceDate" required>
                    </div>
                    <button type="submit" class="btn-primary">Enviar Solicitud</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="/assets/js/client.js"></script>
    <?php include 'footer.php'; ?>
</body>

</html> 