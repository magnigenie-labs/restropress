jQuery(function ($) {

  $(document)
    .on('click', '.rp-service-time span', function (e) {
      $(this)
        .parents('.rp-service-time')
        .find('input')
        .trigger('click');
      e.preventDefault();
      $('.service_available_hrs')
        .timepicker({
          dropdown: true,
          scrollbar: true,
        });
    });

  $('select.addon-items-list')
    .chosen();

  $('select.addon-items-list')
    .on('change', function (event, params) {
      if (event.type == 'change') {
        $('.rpress-order-payment-recalc-totals')
          .show();
      }
    });

  $('input.rpress_timings')
    .timepicker({
      dropdown: true,
      scrollbar: true,
    });

  //Validate License
  $('body')
    .on('click', '.rpress-validate-license', function (e) {
      e.preventDefault();
      var _self = $(this);

      $('.rpress-license-wrapper')
        .find('.rpress-license-field')
        .removeClass('empty-license-key');

      var ButtonText = _self.text();
      var Selected = _self.parent('.rpress-license-wrapper')
        .find('.rpress-license-field')
      var ItemId = Selected.attr('data-item-id');
      var ProductName = Selected.attr('data-item-name');
      var License = Selected.val();
      var LicenseString = _self.parent('.rpress-license-wrapper')
        .find('.rpress_license_string')
        .val();
      var action = _self.attr('data-action');

      if (License.length) {
        _self.addClass('disabled');
        _self.text(rpress_vars.please_wait);

        data = {
          action: action,
          item_id: ItemId,
          product_name: ProductName,
          license: License,
          license_key: LicenseString,
        };

        $.ajax({
          type: "POST",
          data: data,
          dataType: "json",
          url: rpress_vars.ajaxurl,
          xhrFields: {
            withCredentials: true
          },
          success: function (response) {
            if (response.status !== 'error') {
              tata.success(rpress_vars.success, rpress_vars.license_success, {
                position: 'mr'
              })
              _self.parent('.rpress-license-wrapper')
                .addClass('rpress-updated');
              _self.parents('.rpress-purchased-wrap')
                .find('.rpress-license-deactivate-wrapper')
                .removeClass('hide')
                .addClass('show');
            } else {
              tata.error(rpress_vars.error, response.message, {
                position: 'mr'
              })
            }
            _self.text(rpress_vars.license_activate);
            _self.removeClass('disabled');
          }
        })
      } else {
        $(this)
          .parents('.rpress-license-wrapper')
          .find('.rpress-license-field')
          .addClass('empty-license-key');
        tata.error(rpress_vars.error, rpress_vars.empty_license, {
          position: 'mr'
        })
      }
    });

  //Deactivate License
  $('body')
    .on('click', '.rpress-deactivate-license', function (e) {
      e.preventDefault();
      var _self = $(this);
      var action = $(this)
        .attr('data-action');
      var Licensestring = $(this)
        .parents('.rpress-purchased-wrap')
        .find('.rpress_license_string')
        .val();
      var ProductName = $(this)
        .parents('.rpress-purchased-wrap')
        .find('.rpress-license-field')
        .attr('data-item-name');

      _self.addClass('disabled');
      _self.text(rpress_vars.please_wait);

      if (Licensestring.length) {
        data = {
          action: action,
          product_name: ProductName,
          license_key: Licensestring,
        };

        $.ajax({
          type: "POST",
          data: data,
          dataType: "json",
          url: rpress_vars.ajaxurl,
          xhrFields: {
            withCredentials: true
          },
          success: function (response) {
            tata.info(rpress_vars.information, rpress_vars.license_deactivated, {
              position: 'mr'
            })
            if (response.status !== 'error') {
              _self.parents('.rpress-purchased-wrap')
                .find('.rpress-license-wrapper')
                .removeClass('rpress-updated');
              _self.parents('.rpress-purchased-wrap')
                .find('.rpress-license-deactivate-wrapper')
                .removeClass('show')
                .addClass('hide');
            }
            _self.text(rpress_vars.deactivate_license);
            _self.removeClass('disabled');
          }
        })
      }
    });

  $('.rp_print_now')
    .on('click', function () {
      var payment_id = $(this)
        .data('payment-id');
      $('#print-display-area-' + payment_id)
        .load(ajaxurl + '?action=rp_print_payment_data&payment_id=' + payment_id, function () {
          var printContent = document.getElementById('print-display-area-' + payment_id);
          var WinPrint = window.open('', '', 'width=900,height=650');
          WinPrint.document.write(printContent.innerHTML);
          WinPrint.document.close();

          setTimeout(function () {
            WinPrint.focus();
            WinPrint.print();
            WinPrint.close();
          }, 200);
        });
    });

});


