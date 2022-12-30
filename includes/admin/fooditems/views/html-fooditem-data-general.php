<?php
/**
 * Food Item general data panel.
 *
 * @package RestroPress/Admin
 */

defined( 'ABSPATH' ) || exit;
$has_variable_prices = $fooditem_object->has_variable_prices();
?>
<div id="general_fooditem_data" class="panel restropress_options_panel rp-metaboxes-wrapper">
	<div class="rp-metabox-container">
		<div class="toolbar toolbar-top">
			<span class="rp-toolbar-title">
				<?php esc_html_e( 'Item Details', 'restropress' ); ?>
			</span>
		</div>
		<div class="options_group pricing">
			<div class="rp-tab-content">

				<?php

				do_action( 'rpress_fooditem_options_general_top' );

				$vegan_options = array(
					''					=> __( 'N/A', 'restropress' ),
					'veg' 			=> __( 'Veg', 'restropress' ),
					'non_veg' 	=> __( 'Non Veg', 'restropress' ),
				);
				$vegan_options	= apply_filters( 'rpress_vegan_options', $vegan_options );

				// Veg / Non Veg Option
				rpress_radio(
					array(
						'id'        		=> 'rpress_food_type',
						'value'     		=> $fooditem_object->get_food_type(),
						'label'     		=> '',
						'options' 			=> $vegan_options,
						'wrapper_class' => 'admin_vegan_radio',
					)
				);

				do_action( 'rpress_fooditem_options_general_before_pricing' );
				if( !empty( rpress_use_skus() ) ){ 

				rpress_text_input(
					array(
						'id'        => 'rpress_sku',
						'value'     => $fooditem_object->get_sku(),
						'label'     => __( 'SKU', 'restropress' ),
					)
				);
			}
				rpress_text_input(
					array(
						'id'        => 'rpress_price',
						'value'     => $fooditem_object->get_price(),
						'label'     => __( 'Price', 'restropress' ) . ' (' . rpress_currency_symbol() . ')',
						'wrapper_class'		=> $has_variable_prices ? 'hidden' : '',
						'data_type' => 'price',
					)
				);

				// Variable Pricing
				rpress_checkbox(
					array(
						'id'          => '_variable_pricing',
						'label'       => __( 'Variable pricing', 'restropress' ),
						'description' => __( 'Check this box if the food has multiple options and you want to specify price for different options.', 'restropress' ),
						'value'       => $has_variable_prices ? 'yes' : 'no',
					)
				);

				rpress_text_input(
					array(
						'id' => 'rpress_variable_price_label',
						'value' => get_post_meta( $fooditem_object->ID, 'rpress_variable_price_label', true ),
						'label' => __( 'Price Label', 'restropress' ),
						'wrapper_class' => $has_variable_prices ? 'rp-variable-prices' : 'rp-variable-prices hidden',
					)
				);

				?>

				<div class="rp-metaboxes rp-variable-prices <?php echo !$has_variable_prices ? 'hidden' : ''; ?>">
					<?php
					if( $has_variable_prices ) :
						$prices = ( array ) $fooditem_object->get_prices();
						$current = 0;
						foreach ( $prices as $price ) :  ?>
							<?php include 'html-fooditem-variable-price.php'; ?>
						<?php $current++; endforeach; ?>
					<?php else: ?>
						<?php include 'html-fooditem-variable-price.php'; ?>
					<?php endif; ?>
					<button type="button" class="button button-primary add-new-price">
						<?php esc_html_e( '+ Add New', 'restropress' ); ?>
					</button>
				</div>

			</div>
			<?php do_action( 'rpress_fooditem_options_general_after_pricing' ); ?>
			<?php do_action( 'rpress_fooditem_options_general_end' ); ?>
		</div>
	</div>

	<?php do_action( 'rpress_fooditem_options_general_data' ); ?>
</div>
