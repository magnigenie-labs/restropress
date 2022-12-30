/* Get RestroPress Cookie */
function rp_getCookie(cname) {
  var name = cname + "=";
  var ca = document.cookie.split(';');
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') c = c.substring(1);
    if (c.indexOf(name) != -1) return c.substring(name.length, c.length);
  }
  return "";
}
function remove_show_service_options() {
	jQuery('#rpressModal')
            .removeClass('show-service-options');
}

/* Set default addons */
function rp_checked_default_subaddon() {
  if(jQuery('#fooditem-details .rp-addons-data-wrapper .food-item-list.active').length > 0 ) {
      jQuery('#fooditem-details .rp-addons-data-wrapper .food-item-list.active').each(function() {
          var element = jQuery(this).find('input');
            if (element.hasClass('checked')) {
                jQuery(this).find('input').prop('checked', true);
            }
      });
  }
}

/* Set RestroPress Cookie */
function rp_setCookie(cname, cvalue, ex_time) {
  var d = new Date();
  d.setTime(d.getTime() + (ex_time * 60 * 1000));
  var expires = "expires=" + d.toUTCString();
  document.cookie = cname + "=" + cvalue + "; " + expires + ";path=/";
}

/* Get RestroPress Storage Data */
function rp_get_storage_data() {

  var serviceType = rp_getCookie('service_type');
  var serviceTime = rp_getCookie('service_time');

  if (typeof serviceType == undefined || serviceType == '') {
    return false;
  } else {
    return true;
  }
}

/* Display Dynamic Addon Price Based on Selected Variation */
function show_dymanic_pricing(container, ele) {
  var price_key = ele.val();
  if (price_key !== 'undefined') {
    jQuery('#' + container + ' .rp-addons-data-wrapper .food-item-list')
      .removeClass('active');
    jQuery('#' + container + ' .rp-addons-data-wrapper .food-item-list.list_' + price_key)
      .addClass('active');
  }
}

/* Calculate Live Price On Click */
function update_modal_live_price(fooditem_container) {

  //Add changes code

  var single_price = parseFloat(jQuery('#rpressModal .cart-item-price')
    .attr('data-price'));
  var quantity = parseInt(jQuery('input[name=quantity]')
    .val());

  /* Act on the variations */
  jQuery('#' + fooditem_container + ' .rp-variable-price-wrapper .food-item-list')
    .each(function () {

      var element = jQuery(this)
        .find('input');

      if (element.is(':checked')) {

        // Dynamic addon Price
        show_dymanic_pricing(fooditem_container, element);

        var attrs = element.attr('data-value');
        var attrs_arr = attrs.split('|');
        var price = attrs_arr[2];

        single_price = parseFloat(price);
      }
    });

  /* Act on the addons */
  jQuery('#' + fooditem_container + ' .rp-addons-data-wrapper .food-item-list.active')
    .each(function () {

      var element = jQuery(this)
        .find('input');

      if (element.is(':checked')) {

        var attrs = element.val();
        var attrs_arr = attrs.split('|');
        var price = attrs_arr[2];

        if (price != '') {
          single_price = parseFloat(single_price) + parseFloat(price);
        }
      }
    });

  /* Updating as per current quantity */
  total_price = single_price * quantity;

  /* Update the price in Submit Button */

  if (rp_scripts.decimal_separator == ',') {
    total_price_v = total_price.toFixed(2)
      .split('.')
      .join(',');
  } else {
    total_price_v = total_price.toFixed(2);
  }
  jQuery('#rpressModal .cart-item-price')
    .html(rp_scripts.currency_sign + total_price_v);
  jQuery('#rpressModal .cart-item-price')
    .attr('data-current', single_price.toFixed(2));

}

