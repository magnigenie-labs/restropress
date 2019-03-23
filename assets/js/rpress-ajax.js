var rpress_scripts;
jQuery(document).ready(function ($) {

	// Set Cookie
function rpress_setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + (exdays*24*60*60*1000));
  var expires = "expires="+d.toUTCString();
  document.cookie = cname + "=" + cvalue + "; " + expires + ";path=/";
}

// Get Cookie
function rpress_getCookie(cname) {
  var name = cname + "=";
  var ca = document.cookie.split(';');
  for(var i=0; i<ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0)==' ') c = c.substring(1);
    if (c.indexOf(name) != -1) return c.substring(name.length,c.length);
  }
  return "";
}

	$('input#rpress-allowed-hours').timepicker({
		startTime: rpress_scripts.open_hours,
		minTime: rpress_scripts.open_hours,
    maxTime: rpress_scripts.close_hours,
    defaultTime: rpress_scripts.open_hours,
    dropdown: true,
    scrollbar: true
	});


	// Hide unneeded elements. These are things that are required in case JS breaks or isn't present
	$('.rpress-no-js').hide();
	$('a.rpress-add-to-cart').addClass('rpress-has-js');

	// Send Remove from Cart requests
	$(document.body).on('click.rpressRemoveFromCart', '.rpress-remove-from-cart', function (event) {
		var $this  = $(this),
			item   = $this.data('cart-item'),
			action = $this.data('action'),
			id     = $this.data('fooditem-id'),
			data   = {
				action: action,
				cart_item: item
			};

		 $.ajax({
			type: "POST",
			data: data,
			dataType: "json",
			url: rpress_scripts.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function (response) {
				if (response.removed) {

					if ( ( parseInt( rpress_scripts.position_in_cart, 10 ) === parseInt( item, 10 ) ) || rpress_scripts.has_purchase_links ) {
						window.location = window.location;
						return false;
					}

					// Remove the selected cart item
					$('.rpress-cart').each( function() {
						$(this).find("[data-cart-item='" + item + "']").parent().remove();
					});

					//Reset the data-cart-item attributes to match their new values in the RPRESS session cart array
					$('.rpress-cart').each( function() {
						var cart_item_counter = 0;
						$(this).find("[data-cart-item]").each( function() {
							$(this).attr('data-cart-item', cart_item_counter);
							cart_item_counter = cart_item_counter + 1;
						});
					});


					// Check to see if the purchase form(s) for this fooditem is present on this page
					if( $( '[id^=rpress_purchase_' + id + ']' ).length ) {
						$( '[id^=rpress_purchase_' + id + '] .rpress_go_to_checkout' ).hide();
						$( '[id^=rpress_purchase_' + id + '] a.rpress-add-to-cart' ).show().removeAttr('data-rpress-loading');
						if ( rpress_scripts.quantities_enabled == '1' ) {
							$( '[id^=rpress_purchase_' + id + '] .rpress_fooditem_quantity_wrapper' ).show();
						}
					}

					$('span.rpress-cart-quantity').text( response.cart_quantity );
					$(document.body).trigger('rpress_quantity_updated', [ response.cart_quantity ]);
					if ( rpress_scripts.taxes_enabled ) {
						$('.cart_item.rpress_subtotal span').html( response.subtotal );
						$('.cart_item.rpress_cart_tax span').html( response.tax );
					}

					$('.cart_item.rpress_total span').html( response.total );

					if( response.cart_quantity == 0 ) {
						$('.cart_item.rpress_subtotal,.rpress-cart-number-of-items,.cart_item.rpress_checkout,.cart_item.rpress_cart_tax,.cart_item.rpress_total').hide();
						$('.rpress-cart').each( function() {

							var cart_wrapper = $(this).parent();
							if ( cart_wrapper ) {
								cart_wrapper.addClass('cart-empty')
								cart_wrapper.removeClass('cart-not-empty');
							}

							$(this).append('<li class="cart_item empty">' + rpress_scripts.empty_cart_message + '</li>');
						});
					}

					$(document.body).trigger('rpress_cart_item_removed', [ response ]);
				}
			}
		}).fail(function (response) {
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		}).done(function (response) {

		});

		return false;
	});

	//Check Local Storage Data
	function GetStorageDate() {
		var DeliveryMethod = rpress_getCookie('deliveryMethod');
		var DeliveryTime = rpress_getCookie('deliveryTime');

		if( DeliveryMethod == undefined || DeliveryMethod == '' ) 
			return false;
		else
			return true; 
	}

	var unavailableDates = rpress_scripts.rpress_holidays;

  function unavailable(date) {
		ymd = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();

    if( $.inArray(ymd, unavailableDates) !== -1 ) {
    	return { classes: 'holidays' };
    }
  }

	$('.rpress-add-to-cart').click(function(e) {
		var GetDeliveryData = GetStorageDate();
		$('#rpressModal').removeClass('rpress-delivery-options');
		$('#rpressModal').removeClass('rpress-food-options');

		if( ! GetDeliveryData ) {
			var action = 'rpress_show_delivery_options';
			var baseClass = 'rpress-delivery-options';
			var Title = 'Your Order Settings';

			if( rpress_scripts.check_delivery_fee_enabled == 1 ) {
				setTimeout(function(){initAutocomplete()},'2000');
			}
		}
		else {
			var action = 'rpress_show_products';
			var baseClass = 'rpress-food-options';
			var Title = $(this).attr('data-title');
		}
		
		e.preventDefault();
		var $this = $(this);
		var pid 	= $this.attr('data-fooditem-id');
		var price = $this.attr('data-price');
		var action = action;


		var data   = {
			action: action,
			fooditem_id: pid,
			fooditem_price: price,
		};

		$.fancybox.open({
			type     : 'html',
			afterShow : function(instance, current) {
				instance.showLoading( current );
			}
		});

		$.ajax({
			type: "POST",
			data: data,
			dataType: "json",
			url: rpress_scripts.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function(response) {

				$.fancybox.close(true);
				$('#rpressModal .modal-title').html(Title);
				$('#rpressModal .modal-body').html(response);

				$('#rpressModal').addClass(baseClass);

				if( pid !== '' && price !== '' ) {
					$('#rpressModal').find('.submit-fooditem-button').attr('data-item-id', pid); //setter
					$('#rpressModal').find('.submit-fooditem-button').attr('data-item-price', price);
				}
				$('#rpressModal').find('.submit-fooditem-button').attr('data-cart-action', 'add-cart');
				$('#rpressModal').find('.submit-fooditem-button').text(rpress_scripts.add_to_cart);
				
				$('#rpressModal').modal();
				
				// Date picker disable past dates
				var date = new Date();
				date.setDate(date.getDate());

				if( $('.rpress_get_delivery_dates').length ) {
					setTimeout(function(){initAutocomplete()},'1000');

					$('.rpress_get_delivery_dates').datepicker({
    				autoclose: true,
    				format: 'yyyy-mm-dd',
    				todayHighlight: true,
    				endDate : rpress_scripts.rpress_pre_order_until,
    				beforeShowDay: unavailable,
    				startDate: date
  				});

  				$(".rpress_get_delivery_dates").bind("changeDate", function(e) {
						var unavailableDates = rpress_scripts.rpress_holidays;
						var SelectedDate = $(this).val();

						if( $.inArray(SelectedDate, unavailableDates) !== -1 ) {
							//Show Holiday Message
							$(this).parents('.rpress-delivery-wrap').find('.rpress-errors-wrap').addClass('holiday');
							$(this).parents('.rpress-delivery-wrap').find('.rpress-errors-wrap').text(rpress_scripts.holiday_message);
							$(this).parents('.rpress-delivery-wrap').find('.rpress-errors-wrap').removeClass('disabled').addClass('enable');
							$(this).parents('#rpressModal').find('.delivery-settings-wrapper.active .rpress-time-wrap').hide();
    					//$(this).parents('.rpress-delivery-wrap').find('.rpress-store-closed-info').show();
    				}
    				else {
    					$(this).parents('.rpress-delivery-wrap').find('.rpress-errors-wrap').removeClass('holiday');
    					$(this).parents('#rpressModal').find('.delivery-settings-wrapper.active .rpress-time-wrap').show();
    					$(this).parents('.rpress-delivery-wrap').find('.rpress-errors-wrap').removeClass('enable').addClass('disabled');
							var d = new Date(SelectedDate);
							var dayName = d.getDay()
    					
    					if( dayName !== '' ) {
    						var getTimes = rpress_scripts.cutoff_hours[dayName];
    						var getOpenHrs = rpress_scripts.store_open_hours[dayName];
    						$('input#rpress-delivery-hours').val('');
    						$('input#rpress-delivery-hours').timepicker('remove');

	  						$('input#rpress-delivery-hours').timepicker({
									'minTime' :  getOpenHrs.open_time,
									'maxTime' :  getOpenHrs.close_time,
									'disableTimeRanges': [
											[getTimes.cutoff_starts, getTimes.cutoff_ends],
										]
								});

    						$('input#rpress-pickup-hours').val('');
    						$('input#rpress-pickup-hours').timepicker('remove');


    						$('input#rpress-pickup-hours').timepicker({
									'minTime' :  getOpenHrs.open_time,
									'maxTime' :  getOpenHrs.close_time,
								});
    					}
    				
    					//$(this).parents('.rpress-delivery-wrap').find('.rpress-store-closed-info').hide();
    				}
					});

				}

				else {
					$('.rpress-delivery-options input.rpress-hrs').timepicker({
						'scrollDefault': 'now',
						'minTime' :  rpress_scripts.open_hours,
						'maxTime' :  rpress_scripts.close_hours
					});
				}

				// Make the tab open
				if( $('.rpress-tabs-wrapper').length ) {
					$('#rpressdeliveryTab > li:first-child > a')[0].click();
				}
				
			}
		});
		return false;
	});


	//Hide delivery error when switch tabs
	$('body').on('click', '.rpress-delivery-options li.nav-item', function(e) {
		e.preventDefault();
		$(this).parents('.rpress-delivery-wrap').find('.rpress-order-time-error').addClass('hide');
	})

	$('body').on('click', '.rpress-delivery-opt-update', function(e) {
		e.preventDefault();
		var Selected = $(this);
		var DefaultText = $(this).text();
		var FoodItemId = $(this).attr('data-food-id');
		var DeliveryMethod = Selected.parents('.rpress-tabs-wrapper').find('.nav-item.active a').attr('data-delivery-type');
		var DeliveryTime = Selected.parents('.rpress-tabs-wrapper').find('.delivery-settings-wrapper.active .rpress-hrs').val();
		var DeliveryDates = Selected.parents('.rpress-tabs-wrapper').find('.delivery-settings-wrapper.active .rpress_get_delivery_dates').val();

		if( Selected.parents('.rpress-delivery-wrap').find('.rpress-errors-wrap').hasClass('holiday') ) {
			return false;
		}

		if( DeliveryDates == '' ) {
			Selected.parents('.rpress-delivery-wrap').find('.rpress-errors-wrap').text('Please select date for ' + DeliveryMethod);
			Selected.parents('.rpress-delivery-wrap').find('.rpress-errors-wrap').removeClass('disabled').addClass('enable');
			return false;
		}
		
		if( DeliveryTime == '' ) {
			Selected.parents('.rpress-delivery-wrap').find('.rpress-errors-wrap').text('Please select time for ' + DeliveryMethod);
			Selected.parents('.rpress-delivery-wrap').find('.rpress-errors-wrap').removeClass('disabled').addClass('enable');
			return false;
		}


		Selected.parents('.rpress-delivery-wrap').find('.rpress-errors-wrap').removeClass('enable').addClass('disabled');
		Selected.text(rpress_scripts.please_wait);

		rpress_setCookie('deliveryMethod', DeliveryMethod, 1);
		rpress_setCookie('deliveryTime', DeliveryTime, 1);
		rpress_setCookie('OrderDate', DeliveryDates, 1);

		if( FoodItemId ) {
			$('#rpressModal').modal('hide');
			$('.rpress-add-to-cart').each(function() {
				if( $(this).attr('data-fooditem-id') == FoodItemId ) {
					$(this).trigger('click');
				}
			});
		}
		else {
			var DeliveryMethod = rpress_getCookie('deliveryMethod');
      var DeliveryTime = rpress_getCookie('deliveryTime');

      if( DeliveryMethod !== '' &&  DeliveryTime !== '' ) {
      	$('.delivery-items-options').find('.delivery-opts').html('<span class="delMethod">' +DeliveryMethod+ '</span> <span class="delTime"> at '+DeliveryTime+ '</span>' ); 
      	$('#rpressModal').modal('hide');     
      }
		}
	});


	// Show the login form on the checkout page
	$('#rpress_checkout_form_wrap').on('click', '.rpress_checkout_register_login', function () {
		var $this = $(this),
			data = {
				action: $this.data('action')
			};
		// Show the ajax loader
		$('.rpress-cart-ajax').show();

		$.post(rpress_scripts.ajaxurl, data, function (checkout_response) {
			$('#rpress_checkout_login_register').html(rpress_scripts.loading);
			$('#rpress_checkout_login_register').html(checkout_response);
			// Hide the ajax loader
			$('.rpress-cart-ajax').hide();
		});
		return false;
	});

	// Process the login form via ajax
	$(document).on('click', '#rpress_purchase_form #rpress_login_fields input[type=submit]', function(e) {

		e.preventDefault();

		var complete_purchase_val = $(this).val();

		$(this).val(rpress_global_vars.purchase_loading);

		$(this).after('<span class="rpress-loading-ajax rpress-loading"></span>');

		var data = {
			action : 'rpress_process_checkout_login',
			rpress_ajax : 1,
			rpress_user_login : $('#rpress_login_fields #rpress_user_login').val(),
			rpress_user_pass : $('#rpress_login_fields #rpress_user_pass').val()
		};

		$.post(rpress_global_vars.ajaxurl, data, function(data) {

			if ( $.trim(data) == 'success' ) {
				$('.rpress_errors').remove();
				window.location = rpress_scripts.checkout_page;
			} else {
				$('#rpress_login_fields input[type=submit]').val(complete_purchase_val);
				$('.rpress-loading-ajax').remove();
				$('.rpress_errors').remove();
				$('#rpress-user-login-submit').before(data);
			}
		});

	});

	// Load the fields for the selected payment method
	$('select#rpress-gateway, input.rpress-gateway').change( function (e) {

		var payment_mode = $('#rpress-gateway option:selected, input.rpress-gateway:checked').val();

		if( payment_mode == '0' ) {
			return false;
		}

		rpress_load_gateway( payment_mode );

		return false;
	});

	// Auto load first payment gateway
	if( rpress_scripts.is_checkout == '1' ) {

		var chosen_gateway = false;
		var ajax_needed    = false;

		if ( $('select#rpress-gateway, input.rpress-gateway').length ) {
			chosen_gateway = $("meta[name='rpress-chosen-gateway']").attr('content');
			ajax_needed    = true;
		}

		if( ! chosen_gateway ) {
			chosen_gateway = rpress_scripts.default_gateway;
		}

		if ( ajax_needed ) {

			// If we need to ajax in a gateway form, send the requests for the POST.
			setTimeout( function() {
				rpress_load_gateway( chosen_gateway );
			}, 200);

		} else {

			// The form is already on page, just trigger that the gateway is loaded so further action can be taken.
			$('body').trigger('rpress_gateway_loaded', [ chosen_gateway ]);

		}
	}


	//Update delivery process
  $('body').on('click', '.delivery-change', function(e) {
    e.preventDefault();
    
    var action = 'rpress_show_delivery_options';
		var baseClass = 'fancybox-delivery-options';
		$('#rpressModal').removeClass('rpress-food-options');
		$('#rpressModal').removeClass('rpress-delivery-options');

		var data   = {
			action: action,
			changedate : 'yes',
		};

		$.fancybox.open({
			type     : 'html',
			afterShow : function(instance, current) {
				instance.showLoading( current );
			}
		});

		$.ajax({
			type: "POST",
			data: data,
			dataType: "json",
			url: rpress_scripts.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function(response) {

				$.fancybox.close(true);

				$('#rpressModal .modal-title').html('Update Order Settings');

				$('#rpressModal').addClass('rpress-delivery-options');


				$('#rpressModal .modal-body').html(response);
				$('#rpressModal').modal();

				var DeliveryMethod = rpress_getCookie('deliveryMethod');
				var DeliveryTime = rpress_getCookie('deliveryTime');
				var OrderDate = rpress_getCookie('OrderDate');

				if( DeliveryMethod !== '' || DeliveryMethod !== undefined ) {
					// $('.rpress-delivery-wrap').find('.rpress-'+DeliveryMethod+'-time-wrap' ).show();
					// $('.rpress-delivery-wrap').find('.rpress-'+ DeliveryMethod ).val(DeliveryTime);
					$('.rpress-delivery-wrap').find('.rpress-pickup').val(DeliveryTime);
					$('.rpress-delivery-wrap').find('.rpress-delivery').val(DeliveryTime);
					//$('#rpressModal').find('rpress-pickup-time-wrap.rpress-time-wrap').show();
					$('.rpress-delivery-wrap').find('.rpress-delivery-time-wrap' ).show();
					$('.rpress-delivery-wrap').find('.rpress-pickup-time-wrap' ).show();

				}

				if( OrderDate !== '' || OrderDate != undefined ) {
					$('.rpress-delivery-wrap').find('.rpress_get_delivery_dates').val(OrderDate);
				}

				var date = new Date();
				date.setDate(date.getDate());

				if( $('.rpress_get_delivery_dates').length ) {
					$('.rpress_get_delivery_dates').datepicker({
    				autoclose: true,
    				format: 'yyyy-mm-dd',
    				todayHighlight: true,
    				endDate : rpress_scripts.rpress_pre_order_until,
    				//beforeShowDay: unavailable,
    				startDate: date
  				});

  				var unavailableDates = rpress_scripts.rpress_holidays;

  				$(".rpress_get_delivery_dates").bind("changeDate", function(e) {
						
						var SelectedDate = $(this).val();

						if( $.inArray(SelectedDate, unavailableDates) !== -1 ) {
							//Show Holiday Message
							$(this).parents('.rpress-delivery-wrap').find('.rpress-errors-wrap').addClass('holiday');
							$(this).parents('.rpress-delivery-wrap').find('.rpress-errors-wrap').text(rpress_scripts.holiday_message);
							$(this).parents('.rpress-delivery-wrap').find('.rpress-errors-wrap').removeClass('disabled').addClass('enable');
							$(this).parents('#rpressModal').find('.delivery-settings-wrapper.active .rpress-time-wrap').hide();
    				}
    				else {
    					$(this).parents('.rpress-delivery-wrap').find('.rpress-errors-wrap').removeClass('holiday');
    					$(this).parents('#rpressModal').find('.delivery-settings-wrapper.active .rpress-time-wrap').show();
    					$(this).parents('.rpress-delivery-wrap').find('.rpress-errors-wrap').removeClass('enable').addClass('disabled');
							var d = new Date(SelectedDate);
							var dayName = d.getDay();
    					
    					if( dayName !== '' ) {
    						var getTimes = rpress_scripts.cutoff_hours[dayName];
    						var getOpenHrs = rpress_scripts.store_open_hours[dayName];
    						
    						$('input#rpress-delivery-hours').val('');
    						$('input#rpress-pickup-hours').val('');

    						$('input#rpress-delivery-hours').timepicker('remove');
    						$('input#rpress-pickup-hours').timepicker('remove');

    						$('input#rpress-delivery-hours').timepicker({
									'scrollDefault': 'now',
									'minTime' :  getOpenHrs.open_time,
									'maxTime' :  getOpenHrs.close_time,
									'disableTimeRanges': [
										[getTimes.cutoff_starts, getTimes.cutoff_ends],
										]
								});

    						$('input#rpress-pickup-hours').timepicker({
									'scrollDefault': 'now',
									'minTime' :  getOpenHrs.open_time,
									'maxTime' :  getOpenHrs.close_time,
								});
    					}
    				}
					});
				}

				else {
					$('.rpress-delivery-options input.rpress-hrs').timepicker({
						'scrollDefault': 'now',
						'minTime' :  rpress_scripts.open_hours,
						'maxTime' :  rpress_scripts.close_hours
					});
				}

				// Make the tab open
				if( $('.rpress-tabs-wrapper').length ) {
					$('.rpress-delivery-wrap').find('a#nav-'+ DeliveryMethod + '-tab').trigger('click');
				}
				
			}
		});
		return false;
  });

	// Process checkout
	$(document).on('click', '#rpress_purchase_form #rpress_purchase_submit [type=submit]', function(e) {

		var rpressPurchaseform = document.getElementById('rpress_purchase_form');

		if( typeof rpressPurchaseform.checkValidity === "function" && false === rpressPurchaseform.checkValidity() ) {
			return;
		}

		e.preventDefault();

		var complete_purchase_val = $(this).val();

		$(this).val(rpress_global_vars.purchase_loading);

		$(this).prop( 'disabled', true );

		$(this).after('<span class="rpress-loading-ajax rpress-loading"></span>');

		$.post(rpress_global_vars.ajaxurl, $('#rpress_purchase_form').serialize() + '&action=rpress_process_checkout&rpress_ajax=true', function(data) {
			if ( $.trim(data) == 'success' ) {
				$('.rpress_errors').remove();
				$('.rpress-error').hide();
				$(rpressPurchaseform).submit();
			} else {
				$('#rpress-purchase-button').val(complete_purchase_val);
				$('.rpress-loading-ajax').remove();
				$('.rpress_errors').remove();
				$('.rpress-error').hide();
				$( rpress_global_vars.checkout_error_anchor ).before(data);
				$('#rpress-purchase-button').prop( 'disabled', false );

				$(document.body).trigger( 'rpress_checkout_error', [ data ] );
			}
		});

	});

	// Update state field
	$(document.body).on('change', '#rpress_cc_address input.card_state, #rpress_cc_address select, #rpress_address_country', update_state_field);

	function update_state_field() {

		var $this = $(this);
		var $form;
		var is_checkout = typeof rpress_global_vars !== 'undefined';
		var field_name  = 'card_state';
		if ( $(this).attr('id') == 'rpress_address_country' ) {
			field_name = 'rpress_address_state';
		}

		if( 'card_state' != $this.attr('id') ) {

			// If the country field has changed, we need to update the state/province field
			var postData = {
				action: 'rpress_get_shop_states',
				country: $this.val(),
				field_name: field_name,
			};

			$.ajax({
				type: "POST",
				data: postData,
				url: rpress_scripts.ajaxurl,
				xhrFields: {
					withCredentials: true
				},
				success: function (response) {
					if ( is_checkout ) {
						$form = $("#rpress_purchase_form");
					} else {
						$form = $this.closest("form");
					}

					var state_inputs = 'input[name="card_state"], select[name="card_state"], input[name="rpress_address_state"], select[name="rpress_address_state"]';

					if( 'nostates' == $.trim(response) ) {
						var text_field = '<input type="text" name="card_state" class="card-state rpress-input required" value=""/>';
						$form.find(state_inputs).replaceWith( text_field );
					} else {
						$form.find(state_inputs).replaceWith( response );
					}

					if ( is_checkout ) {
						$(document.body).trigger('rpress_cart_billing_address_updated', [ response ]);
					}

				}
			}).fail(function (data) {
				if ( window.console && window.console.log ) {
					console.log( data );
				}
			}).done(function (data) {
				if ( is_checkout ) {
					recalculate_taxes();
				}
			});
		} else {
			if ( is_checkout ) {
				recalculate_taxes();
			}
		}

		return false;
	}

	// If is_checkout, recalculate sales tax on postalCode change.
	$(document.body).on('change', '#rpress_cc_address input[name=card_zip]', function () {
		if (typeof rpress_global_vars !== 'undefined') {
			recalculate_taxes();
		}
	});
});

