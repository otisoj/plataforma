function iniciarMap(){
    var coord = {lat: 4.813, lng: -74.354}; // Puedes cambiar la ubicación
    var map = new google.maps.Map(document.getElementById('map'), {
        zoom: 15,
        center: coord
    });
    var marker = new google.maps.Marker({
        position: coord,
        map: map
    });
}
