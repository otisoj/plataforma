<?php
require_once __DIR__ . '/auth.php';
requireAuth();
requireRole('cliente');

require_once __DIR__ . '/includes/database.php';

// 1. Procesar filtros de búsqueda
$tipo = $_GET['tipo'] ?? '';
$params = [];
$where = "WHERE s.latitud IS NOT NULL AND s.longitud IS NOT NULL AND s.latitud != 0 AND s.longitud != 0";

if ($tipo) {
    $where .= " AND s.tipo_servicio = :tipo";
    $params[':tipo'] = $tipo;
}

// 2. Obtener servicios disponibles
$query = "
    SELECT 
        s.id_servicio, 
        s.tipo_servicio, 
        s.descripcion, 
        s.tarifa, 
        s.disponibilidad,
        s.latitud,
        s.longitud,
        u.id_usuario AS proveedor_id, 
        u.nom_usuario AS proveedor_nombre, 
        u.foto_perfil
    FROM servicios s
    JOIN usuarios u ON s.id_proveedor = u.id_usuario
    $where
    ORDER BY s.id_servicio DESC
";

$servicios = getDBConnection()->prepare($query);
$servicios->execute($params);
$servicios = $servicios->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios Disponibles - Cliente</title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        /* Estructura principal */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            background-color: #f9f9f9;
        }
        
        /* Estilos del mapa */
        #map {
            height: 400px;
            width: 100%;
            margin: 20px 0;
            border-radius: 8px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* Tarjetas de servicios */
        .services-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .service-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .service-card h3 {
            color: #2c3e50;
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .service-info {
            margin: 10px 0;
        }
        
        .service-info p {
            margin: 5px 0;
            color: #555;
        }
        
        .service-price {
            font-weight: bold;
            color: #27ae60;
            font-size: 1.2em;
        }
        
        .btn-request {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        
        .btn-request:hover {
            background-color: #2980b9;
        }
        
        /* Filtros */
        .filter-section {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .filter-group {
            margin-bottom: 15px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #34495e;
        }
        
        .filter-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .btn-filter {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        
        .btn-clear {
            background-color: #95a5a6;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        /* Mensaje cuando no hay servicios */
        .no-services {
            text-align: center;
            padding: 30px;
            color: #7f8c8d;
            grid-column: 1 / -1;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="dashboard-container">
        <div class="sidebar">
            <ul>
                <li><a href="panel_cliente.php" class="active">Inicio</a></li>
                <li><a href="pago_cliente.php">Pagos</a></li>
                <li><a href="reseña_cliente.php">Reseñas</a></li>
                <li><a href="perfil.php">Mi Perfil</a></li>
                <li><a href="login.php">Cerrar Sesión</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1>Servicios Disponibles</h1>
            <p class="welcome-message">Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>

            <!-- Sección de filtros -->
            <div class="filter-section">
                <form method="GET" id="searchForm">
                    <div class="filter-group">
                        <label for="serviceType">Tipo de servicio:</label>
                        <select name="tipo" id="serviceType">
                            <option value="">Todos los servicios</option>
                            <option value="plomeria" <?= $tipo == 'plomeria' ? 'selected' : '' ?>>Plomería</option>
                            <option value="electricidad" <?= $tipo == 'electricidad' ? 'selected' : '' ?>>Electricidad</option>
                            <option value="limpieza" <?= $tipo == 'limpieza' ? 'selected' : '' ?>>Limpieza</option>
                            <option value="jardineria" <?= $tipo == 'jardineria' ? 'selected' : '' ?>>Jardinería</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-filter">Filtrar</button>
                    <a href="/panel_cliente.php" class="btn-clear">Limpiar</a>
                </form>
            </div>

            <!-- Mapa interactivo -->
            <div id="map"></div>

            <!-- Listado de servicios -->
            <div class="services-container">
                <?php if(empty($servicios)): ?>
                    <div class="no-services">
                        <h3>No se encontraron servicios</h3>
                        <p>No hay servicios disponibles con los filtros seleccionados.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($servicios as $servicio): ?>
                        <div class="service-card">
                            <h3><?php echo htmlspecialchars(ucfirst($servicio['tipo_servicio'])); ?></h3>
                            <div class="service-info">
                                <p><?php echo htmlspecialchars($servicio['descripcion']); ?></p>
                                <p><strong>Proveedor:</strong> <?php echo htmlspecialchars($servicio['proveedor_nombre']); ?></p>
                                <p class="service-price">$<?php echo number_format($servicio['tarifa'], 2); ?> COP</p>
                            </div>
                            <a href="solicitar_servicio.php?id=<?= $servicio['id_servicio'] ?>" class="btn-request">Solicitar Servicio</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar mapa con vista por defecto (Bogotá)
            const map = L.map('map').setView([4.6097, -74.0817], 12);
            
            // Añadir capa base de OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            // Icono personalizado para el usuario (azul)
            const userIcon = L.divIcon({
                className: 'user-icon',
                html: '<svg viewBox="0 0 24 24" width="24" height="24" fill="#3498db"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 2a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"/></svg>',
                iconSize: [24, 24],
                iconAnchor: [12, 24]
            });
            
            // Icono personalizado para servicios (rojo)
            const serviceIcon = L.divIcon({
                className: 'service-icon',
                html: '<svg viewBox="0 0 24 24" width="20" height="20" fill="#e74c3c"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>',
                iconSize: [20, 20],
                iconAnchor: [10, 20]
            });
            
            // Intentar obtener ubicación del usuario
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        // Añadir marcador para el usuario
                        L.marker([position.coords.latitude, position.coords.longitude], {
                            icon: userIcon
                        }).addTo(map).bindPopup('Tu ubicación actual');
                        
                        // Centrar mapa en la ubicación del usuario
                        map.setView([position.coords.latitude, position.coords.longitude], 14);
                    },
                    function(error) {
                        console.log("Error obteniendo ubicación:", error);
                    }
                );
            }
            
            // Añadir marcadores para los servicios
            <?php foreach ($servicios as $servicio): ?>
                L.marker([<?= $servicio['latitud'] ?>, <?= $servicio['longitud'] ?>], {
                    icon: serviceIcon
                }).addTo(map).bindPopup(`
                    <b><?= htmlspecialchars($servicio['tipo_servicio']) ?></b><br>
                    <i><?= htmlspecialchars($servicio['proveedor_nombre']) ?></i><br>
                    $<?= number_format($servicio['tarifa'], 2) ?> COP
                `);
            <?php endforeach; ?>
            
            // Asegurar que el mapa se redibuje correctamente
            setTimeout(() => {
                map.invalidateSize();
            }, 100);
        });
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>