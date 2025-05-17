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
        header('Location: login.php');
        exit;
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$error = '';
$success = '';
$query = "
    SELECT 
        r.id_resena,
        r.calificacion,
        r.comentario,
        r.fecha_creacion,
        s.tipo_servicio,
        u.nom_usuario AS proveedor_nombre,
        u.foto_perfil AS proveedor_foto
    FROM resenas r
    JOIN servicios s ON r.id_servicio = s.id_servicio
    JOIN usuarios u ON s.id_proveedor = u.id_usuario
    WHERE r.id_cliente = :cliente_id
    ORDER BY r.fecha_creacion DESC
";

$reseñas = $conn->prepare($query);
$reseñas->execute([':cliente_id' => $_SESSION['user_id']]);
$reseñas = $reseñas->fetchAll();

$queryServiciosSinResena = "
    SELECT 
        s.id_servicio,
        s.tipo_servicio,
        u.nom_usuario AS proveedor_nombre
    FROM solicitudes sc
    JOIN servicios s ON sc.id_servicio = s.id_servicio
    JOIN usuarios u ON s.id_proveedor = u.id_usuario
    WHERE sc.id_cliente = :cliente_id
    AND NOT EXISTS (
        SELECT 1 FROM resenas r 
        WHERE r.id_servicio = s.id_servicio 
        AND r.id_cliente = :cliente_id
    )
    AND sc.estado = 'completado'
";

$serviciosSinResena = $conn->prepare($queryServiciosSinResena);
$serviciosSinResena->execute([':cliente_id' => $_SESSION['user_id']]);
$serviciosSinResena = $serviciosSinResena->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reseñas - Cliente</title>
    <link rel="stylesheet" href="main.css">
    <style>
        /* Estilos CSS... (mantén todos los estilos que ya tienes) */
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="dashboard-container">
        <div class="sidebar">
            <ul>
                <li><a href="panel_cliente.php">Inicio</a></li>
                <li><a href="cliente/pagos.php">Pagos</a></li>
                <li><a href="cliente/reseñas.php" class="active">Reseñas</a></li>
                <li><a href="perfil.php">Mi Perfil</a></li>
                <li><a href="login.php">Cerrar Sesión</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1>Mis Reseñas</h1>
            <p class="welcome-message">Tus opiniones sobre los servicios, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>

            <!-- Sección para agregar reseñas a servicios sin reseña -->
            <?php if(!empty($serviciosSinResena)): ?>
                <div class="add-review-section">
                    <h2 class="add-review-title">Agregar Nueva Reseña</h2>
                    <form action="/cliente/procesar_resena.php" method="POST">
                        <div class="form-group">
                            <label for="servicio">Servicio:</label>
                            <select name="id_servicio" id="servicio" required>
                                <option value="">Selecciona un servicio</option>
                                <?php foreach ($serviciosSinResena as $servicio): ?>
                                    <option value="<?= $servicio['id_servicio'] ?>">
                                        <?= htmlspecialchars(ucfirst($servicio['tipo_servicio'])) ?> - 
                                        <?= htmlspecialchars($servicio['proveedor_nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Calificación:</label>
                            <div class="rating-stars">
                                <input type="radio" id="star5" name="calificacion" value="5" required>
                                <label for="star5">★</label>
                                <input type="radio" id="star4" name="calificacion" value="4">
                                <label for="star4">★</label>
                                <input type="radio" id="star3" name="calificacion" value="3">
                                <label for="star3">★</label>
                                <input type="radio" id="star2" name="calificacion" value="2">
                                <label for="star2">★</label>
                                <input type="radio" id="star1" name="calificacion" value="1">
                                <label for="star1">★</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="comentario">Comentario:</label>
                            <textarea name="comentario" id="comentario" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn-submit">Enviar Reseña</button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Listado de reseñas existentes -->
            <div class="reviews-container">
                <?php if(empty($reseñas)): ?>
                    <div class="no-reviews">
                        <h3>No has escrito ninguna reseña</h3>
                        <p>Cuando califiques un servicio, aparecerá aquí tu opinión.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($reseñas as $resena): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <img src="../uploads/<?= htmlspecialchars($resena['proveedor_foto'] ?? 'default-profile.jpg') ?>" 
                                     alt="<?= htmlspecialchars($resena['proveedor_nombre']) ?>" 
                                     class="review-provider-img">
                                <div class="review-provider-info">
                                    <h3 class="review-provider-name"><?= htmlspecialchars($resena['proveedor_nombre']) ?></h3>
                                    <p class="review-service"><?= htmlspecialchars(ucfirst($resena['tipo_servicio'])) ?></p>
                                </div>
                                <div class="review-rating"><?= $resena['calificacion'] ?> ★</div>
                            </div>
                            <div class="review-content">
                                <p><?= nl2br(htmlspecialchars($resena['comentario'])) ?></p>
                            </div>
                            <div class="review-date">
                                <?= date('d/m/Y H:i', strtotime($resena['fecha_creacion'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>


</body>
</html>