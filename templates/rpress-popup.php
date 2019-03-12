<?php
$color = rpress_get_option( 'checkout_color', 'red' );
?>
<!-- Start Bootstrap Modal -->
<div class="modal fade " id="rpressModal" tabindex="-1" role="dialog" aria-labelledby="rpressModal" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body"></div>
      <div class="modal-footer">
        <div class="rpress-popup-actions edit-rpress-popup-actions">
          <div class="col-md-6 col-sm-12 btn-count">
            <div class="col-md-4 col-xs-3 col-sm-3">
              <input type="button" value="&#8722;" class="qtyminus qtyminus-style qtyminus-style-edit" field="quantity"/>
            </div>
            <div class="col-md-3 col-xs-3  col-sm-4 md-4-mar-lft">
              <input type="text" name="quantity" value="1" class="qty qty-style">
            </div>
            <div class="col-md-4 col-xs-3 col-sm-3  plus-symb">
              <input type="button" value="&#43;" class="qtyplus col-md-3 qtyplus-style qtyplus-style-edit" field="quantity">
            </div>
          </div>

          <div class="col-md-6 col-sm-12">
            <a data-item-qty="1" data-cart-key="" data-item-id="" data-item-price="" data-cart-action="" class="center submit-fooditem-button text-center inline col-md-6 <?php echo $color; ?>"></a>
          </div>
          
        </div>
      </div>
    </div>
  </div>
</div>
</div>
<!-- End Bootstrap Modal -->
