function render(data) {
    var myLatLng = {lat: 50.043, lng: 19.498};
    var map = new google.maps.Map(document.getElementById('map'), {
        zoom: 5,
        center: myLatLng
    });
    for (i = 0; i < data.length; i++) {
        var place = data[i];
        if (data[i].lng == null)
            continue;

        var marker = new google.maps.Marker({
            position: {lat: Number(place.lat), lng: Number(place.lng)},
            map: map,
            title: place.name,
            draggable: false,
        });


        if (Number(place.status) == 0) //Zgłoszenia nowe
        {
            marker.setIcon('http://maps.google.com/mapfiles/ms/icons/blue-dot.png');
        }
        else if (Number(place.status) == 1) //Weryfikacja
        {
            marker.setIcon('http://maps.google.com/mapfiles/ms/icons/yellow-dot.png');
        }
        else if (Number(place.status) == 2) //Zatwierdzone
        {
            marker.setIcon('http://maps.google.com/mapfiles/ms/icons/green-dot.png');
        }
        else if (Number(place.status) == 3) //Odrzucone
        {
            marker.setIcon('http://maps.google.com/mapfiles/ms/icons/red-dot.png');
        }

        var infowindow = new google.maps.InfoWindow();


        google.maps.event.addListener(marker, "click", (function (marker, place) {
            return function (evt) {

                var content = '<div>' +
                    '<p><a href="' + place.id + '/info"><b>' + marker.getTitle() + '</b></a></p>' +
                    '<div id="bodyContent">' +
                    '<p><b>' + place.statusText + '</b> </p>' +
                    '</div>' +
                    '</div>';
                infowindow.setContent(content);
                infowindow.open(map, marker);
            }
        })(marker, place));
    }
    ;

}