/* RestroPress Frontend Functions */
jQuery(function ($) {

  // Show order details on popup
  $(document)
    .on('click', '.rpress-view-order-btn', function (e) {

      e.preventDefault();

      var self = $(this);
      var action = 'rpress_show_order_details';
      var order_id = self.attr('data-order-id');

      var data = {
        action: action,
        order_id: order_id,
        security: rp_scripts.order_details_nonce
      };

      $('#rpressModal')
        .addClass('show-order-details');

      $.ajax({
        type: "POST",
        data: data,
        dataType: "json",
        url: rp_scripts.ajaxurl,
        beforeSend: (jqXHR, object) => {
          self.addClass('rp-loading');
          self.find('.rp-ajax-toggle-text')
            .addClass('rp-text-visibility');
        },
        complete: (jqXHR, object) => {
          self.removeClass('rp-loading');
          self.find('.rp-ajax-toggle-text')
            .removeClass('rp-text-visibility');
        },
        success: function (response) {
          $('#rpressModal .modal__container')
            .html(response.data.html);
          MicroModal.show('rpressModal');
        }
      })
    });

  // Sticky category menu on mobile
  $(window)
    .resize(function () {
      if ($(window)
        .width() > 769) {
        $('.sticky-sidebar.cat-lists')
          .removeClass('rp-mobile-cat');
      } else {
        $('.sticky-sidebar.cat-lists')
          .addClass('rp-mobile-cat');
      }
    })
    .resize();

  // Hide Mneu on Click Float Cart
  $(document)
    .on('click', '.rpress-category-item', function () {
      $('.rp-mb-cat-ft-btn')
        .click();
    });

  // Hide Mneu on Click Items
  $(document)
    .on('click', '.rpress-mobile-cart-icons', function () {
      $('.rp-cat-overlay')
        .click();
    });

  // RP Hide overlay
  $(document)
    .on('click', '.rp-cat-overlay', function () {
      $('.rp-mb-cat-ft-btn')
        .click();
    });
  // Toggel mobile category menu
  $('.rp-mb-cat-ft-btn')
    .on('click', function () {
      $('.rp-mb-cat-ft-btn')
        .toggleClass('rp-close-menu');
      $('.sticky-sidebar.cat-lists')
        .toggleClass('rp-hide');
      $('body')
        .toggleClass('rp-cat-no-scroll');
      if ($('.sticky-sidebar.cat-lists')
        .hasClass('rp-hide'))
        $('body')
          .append('<div class="rp-cat-overlay"></div>');
      else
        $('.rp-cat-overlay')
          .remove();
    });

  $('.rp-mb-cat-ft-btn')
    .click(function () {
      $('.rp-mobile-cat')
        .toggle('fast');

      if ($(this)
        .hasClass('rp-close-menu')) {
        $('.rp-mb-cat-txt')
          .html(`<i class="fa fa-cutlery" aria-hidden="true"></i> ${rpress_scripts.close}`);
      } else {
        $('.rp-mb-cat-txt')
          .html(`<i class="fa fa-cutlery" aria-hidden="true"></i> ${rpress_scripts.menu}`);
      }
    });

  //Remove loading from modal
  $('#rpressModal')
    .removeClass('loading');

  //Remove service options from modal
  $('#rpressModal')
    .removeClass('show-service-options');
  $('#rpressModal')
    .removeClass('minimum-order-notice');

  $('#rpressModal')
    .on('hidden.bs.modal', function () {
      $('#rpressModal')
        .removeClass('show-service-options');
      $('#rpressModal')
        .removeClass('minimum-order-notice');
    });

  var ServiceType = rp_scripts.service_options;

  if (ServiceType == 'delivery_and_pickup') {
    ServiceType = 'delivery';
  }

  // Add to Cart
  $('.rpress-add-to-cart')
    .click(function (e) {

      e.preventDefault();
      var rp_get_delivery_data = rp_get_storage_data();
      $('#rpressModal')
        .removeClass('rpress-delivery-options rpress-food-options checkout-error');
      $('#rpressModal .qty')
        .val('1');
      $('#rpressModal')
        .find('.cart-action-text')
        .html(rp_scripts.add_to_cart);

      if (!rp_get_delivery_data) {
        var action = 'rpress_show_delivery_options';
        var security = rp_scripts.service_type_nonce;
        $('#rpressModal')
          .addClass('show-service-options');
      } else {
      	$('#rpressModal')
          .removeClass('show-service-options');
        var action = 'rpress_show_products';
        var security = rp_scripts.show_products_nonce;
      }

      var _self = $(this);
      var fooditem_id = _self.attr('data-fooditem-id');
      var foodItemName = _self.attr('data-title');
      var price = _self.attr('data-price');
      var variable_price = _self.attr('data-variable-price');

      var data = {
        action: action,
        fooditem_id: fooditem_id,
        security: security,
      };

      $.ajax({
        type: "POST",
        data: data,
        dataType: "json",
        url: rp_scripts.ajaxurl,
        beforeSend: (jqXHR, status) => {
          _self.addClass('rp-loading');
          _self.find('.rp-ajax-toggle-text')
            .addClass('rp-text-visibility');
        },
        xhrFields: {
          withCredentials: true
        },
        complete: (jqXHR, object) => {
          _self.removeClass('rp-loading');
          _self.find('.rp-ajax-toggle-text')
            .removeClass('rp-text-visibility');
          MicroModal.show('rpressModal');
        },
        success: function (response) {

          $('#rpressModal')
            .removeClass('loading');
          
          $('#rpressModal .modal-title')
            .html(response.data.html_title);
          $('#rpressModal .modal-body')
            .html(response.data.html);
          $('#rpressModal .cart-item-price')
            .html(response.data.price);
          $('#rpressModal .cart-item-price')
            .attr('data-price', response.data.price_raw);

          if ($('.rpress-tabs-wrapper')
            .length) {
            $('#rpressdeliveryTab > li:first-child > a')[0].click();
          }

          // Trigger event so themes can refresh other areas.
          $(document.body)
            .trigger('opened_service_options', [response.data]);

          $('#rpressModal')
            .find('.submit-fooditem-button')
            .attr('data-cart-action', 'add-cart');
          $('#rpressModal')
            .find('.cart-action-text')
            .html(rp_scripts.add_to_cart);

          if (fooditem_id !== '' && price !== '') {
            $('#rpressModal')
              .find('.submit-fooditem-button')
              .attr('data-item-id', fooditem_id);
            $('#rpressModal')
              .find('.submit-fooditem-button')
              .attr('data-title', foodItemName);
            $('#rpressModal')
              .find('.submit-fooditem-button')
              .attr('data-item-price', price);
            $('#rpressModal')
              .find('.submit-fooditem-button')
              .attr('data-item-qty', 1);
          }

          update_modal_live_price('fooditem-details');
          rp_checked_default_subaddon();

        }

      });
    });

  // Update Cart
  $('.rpress-sidebar-cart')
    .on('click', 'a.rpress-edit-from-cart', function (e) {
      e.preventDefault();


      var _self = $(this);
      _self.parents('.rpress-cart-item')
        .addClass('edited');

      var CartItemId = _self.attr('data-remove-item');
      var FoodItemId = _self.attr('data-item-id');
      var FoodItemName = _self.attr('data-item-name');
      var FoodQuantity = _self.parents('.rpress-cart-item')
        .find('.rpress-cart-item-qty')
        .text();
      var action = 'rpress_edit_cart_fooditem';
      var security = rp_scripts.edit_cart_fooditem_nonce;

      var data = {
        action: action,
        cartitem_id: CartItemId,
        fooditem_id: FoodItemId,
        fooditem_name: FoodItemName,
        security: security,
      };

      if (CartItemId !== '') {

        $.ajax({
          type: "POST",
          data: data,
          dataType: "json",
          url: rp_scripts.ajaxurl,
          beforeSend: (jqXHR, status) => {
            _self.addClass('rp-loading');
            _self.find('.rp-ajax-toggle-text')
              .addClass('rp-text-visibility');
          },
          complete: (jqXHR, object) => {
            _self.removeClass('rp-loading');
            _self.find('.rp-ajax-toggle-text')
              .removeClass('rp-text-visibility');
          },
          xhrFields: {
            withCredentials: true
          },
          success: function (response) {
            MicroModal.show('rpressModal');
            $('#rpressModal')
              .removeClass('checkout-error');
            $('#rpressModal')
              .removeClass('show-service-options');
            $('#rpressModal')
              .removeClass('loading');
            $('#rpressModal .modal-title')
              .html(response.data.html_title);

            $('#rpressModal')
              .find(".qty")
              .val(FoodQuantity);
            $('#rpressModal')
              .find('.submit-fooditem-button')
              .attr('data-item-id', FoodItemId);
            $('#rpressModal')
              .find('.submit-fooditem-button')
              .attr('data-title', FoodItemName);
            $('#rpressModal')
              .find('.submit-fooditem-button')
              .attr('data-cart-key', CartItemId);
            $('#rpressModal')
              .find('.submit-fooditem-button')
              .attr('data-cart-action', 'update-cart');
            $('#rpressModal')
              .find('.submit-fooditem-button')
              .find('.cart-action-text')
              .html(rp_scripts.update_cart);
            $('#rpressModal')
              .find('.submit-fooditem-button')
              .find('.cart-item-price')
              .html(response.data.price);
            $('#rpressModal')
              .find('.submit-fooditem-button')
              .find('.cart-item-price')
              .attr('data-price', response.data.price_raw);
            $('#rpressModal')
              .find('.submit-fooditem-button')
              .attr('data-item-qty', FoodQuantity);
            $('#rpressModal .modal-body')
              .html(response.data.html);

            update_modal_live_price('fooditem-update-details');
          }
        });
      }
    });

  // Add to Cart / Update Cart Button From Popup
  $(document)
    .on('click', '.submit-fooditem-button', function (e) {

      e.preventDefault();

      var self = $(this);
      var cartAction = self.attr('data-cart-action');
      var text = self.find('span.cart-action-text')
        .text();
      var validation = '';

      // Checking the Required & Max addon settings for Addons
      if (jQuery('.addons-wrapper')
        .length > 0) {

        jQuery('.addons-wrapper')
          .each(function (index, el) {

            var _self = jQuery(this);
            var addon = _self.attr('data-id');
            var is_required = _self.children('input.addon_is_required')
              .val();
            var max_addons = _self.children('input.addon_max_limit')
              .val();
            var checked = _self.find('.food-item-list.active input:checked')
              .length;

            _self.find('.rp-addon-error')
              .removeClass('rp-addon-error');
            if (is_required == 'yes' && checked == 0) {
              _self.find('.rp-addon-required')
                .addClass('rp-addon-error');
              validation = 1;
            } else if (max_addons != 0 && checked > max_addons) {
              _self.find('.rp-max-addon')
                .addClass('rp-addon-error');
              validation = 1;
            }

            if (validation != '') {
              self.removeClass('disable_click');
              self.find('.cart-action-text')
                .text(text);
              return false;
            }
          });
      }

      if (cartAction == 'add-cart' && validation == '') {

        self.addClass('disable_click');

        var this_form = self.parents('.modal')
          .find('form#fooditem-details .food-item-list.active input');
        var itemId = self.attr('data-item-id');
        var itemName = self.attr('data-title');
        var itemQty = self.attr('data-item-qty');
        var FormData = this_form.serializeArray();
        var SpecialInstruction = self.parents('.modal')
          .find('textarea.special-instructions')
          .val();
        var action = 'rpress_add_to_cart';

        var data = {
          action: action,
          fooditem_id: itemId,
          fooditem_qty: itemQty,
          special_instruction: SpecialInstruction,
          post_data: FormData,
          security: rp_scripts.add_to_cart_nonce
        };

        if (itemId !== '') {
          $.ajax({
            type: "POST",
            data: data,
            dataType: "json",
            url: rp_scripts.ajaxurl,
            xhrFields: {
              withCredentials: true
            },
            beforeSend: (jqXHR, object) => {
              self.addClass('rp-loading');
              self.find('.rp-ajax-toggle-text')
                .addClass('rp-text-visibility')
            },
            complete: (jqXHR, object) => {
              self.removeClass('rp-loading');
              self.find('.rp-ajax-toggle-text')
                .removeClass('rp-text-visibility')
            },
            success: function (response) {
              if (response) {
                $('.rpress-mobile-cart-icons')
                  .css({
                    display: 'flex'
                  });

                self.removeClass('disable_click');
                self.find('.cart-action-text')
                  .text(text);

                var serviceType = rp_getCookie('service_type');
                var serviceTime = rp_getCookie('service_time');
                var serviceTimeText = rp_getCookie('service_time_text');
                var serviceDate = rp_getCookie('delivery_date');

                $('ul.rpress-cart')
                  .find('li.cart_item.empty')
                  .remove();
                $('ul.rpress-cart')
                  .find('li.cart_item.rpress_subtotal')
                  .remove();
                $('ul.rpress-cart')
                  .find('li.cart_item.cart-sub-total')
                  .remove();
                $('ul.rpress-cart')
                  .find('li.cart_item.rpress_cart_tax')
                  .remove();
                $('ul.rpress-cart')
                  .find('li.cart_item.rpress-cart-meta.rpress-delivery-fee')
                  .remove();
                $('ul.rpress-cart')
                  .find('li.cart_item.rpress-cart-meta.rpress_subtotal')
                  .remove();

                $(response.cart_item)
                  .insertBefore('ul.rpress-cart li.cart_item.rpress_total');

                if ($('.rpress-cart')
                  .find('.rpress-cart-meta.rpress_subtotal')
                  .is(':first-child')) {
                  $(this)
                    .hide();
                }

                $('.rpress-cart-quantity')
                  .show()
                  .html(`${response.cart_quantity}<span></>`);
                $('.rp-mb-price')
                  .text(response.total);
                $('.cart_item.rpress-cart-meta.rpress_total')
                  .find('.cart-total')
                  .text(response.total);
                $('.cart_item.rpress-cart-meta.rpress_subtotal')
                  .find('.subtotal')
                  .text(response.total);
                $('.cart_item.rpress-cart-meta.rpress_total')
                  .css('display', 'block');
                $('.cart_item.rpress-cart-meta.rpress_subtotal')
                  .css('display', 'block');
                $('.cart_item.rpress_checkout')
                  .addClass(rp_scripts.button_color);
                $('.cart_item.rpress_checkout')
                  .css('display', 'block');

                if (serviceType !== undefined) {
                  serviceLabel = window.localStorage.getItem('serviceLabel');
                  var orderInfo = '<span class="delMethod">' + serviceLabel + ', ' + serviceDate + '</span>';

                  if (serviceTime !== undefined) {
                    orderInfo += '<span class="delTime">, ' + serviceTimeText + '</span>';
                  }

                  $('.delivery-items-options')
                    .find('.delivery-opts')
                    .html(orderInfo);

                  if ($('.delivery-wrap .delivery-change')
                    .length == 0) {
                    $("<a href='#' class='delivery-change'>" + rp_scripts.change_txt + "</a>")
                      .insertAfter(".delivery-opts");
                  }
                }

                $('.delivery-items-options')
                  .css('display', 'block');

                var subTotal = '<li class="cart_item rpress-cart-meta rpress_subtotal">' + rp_scripts.total_text + '<span class="cart-subtotal">' + response.subtotal + '</span></li>';
                if (response.subtotal) {
                  var cartLastChild = $('ul.rpress-cart>li.rpress-cart-item:last');
                  $(subTotal)
                    .insertAfter(cartLastChild);
                }
                if (response.taxes) {
                  var taxHtml = '<li class="cart_item rpress-cart-meta rpress_cart_tax">' + rp_scripts.estimated_tax + '<span class="cart-tax">' + response.taxes + '</span></li>';
                  $(taxHtml)
                    .insertBefore('ul.rpress-cart li.cart_item.rpress_total');
                }

                if (response.taxes === undefined) {
                  $('ul.rpress-cart')
                    .find('.cart_item.rpress-cart-meta.rpress_subtotal')
                    .remove();
                  var cartLastChild = $('ul.rpress-cart>li.rpress-cart-item:last');
                  $(subTotal)
                    .insertAfter(cartLastChild);
                }

                $(document.body)
                  .trigger('rpress_added_to_cart', [response]);
                $('ul.rpress-cart')
                  .find('.cart-total')
                  .html(response.total);
                $('ul.rpress-cart')
                  .find('.cart-subtotal')
                  .html(response.subtotal);

                if ($('li.rpress-cart-item')
                  .length > 0) {
                  $('a.rpress-clear-cart')
                    .show();
                } else {
                  $('a.rpress-clear-cart')
                    .hide();
                }

                $(document.body)
                  .trigger('rpress_added_to_cart', [response]);
                MicroModal.close('rpressModal');
                tata.success(window.rp_scripts.success, self.attr('data-title') + window.rp_scripts.added_to_cart, {
                  position: "tr"
                })
              }
            }
          })
        }
      }

      if (cartAction == 'update-cart' && validation == '') {

        self.addClass('disable_click');

        var this_form = self.parents('.modal')
          .find('form#fooditem-update-details .food-item-list.active input');
        var itemId = self.attr('data-item-id');
        var itemPrice = self.attr('data-item-price');
        var cartKey = self.attr('data-cart-key');
        var itemQty = self.attr('data-item-qty');
        var FormData = this_form.serializeArray();
        var SpecialInstruction = self.parents('.modal')
          .find('textarea.special-instructions')
          .val();
        var action = 'rpress_update_cart_items';

        var data = {
          action: action,
          fooditem_id: itemId,
          fooditem_qty: itemQty,
          fooditem_cartkey: cartKey,
          special_instruction: SpecialInstruction,
          post_data: FormData,
          security: rp_scripts.update_cart_item_nonce
        };

        if (itemId !== '') {

          $.ajax({
            type: "POST",
            data: data,
            dataType: "json",
            url: rp_scripts.ajaxurl,
            xhrFields: {
              withCredentials: true
            },
            success: function (response) {

              self.removeClass('disable_click');
              self.find('.cart-action-text')
                .text(text);

              if (response) {

                html = response.cart_item;

                $('ul.rpress-cart')
                  .find('li.cart_item.empty')
                  .remove();

                $('.rpress-cart >li.rpress-cart-item')
                  .each(function (index, item) {
                    $(this)
                      .find("[data-cart-item]")
                      .attr('data-cart-item', index);
                    $(this)
                      .attr('data-cart-key', index);
                    $(this)
                      .attr('data-remove-item', index);
                  });

                $('ul.rpress-cart')
                  .find('li.edited')
                  .replaceWith(function () {

                    let obj = $(html);
                    obj.attr('data-cart-key', response.cart_key);

                    obj.find("a.rpress-edit-from-cart")
                      .attr("data-cart-item", response.cart_key);
                    obj.find("a.rpress-edit-from-cart")
                      .attr("data-remove-item", response.cart_key);

                    obj.find("a.rpress_remove_from_cart")
                      .attr("data-cart-item", response.cart_key);
                    obj.find("a.rpress_remove_from_cart")
                      .attr("data-remove-item", response.cart_key);

                    return obj;
                  });

                $('ul.rpress-cart')
                  .find('.cart-total')
                  .html(response.total);
                $('ul.rpress-cart')
                  .find('.cart-subtotal')
                  .html(response.subtotal);
                $('ul.rpress-cart')
                  .find('.cart-tax')
                  .html(response.tax);

                $(document.body)
                  .trigger('rpress_items_updated', [response]);
                MicroModal.close('rpressModal');
              }
            }
          });
        }
      }
    });

  // Add Service Date and Time
  $('body')
    .on('click', '.rpress-delivery-opt-update', function (e) {
      e.preventDefault();

      var _self = $(this);
      var foodItemId = _self.attr('data-food-id');

      if ($('.rpress-tabs-wrapper')
        .find('.nav-item.active a')
        .length > 0) {
        var serviceType = $('.rpress-tabs-wrapper')
          .find('.nav-item.active a')
          .attr('data-service-type');
        var serviceLabel = $('.rpress-tabs-wrapper')
          .find('.nav-item.active a')
          .text()
          .trim();
        //Store the service label for later use
        window.localStorage.setItem('serviceLabel', serviceLabel);
      }

      var serviceTime = _self.parents('.rpress-tabs-wrapper')
        .find('.delivery-settings-wrapper.active .rpress-hrs')
        .val();
      var serviceTimeText = _self.parents('.rpress-tabs-wrapper')
        .find('.delivery-settings-wrapper.active .rpress-hrs option:selected')
        .text();
      var serviceDate = _self.parents('.rpress-tabs-wrapper')
        .find('.delivery-settings-wrapper.active .rpress_get_delivery_dates')
        .val();

      if (serviceTime === undefined && (rpress_scripts.pickup_time_enabled == 1 && serviceType == 'pickup' || rpress_scripts.delivery_time_enabled == 1 && serviceType == 'delivery')) {
        tata.error(rp_scripts.error, select_time_error + serviceLabel);
        return false;
      }


      var sDate = serviceDate === undefined ? rpress_scripts.current_date : serviceDate;

      var action = 'rpress_check_service_slot';
      var data = {
        action: action,
        serviceType: serviceType,
        serviceTime: serviceTime,
        service_date: sDate,

      };

      $.ajax({
        type: "POST",
        data: data,
        dataType: "json",
        url: rpress_scripts.ajaxurl,
        xhrFields: {
          withCredentials: true
        },
        beforeSend: (jqXHR, status) => {
          _self.addClass('rp-loading');
          _self.find('.rp-ajax-toggle-text')
            .addClass('rp-text-visibility');
        },
        complete: (jqXHR, oject) => {
          _self.removeClass('rp-loading');
          _self.find('.rp-ajax-toggle-text')
            .removeClass('rp-text-visibility');
        },
        success: function (response) {
          _self.removeClass('rp-loading');
          _self.find('.rp-ajax-toggle-text')
            .removeClass('rp-text-visibility');

          if (response.status == 'error') {
            _self.text(rpress_scripts.update);
            tata.error(rp_scripts.error, response.msg);
            return false;
          } else {
            rp_setCookie('service_type', serviceType, rp_scripts.expire_cookie_time);
            if (serviceDate === undefined) {
              rp_setCookie('service_date', rpress_scripts.current_date, rp_scripts.expire_cookie_time);
              rp_setCookie('delivery_date', rpress_scripts.display_date, rp_scripts.expire_cookie_time);
            } else {
              var delivery_date = $('.delivery-settings-wrapper.active .rpress_get_delivery_dates option:selected')
                .text();
              rp_setCookie('service_date', serviceDate, rp_scripts.expire_cookie_time);
              rp_setCookie('delivery_date', delivery_date, rp_scripts.expire_cookie_time);
            }

            if (serviceTime === undefined) {
              rp_setCookie('service_time', '', rp_scripts.expire_cookie_time);
            } else {
              rp_setCookie('service_time', serviceTime, rp_scripts.expire_cookie_time);
              rp_setCookie('service_time_text', serviceTimeText, rp_scripts.expire_cookie_time);
            }

            $('#rpressModal')
              .removeClass('show-service-options');

            if (foodItemId) {

              $('#rpressModal')
                .addClass('loading');
              $('#rpress_fooditem_' + foodItemId)
                .find('.rpress-add-to-cart')
                .trigger('click');
              MicroModal.close('rpressModal');

            } else {

              MicroModal.close('rpressModal');

              if (typeof serviceType !== 'undefined' && typeof serviceTime !== 'undefined') {

                $('.delivery-wrap .delivery-opts')
                  .html('<span class="delMethod">' + serviceLabel + ',</span> <span class="delTime"> ' + Cookies.get('delivery_date') + ', ' + serviceTimeText + '</span>');

              } else if (typeof serviceTime == 'undefined') {

                $('.delivery-items-options')
                  .find('.delivery-opts')
                  .html('<span class="delMethod">' + serviceLabel + ',</span> <span class="delTime"> ' + Cookies.get('delivery_date') + '</span>');
              }
            }
      
            //Trigger checked slot event so that it can be used by theme/plugins
            $(document.body)
              .trigger('rpress_checked_slots', [response]);

            //If it's checkout page then refresh the page to reflect the updated changes.
            if (rpress_scripts.is_checkout == '1')
              window.location.reload();
          }
        }

      });
    });

  // Update Service Date and Time
  $(document)
    .on('click', '.delivery-change', function (e) {

      e.preventDefault();

      var self = $(this);
      var action = 'rpress_show_delivery_options';
      var ServiceType = rp_getCookie('service_type');
      var ServiceTime = rp_getCookie('service_time');
      var text = self.text();

      var data = {
        action: action,
        security: rp_scripts.service_type_nonce
      }

      $('#rpressModal')
        .addClass('show-service-options');

      $.ajax({
        type: "POST",
        data: data,
        dataType: "json",
        url: rp_scripts.ajaxurl,
        beforeSend: (jqXHR, obj) => {
          self.addClass('rp-loading');
          self.find('.rp-ajax-toggle-text')
            .addClass('rp-text-visibility')
        },
        complete: (jqXHR, obj) => {
          self.removeClass('rp-loading');
          self.find('.rp-ajax-toggle-text')
            .removeClass('rp-text-visibility')
        },
        success: function (response) {

          // self.text(text);
          $('#rpressModal .modal-title')
            .html(response.data.html_title);
          $('#rpressModal .modal-body')
            .html(response.data.html);
          MicroModal.show('rpressModal');

          if ($('.rpress-tabs-wrapper')
            .length) {

            if (ServiceTime !== '') {
              $('.rpress-delivery-wrap')
                .find('select#rpress-' + ServiceType + '-hours')
                .val(ServiceTime);
            }

            $('.rpress-delivery-wrap')
              .find('a#nav-' + ServiceType + '-tab')
              .trigger('click');
          }

          // Trigger event so themes can refresh other areas.
          $(document.body)
            .trigger('opened_service_options', [response.data]);
        }
      })
    });

  // Remove Item from Cart
  $('.rpress-cart')
    .on('click', '.rpress-remove-from-cart', function (event) {

      if ($('.rpress-remove-from-cart')
        .length == 1 && !confirm(rp_scripts.confirm_empty_cart)) {
        return false;
      }

      var $this = $(this),
        item = $this.data('cart-item'),
        action = $this.data('action'),
        id = $this.data('fooditem-id'),
        security = rp_scripts.edit_cart_fooditem_nonce,
        data = {
          action: action,
          cart_item: item,
          security: security
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

            // Remove the $this cart item
            $('.rpress-cart .rpress-cart-item')
              .each(function () {
                $(this)
                  .find("[data-cart-item='" + item + "']")
                  .parents('.rpress-cart-item')
                  .remove();
              });

            // Check to see if the purchase form(s) for this fooditem is present on this page
            if ($('[id^=rpress_purchase_' + id + ']')
              .length) {
              $('[id^=rpress_purchase_' + id + '] .rpress_go_to_checkout')
                .hide();
              $('[id^=rpress_purchase_' + id + '] a.rpress-add-to-cart')
                .show()
                .removeAttr('data-rpress-loading');

              if (rpress_scripts.quantities_enabled == '1') {
                $('[id^=rpress_purchase_' + id + '] .rpress_fooditem_quantity_wrapper')
                  .show();
              }
            }

            $('span.rpress-cart-quantity')
              .html(`${response.cart_quantity}<span>${rpress_scripts.items}</span>`);
            $('.rp-mb-price')
              .text(response.total);
            $(document.body)
              .trigger('rpress_quantity_updated', [response.cart_quantity]);

            if (rpress_scripts.taxes_enabled) {
              $('.cart_item.rpress_subtotal span')
                .html(response.subtotal);
              $('.cart_item.rpress_cart_tax span')
                .html(response.tax);
            }

            $('.cart_item.rpress_total span.rpress-cart-quantity')
              .html(response.cart_quantity);
            $('.cart_item.rpress_total span.cart-total')
              .html(response.total);

            if (response.cart_quantity == 0) {

              $('li.rpress-cart-meta, .cart_item.rpress_subtotal, .rpress-cart-number-of-items, .cart_item.rpress_checkout, .cart_item.rpress_cart_tax, .cart_item.rpress_total')
                .hide();
              $('.rpress-cart')
                .each(function () {

                  var cart_wrapper = $(this)
                    .parent();

                  if (cart_wrapper) {
                    cart_wrapper.addClass('cart-empty')
                    cart_wrapper.removeClass('cart-not-empty');
                  }

                  $(this)
                    .append('<li class="cart_item empty">' + rpress_scripts.empty_cart_message + '</li>');
                });
            }

            $(document.body)
              .trigger('rpress_cart_item_removed', [response]);

            $('ul.rpress-cart > li.rpress-cart-item')
              .each(function (index, item) {
                $(this)
                  .find("[data-cart-item]")
                  .attr('data-cart-item', index);
                $(this)
                  .find("[data-remove-item]")
                  .attr('data-remove-item', index);
                $(this)
                  .attr('data-cart-key', index);
              });

            // check if no item in cart left
            if ($('li.rpress-cart-item')
              .length == 0) {
              // $('a.rpress-clear-cart').trigger('click');
              $('li.rpress-cart-meta')
                .hide();
              $('li.delivery-items-options')
                .hide();
              $('a.rpress-clear-cart')
                .hide();
            }
          }
        }
      });

      return false;
    });

  // Clear All Fooditems from Cart
  $(document)
    .on('click', 'a.rpress-clear-cart', function (e) {

      e.preventDefault();

      if (confirm(rp_scripts.confirm_empty_cart)) {

        var self = $(this);
        var old_text = self.html();
        var action = 'rpress_clear_cart';
        var data = {
          security: rp_scripts.clear_cart_nonce,
          action: action
        }
        $.ajax({
          type: "POST",
          data: data,
          dataType: "json",
          url: rp_scripts.ajaxurl,
          xhrFields: {
            withCredentials: true
          },
          beforeSend: (jqXHR, object) => {
            self.addClass('rp-loading')
            self.find('.rp-ajax-toggle-text')
              .addClass('rp-text-visibility')

          },
          complete: (jqXHR, object) => {
            self.removeClass('rp-loading')
            self.find('.rp-ajax-toggle-text')
              .removeClass('rp-text-visibility')

          },
          success: function (response) {
            if (response.status == 'success') {
              $('span.rpress-cart-quantity')
                .html(`${0}<span> Items</span>`);
              $('.rp-mb-price')
                .text(`${rp_scripts.currency_sign}0.00`);

              $(document.body)
                .trigger('rpress_quantity_updated', [0]);
              $(".rpress-sidebar-main-wrap")
                .css("left", "100%");
              $('ul.rpress-cart')
                .find('li.cart_item.rpress_total')
                .css('display', 'none');
              $('ul.rpress-cart')
                .find('li.cart_item.rpress_checkout')
                .css('display', 'none');
              $('ul.rpress-cart')
                .find('li.rpress-cart-item')
                .remove();
              $('ul.rpress-cart')
                .find('li.cart_item.empty')
                .remove();
              $('ul.rpress-cart')
                .find('li.rpress_subtotal')
                .remove();
              $('ul.rpress-cart')
                .find('li.rpress_cart_tax')
                .remove();
              $('ul.rpress-cart')
                .find('li.rpress-delivery-fee')
                .remove();
              $('ul.rpress-cart')
                .append(response.response);
              $('.rpress-cart-number-of-items')
                .css('display', 'none');
              $('.delivery-items-options')
                .css('display', 'none');
              $('.rpress-mobile-cart-icons')
                .hide();
              self.hide();
              tata.success(window.rp_scripts.success, window.rp_scripts.success_empty_cart, {
                position: "tr"
              })
            }
          }
        });
      }
    });

  // Proceed to Checkout
  $(document)
    .on('click', '.cart_item.rpress_checkout a', function (e) {
      e.preventDefault();

      var CheckoutUrl = rp_scripts.checkout_page;
      var _self = $(this);
      var OrderText = _self.text();

      var action = 'rpress_proceed_checkout';
      var data = {
        action: action,
        security: rp_scripts.proceed_checkout_nonce,
      }

      $.ajax({
        type: "POST",
        data: data,
        dataType: "json",
        url: rp_scripts.ajaxurl,
        beforeSend: function () {
          _self.addClass('rp-loading');
          _self.children('.rp-ajax-toggle-text')
            .addClass('rp-text-visibility');
        },
        success: function (response) {

          if (response.status == 'error') {
            if (response.error_msg) {

              errorString = response.error_msg;

            }
            tata.error(rp_scripts.error, errorString);
            _self.removeClass('rp-loading');
            _self.children('.rp-ajax-toggle-text')
              .removeClass('rp-text-visibility');

          } else {
            window.location.href = rp_scripts.checkout_page
          }
        }
      })
    });

  $(document)
    .on('click', 'span.special-instructions-link', function (e) {
      e.preventDefault();
      $(this)
        .parent('div')
        .find('.special-instructions')
        .toggleClass('hide');
    });

  $('body')
    .on('click', '.rpress-filter-toggle', function () {
      $('div.rpress-filter-wrapper')
        .toggleClass('active');
    });

  $(".rp-cart-left-wrap")
    .click(function () {
      $(".rpress-sidebar-main-wrap")
        .css("left", "0%");
    });

  //Triggering cart
  $(".rp-cart-right-wrap")
    .click(function () {
      $(".cart_item.rpress_checkout a")
        .trigger('click');
    });


  $(".close-cart-ic")
    .click(function () {
      $(".rpress-sidebar-main-wrap")
        .css("left", "100%");
    });

  // Show Image on Modal
  $(".rpress-thumbnail-popup")
    .fancybox({

      openEffect: 'elastic',
      closeEffect: 'elastic',

      helpers: {
        title: {
          type: 'inside'
        }
      }
    });

  if ($(window)
    .width() > 991) {
    var totalHeight = $('header:eq(0)')
      .length > 0 ? $('header:eq(0)')
        .height() + 30 : 120;
    if ($(".sticky-sidebar")
      .length != '') {
      $('.sticky-sidebar')
        .rpressStickySidebar({
          additionalMarginTop: totalHeight
        });
    }
  } else {
    var totalHeight = $('header:eq(0)')
      .length > 0 ? $('header:eq(0)')
        .height() + 30 : 70;
  }
});

