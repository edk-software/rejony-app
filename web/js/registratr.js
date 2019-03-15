(function ($) {
    $.fn.registratr = function (options) {
        var opts = $.extend({}, $.fn.registratr.defaults, options);
        var root = $(this);
        var routesList = [];
        var routeSelect;
        var territorySelect;
        var selectedRouteId = 0;

        if (null !== opts['routeSelector'] && null !== opts['routeUrl']) {
            routeSelect = root.find(opts['routeSelector']);
            selectedRouteId = parseInt(routeSelect.data('route-selected'), 10);
            routeSelect.change(function () {
                var presenter = root.find(opts['routePresenter']);
                if (routesList[routeSelect.val()]) {
                    var route = routesList[routeSelect.val()];
                    presenter.empty();
                    var inspired = '';
                    if (route.t === 1) {
                        inspired = '<p class="text-danger">' + opts['inspiredWarningText'] + '</p>';
                    }
                    presenter.html('<table class="table table-hover">' +
                        '<tbody>' +
                        '	<tr>' +
                        '		<td width="30%">' + opts['areaText'] + '</td>' +
                        '		<td>' + route.area.name + '</td>' +
                        '	</tr>' +
                        '	<tr>' +
                        '		<td width="30%">' + opts['beginningText'] + '</td>' +
                        '		<td>' + route.from + '</td>' +
                        '	</tr>' +
                        '	<tr>' +
                        '		<td width="30%">' + opts['endingText'] + '</td>' +
                        '		<td>' + route.to + '</td>' +
                        '	</tr>' +
                        '	<tr>' +
                        '		<td width="30%">' + opts['lengthText'] + '</td>' +
                        '		<td>' + route.length + ' km</td>' +
                        '	</tr>' +
                        '	<tr>' +
                        '		<td width="30%">' + opts['ascentText'] + '</td>' +
                        '		<td>' + route.ascent + ' m</td>' +
                        '	</tr>' +
                        '   <tr>' +
                        '		<td width="30%"></td>' +
                        '		<td><a href="http://www.edk.org.pl/trasa-edk/' + route.id + '/" target="_blank">' + opts['routeDetailsText'] + '</a></td>' +
                        '	</tr>' +
                        '</tbody>' +
                        '</table>' + inspired);
                    enableEverything(route.q);
                } else {
                    presenter.html('');
                    disableEverything();
                }
            });
            reloadAction();
        }

        function reloadAction() {
            $.ajax({
                url: opts['routeUrl'],
                dataType: "json",
                success: function (result) {
                    disableEverything();
                    renderSelectors(result);
                }
            });
        }

        function disableEverything() {
            root.find("input").prop("disabled", true);
            root.find("textarea").prop("disabled", true);
            root.find("select#where-learnt").prop("disabled", true);
        }

        function enableEverything(customAnswer) {
            root.find("input").prop("disabled", false);
            root.find("textarea").prop("disabled", false);
            root.find("select#where-learnt").prop("disabled", false);

            if (customAnswer !== '' && customAnswer !== null) {
                $("label[for='custom-answer']").text(customAnswer);
            } else {
                $("label[for='custom-answer']").text(opts['additionalInformationText']);
            }
        }

        function renderTerritoryOptions(select, territories) {
            var code = '<option value="">---</option>';
            $.each(territories, function (key, territory) {
                code += '<option value="' + territory.id + '">' + territory.name + '</option>';
            });
            select.empty()
                .append(code);
        }

        function renderRouteOptions(select, territories, selectedId) {
            var code = '<option value="">---</option>';
            $.each(territories, function (key, territory) {
                if (!selectedId || territory.id === selectedId) {
                    $.each(territory.areas, function (key, area) {
                        code += '<optgroup label="' + area.name + '" data-territory="' + territory.id + '">';
                        $.each(area.routes, function (key, route) {
                            route.area = area;
                            routesList[route.id] = route;
                            code += '<option value="' + route.id + '">' + route.name + '</option>';
                        });
                        code += '</optgroup>';
                    });
                }
            });
            select.empty()
                .append(code);
        }

        function renderSelectors(territories) {
            var routeFormGroup = routeSelect.parent();
            if (!territorySelect) {
                territorySelect = $('<select id="territory" class="form-control">');
                var territoryFormGroup = $('<div class="form-group">');
                territoryFormGroup.append(territorySelect);
                routeFormGroup.before(territoryFormGroup);
                territorySelect.on('change', function () {
                    var selectedTerritoryId = parseInt(territorySelect.val(), 10) || 0;
                    if (selectedTerritoryId > 0) {
                        renderRouteOptions(routeSelect, territories, selectedTerritoryId);
                        routeFormGroup.show();
                    } else {
                        routeFormGroup.hide();
                    }
                    routeSelect.val('')
                        .trigger('change');
                });
            }
            routeFormGroup.hide();

            renderTerritoryOptions(territorySelect, territories);
            renderRouteOptions(routeSelect, territories);

            if (selectedRouteId > 0) {
                routeSelect.val(selectedRouteId);
                selectedRouteId = 0;
                routeSelect.trigger('change');
                routeFormGroup.show();
                var selectedTerritoryId = routeSelect.find('option:selected')
                    .parent()
                    .data('territory');
                if (selectedTerritoryId > 0) {
                    territorySelect.val(selectedTerritoryId);
                    renderRouteOptions(routeSelect, territories, selectedTerritoryId);
                }
            }
        }
    };

    $.fn.registratr.defaults = {
        routeSelector: null,
        routePresenter: null,
        routeUrl: null,
        areaText: '',
        beginningText: '',
        endingText: '',
        lengthText: '',
        ascentText: '',
        estimatedParticipantNumText: '',
        participantNumText: '',
        inspiredWarningText: '',
        additionalInformationText: '',
        routeDetailsText: '',
        areaDetailsText: ''
    };
}(jQuery));
