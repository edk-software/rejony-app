(function($) {
	$.fn.registratr = function(options) {
		var opts = $.extend( {}, $.fn.registratr.defaults, options );
		var root = $(this);
		var routes = new Array();
		var routeElem;
		var selectedRouteId = 0;
		
		if (null !== opts['routeSelector'] && null !== opts['routeUrl']) {
			routeElem = root.find(opts['routeSelector']);
			selectedRouteId = parseInt(routeElem.data('route-selected'), 10);
			routeElem.change(function() {
				var presenter = root.find(opts['routePresenter']);
				if (routes[routeElem.val()]) {
					route = routes[routeElem.val()];
					presenter.empty();
					var inspired = '';
					if (route.t == 1) {
						inspired = '<p class="text-danger">'+opts['inspiredWarningText']+'</p>';
					}
					presenter.html('<table class="table table-hover">'+
					'<tbody>'+
					'	<tr>'+
					'		<td width="30%">'+opts['areaText']+'</td>'+
					'		<td>'+route.area+'</td>'+
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
		
		function renderSelector(result) {
			var code = '<option vaule="">---</option>';
			for (i in result) {
				code += '<optgroup label="'+result[i]['name']+'" class="wv">';
				for (j in result[i]['areas']) {
					code += '<optgroup label="'+result[i]['areas'][j]['name']+'">';
					for(k in result[i]['areas'][j]['routes']) {
						var route = result[i]['areas'][j]['routes'][k];
						route['area'] = result[i]['areas'][j]['name'];
						routes[route['id']] = route;
						code += '<option value="'+route['id']+'">'+route['name']+'</option>';
					}
					code += '</optgroup>';
				}				
				code += '</optgroup>';
			}
			routeElem.empty();
			routeElem.append(code);

			if (selectedRouteId > 0) {
				routeElem.val(selectedRouteId);
				routeElem.trigger('change');
				selectedRouteId = 0;
			}

			data = result;
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
