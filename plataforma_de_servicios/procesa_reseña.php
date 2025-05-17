<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
require_once '../../models/Review.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['solicitud_id'])) {
    $db = new Database();
    $conn = $db->getConnection();
    $review = new Review($conn);
    
    $solicitudId = $_POST['solicitud_id'];
    $calificacion = $_POST['calificacion'];
    $comentario = isset($_POST['comentario']) ? $_POST['comentario'] : null;
    
    // Verificar que la solicitud existe, está completada y pertenece al usuario
    $stmt = $conn->prepare("
        SELECT s.id_solicitud, s.id_cliente, s.id_servicio, 
               sv.id_proveedor
        FROM solicitudes s
        JOIN servicios sv ON s.id_servicio = sv.id_servicio
        WHERE s.id_solicitud = ? AND s.id_cliente = ? AND s.estado = 'completada'
        AND NOT EXISTS (
            SELECT 1 FROM reseñas r WHERE r.id_solicitud = s.id_solicitud
        )
    ");
    $stmt->bind_param("ii", $solicitudId, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "No puedes calificar este servicio";
        header("Location: /client/solicitudes.php");
        exit();
    }
    
    $solicitud = $result->fetch_assoc();
    
    // Crear la reseña
    $success = $review->createReview(
        $solicitud['id_cliente'],
        $solicitud['id_proveedor'],
        $solicitudId,
        $calificacion,
        $comentario
    );
    
    if ($success) {
        $_SESSION['success'] = "¡Gracias por tu reseña!";
    } else {
        $_SESSION['error'] = "Error al enviar la reseña. Por favor intenta nuevamente.";
    }
    
    header("Location: /client/solicitudes.php");
    exit();
} else {
    header("Location: /client/solicitudes.php");
    exit();
}
?>