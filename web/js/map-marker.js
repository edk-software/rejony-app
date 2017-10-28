function render(data) {
    var myLatLng = {lat: 50.043, lng: 19.498};
    var map = new
    google.maps.Map(document.getElementById('map'), {
        zoom: 5,
        center: myLatLng,
        styles: [{
            "featureType": "administrative",
            "elementType": "labels.text.fill",
            "stylers": [{"color": "#444444"}]
        }, {
            "featureType": "administrative.land_parcel",
            "elementType": "all",
            "stylers": [{"visibility": "off"}]
        }, {
            "featureType": "landscape",
            "elementType": "all",
            "stylers": [{"color": "#f2f2f2"}]
        }, {
            "featureType": "landscape.natural",
            "elementType": "all",
            "stylers": [{"visibility": "off"}]
        }, {
            "featureType": "poi",
            "elementType": "all",
            "stylers": [{"visibility": "on"}, {"color": "#052366"}, {"saturation": "-70"}, {"lightness": "85"}]
        }, {
            "featureType": "poi",
            "elementType": "labels",
            "stylers": [{"visibility": "simplified"}, {"lightness": "-53"}, {"weight": "1.00"}, {"gamma": "0.98"}]
        }, {
            "featureType": "poi",
            "elementType": "labels.icon",
            "stylers": [{"visibility": "simplified"}]
        }, {
            "featureType": "road",
            "elementType": "all",
            "stylers": [{"saturation": -100}, {"lightness": 45}, {"visibility": "on"}]
        }, {
            "featureType": "road",
            "elementType": "geometry",
            "stylers": [{"saturation": "-18"}]
        }, {
            "featureType": "road",
            "elementType": "labels",
            "stylers": [{"visibility": "off"}]
        }, {
            "featureType": "road.highway",
            "elementType": "all",
            "stylers": [{"visibility": "on"}]
        }, {
            "featureType": "road.arterial",
            "elementType": "all",
            "stylers": [{"visibility": "on"}]
        }, {
            "featureType": "road.arterial",
            "elementType": "labels.icon",
            "stylers": [{"visibility": "off"}]
        }, {
            "featureType": "road.local",
            "elementType": "all",
            "stylers": [{"visibility": "on"}]
        }, {
            "featureType": "transit",
            "elementType": "all",
            "stylers": [{"visibility": "off"}]
        }, {"featureType": "water", "elementType": "all", "stylers": [{"color": "#57677a"}, {"visibility": "on"}]}]
    });
    for (i = 0; i < data.length; i++) {
        var place = data[i];
        if (data[i].lng == null)
            continue;

        var marker = new google.maps.Marker({
            position: {lat: Number(place.lat), lng: Number(place.lng)},
            map: map,
            title: place.name,
            draggable: false
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