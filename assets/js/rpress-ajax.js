var rpress_scripts;
jQuery(document).ready(function ($) {

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



	$('.rpress-add-to-cart').click(function(e) {
		e.preventDefault();
		var $this = $(this);
		var pid 	= $this.attr('data-fooditem-id');
		var price = $this.attr('data-price');
		var action = 'rpress_show_product';

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
				$.fancybox.close();
				$.fancybox.open({
					'content'  : response,
					'type' : 'html',
					headers  : { 'X-fancyBox': true },
  				'width' : 600,
  				'height': 600,
  				'openEffect'  : 'fade',
				});
			}
		});
		return false;
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