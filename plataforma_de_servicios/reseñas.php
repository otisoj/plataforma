<?php
require_once '../config/auth.php';

// Para páginas que requieren cualquier usuario autenticado
requireAuth();

// Para páginas que requieren un rol específico
requireRole('proveedor'); // o 'admin' o 'cliente'

// Para páginas de login/register que no deben ser accedidas por usuarios autenticados
requireGuest();
if (!isset($_GET['solicitud_id'])) {
    header("Location: /client/solicitudes.php");
    exit();
}

$solicitudId = $_GET['solicitud_id'];
// Verificar que la solicitud existe y está completada
// ...
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calificar Servicio</title>
    <link rel="stylesheet" href="/assets/css/client.css">
</head>
<body>
    <?php include '../../views/partials/header.php'; ?>
    
    <div class="container">
        <h2>Calificar Servicio</h2>
        
        <form action="/controllers/reviews/submit.php" method="POST">
            <input type="hidden" name="solicitud_id" value="<?php echo $solicitudId; ?>">
            
            <div class="form-group">
                <label>Calificación</label>
                <div class="rating-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <input type="radio" id="star<?php echo $i; ?>" name="calificacion" value="<?php echo $i; ?>" required>
                        <label for="star<?php echo $i; ?>">★</label>
                    <?php endfor; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="comentario">Comentario (Opcional)</label>
                <textarea id="comentario" name="comentario" rows="4"></textarea>
            </div>
            
            <button type="submit" class="btn-primary">Enviar Reseña</button>
        </form>
    </div>
    
    <?php include '../../views/partials/footer.php'; ?>
</body>
</html>