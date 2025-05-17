<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Home Services</title>
    <link rel="stylesheet" href="main.css"></head>
</head>
<body>
    <div class="auth-container">
        <h2>Registro</h2>
        <form action="registro_en_proceso.php" method="POST">
            <div class="form-group">
                <label for="nom_usuario">Nombre Completo</label>
                <input type="text" id="nom_usuario" name="nom_usuario" required>
            </div>
            <div class="form-group">
                <label for="correo">Correo Electrónico</label>
                <input type="email" id="correo" name="correo" required>
            </div>
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" required>
            </div>
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <input type="text" id="direccion" name="direccion" required>
            </div>
            <div class="form-group">
                <label for="tipo_usuario">Tipo de Usuario</label>
                <select id="tipo_usuario" name="tipo_usuario" required>
                    <option value="cliente">Cliente</option>
                    <option value="proveedor">Proveedor de Servicios</option>
                </select>
            </div>
            <div class="form-group">
                <label for="contraseña">Contraseña</label>
                <input type="password" id="contraseña" name="contraseña" required>
            </div>
            <div class="form-group">
                <label for="confirmar_contraseña">Confirmar Contraseña</label>
                <input type="password" id="confirmar_contraseña" name="confirmar_contraseña" required>
            </div>
            <button type="submit" class="btn-primary">Registrarse</button>
        </form>
        <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></p>
    </div>
</body>
</html>