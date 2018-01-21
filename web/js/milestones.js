
$(document).ready(function() {
	//Add Next tasks li
	//$('.milestones li').first().after("<li class='item'><h4>Następne zadania:</h4></li>");
	//Add current task text
	//$('#milestone_1>div>h5').first().append("</br><h6>Twoje obecne zadanie</h6>");
	//Add formating for ended milestones
	addEndedStyleing();
	//Add formating for overdue milestones
	addoverdueStyleing();
	//Show milestone body
	$('.milestone-header').click(function(){
		$(this).next('.milestone-body').slideToggle();
		$(this).children('span').children('i').toggle();
	});
	//Show-hide all milestones
	$('.milestones-show-button').click(function(){
		$('.milestones>ul>li:nth-child(n+6)').slideToggle();
		$(this).toggleClass('hide-all');
		$(this).toggleClass('show-all');
		return false;
	});
	//Show confirmation panel for milestone
	$('.milestones-confirm-button').click(function(){
		$(this).parent().hide();
		$(this).parent().next('div').fadeIn();
		return false;
	});
	//Cancel and show description panel
	$('.milestones-cancel-button').click(function(){
		$(this).closest('.milestone-body-confirm').hide();
		$(this).closest('.milestone-body-confirm').prev('div').fadeIn();
		return false;
	});
	//Update milestone status
	$('.milestones-status-button').click(function(){
		alert('update-action');
		$(this).toggleClass('hide-ended');
		$(this).toggleClass('show-ended');
	});
	//Show ended milestones
	$('.milestones-ended-button').click(function(){
		alert('relode view with all milestones');
	});
});

var addEndedStyleing = function(){
	var items = $('.milestone').filter(function(){
		return $(this).hasClass('milestone-ended');
	})
	items.find('.milestone-header').addClass('text-green');
	items.find('.milestones-confirm-button').hide();
	items.find('.product-title').prepend("<i class='fa fa-check' aria-hidden='true'></i> ");
}

var addoverdueStyleing = function(){
	var items = $('.milestone').filter(function(){
		return $(this).hasClass('milestone-overdue');
	})
	items.find('.milestone-header').addClass('text-red');
	items.find('.product-title').prepend("<i class='fa fa-exclamation' aria-hidden='true'></i> ");
}
