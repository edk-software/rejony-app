$(document).ready(function() {
	$('.faq-link').on('click', function(){
		$(this).next('a').children().toggle();
		$(this).closest('.box-header').toggleClass('faq-selected');
	});
	$('.faq-button').on('click', function(){
		$(this).children().toggle();
		$(this).closest('.box-header').toggleClass('faq-selected');
	});
});
