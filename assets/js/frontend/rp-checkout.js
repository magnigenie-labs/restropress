window.RPRESS_Checkout = (function ($) {
	'use strict';

	var $body,
		$form,
		$rpress_cart_amount,
		before_discount,
		$checkout_form_wrap;

	function init() {
		$body = $(document.body);
		$form = $("#rpress_purchase_form");
		$rpress_cart_amount = $('.rpress_cart_amount');
		before_discount = $rpress_cart_amount.text();
		$checkout_form_wrap = $('#rpress_checkout_form_wrap');

		$body.on('rpress_gateway_loaded', function (e) {
			rpress_format_card_number($form);
		});

		$body.on('keyup change', '.rpress-do-validate .card-number', function () {
			rpress_validate_card($(this));
		});

		$body.on('blur change', '.card-name', function () {
			var name_field = $(this);

			name_field.validateCreditCard(function (result) {
				if (result.card_type != null) {
					name_field.removeClass('valid')
						.addClass('error');
					$('#rpress-purchase-button')
						.attr('disabled', 'disabled');
				} else {
					name_field.removeClass('error')
						.addClass('valid');
					$('#rpress-purchase-button')
						.removeAttr('disabled');
				}
			});
		});

		// Make sure a gateway is selected
		$body.on('submit', '#rpress_payment_mode', function () {
			var gateway = $('#rpress-gateway option:selected')
				.val();
			if (gateway == 0) {
				alert(rpress_global_vars.no_gateway);
				return false;
			}
		});

		// Add a class to the currently selected gateway on click
		$body.on('click', '#rpress_payment_mode_select input', function () {
			$('#rpress_payment_mode_select label.rpress-gateway-option-selected')
				.removeClass('rpress-gateway-option-selected');
			$('#rpress_payment_mode_select input:checked')
				.parent()
				.addClass('rpress-gateway-option-selected');
		});

		// Validate and apply a discount
		$checkout_form_wrap.on('click', '.rpress-apply-discount', apply_discount);

		// Prevent the checkout form from submitting when hitting Enter in the discount field
		$checkout_form_wrap.on('keypress', '#rpress-discount', function (event) {
			if (event.keyCode == '13') {
				return false;
			}
		});

		// Apply the discount when hitting Enter in the discount field instead
		$checkout_form_wrap.on('keyup', '#rpress-discount', function (event) {
			if (event.keyCode == '13') {
				$checkout_form_wrap.find('.rpress-apply-discount')
					.trigger('click');
			}
		});

		// Remove a discount
		$body.on('click', '.rpress_discount_remove', remove_discount);

		// When discount link is clicked, hide the link, then show the discount input and set focus.
		$body.on('click', '.rpress_discount_link', function (e) {
			e.preventDefault();
			$('.rpress_discount_link')
				.parent()
				.hide();
			$('#rpress-discount-code-wrap')
				.show()
				.find('#rpress-discount')
				.focus();
		});

		// Hide / show discount fields for browsers without javascript enabled
		$body.find('#rpress-discount-code-wrap')
			.hide();
		$body.find('#rpress_show_discount')
			.show();

		$body.on('click', '.rpress-amazon-logout #Logout', function (e) {
			e.preventDefault();
			amazon.Login.logout();
			window.location = rpress_amazon.checkoutUri;
		});

	}

	function rpress_validate_card(field) {
		var card_field = field;
		card_field.validateCreditCard(function (result) {
			var $card_type = $('.card-type');

			if (result.card_type == null) {
				$card_type.removeClass()
					.addClass('off card-type');
				card_field.removeClass('valid');
				card_field.addClass('error');
			} else {
				$card_type.removeClass('off');
				$card_type.addClass(result.card_type.name);
				if (result.length_valid && result.luhn_valid) {
					card_field.addClass('valid');
					card_field.removeClass('error');
				} else {
					card_field.removeClass('valid');
					card_field.addClass('error');
				}
			}
		});
	}

	function rpress_format_card_number(form) {
		var card_number = form.find('.card-number'),
			card_cvc = form.find('.card-cvc'),
			card_expiry = form.find('.card-expiry');

		if (card_number.length && 'function' === typeof card_number.payment) {
			card_number.payment('formatCardNumber');
			card_cvc.payment('formatCardCVC');
			card_expiry.payment('formatCardExpiry');
		}
	}

	function apply_discount(event) {
		event.preventDefault();

		var $this = $(this),
			discount_code = $('#rpress-discount')
				.val(),
			rpress_discount_loader = $('#rpress-discount-loader');

		if (discount_code == '' || discount_code == rpress_global_vars.enter_discount) {
			return false;
		}

		var postData = {
			action: 'rpress_apply_discount',
			code: discount_code,
			form: $('#rpress_purchase_form')
				.serialize()
		};

		$('#rpress-discount-error-wrap')
			.html('')
			.hide();
		rpress_discount_loader.show();

		$.ajax({
			type: "POST",
			data: postData,
			dataType: "json",
			url: rpress_global_vars.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			beforeSend: () => {
				$('.rpress-discount-code-field-wrap')
					.append('<span class="rp-loading"></span>');
			},
			complete: () => {
				$('.rp-loading')
					.remove();
			},
			success: function (discount_response) {
				if (discount_response) {
					if (discount_response.msg == 'valid') {
						$('.rpress_cart_discount')
							.html(discount_response.html);
						$('.rpress_cart_discount_row')
							.show();

						$('.rpress_cart_amount')
							.each(function () {
								// Format discounted amount for display.
								$(this)
									.text(discount_response.total);
								// Set data attribute to new (unformatted) discounted amount.'
								$(this)
									.data('total', discount_response.total_plain);
							});

						$('#rpress-discount', $checkout_form_wrap)
							.val('');

						recalculate_taxes();

						var inputs = $('#rpress_cc_fields .rpress-input, #rpress_cc_fields .rpress-select,#rpress_cc_address .rpress-input, #rpress_cc_address .rpress-select,#rpress_payment_mode_select .rpress-input, #rpress_payment_mode_select .rpress-select');

						if ('0.00' == discount_response.total_plain) {

							$('#rpress_cc_fields,#rpress_cc_address,#rpress_payment_mode_select')
								.slideUp();
							inputs.removeAttr('required');
							$('input[name="rpress-gateway"]')
								.val('manual');

						} else {

							if (!inputs.is('.card-address-2')) {
								inputs.attr('required', 'required');
							}
							$('#rpress_cc_fields,#rpress_cc_address')
								.slideDown();

						}

						$body.trigger('rpress_discount_applied', [discount_response]);

					} else {
						$('#rpress-discount-error-wrap')
							.html('<span class="rpress_error">' + discount_response.msg + '</span>');
						$('#rpress-discount-error-wrap')
							.show();
						$body.trigger('rpress_discount_invalid', [discount_response]);
					}
				} else {
					if (window.console && window.console.log) {
						console.log(discount_response);
					}
					$body.trigger('rpress_discount_failed', [discount_response]);
				}
				rpress_discount_loader.hide();
			}
		})
			.fail(function (data) {
				if (window.console && window.console.log) {
					console.log(data);
				}
			});

		return false;
	};

	function remove_discount(event) {

		var $this = $(this),
			postData = {
				action: 'rpress_remove_discount',
				code: $this.data('code')
			};

		$.ajax({
			type: "POST",
			data: postData,
			dataType: "json",
			url: rpress_global_vars.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function (discount_response) {

				var zero = '0' + rpress_global_vars.decimal_separator + '00';

				$('.rpress_cart_amount')
					.each(function () {
						if (rpress_global_vars.currency_sign + zero == $(this)
							.text() || zero + rpress_global_vars.currency_sign == $(this)
								.text()) {
							// We're removing a 100% discount code so we need to force the payment gateway to reload
							window.location.reload();
						}

						// Format discounted amount for display.
						$(this)
							.text(discount_response.total);
						// Set data attribute to new (unformatted) discounted amount.'
						$(this)
							.data('total', discount_response.total_plain);
					});

				$('.rpress_cart_discount')
					.html(discount_response.html);

				if (!discount_response.discounts) {

					$('.rpress_cart_discount_row')
						.hide();

				}

				recalculate_taxes();

				$('#rpress_cc_fields,#rpress_cc_address')
					.slideDown();

				$body.trigger('rpress_discount_removed', [discount_response]);

			}
		})
			.fail(function (data) {
				if (window.console && window.console.log) {
					console.log(data);
				}
			});

		return false;
	}

	// Expose some functions or variables to window.RPRESS_Checkout object
	return {
		'init': init,
		'recalculate_taxes': recalculate_taxes
	}

})(window.jQuery);

