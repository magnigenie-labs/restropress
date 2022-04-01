var rpress_scripts;

jQuery(document)
  .ready(function ($) {

    // Hide un-necessary elements. These are things that are required in case JS breaks or isn't present
    $('.rpress-no-js')
      .hide();

    //Hide delivery error when switch tabs
    $('body')
      .on('click', '.rpress-delivery-options li.nav-item', function (e) {
        e.preventDefault();
        $(this)
          .parents('.rpress-delivery-wrap')
          .find('.rpress-order-time-error')
          .addClass('hide');
      });

    // Show the login form on the checkout page
    $('#rpress_checkout_form_wrap')
      .on('click', '.rpress_checkout_register_login', function () {

        var $this = $(this);
        $this.addClass('rp-loading')
        $this.find('.rp-ajax-toggle-text')
          .addClass('rp-text-visibility')

        payment_form = $('#rpress_payment_mode_select_wrap,#rpress_purchase_form_wrap');
        ajax_loader = '<span class="rpress-loading-ajax rpress-loading"></span>';
        data = {
          action: $this.data('action')
        };
        payment_form.hide();

        // Show the ajax loader
        //$this.html($this.html() + ajax_loader);

        $.post(rpress_scripts.ajaxurl, data, function (checkout_response) {

          $('#rpress_checkout_login_register')
            .html(rpress_scripts.loading);
          $('#rpress_checkout_login_register')
            .html(checkout_response);

          // Hide the ajax loader
          $('.rpress-cart-ajax')
            .hide();
          $this.removeClass('rp-loading')
          $this.find('.rp-ajax-toggle-text')
            .removeClass('rp-text-visibility')
          //Show the payment form
          if (data.action == 'rpress_checkout_register')
            payment_form.show();
        });
        return false;
      });

    // Process the login form via ajax
    $(document)
      .on('click', '#rpress_purchase_form #rpress_login_fields input[type=submit]', function (e) {

        e.preventDefault();

        var complete_purchase_val = $(this)
          .val();

        $(this)
          .val(rpress_global_vars.purchase_loading);

        $(this)
          .after('<span class="rp-loading"></span>');

        var data = {
          action: 'rpress_process_checkout_login',
          rpress_ajax: 1,
          rpress_user_login: $('#rpress_login_fields #rpress_user_login')
            .val(),
          rpress_user_pass: $('#rpress_login_fields #rpress_user_pass')
            .val()
        };

        $.post(rpress_global_vars.ajaxurl, data, function (data) {

          if ($.trim(data) == 'success') {
            $('.rpress_errors')
              .remove();
            window.location = rpress_scripts.checkout_page;
          } else {
            $('#rpress_login_fields input[type=submit]')
              .val(complete_purchase_val);
            $('.rp-loading')
              .remove();
            $('.rpress_errors')
              .remove();
            $('#rpress-user-login-submit')
              .before(data);
          }
        });

      });

    // Load the fields for the $this payment method
    $('select#rpress-gateway, input.rpress-gateway')
      .change(function (e) {

        var payment_mode = $('#rpress-gateway option:selected, input.rpress-gateway:checked')
          .val();

        if (payment_mode == '0') {
          return false;
        }

        rpress_load_gateway(payment_mode);

        return false;
      });

    // Auto load first payment gateway
    if (rpress_scripts.is_checkout == '1') {

      var chosen_gateway = false;
      var ajax_needed = false;

      if ($('select#rpress-gateway, input.rpress-gateway')
        .length) {
        chosen_gateway = $("meta[name='rpress-chosen-gateway']")
          .attr('content');
        ajax_needed = true;
      }

      if (!chosen_gateway) {
        chosen_gateway = rpress_scripts.default_gateway;
      }

      if (ajax_needed) {

        // If we need to ajax in a gateway form, send the requests for the POST.
        setTimeout(function () {
          rpress_load_gateway(chosen_gateway);
        }, 200);

      } else {

        // The form is already on page, just trigger that the gateway is loaded so further action can be taken.
        $('body')
          .trigger('rpress_gateway_loaded', [chosen_gateway]);

      }
    }

    // Process checkout
    $(document)
      .on('click', '#rpress_purchase_form #rpress_purchase_submit [type=submit]', function (e) {

        var rpressPurchaseform = document.getElementById('rpress_purchase_form');

        if (typeof rpressPurchaseform.checkValidity === "function" && false === rpressPurchaseform.checkValidity()) {
          return;
        }

        e.preventDefault();

        var complete_purchase_val = $(this)
          .val();

        $(this)
          .val(rpress_global_vars.purchase_loading);

        $(this)
          .prop('disabled', true);

        $(this)
          .after('<span class="rp-loading"></span>');

        $.post(rpress_global_vars.ajaxurl, $('#rpress_purchase_form')
          .serialize() + '&action=rpress_process_checkout&rpress_ajax=true',
          function (data) {

            if ($.trim(data) == 'success') {
              $('.rpress_errors')
                .remove();
              $('.rpress-error')
                .hide();
              $(rpressPurchaseform)
                .submit();
            } else {
              $('#rpress-purchase-button')
                .val(complete_purchase_val);
              $('.rp-loading')
                .remove();
              $('.rpress_errors')
                .remove();
              $('.rpress-error')
                .hide();
              $(rpress_global_vars.checkout_error_anchor)
                .before(data);
              $('#rpress-purchase-button')
                .prop('disabled', false);

              $(document.body)
                .trigger('rpress_checkout_error', [data]);
            }
          });

      });

    // Update state field
    $(document.body)
      .on('change', '#rpress_cc_address input.card_state, #rpress_cc_address select, #rpress_address_country', update_state_field);

    function update_state_field() {

      var $this = $(this);
      var $form;
      var is_checkout = typeof rpress_global_vars !== 'undefined';
      var field_name = 'card_state';

      if ($(this)
        .attr('id') == 'rpress_address_country') {
        field_name = 'rpress_address_state';
      }

      if ('card_state' != $this.attr('id')) {

        // If the country field has changed, we need to update the state/province field
        var postData = {
          action: 'rpress_get_states',
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
            // if (is_checkout) {
            //   $form = $("#rpress_purchase_form");
            // }
            // else {
            //   $form = $this.closest("form");
            // }

            // var state_inputs = 'input[name="card_state"], select[name="card_state"], input[name="rpress_address_state"], select[name="rpress_address_state"]';

            // if ('nostates' == $.trim(response)) {
            //   var text_field = '<input type="text" name="card_state" class="card-state rpress-input required" value=""/>';
            //   $form.find(state_inputs).replaceWith(text_field);
            // }
            // else {
            //   $form.find(state_inputs).replaceWith(response);
            // }

            // if (is_checkout) {
            //   $(document.body).trigger('rpress_cart_billing_address_updated', [response]);
            // }

          }
        })
          .fail(function (data) {
            if (window.console && window.console.log) {
              console.log(data);
            }
          })
          .done(function (data) {
            if (is_checkout) {
              recalculate_taxes();
            }
          });
      } else {
        if (is_checkout) {
          recalculate_taxes();
        }
      }

      return false;
    }

    // If is_checkout, recalculate sales tax on postalCode change.
    $(document.body)
      .on('change', '#rpress_cc_address input[name=card_zip]', function () {
        if (typeof rpress_global_vars !== 'undefined') {
          recalculate_taxes();
        }
      });

    $("#rpressModal")
      .on('hide.bs.modal', function () {
        $('.modal-backdrop.in')
          .remove();
      });

  });


// Load a payment gateway
function rpress_load_gateway(payment_mode) {

  // Show the ajax loader
  jQuery('.rpress-cart-ajax')
    .show();
  jQuery('#rpress_purchase_form_wrap')
    .html('<span class="rpress-loading-ajax rpress-loading"></span>');

  var url = rpress_scripts.ajaxurl;

  if (url.indexOf('?') > 0) {
    url = url + '&';
  } else {
    url = url + '?';
  }

  url = url + 'payment-mode=' + payment_mode;

  jQuery.post(url, {
    action: 'rpress_load_gateway',
    rpress_payment_mode: payment_mode
  },
    function (response) {
      jQuery('#rpress_purchase_form_wrap')
        .html(response);
      jQuery('.rpress-no-js')
        .hide();
      jQuery('body')
        .trigger('rpress_gateway_loaded', [payment_mode]);
    });
}