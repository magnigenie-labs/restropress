<?php
/**
 * Metabox Functions
 *
 * @package     RPRESS
 * @subpackage  Admin/RestroPress
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** All RestroPress *****************************************************************/

/**
 * Register all the meta boxes for the RestroPress custom post type
 *
 * @since 1.0
 * @return void
 */
function rpress_add_fooditem_meta_box() {

	$post_types = apply_filters( 'rpress_fooditem_metabox_post_types' , array( 'fooditem' ) );

	foreach ( $post_types as $post_type ) {

		/** Product Prices **/
		add_meta_box( 'rpress_product_prices', sprintf( __( '%1$s Prices', 'restropress' ), rpress_get_label_singular(), rpress_get_label_plural() ),  'rpress_render_fooditem_meta_box', $post_type, 'normal', 'high' );

		/** Product Notes */
		// add_meta_box( 'rpress_product_notes', sprintf( __( '%1$s Notes', 'restropress' ), rpress_get_label_singular(), rpress_get_label_plural() ), 'rpress_render_product_notes_meta_box', $post_type, 'normal', 'high' );

	}
}
add_action( 'add_meta_boxes', 'rpress_add_fooditem_meta_box' );

/**
 * Returns default RPRESS RestroPress meta fields.
 *
 * @since  1.0.0
 * @return array $fields Array of fields.
 */
function rpress_fooditem_metabox_fields() {

	$fields = array(
			'_rpress_product_type',
			'rpress_price',
			'_variable_pricing',
			'_rpress_price_options_mode',
			'rpress_variable_prices',
			'rpress_fooditem_files',
			'_rpress_purchase_text',
			'_rpress_purchase_style',
			'_rpress_purchase_color',
			'_rpress_bundled_products',
			'_rpress_hide_purchase_link',
			'_rpress_fooditem_tax_exclusive',
			'_rpress_button_behavior',
			'_rpress_quantities_disabled',
			'rpress_product_notes',
			'_rpress_default_price_id',
			'_rpress_bundled_products_conditions'
		);

	if ( current_user_can( 'manage_shop_settings' ) ) {
		$fields[] = '_rpress_fooditem_limit';
	}

	if ( rpress_use_skus() ) {
		$fields[] = 'rpress_sku';
	}

	return apply_filters( 'rpress_metabox_fields_save', $fields );
}

/**
 * Save post meta when the save_post action is called
 *
 * @since 1.0
 * @param int $post_id RestroPress (Post) ID
 * @global array $post All the data of the the current post
 * @return void
 */
function rpress_fooditem_meta_box_save( $post_id, $post ) {

	if ( ! isset( $_POST['rpress_fooditem_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['rpress_fooditem_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return;
	}

	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return;
	}

	if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
		return;
	}

	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	// The default fields that get saved
	$fields = rpress_fooditem_metabox_fields();

	foreach ( $fields as $field ) {

		// Accept blank or "0"
		if ( '_rpress_fooditem_limit' == $field ) {
			if ( ! empty( $_POST[ $field ] ) || ( isset( $_POST[ $field ] ) && strlen( $_POST[ $field ] ) === 0 ) || ( isset( $_POST[ $field ] ) && "0" === $_POST[ $field ] ) ) {

				$global_limit = rpress_get_option( 'file_fooditem_limit' );
				$new_limit    = apply_filters( 'rpress_metabox_save_' . $field, $_POST[ $field ] );

				// Only update the new limit if it is not the same as the global limit
				if( $global_limit == $new_limit ) {

					delete_post_meta( $post_id, '_rpress_fooditem_limit' );

				} else {

					update_post_meta( $post_id, '_rpress_fooditem_limit', $new_limit );

				}
			}

		} elseif ( '_rpress_default_price_id' == $field && rpress_has_variable_prices( $post_id ) ) {

			if ( isset( $_POST[ $field ] ) ) {
				$new_default_price_id = ( ! empty( $_POST[ $field ] ) && is_numeric( $_POST[ $field ] ) ) || ( 0 === (int) $_POST[ $field ] ) ? (int) $_POST[ $field ] : 1;
			} else {
				$new_default_price_id = 1;
			}

			update_post_meta( $post_id, $field, $new_default_price_id );

		} else {

			if ( ! empty( $_POST[ $field ] ) ) {
				$new = apply_filters( 'rpress_metabox_save_' . $field, $_POST[ $field ] );
				update_post_meta( $post_id, $field, $new );
			} else {
				delete_post_meta( $post_id, $field );
			}
		}

	}

	if ( rpress_has_variable_prices( $post_id ) ) {
		$lowest = rpress_get_lowest_price_option( $post_id );
		update_post_meta( $post_id, 'rpress_price', $lowest );
	}

	do_action( 'rpress_save_fooditem', $post_id, $post );
}

add_action( 'save_post', 'rpress_fooditem_meta_box_save', 10, 2 );

/**
 * Sanitize bundled products on save
 *
 * Ensures a user doesn't try and include a product's ID in the products bundled with that product
 *
 * @since  1.0.0
 *
 * @param array $products
 * @return array
 */
function rpress_sanitize_bundled_products_save( $products = array() ) {

	global $post;

	$self = array_search( $post->ID, $products );

	if( $self !== false )
		unset( $products[ $self ] );

	return array_values( array_unique( $products ) );
}
add_filter( 'rpress_metabox_save__rpress_bundled_products', 'rpress_sanitize_bundled_products_save' );

/**
 * Don't save blank rows.
 *
 * When saving, check the price and file table for blank rows.
 * If the name of the price or file is empty, that row should not
 * be saved.
 *
 * @since  1.0.0
 * @param array $new Array of all the meta values
 * @return array $new New meta value with empty keys removed
 */
