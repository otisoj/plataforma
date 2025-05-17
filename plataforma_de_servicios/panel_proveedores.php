<?php
require_once __DIR__ . '/auth.php';
requireAuth();
requireRole('proveedor');

require_once __DIR__ . '/includes/database.php';

// Procesar creación de servicio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_servicio'])) {
    try {
        $pdo = getDBConnection();
        $pdo->beginTransaction();
        
        $requiredFields = ['tipo_servicio', 'descripcion', 'tarifa', 'disponibilidad', 'latitud', 'longitud'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("El campo $field es requerido");
            }
        }

        // Validar coordenadas
        if (!is_numeric($_POST['latitud']) || !is_numeric($_POST['longitud'])) {
            throw new Exception("Las coordenadas deben ser valores numéricos");
        }

        // Insertar servicio (CORRECCIÓN APLICADA AQUÍ)
        $stmt = $pdo->prepare("
            INSERT INTO servicios (
                id_proveedor, tipo_servicio, descripcion, tarifa, disponibilidad,
                ubicacion, latitud, longitud
            ) VALUES (
                :proveedor_id, :tipo_servicio, :descripcion, :tarifa, :disponibilidad,
                ST_GeomFromText(CONCAT('POINT(', :longitud_param, ' ', :latitud_param, ')')),
                :latitud, :longitud
            )
        ");
        
        $stmt->execute([
            ':proveedor_id' => $_SESSION['user_id'],
            ':tipo_servicio' => $_POST['tipo_servicio'],
            ':descripcion' => $_POST['descripcion'],
            ':tarifa' => (float)$_POST['tarifa'],
            ':disponibilidad' => $_POST['disponibilidad'],
            ':latitud_param' => (float)$_POST['latitud'],
            ':longitud_param' => (float)$_POST['longitud'],
            ':latitud' => (float)$_POST['latitud'],
            ':longitud' => (float)$_POST['longitud']
        ]);

        // Crear notificación
        $notif = $pdo->prepare("
            INSERT INTO notificaciones (id_usuario, mensaje, tipo) 
            VALUES (?, ?, ?)
        ");
        $notif->execute([
            $_SESSION['user_id'],
            "Nuevo servicio creado: " . $_POST['tipo_servicio'],
            "servicio_creado"
        ]);

        $pdo->commit();
        $_SESSION['success_message'] = "Servicio creado exitosamente";
        header("Location: panel_proveedores.php");
        exit;
        
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error en base de datos: " . $e->getMessage());
        $_SESSION['error_message'] = "Error técnico al crear el servicio. Por favor intenta nuevamente.";
        header("Location: panel_proveedores.php");
        exit;
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: panel_proveedores.php");
        exit;
    }
}

