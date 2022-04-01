<?php 
  $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : ''; 

  $payment = get_post( $order_id );

  if( empty( $payment ) ) return;

  $meta           = rpress_get_payment_meta( $payment->ID );
  $service_time   = rpress_get_payment_meta( $payment->ID, '_rpress_delivery_time' );
  $service_date   = rpress_get_payment_meta( $payment->ID, '_rpress_delivery_date', true );
  $cart           = rpress_get_payment_meta_cart_details( $payment->ID, true );
  $discount       = rpress_get_discount_price_by_payment_id( $payment->ID );
  $user           = rpress_get_payment_meta_user_info( $payment->ID );
  $email          = rpress_get_payment_user_email( $payment->ID );
  $payment_status = rpress_get_payment_status( $payment, true );
  $order_status   = rpress_get_order_status( $payment->ID );
  $order_note     = rpress_get_payment_meta( $payment->ID, '_rpress_order_note', true );
  $service_type   = rpress_get_payment_meta( $payment->ID, '_rpress_delivery_type' );
  $service_label  = rpress_service_label( $service_type );
  $phone          = !empty( $meta['phone'] ) ? $meta['phone'] : ( !empty( $user['phone'] ) ? $user['phone'] : '' );
  $firstname      = isset( $user['first_name'] ) ? $user['first_name'] : '';
  $lastname       = isset( $user['last_name'] ) ? $user['last_name'] : '';
  $address_info   = get_post_meta( $payment->ID, '_rpress_delivery_address', true );
  $address        = !empty( $address_info['address'] ) ? $address_info['address'] . ', ' : '';
  $address       .= !empty( $address_info['flat'] ) ? $address_info['flat'] . ', ' : '';
  $address       .= !empty( $address_info['city'] ) ? $address_info['city'] . ', ' : '';
  $address       .= !empty( $address_info['postcode'] ) ? $address_info['postcode']  : '';
?>

    <div>
      <header class="modal__header modal-header">
        <h2 class="modal__title modal-title"><?php esc_html_e( 'Order Details', 'restropress' ) ?>
          <span class="button rpress-status"><?php echo esc_html( $order_status ); ?></span>
        </h2>
        <button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
      </header>
      <main class="modal__content modal-body">
        <div class="rpress-order-details">
          <div class="rp-order-section-md-data">
                <div class="rp-detils-content-view">
                    <p><?php esc_html_e( "Name", 'restropress' ); ?><span><?php echo  esc_html( "$firstname $lastname" ) ?></span></p>
                    <p><?php esc_html_e( "Phone Number", 'restropress' ); ?><span><?php echo esc_html( $phone ) ?></span></p>
                    <?php if( !empty( $address ) ): ?>
                      <p><?php esc_html_e( "Delivery To", 'restropress' ); ?><span><?php echo esc_html( $address ) ?></span></p>
                    <?php endif ;?>
                    <p><?php echo ucfirst( $service_label ) ?> <?php esc_html_e( "Date", 'restropress' ) ?><span><?php echo esc_html( $service_date ) ?></span></p>
                    <p><?php echo ucfirst( $service_label ) ?> <?php esc_html_e( "Time", 'restropress' ) ?><span><?php echo esc_html( $service_time ) ?></span></p>
                    <p><?php esc_html_e( "Payment Type", 'restropress' ) ?><span><?php echo esc_html( rpress_get_gateway_checkout_label( rpress_get_payment_gateway( $payment->ID ) ) ); ?></span></p>
                    <p><?php esc_html_e( "Payment Status", 'restropress' ) ?><span><?php echo esc_html( $payment_status ) ?></span></p>  
                </div>
            </div>
            <hr class="rp-line" style="border-style: dashed;">
            <div class="rp-order-list-main-wrap">
              <h3><?php esc_html_e( "Your Order", 'restropress' ) ?></h3>
              <ul class="rpress-cart">
                <!-- Loop cart data -->
                <?php if( $cart ) :?>
                  <?php foreach( $cart as $key=>$item ): ?>
                    <?php $special_instruction = isset( $item['instruction'] ) ? $item['instruction'] : ''; ?>
                    <li class="rpress-cart-item" data-cart-key="<?php echo esc_html( $key ) ?>">
                      <span class="rpress-cart-item-qty qty-class"><?php echo absint( $item['quantity'] ); ?> <?php esc_html_e( "x", 'restropress' ); ?></span>
                      <span class="rpress-cart-item-title"><?php echo wp_kses_post( rpress_get_cart_item_name( $item ) ) ; ?></span>&nbsp;
                      <span class="cart-item-quantity-wrap">
                        <span class="rpress-cart-item-price qty-class">
                          <?php if( empty( $item['in_bundle'] ) ) :  ?>
                            <?php echo rpress_currency_filter( rpress_format_amount( $item[ 'subtotal' ] ) ); ?>
                          <?php endif; ?>
                        </span>
                      </span>
                      <div class="rp-addons-ht-wrap">
                        <?php $addon_name = []; ?>
                        <?php foreach( $item['item_number']['options'] as $k => $v ) {
                          if( !empty($v['addon_item_name']) ) : ?>
                            <?php array_push($addon_name, $v['addon_item_name']) ; ?>
                          <?php
                           endif;
                        }?>
                        <?php echo implode(",", array_filter($addon_name)); ?>
                       </div>
                       <?php if ( !empty( $special_instruction ) ) : ?>
                        <span class="rpress-special-instruction"><?php echo esc_html( $special_instruction ) ; ?></span>
                      <?php endif ;?>
                    </li>
                  <?php endforeach;?>
                <?php endif ;?>
                <!-- Subtotal -->
                <li class="cart_item rpress-cart-meta rpress_subtotal"><?php esc_html_e( 'Subtotal', 'restropress' ); ?><span class="cart-subtotal"><?php echo rpress_payment_subtotal( $payment->ID ); ?></span></li>
                <!-- Tax -->
                <?php if( rpress_use_taxes() ) : ?>
                  <li class="cart_item rpress-cart-meta rpress_cart_tax"><?php echo rpress_get_tax_name(); ?><span class="cart-tax"><?php echo rpress_payment_tax( $payment->ID ); ?></span></li>
                <?php endif; ?>
                <!-- Fees -->
                <?php if ( ( $fees = rpress_get_payment_fees( $payment->ID, 'fee' ) ) ) :
                  foreach( $fees as $fee ) : ?>
                    <li class="cart_item rpress-cart-meta rpress_fess"><?php echo esc_html( $fee['label'] ); ?><span class="cart-discount"><?php echo rpress_currency_filter( rpress_format_amount( $fee['amount'] ) ); ?></span></li>
                  <?php endforeach; ?>
                <?php endif; ?>
                <!-- Discount -->
                <?php if(isset( $user['discount'] ) && $user['discount'] != 'none' ): ?>
                  <li class="cart_item rpress-cart-meta rpress_user_discount"><?php esc_html_e( 'Coupon', 'restropress' ); ?><span class="cart-discount"><?php echo wp_kses_post( $discount ); ?></span></li>
                <?php endif; ?>
              </ul>
            </div>
        </div>
      </main>
      <footer class="modal__footer modal-footer">
        <?php esc_html_e( 'Total', 'restropress' ); ?>
        <span><?php echo rpress_payment_amount( $payment->ID ); ?></span>
      </footer>
    </div>
  
