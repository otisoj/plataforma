<?php
// Verifica si la sesión está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
        </div> <!-- Cierre del div.main-wrapper abierto en header.php -->

        <footer class="footer">
            <div class="footer-container">
                <!-- Sección 1: Logo y descripción -->
                <div class="footer-section">
                    <h3>HomeServices</h3>
                    <p>Conectando clientes con proveedores de servicios del hogar en Colombia.</p>
                </div>

                <!-- Sección 2: Enlaces rápidos -->
                <div class="footer-section">
                    <h4>Enlaces Rápidos</h4>
                    <ul>
                        <li><a href="/plataforma_de_servicios/">Inicio</a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="/plataforma_de_servicios/<?php echo $_SESSION['user_type'] === 'admin' ? 'admin/dashboard.php' : ($_SESSION['user_type'] === 'proveedor' ? 'panel_proovedores.php' : 'panel_cliente.php'); ?>">Mi Panel</a></li>
                        <?php else: ?>
                            <li><a href="login.php">Iniciar Sesión</a></li>
                        <?php endif; ?>
                        <li><a href="/plataforma_de_servicios/terminos.php">Términos y Condiciones</a></li>
                    </ul>
                </div>

                <!-- Sección 3: Contacto -->
                <div class="footer-section">
                    <h4>Contacto</h4>
                    <ul>
                        <li><i class="fas fa-envelope"></i> info@homeservices.com</li>
                        <li><i class="fas fa-phone"></i> +57 123 456 7890</li>
                        <li><i class="fas fa-map-marker-alt"></i> Bogotá, Colombia</li>
                    </ul>
                </div>

                <!-- Sección 4: Redes sociales -->
                <div class="footer-section">
                    <h4>Síguenos</h4>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> HomeServices. Todos los derechos reservados.</p>
            </div>
        </footer>

        <!-- Scripts comunes -->
        <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
        <script src="provider.js"></script>
    </body>
</html>