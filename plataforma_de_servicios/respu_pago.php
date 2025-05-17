<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
require_once '../../models/Payment.php';

$db = new Database();
$conn = $db->getConnection();
$payment = new Payment($conn);

// Procesar respuesta de PayU
$transactionState = $_POST['transactionState'];
$referenceCode = $_POST['referenceCode'];
$transactionId = $_POST['transactionId'];
$polPaymentMethod = $_POST['polPaymentMethod'];

// Extraer el ID de pago de la referencia
$pagoId = str_replace('PAY_', '', $referenceCode);

// Actualizar estado del pago
if ($transactionState == 4) { // Aprobado
    $payment->updatePaymentStatus($pagoId, 'completado', $transactionId, $polPaymentMethod);
    
    // Actualizar estado de la solicitud a completada
    $stmt = $conn->prepare("
        UPDATE solicitudes s
        JOIN pagos p ON s.id_solicitud = p.id_solicitud
        SET s.estado = 'completada'
        WHERE p.id_pago = ?
    ");
    $stmt->bind_param("i", $pagoId);
    $stmt->execute();
    
    $_SESSION['success'] = "Pago completado exitosamente. ¡Gracias!";
} else {
    $payment->updatePaymentStatus($pagoId, 'fallido', $transactionId, $polPaymentMethod);
    $_SESSION['error'] = "El pago no pudo ser procesado. Por favor intenta nuevamente.";
}

header("Location: /client/pagos.php");
exit();
?>