// Obtener estadísticas del proveedor
$stats = getDBConnection()->prepare("
    SELECT 
        (SELECT COUNT(*) FROM solicitudes WHERE id_servicio IN 
            (SELECT id_servicio FROM servicios WHERE id_proveedor = ?) AND estado = 'pendiente') AS solicitudes_pendientes,
        (SELECT COUNT(*) FROM servicios WHERE id_proveedor = ?) AS servicios_activos,
        (SELECT AVG(calificacion) FROM reseñas WHERE id_proveedor = ?) AS calificacion_promedio
");
$stats->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$estadisticas = $stats->fetch();

// Obtener notificaciones
$notificaciones = getDBConnection()->prepare("
    SELECT * FROM notificaciones 
    WHERE id_usuario = ? 
    ORDER BY fecha_creacion DESC 
    LIMIT 5
");
$notificaciones->execute([$_SESSION['user_id']]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Proveedor</title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        #map { 
            height: 300px; 
            width: 100%;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .location-controls {
            display: flex;
            gap: 10px;
            margin: 10px 0;
        }
        .location-controls button {
            padding: 5px 10px;
            background: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
        }
        .map-container {
            margin: 15px 0;
        }
        .alert {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .alert.success {
            background: #d4edda;
            color: #155724;
        }
        .alert.error {
            background: #f8d7da;
            color: #721c24;
        }
        .notification {
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .notification.unread {
            background: #f0f7ff;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="dashboard-container">
        <div class="sidebar">
            <ul>
                <li><a href="panel_proveedores.php" class="active">Inicio</a></li>
                <li><a href="provider/servicios.php">Mis Servicios</a></li>
                <li><a href="/provider/solicitudes.php">Solicitudes</a></li>
                <li><a href="/provider/pagos.php">Pagos</a></li>
                <li><a href="reseña_cliente.php">Reseñas</a></li>
                <li><a href="/provider/perfil.php">Mi Perfil</a></li>
                <li><a href="login.php">Cerrar Sesión</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert success"><?= $_SESSION['success_message'] ?></div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert error"><?= $_SESSION['error_message'] ?></div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Solicitudes Pendientes</h3>
                    <p><?= $estadisticas['solicitudes_pendientes'] ?></p>
                </div>
                <div class="stat-card">
                    <h3>Servicios Activos</h3>
                    <p><?= $estadisticas['servicios_activos'] ?></p>
                </div>
                <div class="stat-card">
                    <h3>Calificación Promedio</h3>
                    <p><?= number_format($estadisticas['calificacion_promedio'] ?? 0, 1) ?></p>
                </div>
            </div>
            
            <div class="notifications">
                <h3>Notificaciones Recientes</h3>
                <div id="notificationsList">
                    <?php while ($notif = $notificaciones->fetch()): ?>
                        <div class="notification <?= $notif['leida'] ? '' : 'unread' ?>">
                            <p><?= htmlspecialchars($notif['mensaje']) ?></p>
                            <small><?= date('d/m/Y H:i', strtotime($notif['fecha_creacion'])) ?></small>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <div class="add-service">
                <h3>Agregar Nuevo Servicio</h3>
                <form method="POST" id="serviceForm">
                    <input type="hidden" name="crear_servicio" value="1">
                    
                    <div class="form-group">
                        <label for="serviceType">Tipo de Servicio</label>
                        <select id="serviceType" name="tipo_servicio" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="plomeria">Plomería</option>
                            <option value="electricidad">Electricidad</option>
                            <option value="limpieza">Limpieza</option>
                            <option value="jardineria">Jardinería</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="serviceDescription">Descripción</label>
                        <textarea id="serviceDescription" name="descripcion" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="serviceRate">Tarifa (COP)</label>
                        <input type="number" id="serviceRate" name="tarifa" min="0" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="serviceAvailability">Disponibilidad</label>
                        <input type="text" id="serviceAvailability" name="disponibilidad" placeholder="Ej: Lunes a Viernes, 8am-6pm" required>
                    </div>
                    
                    <div class="form-group map-container">
                        <label>Ubicación del Servicio</label>
                        <div class="location-controls">
                            <button type="button" id="useCurrentLocation">Usar mi ubicación actual</button>
                            <button type="button" id="clearLocation">Limpiar ubicación</button>
                        </div>
                        <div id="map"></div>
                        <input type="hidden" id="lat" name="latitud" required>
                        <input type="hidden" id="lng" name="longitud" required>
                        <input type="text" id="serviceLocation" name="ubicacion_texto" placeholder="Dirección descriptiva" required>
                    </div>
                    
                    <button type="submit" class="btn-primary">Publicar Servicio</button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        // Configurar mapa
        const map = L.map('map').setView([4.6097, -74.0817], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        
        let marker;
        const latInput = document.getElementById('lat');
        const lngInput = document.getElementById('lng');
        const addressInput = document.getElementById('serviceLocation');
        const form = document.getElementById('serviceForm');
        
        // Manejador de clics en el mapa
        map.on('click', function(e) {
            const { lat, lng } = e.latlng;
            updateLocation(lat, lng);
        });
        
        // Botón para usar ubicación actual
        document.getElementById('useCurrentLocation').addEventListener('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    updateLocation(lat, lng);
                    map.setView([lat, lng], 15);
                }, function(error) {
                    alert('Error al obtener la ubicación: ' + error.message);
                });
            } else {
                alert('Tu navegador no soporta geolocalización');
            }
        });
        
        // Botón para limpiar ubicación
        document.getElementById('clearLocation').addEventListener('click', function() {
            if (marker) map.removeLayer(marker);
            marker = null;
            latInput.value = '';
            lngInput.value = '';
            addressInput.value = '';
        });
        
        // Función para actualizar la ubicación
        function updateLocation(lat, lng) {
            latInput.value = lat;
            lngInput.value = lng;
            
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng]).addTo(map);
            }
            
            // Geocodificación inversa para obtener dirección
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    addressInput.value = data.display_name || `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                })
                .catch(() => {
                    addressInput.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                });
        }
        
        // Validación del formulario
        form.addEventListener('submit', function(e) {
            if (!latInput.value || !lngInput.value) {
                e.preventDefault();
                alert('Por favor selecciona una ubicación en el mapa');
                map.scrollIntoView({ behavior: 'smooth' });
            }
        });
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>