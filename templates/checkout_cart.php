<?php
/**
 *  This template is used to display the Checkout page when items are in the cart
 */

global $post;
?>

<table id="rpress_checkout_cart " class="rpress-cart ajaxed">

  <thead>
    <th colspan="3">
      <div class="rpress item-order">
        <h6><?php echo apply_filters( 'rpress_cart_title', __('Your Order', 'restropress' ) ); ?></h6>
      </div>
    </th>
  </thead>

  <tbody>

    <?php $cart_items = rpress_get_cart_contents(); ?>

    <?php do_action( 'rpress_cart_items_before' ); ?>

    <?php if ( $cart_items ) : ?>

      <?php foreach ( $cart_items as $key => $item ) :


        $cart_list_item   = rpress_get_cart_items_by_key($key);
        $cart_item_price  = rpress_get_cart_item_by_price($key);
        $get_item_qty     = rpress_get_item_qty_by_key($key);

        ?>

        <tr class="rpress_cart_item" id="rpress_cart_item_<?php echo esc_attr( $key ) . '_' . esc_attr( $item['id'] ); ?>" data-fooditem-id="<?php echo esc_attr( $item['id'] ); ?>">

          <?php do_action( 'rpress_checkout_table_body_first', $item ); ?>

          <td class="rpress_cart_item_name" colspan="3">

            <?php

            $item_title = rpress_get_cart_item_name( $item );
            $item_options = isset( $item['options'] ) ? $item['options'] : array();
            $item_price = rpress_cart_item_price( $item['id'], $item, $item_options );

            if ( rpress_has_variable_prices( $item['id'] ) ) {
              $price_id = !empty( $item['price_id'] ) ? $item['price_id'] : 0;
              $item_price = rpress_get_price_option_amount( $item['id'], $price_id );
              $item_price = esc_html( rpress_currency_filter( rpress_format_amount( $item_price ) ) );
            } ?>

            <div class="rpress-checkout-item-row">

              <!-- Item Quantity Wrap starts Here -->
              <span class="rpress_checkout_cart_item_qty"><?php echo esc_html( $get_item_qty ) ; ?>&nbsp;x&nbsp;</span>

              <!-- Item Name Here -->
              <span class="rpress-cart-item-title rpress-cart-item rpress_checkout_cart_item_title"><?php echo wp_kses_post( $item_title ); ?></span>

              <!-- Item Price Wrap starts Here -->
              <span class="cart-item-quantity-wrap"><?php echo wp_kses_post( $item_price ); ?></span>

              <?php if( is_array( $cart_list_item ) ) {

                foreach( $cart_list_item as $k => $val ) {

                  if ( empty( $val['quantity'] ) ) {
                    continue;
                  }

                  if( isset( $val['addon_item_name'] ) && isset( $val['price'] ) ) { ?>

                    <!-- Item Row Starts Here -->
                    <div class="rpress-checkout-addon-row">

                      <!-- Item Quantity Wrap starts Here -->
                      <span class="rpress_checkout_cart_item_qty"><?php echo esc_html( $val['quantity'] ) ; ?>&nbsp;x&nbsp;</span>

                      <!-- Item Title -->
                      <span class="rpress-cart-item-title"><?php echo wp_kses_post( $val['addon_item_name'] ); ?></span>

                      <!-- Item Quanity Starts Here -->
                      <span class="cart-item-quantity-wrap">
                        <?php
                          $cart = new RPRESS_Cart();
                          $addon_id = !empty( $val['addon_id'] ) ? $val['addon_id'] : '';
                          $item_addon_price = !empty( $val['price'] ) ? $val['price'] : 0;
                          $addon_price = $cart->get_addon_price( $addon_id, $item, $item_addon_price );
                        ?>
                        <span class="rpress_checkout_cart_item_qty"><?php echo esc_html( rpress_currency_filter( rpress_format_amount( $addon_price ) ) ) ;?></span>
                      </span>
                      <!-- Item Quanity Ends Here -->

                      <!--Item Action Here -->
                      <span class="cart-action-wrap addon-items">
                        <a class="rpress_cart_remove_item_btn" href="<?php echo esc_url( rpress_remove_item_url( $key ) ); ?>"></a>
                      </span>
                    </div>
                    <!-- Item Row Ends Here -->

                  <?php }
                }
              }

              if( isset($item['instruction']) && !empty($item['instruction']) ) { ?>
                <div class="special-instruction-wrapper">
                  <span class="restro-instruction"><?php echo wp_kses_post( $item['instruction'] ); ?></span>
                </div>

              <?php }

              /**
               * Runs after the item in cart's title is echoed
               * @since 1.0.0
               *
               * @param array $item Cart Item
               * @param int $key Cart key
               */
              do_action( 'rpress_checkout_cart_item_title_after', $item, $key ); ?>

              <!-- Item Action Here -->
              <?php do_action( 'rpress_cart_actions', $item, $key ); ?>

              <a class="rpress_cart_remove_item_btn" href="<?php echo esc_url( rpress_remove_item_url( $key ) ); ?>"><?php esc_html_e( 'Remove', 'restropress' ) ?></a>

            </div>

          </td>

        <?php do_action( 'rpress_checkout_table_body_last', $item ); ?>

        </tr>

      <?php endforeach; ?>

    <?php endif; ?>

    <?php do_action( 'rpress_cart_items_middle' ); ?>

    <tr>
      <th colspan="3" class="rpress_get_subtotal">
        <?php esc_html_e( 'Subtotal', 'restropress' ); ?>:&nbsp;<span class="rpress_cart_subtotal_amount pull-right"><?php echo rpress_cart_subtotal(); ?></span>
      </th>
    </tr>

    <?php do_action( 'rpress_cart_items_after' ); ?>

  </tbody>

  <tfoot>

    <?php if( rpress_use_taxes() && ! rpress_prices_include_tax() ) : ?>
      <tr class="rpress_cart_footer_row rpress_cart_subtotal_row"<?php if ( ! rpress_is_cart_taxed() ) echo wp_kses_post( ' style="display:none;"' ); ?>>
        <?php do_action( 'rpress_checkout_table_subtotal_first' ); ?>
        <th colspan="<?php echo rpress_checkout_cart_columns(); ?>" class="rpress_cart_subtotal">
        </th>
        <?php do_action( 'rpress_checkout_table_subtotal_last' ); ?>
      </tr>
    <?php endif; ?>

    <tr class="rpress_cart_footer_row rpress_cart_discount_row" <?php if( ! rpress_cart_has_discounts() )  echo wp_kses_post( ' style="display:none;"' ); ?>>
      <?php do_action( 'rpress_checkout_table_discount_first' ); ?>
      <th colspan="<?php echo rpress_checkout_cart_columns(); ?>" class="rpress_cart_discount">
        <?php rpress_cart_discounts_html(); ?>
      </th>
      <?php do_action( 'rpress_checkout_table_discount_last' ); ?>
    </tr>

    <?php if( rpress_use_taxes() ) : ?>
      <?php do_action( 'rpress_checkout_table_tax_first' ); ?>
      <tr class="rpress_cart_footer_row test rpress_cart_tax_row"<?php if( ! rpress_is_cart_taxed() ) echo wp_kses_post( ' style="display:none;"' ); ?>>
        <th colspan="<?php echo rpress_checkout_cart_columns(); ?>" class="rpress_cart_tax">
          <span class="rpress-tax pull-left"><?php echo rpress_get_tax_name(); ?>:&nbsp;</span>
          <span class="rpress_cart_tax_amount pull-right" data-tax="<?php echo rpress_get_cart_tax( false ); ?>"><?php echo esc_html( rpress_cart_tax() ); ?></span>
        </th>
      </tr>
      <?php do_action( 'rpress_checkout_table_tax_last' ); ?>
    <?php endif; ?>

    <!-- Show any cart fees, both positive and negative fees -->
    <?php if( rpress_cart_has_fees() ) : ?>
      <?php foreach( rpress_get_cart_fees() as $fee_id => $fee ) : ?>
        <tr class="rpress_cart_fee" id="rpress_cart_fee_<?php echo esc_attr( $fee_id ); ?>">

          <?php do_action( 'rpress_cart_fee_rows_before', $fee_id, $fee ); ?>

          <th colspan="3" class="rpress_cart_fee_label">
            <?php echo esc_html( $fee['label'] ); ?>
            <span style="float:right">
              <?php echo esc_html( rpress_currency_filter( rpress_format_amount( $fee['amount'] ) ) ); ?>

              <?php if( ! empty( $fee['type'] ) && 'item' == $fee['type'] ) : ?>
              <a href="<?php echo esc_url( rpress_remove_cart_fee_url( $fee_id ) ); ?>"><?php esc_html_e( 'Remove', 'restropress' ); ?></a>
            <?php endif; ?>

            </span>
          </th>

          <?php do_action( 'rpress_cart_fee_rows_after', $fee_id, $fee ); ?>

        </tr>
      <?php endforeach; ?>
    <?php endif; ?>

    <tr class="rpress_cart_footer_row">
      <?php do_action( 'rpress_checkout_table_footer_first' ); ?>
      <th colspan="<?php echo rpress_checkout_cart_columns(); ?>" class="rpress_cart_total"><?php esc_html_e( 'Total', 'restropress' ); ?>: <span class="rpress_cart_amount pull-right" data-subtotal="<?php echo rpress_get_cart_subtotal(); ?>" data-total="<?php echo rpress_get_cart_total(); ?>"><?php rpress_cart_total(); ?></span>
        <?php echo get_delivery_options( true ); ?>
      </th>
      <?php do_action( 'rpress_checkout_table_footer_last' ); ?>
    </tr>

    <?php if( has_action( 'rpress_cart_footer_buttons' ) ) : ?>
      <tr class="rpress_cart_footer_row<?php if ( rpress_is_cart_saving_disabled() ) { echo ' rpress-no-js'; } ?>">
        <th colspan="<?php echo rpress_checkout_cart_columns(); ?>">
          <?php do_action( 'rpress_cart_footer_buttons' ); ?>
        </th>
      </tr>
    <?php endif; ?>

  </tfoot>
</table>