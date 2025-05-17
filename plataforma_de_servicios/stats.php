<?php
require_once 'database.php';
require_once 'auth.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

// Verificar autenticación
requireRole('proveedor');

$providerId = $_SESSION['user_id'];

// Consultas SQL
$stats = [
    'pending_requests' => 0,
    'active_services' => 0,
    'average_rating' => 0
];

// 1. Solicitudes pendientes
$stmt = $conn->prepare("SELECT COUNT(*) FROM solicitudes WHERE id_servicio IN 
    (SELECT id_servicio FROM servicios WHERE id_proveedor = ?) AND estado = 'pendiente'");
$stmt->bind_param("i", $providerId);
$stmt->execute();
$stmt->bind_result($stats['pending_requests']);
$stmt->fetch();
$stmt->close();

// 2. Servicios activos
$stmt = $conn->prepare("SELECT COUNT(*) FROM servicios WHERE id_proveedor = ? AND disponibilidad = 'activo'");
$stmt->bind_param("i", $providerId);
$stmt->execute();
$stmt->bind_result($stats['active_services']);
$stmt->fetch();
$stmt->close();

// 3. Rating promedio
$stmt = $conn->prepare("SELECT AVG(calificacion) FROM reseñas WHERE id_proveedor = ?");
$stmt->bind_param("i", $providerId);
$stmt->execute();
$stmt->bind_result($stats['average_rating']);
$stmt->fetch();
$stmt->close();

echo json_encode($stats);
?>