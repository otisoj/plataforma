<?php
// Verifica si la sesión ya está iniciada antes de llamar a session_start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance(); // Correcto
    $conn = $db->getConnection();

    // Sanitize input
    $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
    $contraseña = $_POST['contraseña'];

    // Consulta SQL preparada
    $stmt = $conn->prepare("SELECT id_usuario, nom_usuario, contraseña, tipo FROM usuarios WHERE correo = :correo");
    
    // Vincular el parámetro
    $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si el usuario existe
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_usuario = $row['id_usuario'];
        $nom_usuario = $row['nom_usuario'];
        $hashed_password = $row['contraseña'];
        $tipo = $row['tipo'];

        // Verificar la contraseña
        if (password_verify($contraseña, $hashed_password)) {
            // Guardar información en la sesión
            $_SESSION['user_id'] = $id_usuario;
            $_SESSION['user_name'] = $nom_usuario;
            $_SESSION['user_type'] = $tipo;

            // Redirección según el tipo de usuario
            switch ($tipo) {
                case 'admin':
                    header("Location: /admin/dashboard.php");
                    exit;
                case 'proveedor':
                    header("Location: panel_proveedores.php");
                    exit;
                default:
                    header("Location: panel_cliente.php");
                    exit;
            }
        } else {
            // Error de autenticación
            $_SESSION['error'] = "Correo o contraseña incorrectos";
            header("Location: login.php");
            exit;
        }
    } else {
        // Si no se encuentra el usuario
        $_SESSION['error'] = "Correo o contraseña incorrectos";
        header("Location: login.php");
        exit;
    }

    // Cerrar la conexión
    $conn = null;
} else {
    // Si el método no es POST, redirigir al login
    header("Location: login.php");
    exit;
}
?>
