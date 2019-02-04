<?php
/**
 *  This template is used to display the Checkout page when items are in the cart
 */

global $post; ?>
<table id="rpress_checkout_cart" <?php if ( ! rpress_is_ajax_disabled() ) { echo 'class="ajaxed"'; } ?>>
	<thead>
		<tr class="rpress_cart_header_row">
			<?php do_action( 'rpress_checkout_table_header_first' ); ?>
			<th class="rpress_cart_item_name"><?php _e( 'Item Name', 'restro-press' ); ?></th>
			<th class="rpress_cart_item_price"><?php _e( 'Item Price', 'restro-press' ); ?></th>
			<th class="rpress_cart_actions"><?php _e( 'Actions', 'restro-press' ); ?></th>
			<?php do_action( 'rpress_checkout_table_header_last' ); ?>
		</tr>
	</thead>
	<tbody>
		<?php $cart_items = rpress_get_cart_contents(); ?>
		<?php //print_r($cart_items); ?>
		<?php do_action( 'rpress_cart_items_before' ); ?>
		<?php if ( $cart_items ) : ?>
			<?php foreach ( $cart_items as $key => $item ) :
				//print_r($item);
				$cart_list_item = getCartItemsByKey($key);
				$cart_item_price = getCartItemsByPrice($key);
				$get_item_qty = rpress_get_item_qty_by_key($key);
			 ?>
				<tr class="rpress_cart_item" id="rpress_cart_item_<?php echo esc_attr( $key ) . '_' . esc_attr( $item['id'] ); ?>" data-fooditem-id="<?php echo esc_attr( $item['id'] ); ?>">
					<?php do_action( 'rpress_checkout_table_body_first', $item ); ?>
					<td class="rpress_cart_item_name">
						<?php
							if ( current_theme_supports( 'post-thumbnails' ) && has_post_thumbnail( $item['id'] ) ) : 
								echo '<div class="rpress_cart_item_image">';
									echo get_the_post_thumbnail( $item['id'], apply_filters( 'rpress_checkout_image_size', array( 25,25 ) ) );
								echo '</div>';?>
								<?php else :  ?>
								       <div id="item_thumbnail">
								        <img src="<?php echo plugins_url() ;?>/restropress/assets/svg/no_image.png">
								        
								        </div>
								    <?php endif; ?>
							
							<?php
							$item_title = rpress_get_cart_item_name( $item );
							echo '<div class="tb-cart">';
							echo '<span class="rpress_checkout_cart_item_title">' . esc_html( $item_title ) . '</span>';
							echo '&nbsp;X&nbsp;<span class="rpress_checkout_cart_item_qty">'.$get_item_qty.'</span>';

							if( is_array($cart_list_item) ) {
								foreach( $cart_list_item as $k => $val ) {
									if( isset($val['addon_item_name']) && isset($val['price']) && isset($val['quantity']) ) {
									 echo '<span style="display: block;" class="">' . $val['quantity']. ' X ' . $val['addon_item_name'] . '  ' .  esc_html( rpress_currency_filter( rpress_format_amount( $val['price'] ) ) ). '</span>';
									}
								}
							}

							if( isset($item['instruction']) && !empty($item['instruction']) ) {
								echo '<div class="special-instruction-wrapper">';
								echo '<span class="restro-instruction">'.__('Special Instruction', 'restro-press').'</span> : ';
								echo '<p>'.$item['instruction'].'</p></div>';
								echo '</div>';
							}

							/**
							 * Runs after the item in cart's title is echoed
							 * @since 1.0.0
							 *
							 * @param array $item Cart Item
							 * @param int $key Cart key
							 */
							do_action( 'rpress_checkout_cart_item_title_after', $item, $key );
						?>
					</td>
					<td class="rpress_cart_item_price">
						<?php
						//$cart_item_price = $cart_item_price + rpress_cart_item_price( $item['id'], $item['options'] );
						//echo $cart_item_price;
						echo rpress_currency_filter( rpress_format_amount( $cart_item_price ) );

						//echo rpress_cart_item_price( $item['id'], $item['options'] );
						do_action( 'rpress_checkout_cart_item_price_after', $item );
						?>
					</td>
					<td class="rpress_cart_actions">
						<?php if( rpress_item_quantities_enabled() && ! rpress_fooditem_quantities_disabled( $item['id'] ) ) : ?>
							<input type="number" min="1" step="1" name="rpress-cart-fooditem-<?php echo $key; ?>-quantity" data-key="<?php echo $key; ?>" class="rpress-input rpress-item-quantity" value="<?php echo rpress_get_cart_item_quantity( $item['id'], $item['options'] ); ?>"/>
							<input type="hidden" name="rpress-cart-fooditems[]" value="<?php echo $item['id']; ?>"/>
							<input type="hidden" name="rpress-cart-fooditem-<?php echo $key; ?>-options" value="<?php echo esc_attr( json_encode( $item['options'] ) ); ?>"/>
						<?php endif; ?>
						<?php do_action( 'rpress_cart_actions', $item, $key ); ?>
						<a class="rpress_cart_remove_item_btn" href="<?php echo esc_url( rpress_remove_item_url( $key ) ); ?>"><?php _e( '<div class="remove_icon"><img src="'.plugins_url().'/restropress/assets/svg/rubbish-bin.svg"></div>', 'restro-press', 'restro-press' ); ?></a>
					</td>
					<?php do_action( 'rpress_checkout_table_body_last', $item ); ?>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php do_action( 'rpress_cart_items_middle' ); ?>
		<!-- Show any cart fees, both positive and negative fees -->
		<?php if( rpress_cart_has_fees() ) : ?>
			<?php foreach( rpress_get_cart_fees() as $fee_id => $fee ) : ?>
				<tr class="rpress_cart_fee" id="rpress_cart_fee_<?php echo $fee_id; ?>">

					<?php do_action( 'rpress_cart_fee_rows_before', $fee_id, $fee ); ?>

					<td class="rpress_cart_fee_label"><?php echo esc_html( $fee['label'] ); ?></td>
					<td class="rpress_cart_fee_amount"><?php echo esc_html( rpress_currency_filter( rpress_format_amount( $fee['amount'] ) ) ); ?></td>
					<td>
						<?php if( ! empty( $fee['type'] ) && 'item' == $fee['type'] ) : ?>
							<a href="<?php echo esc_url( rpress_remove_cart_fee_url( $fee_id ) ); ?>"><?php _e( 'Remove', 'restro-press' ); ?></a>
						<?php endif; ?>

					</td>

					<?php do_action( 'rpress_cart_fee_rows_after', $fee_id, $fee ); ?>

				</tr>
			<?php endforeach; ?>
		<?php endif; ?>

		<?php do_action( 'rpress_cart_items_after' ); ?>
	</tbody>
	<tfoot>

		<?php if( has_action( 'rpress_cart_footer_buttons' ) ) : ?>
			<tr class="rpress_cart_footer_row<?php if ( rpress_is_cart_saving_disabled() ) { echo ' rpress-no-js'; } ?>">
				<th colspan="<?php echo rpress_checkout_cart_columns(); ?>">
					<?php do_action( 'rpress_cart_footer_buttons' ); ?>
				</th>
			</tr>
		<?php endif; ?>

		<?php if( rpress_use_taxes() && ! rpress_prices_include_tax() ) : ?>
			<tr class="rpress_cart_footer_row rpress_cart_subtotal_row"<?php if ( ! rpress_is_cart_taxed() ) echo ' style="display:none;"'; ?>>
				<?php do_action( 'rpress_checkout_table_subtotal_first' ); ?>
				<th colspan="<?php echo rpress_checkout_cart_columns(); ?>" class="rpress_cart_subtotal">
					<?php _e( 'Subtotal', 'restro-press' ); ?>:&nbsp;<span class="rpress_cart_subtotal_amount"><?php echo rpress_cart_subtotal(); ?></span>
				</th>
				<?php do_action( 'rpress_checkout_table_subtotal_last' ); ?>
			</tr>
		<?php endif; ?>

		<tr class="rpress_cart_footer_row rpress_cart_discount_row" <?php if( ! rpress_cart_has_discounts() )  echo ' style="display:none;"'; ?>>
			<?php do_action( 'rpress_checkout_table_discount_first' ); ?>
			<th colspan="<?php echo rpress_checkout_cart_columns(); ?>" class="rpress_cart_discount">
				<?php rpress_cart_discounts_html(); ?>
			</th>
			<?php do_action( 'rpress_checkout_table_discount_last' ); ?>
		</tr>

		<?php if( rpress_use_taxes() ) : ?>
			<tr class="rpress_cart_footer_row rpress_cart_tax_row"<?php if( ! rpress_is_cart_taxed() ) echo ' style="display:none;"'; ?>>
				<?php do_action( 'rpress_checkout_table_tax_first' ); ?>
				<th colspan="<?php echo rpress_checkout_cart_columns(); ?>" class="rpress_cart_tax">
					<?php _e( 'Tax', 'restro-press' ); ?>:&nbsp;<span class="rpress_cart_tax_amount" data-tax="<?php echo rpress_get_cart_tax( false ); ?>"><?php echo esc_html( rpress_cart_tax() ); ?></span>
				</th>
				<?php do_action( 'rpress_checkout_table_tax_last' ); ?>
			</tr>

		<?php endif; ?>

		<tr class="rpress_cart_footer_row">
			<?php do_action( 'rpress_checkout_table_footer_first' ); ?>
			<th colspan="<?php echo rpress_checkout_cart_columns(); ?>" class="rpress_cart_total"><?php _e( 'Total', 'restro-press' ); ?>: <span class="rpress_cart_amount" data-subtotal="<?php echo rpress_get_cart_subtotal(); ?>" data-total="<?php echo rpress_get_cart_total(); ?>"><?php rpress_cart_total(); ?></span></th>
			<?php do_action( 'rpress_checkout_table_footer_last' ); ?>
		</tr>
	</tfoot>
</table>