function rpress_metabox_save_check_blank_rows( $new ) {
	foreach ( $new as $key => $value ) {
		if ( empty( $value['name'] ) && empty( $value['amount'] ) && empty( $value['file'] ) )
			unset( $new[ $key ] );
	}

	return $new;
}

/** RestroPress Configuration *****************************************************************/

/**
 * RestroPress Metabox
 *
 * Extensions (as well as the core plugin) can add items to the main fooditem
 * configuration metabox via the `rpress_meta_box_fields` action.
 *
 * @since 1.0
 * @return void
 */
function rpress_render_fooditem_meta_box() {
	global $post;

	/*
	 * Output the price fields
	 * @since  1.0.0
	 */
	do_action( 'rpress_meta_box_price_fields', $post->ID );

	/*
	 * Output the price fields
	 *
	 * Left for backwards compatibility
	 *
	 */
	do_action( 'rpress_meta_box_fields', $post->ID );

	wp_nonce_field( basename( __FILE__ ), 'rpress_fooditem_meta_box_nonce' );
}


/**
 * RestroPress Settings Metabox
 *
 * @since  1.0.0
 * @return void
 */
function rpress_render_settings_meta_box() {
	global $post;

	/*
	 * Output the files fields
	 * @since  1.0.0
	 */
	//do_action( 'rpress_meta_box_settings_fields', $post->ID );
}

/**
 * Price Section
 *
 * If variable pricing is not enabled, simply output a single input box.
 *
 * If variable pricing is enabled, outputs a table of all current prices.
 * Extensions can add column heads to the table via the `rpress_fooditem_file_table_head`
 * hook, and actual columns via `rpress_fooditem_file_table_row`
 *
 * @since 1.0
 *
 * @see rpress_render_price_row()
 *
 * @param $post_id
 */
function rpress_render_price_field( $post_id ) {
	$price              = rpress_get_fooditem_price( $post_id );
	$variable_pricing   = rpress_has_variable_prices( $post_id );
	$prices             = rpress_get_variable_prices( $post_id );
	$single_option_mode = rpress_single_price_option_mode( $post_id );

	$price_display      = $variable_pricing ? ' style="display:none;"' : '';
	$variable_display   = $variable_pricing ? '' : ' style="display:none;"';
	$currency_position  = rpress_get_option( 'currency_position', 'before' );
	?>
	<p>
		<strong><?php echo apply_filters( 'rpress_price_options_heading', __( 'Item Price:', 'restropress' ) ); ?></strong>
	</p>

	

	<div id="rpress_regular_price_field" class="rpress_pricing_fields" <?php echo $price_display; ?>>
		<?php
			$price_args = array(
				'name'  => 'rpress_price',
				'value' => isset( $price ) ? esc_attr( rpress_format_amount( $price ) ) : '',
				'class' => 'rpress-price-field'
			);
		?>

		<?php if ( $currency_position == 'before' ) : ?>
			<?php echo rpress_currency_filter( '' ); ?>
			<?php echo RPRESS()->html->text( $price_args ); ?>
		<?php else : ?>
			<?php echo RPRESS()->html->text( $price_args ); ?>
			<?php echo rpress_currency_filter( '' ); ?>
		<?php endif; ?>

		<?php do_action( 'rpress_price_field', $post_id ); ?>
	</div>

	<?php do_action( 'rpress_after_price_field', $post_id ); ?>

	<div id="rpress_variable_price_fields" class="rpress_pricing_fields" <?php echo $variable_display; ?>>
		<input type="hidden" id="rpress_variable_prices" class="rpress_variable_prices_name_field" value=""/>
		<p>
			<?php echo RPRESS()->html->checkbox( array( 'name' => '_rpress_price_options_mode', 'current' => $single_option_mode ) ); ?>
			<label for="_rpress_price_options_mode"><?php echo apply_filters( 'rpress_multi_option_purchase_text', __( 'Enable multi-option purchase mode. Allows multiple price options to be added to your cart at once', 'restropress' ) ); ?></label>
		</p>
		<div id="rpress_price_fields" class="rpress_meta_table_wrap">
			<div class="widefat rpress_repeatable_table">

				<div class="rpress-price-option-fields rpress-repeatables-wrap">
					<?php
						if ( ! empty( $prices ) ) :

							foreach ( $prices as $key => $value ) :
								$name   = ( isset( $value['name'] ) && ! empty( $value['name'] ) ) ? $value['name']   : '';
								$index  = ( isset( $value['index'] ) && $value['index'] !== '' )   ? $value['index']  : $key;
								$amount = isset( $value['amount'] ) ? $value['amount'] : '';
								$args   = apply_filters( 'rpress_price_row_args', compact( 'name', 'amount' ), $value );
								?>
								<div class="rpress_variable_prices_wrapper rpress_repeatable_row" data-key="<?php echo esc_attr( $key ); ?>">
									<?php do_action( 'rpress_render_price_row', $key, $args, $post_id, $index ); ?>
								</div>
							<?php
							endforeach;
						else :
					?>
						<div class="rpress_variable_prices_wrapper rpress_repeatable_row" data-key="1">
							<?php do_action( 'rpress_render_price_row', 1, array(), $post_id, 1 ); ?>
						</div>
					<?php endif; ?>

					<div class="rpress-add-repeatable-row">
						<div class="submit" style="float: none; clear:both; background:#fff; padding: 4px 4px 0 0;">
							<button class="button-secondary rpress_add_repeatable"><?php _e( 'Add New Price', 'restropress' ); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div><!--end #rpress_variable_price_fields-->
<?php
}
add_action( 'rpress_meta_box_price_fields', 'rpress_render_price_field', 10 );

/**
 * Individual Price Row
 *
 * Used to output a table row for each price associated with a fooditem.
 * Can be called directly, or attached to an action.
 *
 * @since  1.0.0
 *
 * @param       $key
 * @param array $args
 * @param       $post_id
 */
