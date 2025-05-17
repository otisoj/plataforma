<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $requestId = $_POST['request_id'];
    $amount = $_POST['amount'];
    $message = isset($_POST['message']) ? $_POST['message'] : null;
    $providerId = $_SESSION['user_id'];
    
    // Verificar que la solicitud existe y está pendiente
    $stmt = $conn->prepare("
        SELECT s.id_solicitud, sv.tipo_servicio
        FROM solicitudes s
        JOIN servicios sv ON s.id_servicio = sv.id_servicio
        WHERE s.id_solicitud = ? AND s.estado = 'pendiente'
        AND sv.tipo_servicio IN (
            SELECT tipo_servicio FROM servicios WHERE id_proveedor = ?
        )
    ");
    $stmt->bind_param("ii", $requestId, $providerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Solicitud no válida']);
        exit();
    }
    
    // Verificar que no haya ya una oferta del mismo proveedor
    $stmt = $conn->prepare("
        SELECT id_oferta FROM ofertas 
        WHERE id_solicitud = ? AND id_proveedor = ?
    ");
    $stmt->bind_param("ii", $requestId, $providerId);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Ya has enviado una oferta para esta solicitud']);
        exit();
    }
    
    // Insertar la oferta
    $stmt = $conn->prepare("
        INSERT INTO ofertas (id_solicitud, id_proveedor, monto_ofertado, mensaje)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iids", $requestId, $providerId, $amount, $message);
    
    if ($stmt->execute()) {
        // Crear notificación para el cliente
        $solicitud = $result->fetch_assoc();
        $notificationMessage = "Has recibido una nueva oferta para tu solicitud de {$solicitud['tipo_servicio']}";
        
        $stmt = $conn->prepare("
            INSERT INTO notificaciones (id_usuario, mensaje, tipo, url_destino)
            SELECT id_cliente, ?, 'nueva_oferta', '/client/solicitudes.php'
            FROM solicitudes WHERE id_solicitud = ?
        ");
        $stmt->bind_param("si", $notificationMessage, $requestId);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al enviar la oferta']);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>