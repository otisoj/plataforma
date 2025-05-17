// Polling para notificaciones
function pollNotifications() {
    fetch('/api/notifications')
        .then(response => response.json())
        .then(data => {
            updateNotificationBadge(data.length);
            updateNotificationsList(data);
            setTimeout(pollNotifications, 30000); // Poll cada 30 segundos
        })
        .catch(error => {
            console.error('Error fetching notifications:', error);
            setTimeout(pollNotifications, 60000); // Reintentar después de 1 minuto en caso de error
        });
}

function updateNotificationBadge(count) {
    const badge = document.getElementById('notificationBadge');
    if (badge) {
        badge.textContent = count > 0 ? count : '';
        badge.style.display = count > 0 ? 'block' : 'none';
    }
}

function updateNotificationsList(notifications) {
    const container = document.getElementById('notificationsList');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (notifications.length === 0) {
        container.innerHTML = '<p>No hay notificaciones nuevas</p>';
        return;
    }
    
    notifications.forEach(notification => {
        const notificationElement = document.createElement('div');
        notificationElement.className = `notification ${notification.leida ? 'read' : 'unread'}`;
        notificationElement.innerHTML = `
            <p>${notification.mensaje}</p>
            <small>${new Date(notification.fecha_creacion).toLocaleString()}</small>
        `;
        
        if (notification.url_destino) {
            notificationElement.addEventListener('click', () => {
                markAsRead(notification.id_notificacion);
                window.location.href = notification.url_destino;
            });
        } else {
            notificationElement.addEventListener('click', () => {
                markAsRead(notification.id_notificacion);
            });
        }
        
        container.appendChild(notificationElement);
    });
}

function markAsRead(notificationId) {
    fetch('/api/notifications', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ notificationId })
    });
}

// Iniciar polling cuando la página cargue
document.addEventListener('DOMContentLoaded', pollNotifications);