function rpress_render_price_row( $key, $args = array(), $post_id, $index ) {
	global $wp_filter;

	$defaults = array(
		'name'   => null,
		'amount' => null
	);

	$args = wp_parse_args( $args, $defaults );

	$default_price_id     = rpress_get_default_variable_price( $post_id );
	$currency_position    = rpress_get_option( 'currency_position', 'before' );
	$custom_price_options = isset( $wp_filter['rpress_fooditem_price_option_row'] ) ? true : false;

	// Run our advanced settings now, so we know if we need to display the settings.
	// Output buffer so that the headers run, so we can log them and use them later
	ob_start();
	do_action( 'rpress_fooditem_price_table_head', $post_id );
	ob_end_clean();

	ob_start();
	do_action( 'rpress_fooditem_price_table_row', $post_id, $key, $args );
	$show_advanced = ob_get_clean();
?>
	<?php
	// If we need to show the legacy form fields, load the backwards compatibility layer of the JavaScript as well.
	if ( $show_advanced ) {
		wp_enqueue_script( 'rpress-admin-scripts-compatibility' );
	}
	?>
	<div class="rpress-repeatable-row-header rpress-draghandle-anchor">
		<span class="rpress-repeatable-row-title" title="<?php _e( 'Click and drag to re-order price options', 'restropress' ); ?>">
			<?php printf( __( 'Price ID: %s', 'restropress' ), '<span class="rpress_price_id">' . $key . '</span>' ); ?>
			<input type="hidden" name="rpress_variable_prices[<?php echo $key; ?>][index]" class="rpress_repeatable_index" value="<?php echo $index; ?>"/>
		</span>
		<?php
		$actions = array();
		if ( ! empty( $show_advanced ) || $custom_price_options ) {
			$actions['show_advanced'] = '<a href="#" class="toggle-custom-price-option-section">' . __( 'Show advanced settings', 'restropress' ) . '</a>';
		}

		$actions['remove'] = '<a class="rpress-remove-row rpress-delete" data-type="price">' . sprintf( __( 'Remove', 'restropress' ), $key ) . '<span class="screen-reader-text">' . sprintf( __( 'Remove price option %s', 'restropress' ), $key ) . '</span></a>';
		?>
		<span class="rpress-repeatable-row-actions">
			<?php echo implode( '&nbsp;&#124;&nbsp;', $actions ); ?>
		</span>
	</div>

	<div class="rpress-repeatable-row-standard-fields">

		<div class="rpress-option-name">
			<span class="rpress-repeatable-row-setting-label"><?php _e( 'Option Name', 'restropress' ); ?></span>
			<?php echo RPRESS()->html->text( array(
				'name'  => 'rpress_variable_prices[' . $key . '][name]',
				'value' => esc_attr( $args['name'] ),
				'placeholder' => __( 'Option Name', 'restropress' ),
				'class' => 'rpress_variable_prices_name large-text'
			) ); ?>
		</div>

		<div class="rpress-option-price">
			<span class="rpress-repeatable-row-setting-label"><?php _e( 'Price', 'restropress' ); ?></span>
			<?php
			$price_args = array(
				'name'  => 'rpress_variable_prices[' . $key . '][amount]',
				'value' => $args['amount'],
				'placeholder' => rpress_format_amount( 9.99 ),
				'class' => 'rpress-price-field'
			);
			?>

			<span class="rpress-price-input-group">
				<?php if( $currency_position == 'before' ) : ?>
					<span><?php echo rpress_currency_filter( '' ); ?></span>
					<?php echo RPRESS()->html->text( $price_args ); ?>
				<?php else : ?>
					<?php echo RPRESS()->html->text( $price_args ); ?>
					<?php echo rpress_currency_filter( '' ); ?>
				<?php endif; ?>
			</span>
		</div>

		<div class="rpress_repeatable_default rpress_repeatable_default_wrapper">
			<span class="rpress-repeatable-row-setting-label"><?php _e( 'Default', 'restropress' ); ?></span>
			<label class="rpress-default-price">
				<input type="radio" <?php checked( $default_price_id, $key, true ); ?> class="rpress_repeatable_default_input" name="_rpress_default_price_id" value="<?php echo $key; ?>" />
				<span class="screen-reader-text"><?php printf( __( 'Set ID %s as default price', 'restropress' ), $key ); ?></span>
			</label>
		</div>

	</div>

	<?php
		/**
		 * Intercept extension-specific settings and rebuild the markup
		 */
		if ( ! empty( $show_advanced ) || $custom_price_options ) {
			?>

			<div class="rpress-custom-price-option-sections-wrap">
				<?php
				$elements = str_replace(
					array(
						'<td>',
						'<td ',
						'</td>',
						'<th>',
						'<th ',
						'</th>',
						'class="times"',
						'class="signup_fee"',
					),
					array(
						'<span class="rpress-custom-price-option-section">',
						'<span ',
						'</span>',
						'<label class="rpress-legacy-setting-label">',
						'<label ',
						'</label>',
						'class="rpress-recurring-times times"', // keep old class for back compat
						'class="rpress-recurring-signup-fee signup_fee"' // keep old class for back compat
					),
					$show_advanced
				);
				?>
				<div class="rpress-custom-price-option-sections">
					<?php
						echo $elements;
						do_action( 'rpress_fooditem_price_option_row', $post_id, $key, $args );
					?>
				</div>
			</div>

			<?php
		}
}
add_action( 'rpress_render_price_row', 'rpress_render_price_row', 10, 4 );

/**
 * Product type options
 *
 * @access      private
 * @since  1.0.0
 * @return      void
 */
