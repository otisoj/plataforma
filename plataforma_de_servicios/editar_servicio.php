<?php
require_once __DIR__ . '/auth.php';
requireAuth();
requireRole('proveedor');

require_once __DIR__ . '/includes/database.php';

// Verificar que se recibe el ID del servicio
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "No se especificó el servicio a editar";
    header("Location: servicios.php");
    exit;
}

$id_servicio = $_GET['id'];

// Obtener los datos actuales del servicio
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT * FROM servicios 
        WHERE id_servicio = :id_servicio AND id_proveedor = :id_proveedor
    ");
    $stmt->execute([
        ':id_servicio' => $id_servicio,
        ':id_proveedor' => $_SESSION['user_id']
    ]);
    $servicio = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$servicio) {
        throw new Exception("Servicio no encontrado o no tienes permiso para editarlo");
    }
    
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: servicios.php");
    exit;
}

// Procesar actualización del servicio
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDBConnection();
        $pdo->beginTransaction();
        
        // Validar datos requeridos
        $requiredFields = ['tipo_servicio', 'descripcion', 'tarifa', 'disponibilidad'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("El campo $field es requerido");
            }
        }

        // Validar tarifa numérica
        if (!is_numeric($_POST['tarifa']) || $_POST['tarifa'] < 0) {
            throw new Exception("La tarifa debe ser un número positivo");
        }

        // Actualizar servicio en la base de datos
        $stmt = $pdo->prepare("
            UPDATE servicios SET
                tipo_servicio = :tipo_servicio,
                descripcion = :descripcion,
                tarifa = :tarifa,
                disponibilidad = :disponibilidad
            WHERE id_servicio = :id_servicio AND id_proveedor = :id_proveedor
        ");
        
        $result = $stmt->execute([
            ':tipo_servicio' => $_POST['tipo_servicio'],
            ':descripcion' => $_POST['descripcion'],
            ':tarifa' => (float)$_POST['tarifa'],
            ':disponibilidad' => $_POST['disponibilidad'],
            ':id_servicio' => $id_servicio,
            ':id_proveedor' => $_SESSION['user_id']
        ]);

        if (!$result) {
            throw new Exception("No se pudo actualizar el servicio");
        }

        $pdo->commit();
        
        // Actualizar los datos locales para mostrar los cambios inmediatamente
        $servicio['tipo_servicio'] = $_POST['tipo_servicio'];
        $servicio['descripcion'] = $_POST['descripcion'];
        $servicio['tarifa'] = $_POST['tarifa'];
        $servicio['disponibilidad'] = $_POST['disponibilidad'];
        
        $_SESSION['success_message'] = "Servicio actualizado exitosamente";
        
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error_message'] = "Error técnico al actualizar el servicio: " . $e->getMessage();
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error_message'] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Servicio</title>
    <link rel="stylesheet" href="main.css">
    <style>
        .edit-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea.form-control {
            height: 100px;
            resize: vertical;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            display: inline-block;
            text-decoration: none;
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .location-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="dashboard-container">
        <div class="sidebar">
            <ul>
                <li><a href="../panel_proveedores.php">Inicio</a></li>
                <li><a href="servicios.php">Mis Servicios</a></li>
                <li><a href="solicitudes.php">Solicitudes</a></li>
                <li><a href="ofertas.php">Ofertas</a></li>
                <li><a href="pagos.php">Pagos</a></li>
                <li><a href="reseñas.php">Reseñas</a></li>
                <li><a href="perfil.php">Mi Perfil</a></li>
                <li><a href="../login.php">Cerrar Sesión</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="edit-container">
                <h2>Editar Servicio</h2>
                
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-error"><?= $_SESSION['error_message'] ?></div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                
                <form method="POST" id="serviceForm">
                    <div class="form-group">
                        <label for="tipo_servicio">Tipo de Servicio</label>
                        <select id="tipo_servicio" name="tipo_servicio" class="form-control" required>
                            <option value="plomeria" <?= $servicio['tipo_servicio'] == 'plomeria' ? 'selected' : '' ?>>Plomería</option>
                            <option value="electricidad" <?= $servicio['tipo_servicio'] == 'electricidad' ? 'selected' : '' ?>>Electricidad</option>
                            <option value="limpieza" <?= $servicio['tipo_servicio'] == 'limpieza' ? 'selected' : '' ?>>Limpieza</option>
                            <option value="jardineria" <?= $servicio['tipo_servicio'] == 'jardineria' ? 'selected' : '' ?>>Jardinería</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" class="form-control" required><?= htmlspecialchars($servicio['descripcion']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="tarifa">Tarifa (COP)</label>
                        <input type="number" id="tarifa" name="tarifa" class="form-control" min="0" step="0.01" 
                               value="<?= htmlspecialchars($servicio['tarifa']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="disponibilidad">Disponibilidad</label>
                        <input type="text" id="disponibilidad" name="disponibilidad" class="form-control" 
                               value="<?= htmlspecialchars($servicio['disponibilidad']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <div class="location-info">
                            <label>Ubicación Actual</label>
                            <p>Latitud: <?= $servicio['latitud'] ?>, Longitud: <?= $servicio['longitud'] ?></p>
                            <small>Para cambiar la ubicación, debes crear un nuevo servicio con la ubicación correcta.</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        <a href="provider/servicios.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>

    <script>
        // Validación del formulario antes de enviar
        document.getElementById('serviceForm').addEventListener('submit', function(e) {
            const tarifa = document.getElementById('tarifa').value;
            
            if (isNaN(tarifa) || parseFloat(tarifa) < 0) {
                alert('La tarifa debe ser un número positivo');
                e.preventDefault();
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>