/* Make Addons and Variables clickable for Live Price */
jQuery(document)
  .ajaxComplete(function () {

    jQuery('#fooditem-details .food-item-list input')
      .on('click', function (event) {
        update_modal_live_price('fooditem-details');
      });

    jQuery('#fooditem-update-details .food-item-list input')
      .on('click', function (event) {
        update_modal_live_price('fooditem-update-details');
      });
  });

/* RestroPress Sticky Sidebar - Imported from rp-sticky-sidebar.js */
jQuery(function ($) {

  if ($(window)
    .width() > 991) {
    var totalHeight = $('header:eq(0)')
      .length > 0 ? $('header:eq(0)')
        .height() + 30 : 120;
    if ($(".sticky-sidebar")
      .length > 0) {
      $('.sticky-sidebar')
        .rpressStickySidebar({
          additionalMarginTop: totalHeight
        });
    }
  } else {
    var totalHeight = $('header:eq(0)')
      .length > 0 ? $('header:eq(0)')
        .height() + 30 : 70;
  }

  // Category Navigation
  $('body')
    .on('click', '.rpress-category-link', function (e) {
      e.preventDefault();
      var this_id = $(this)
        .data('id');
      var gotom = setInterval(function () {
        rpress_go_to_navtab(this_id);
        clearInterval(gotom);
      }, 100);
    });

  function rpress_go_to_navtab(id) {
    var scrolling_div = jQuery('div.rpress_fooditems_list')
      .find('div#menu-category-' + id);
    if (scrolling_div.length) {
      offSet = scrolling_div.offset()
        .top;

      var body = jQuery("html, body");

      body.animate({
        scrollTop: offSet - totalHeight
      }, 500);
    }
  }

  $('.rpress-category-item')
    .on('click', function () {
      $('.rpress-category-item')
        .removeClass('current');
      $(this)
        .addClass('current');
    });
});

