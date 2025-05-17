<?php
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance(); // Correcto
    $conn = $db->getConnection();

    // Recoger y sanitizar datos
    $nombre = htmlspecialchars($_POST['nom_usuario']);
    $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
    $telefono = htmlspecialchars($_POST['telefono']);
    $direccion = htmlspecialchars($_POST['direccion']);
    $tipo_usuario = $_POST['tipo_usuario'];
    $contraseña = $_POST['contraseña'];
    $confirmar_contraseña = $_POST['confirmar_contraseña'];

    // Validaciones
    if ($contraseña !== $confirmar_contraseña) {
        $_SESSION['error'] = "Las contraseñas no coinciden";
        header("Location: registro.php");
        exit();
    }

    // Verificar si el correo ya existe
    $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "El correo electrónico ya está registrado";
        header("Location: registro.php");
        exit();
    }

    // Hash de la contraseña
    $hashed_password = password_hash($contraseña, PASSWORD_DEFAULT);

    // Insertar nuevo usuario
    $stmt = $conn->prepare("INSERT INTO usuarios (nom_usuario, telefono, direccion, correo, contraseña, tipo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $nombre, $telefono, $direccion, $correo, $hashed_password, $tipo_usuario);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Registro exitoso. Por favor inicia sesión.";
        header("Location: login.php");
    } else {
        $_SESSION['error'] = "Error al registrar. Por favor intenta nuevamente.";
        header("Location: registro.php");
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: registro.php");
}
