jQuery(function($){
	$('select.addon-items-list').chosen();

	$('select.addon-items-list').on('change', function(event, params) {
		if( event.type == 'change' ) {
			$('.rpress-order-payment-recalc-totals').show();
		}
		
	});


	$('input.rpress_timings').timepicker({
		dropdown: true,
    	scrollbar: true
	});
});