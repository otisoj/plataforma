<?php
define('BASE_DIR', __DIR__); // Definir BASE_DIR como constante global
$baseDir = BASE_DIR; // Asignar también a una variable para usar en includes


// 2. Incluir archivos necesarios
require_once $baseDir . '/auth.php';
require_once $baseDir . '/database.php';

if (!class_exists('Database')) {
    die("Error: La clase Database no está disponible");
}

// Obtener historial de pagos del cliente
$query = "
    SELECT 
        p.id_pago,
        p.fecha_pago,
        p.monto,
        p.estado,
        p.metodo_pago,
        s.tipo_servicio,
        u.nom_usuario AS proveedor_nombre
    FROM pagos p
    JOIN servicios s ON p.id_servicio = s.id_servicio
    JOIN usuarios u ON s.id_proveedor = u.id_usuario
    WHERE p.id_cliente = :cliente_id
    ORDER BY p.fecha_pago DESC
";
$pagos = $conn->prepare($query);
$pagos->execute([':cliente_id' => $_SESSION['user_id']]);
$pagos = $pagos->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pagos - Cliente</title>
    <link rel="stylesheet" href="../main.css">
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
        
        /* Tabla de pagos */
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .payments-table th, 
        .payments-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .payments-table th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        
        .payments-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .payment-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .status-completed {
            background-color: #2ecc71;
            color: white;
        }
        
        .status-pending {
            background-color: #f39c12;
            color: white;
        }
        
        .status-failed {
            background-color: #e74c3c;
            color: white;
        }
        
        .payment-amount {
            font-weight: bold;
            color: #27ae60;
        }
        
        /* Mensaje cuando no hay pagos */
        .no-payments {
            text-align: center;
            padding: 30px;
            color: #7f8c8d;
            background: white;
            border-radius: 8px;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .payments-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <?php include '../header.php'; ?>

    <div class="dashboard-container">
        <div class="sidebar">
            <ul>
                <li><a href="/panel_cliente.php">Inicio</a></li>
                <li><a href="/cliente/pagos.php" class="active">Pagos</a></li>
                <li><a href="/cliente/reseñas.php">Reseñas</a></li>
                <li><a href="../perfil.php">Mi Perfil</a></li>
                <li><a href="../login.php">Cerrar Sesión</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1>Mis Pagos</h1>
            <p class="welcome-message">Historial de transacciones, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>

            <?php if(empty($pagos)): ?>
                <div class="no-payments">
                    <h3>No tienes pagos registrados</h3>
                    <p>Cuando realices solicitudes de servicio, aparecerán aquí tus transacciones.</p>
                </div>
            <?php else: ?>
                <table class="payments-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Servicio</th>
                            <th>Proveedor</th>
                            <th>Método</th>
                            <th>Monto</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pagos as $pago): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?></td>
                                <td><?= htmlspecialchars(ucfirst($pago['tipo_servicio'])) ?></td>
                                <td><?= htmlspecialchars($pago['proveedor_nombre']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($pago['metodo_pago'])) ?></td>
                                <td class="payment-amount">$<?= number_format($pago['monto'], 2) ?> COP</td>
                                <td>
                                    <?php 
                                        $statusClass = '';
                                        if ($pago['estado'] == 'completado') $statusClass = 'status-completed';
                                        elseif ($pago['estado'] == 'pendiente') $statusClass = 'status-pending';
                                        else $statusClass = 'status-failed';
                                    ?>
                                    <span class="payment-status <?= $statusClass ?>">
                                        <?= ucfirst($pago['estado']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../footer.php'; ?>
</body>
</html>