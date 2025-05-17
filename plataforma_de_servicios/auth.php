<?php
session_start();

/**
 * Verifica si el usuario está autenticado
 * Redirige al login si no hay sesión activa
 */
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /login.php");
        exit();
    }
}

/**
 * Verifica si el usuario tiene un rol específico
 * Redirige al dashboard correspondiente si no tiene el rol requerido
 * @param string $requiredRole Rol requerido (admin, proveedor, cliente)
 */
function requireRole($requiredRole) {
    requireAuth(); // Primero verifica que esté autenticado
    
    if ($_SESSION['user_type'] !== $requiredRole) {
        // Redirige según el rol del usuario
        switch ($_SESSION['user_type']) {
            case 'admin':
                header("Location: /admin/dashboard.php");
                break;
            case 'proveedor':
                header("Location: /provider/dashboard.php");
                break;
            default:
                header("Location: /client/dashboard.php");
        }
        exit();
    }
}

/**
 * Verifica si el usuario NO está autenticado
 * Redirige al dashboard si hay sesión activa
 */
function requireGuest() {
    if (isset($_SESSION['user_id'])) {
        // Redirige según el rol del usuario
        switch ($_SESSION['user_type']) {
            case 'admin':
                header("Location: /admin/dashboard.php");
                break;
            case 'proveedor':
                header("Location: /provider/dashboard.php");
                break;
            default:
                header("Location: /client/dashboard.php");
        }
        exit();
    }
}

/**
 * Obtiene el ID del usuario autenticado
 * @return int|null ID del usuario o null si no está autenticado
 */
function getAuthUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Obtiene el tipo de usuario autenticado
 * @return string|null Tipo de usuario o null si no está autenticado
 */
function getAuthUserType() {
    return $_SESSION['user_type'] ?? null;
}

/**
 * Obtiene el nombre del usuario autenticado
 * @return string|null Nombre del usuario o null si no está autenticado
 */
function getAuthUserName() {
    return $_SESSION['user_name'] ?? null;
}

/**
 * Verifica si el usuario actual tiene permiso para acceder a un recurso
 * @param int $resourceUserId ID del usuario dueño del recurso
 * @param string|null $resourceUserType Tipo de usuario dueño del recurso (opcional)
 * @return bool True si tiene permiso, false si no
 */
function checkResourcePermission($resourceUserId, $resourceUserType = null) {
    $currentUserId = getAuthUserId();
    $currentUserType = getAuthUserType();
    
    // Admins pueden acceder a todo
    if ($currentUserType === 'admin') {
        return true;
    }
    
    // El dueño del recurso puede acceder
    if ($currentUserId === $resourceUserId) {
        return true;
    }
    
    // Validación adicional por tipo de recurso si se especifica
    if ($resourceUserType && $currentUserType === $resourceUserType) {
        return true;
    }
    
    return false;
}