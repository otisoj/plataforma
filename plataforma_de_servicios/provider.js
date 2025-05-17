/**
 * provider.js - Lógica para el panel de proveedores
 * Ubicación: /plataforma_de_servicios/assets/js/provider.js
 */

document.addEventListener('DOMContentLoaded', function() {
    // ======================
    // 1. Cargar estadísticas
    // ======================
    loadProviderStats();

    // ======================
    // 2. Notificaciones en tiempo real
    // ======================
    setupNotifications();
    setInterval(fetchNotifications, 30000); // Actualizar cada 30 segundos

    // ======================
    // 3. Formulario de nuevo servicio
    // ======================
    document.getElementById('addServiceForm')?.addEventListener('submit', handleAddService);

    // ======================
    // 4. Geolocalización para servicios
    // ======================
    initServiceLocation();
});

// =========================================
// Funciones principales
// =========================================

/**
 * Carga las estadísticas del proveedor (solicitudes, servicios, rating)
 */
async function loadProviderStats() {
    try {
        const response = await fetch('/plataforma_de_servicios/api/provider/stats');
        const data = await response.json();

        document.getElementById('pendingRequests').textContent = data.pending_requests || '0';
        document.getElementById('activeServices').textContent = data.active_services || '0';
        document.getElementById('averageRating').textContent = data.average_rating?.toFixed(1) || '0.0';
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
    }
}

/**
 * Configura el sistema de notificaciones
 */
function setupNotifications() {
    const notificationsList = document.getElementById('notificationsList');
    if (!notificationsList) return;

    // Cargar notificaciones al iniciar
    fetchNotifications();

    // Marcarlas como leídas al hacer clic
    notificationsList.addEventListener('click', async (e) => {
        const notificationItem = e.target.closest('.notification-item');
        if (notificationItem) {
            const notificationId = notificationItem.dataset.id;
            await markNotificationAsRead(notificationId);
            notificationItem.classList.remove('unread');
        }
    });
}

/**
 * Maneja el envío del formulario de nuevo servicio
 */
async function handleAddService(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.textContent;

    try {
        // Mostrar loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="loading-spinner"></span> Procesando...';

        const response = await fetch('/plataforma_de_servicios/api/services/add', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert('¡Servicio publicado con éxito!');
            form.reset();
            loadProviderStats(); // Actualizar estadísticas
        } else {
            throw new Error(result.message || 'Error al publicar el servicio');
        }
    } catch (error) {
        console.error('Error:', error);
        alert(error.message);
    } finally {
        // Restaurar botón
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
    }
}

// =========================================
// Funciones auxiliares
// =========================================

/**
 * Obtiene notificaciones del servidor
 */
async function fetchNotifications() {
    try {
        const response = await fetch('/plataforma_de_servicios/api/notifications');
        const notifications = await response.json();

        const container = document.getElementById('notificationsList');
        if (!container) return;

        container.innerHTML = notifications.map(notification => `
            <div class="notification-item ${notification.leida ? '' : 'unread'}" data-id="${notification.id}">
                <p>${notification.mensaje}</p>
                <small>${new Date(notification.fecha_creacion).toLocaleString()}</small>
            </div>
        `).join('');

    } catch (error) {
        console.error('Error al cargar notificaciones:', error);
    }
}

/**
 * Marca una notificación como leída
 */
async function markNotificationAsRead(notificationId) {
    try {
        await fetch(`/plataforma_de_servicios/api/notifications/mark-read?id=${notificationId}`);
    } catch (error) {
        console.error('Error al marcar notificación:', error);
    }
}

/**
 * Inicializa la geolocalización para servicios
 */
function initServiceLocation() {
    const locationInput = document.getElementById('serviceLocation');
    if (!locationInput) return;

    // Autocompletado con Google Places API (requiere tenerla configurada)
    if (typeof google !== 'undefined') {
        const autocomplete = new google.maps.places.Autocomplete(locationInput, {
            types: ['address'],
            componentRestrictions: { country: 'co' }
        });

        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();
            // Opcional: Guardar lat/lng en campos ocultos
            document.getElementById('serviceLat')?.value = place.geometry?.location.lat();
            document.getElementById('serviceLng')?.value = place.geometry?.location.lng();
        });
    }

    // Botón "Usar mi ubicación actual"
    const geoButton = document.createElement('button');
    geoButton.type = 'button';
    geoButton.className = 'btn-secondary';
    geoButton.innerHTML = '<i class="fas fa-location-arrow"></i> Usar mi ubicación';
    geoButton.style.marginTop = '8px';
    geoButton.onclick = getCurrentLocation;

    locationInput.parentNode.appendChild(geoButton);
}

/**
 * Obtiene la ubicación actual del usuario
 */
function getCurrentLocation() {
    const locationInput = document.getElementById('serviceLocation');
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
                // Reverse geocoding (requiere implementación con API)
                fetch(`https://maps.googleapis.com/maps/api/geocode/json?latlng=${latitude},${longitude}&key=TU_API_KEY`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.results[0]) {
                            locationInput.value = data.results[0].formatted_address;
                            // Opcional: Guardar lat/lng
                            document.getElementById('serviceLat')?.value = latitude;
                            document.getElementById('serviceLng')?.value = longitude;
                        }
                    });
            },
            (error) => {
                console.error('Error al obtener ubicación:', error);
                alert('No se pudo obtener la ubicación. Por favor ingrésala manualmente.');
            }
        );
    } else {
        alert('Tu navegador no soporta geolocalización.');
    }
}