/* Cart Quantity Changer - Imported from cart-quantity-changer.js */
jQuery(function ($) {

  //quantity Minus
  var liveQtyVal;

  jQuery(document)
    .on('click', '.qtyminus', function (e) {

      // Stop acting like a button
      e.preventDefault();

      // Get the field name
      fieldName = 'quantity';

      // Get its current value
      var currentVal = parseInt(jQuery('input[name=' + fieldName + ']')
        .val());

      // If it isn't undefined or its greater than 0
      if (!isNaN(currentVal) && currentVal > 1) {

        // Decrement one only if value is > 1
        jQuery('input[name=' + fieldName + ']')
          .val(currentVal - 1);
        jQuery('.qtyplus')
          .removeAttr('style');
        liveQtyVal = currentVal - 1;

      } else {

        // Otherwise put a 0 there
        jQuery('input[name=' + fieldName + ']')
          .val(1);
        jQuery('.qtyminus')
          .css('color', '#aaa')
          .css('cursor', 'not-allowed');
        liveQtyVal = 1;
      }

      jQuery(this)
        .parents('footer.modal-footer')
        .find('a.submit-fooditem-button')
        .attr('data-item-qty', liveQtyVal);
      jQuery(this)
        .parents('footer.modal-footer')
        .find('a.submit-fooditem-button')
        .attr('data-item-qty', liveQtyVal);

      // Updating live price as per quantity
      var total_price = parseFloat(jQuery('#rpressModal .cart-item-price')
        .attr('data-current'));
      var new_price = parseFloat(total_price * liveQtyVal);
      if (rp_scripts.decimal_separator == ',') {
        new_price_v = new_price.toFixed(2)
          .split('.')
          .join(',');
      } else {
        new_price_v = new_price.toFixed(2);
      }
      jQuery('#rpressModal .cart-item-price')
        .html(rp_scripts.currency_sign + new_price_v);
    });

  jQuery(document)
    .on('click', '.qtyplus', function (e) {

      // Stop acting like a button
      e.preventDefault();

      // Get the field name
      fieldName = 'quantity';

      // Get its current value
      var currentVal = parseInt(jQuery('input[name=' + fieldName + ']')
        .val());
      // If is not undefined
      if (!isNaN(currentVal)) {
        jQuery('input[name=' + fieldName + ']')
          .val(currentVal + 1);
        jQuery('.qtyminus')
          .removeAttr('style');
        liveQtyVal = currentVal + 1;
      } else {
        // Otherwise put a 0 there
        jQuery('input[name=' + fieldName + ']')
          .val(1);
        liveQtyVal = 1;
      }

      jQuery(this)
        .parents('footer.modal-footer')
        .find('a.submit-fooditem-button')
        .attr('data-item-qty', liveQtyVal);
      jQuery(this)
        .parents('footer.modal-footer')
        .find('a.submit-fooditem-button')
        .attr('data-item-qty', liveQtyVal);

      // Updating live price as per quantity
      var total_price = parseFloat(jQuery('#rpressModal .cart-item-price')
        .attr('data-current'));
      var new_price = parseFloat(total_price * liveQtyVal);
      if (rp_scripts.decimal_separator == ',') {
        new_price_v = new_price.toFixed(2)
          .split('.')
          .join(',');
      } else {
        new_price_v = new_price.toFixed(2);
      }
      jQuery('#rpressModal .cart-item-price')
        .html(rp_scripts.currency_sign + new_price_v);
    });

  jQuery(document)
    .on("input", ".qty", function () {
      this.value = this.value.replace(/\D/g, '');
    });

  jQuery(document)
    .on('keyup', '.qty', function (e) {
      // Updating live price as per quantity
      liveQtyVal = jQuery(this)
        .val();
      var total_price = parseFloat(jQuery('#rpressModal .cart-item-price')
        .attr('data-current'));
      var new_price = parseFloat(total_price * liveQtyVal);
      if (rp_scripts.decimal_separator == ',') {
        new_price_v = new_price.toFixed(2)
          .split('.')
          .join(',');
      } else {
        new_price_v = new_price.toFixed(2);
      }
      jQuery('#rpressModal .cart-item-price')
        .html(rp_scripts.currency_sign + new_price_v);
    });
});