function rpress_render_product_type_field( $post_id = 0 ) {

	$types = rpress_get_fooditem_types();
	$type  = rpress_get_fooditem_type( $post_id );
?>
	<p>
		<strong><?php echo apply_filters( 'rpress_product_type_options_heading', __( 'Product Type Options:', 'restropress' ) ); ?></strong>
	</p>
	<p>
		<?php echo RPRESS()->html->select( array(
			'options'          => $types,
			'name'             => '_rpress_product_type',
			'id'               => '_rpress_product_type',
			'selected'         => $type,
			'show_option_all'  => false,
			'show_option_none' => false
		) ); ?>
		<label for="_rpress_product_type"><?php _e( 'Select a product type', 'restropress' ); ?></label>
		<span alt="f223" class="rpress-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Product Type</strong>: Sell this item as a single product, or use the Bundle type to sell a collection of products.', 'restropress' ); ?>"></span>
	</p>
<?php
}
add_action( 'rpress_meta_box_files_fields', 'rpress_render_product_type_field', 10 );

/**
 * Renders product field
 * @since  1.0.0
 *
 * @param $post_id
 */
function rpress_render_products_field( $post_id ) {
	$fooditem         = new RPRESS_RestroPress( $post_id );
	$type             = $fooditem->get_type();
	$display          = $type == 'bundle' ? '' : ' style="display:none;"';
	$products         = $fooditem->get_bundled_fooditems();
	$variable_pricing = $fooditem->has_variable_prices();
	$variable_display = $variable_pricing ? '' : 'display:none;';
	$variable_class   = $variable_pricing ? ' has-variable-pricing' : '';
	$prices           = $fooditem->get_prices();
?>
	<div id="rpress_products"<?php echo $display; ?>>
		<div id="rpress_file_fields" class="rpress_meta_table_wrap">
			<div class="widefat rpress_repeatable_table">

				<?php do_action( 'rpress_fooditem_products_table_head', $post_id ); ?>

				<div class="rpress-bundled-product-select rpress-repeatables-wrap">

					<?php if ( $products ) : ?>

						<div class="rpress-bundle-products-header">
							<span class="rpress-bundle-products-title"><?php printf( __( 'Bundled %s', 'restropress' ), rpress_get_label_plural() ); ?></span>
						</div>

						<?php $index = 1; ?>
						<?php foreach ( $products as $key => $product ) : ?>
							<div class="rpress_repeatable_product_wrapper rpress_repeatable_row" data-key="<?php echo esc_attr( $index ); ?>">
								<div class="rpress-bundled-product-row<?php echo $variable_class; ?>">
									<div class="rpress-bundled-product-item-reorder">
										<span class="rpress-product-file-reorder rpress-draghandle-anchor dashicons dashicons-move"  title="<?php printf( __( 'Click and drag to re-order bundled %s', 'restropress' ), rpress_get_label_plural() ); ?>"></span>
										<input type="hidden" name="rpress_bundled_products[<?php echo $index; ?>][index]" class="rpress_repeatable_index" value="<?php echo $index; ?>"/>
									</div>
									<div class="rpress-bundled-product-item">
										<span class="rpress-repeatable-row-setting-label"><?php printf( __( 'Select %s:', 'restropress' ), rpress_get_label_singular() ); ?></span>
										<?php
										echo RPRESS()->html->product_dropdown( array(
											'name'       => '_rpress_bundled_products[]',
											'id'         => 'rpress_bundled_products_' . $index,
											'selected'   => $product,
											'multiple'   => false,
											'chosen'     => true,
											'bundles'    => false,
											'variations' => true,
										) );
										?>
									</div>
									<div class="rpress-bundled-product-price-assignment pricing" style="<?php echo $variable_display; ?>">
										<span class="rpress-repeatable-row-setting-label"><?php _e( 'Price assignment:', 'restropress' ); ?></span>
										<?php
											$options = array();

											if ( $prices ) {
												foreach ( $prices as $price_key => $price ) {
													$options[ $price_key ] = $prices[ $price_key ]['name'];
												}
											}

											$price_assignments = rpress_get_bundle_pricing_variations( $post_id );
											$price_assignments = $price_assignments[0];

											$selected = isset( $price_assignments[ $index ] ) ? $price_assignments[ $index ] : null;

											echo RPRESS()->html->select( array(
												'name'             => '_rpress_bundled_products_conditions['. $index .']',
												'class'            => 'rpress_repeatable_condition_field',
												'options'          => $options,
												'show_option_none' => false,
												'selected'         => $selected
											) );
										?>
									</div>
									<span class="rpress-bundled-product-actions">
										<a class="rpress-remove-row rpress-delete" data-type="file"><?php printf( __( 'Remove', 'restropress' ), $index ); ?><span class="screen-reader-text"><?php printf( __( 'Remove bundle option %s', 'restropress' ), $index ); ?></span></a>
									</span>
									<?php do_action( 'rpress_fooditem_products_table_row', $post_id ); ?>
								</div>
							</div>
							<?php $index++; ?>
						<?php endforeach; ?>

					<?php else: ?>

						<div class="rpress-bundle-products-header">
							<span class="rpress-bundle-products-title"><?php printf( __( 'Bundled %s:', 'restropress' ), rpress_get_label_plural() ); ?></span>
						</div>
						<div class="rpress_repeatable_product_wrapper rpress_repeatable_row" data-key="1">
							<div class="rpress-bundled-product-row<?php echo $variable_class; ?>">

								<div class="rpress-bundled-product-item-reorder">
									<span class="rpress-product-file-reorder rpress-draghandle-anchor dashicons dashicons-move" title="<?php printf( __( 'Click and drag to re-order bundled %s', 'restropress' ), rpress_get_label_plural() ); ?>"></span>
									<input type="hidden" name="rpress_bundled_products[1][index]" class="rpress_repeatable_index" value="1"/>
								</div>
								<div class="rpress-bundled-product-item">
									<span class="rpress-repeatable-row-setting-label"><?php printf( __( 'Select %s:', 'restropress' ), rpress_get_label_singular() ); ?></span>
									<?php
									echo RPRESS()->html->product_dropdown( array(
										'name'       => '_rpress_bundled_products[]',
										'id'         => 'rpress_bundled_products_1',
										'multiple'   => false,
										'chosen'     => true,
										'bundles'    => false,
										'variations' => true,
									) );
									?>
								</div>
								<div class="rpress-bundled-product-price-assignment pricing" style="<?php echo $variable_display; ?>">
									<span class="rpress-repeatable-row-setting-label"><?php _e( 'Price assignment:', 'restropress' ); ?></span>
									<?php
										$options = array();

										if ( $prices ) {
											foreach ( $prices as $price_key => $price ) {
												$options[ $price_key ] = $prices[ $price_key ]['name'];
											}
										}

										$price_assignments = rpress_get_bundle_pricing_variations( $post_id );

										echo RPRESS()->html->select( array(
											'name'             => '_rpress_bundled_products_conditions[1]',
											'class'            => 'rpress_repeatable_condition_field',
											'options'          => $options,
											'show_option_none' => false,
											'selected'         => null,
										) );
									?>
								</div>
								<span class="rpress-bundled-product-actions">
									<a class="rpress-remove-row rpress-delete" data-type="file" ><?php printf( __( 'Remove', 'restropress' ) ); ?><span class="screen-reader-text"><?php __( 'Remove bundle option 1', 'restropress' ); ?></span></a>
								</span>
								<?php do_action( 'rpress_fooditem_products_table_row', $post_id ); ?>
							</div>
						</div>

					<?php endif; ?>

					<div class="rpress-add-repeatable-row">
						<div class="submit" style="float: none; clear:both; background: #fff;">
							<button class="button-secondary rpress_add_repeatable"><?php _e( 'Add New File', 'restropress' ); ?></button>
						</div>
					</div>

				</div>

			</div>
		</div>
	</div>
<?php
}
add_action( 'rpress_meta_box_files_fields', 'rpress_render_products_field', 10 );

