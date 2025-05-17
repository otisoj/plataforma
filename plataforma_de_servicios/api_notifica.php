<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../models/Notification.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$notification = new Notification($conn);

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Obtener notificaciones
    $notifications = $notification->getUserNotifications($userId);
    echo json_encode($notifications);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Marcar como leída
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['notificationId'])) {
        $success = $notification->markAsRead($data['notificationId']);
        echo json_encode(['success' => $success]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'ID de notificación requerido']);
    }
}
?>