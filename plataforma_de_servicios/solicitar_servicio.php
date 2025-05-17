<?php
define('BASE_DIR', __DIR__); // Definir BASE_DIR como constante global
$baseDir = BASE_DIR; // Asignar también a una variable para usar en includes


// 2. Incluir archivos necesarios
require_once $baseDir . '/auth.php';
require_once $baseDir . '/database.php';

if (!class_exists('Database')) {
    die("Error: La clase Database no está disponible");
}

// 3. Conexión a la base de datos
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $usuario_id = $_SESSION['user_id'] ?? null;

    if (!$usuario_id) {
        header('Location: /login.php');
        exit;
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}


$db = getDBConnection();
$stmt = $db->prepare("SELECT s.*, u.nom_usuario FROM servicios s JOIN usuarios u ON s.id_proveedor = u.id_usuario WHERE s.id_servicio = :id");
$stmt->execute([':id' => $id]);
$servicio = $stmt->fetch();

if (!$servicio) {
    die("Servicio no encontrado.");
}
?>

<h2>Solicitar Servicio</h2>
<p><strong>Tipo de Servicio:</strong> <?= htmlspecialchars($servicio['tipo_servicio']) ?></p>
<p><strong>Descripción:</strong> <?= htmlspecialchars($servicio['descripcion']) ?></p>
<p><strong>Tarifa:</strong> $<?= number_format($servicio['tarifa'], 2) ?> COP</p>
<p><strong>Proveedor:</strong> <?= htmlspecialchars($servicio['nom_usuario']) ?></p>

<form method="POST" action="procesar_solicitud.php">
    <input type="hidden" name="id_servicio" value="<?= $servicio['id_servicio'] ?>">
    
    <label for="fecha_programada">Fecha y Hora del Servicio:</label><br>
    <input type="datetime-local" id="fecha_programada" name="fecha_programada" required><br><br>
    
    <label for="direccion">Dirección:</label><br>
    <input type="text" id="direccion" name="direccion" required><br><br>
    
    <label for="comentarios">Comentarios Adicionales:</label><br>
    <textarea id="comentarios" name="comentarios"></textarea><br><br>
    
    <button type="submit">Confirmar Solicitud</button>
</form>
