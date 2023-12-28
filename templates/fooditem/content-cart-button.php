<div class="rpress-price-holder">
	<span class="price">
	
    <?php
  	
    global $post;
    global $rpress_options;

    $price = get_post_meta( $post->ID,'rpress_price', true ) ;
    $variable_pricing = rpress_has_variable_prices( $post->ID );

    $rate = (float) rpress_get_option( 'tax_rate', 0 );
    // Convert to a number we can use
    $item_tax = (float) $price - ( (float) $price / ( ( (float) $rate / 100 ) + 1 ) );
    $include_tax  = rpress_get_option( 'prices_include_tax', true );
    $tax_inc_exc_item_option = rpress_get_option('tax_item', true );

   

    if ( $variable_pricing ) {

      echo  rpress_price_range( $post->ID );

    } else {
 /** 
    * Condition added to show the item price as included or excluded Tax
    * @since 2.9.6
    */

    if( $include_tax == 'yes' && $tax_inc_exc_item_option == 'inc_tax' ) {
        $price = get_post_meta( $post->ID,'rpress_price', true );
      } elseif ( $include_tax == 'yes' && $tax_inc_exc_item_option == 'exc_tax' ) {
        $item_tax = ( float ) $price - ( (float) $price / ( ( (float) $rate / 100 ) + 1 ) );
        $price = $price - $item_tax;
      } elseif ($include_tax == 'no' && $tax_inc_exc_item_option == 'inc_tax') {
        $item_tax = ( float ) $price * ( (float) $rate / 100 );
        $price = ( float ) $price + ( float ) $item_tax;
      } else {
        $price = get_post_meta( $post->ID,'rpress_price', true ) ;
      }
      echo rpress_currency_filter( rpress_format_amount( $price ) );
    }

    ?>

	</span>
	
  <div class="rpress_fooditem_buy_button">
		<?php echo rpress_get_purchase_link( array( 'fooditem_id' => get_the_ID() ) ); ?>
	</div>

</div>