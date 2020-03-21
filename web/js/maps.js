var maps = (function ($, ol) {
    function getDefaultLonLat() {
        return [19.498, 50.043];
    }

    function getMap(id, zoom, centerLonLat) {
        return new ol.Map({
            layers: [
                new ol.layer.Tile({
                    source: new ol.source.OSM()
                })
            ],
            target: id,
            view: new ol.View({
                center: ol.proj.fromLonLat(centerLonLat),
                zoom: zoom
            })
        });
    }

    function addMarkerPopupsSupport(map) {
        if (!ol.Overlay.Popup) {
            return;
        }
        var overlay = new ol.Overlay.Popup();
        map.addOverlay(overlay);
        map.on('singleclick', function (event) {
            var marker = (function (features) {
                for (var i = 0; i < features.length; i++) {
                    if (features[i].get('popup')) {
                        return features[i];
                    }
                }
                return null;
            })(map.getFeaturesAtPixel(event.pixel));
            if (marker) {
                overlay.show(marker.getGeometry().getCoordinates(), marker.get('popup'));
            } else {
                overlay.hide();
            }
        });
    }

    function getIcon(status) {
        var baseUrl = 'https://maps.google.com/mapfiles/ms/icons/';
        switch (Number(status)) {
            case 0:
                return baseUrl + 'blue-dot.png';
            case 1:
                return baseUrl + 'yellow-dot.png';
            case 2:
                return baseUrl + 'green-dot.png';
            case 3:
            default:
                return baseUrl + 'red-dot.png';
        }
    }

    function getMarker(coords, options) {
        if (!options) {
            options = {};
        }
        var marker = new ol.Feature(
            new ol.geom.Point(coords)
        );
        marker.setStyle(
            new ol.style.Style({
                image: new ol.style.Icon(({
                    anchor: options.anchor || [0.5, 1],
                    anchorXUnits: 'fraction',
                    anchorYUnits: 'fraction',
                    opacity: 1,
                    src: options.icon || getIcon(),
                    zIndex: 1
                })),
                zIndex: 1
            })
        );
        if (options.popup) {
            marker.set('popup', options.popup, true);
        }
        return marker;
    }

    function renderMarkers(id, places) {
        var map = getMap(id, 5, getDefaultLonLat());
        var markers = places.filter(function (place) {
            return place.lng && place.lat;
        }).map(function (place) {
            return getMarker(ol.proj.fromLonLat([Number(place.lng), Number(place.lat)]), {
                icon: getIcon(place.status),
                popup: '<p><a href="' + place.id + '/info"><b>' + place.name + '</b></a></p>' +
                    '<p><b>' + place.statusText + '</b></p>',
            });
        });
        map.addLayer(new ol.layer.Vector({
            source: new ol.source.Vector({
                features: markers
            })
        }));
        addMarkerPopupsSupport(map);
        // @TODO: Move center of the map to sum of marker's coordinates.
    }

    return {
        renderMarkersFromUrl: function (id, jsonUrl) {
            $.ajax({
                dataType: 'json',
                success: function (places) {
                    renderMarkers(id, places);
                },
                url: jsonUrl
            });
        },
        renderFromKml: function (id, kmlUrl) {
            var map = getMap(id, 11, [19.946850, 50.06561980]); // @TODO: Use pathAvg coordinates instead.
            map.addLayer(new ol.layer.Vector({
                source: new ol.source.Vector({
                    format: new ol.format.KML(),
                    url: kmlUrl
                })
            }));
        }
    };
})(jQuery, ol);