jQuery(document)
  .ready(function ($) {

    // Tooltips
    var tooltips = $('.rpress-help-tip');
    rpress_attach_tooltips(tooltips);

    /**
     * RestroPress Configuration Metabox
     */
    var RPRESS_RestroPress_Configuration = {
      init: function () {
        this.add();
        this.move();
        this.remove();
        this.type();
        this.prices();
        this.files();
        this.updatePrices();
      },
      clone_repeatable: function (row) {

        // Retrieve the highest current key
        var key = highest = 1;
        row.parent()
          .find('.rpress_repeatable_row')
          .each(function () {
            var current = $(this)
              .data('key');
            if (parseInt(current) > highest) {
              highest = current;
            }
          });
        key = highest += 1;

        clone = row.clone();

        clone.removeClass('rpress_add_blank');

        clone.attr('data-key', key);
        clone.find('input, select, textarea')
          .val('')
          .each(function () {
            var name = $(this)
              .attr('name');
            var id = $(this)
              .attr('id');

            if (name) {

              name = name.replace(/\[(\d+)\]/, '[' + parseInt(key) + ']');
              $(this)
                .attr('name', name);

            }

            $(this)
              .attr('data-key', key);

            if (typeof id != 'undefined') {

              id = id.replace(/(\d+)/, parseInt(key));
              $(this)
                .attr('id', id);

            }

          });

        /** manually update any select box values */
        clone.find('select')
          .each(function () {
            $(this)
              .val(row.find('select[name="' + $(this)
                .attr('name') + '"]')
                .val());
          });

        /** manually uncheck any checkboxes */
        clone.find('input[type="checkbox"]')
          .each(function () {

            // Make sure checkboxes are unchecked when cloned
            var checked = $(this)
              .is(':checked');
            if (checked) {
              $(this)
                .prop('checked', false);
            }

            // reset the value attribute to 1 in order to properly save the new checked state
            $(this)
              .val(1);
          });

        clone.find('span.rpress_price_id')
          .each(function () {
            $(this)
              .text(parseInt(key));
          });

        clone.find('span.rpress_file_id')
          .each(function () {
            $(this)
              .text(parseInt(key));
          });

        clone.find('.rpress_repeatable_default_input')
          .each(function () {
            $(this)
              .val(parseInt(key))
              .removeAttr('checked');
          });

        clone.find('.rpress_repeatable_condition_field')
          .each(function () {
            $(this)
              .find('option:eq(0)')
              .prop('selected', 'selected');
          });

        // Remove Chosen elements
        clone.find('.search-choice')
          .remove();
        clone.find('.chosen-container')
          .remove();
        rpress_attach_tooltips(clone.find('.rpress-help-tip'));

        return clone;
      },

      add: function () {
        $(document.body)
          .on('click', '.submit .rpress_add_repeatable', function (e) {
            e.preventDefault();
            var button = $(this),
              row = button.parent()
                .parent()
                .prev('.rpress_repeatable_row'),
              clone = RPRESS_RestroPress_Configuration.clone_repeatable(row);

            clone.insertAfter(row)
              .find('input, textarea, select')
              .filter(':visible')
              .eq(0)
              .focus();

            // Setup chosen fields again if they exist
            clone.find('.rpress-select-chosen')
              .chosen({
                inherit_select_classes: true,
                placeholder_text_single: rpress_vars.one_option,
                placeholder_text_multiple: rpress_vars.one_or_more_option,
              });
            clone.find('.rpress-select-chosen')
              .css('width', '100%');
            clone.find('.rpress-select-chosen .chosen-search input')
              .attr('placeholder', rpress_vars.search_placeholder);
          });
      },

      move: function () {

        $(".rpress_repeatable_table .rpress-repeatables-wrap")
          .sortable({
            handle: '.rpress-draghandle-anchor',
            items: '.rpress_repeatable_row',
            opacity: 0.6,
            cursor: 'move',
            axis: 'y',
            update: function () {
              var count = 0;
              $(this)
                .find('.rpress_repeatable_row')
                .each(function () {
                  $(this)
                    .find('input.rpress_repeatable_index')
                    .each(function () {
                      $(this)
                        .val(count);
                    });
                  count++;
                });
            }
          });

      },

      remove: function () {
        $(document.body)
          .on('click', '.rpress-remove-row, .rpress_remove_repeatable', function (e) {
            e.preventDefault();

            var row = $(this)
              .parents('.rpress_repeatable_row'),
              count = row.parent()
                .find('.rpress_repeatable_row')
                .length,
              type = $(this)
                .data('type'),
              repeatable = 'div.rpress_repeatable_' + type + 's',
              focusElement,
              focusable,
              firstFocusable;

            // Set focus on next element if removing the first row. Otherwise set focus on previous element.
            if ($(this)
              .is('.ui-sortable .rpress_repeatable_row:first-child .rpress-remove-row, .ui-sortable .rpress_repeatable_row:first-child .rpress_remove_repeatable')) {
              focusElement = row.next('.rpress_repeatable_row');
            } else {
              focusElement = row.prev('.rpress_repeatable_row');
            }

            focusable = focusElement.find('select, input, textarea, button')
              .filter(':visible');
            firstFocusable = focusable.eq(0);

            if (type === 'price') {
              var price_row_id = row.data('key');
              /** remove from price condition */
              $('.rpress_repeatable_condition_field option[value="' + price_row_id + '"]')
                .remove();
            }

            if (count > 1) {
              $('input, select', row)
                .val('');
              row.fadeOut('fast')
                .remove();
              firstFocusable.focus();
            } else {
              switch (type) {
                case 'price':
                  alert(rpress_vars.one_price_min);
                  break;
                case 'file':
                  $('input, select', row)
                    .val('');
                  break;
                default:
                  alert(rpress_vars.one_field_min);
                  break;
              }
            }

            /* re-index after deleting */
            $(repeatable)
              .each(function (rowIndex) {
                $(this)
                  .find('input, select')
                  .each(function () {
                    var name = $(this)
                      .attr('name');
                    name = name.replace(/\[(\d+)\]/, '[' + rowIndex + ']');
                    $(this)
                      .attr('name', name)
                      .attr('id', name);
                  });
              });
          });
      },

      type: function () {

        $(document.body)
          .on('change', '#_rpress_product_type', function (e) {

            var rpress_products = $('#rpress_products'),
              rpress_fooditem_files = $('#rpress_fooditem_files'),
              rpress_fooditem_limit_wrap = $('#rpress_fooditem_limit_wrap');

            if ('bundle' === $(this)
              .val()) {
              rpress_products.show();
              rpress_fooditem_files.hide();
              rpress_fooditem_limit_wrap.hide();
            } else {
              rpress_products.hide();
              rpress_fooditem_files.show();
              rpress_fooditem_limit_wrap.show();
            }

          });

      },

      prices: function () {
        $(document.body)
          .on('change', '#rpress_variable_pricing', function (e) {
            var checked = $(this)
              .is(':checked');
            var single = $('#rpress_regular_price_field');
            var variable = $('#rpress_variable_price_fields, .rpress_repeatable_table .pricing');
            var bundleRow = $('.rpress-bundled-product-row, .rpress-repeatable-row-standard-fields');
            if (checked) {
              single.hide();
              variable.show();
              bundleRow.addClass('has-variable-pricing');
            } else {
              single.show();
              variable.hide();
              bundleRow.removeClass('has-variable-pricing');
            }
          });
      },

      files: function () {
        var file_frame;
        window.formfield = '';

        $(document.body)
          .on('click', '.rpress_upload_file_button', function (e) {

            e.preventDefault();

            var button = $(this);

            window.formfield = $(this)
              .closest('.rpress_repeatable_upload_wrapper');

            // If the media frame already exists, reopen it.
            if (file_frame) {
              //file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
              file_frame.open();
              return;
            }

            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
              frame: 'post',
              state: 'insert',
              title: button.data('uploader-title'),
              button: {
                text: button.data('uploader-button-text')
              },
              multiple: $(this)
                .data('multiple') == '0' ? false : true // Set to true to allow multiple files to be selected
            });

            file_frame.on('menu:render:default', function (view) {
              // Store our views in an object.
              var views = {};

              // Unset default menu items
              view.unset('library-separator');
              view.unset('gallery');
              view.unset('featured-image');
              view.unset('embed');

              // Initialize the views in our view object.
              view.set(views);
            });

            // When an image is selected, run a callback.
            file_frame.on('insert', function () {

              var selection = file_frame.state()
                .get('selection');
              selection.each(function (attachment, index) {
                attachment = attachment.toJSON();

                var selectedSize = 'image' === attachment.type ? $('.attachment-display-settings .size option:selected')
                  .val() : false;
                var selectedURL = attachment.url;
                var selectedName = attachment.title.length > 0 ? attachment.title : attachment.filename;

                if (selectedSize && typeof attachment.sizes[selectedSize] != "undefined") {
                  selectedURL = attachment.sizes[selectedSize].url;
                }

                if ('image' === attachment.type) {
                  if (selectedSize && typeof attachment.sizes[selectedSize] != "undefined") {
                    selectedName = selectedName + '-' + attachment.sizes[selectedSize].width + 'x' + attachment.sizes[selectedSize].height;
                  } else {
                    selectedName = selectedName + '-' + attachment.width + 'x' + attachment.height;
                  }
                }

                if (0 === index) {
                  // place first attachment in field
                  window.formfield.find('.rpress_repeatable_attachment_id_field')
                    .val(attachment.id);
                  window.formfield.find('.rpress_repeatable_thumbnail_size_field')
                    .val(selectedSize);
                  window.formfield.find('.rpress_repeatable_upload_field')
                    .val(selectedURL);
                  window.formfield.find('.rpress_repeatable_name_field')
                    .val(selectedName);
                } else {
                  // Create a new row for all additional attachments
                  var row = window.formfield,
                    clone = RPRESS_RestroPress_Configuration.clone_repeatable(row);

                  clone.find('.rpress_repeatable_attachment_id_field')
                    .val(attachment.id);
                  clone.find('.rpress_repeatable_thumbnail_size_field')
                    .val(selectedSize);
                  clone.find('.rpress_repeatable_upload_field')
                    .val(selectedURL);
                  clone.find('.rpress_repeatable_name_field')
                    .val(selectedName);
                  clone.insertAfter(row);
                }
              });
            });

            // Finally, open the modal
            file_frame.open();
          });


        var file_frame;
        window.formfield = '';

      },

      updatePrices: function () {
        $('#rpress_price_fields')
          .on('keyup', '.rpress_variable_prices_name', function () {

            var key = $(this)
              .parents('.rpress_repeatable_row')
              .data('key'),
              name = $(this)
                .val(),
              field_option = $('.rpress_repeatable_condition_field option[value=' + key + ']');

            if (field_option.length > 0) {
              field_option.text(name);
            } else {
              $('.rpress_repeatable_condition_field')
                .append(
                  $('<option></option>')
                    .attr('value', key)
                    .text(name)
                );
            }
          });
      }

    };

    // Toggle display of entire custom settings section for a price option
    $(document.body)
      .on('click', '.toggle-custom-price-option-section', function (e) {
        e.preventDefault();
        var show = $(this)
          .html() == rpress_vars.show_advanced_settings ? true : false;

        if (show) {
          $(this)
            .html(rpress_vars.hide_advanced_settings);
        } else {
          $(this)
            .html(rpress_vars.show_advanced_settings);
        }

        var header = $(this)
          .parents('.rpress-repeatable-row-header');
        header.siblings('.rpress-custom-price-option-sections-wrap')
          .slideToggle();

        var first_input;
        if (show) {
          first_input = $(":input:not(input[type=button],input[type=submit],button):visible:first", header.siblings('.rpress-custom-price-option-sections-wrap'));
        } else {
          first_input = $(":input:not(input[type=button],input[type=submit],button):visible:first", header.siblings('.rpress-repeatable-row-standard-fields'));
        }
        first_input.focus();
      });

    RPRESS_RestroPress_Configuration.init();

    // Date picker
    var rpress_datepicker = $('.rpress_datepicker');
    if (rpress_datepicker.length > 0) {
      var dateFormat = 'mm/dd/yy';
      rpress_datepicker.datepicker({
        dateFormat: dateFormat
      });
    }

    /**
     * Edit payment screen JS
     */
    var RPRESS_Edit_Payment = {

      init: function () {
        this.edit_address();
        this.remove_fooditem();
        this.add_fooditem();
        this.change_customer();
        this.new_customer();
        this.edit_price();
        this.edit_qty();
        this.recalculate_total();
        this.variable_prices_check();
        this.add_note();
        this.remove_note();
        this.resend_receipt();
      },


      edit_address: function () {

        // Update base state field based on selected base country
        $('select[name="rpress-payment-address[0][country]"]')
          .change(function () {
            var $this = $(this);
            var data = {
              action: 'rpress_get_states',
              country: $this.val(),
              field_name: 'rpress-payment-address[0][state]'
            };
            $.post(ajaxurl, data, function (response) {
              var state_wrapper = $('#rpress-order-address-state-wrap select, #rpress-order-address-state-wrap input');
              // Remove any chosen containers here too
              $('#rpress-order-address-state-wrap .chosen-container')
                .remove();
              if ('nostates' == response) {
                state_wrapper.replaceWith('<input type="text" name="rpress-payment-address[0][state]" value="" class="rpress-edit-toggles medium-text"/>');
              } else {
                state_wrapper.replaceWith(response);
              }
            });

            return false;
          });

      },

      remove_fooditem: function () {

        // Remove a fooditem from a purchase
        $('#rpress-purchased-items')
          .on('click', '.rpress-order-remove-fooditem', function (e) {

            var count = $(document.body)
              .find('#rpress-purchased-items > .row:not(.header)')
              .length;

            if (count === 1) {
              alert(rpress_vars.one_fooditem_min);
              return false;
            }

            if (confirm(rpress_vars.delete_payment_fooditem)) {

              var key = $(this)
                .data('key');
              var fooditem_id = $('input[name="rpress-payment-details-fooditems[' + key + '][id]"]')
                .val();
              var price_id = $('input[name="rpress-payment-details-fooditems[' + key + '][price_id]"]')
                .val();
              var quantity = $('input[name="rpress-payment-details-fooditems[' + key + '][quantity]"]')
                .val();
              var amount = $('input[name="rpress-payment-details-fooditems[' + key + '][amount]"]')
                .val();

              // if ( $('input[name="rpress-payment-details-fooditems['+key+'][item_tax]"]') ) {
              //   var fees = $('input[name="rpress-payment-details-fooditems['+key+'][item_tax]"]').val();
              // }

              // if ( $('input[name="rpress-payment-details-fooditems['+key+'][fees]"]') ) {
              //   var fees = $.parseJSON( $('input[name="rpress-payment-details-fooditems['+key+'][fees]"]').val() );
              // }

              var currently_removed = $('input[name="rpress-payment-removed"]')
                .val();
              currently_removed = $.parseJSON(currently_removed);
              if (currently_removed.length < 1) {
                currently_removed = {};
              }

              var removed_item = [{
                'id': fooditem_id,
                'price_id': price_id,
                'quantity': quantity,
                'amount': amount,
                'cart_index': key
              }];
              currently_removed[key] = removed_item

              $('input[name="rpress-payment-removed"]')
                .val(JSON.stringify(currently_removed));

              $(this)
                .parents('.row.rpress-purchased-row')
                .remove();

              // Flag the RestroPress section as changed
              $('#rpress-payment-fooditems-changed')
                .val(1);
              $('.rpress-order-payment-recalc-totals')
                .show();
            }
            return false;
          });

      },

      change_customer: function () {

        $('#rpress-customer-details')
          .on('click', '.rpress-payment-change-customer, .rpress-payment-change-customer-cancel', function (e) {
            e.preventDefault();

            var change_customer = $(this)
              .hasClass('rpress-payment-change-customer');
            var cancel = $(this)
              .hasClass('rpress-payment-change-customer-cancel');

            if (change_customer) {
              $('.customer-info')
                .hide();
              $('.change-customer')
                .show();
              $('.rpress-payment-change-customer-input')
                .css('width', 'auto');
            } else if (cancel) {
              $('.customer-info')
                .show();
              $('.change-customer')
                .hide();
            }

          });

      },

      new_customer: function () {

        $('#rpress-customer-details')
          .on('click', '.rpress-payment-new-customer, .rpress-payment-new-customer-cancel', function (e) {
            e.preventDefault();

            var new_customer = $(this)
              .hasClass('rpress-payment-new-customer');
            var cancel = $(this)
              .hasClass('rpress-payment-new-customer-cancel');

            if (new_customer) {
              $('.customer-info')
                .hide();
              $('.new-customer')
                .show();
            } else if (cancel) {
              $('.customer-info')
                .show();
              $('.new-customer')
                .hide();
            }


            var new_customer = $('#rpress-new-customer');
            if ($('.new-customer')
              .is(":visible")) {
              new_customer.val(1);
            } else {
              new_customer.val(0);
            }

          });

      },

      add_fooditem: function () {

        // Add a New RestroPress from the Add RestroPress to Purchase Box
        $('.rpress-edit-purchase-element')
          .on('click', '#rpress-order-add-fooditem', function (e) {

            e.preventDefault();

            var selectedButton = $(this);

            var order_fooditem_select = $('#rpress_order_fooditem_select'),
              order_fooditem_quantity = $('#rpress-order-fooditem-quantity'),
              order_fooditem_price = $('#rpress-order-fooditem-price'),
              order_fooditem_tax = $('#rpress-order-fooditem-tax'),
              selected_price_option = $('.rpress_price_options_select option:selected');
            selected_item_price = $('.rpress_selected_price');

            var fooditem_id = order_fooditem_select.val();
            var fooditem_title = order_fooditem_select.find(':selected')
              .text();
            var quantity = order_fooditem_quantity.val();
            var item_price = selected_item_price.val();
            var item_tax = order_fooditem_tax.val();
            var price_id = selected_price_option.val();
            var price_name = selected_price_option.text();

            if (fooditem_id < 1) {
              return false;
            }

            if (!item_price) {
              item_price = 0;
            }

            item_price = parseFloat(item_price);
            if (isNaN(item_price)) {
              alert(rpress_vars.numeric_item_price);
              return false;
            }

            item_tax = parseFloat(item_tax);
            if (isNaN(item_tax)) {
              alert(rpress_vars.numeric_item_tax);
              return false;
            }

            if (isNaN(parseInt(quantity))) {
              alert(rpress_vars.numeric_quantity);
              return false;
            }

            if (price_name) {
              fooditem_title = fooditem_title + ' - ' + price_name;
            }

            var count = $('#rpress-purchased-items div.row')
              .length;
            var IndexCount = count - 1;
            var clone = $('#rpress-purchased-items div.row:last')
              .clone();
            // var Name = $('#rpress-purchased-items div.row:last').find('select').attr('name');

            clone.find('.fooditem span.rpress-purchased-fooditem-title')
              .html('<a href="post.php?post=' + fooditem_id + '&action=edit"></a>');
            clone.find('.fooditem span.rpress-purchased-fooditem-title a')
              .text(fooditem_title);
            clone.find('h3.rpress-purchased-item-name')
              .text(fooditem_title);
            clone.find('.rpress-payment-details-fooditem-item-price')
              .val(item_price.toFixed(rpress_vars.currency_decimals));
            clone.find('.rpress-payment-details-fooditem-item-tax')
              .val(item_tax.toFixed(rpress_vars.currency_decimals));
            clone.find('input.rpress-payment-details-fooditem-id')
              .val(fooditem_id);
            clone.find('input.rpress-payment-details-fooditem-price-id')
              .val(price_id);

            clone.find('.order-addon-items.special-instructions')
              .remove();

            var item_total = (item_price * quantity) + item_tax;
            item_total = item_total.toFixed(rpress_vars.currency_decimals);
            clone.find('span.rpress-payment-details-fooditem-amount')
              .text(item_total);
            clone.find('input.rpress-payment-details-fooditem-amount')
              .val(item_total);
            clone.find('input.rpress-payment-details-fooditem-quantity')
              .val(quantity);
            clone.find('input.rpress-payment-details-fooditem-has-log')
              .val(0);

            clone.find('.rpress-copy-fooditem-link-wrapper')
              .remove();
            clone.find('.rpress-special-instruction')
              .remove();

            // Replace the name / id attributes
            clone.find('input')
              .each(function () {
                var name = $(this)
                  .attr('name');
                if (name !== undefined) {
                  name = name.replace(/\[(\d+)\]/, '[' + parseInt(count) + ']');

                  $(this)
                    .attr('name', name)
                    .attr('id', name);
                }
              });

            clone.find('select')
              .each(function () {
                var name = $(this)
                  .attr('name');
                var CustomName = 'rpress-payment-details-fooditems[' + count + '][addon_items][]';
                $(this)
                  .attr('name', CustomName);
              });

            clone.find('a.rpress-order-remove-fooditem')
              .attr('data-key', parseInt(count));

            // Flag the RestroPress section as changed
            $('#rpress-payment-fooditems-changed')
              .val(1);

            setTimeout(function () {
              rpress_get_addon_items_list(fooditem_id, clone);
            }, 1000);

            $(clone)
              .insertAfter('#rpress-purchased-items div.row:last');
            clone.find('select')
              .html();
            $('.rpress-order-payment-recalc-totals')
              .show();
            $('.rpress-add-fooditem-field')
              .val('');

            $("#rpress_order_fooditem_select")
              .val('')
              .trigger("chosen:updated");
            $(".rp-add-update-elements")
              .find('.rpress-fooditem-price')
              .empty();
          });
      },

      edit_qty: function () {
        $(document.body)
          .on('change keyup', '.rpress-payment-details-fooditem-quantity', function () {
            var selectedQty = $(this)
              .val();
            var row = $(this)
              .parents('ul.rpress-purchased-items-list-wrapper');


            row.find('input.rpress-payment-details-fooditem-quantity')
              .val(selectedQty);
          });
      },

      edit_price: function () {
        $(document.body)
          .on('change keyup', '.rpress-payment-item-input', function () {
            var row = $(this)
              .parents('ul.rpress-purchased-items-list-wrapper');
            $('.rpress-order-payment-recalc-totals')
              .show();

            var quantity = row.find('input.rpress-payment-details-fooditem-quantity')
              .val()
              .replace(rpress_vars.thousands_separator, '');
            var item_price = row.find('input.rpress-payment-details-fooditem-item-price')
              .val()
              .replace(rpress_vars.thousands_separator, '');
            var item_tax = row.find('input.rpress-payment-details-fooditem-item-tax')
              .val()
              .replace(rpress_vars.thousands_separator, '');
            if ($(this)
              .hasClass('rpress-payment-details-fooditem-quantity')) {
              var quantity = $(this)
                .val();
            }

            item_price = parseFloat(item_price);
            if (isNaN(item_price)) {
              alert(rpress_vars.numeric_item_price);
              return false;
            }

            item_tax = parseFloat(item_tax);
            if (isNaN(item_tax)) {
              item_tax = 0.00;
            }

            if (isNaN(parseInt(quantity))) {
              quantity = 1;
            }

            var item_total = (item_price * quantity) + item_tax;
            item_total = item_total.toFixed(rpress_vars.currency_decimals);
            row.find('input.rpress-payment-details-fooditem-amount')
              .val(item_total);
            row.find('span.rpress-payment-details-fooditem-amount')
              .text(item_total);
          });

      },

      recalculate_total: function () {

        // Update taxes and totals for any changes made.
        $('#rpress-order-recalc-total')
          .on('click', function (e) {
            e.preventDefault();

            var addonTotalPrice;
            var addonTotal = 0;

            $(".addon-items-list")
              .each(function (key, item) {

                var row = $(this)
                  .parents('.rpress-order-items-wrapper');
                var quantity = row.find('input.rpress-payment-details-fooditem-quantity')
                  .val()
                  .replace(rpress_vars.thousands_separator, '');

                addonTotalPrice = $(this)
                  .val();
                if (addonTotalPrice !== null && addonTotalPrice !== '') {
                  for (var i = 0; i < addonTotalPrice.length; i++) {
                    addonData = addonTotalPrice[i].split('|');
                    addonData = addonData[2] == '' ? 0 : addonData[2];
                    addonTotal += parseFloat(addonData * quantity);
                  }
                }

              });

            var total = 0,
              tax = 0,
              totals = $('#rpress-purchased-items .row input.rpress-payment-details-fooditem-amount'),
              taxes = $('#rpress-purchased-items .row input.rpress-payment-details-fooditem-item-tax');

            if (totals.length) {
              totals.each(function () {
                total += parseFloat($(this)
                  .val());
              });
            }

            total += addonTotal;

            if (taxes.length) {
              taxes.each(function () {
                tax += parseFloat($(this)
                  .val());
              });
            }

            if ($('.rpress-payment-fees')
              .length) {
              $('.rpress-payment-fees span.fee-amount')
                .each(function () {
                  total += parseFloat($(this)
                    .data('fee'));
                });
            }

            $('input[name=rpress-payment-total]')
              .val(total.toFixed(rpress_vars.currency_decimals));
            $('input[name=rpress-payment-tax]')
              .val(tax.toFixed(rpress_vars.currency_decimals))
          });

      },

      variable_prices_check: function () {

        // On RestroPress Select, Check if Variable Prices Exist
        $('.rpress-edit-purchase-element')
          .on('change', 'select#rpress_order_fooditem_select', function () {

            var $this = $(this),
              fooditem_id = $this.val();

            if (parseInt(fooditem_id) > 0) {
              var postData = {
                action: 'rpress_check_for_fooditem_price_variations',
                fooditem_id: fooditem_id
              };

              $.ajax({
                type: "POST",
                data: postData,
                url: ajaxurl,
                success: function (response) {
                  //$this.parents('.rpress-add-fooditem-to-purchase').find('span.rpress-fooditem-price').html(response);
                  //$this.parents('.rpress-add-fooditem-to-purchase').find('input.rpress-order-fooditem-price').val(response);
                  //$('.rpress_price_options_select').remove();
                  //$(response).insertAfter( $this.next() );
                }
              })
                .fail(function (data) {
                  if (window.console && window.console.log) {
                    console.log(data);
                  }
                });

            }
          });

      },

      add_note: function () {

        $('#rpress-add-payment-note')
          .on('click', function (e) {
            e.preventDefault();
            var postData = {
              action: 'rpress_insert_payment_note',
              payment_id: $(this)
                .data('payment-id'),
              note: $('#rpress-payment-note')
                .val()
            };

            if (postData.note) {

              $.ajax({
                type: "POST",
                data: postData,
                url: ajaxurl,
                success: function (response) {
                  $('#rpress-payment-notes-inner')
                    .append(response);
                  $('.rpress-no-payment-notes')
                    .hide();
                  $('#rpress-payment-note')
                    .val('');
                }
              })
                .fail(function (data) {
                  if (window.console && window.console.log) {
                    console.log(data);
                  }
                });

            } else {
              var border_color = $('#rpress-payment-note')
                .css('border-color');
              $('#rpress-payment-note')
                .css('border-color', 'red');
              setTimeout(function () {
                $('#rpress-payment-note')
                  .css('border-color', border_color);
              }, 500);
            }

          });

      },

      remove_note: function () {

        $(document.body)
          .on('click', '.rpress-delete-payment-note', function (e) {

            e.preventDefault();

            if (confirm(rpress_vars.delete_payment_note)) {

              var postData = {
                action: 'rpress_delete_payment_note',
                payment_id: $(this)
                  .data('payment-id'),
                note_id: $(this)
                  .data('note-id')
              };

              $.ajax({
                type: "POST",
                data: postData,
                url: ajaxurl,
                success: function (response) {
                  $('#rpress-payment-note-' + postData.note_id)
                    .remove();
                  if (!$('.rpress-payment-note')
                    .length) {
                    $('.rpress-no-payment-notes')
                      .show();
                  }
                  return false;
                }
              })
                .fail(function (data) {
                  if (window.console && window.console.log) {
                    console.log(data);
                  }
                });
              return true;
            }

          });

      },

      resend_receipt: function () {

        var emails_wrap = $('.rpress-order-resend-receipt-addresses');

        $(document.body)
          .on('click', '#rpress-select-receipt-email', function (e) {

            e.preventDefault();
            emails_wrap.slideDown();

          });

        $(document.body)
          .on('change', '.rpress-order-resend-receipt-email', function () {

            var href = $('#rpress-select-receipt-email')
              .prop('href') + '&email=' + $(this)
                .val();

            if (confirm(rpress_vars.resend_receipt)) {
              window.location = href;
            }

          });


        $(document.body)
          .on('click', '#rpress-resend-receipt', function (e) {

            return confirm(rpress_vars.resend_receipt);

          });

      },
    };
    RPRESS_Edit_Payment.init();


    /**
     * Discount add / edit screen JS
     */
    var RPRESS_Discount = {

      init: function () {
        this.type_select();
        this.product_requirements();
      },

      type_select: function () {

        $('#rpress-edit-discount #rpress-type, #rpress-add-discount #rpress-type')
          .change(function () {
            var val = $(this)
              .val();
            $('.rpress-amount-description')
              .hide();
            $('.rpress-amount-description.' + val + '-discount')
              .show();

          });

      },

      product_requirements: function () {

        $('#products')
          .change(function () {

            var product_conditions = $('#rpress-discount-product-conditions');

            if ($(this)
              .val()) {
              product_conditions.show();
            } else {
              product_conditions.hide();
            }

          });

      },

    };
    RPRESS_Discount.init();


    /**
     * Reports / Exports screen JS
     */
    var RPRESS_Reports = {

      init: function () {
        this.date_options();
        this.customers_export();
      },

      date_options: function () {

        // Show hide extended date options
        $('#rpress-graphs-date-options')
          .change(function () {
            var $this = $(this),
              date_range_options = $('#rpress-date-range-options');

            if ('other' === $this.val()) {
              date_range_options.show();
            } else {
              date_range_options.hide();
            }
          });

      },

      customers_export: function () {

        // Show / hide RestroPress option when exporting customers

        $('#rpress_customer_export_fooditem')
          .change(function () {

            var $this = $(this),
              fooditem_id = $('option:selected', $this)
                .val(),
              customer_export_option = $('#rpress_customer_export_option');

            if ('0' === $this.val()) {
              customer_export_option.show();
            } else {
              customer_export_option.hide();
            }

            // On RestroPress Select, Check if Variable Prices Exist
            if (parseInt(fooditem_id) != 0) {
              var data = {
                action: 'rpress_check_for_fooditem_price_variations',
                fooditem_id: fooditem_id,
                all_prices: true
              };

              var price_options_select = $('.rpress_price_options_select');

              $.post(ajaxurl, data, function (response) {
                price_options_select.remove();
                $('#rpress_customer_export_fooditem_chosen')
                  .after(response);
              });
            } else {
              price_options_select.remove();
            }
          });

      }

    };
    RPRESS_Reports.init();

    /**
     * Settings screen JS
     */
    var RPRESS_Settings = {

      init: function () {
        this.general();
        this.taxes();
        this.misc();
      },

      general: function () {

        var rpress_color_picker = $('.rpress-color-picker');

        if (rpress_color_picker.length) {
          rpress_color_picker.wpColorPicker();
        }

        // Settings Upload field JS
        if (typeof wp === "undefined" || '1' !== rpress_vars.new_media_ui) {
          //Old Thickbox uploader
          var rpress_settings_upload_button = $('.rpress_settings_upload_button');
          if (rpress_settings_upload_button.length > 0) {
            window.formfield = '';

            $(document.body)
              .on('click', rpress_settings_upload_button, function (e) {
                e.preventDefault();
                window.formfield = $(this)
                  .parent()
                  .prev();
                window.tbframe_interval = setInterval(function () {
                  jQuery('#TB_iframeContent')
                    .contents()
                    .find('.savesend .button')
                    .val(rpress_vars.use_this_file)
                    .end()
                    .find('#insert-gallery, .wp-post-thumbnail')
                    .hide();
                }, 2000);
                tb_show(rpress_vars.add_new_fooditem, 'media-upload.php?TB_iframe=true');
              });

            window.rpress_send_to_editor = window.send_to_editor;
            window.send_to_editor = function (html) {
              if (window.formfield) {
                imgurl = $('a', '<div>' + html + '</div>')
                  .attr('href');
                window.formfield.val(imgurl);
                window.clearInterval(window.tbframe_interval);
                tb_remove();
              } else {
                window.rpress_send_to_editor(html);
              }
              window.send_to_editor = window.rpress_send_to_editor;
              window.formfield = '';
              window.imagefield = false;
            };
          }
        } else {
          // WP 3.5+ uploader
          var file_frame;
          window.formfield = '';

          $(document.body)
            .on('click', '.rpress_settings_upload_button', function (e) {

              e.preventDefault();

              var button = $(this);

              window.formfield = $(this)
                .parent()
                .prev();

              // If the media frame already exists, reopen it.
              if (file_frame) {
                //file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
                file_frame.open();
                return;
              }

              // Create the media frame.
              file_frame = wp.media.frames.file_frame = wp.media({
                frame: 'post',
                state: 'insert',
                title: button.data('uploader_title'),
                button: {
                  text: button.data('uploader_button_text')
                },
                multiple: false
              });

              file_frame.on('menu:render:default', function (view) {
                // Store our views in an object.
                var views = {};

                // Unset default menu items
                view.unset('library-separator');
                view.unset('gallery');
                view.unset('featured-image');
                view.unset('embed');

                // Initialize the views in our view object.
                view.set(views);
              });

              // When an image is selected, run a callback.
              file_frame.on('insert', function () {

                var selection = file_frame.state()
                  .get('selection');
                selection.each(function (attachment, index) {
                  attachment = attachment.toJSON();
                  window.formfield.val(attachment.url);
                });
              });

              // Finally, open the modal
              file_frame.open();
            });


          // WP 3.5+ uploader
          var file_frame;
          window.formfield = '';
        }

      },

      taxes: function () {
        var no_states = $('select.rpress-no-states');
        if (no_states.length) {
          no_states.closest('tr')
            .addClass('hidden');
        }

        // Update base state field based on selected base country
        $('select[name="rpress_settings[base_country]"]')
          .change(function () {
            var $this = $(this),
              $tr = $this.closest('tr');
            var data = {
              action: 'rpress_get_states',
              country: $(this)
                .val(),
              field_name: 'rpress_settings[base_state]'
            };
            $.post(ajaxurl, data, function (response) {
              if ('nostates' == response) {
                $tr.next()
                  .addClass('hidden');
              } else {
                $tr.next()
                  .removeClass('hidden');
                $tr.next()
                  .find('select')
                  .replaceWith(response);
              }
            });

            return false;
          });

        // Update tax rate state field based on selected rate country
        $(document.body)
          .on('change', '#rpress_tax_rates select.rpress-tax-country', function () {
            var $this = $(this);
            var data = {
              action: 'rpress_get_states',
              country: $(this)
                .val(),
              field_name: $this.attr('name')
                .replace('country', 'state')
            };
            $.post(ajaxurl, data, function (response) {
              if ('nostates' == response) {
                var text_field = '<input type="text" name="' + data.field_name + '" value=""/>';
                $this.parent()
                  .next()
                  .find('select')
                  .replaceWith(text_field);
              } else {
                $this.parent()
                  .next()
                  .find('input,select')
                  .show();
                $this.parent()
                  .next()
                  .find('input,select')
                  .replaceWith(response);
              }
            });

            return false;
          });

        // Insert new tax rate row
        $('#rpress_add_tax_rate')
          .on('click', function () {
            var row = $('#rpress_tax_rates tr:last');
            var clone = row.clone();
            var count = row.parent()
              .find('tr')
              .length;
            clone.find('td input')
              .not(':input[type=checkbox]')
              .val('');
            clone.find('td [type="checkbox"]')
              .attr('checked', false);
            clone.find('input, select')
              .each(function () {
                var name = $(this)
                  .attr('name');
                name = name.replace(/\[(\d+)\]/, '[' + parseInt(count) + ']');
                $(this)
                  .attr('name', name)
                  .attr('id', name);
              });
            clone.find('label')
              .each(function () {
                var name = $(this)
                  .attr('for');
                name = name.replace(/\[(\d+)\]/, '[' + parseInt(count) + ']');
                $(this)
                  .attr('for', name);
              });
            clone.insertAfter(row);
            return false;
          });

        // Remove tax row
        $(document.body)
          .on('click', '#rpress_tax_rates .rpress_remove_tax_rate', function () {
            if (confirm(rpress_vars.delete_tax_rate)) {
              var tax_rates = $('#rpress_tax_rates tr:visible');
              var count = tax_rates.length;

              if (count === 2) {
                $('#rpress_tax_rates select')
                  .val('');
                $('#rpress_tax_rates input[type="text"]')
                  .val('');
                $('#rpress_tax_rates input[type="number"]')
                  .val('');
                $('#rpress_tax_rates input[type="checkbox"]')
                  .attr('checked', false);
              } else {
                $(this)
                  .closest('tr')
                  .remove();
              }

              /* re-index after deleting */
              $('#rpress_tax_rates tr')
                .each(function (rowIndex) {
                  $(this)
                    .children()
                    .find('input, select')
                    .each(function () {
                      var name = $(this)
                        .attr('name');
                      name = name.replace(/\[(\d+)\]/, '[' + (rowIndex - 1) + ']');
                      $(this)
                        .attr('name', name)
                        .attr('id', name);
                    });
                });
            }
            return false;
          });

      },

      misc: function () {

        var fooditemMethod = $('select[name="rpress_settings[fooditem_method]"]');
        var symlink = fooditemMethod.parent()
          .parent()
          .next();

        // Hide Symlink option if RestroPress Method is set to Direct
        if (fooditemMethod.val() == 'direct') {
          symlink.hide();
          symlink.find('input')
            .prop('checked', false);
        }
        // Toggle fooditem method option
        fooditemMethod.on('change', function () {
          if ($(this)
            .val() == 'direct') {
            symlink.hide();
            symlink.find('input')
              .prop('checked', false);
          } else {
            symlink.show();
          }
        });
      }

    }
    RPRESS_Settings.init();

    $('.fooditem_page_rpress-payment-history .row-actions .delete a, a.rpress-delete-payment')
      .on('click', function () {
        if (confirm(rpress_vars.delete_payment)) {
          return true;
        }
        return false;
      });

    $('body')
      .on('click', '#the-list .editinline', function () {

        var post_id = $(this)
          .closest('tr')
          .attr('id');

        post_id = post_id.replace("post-", "");

        var $rpress_inline_data = $('#post-' + post_id);

        var regprice = $rpress_inline_data.find('.column-price .fooditemprice-' + post_id)
          .val();

        // If variable priced product disable editing, otherwise allow price changes
        if (regprice != $('#post-' + post_id + '.column-price .fooditemprice-' + post_id)
          .val()) {
          $('.regprice', '#rpress-fooditem-data')
            .val(regprice)
            .attr('disabled', false);
        } else {
          $('.regprice', '#rpress-fooditem-data')
            .val(rpress_vars.quick_edit_warning)
            .attr('disabled', 'disabled');
        }
      });


    // Bulk edit save
    $(document.body)
      .on('click', '#bulk_edit', function () {

        // define the bulk edit row
        var $bulk_row = $('#bulk-edit');

        // get the selected post ids that are being edited
        var $post_ids = new Array();
        $bulk_row.find('#bulk-titles')
          .children()
          .each(function () {
            $post_ids.push($(this)
              .attr('id')
              .replace(/^(ttle)/i, ''));
          });

        // get the stock and price values to save for all the product ID's
        var $price = $('#rpress-fooditem-data input[name="_rpress_regprice"]')
          .val();

        var data = {
          action: 'rpress_save_bulk_edit',
          rpress_bulk_nonce: $post_ids,
          post_ids: $post_ids,
          price: $price
        };

        // save the data
        $.post(ajaxurl, data);

      });

    // Setup Chosen menus
    $('.rpress-select-chosen')
      .chosen({
        inherit_select_classes: true,
        placeholder_text_single: rpress_vars.one_option,
        placeholder_text_multiple: rpress_vars.one_or_more_option,
      });

    $('.rpress-select-chosen .chosen-search input')
      .each(function () {
        var selectElem = $(this)
          .parent()
          .parent()
          .parent()
          .prev('select.rpress-select-chosen'),
          type = selectElem.data('search-type'),
          placeholder = selectElem.data('search-placeholder');
        $(this)
          .attr('placeholder', placeholder);
      });

    // Add placeholders for Chosen input fields
    $('.chosen-choices')
      .on('click', function () {
        var placeholder = $(this)
          .parent()
          .prev()
          .data('search-placeholder');
        if (typeof placeholder === "undefined") {
          placeholder = rpress_vars.type_to_search;
        }
        $(this)
          .children('li')
          .children('input')
          .attr('placeholder', placeholder);
      });

    // Variables for setting up the typing timer
    var typingTimer; // Timer identifier
    var doneTypingInterval = 342; // Time in ms, Slow - 521ms, Moderate - 342ms, Fast - 300ms

    // Replace options with search results
    $(document.body)
      .on('keyup', '.rpress-select.chosen-container .chosen-search input, .rpress-select.chosen-container .search-field input', function (e) {

        var val = $(this)
          .val()
        var container = $(this)
          .closest('.rpress-select-chosen');
        var menu_id = container.attr('id')
          .replace('_chosen', '');
        var select = container.prev();
        var no_bundles = container.hasClass('no-bundles');
        var variations = container.hasClass('variations');
        var lastKey = e.which;
        var search_type = 'rpress_fooditem_search';

        // Detect if we have a defined search type, otherwise default to fooditems
        if (container.prev()
          .data('search-type')) {

          // Don't trigger AJAX if this select has all options loaded
          if ('no_ajax' == select.data('search-type')) {
            return;
          }

          search_type = 'rpress_' + select.data('search-type') + '_search';
        }

        // Don't fire if short or is a modifier key (shift, ctrl, apple command key, or arrow keys)
        if (
          (val.length <= 3 && 'rpress_fooditem_search' == search_type) ||
          (
            lastKey == 16 ||
            lastKey == 13 ||
            lastKey == 91 ||
            lastKey == 17 ||
            lastKey == 37 ||
            lastKey == 38 ||
            lastKey == 39 ||
            lastKey == 40
          )
        ) {
          return;
        }
        clearTimeout(typingTimer);
        typingTimer = setTimeout(
          function () {
            $.ajax({
              type: 'GET',
              url: ajaxurl,
              data: {
                action: search_type,
                s: val,
                no_bundles: no_bundles,
                variations: variations,
              },
              dataType: "json",
              beforeSend: function () {
                select.closest('ul.chosen-results')
                  .empty();
              },
              success: function (data) {
                // Remove all options but those that are selected
                // $('option:not(:selected)', select).remove();
                // $.each( data, function( key, item ) {
                //   // Add any option that doesn't already exist
                //   if( ! $('option[value="' + item.id + '"]', select).length ) {
                //     select.prepend( '<option value="' + item.id + '">' + item.name + '</option>' );
                //   }
                // });
                // // Update the options
                // $('.rpress-select-chosen').trigger('chosen:updated');
                // select.next().find('input').val(val);
              }
            })
              .fail(function (response) {
                if (window.console && window.console.log) {
                  console.log(response);
                }
              })
              .done(function (response) {

              });
          },
          doneTypingInterval
        );
      });

    // This fixes the Chosen box being 0px wide when the thickbox is opened
    $('#post')
      .on('click', '.rpress-thickbox', function () {
        $('.rpress-select-chosen', '#choose-fooditem')
          .css('width', '100%');
      });

    /**
     * Tools screen JS
     */
    var RPRESS_Tools = {

      init: function () {
        this.revoke_api_key();
        this.regenerate_api_key();
        this.create_api_key();
        this.recount_stats();
      },

      revoke_api_key: function () {
        $(document.body)
          .on('click', '.rpress-revoke-api-key', function (e) {
            return confirm(rpress_vars.revoke_api_key);
          });
      },
      regenerate_api_key: function () {
        $(document.body)
          .on('click', '.rpress-regenerate-api-key', function (e) {
            return confirm(rpress_vars.regenerate_api_key);
          });
      },
      create_api_key: function () {
        $(document.body)
          .on('submit', '#api-key-generate-form', function (e) {
            var input = $('input[type="text"][name="user_id"]');

            input.css('border-color', '#ddd');

            var user_id = input.val();
            if (user_id.length < 1 || user_id == 0) {
              input.css('border-color', '#ff0000');
              return false;
            }
          });
      },
      recount_stats: function () {
        $(document.body)
          .on('change', '#recount-stats-type', function () {

            var export_form = $('#rpress-tools-recount-form');
            var selected_type = $('option:selected', this)
              .data('type');
            var submit_button = $('#recount-stats-submit');
            var products = $('#tools-product-dropdown');

            // Reset the form
            export_form.find('.notice-wrap')
              .remove();
            submit_button.removeClass('button-disabled')
              .attr('disabled', false);
            products.hide();
            $('.rpress-recount-stats-descriptions span')
              .hide();

            if ('recount-fooditem' === selected_type) {

              products.show();
              products.find('.rpress-select-chosen')
                .css('width', 'auto');

            } else if ('reset-stats' === selected_type) {

              export_form.append('<div class="notice-wrap"></div>');
              var notice_wrap = export_form.find('.notice-wrap');
              notice_wrap.html('<div class="notice notice-warning"><p><input type="checkbox" id="confirm-reset" name="confirm_reset_store" value="1" /> <label for="confirm-reset">' + rpress_vars.reset_stats_warn + '</label></p></div>');

              $('#recount-stats-submit')
                .addClass('button-disabled')
                .attr('disabled', 'disabled');

            } else {

              products.hide();
              products.val(0);

            }

            $('#' + selected_type)
              .show();
          });

        $(document.body)
          .on('change', '#confirm-reset', function () {
            var checked = $(this)
              .is(':checked');
            if (checked) {
              $('#recount-stats-submit')
                .removeClass('button-disabled')
                .removeAttr('disabled');
            } else {
              $('#recount-stats-submit')
                .addClass('button-disabled')
                .attr('disabled', 'disabled');
            }
          });

        $('#rpress-tools-recount-form')
          .submit(function (e) {
            var selection = $('#recount-stats-type')
              .val();
            var export_form = $(this);
            var selected_type = $('option:selected', this)
              .data('type');


            if ('reset-stats' === selected_type) {
              var is_confirmed = $('#confirm-reset')
                .is(':checked');
              if (is_confirmed) {
                return true;
              } else {
                has_errors = true;
              }
            }

            export_form.find('.notice-wrap')
              .remove();

            export_form.append('<div class="notice-wrap"></div>');
            var notice_wrap = export_form.find('.notice-wrap');
            var has_errors = false;

            if (null === selection || 0 === selection) {
              // Needs to pick a method rpress_vars.batch_export_no_class
              notice_wrap.html('<div class="updated error"><p>' + rpress_vars.batch_export_no_class + '</p></div>');
              has_errors = true;
            }

            if ('recount-fooditem' === selected_type) {

              var selected_fooditem = $('select[name="fooditem_id"]')
                .val();
              if (selected_fooditem == 0) {
                // Needs to pick fooditem rpress_vars.batch_export_no_reqs
                notice_wrap.html('<div class="updated error"><p>' + rpress_vars.batch_export_no_reqs + '</p></div>');
                has_errors = true;
              }

            }

            if (has_errors) {
              export_form.find('.button-disabled')
                .removeClass('button-disabled');
              return false;
            }
          });
      },
    };
    RPRESS_Tools.init();

    /**
     * Export screen JS
     */
    var RPRESS_Export = {

      init: function () {
        this.submit();
        this.dismiss_message();
      },

      submit: function () {

        var self = this;

        $(document.body)
          .on('submit', '.rpress-export-form', function (e) {
            e.preventDefault();

            var submitButton = $(this)
              .find('input[type="submit"]');

            if (!submitButton.hasClass('button-disabled')) {

              var data = $(this)
                .serialize();

              submitButton.addClass('button-disabled');
              $(this)
                .find('.notice-wrap')
                .remove();
              $(this)
                .append('<div class="notice-wrap"><span class="spinner is-active"></span><div class="rpress-progress"><div></div></div></div>');

              // start the process
              self.process_step(1, data, self);

            }

          });
      },

      process_step: function (step, data, self) {

        $.ajax({
          type: 'POST',
          url: ajaxurl,
          data: {
            form: data,
            action: 'rpress_do_ajax_export',
            step: step,
          },
          dataType: "json",
          success: function (response) {
            if ('done' == response.step || response.error || response.success) {

              // We need to get the actual in progress form, not all forms on the page
              var export_form = $('.rpress-export-form')
                .find('.rpress-progress')
                .parent()
                .parent();
              var notice_wrap = export_form.find('.notice-wrap');

              export_form.find('.button-disabled')
                .removeClass('button-disabled');

              if (response.error) {

                var error_message = response.message;
                notice_wrap.html('<div class="updated error"><p>' + error_message + '</p></div>');

              } else if (response.success) {

                var success_message = response.message;
                notice_wrap.html('<div id="rpress-batch-success" class="updated notice is-dismissible"><p>' + success_message + '<span class="notice-dismiss"></span></p></div>');

              } else {

                notice_wrap.remove();
                window.location = response.url;

              }

            } else {
              $('.rpress-progress div')
                .animate({
                  width: response.percentage + '%',
                }, 50, function () {
                  // Animation complete.
                });
              self.process_step(parseInt(response.step), data, self);
            }

          }
        })
          .fail(function (response) {
            if (window.console && window.console.log) {
              console.log(response);
            }
          });

      },

      dismiss_message: function () {
        $(document.body)
          .on('click', '#rpress-batch-success .notice-dismiss', function () {
            $('#rpress-batch-success')
              .parent()
              .slideUp('fast');
          });
      }

    };
    RPRESS_Export.init();

    /**
     * Import screen JS
     */
    var RPRESS_Import = {

      init: function () {
        this.submit();
      },

      submit: function () {

        var self = this;

        $('.rpress-import-form')
          .ajaxForm({
            beforeSubmit: self.before_submit,
            success: self.success,
            complete: self.complete,
            dataType: 'json',
            error: self.error
          });

      },

      before_submit: function (arr, $form, options) {

        $form.find('.notice-wrap')
          .remove();
        $form.append('<div class="notice-wrap"><span class="spinner is-active"></span><div class="rpress-progress"><div></div></div></div>');

        //check whether client browser fully supports all File API
        if (window.File && window.FileReader && window.FileList && window.Blob) {

          // HTML5 File API is supported by browser

        } else {

          var import_form = $('.rpress-import-form')
            .find('.rpress-progress')
            .parent()
            .parent();
          var notice_wrap = import_form.find('.notice-wrap');

          import_form.find('.button-disabled')
            .removeClass('button-disabled');

          //Error for older unsupported browsers that doesn't support HTML5 File API
          notice_wrap.html('<div class="update error"><p>' + rpress_vars.unsupported_browser + '</p></div>');
          return false;

        }

      },

      success: function (responseText, statusText, xhr, $form) { },

      complete: function (xhr) {

        var response = jQuery.parseJSON(xhr.responseText);

        if (response.success) {

          var $form = $('.rpress-import-form .notice-wrap')
            .parent();

          $form.find('.rpress-import-file-wrap,.notice-wrap')
            .remove();

          $form.find('.rpress-import-options')
            .slideDown();

          // Show column mapping
          var select = $form.find('select.rpress-import-csv-column');
          var row = select.parents('tr')
            .first();
          var options = '';

          var columns = response.data.columns.sort(function (a, b) {
            if (a < b) return -1;
            if (a > b) return 1;
            return 0;
          });

          $.each(columns, function (key, value) {
            options += '<option value="' + value + '">' + value + '</option>';
          });

          select.append(options);

          select.on('change', function () {
            var $key = $(this)
              .val();

            if (!$key) {

              $(this)
                .parent()
                .next()
                .html('');

            } else {

              if (false != response.data.first_row[$key]) {
                $(this)
                  .parent()
                  .next()
                  .html(response.data.first_row[$key]);
              } else {
                $(this)
                  .parent()
                  .next()
                  .html('');
              }

            }

          });

          $.each(select, function () {
            $(this)
              .val($(this)
                .attr('data-field'))
              .change();
          });

          $(document.body)
            .on('click', '.rpress-import-proceed', function (e) {

              e.preventDefault();

              $form.append('<div class="notice-wrap"><span class="spinner is-active"></span><div class="rpress-progress"><div></div></div></div>');

              response.data.mapping = $form.serialize();

              RPRESS_Import.process_step(1, response.data, self);
            });

        } else {

          RPRESS_Import.error(xhr);

        }

      },

      error: function (xhr) {

        // Something went wrong. This will display error on form

        var response = jQuery.parseJSON(xhr.responseText);
        var import_form = $('.rpress-import-form')
          .find('.rpress-progress')
          .parent()
          .parent();
        var notice_wrap = import_form.find('.notice-wrap');

        import_form.find('.button-disabled')
          .removeClass('button-disabled');

        if (response.data.error) {

          notice_wrap.html('<div class="update error"><p>' + response.data.error + '</p></div>');

        } else {

          notice_wrap.remove();

        }
      },

      process_step: function (step, import_data, self) {

        $.ajax({
          type: 'POST',
          url: ajaxurl,
          data: {
            form: import_data.form,
            nonce: import_data.nonce,
            class: import_data.class,
            upload: import_data.upload,
            mapping: import_data.mapping,
            action: 'rpress_do_ajax_import',
            step: step,
          },
          dataType: "json",
          success: function (response) {

            if ('done' == response.data.step || response.data.error) {

              // We need to get the actual in progress form, not all forms on the page
              var import_form = $('.rpress-import-form')
                .find('.rpress-progress')
                .parent()
                .parent();
              var notice_wrap = import_form.find('.notice-wrap');

              import_form.find('.button-disabled')
                .removeClass('button-disabled');

              if (response.data.error) {

                notice_wrap.html('<div class="update error"><p>' + response.data.error + '</p></div>');

              } else {

                import_form.find('.rpress-import-options')
                  .hide();
                $('html, body')
                  .animate({
                    scrollTop: import_form.parent()
                      .offset()
                      .top
                  }, 500);

                notice_wrap.html('<div class="updated"><p>' + response.data.message + '</p></div>');

              }

            } else {

              $('.rpress-progress div')
                .animate({
                  width: response.data.percentage + '%',
                }, 50, function () {
                  // Animation complete.
                });

              RPRESS_Import.process_step(parseInt(response.data.step), import_data, self);
            }

          }
        })
          .fail(function (response) {
            if (window.console && window.console.log) {
              console.log(response);
            }
          });

      }

    };
    RPRESS_Import.init();

    /**
     * Customer management screen JS
     */
    var RPRESS_Customer = {

      vars: {
        customer_card_wrap_editable: $('.rpress-customer-card-wrapper .editable'),
        customer_card_wrap_edit_item: $('.rpress-customer-card-wrapper .edit-item'),
        user_id: $('input[name="customerinfo[user_id]"]'),
        state_input: $(':input[name="customerinfo[state]"]'),
        note: $('#customer-note'),
      },
      init: function () {
        this.edit_customer();
        this.add_email();
        this.user_search();
        this.remove_user();
        this.cancel_edit();
        this.change_country();
        this.add_note();
        this.delete_checked();
      },
      edit_customer: function () {
        $(document.body)
          .on('click', '#edit-customer', function (e) {
            e.preventDefault();

            RPRESS_Customer.vars.customer_card_wrap_editable.hide();
            RPRESS_Customer.vars.customer_card_wrap_edit_item.fadeIn()
              .css('display', 'block');
          });
      },
      add_email: function () {
        $(document.body)
          .on('click', '#add-customer-email', function (e) {
            e.preventDefault();
            var button = $(this);
            var wrapper = button.parent();

            wrapper.parent()
              .find('.notice-container')
              .remove();
            wrapper.find('.spinner')
              .css('visibility', 'visible');
            button.attr('disabled', true);

            var customer_id = wrapper.find('input[name="customer-id"]')
              .val();
            var email = wrapper.find('input[name="additional-email"]')
              .val();
            var primary = wrapper.find('input[name="make-additional-primary"]')
              .is(':checked');
            var nonce = wrapper.find('input[name="add_email_nonce"]')
              .val();

            var postData = {
              rpress_action: 'customer-add-email',
              customer_id: customer_id,
              email: email,
              primary: primary,
              _wpnonce: nonce,
            };

            $.post(ajaxurl, postData, function (response) {

              if (true === response.success) {
                window.location.href = response.redirect;
              } else {
                button.attr('disabled', false);
                wrapper.after('<div class="notice-container"><div class="notice notice-error inline"><p>' + response.message + '</p></div></div>');
                wrapper.find('.spinner')
                  .css('visibility', 'hidden');
              }

            }, 'json');

          });
      },
      user_search: function () {
        // Upon selecting a user from the dropdown, we need to update the User ID
        $(document.body)
          .on('click.rpressSelectUser', '.rpress_user_search_results a', function (e) {
            e.preventDefault();
            var user_id = $(this)
              .data('userid');
            RPRESS_Customer.vars.user_id.val(user_id);
          });
      },
      remove_user: function () {
        $(document.body)
          .on('click', '#disconnect-customer', function (e) {

            e.preventDefault();

            if (confirm(rpress_vars.disconnect_customer)) {

              var customer_id = $('input[name="customerinfo[id]"]')
                .val();

              var postData = {
                rpress_action: 'disconnect-userid',
                customer_id: customer_id,
                _wpnonce: $('#edit-customer-info #_wpnonce')
                  .val()
              };

              $.post(ajaxurl, postData, function (response) {

                window.location.href = window.location.href;

              }, 'json');
            }

          });
      },
      cancel_edit: function () {
        $(document.body)
          .on('click', '#rpress-edit-customer-cancel', function (e) {
            e.preventDefault();
            RPRESS_Customer.vars.customer_card_wrap_edit_item.hide();
            RPRESS_Customer.vars.customer_card_wrap_editable.show();

            $('.rpress_user_search_results')
              .html('');
          });
      },
      change_country: function () {
        $('select[name="customerinfo[country]"]')
          .change(function () {
            var $this = $(this);
            var data = {
              action: 'rpress_get_states',
              country: $this.val(),
              field_name: 'customerinfo[state]'
            };
            $.post(ajaxurl, data, function (response) {
              if ('nostates' == response) {
                RPRESS_Customer.vars.state_input.replaceWith('<input type="text" name="' + data.field_name + '" value="" class="rpress-edit-toggles medium-text"/>');
              } else {
                RPRESS_Customer.vars.state_input.replaceWith(response);
              }
            });

            return false;
          });
      },
      add_note: function () {
        $(document.body)
          .on('click', '#add-customer-note', function (e) {
            e.preventDefault();
            var postData = {
              rpress_action: 'add-customer-note',
              customer_id: $('#customer-id')
                .val(),
              customer_note: RPRESS_Customer.vars.note.val(),
              add_customer_note_nonce: $('#add_customer_note_nonce')
                .val()
            };

            if (postData.customer_note) {

              $.ajax({
                type: "POST",
                data: postData,
                url: ajaxurl,
                success: function (response) {
                  $('#rpress-customer-notes')
                    .prepend(response);
                  $('.rpress-no-customer-notes')
                    .hide();
                  RPRESS_Customer.vars.note.val('');
                }
              })
                .fail(function (data) {
                  if (window.console && window.console.log) {
                    console.log(data);
                  }
                });

            } else {
              var border_color = RPRESS_Customer.vars.note.css('border-color');
              RPRESS_Customer.vars.note.css('border-color', 'red');
              setTimeout(function () {
                RPRESS_Customer.vars.note.css('border-color', border_color);
              }, 500);
            }
          });
      },
      delete_checked: function () {
        $('#rpress-customer-delete-confirm')
          .change(function () {
            var records_input = $('#rpress-customer-delete-records');
            var submit_button = $('#rpress-delete-customer');

            if ($(this)
              .prop('checked')) {
              records_input.attr('disabled', false);
              submit_button.attr('disabled', false);
            } else {
              records_input.attr('disabled', true);
              records_input.prop('checked', false);
              submit_button.attr('disabled', true);
            }
          });
      }

    };
    RPRESS_Customer.init();

    // AJAX user search
    $('.rpress-ajax-user-search')
      .keyup(function () {
        var user_search = $(this)
          .val();
        var exclude = '';

        if ($(this)
          .data('exclude')) {
          exclude = $(this)
            .data('exclude');
        }

        $('.rpress-ajax')
          .show();
        var data = {
          action: 'rpress_search_users',
          user_name: user_search,
          exclude: exclude
        };

        document.body.style.cursor = 'wait';

        $.ajax({
          type: "POST",
          data: data,
          dataType: "json",
          url: ajaxurl,
          success: function (search_response) {

            $('.rpress-ajax')
              .hide();
            $('.rpress_user_search_results')
              .removeClass('hidden');
            $('.rpress_user_search_results span')
              .html('');
            $(search_response.results)
              .appendTo('.rpress_user_search_results span');
            document.body.style.cursor = 'default';
          }
        });
      });

    $(document.body)
      .on('click.rpressSelectUser', '.rpress_user_search_results span a', function (e) {
        e.preventDefault();
        var login = $(this)
          .data('login');
        $('.rpress-ajax-user-search')
          .val(login);
        $('.rpress_user_search_results')
          .addClass('hidden');
        $('.rpress_user_search_results span')
          .html('');
      });

    $(document.body)
      .on('click.rpressCancelUserSearch', '.rpress_user_search_results a.rpress-ajax-user-cancel', function (e) {
        e.preventDefault();
        $('.rpress-ajax-user-search')
          .val('');
        $('.rpress_user_search_results')
          .addClass('hidden');
        $('.rpress_user_search_results span')
          .html('');
      });

    if ($('#rpress_dashboard_sales')
      .length) {
      $.ajax({
        type: "GET",
        data: {
          action: 'rpress_load_dashboard_widget'
        },
        url: ajaxurl,
        success: function (response) {
          $('#rpress_dashboard_sales .inside')
            .html(response);
        }
      });
    }

    $(document.body)
      .on('keydown', '.customer-note-input', function (e) {
        if (e.keyCode == 13 && (e.metaKey || e.ctrlKey)) {
          $('#add-customer-note')
            .click();
        }
      });

  });

