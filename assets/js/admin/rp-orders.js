jQuery(function ($) {
  /**
   * RPOrdersTable class.
   */
  var RPOrdersTable = function () {
    $(document)
      .on('click', '.order-preview:not(.disabled)', this.onPreview);
  };

  /**
   * Order status change by dropdown
   */
  jQuery(document)
    .on('change', '#rpress-payments-filter .rp_order_status', function (e) {
      e.preventDefault();
      var _self = jQuery(this);
      var selectedStatus = _self.val();
      var currentStatus = _self.attr('data-current-status');
      var payment_id = _self.attr('data-payment-id');

      if (selectedStatus !== '') {

        _self.removeClass('rp_current_status_' + currentStatus);
        _self.addClass('rp_current_status_' + selectedStatus);
        _self.attr('data-current-status', selectedStatus);
        _self.parent('td')
          .find('.order-status-loading')
          .addClass('disabled');

        $.ajax({
          url: rp_orders_params.ajax_url,
          data: {
            payment_id: payment_id,
            status: selectedStatus,
            action: 'rpress_update_order_status',
            security: rp_orders_params.order_nonce
          },
          type: 'GET',
          dataType: 'JSON',
          success: function (response) {
            if (response) {
              window.location.href = response.redirect;
            }
          }
        });
      }
    });


  /**
   * Preview an order
   */
  RPOrdersTable.prototype.onPreview = function () {
    var $previewButton = $(this),
      $order_id = $previewButton.data('order-id');

    if ($previewButton.data('order-data')) {
      $(this)
        .RPBackboneModal({
          template: 'rp-modal-view-order',
          variable: $previewButton.data('order-data')
        });
    } else {
      $previewButton.addClass('disabled');

      $.ajax({
        url: rp_orders_params.ajax_url,
        data: {
          order_id: $order_id,
          action: 'rpress_get_order_details',
          security: rp_orders_params.preview_nonce
        },
        type: 'GET',
        success: function (response) {
          $('.order-preview')
            .removeClass('disabled');

          if (response.success) {
            $previewButton.data('order-data', response.data);

            $(this)
              .RPBackboneModal({
                template: 'rp-modal-view-order',
                variable: response.data
              });
          }
        }
      });
    }
    return false;

  };

  /**
   * Init RPOrdersTable.
   */
  new RPOrdersTable();
});