// init on document.ready
window.jQuery(document)
	.ready(RPRESS_Checkout.init);

var ajax_tax_count = 0;

function recalculate_taxes(state) {

	if ('1' != rpress_global_vars.taxes_enabled)
		return; // Taxes not enabled

	var $rpress_cc_address = jQuery('#rpress_cc_address');

	if (!state) {
		state = $rpress_cc_address.find('#card_state')
			.val();
	}

	var postData = {
		action: 'rpress_recalculate_taxes',
		billing_country: $rpress_cc_address.find('#billing_country')
			.val(),
		state: state,
		card_zip: $rpress_cc_address.find('input[name=card_zip]')
			.val()
	};

	var current_ajax_count = ++ajax_tax_count;
	jQuery.ajax({
		type: "POST",
		data: postData,
		dataType: "json",
		url: rpress_global_vars.ajaxurl,
		xhrFields: {
			withCredentials: true
		},
		success: function (tax_response) {
			// Only update tax info if this response is the most recent ajax call.
			// Avoids bug with form autocomplete firing multiple ajax calls at the same time and not
			// being able to predict the call response order.
			if (current_ajax_count === ajax_tax_count) {
				jQuery('#rpress_checkout_cart_wrap')
					.html($(tax_response.html)
						.find('#rpress_checkout_cart_wrap')
						.html());
				jQuery('.rpress_cart_amount')
					.html(tax_response.total);
				var tax_data = new Object();
				tax_data.postdata = postData;
				tax_data.response = tax_response;
				jQuery('body')
					.trigger('rpress_taxes_recalculated', [tax_data]);
			}
		}
	})
		.fail(function (data) {
			if (window.console && window.console.log) {
				if (current_ajax_count === ajax_tax_count) {
					jQuery('body')
						.trigger('rpress_taxes_recalculated', [tax_data]);
				}
			}
		});
}