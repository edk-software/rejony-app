(function($) {
	$.fn.registratr = function(options) {
		var opts = $.extend( {}, $.fn.registratr.defaults, options );
		var root = $(this);
		var routesList = [];
		var routeSelect;
		var territorySelect;
		var selectedRouteId = 0;
		
		if (null !== opts['routeSelector'] && null !== opts['routeUrl']) {
			routeSelect = root.find(opts['routeSelector']);
			selectedRouteId = parseInt(routeSelect.data('route-selected'), 10);
			routeSelect.change(function() {
				var presenter = root.find(opts['routePresenter']);
				if (routesList[routeSelect.val()]) {
					var route = routesList[routeSelect.val()];
					presenter.empty();
					var inspired = '';
					if (route.t == 1) {
						inspired = '<p class="text-danger">'+opts['inspiredWarningText']+'</p>';
					}
					presenter.html('<table class="table table-hover">'+
					'<tbody>'+
					'	<tr>'+
					'		<td width="30%">'+opts['areaText']+'</td>'+
					'		<td>'+route.area.name+'</td>'+
					'	</tr>'+
					'	<tr>'+
					'		<td width="30%">'+opts['beginningText']+'</td>'+
					'		<td>'+route.from+'</td>'+
					'	</tr>'+
					'	<tr>'+
					'		<td width="30%">'+opts['endingText']+'</td>'+
					'		<td>'+route.to+'</td>'+
					'	</tr>'+
					'	<tr>'+
					'		<td width="30%">'+opts['lengthText']+'</td>'+
					'		<td>'+route.length+' km</td>'+
					'	</tr>'+
					'	<tr>'+
					'		<td width="30%">'+opts['ascentText']+'</td>'+
					'		<td>'+route.ascent+' m</td>'+
					'	</tr>'+
					'	<tr>'+
					'		<td width="30%">'+opts['estimatedParticipantNumText']+'</td>'+
					'		<td>'+route.pl+'</td>'+
					'	</tr>'+
					'	<tr>'+
					'		<td width="30%">'+opts['participantNumText']+'</td>'+
					'		<td>'+route.pn+'</td>'+
					'	</tr>'+
					( opts['maxPeoplePerRecordText'] ? 
					'	<tr>'+
					'		<td width="30%">'+opts['maxPeoplePerRecordText']+'</td>'+
					'		<td>'+route.ppr+'</td>'+
					'	</tr>'
					: '')+
					'</tbody>'+
					'</table>'+inspired);
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
					renderSelector(result);
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

			if(customAnswer != "" && null !== customAnswer) {
				$("label[for='custom-answer']").text(customAnswer);
			} else {
				$("label[for='custom-answer']").text(opts['additionalInformationText']);
			}
		}

		function selectOptGroup(territoryId) {
			var optgroups = routeSelect.find('optgroup');
			optgroups.hide();
			optgroups.filter('[data-territory="' + territoryId + '"]')
				.show();
		}

		function renderSelector(territories) {
			var routeFormGroup = routeSelect.parent();
			if (!territorySelect) {
				territorySelect = $('<select id="territory" class="form-control">');
				var territoryFormGroup = $('<div class="form-group">');
				territoryFormGroup.append(territorySelect);
				routeFormGroup.before(territoryFormGroup);
				territorySelect.on('change', function () {
					var selectedTerritoryId = territorySelect.val();
					if (selectedTerritoryId !== '') {
						selectOptGroup(selectedTerritoryId);
						routeFormGroup.show();
					} else {
						routeFormGroup.hide();
					}
					routeSelect.val('')
						.trigger('change');
				});
			}
			routeFormGroup.hide();

			var territoryCode = '<option value="">---</option>';
			var routeCode = '<option value="">---</option>';
			$.each(territories, function (key, territory) {
				territoryCode += '<option value="' + territory.id + '">' + territory.name + '</option>';
				$.each(territory.areas, function (key, area) {
					routeCode += '<optgroup label="' + area.name + '" data-territory="' + territory.id + '">';
					$.each(area.routes, function (key, route) {
						route.area = area;
						routesList[route.id] = route;
						routeCode += '<option value="' + route.id + '">' + route.name + '</option>';
					});
					routeCode += '</optgroup>';
				});
			});
			territorySelect.empty();
			territorySelect.append(territoryCode);
			routeSelect.empty();
			routeSelect.append(routeCode);

			if (selectedRouteId > 0) {
				routeSelect.val(selectedRouteId);
				selectedRouteId = 0;
				routeSelect.trigger('change');
				routeFormGroup.show();
				var selectedTerritoryId = routeSelect.find('option:selected')
					.parent()
					.data('territory');
				if (selectedTerritoryId > 0) {
					selectOptGroup(selectedTerritoryId);
					territorySelect.val(selectedTerritoryId);
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
		additionalInformationText: ''
	};
}(jQuery));
