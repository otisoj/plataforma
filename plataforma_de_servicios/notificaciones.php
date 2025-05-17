<?php
class Notification {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create($userId, $message, $type, $url = null) {
        $stmt = $this->conn->prepare("
            INSERT INTO notificaciones (id_usuario, mensaje, tipo, url_destino) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $userId, $message, $type, $url);
        return $stmt->execute();
    }
    
    public function getUserNotifications($userId, $unreadOnly = true) {
        $query = "SELECT * FROM notificaciones WHERE id_usuario = ?";
        if ($unreadOnly) {
            $query .= " AND leida = FALSE";
        }
        $query .= " ORDER BY fecha_creacion DESC LIMIT 10";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function markAsRead($notificationId) {
        $stmt = $this->conn->prepare("
            UPDATE notificaciones SET leida = TRUE WHERE id_notificacion = ?
        ");
        $stmt->bind_param("i", $notificationId);
        return $stmt->execute();
    }
}
?>