/**
 * File RestroPress section.
 *
 * Outputs a table of all current files. Extensions can add column heads to the table
 * via the `rpress_fooditem_file_table_head` hook, and actual columns via
 * `rpress_fooditem_file_table_row`
 *
 * @since 1.0
 * @see rpress_render_file_row()
 * @param int $post_id RestroPress (Post) ID
 * @return void
 */
function rpress_render_files_field( $post_id = 0 ) {
	$type             = rpress_get_fooditem_type( $post_id );
	$files            = rpress_get_fooditem_files( $post_id );
	$variable_pricing = rpress_has_variable_prices( $post_id );
	$display          = $type == 'bundle' ? ' style="display:none;"' : '';
	$variable_display = $variable_pricing ? '' : 'display:none;';
?>
	<div id="rpress_fooditem_files"<?php echo $display; ?>>

		<input type="hidden" id="rpress_fooditem_files" class="rpress_repeatable_upload_name_field" value=""/>

		<div id="rpress_file_fields" class="rpress_meta_table_wrap">
			<div class="widefat rpress_repeatable_table">

				<div class="rpress-file-fields rpress-repeatables-wrap">
					<?php
						if ( ! empty( $files ) && is_array( $files ) ) :
							foreach ( $files as $key => $value ) :
								$index          = isset( $value['index'] )         ? $value['index']         : $key;
								$name           = isset( $value['name'] )          ? $value['name']          : '';
								$file           = isset( $value['file'] )          ? $value['file']          : '';
								$condition      = isset( $value['condition'] )     ? $value['condition']     : false;
								$attachment_id  = isset( $value['attachment_id'] ) ? absint( $value['attachment_id'] ) : false;
								$thumbnail_size = isset( $value['thumbnail_size'] ) ? $value['thumbnail_size'] : '';

								$args = apply_filters( 'rpress_file_row_args', compact( 'name', 'file', 'condition', 'attachment_id', 'thumbnail_size' ), $value );
								?>

								<div class="rpress_repeatable_upload_wrapper rpress_repeatable_row" data-key="<?php echo esc_attr( $key ); ?>">
									<?php do_action( 'rpress_render_file_row', $key, $args, $post_id, $index ); ?>
								</div>
								<?php
							endforeach;
						else : ?>
							<div class="rpress_repeatable_upload_wrapper rpress_repeatable_row">
								<?php do_action( 'rpress_render_file_row', 1, array(), $post_id, 0 ); ?>
							</div>
							<?php
						endif;
					?>

					<div class="rpress-add-repeatable-row">
						<div class="submit" style="float: none; clear:both; background: #fff;">
							<button class="button-secondary rpress_add_repeatable"><?php _e( 'Add New File', 'restropress' ); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}
add_action( 'rpress_meta_box_files_fields', 'rpress_render_files_field', 20 );


/**
 * Individual file row.
 *
 * Used to output a table row for each file associated with a fooditem.
 * Can be called directly, or attached to an action.
 *
 * @since  1.0.0
 * @param string $key Array key
 * @param array $args Array of all the arguments passed to the function
 * @param int $post_id RestroPress (Post) ID
 * @return void
 */