var placeSearch, autocomplete;
    var componentForm = {
      street_number: 'short_name',
      route: 'long_name',
      locality: 'long_name',
      administrative_area_level_1: 'short_name',
      country: 'short_name',
      postal_code: 'short_name'
    };

    function initAutocomplete() {
    	//if( typeof google !== 'undefined' ) {
    		// Create the autocomplete object, restricting the search to geographical
      	// location types.
      	autocomplete = new google.maps.places.Autocomplete(

      	/** @type {!HTMLInputElement} */(document.getElementsByClassName("autocomplete")[0]),

      	{types: ['geocode']});


      	// When the user selects an address from the dropdown, populate the address
      	// fields in the form.
      	autocomplete.addListener('place_changed', fillInAddress);
    	//}
      
    }

    function fillInAddress() {
      // Get the place details from the autocomplete object.
      var place = autocomplete.getPlace();
      var LatLng = place.geometry.location.lat() + '-' + place.geometry.location.lng();
      jQuery('#rpress_geo_address').val(LatLng);

      // Get each component of the address from the place details
      // and fill the corresponding field on the form.
     
    }

    // Bias the autocomplete object to the user's geographical location,
    // as supplied by the browser's 'navigator.geolocation' object.
    function geolocate() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
          var geolocation = {
            lat: position.coords.latitude,
            lng: position.coords.longitude
          };

          var circle = new google.maps.Circle({
            center: geolocation,
            radius: position.coords.accuracy
          });
          autocomplete.setBounds(circle.getBounds());
        });
      }
    }

// Load a payment gateway
function rpress_load_gateway( payment_mode ) {

	// Show the ajax loader
	jQuery('.rpress-cart-ajax').show();
	jQuery('#rpress_purchase_form_wrap').html('<span class="rpress-loading-ajax rpress-loading"></span>');

	var url = rpress_scripts.ajaxurl;

	if ( url.indexOf( '?' ) > 0 ) {
		url = url + '&';
	} else {
		url = url + '?';
	}

	url = url + 'payment-mode=' + payment_mode;

	jQuery.post(url, { action: 'rpress_load_gateway', rpress_payment_mode: payment_mode },
		function(response){
			jQuery('#rpress_purchase_form_wrap').html(response);
			jQuery('.rpress-no-js').hide();
			jQuery('body').trigger('rpress_gateway_loaded', [ payment_mode ]);
		}
	);
}