/* RestroPress Live Search - Imported from live-search.js */
jQuery(function ($) {

  $('.rpress_fooditems_list')
    .find('.rpress-title-holder')
    .each(function () {
      $(this)
        .attr('data-search-term', $(this)
          .text()
          .toLowerCase());
    });

  $('#rpress-food-search')
    .on('keyup', function () {
      var searchTerm = $(this)
        .val()
        .toLowerCase();
      var DataId;
      var SelectedTermId;

      $('.rpress_fooditems_list')
        .find('.rpress-element-title')
        .each(function (index, elem) {
          $(this)
            .removeClass('not-matched');
          $(this)
            .removeClass('matched');
        });

      $('.rpress_fooditems_list')
        .find('.rpress-title-holder')
        .each(function () {
          DataId = $(this)
            .parents('.rpress_fooditem')
            .attr('data-term-id');

          if ((searchTerm != '' && $(this)
            .filter('[data-search-term *= ' + searchTerm + ']')
            .length > 0) || searchTerm.length < 1) {
            $(this)
              .parents('.rpress_fooditem')
              .show();
            $('.rpress_fooditems_list')
              .find('.rpress-element-title')
              .each(function (index, elem) {
                if ($(this)
                  .attr('data-term-id') == DataId) {
                  $(this)
                    .addClass('matched');
                } else {
                  $(this)
                    .addClass('not-matched');
                }
              });
          } else {
            $(this)
              .parents('.rpress_fooditem')
              .hide();
            $('.rpress_fooditems_list')
              .find('.rpress-element-title')
              .each(function (index, elem) {
                $(this)
                  .addClass('not-matched');
              });
          }
        });

      $('.rpress_fooditems_list')
        .find('.rpress-element-title')
        .each(function () {
          if (!$(this)
            .is(':visible')) {
            $('.rpress-category-link[data-id="' + $(this)
              .data('term-id') + '"]')
              .parent()
              .hide();
          } else {
            $('.rpress-category-link[data-id="' + $(this)
              .data('term-id') + '"]')
              .parent()
              .show();
          }
        });
    });
})