function rpress_render_file_row( $key = '', $args = array(), $post_id, $index ) {
	$defaults = array(
		'name'           => null,
		'file'           => null,
		'condition'      => null,
		'attachment_id'  => null,
		'thumbnail_size' => null,
	);

	$args = wp_parse_args( $args, $defaults );

	$prices = rpress_get_variable_prices( $post_id );

	$variable_pricing = rpress_has_variable_prices( $post_id );
	$variable_display = $variable_pricing ? '' : ' style="display:none;"';
	$variable_class   = $variable_pricing ? ' has-variable-pricing' : '';
?>
	<div class="rpress-repeatable-row-header rpress-draghandle-anchor">
		<span class="rpress-repeatable-row-title" title="<?php _e( 'Click and drag to re-order files', 'restropress' ); ?>">
			<?php printf( __( '%1$s file ID: %2$s', 'restropress' ), rpress_get_label_singular(), '<span class="rpress_file_id">' . $key . '</span>' ); ?>
			<input type="hidden" name="rpress_fooditem_files[<?php echo $key; ?>][index]" class="rpress_repeatable_index" value="<?php echo $index; ?>"/>
		</span>
		<span class="rpress-repeatable-row-actions">
			<a class="rpress-remove-row rpress-delete" data-type="file"><?php printf( __( 'Remove', 'restropress' ), $key ); ?><span class="screen-reader-text"><?php printf( __( 'Remove file %s', 'restropress' ), $key ); ?></span>
			</a>
		</span>
	</div>

	<div class="rpress-repeatable-row-standard-fields<?php echo $variable_class; ?>">

		<div class="rpress-file-name">
			<span class="rpress-repeatable-row-setting-label"><?php _e( 'File Name', 'restropress' ); ?></span>
			<input type="hidden" name="rpress_fooditem_files[<?php echo absint( $key ); ?>][attachment_id]" class="rpress_repeatable_attachment_id_field" value="<?php echo esc_attr( absint( $args['attachment_id'] ) ); ?>"/>
			<input type="hidden" name="rpress_fooditem_files[<?php echo absint( $key ); ?>][thumbnail_size]" class="rpress_repeatable_thumbnail_size_field" value="<?php echo esc_attr( $args['thumbnail_size'] ); ?>"/>
			<?php echo RPRESS()->html->text( array(
				'name'        => 'rpress_fooditem_files[' . $key . '][name]',
				'value'       => $args['name'],
				'placeholder' => __( 'File Name', 'restropress' ),
				'class'       => 'rpress_repeatable_name_field large-text'
			) ); ?>
		</div>

		<div class="rpress-file-url">
			<span class="rpress-repeatable-row-setting-label"><?php _e( 'File URL', 'restropress' ); ?></span>
			<div class="rpress_repeatable_upload_field_container">
				<?php echo RPRESS()->html->text( array(
					'name'        => 'rpress_fooditem_files[' . $key . '][file]',
					'value'       => $args['file'],
					'placeholder' => __( 'Upload or enter the file URL', 'restropress' ),
					'class'       => 'rpress_repeatable_upload_field rpress_upload_field large-text'
				) ); ?>

				<span class="rpress_upload_file">
					<a href="#" data-uploader-title="<?php _e( 'Insert File', 'restropress' ); ?>" data-uploader-button-text="<?php _e( 'Insert', 'restropress' ); ?>" class="rpress_upload_file_button" onclick="return false;"><?php _e( 'Upload a File', 'restropress' ); ?></a>
				</span>
			</div>
		</div>

		<div class="rpress-file-assignment pricing"<?php echo $variable_display; ?>>

			<span class="rpress-repeatable-row-setting-label"><?php _e( 'Price Assignment', 'restropress' ); ?><span alt="f223" class="rpress-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Price Assignment</strong>: With variable pricing enabled, you can choose to allow certain price variations access to specific files, or allow all price variations to access a file.', 'restropress' ); ?>"></span></span>

			<?php
				$options = array();

				if ( $prices ) {
					foreach ( $prices as $price_key => $price ) {
						$options[ $price_key ] = $prices[ $price_key ]['name'];
					}
				}

				echo RPRESS()->html->select( array(
					'name'             => 'rpress_fooditem_files[' . $key . '][condition]',
					'class'            => 'rpress_repeatable_condition_field',
					'options'          => $options,
					'selected'         => $args['condition'],
					'show_option_none' => false
				) );
			?>
		</div>

		<?php do_action( 'rpress_fooditem_file_table_row', $post_id, $key, $args ); ?>

	</div>
<?php
}
add_action( 'rpress_render_file_row', 'rpress_render_file_row', 10, 4 );

/**
 * Alter the Add to post button in the media manager for fooditems
 *
 * @since 1.0
 * @param  array $strings Array of default strings for media manager
 * @return array          The altered array of strings for media manager
 */
function rpress_fooditem_media_strings( $strings ) {
	global $post;

	if ( ! $post || $post->post_type !== 'fooditem' ) {
		return $strings;
	}

	$fooditems_object = get_post_type_object( 'fooditem' );
	$labels = $fooditems_object->labels;

	$strings['insertIntoPost'] = sprintf( __( 'Insert into %s', 'restropress' ), strtolower( $labels->singular_name ) );

	return $strings;
}
add_filter( 'media_view_strings', 'rpress_fooditem_media_strings', 10, 1 );


/**
 * File RestroPress Limit Row
 *
 * The file fooditem limit is the maximum number of times each file
 * can be fooditemed by the buyer
 *
 * @since  1.0.0
 * @param int $post_id RestroPress (Post) ID
 * @return void
 */
