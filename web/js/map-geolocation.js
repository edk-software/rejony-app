var markers = [];
var map;

var placeId;
var latId;
var lngId;
var searchId;

function initMapCore() {
    map = new google.maps.Map(document.getElementById('map_canvas'), {
        center: getDefaultLatLng(),
        styles: getMapStyle(),
        zoom: 9
    });
}

function findLocation() {
    var defaultPosition = getDefaultLatLng();
    debugger;
    // Try HTML5 geolocation.
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            var pos = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            updateLocation(pos.lat, pos.lng, 'Twoja lokalizacja');
        }, function () {
            showMarker(defaultPosition, 'Domyślna lokalizacja');
        });
    } else {
        // Browser doesn't support Geolocation
        showMarker(defaultPosition, 'Domyślna lokalizacja');
    }
}

function handleLocationError(browserHasGeolocation, infoWindow, pos) {
    infoWindow.setPosition(pos);
    infoWindow.setContent(browserHasGeolocation ?
        'Error: The Geolocation service failed.' :
        'Error: Your browser doesn\'t support geolocation.');
    infoWindow.open(map);
}

function deleteMarkers() {
    clearMarkers();
    markers = [];
}

function updateLocation(latVal, lngVal, address) {
    var pos = {lat: latVal, lng: lngVal};
    showMarker(pos, address);
    updateCoordinates(pos);
}

function registerChangeCoordinates(placeInputId, latInputId, lngInputId) {
    latId = latInputId;
    lngId = lngInputId;
    placeId = placeInputId;

    getLat().addEventListener('change', function () {
        updateMap();
    });
    getLng().addEventListener('change', function () {
        updateMap();
    });
}

function updateMap() {
    var latVal = Number(getLat().value);
    var lngVal = Number(getLng().value);
    var pos = {lat: latVal, lng: lngVal};
    showMarker(pos, getPlace().value);
}

function showMarker(myLatLng, address) {
    deleteMarkers();
    var marker = new google.maps.Marker({
        position: myLatLng,
        map: map,
        title: address,
        draggable: true
    });
    markers.push(marker);
    google.maps.event.addListener(marker, 'dragend', function (marker) {
        var pos = {lat: marker.latLng.lat(), lng: marker.latLng.lng()};
        updateCoordinates(pos);
    });
    map.setCenter(myLatLng);
    return marker;
}

function setMapOnAll(maps) {
    for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(maps);
    }
}

function clearMarkers() {
    setMapOnAll(null);
}

function updateCoordinates(pos) {
    getLat().value = pos.lat;
    getLng().value = pos.lng;
}

function registerGeocoder(placeInputId, searchButtonId) {
    placeId = placeInputId;
    searchId = searchButtonId;
    var geocoder = new google.maps.Geocoder();

    getSearchButton().addEventListener('click', function () {
        geocodeAddress(geocoder, map);
    });
}

function geocodeAddress(geocoder) {
    var address = getPlace().value;
    if (address === "")
        return;
    geocoder.geocode({'address': address}, function (results, status) {
        if (status === 'OK') {
            updateLocation(results[0].geometry.location.lat(), results[0].geometry.location.lng(), address);

        } else {
            alert('Geocode was not successful for the following reason: ' + status);
        }
    });
}

function getPlace() {
    return document.getElementById(placeId);
}

function getLat() {
    return document.getElementById(latId);
}

function getLng() {
    return document.getElementById(lngId)
}

function getSearchButton() {
    return document.getElementById(searchId);
}