/* RestroPress active category highlighter */
jQuery(function ($) {
  const rp_category_links = $('.rpress-category-lists .rpress-category-link');
  if (rp_category_links.length > 0) {
    const header_height = $('header:eq(0)')
      .height();
    let current_category = rp_category_links.eq('0')
      .attr('href')
      .substr(1);

    function RpScrollingCategories() {
      rp_category_links.each(function () {
        const section_id = $(this)
          .attr('href')
          .substr(1);
        const section = document.querySelector(`.menu-category-wrap[data-cat-id="${section_id}"]`);

        if (section.getBoundingClientRect()
          .top < header_height + 40) {
          current_category = section_id;
        }

        $('.rpress-category-lists .rpress-category-link')
          .removeClass('active');
        $(`.rpress-category-lists .rpress-category-link[href="#${current_category}"]`)
          .addClass('active');

      });
    }
    window.onscroll = function () {
      RpScrollingCategories();
    }
  }
  $(document)
    .ready(function () {

      // Infinate scroll for order history page 
      createObserver();
      //Select service type on checkout page 
      let chkServiceType = rp_getCookie('service_type');


      if (chkServiceType) {
        $(`.rp-checkout-service-option .single-service-selected[data-service-type=${chkServiceType}]`)
          .trigger('click');
      }

      $('.rp-checkout-service-option .single-service-selected')
        .on('click', async function () {
          const serviceType_ = $(this)
            .data('service-type');
          rp_setCookie('service_type', serviceType_, rp_scripts.expire_cookie_time);
          $('#rpress_checkout_order_details')
            .addClass('rp-loading')

          const promiseData = await fetch(`${rp_scripts.ajaxurl}?action=rpress_checkout_update_service_option`);
          const html = await promiseData.json();
          $('#rpress_checkout_order_details')
            .removeClass('rp-loading')
          $('#rpress_checkout_order_details')
            .replaceWith(html.data['order_html']);
          $('#rpress_checkout_cart_wrap')
            .html(html.data['cart_html']);
          $('.rpress_cart_amount')
            .replaceWith(html.data['total_amount']);
          $(document.body)
            .trigger('rp-checkout-update-service-option', [html]);
        });

      //set cookies on checkout page onchange service type
      $('.rp-checkout-service-option .rpress-hrs')
        .on('change', function () {
          $('.rp-checkout-service-option .rpress-delivery-opt-update')
            .trigger('click');
        })

      //Remove the additional service date dropdown
      if (typeof rp_st_vars !== 'undefined' && rp_st_vars.enabled_sevice_type == 'delivery_and_pickup') {
        $('.delivery-settings-wrapper#nav-pickup .delivery-time-wrapper:eq(0)')
          .remove();
      }

      var ServiceTime = rp_getCookie('service_time');
      $('.rpress-delivery')
        .val(ServiceTime);
      $('.rpress-pickup')
        .val(ServiceTime);


    })
  // 
});
let page = 1;
const infinateCallback = async function (entries, observer) {
  for (var i = 0; i < entries.length; i++) {
    let cahnge = entries[i];
    if (cahnge.isIntersecting) {
      jQuery('#rp-order-history-infi-load-container')
        .html('<h2 class="rp-infi-load"><div class="rp-infi-loading">Loading...</div></h2>');
      page = page + 1;
      const data = await fetch(`${rp_scripts.ajaxurl}?action=rpress_more_order_history&security=${rp_scripts.order_details_nonce}&paged=${page}`);
      const html = await data.json();
      jQuery('#rpress_user_history .repress-history-inner')
        .append(html.data['html']);
      if (html.data['found_post'] == '0') {
        jQuery('#rp-order-history-infi-load-container')
          .hide();
      }
    }
  }
}

const createObserver = () => {
  const options = {
    threshold: 0,
  }
  const lastDiv = document.getElementById('rp-order-history-infi-load-container');
  if (!lastDiv) return;
  const observer = new IntersectionObserver(infinateCallback, options);
  observer.observe(lastDiv)
}

// Refresh page after coming from prev page
if (performance.navigation.type == 2) {
  location.reload(true);
}

jQuery('.rpress_fooditems_list').find('.rpress_fooditem_inner').each(function(index, item){
    if( 0 === jQuery(this).find('.rpress-thumbnail-holder').length ){
      jQuery(this).addClass('no-thumbnail-img');
    }
});

jQuery(document).ready(function($){

  $(document).on('change','.rp-variable-price-option', function(){
    rp_checked_default_subaddon();
  });
});