function rpress_render_fooditem_limit_row( $post_id ) {
	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	$rpress_fooditem_limit = rpress_get_file_fooditem_limit( $post_id );
	$display = 'bundle' == rpress_get_fooditem_type( $post_id ) ? ' style="display: none;"' : '';
?>
	<div id="rpress_fooditem_limit_wrap"<?php echo $display; ?>>
		<p><strong><?php _e( 'File RestroPress Limit:', 'restropress' ); ?></strong></p>
		<label for="rpress_fooditem_limit">
			<?php echo RPRESS()->html->text( array(
				'name'  => '_rpress_fooditem_limit',
				'value' => $rpress_fooditem_limit,
				'class' => 'small-text'
			) ); ?>
			<?php _e( 'Leave blank for global setting or 0 for unlimited', 'restropress' ); ?>
		</label>
		<span alt="f223" class="rpress-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>File RestroPress Limit</strong>: Limit the number of times a customer who purchased this product can access their fooditem links.', 'restropress' ); ?>"></span>
	</div>
<?php
}
add_action( 'rpress_meta_box_settings_fields', 'rpress_render_fooditem_limit_row', 20 );

/**
 * Product tax settings
 *
 * Outputs the option to mark whether a product is exclusive of tax
 *
 * @since  1.0.0
 * @param int $post_id RestroPress (Post) ID
 * @return void
 */
function rpress_render_dowwn_tax_options( $post_id = 0 ) {
	rpress_render_down_tax_options( $post_id );
}

/**
 * Product tax settings
 *
 * Outputs the option to mark whether a product is exclusive of tax
 *
 * @since  1.0.0
 * @since 1.0.0.12 Fixed miss-spelling in function name.
 * @param int $post_id RestroPress (Post) ID
 * @return void
 */
function rpress_render_down_tax_options( $post_id = 0 ) {
	if( ! current_user_can( 'manage_shop_settings' ) || ! rpress_use_taxes() ) {
		return;
	}

	$exclusive = rpress_fooditem_is_tax_exclusive( $post_id );
?>
	<p><strong><?php _e( 'Ignore Tax:', 'restropress' ); ?></strong></p>
	<label for="_rpress_fooditem_tax_exclusive">
		<?php echo RPRESS()->html->checkbox( array(
			'name'    => '_rpress_fooditem_tax_exclusive',
			'current' => $exclusive
		) ); ?>
		<?php _e( 'Mark this product as exclusive of tax', 'restropress' ); ?>
	</label>
<?php
}
add_action( 'rpress_meta_box_settings_fields', 'rpress_render_down_tax_options', 30 );

/**
 * Product quantity settings
 *
 * Outputs the option to disable quantity field on product.
 *
 * @since 1.0
 * @param int $post_id RestroPress (Post) ID
 * @return void
 */
function rpress_render_fooditem_quantity_option( $post_id = 0 ) {
	if( ! current_user_can( 'manage_shop_settings' ) || ! rpress_item_quantities_enabled() ) {
		return;
	}

	$disabled = rpress_fooditem_quantities_disabled( $post_id );
?>
	<p><strong><?php _e( 'Item Quantities:', 'restropress' ); ?></strong></p>
	<label for="_rpress_quantities_disabled">
		<?php echo RPRESS()->html->checkbox( array(
			'name'    => '_rpress_quantities_disabled',
			'current' => $disabled
		) ); ?>
		<?php _e( 'Disable quantity input for this product', 'restropress' ); ?>
	</label>
	<span alt="f223" class="rpress-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Item Quantities</strong>: if disabled, customers will not be provided an option to change the number they wish to purchase.', 'restropress' ); ?>"></span>
<?php
}
add_action( 'rpress_meta_box_settings_fields', 'rpress_render_fooditem_quantity_option', 30 );

/**
 * Add shortcode to settings meta box
 *
 * @since  1.0.0
 * @global array $post Contains all the fooditem data
 * @return void
 */
function rpress_render_meta_box_shortcode() {
	global $post;

	if( $post->post_type != 'fooditem' ) {
		return;
	}

	$purchase_text = rpress_get_option( 'add_to_cart_text', __( 'Purchase', 'restropress' ) );
	$style         = rpress_get_option( 'button_style', 'button' );
	$color         = rpress_get_option( 'checkout_color', 'red' );
	$color         = ( $color == 'inherit' ) ? '' : $color;
	$shortcode     = '[purchase_link id="' . absint( $post->ID ) . '" text="' . esc_html( $purchase_text ) . '" style="' . $style . '" color="' . esc_attr( $color ) . '"]';
?>
	<p>
		<strong><?php _e( 'Purchase Shortcode:', 'restropress' ); ?></strong>
		<span alt="f223" class="rpress-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Purchse Shortcode</strong>: Use this shortcode to output a purchase link for this product in the location of your choosing.', 'restropress' ); ?>"></span>
	</p>
	<input type="text" id="rpress-purchase-shortcode" class="widefat" readonly="readonly" value="<?php echo htmlentities( $shortcode ); ?>">
<?php
}
add_action( 'rpress_meta_box_settings_fields', 'rpress_render_meta_box_shortcode', 35 );

/**
 * Render Accounting Options
 *
 * @since  1.0.0
 * @param int $post_id RestroPress (Post) ID
 * @return void
 */
function rpress_render_accounting_options( $post_id ) {
	if( ! rpress_use_skus() ) {
		return;
	}

		$rpress_sku = get_post_meta( $post_id, 'rpress_sku', true );
?>
		<p><strong><?php _e( 'Accounting Options:', 'restropress' ); ?></strong></p>
		<p>
			<label for="rpress_sku">
				<?php echo RPRESS()->html->text( array(
					'name'  => 'rpress_sku',
					'value' => $rpress_sku,
					'class' => 'small-text'
				) ); ?>
				<?php echo sprintf( __( 'Enter an SKU for this %s.', 'restropress' ), strtolower( rpress_get_label_singular() ) ); ?>
			</label>
		</p>
<?php
}
add_action( 'rpress_meta_box_settings_fields', 'rpress_render_accounting_options', 25 );


/**
 * Render Disable Button
 *
 * @since 1.0
 * @param int $post_id RestroPress (Post) ID
 * @return void
 */
