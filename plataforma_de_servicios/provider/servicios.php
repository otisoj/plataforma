<?php
require_once __DIR__ . '/../auth.php';
requireAuth();
requireRole('proveedor');

require_once __DIR__ . '/../includes/database.php';

// Procesar eliminación de servicio si se recibe el parámetro
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['eliminar'])) {
    try {
        $pdo = getDBConnection();
        
        // Verificar que el servicio pertenece al proveedor actual
        $stmt = $pdo->prepare("SELECT id_proveedor FROM servicios WHERE id_servicio = :id_servicio");
        $stmt->execute([':id_servicio' => $_GET['id']]);
        $servicio = $stmt->fetch();
        
        if (!$servicio || $servicio['id_proveedor'] != $_SESSION['user_id']) {
            throw new Exception("No tienes permiso para eliminar este servicio");
        }
        
        // Eliminar el servicio
        $stmt = $pdo->prepare("DELETE FROM servicios WHERE id_servicio = :id_servicio");
        $stmt->execute([':id_servicio' => $_GET['id']]);
        
        $_SESSION['success_message'] = "Servicio eliminado correctamente";
        header("Location: servicios.php");
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: servicios.php");
        exit;
    }
}

// Obtener los servicios del proveedor actual
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT id_servicio, tipo_servicio, descripcion, tarifa, disponibilidad, 
               latitud, longitud
        FROM servicios 
        WHERE id_proveedor = :proveedor_id
        ORDER BY id_servicio DESC
    ");

    $stmt->execute([':proveedor_id' => $_SESSION['user_id']]);
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error al cargar los servicios: " . $e->getMessage();
    header("Location: panel_proveedores.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Servicios</title>
    <link rel="stylesheet" href="../main.css">
    <style>
        .service-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        .service-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .service-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .service-title {
            font-size: 1.5rem;
            color: #333;
            margin: 0;
        }

        .service-price {
            font-weight: bold;
            color: #2a9d8f;
            font-size: 1.2rem;
        }

        .service-meta {
            margin-bottom: 15px;
        }

        .service-description {
            color: #555;
            line-height: 1.6;
        }

        .service-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }

        .btn-edit {
            background: #3498db;
            color: white;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        .no-services {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        /* Estilos para mensajes */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
        .alert-error {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
    </style>
</head>

<body>
<?php include '../header.php'; ?>

    <div class="dashboard-container">
        <div class="sidebar">
            <ul>
                <li><a href="../panel_proveedores.php">Inicio</a></li>
                <li><a href="servicios.php" class="active">Mis Servicios</a></li>
                <li><a href="solicitudes.php">Solicitudes</a></li>
                <li><a href="ofertas.php">Ofertas</a></li>
                <li><a href="pagos.php">Pagos</a></li>
                <li><a href="reseñas.php">Reseñas</a></li>
                <li><a href="perfil.php">Mi Perfil</a></li>
                <li><a href="../login.php">Cerrar Sesión</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1>Mis Servicios</h1>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error"><?= $_SESSION['error_message'] ?></div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <?php if (empty($servicios)): ?>
                <div class="no-services">
                    <h3>No tienes servicios registrados</h3>
                    <p>Puedes crear tu primer servicio desde el panel principal.</p>
                    <a href="../panel_proveedores.php" class="btn btn-edit">Crear Servicio</a>
                </div>
            <?php else: ?>
                <div class="service-container">
                    <?php foreach ($servicios as $servicio): ?>
                        <div class="service-card">
                            <div class="service-header">
                                <h2 class="service-title"><?= htmlspecialchars(ucfirst($servicio['tipo_servicio'])) ?></h2>
                                <span class="service-price">$<?= number_format($servicio['tarifa'], 2) ?> COP</span>
                            </div>

                            <div class="service-meta">
                                <p><strong>Disponibilidad:</strong> <?= htmlspecialchars($servicio['disponibilidad']) ?></p>
                                <p><strong>Ubicación:</strong> Lat: <?= $servicio['latitud'] ?>, Long:
                                    <?= $servicio['longitud'] ?></p>
                            </div>

                            <div class="service-description">
                                <p><?= htmlspecialchars($servicio['descripcion']) ?></p>
                            </div>

                            <div class="service-actions">
                                <a href="../editar_servicio.php?id=<?= $servicio['id_servicio'] ?>" class="btn btn-edit">Editar</a>
                                <a href="servicios.php?eliminar=1&id=<?= $servicio['id_servicio'] ?>" class="btn btn-delete"
                                    onclick="return confirm('¿Estás seguro de eliminar este servicio?')">Eliminar</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../footer.php'; ?>
</body>
</html>