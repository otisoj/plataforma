<?php
// Configuración de la base de datos
$host = 'localhost';
$dbname = 'servicios_de_plataforma';
$user = 'root';
$pass = '';

try {
    // Crear conexión PDO con configuración mejorada
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4", 
        $user, 
        $pass, 
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '-05:00'"
        ]
    );

    /**
     * Obtiene la conexión a la base de datos
     */
    function getDBConnection() {
        global $pdo;
        return $pdo;
    }

    /**
     * Crea un nuevo servicio con ubicación geográfica
     */
    function createService($providerId, $serviceData) {
        global $pdo;
        
        $sql = "INSERT INTO servicios (
                    id_proveedor, tipo_servicio, descripcion, 
                    tarifa, disponibilidad, ubicacion,
                    latitud, longitud
                ) VALUES (
                    :proveedor_id, :tipo_servicio, :descripcion, 
                    :tarifa, :disponibilidad, 
                    ST_GeomFromText('POINT(:longitud :latitud)'),
                    :latitud, :longitud
                )";
        
        $stmt = $pdo->prepare($sql);
        
        $params = [
            ':proveedor_id' => $providerId,
            ':tipo_servicio' => $serviceData['tipo_servicio'],
            ':descripcion' => $serviceData['descripcion'],
            ':tarifa' => (float)$serviceData['tarifa'],
            ':disponibilidad' => $serviceData['disponibilidad'],
            ':latitud' => (float)$serviceData['latitud'],
            ':longitud' => (float)$serviceData['longitud']
        ];
        
        $stmt->execute($params);
        return $pdo->lastInsertId();
    }

    /**
     * Obtiene servicios cercanos a una ubicación
     */
    function getNearbyServices($lat, $lng, $radiusKm, $serviceType = null) {
        global $pdo;
        
        $sql = "SELECT 
                    s.*, 
                    u.nom_usuario as proveedor_nombre,
                    u.foto_perfil as proveedor_foto,
                    ST_X(s.ubicacion) AS lng,
                    ST_Y(s.ubicacion) AS lat,
                    ST_Distance_Sphere(s.ubicacion, ST_GeomFromText('POINT(:lng :lat)')) / 1000 AS distancia_km
                FROM servicios s
                JOIN usuarios u ON s.id_proveedor = u.id_usuario
                WHERE ST_Distance_Sphere(s.ubicacion, ST_GeomFromText('POINT(:lng :lat)')) / 1000 <= :radius";
        
        $params = [
            ':lat' => $lat,
            ':lng' => $lng,
            ':radius' => $radiusKm
        ];
        
        if ($serviceType) {
            $sql .= " AND s.tipo_servicio = :tipo";
            $params[':tipo'] = $serviceType;
        }
        
        $sql .= " ORDER BY distancia_km ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

} catch (PDOException $e) {
    error_log("Error de conexión a BD: " . $e->getMessage());
    die("Error al conectar con la base de datos. Por favor intente más tarde.");
}