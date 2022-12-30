<?php
/**
 * A single fooditem inside of the [fooditems] shortcode.
 *
 * @since 1.0.0
 *
 * @package RPRESS
 * @category Template
 * @author RestroPress
 * @version 1.0.4
 */

global $rpress_fooditem_shortcode_item_atts, $rpress_fooditem_shortcode_item_i, $rpress_food_item_cats;

$schema = rpress_add_schema_microdata() ? 'itemscope itemtype="http://schema.org/Product" ' : '';

$post_terms = wp_get_post_terms( get_the_ID(), 'food-category' );

$term_id = $post_terms[0]->term_taxonomy_id;

$disable_category = rpress_get_option( 'disable_category_menu', false );
$option_view_food_items  = rpress_get_option( 'option_view_food_items' );

if( rpress_get_option( 'disable_styles', false ) == 0 && $disable_category && $option_view_food_items == "grid_view" ) {

  $food_item_class = "rpress-grid";
}
else {
  $food_item_class = "rpress-list";
}

?>

<div <?php echo esc_html( $schema ); ?>class="<?php echo esc_attr( apply_filters( 'rpress_fooditem_class', 'rpress_fooditem', get_the_ID(), $rpress_fooditem_shortcode_item_atts, $rpress_fooditem_shortcode_item_i ) ); ?> <?php echo $food_item_class; ?>" data-term-id="<?php echo esc_attr( $term_id ); ?>" id="rpress_fooditem_<?php the_ID(); ?>">
<?php $img_wrp = ( ! has_post_thumbnail( get_the_ID() ) ) ? 'rp-no-img' : ''; ?>
	<div class="row <?php echo esc_attr( $img_wrp ) . ' ' . esc_attr( apply_filters( 'rpress_fooditem_inner_class', 'rpress_fooditem_inner', get_the_ID(), $rpress_fooditem_shortcode_item_atts, $rpress_fooditem_shortcode_item_i ) ); ?>">

		<?php do_action( 'rpress_fooditem_before' ); ?>

		<div class="rp-col-md-9 rp-grid-view-wrap">

			<?php

			rpress_get_template_part( 'fooditem/content-image' );
			do_action( 'rpress_fooditem_after_thumbnail' );

			rpress_get_template_part( 'fooditem/content-title' );
			do_action( 'rpress_fooditem_after_title' );

			?>

		</div>

		<div class="rp-col-md-3">

			<?php

			rpress_get_template_part( 'fooditem/content-cart-button' );
			do_action( 'rpress_fooditem_after_cart_button' );

			?>

		</div>

		<?php do_action( 'rpress_fooditem_after' ); ?>

	</div>
</div>