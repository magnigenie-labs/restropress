console.log(rp_scripts);
jQuery(function ($) {
    $(document).on('click', '.rpress-reorder-btn', function (e) {
        e.preventDefault();
        var self = $(this);
        var action = 'rpress_reorder';
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
});