function rpress_render_disable_button( $post_id ) {
	$hide_button = get_post_meta( $post_id, '_rpress_hide_purchase_link', true ) ? 1 : 0;
	$behavior    = get_post_meta( $post_id, '_rpress_button_behavior', true );
?>
	<p><strong><?php _e( 'Button Options:', 'restropress' ); ?></strong></p>
	<p>
		<label for="_rpress_hide_purchase_link">
			<?php echo RPRESS()->html->checkbox( array(
				'name'    => '_rpress_hide_purchase_link',
				'current' => $hide_button
			) ); ?>
			<?php _e( 'Disable the automatic output of the purchase button', 'restropress' ); ?>
			<span alt="f223" class="rpress-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Automatic Output</strong>: By default, the purchase buttons will be displayed at the bottom of the fooditem, when disabled you will need to use the Purchase link shortcode below to output the ability to buy the product where you prefer.', 'restropress' ); ?>"></span>
		</label>
	</p>
	<?php $supports_buy_now = rpress_shop_supports_buy_now(); ?>
	<p>
		<label for="_rpress_button_behavior">
			<?php
			$args = array(
				'name'    => '_rpress_button_behavior',
				'options' => array(
					'add_to_cart' => __( 'Add to Cart', 'restropress' ),
					'direct'      => __( 'Buy Now', 'restropress' ),
				),
				'show_option_all'  => null,
				'show_option_none' => null,
				'selected' => $behavior
			);

			if ( ! $supports_buy_now ) {
				$args['disabled'] = true;
				$args['readonly'] = true;
			}
			?>
			<?php echo RPRESS()->html->select( $args ); ?>
			<?php _e( 'Purchase button behavior', 'restropress' ); ?>
			<?php if ( $supports_buy_now ) : ?>
				<span alt="f223" class="rpress-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Button Behavior</strong>: Add to Cart buttons follow a traditional eCommerce flow. A Buy Now button bypasses most of the process, taking the customer directly from button click to payment, greatly speeding up the process of buying the product.', 'restropress' ); ?>"></span>
			<?php else: ?>
				<span alt="f223" class="rpress-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Button Behavior</strong>: Add to Cart buttons follow a traditional eCommerce flow. Buy Now buttons are only available for stores that have a single supported gateway active and that do not use taxes.', 'restropress' ); ?>"></span>
			<?php endif; ?>

		</label>
	</p>
<?php
}
add_action( 'rpress_meta_box_settings_fields', 'rpress_render_disable_button', 30 );


/** Product Notes *****************************************************************/

/**
 * Product Notes Meta Box
 *
 * Renders the Product Notes meta box
 *
 * @since  1.0.0
 * @global array $post Contains all the fooditem data
 * @return void
 */
function rpress_render_product_notes_meta_box() {
	global $post;

	do_action( 'rpress_product_notes_meta_box_fields', $post->ID );
}

/**
 * Render Product Notes Field
 *
 * @since  1.0.0
 * @param int $post_id RestroPress (Post) ID
 * @return void
 */
function rpress_render_product_notes_field( $post_id ) {
	$product_notes = rpress_get_product_notes( $post_id );
?>
	<textarea rows="1" cols="40" class="large-texarea" name="rpress_product_notes" id="rpress_product_notes_field"><?php echo esc_textarea( $product_notes ); ?></textarea>
	<p><?php _e( 'Special notes or instructions for this product. These notes will be added to the purchase receipt.', 'restropress' ); ?></p>
<?php
}
add_action( 'rpress_product_notes_meta_box_fields', 'rpress_render_product_notes_field' );






/**
 * Internal use only
 *
 * This function takes any hooked functions for rpress_fooditem_price_table_head and re-registers them into the rpress_fooditem_price_table_row
 * action. It will also de-register any original table_row data, so that labels appear before their setting, then re-registers the table_row.
 *
 * @since 1.0.0
 *
 * @param $arg1
 * @param $arg2
 * @param $arg3
 *
 * @return void
 */
function rpress_hijack_rpress_fooditem_price_table_head( $arg1, $arg2, $arg3 ) {
	global $wp_filter;

	$found_fields  = isset( $wp_filter['rpress_fooditem_price_table_row'] )  ? $wp_filter['rpress_fooditem_price_table_row']  : false;
	$found_headers = isset( $wp_filter['rpress_fooditem_price_table_head'] ) ? $wp_filter['rpress_fooditem_price_table_head'] : false;

	$re_register = array();

	if ( ! $found_fields && ! $found_headers ) {
		return;
	}

	foreach ( $found_fields->callbacks as $priority => $callbacks ) {
		if ( -1 === $priority ) {
			continue; // Skip our -1 priority so we don't break the interwebs
		}

		if ( is_object( $found_headers ) && property_exists( $found_headers, 'callbacks' ) && array_key_exists( $priority, $found_headers->callbacks ) ) {

			// De-register any row data.
			foreach ( $callbacks as $callback ) {
				$re_register[ $priority ][] = $callback;
				remove_action( 'rpress_fooditem_price_table_row', $callback['function'], $priority, $callback['accepted_args'] );
			}

			// Register any header data.
			foreach( $found_headers->callbacks[ $priority ] as $callback ) {
				if ( is_callable( $callback['function'] ) ) {
					add_action( 'rpress_fooditem_price_table_row', $callback['function'], $priority, 1 );
				}
			}
		}

	}

	// Now that we've re-registered our headers first...re-register the inputs
	foreach ( $re_register as $priority => $callbacks ) {
		foreach ( $callbacks as $callback ) {
			add_action( 'rpress_fooditem_price_table_row', $callback['function'], $priority, $callback['accepted_args'] );
		}
	}
}
add_action( 'rpress_fooditem_price_table_row', 'rpress_hijack_rpress_fooditem_price_table_head', -1, 3 );