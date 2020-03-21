var maps = (function (ol) {
    function getDefaultLonLat() {
        return [19.498, 50.043];
    }

    function getMap(id, zoom, lonLat) {
        return new ol.Map({
            layers: [
                new ol.layer.Tile({
                    source: new ol.source.OSM(),
                }),
            ],
            target: id,
            view: new ol.View({
                center: ol.proj.fromLonLat(lonLat),
                zoom: zoom,
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

    function getMarker(lonLat, options) {
        if (!options) {
            options = {};
        }
        var marker = new ol.Feature(
            new ol.geom.Point(ol.proj.fromLonLat(lonLat))
        );
        marker.setStyle(
            new ol.style.Style({
                image: new ol.style.Icon(({
                    anchor: options.anchor || [0.5, 1],
                    anchorXUnits: 'fraction',
                    anchorYUnits: 'fraction',
                    opacity: 1,
                    src: options.icon || getIcon(),
                    zIndex: 1,
                })),
                zIndex: 1,
            })
        );
        if (options.popup) {
            marker.set('popup', options.popup, true);
        }
        return marker;
    }

    function getNumberFromInput(field) {
        return Number(field.val().trim().replace(/[^0-9\.-]/g, ''));
    }

    return {
        renderCoordsFetcher: function (id, initLonLat, latInput, lonInput, placeInput, searchButton) {
            var coordsNotSet = initLonLat[0] === 0 && initLonLat[1] === 0;
            var map = getMap(id, coordsNotSet ? 3 : 11, initLonLat);
            var marker = getMarker(initLonLat);
            var source = new ol.source.Vector({
                features: [marker],
            });
            map.addLayer(new ol.layer.Vector({
                source: source,
            }));
            var modify = new ol.interaction.Modify({
                source: source,
            });
            map.addInteraction(modify);
            var relocateMarker = function (lonLat) {
                marker.getGeometry()
                    .setCoordinates(ol.proj.fromLonLat(lonLat));
            };
            var onCoordsChange = function () {
                relocateMarker([getNumberFromInput(lonInput), getNumberFromInput(latInput)]);
            };
            var onMarkerMove = function () {
                var lonLatFromMarker = ol.proj.toLonLat(marker.getGeometry().getCoordinates());
                latInput.val(lonLatFromMarker[1]);
                lonInput.val(lonLatFromMarker[0]);
            };
            var onPositionFind = function (lonLat) {
                relocateMarker(lonLat);
                var view = map.getView();
                view.setCenter(ol.proj.fromLonLat(lonLat));
                view.setZoom(8);
                onMarkerMove();
            };
            latInput.on('change', onCoordsChange);
            lonInput.on('change', onCoordsChange);
            modify.on('modifyend', onMarkerMove);
            if (coordsNotSet && navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    onPositionFind([position.coords.longitude, position.coords.latitude]);
                }, function () {
                    onPositionFind(getDefaultLonLat());
                });
            }
            if (placeInput && searchButton && google.maps.Geocoder) {
                searchButton.on('click', function () {
                    var address = placeInput.val();
                    if (address === '') {
                        return;
                    }
                    var geoCoder = new google.maps.Geocoder();
                    geoCoder.geocode({ address: address }, function (results, status) {
                        if (status !== 'OK') {
                            alert('Geocode was not successful for the following reason: ' + status);
                            return;
                        }
                        onPositionFind([results[0].geometry.location.lng(), results[0].geometry.location.lat()]);
                    });
                });
            }
        },
        renderFromKml: function (id, kmlUrl) {
            var map = getMap(id, 11, [19.946850, 50.06561980]); // @TODO: Use pathAvg coordinates instead.
            map.addLayer(new ol.layer.Vector({
                source: new ol.source.Vector({
                    format: new ol.format.KML(),
                    url: kmlUrl,
                })
            }));
        },
        renderFromPlaces: function (id, places) {
            var map = getMap(id, 5, getDefaultLonLat());
            var markers = places.filter(function (place) {
                return place.lng && place.lat;
            }).map(function (place) {
                return getMarker([Number(place.lng), Number(place.lat)], {
                    icon: getIcon(place.status),
                    popup: '<p><a href="' + place.id + '/info"><b>' + place.name + '</b></a></p>' +
                        '<p><b>' + place.statusText + '</b></p>',
                });
            });
            map.addLayer(new ol.layer.Vector({
                source: new ol.source.Vector({
                    features: markers,
                })
            }));
            addMarkerPopupsSupport(map);
            // @TODO: Move center of the map to sum of marker's coordinates.
        },
        renderWithMarker: function (id, lonLat) {
            var map = getMap(id, 15, lonLat);
            map.addLayer(new ol.layer.Vector({
                source: new ol.source.Vector({
                    features: [getMarker(lonLat)],
                })
            }));
        },
    };
})(ol);
