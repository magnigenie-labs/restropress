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

	

	//Validate License
	$('body').on('click', '.rpress-validate-license', function(e) {
		e.preventDefault();
		var ButtonText = $(this).text();
		var Button = $(this);
		var Selected = $(this).parent('.rpress-license-wrapper').find('.rpress-license-field')
		var ItemId = Selected.attr('data-item-id');
		var ProductName = Selected.attr('data-item-name');
		var License = Selected.val();
		var LicenseString = $(this).parent('.rpress-license-wrapper').find('.rpress_license_string').val();
		var action = $(this).attr('data-action');
		
		if( License.length ) {
			Button.addClass('disabled');
			Button.text('Please Wait');

			data = {
				action       : action,
				item_id      : ItemId,
				product_name : ProductName,
				license 		 : License,
				license_key  : LicenseString,
			};

			$.ajax({
				type: "POST",
				data : data,
				dataType: "json",
				url: rpress_admin_vars.ajaxurl,
				xhrFields: {
					withCredentials: true
				},
				success: function (response) {
					if( response.status !== 'error' ){
						
						$.toast({
    					heading: 'Success',
    					text: 'Congrats, your license successfully activated!',
    					showHideTransition: 'slide',
    					icon: 'success',
    					position: { top: '36px', right: '0px' },
    					stack: false
						});

						Button.parent('.rpress-license-wrapper').addClass('rpress-updated');
						Button.parents('.rpress-purchased-wrap').find('.rpress-license-deactivate-wrapper').removeClass('hide').addClass('show');
					}
					else {
						$.toast({
    					heading: 'Error',
    					text: 'Invalid License Key',
    					showHideTransition: 'slide',
    					icon: 'error',
    					position: { top: '36px', right: '0px' },
    					stack: false
						})
					}
					Button.text('Activate License');
					Button.removeClass('disabled');
					
				}
			})
		}
	});

	//Deactivate License
	$('body').on('click', '.rpress-deactivate-license', function(e) {
		e.preventDefault();
		var Selected = $(this);
		var action = $(this).attr('data-action');
		var Licensestring = $(this).parents('.rpress-purchased-wrap').find('.rpress_license_string').val();
		var ProductName = $(this).parents('.rpress-purchased-wrap').find('.rpress-license-field').attr('data-item-name');

		Selected.addClass('disabled');
		Selected.text('Please Wait');

		if( Licensestring.length ) {
			data = {
				action       : action,
				product_name : ProductName,
				license_key  : Licensestring,
			};

			$.ajax({
				type: "POST",
				data : data,
				dataType: "json",
				url: rpress_admin_vars.ajaxurl,
				xhrFields: {
					withCredentials: true
				},
				success: function (response) {
					$.toast({
						heading: 'Information',
						text: 'Your license has been deactivated',
						showHideTransition: 'slide',
						icon: 'info',
						position: { top: '36px', right: '0px' },
						stack: false
					});

					if( response.status !== 'error' ){
						Selected.parents('.rpress-purchased-wrap').find('.rpress-license-wrapper').removeClass('rpress-updated');
						Selected.parents('.rpress-purchased-wrap').find('.rpress-license-deactivate-wrapper').removeClass('show').addClass('hide');
					}
					Selected.text('Deactivate License');
					Selected.removeClass('disabled');
					
				}
			})
		}
	});

	

});