// Graphing Helper Functions
var rpressFormatCurrency = function (value) {
  // Convert the value to a floating point number in case it arrives as a string.
  var numeric = parseFloat(value);
  // Specify the local currency.
  var storeCurrency = rpress_vars.currency;
  var decimalPlaces = rpress_vars.currency_decimals;
  return numeric.toLocaleString(storeCurrency, {
    style: 'currency',
    currency: storeCurrency,
    minimumFractionDigits: decimalPlaces,
    maximumFractionDigits: decimalPlaces
  });
}

var rpressFormatNumber = function (value) {
  // Convert the value to a floating point number in case it arrives as a string.
  var numeric = parseFloat(value);
  // Specify the local currency.
  var storeCurrency = rpress_vars.currency;
  var decimalPlaces = rpress_vars.currency_decimals;
  return numeric.toLocaleString(storeCurrency, {
    style: 'decimal',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  });
}

var rpressLabelFormatter = function (label, series) {
  return '<div style="font-size:12px; text-align:center; padding:2px">' + label + '</div>';
}

var rpressLegendFormatterSales = function (label, series) {
  var slug = label.toLowerCase()
    .replace(/\s/g, '-');
  var color = '<div class="rpress-legend-color" style="background-color: ' + series.color + '"></div>';
  var value = '<div class="rpress-pie-legend-item">' + label + ': ' + Math.round(series.percent) + '% (' + rpressFormatNumber(series.data[0][1]) + ')</div>';
  var item = '<div id="' + series.rpress_vars.id + slug + '" class="rpress-legend-item-wrapper">' + color + value + '</div>';

  jQuery('#rpress-pie-legend-' + series.rpress_vars.id)
    .append(item);
  return item;
}

