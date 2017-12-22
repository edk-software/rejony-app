function getMapStyle() {
    return [{
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
    }, {"featureType": "water", "elementType": "all", "stylers": [{"color": "#57677a"}, {"visibility": "on"}]}];
}

function getDefaultLatLng() {
    return {lat: 50.043, lng: 19.498};
}

function getMap(elementId, zoomVal, defaultPos) {
    return new google.maps.Map(document.getElementById(elementId), {
        zoom: zoomVal,
        center: defaultPos,
        styles: getMapStyle()
    });
}
function loadMap(mapElementId, lat, lng, title){
    loadMapZoom(mapElementId,lat,lng,title,15);
}
function loadMapZoom(mapElementId, lat, lng, title, zoomVal) {
    var pos = {lat: lat, lng: lng};
    var map = getMap(mapElementId, zoomVal, pos);
    var marker = new google.maps.Marker({
        position: pos,
        map: map,
        title: title,
        draggable: false
    });
}