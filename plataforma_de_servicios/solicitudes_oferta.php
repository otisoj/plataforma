<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Obtener solicitudes relevantes para el proveedor
$stmt = $conn->prepare("
    SELECT s.id_solicitud, s.fecha_solicitud, s.descripcion, s.estado,
           s.fecha_requerida, s.direccion_servicio,
           u.nom_usuario as cliente_nombre, u.telefono as cliente_telefono,
           sv.tipo_servicio,
           (SELECT COUNT(*) FROM ofertas o WHERE o.id_solicitud = s.id_solicitud AND o.id_proveedor = ?) as ya_ofertado
    FROM solicitudes s
    JOIN servicios sv ON s.id_servicio = sv.id_servicio
    JOIN usuarios u ON s.id_cliente = u.id_usuario
    WHERE sv.tipo_servicio IN (
        SELECT tipo_servicio FROM servicios WHERE id_proveedor = ?
    )
    AND s.estado = 'pendiente'
    ORDER BY s.fecha_solicitud DESC
");
$stmt->bind_param("ii", $_SESSION['user_id'], $_SESSION['user_id']);
$stmt->execute();
$solicitudes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes - Proveedor</title>
    <link rel="stylesheet" href="/assets/css/provider.css">
</head>
<body>
    <?php include '../../views/partials/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../../views/provider/sidebar.php'; ?>
        
        <div class="main-content">
            <h2>Solicitudes de Servicios</h2>
            
            <div class="requests-list">
                <?php if (empty($solicitudes)): ?>
                    <p>No hay solicitudes disponibles en este momento.</p>
                <?php else: ?>
                    <?php foreach ($solicitudes as $solicitud): ?>
                        <div class="request-card">
                            <h3><?php echo htmlspecialchars($solicitud['tipo_servicio']); ?></h3>
                            <p><strong>Cliente:</strong> <?php echo htmlspecialchars($solicitud['cliente_nombre']); ?></p>
                            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($solicitud['cliente_telefono']); ?></p>
                            <p><strong>Dirección:</strong> <?php echo htmlspecialchars($solicitud['direccion_servicio']); ?></p>
                            <p><strong>Fecha requerida:</strong> <?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_requerida'])); ?></p>
                            <p><strong>Descripción:</strong> <?php echo htmlspecialchars($solicitud['descripcion']); ?></p>
                            
                            <?php if ($solicitud['ya_ofertado'] > 0): ?>
                                <p class="text-success">Ya has enviado una oferta para esta solicitud</p>
                            <?php else: ?>
                                <button class="btn-primary make-offer-btn" data-request="<?php echo $solicitud['id_solicitud']; ?>">
                                    Hacer Oferta
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal para hacer oferta -->
    <div id="offerModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h3>Enviar Oferta</h3>
            <form id="offerForm">
                <input type="hidden" id="offerRequestId" name="request_id">
                <div class="form-group">
                    <label for="offerAmount">Monto (COP)</label>
                    <input type="number" id="offerAmount" name="amount" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="offerMessage">Mensaje (Opcional)</label>
                    <textarea id="offerMessage" name="message" rows="4"></textarea>
                </div>
                <button type="submit" class="btn-primary">Enviar Oferta</button>
            </form>
        </div>
    </div>
    
    <script src="/assets/js/provider.js"></script>
    <?php include '../../views/partials/footer.php'; ?>
</body>
</html>