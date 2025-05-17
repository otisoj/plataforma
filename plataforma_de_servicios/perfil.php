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

$error = '';
$success = '';

// 4. Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nombre = trim($_POST['nombre']);
        $correo = trim($_POST['correo']);
        $telefono = trim($_POST['telefono']);
        $direccion = trim($_POST['direccion']);

        if (empty($nombre) || empty($correo)) {
            throw new Exception('Nombre y correo son obligatorios');
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El correo no es válido');
        }

        $foto_perfil = null;
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['foto_perfil'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

            if (!in_array($file['type'], $allowed_types)) {
                throw new Exception('Solo se permiten imágenes JPEG, PNG o GIF');
            }

            if ($file['size'] > 2097152) {
                throw new Exception('La imagen no puede superar los 2MB');
            }

            $upload_dir = $baseDir . '/uploads/perfiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'user_' . $usuario_id . '_' . time() . '.' . $ext;
            $destination = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $foto_perfil = '/uploads/perfiles/' . $filename;

                // Eliminar foto anterior
                $stmt = $conn->prepare("SELECT foto_perfil FROM usuarios WHERE id_usuario = ?");
                $stmt->execute([$usuario_id]);
                $old_photo = $stmt->fetchColumn();

                if ($old_photo && file_exists($baseDir . $old_photo)) {
                    unlink($baseDir . $old_photo);
                }
            }
        }

        if ($foto_perfil) {
            $stmt = $conn->prepare("UPDATE usuarios SET nom_usuario=?, correo=?, telefono=?, direccion=?, foto_perfil=? WHERE id_usuario=?");
            $stmt->execute([$nombre, $correo, $telefono, $direccion, $foto_perfil, $usuario_id]);
        } else {
            $stmt = $conn->prepare("UPDATE usuarios SET nom_usuario=?, correo=?, telefono=?, direccion=? WHERE id_usuario=?");
            $stmt->execute([$nombre, $correo, $telefono, $direccion, $usuario_id]);
        }

        $_SESSION['user_name'] = $nombre;
        $_SESSION['user_email'] = $correo;
        if ($foto_perfil) {
            $_SESSION['user_photo'] = $foto_perfil;
        }

        $success = 'Perfil actualizado correctamente';
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// 5. Obtener información actual del usuario
$stmt = $conn->prepare("SELECT id_usuario, nom_usuario, telefono, direccion, correo, foto_perfil, fecha_registro FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header('Location: /panel_cliente.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - Cliente</title>
    <link rel="stylesheet" href="main.css">
    <style>
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
        }

        .profile-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 25px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .profile-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 25px;
            border: 4px solid #3498db;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn-primary {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
    </style>
</head>

<body>
    <?php include $baseDir . '/header.php'; ?>

    <div class="perfil">

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
<div class="sidebar">
            <ul>
                <li><a href="panel_cliente.php" class="active">Inicio</a></li>
                <li><a href="pago_cliente.php">Pagos</a></li>
                <li><a href="reseña_cliente.php">Reseñas</a></li>
                <li><a href="perfil.php">Mi Perfil</a></li>
                <li><a href="login.php">Cerrar Sesión</a></li>
            </ul>
        </div>
        <div class="profile-container">
        
            <h1>Mi Perfil</h1>
            <div class="profile-header">

                <img src="<?= htmlspecialchars($usuario['foto_perfil'] ?? '/assets/images/default-profile.jpg') ?>" class="profile-photo" id="currentPhoto">
                <div>
                    <h2><?= htmlspecialchars($usuario['nom_usuario']) ?></h2>
                    <p>Miembro desde: <?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></p>
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nombre">Nombre completo</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nom_usuario']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="correo">Correo electrónico</label>
                    <input type="email" id="correo" name="correo" class="form-control" value="<?= htmlspecialchars($usuario['correo']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" class="form-control" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="direccion">Dirección</label>
                    <input type="text" id="direccion" name="direccion" class="form-control" value="<?= htmlspecialchars($usuario['direccion'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="foto_perfil">Cambiar foto de perfil</label>
                    <input type="file" id="foto_perfil" name="foto_perfil" class="form-control" accept="image/*">
                    <img id="photoPreview" class="photo-preview" src="#" alt="Vista previa" style="display:none;">
                </div>
                <button type="submit" class="btn-primary">Guardar cambios</button>
            </form>
        </div>
    </div>

    <?php include $baseDir . '/footer.php'; ?>

    <script>
        // Vista previa imagen
        document.getElementById('foto_perfil').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('photoPreview').src = event.target.result;
                    document.getElementById('photoPreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>