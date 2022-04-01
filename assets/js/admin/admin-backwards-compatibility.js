/**
 * Developer Notice: The contents of this JavaScript file are not to be relied on in any future versions of RPRESS
 */
jQuery(document)
	.ready(function ($) {

		// Adjust location of setting labels for settings in the new containers created below (back compat)
		$(document.body)
			.find('.rpress-custom-price-option-sections .rpress-legacy-setting-label')
			.each(function () {
				$(this)
					.prependTo($(this)
						.nextAll('span:not(:has(>.rpress-legacy-setting-label))')
						.first());
			});

		// Build HTML containers for existing price option settings (back compat)
		$(document.body)
			.find('.rpress-custom-price-option-sections')
			.each(function () {
				$(this)
					.find('[class*="purchase_limit"]')
					.wrapAll('<div class="rpress-purchase-limit-price-option-settings-legacy rpress-custom-price-option-section"></div>');
				$(this)
					.find('[class*="shipping"]')
					.wrapAll('<div class="rpress-simple-shipping-price-option-settings-legacy rpress-custom-price-option-section" style="display: none;"></div>');
				$(this)
					.find('[class*="sl-"]')
					.wrapAll('<div class="rpress-sl-price-option-settings-legacy rpress-custom-price-option-section"></div>');
				$(this)
					.find('[class*="rpress-recurring-"]')
					.wrapAll('<div class="rpress-recurring-price-option-settings-legacy rpress-custom-price-option-section"></div>');
			});

		// only display Simple Shipping/Software Licensing sections if enabled (back compat)
		$(document.body)
			.find('#rpress_enable_shipping', '#rpress_license_enabled')
			.each(function () {
				var variable_pricing = $('#rpress_variable_pricing')
					.is(':checked');
				var ss_checked = $('#rpress_enable_shipping')
					.is(':checked');
				var ss_section = $('.rpress-simple-shipping-price-option-settings-legacy');
				var sl_checked = $('#rpress_license_enabled')
					.is(':checked');
				var sl_section = $('.rpress-sl-price-option-settings-legacy');
				if (variable_pricing) {
					if (ss_checked) {
						ss_section.show();
					} else {
						ss_section.hide();
					}
					if (sl_checked) {
						sl_section.show();
					} else {
						sl_section.hide();
					}
				}
			});
		$('#rpress_enable_shipping')
			.on('change', function () {
				var enabled = $(this)
					.is(':checked');
				var section = $('.rpress-simple-shipping-price-option-settings-legacy');
				if (enabled) {
					section.show();
				} else {
					section.hide();
				}
			});
		$('#rpress_license_enabled')
			.on('change', function () {
				var enabled = $(this)
					.is(':checked');
				var section = $('.rpress-sl-price-option-settings-legacy');
				if (enabled) {
					section.show();
				} else {
					section.hide();
				}
			});

		// Create section titles for newly created HTML containers (back compat)
		$(document.body)
			.find('.rpress-purchase-limit-price-option-settings-legacy')
			.each(function () {
				$(this)
					.prepend('<span class="rpress-custom-price-option-section-title">' + rpress_backcompat_vars.purchase_limit_settings + '</span>');
			});
		$(document.body)
			.find('.rpress-simple-shipping-price-option-settings-legacy')
			.each(function () {
				$(this)
					.prepend('<span class="rpress-custom-price-option-section-title">' + rpress_backcompat_vars.simple_shipping_settings + '</span>');
			});
		$(document.body)
			.find('.rpress-sl-price-option-settings-legacy')
			.each(function () {
				$(this)
					.prepend('<span class="rpress-custom-price-option-section-title">' + rpress_backcompat_vars.software_licensing_settings + '</span>');
			});
		$(document.body)
			.find('.rpress-recurring-price-option-settings-legacy')
			.each(function () {
				$(this)
					.prepend('<span class="rpress-custom-price-option-section-title">' + rpress_backcompat_vars.recurring_payments_settings + '</span>');
			});

	});