var rpressLegendFormatterEarnings = function (label, series) {
  var slug = label.toLowerCase()
    .replace(/\s/g, '-');
  var color = '<div class="rpress-legend-color" style="background-color: ' + series.color + '"></div>';
  var value = '<div class="rpress-pie-legend-item">' + label + ': ' + Math.round(series.percent) + '% (' + rpressFormatCurrency(series.data[0][1]) + ')</div>';
  var item = '<div id="' + series.rpress_vars.id + slug + '" class="rpress-legend-item-wrapper">' + color + value + '</div>';

  jQuery('#rpress-pie-legend-' + series.rpress_vars.id)
    .append(item);
  return item;
}

jQuery(document)
  .on('change', '.rpress-get-variable-prices input[type=radio]', function () {
    var selectedPrice = jQuery(this)
      .val();
    jQuery(this)
      .parents('.rpress-get-variable-prices')
      .find('.rpress_selected_price')
      .val(selectedPrice);
  });

//Get addon items in the admin order
function rpress_get_addon_items_list(fooditem_id, clone) {

  if (parseInt(fooditem_id) > 0) {

    var Options;
    var postData = {
      action: 'rpress_admin_order_addon_items',
      fooditem_id: fooditem_id,
      security: rpress_vars.load_admin_addon_nonce,
    };

    clone.find('select')
      .html();

    jQuery.ajax({
      type: "POST",
      data: postData,
      url: ajaxurl,
      success: function (response) {
        if (response !== undefined) {
          clone.find('select.addon-items-list')
            .html(response);
          clone.find('div.chosen-container')
            .last()
            .remove();
          clone.find('select')
            .chosen();
        }
      },
    });
  }
}

