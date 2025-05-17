document.addEventListener('DOMContentLoaded', function() {
    // Inicializar mapa
    const map = L.map('map').setView([4.5709, -74.2973], 12); // Coordenadas de Bogotá
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Obtener ubicación del usuario
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const userLat = position.coords.latitude;
            const userLng = position.coords.longitude;
            
            // Centrar mapa en la ubicación del usuario
            map.setView([userLat, userLng], 14);
            
            // Marcador para la ubicación del usuario
            L.marker([userLat, userLng]).addTo(map)
                .bindPopup('Tu ubicación')
                .openPopup();
                
            // Cargar proveedores cercanos
            loadNearbyProviders(userLat, userLng);
        }, function(error) {
            console.error("Error al obtener la ubicación:", error);
            // Ubicación por defecto (Bogotá) si el usuario no permite geolocalización
            loadNearbyProviders(4.5709, -74.2973);
        });
    } else {
        console.error("Geolocalización no soportada por el navegador");
        loadNearbyProviders(4.5709, -74.2973);
    }
    
    // Función para cargar proveedores cercanos
    function loadNearbyProviders(lat, lng) {
        const serviceType = document.getElementById('serviceType').value;
        
        fetch(`/api/providers/nearby?lat=${lat}&lng=${lng}&service=${serviceType}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(provider => {
                    const marker = L.marker([provider.latitud, provider.longitud]).addTo(map);
                    
                    let popupContent = `
                        <h4>${provider.nom_usuario}</h4>
                        <p><strong>Servicio:</strong> ${provider.tipo_servicio}</p>
                        <p><strong>Tarifa:</strong> $${provider.tarifa} COP</p>
                        <p><strong>Disponibilidad:</strong> ${provider.disponibilidad}</p>
                        <button onclick="requestService(${provider.id_servicio})" class="btn-primary">Solicitar Servicio</button>
                    `;
                    
                    marker.bindPopup(popupContent);
                });
            })
            .catch(error => console.error('Error:', error));
    }
    
    // Evento de búsqueda
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                loadNearbyProviders(position.coords.latitude, position.coords.longitude);
            });
        }
    });
    
    // Solicitar servicio
    window.requestService = function(serviceId) {
        // Implementar lógica para solicitar servicio
        console.log("Solicitando servicio:", serviceId);
    }
});