function rpress_attach_tooltips(selector) {
  // Tooltips
  selector.tooltip({
    content: function () {
      return jQuery(this)
        .prop('title');
    },
    tooltipClass: 'rpress-ui-tooltip',
    position: {
      my: 'center top',
      at: 'center bottom+10',
      collision: 'flipfit'
    },
    hide: {
      duration: 200
    },
    show: {
      duration: 200
    }
  });
}

jQuery(function ($) {
  if (rpress_vars.is_admin == 1 && rpress_vars.enable_order_notification == 1) {
    if (typeof Notification !== "undefined") {
      Notification.requestPermission()
        .then(function (result) {
          if (result === 'denied') {
            console.log('Permission wasn\'t granted. Allow a retry.');
            return;
          }

          if (result === 'default') {
            console.log('The permission request was dismissed.');
            return;
          }

          setInterval(function () {
            $.ajax({
              type: 'POST',
              data: {
                action: 'rpress_check_new_orders'
              },
              url: ajaxurl,
              success: function (response) {
                if (response != '0') {

                  if (typeof response.title === "undefined") return;

                  var notifyTitle = response.title;
                  var options = {
                    body: response.body,
                    icon: response.icon,
                    sound: response.sound,
                  };
                  var n = new Notification(notifyTitle, options);
                  n.custom_options = {
                    url: response.url,
                  }
                  n.onclick = function (event) {
                    event.preventDefault(); // prevent the browser from focusing the Notification's tab
                    window.open(n.custom_options.url, '_blank');
                  };

                  //add audio notify because, this property is not currently supported in any browser.
                  if (response.sound != '') {
                    var loopsound = '1' == rpress_vars.loopsound ? 'loop' : '';
                    $("<audio controls " + loopsound + " class='rpress_notify_audio'></audio>")
                      .attr({
                        'src': response.sound,
                      })
                      .appendTo("body");
                    $('.rpress_notify_audio')
                      .trigger("play");
                  }

                  //set time to notify is show
                  var time_notify = parseInt(rpress_vars.notification_duration);
                  if (time_notify > 0) {
                    time_notify = time_notify * 1000;
                    setTimeout(n.close.bind(n), time_notify);
                  }

                  n.onclose = function (event) {
                    event.preventDefault();
                    $('.rpress_notify_audio')
                      .remove();
                  };
                }
              },
              complete: function () { }
            });
          }, 10000);
        });
    }
  }
});

jQuery(function($) {

  $( '.restropress-addon-item' ).find( '.rpress-addon-title' ).each(function(){
    $(this).attr('data-search-term', $(this).text().toLowerCase());
  });

  $('#rpress-plugin-search').on('keyup', function(){

    var searchTerm = $(this).val().toLowerCase();
    var DataId = '';
    var SelectedTermId;
    
    $('.restropress-addon-item').hide()
    $('.rpress-addon-title').each(function(index, elem) {
      let result =  $(this).text().match(new RegExp(searchTerm,'gi'));
      if(Array.isArray(result) && result.length >0){
        let id = $(this).parent().find('.rpress-license-field').attr('data-item-id');
        $(`input[data-item-id='${id}']`).parent().parent().parent().parent().parent().show()
      }
  
    });
   });
});