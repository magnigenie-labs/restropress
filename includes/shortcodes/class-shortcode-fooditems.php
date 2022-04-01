<?php
/**
 * Food Items Shortcode
 *
 * @package RestroPress/Shortcodes/FoodItems
 * @version 2.6
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shortcode Food Items Class.
 */
class RP_Shortcode_Fooditems {

	/**
	 * Food Items Attributes Shortcode
	 *
	 * @var array
	 * @since 1.0
	 */
	public static $atts = array();

	/**
	 * Prepare the Food Items Queries.
	 *
	 * @param array $atts Shortcode attributes.
	 */
	public static function query( $term_slug ) {

		$atts = RP_Shortcode_Fooditems::$atts;

		$query = array(
            'post_type'      => 'fooditem',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => $atts['fooditem_orderby'],
            'order'          => $atts['fooditem_order']
        );

        $query['tax_query'][] = array(
            'taxonomy' => 'food-category',
            'field'    => 'slug',
            'terms'    => $term_slug,
        );

        if( ! empty( $atts['ids'] ) )
            $query['post__in'] = explode( ',', $atts['ids'] );

        return $query;
	}

	/**
	 * Output the Food Items shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 */
	public static function output( $atts ) {

		if ( !apply_filters( 'restropress_output_fooditem_shortcode_content', true ) ) {
			return;
		}

		$atts = shortcode_atts( array(
        'category'          => '',
        'category_menu'     => '',
        'fooditem_orderby'  => 'title',
        'fooditem_order'    => 'ASC',
        'relation'          => 'OR',
        'cat_orderby'       => 'include',
        'cat_order'         => 'ASC',
	    ), $atts, 'fooditems' );

	    RP_Shortcode_Fooditems::$atts = apply_filters( 'rpress_set_fooditems_attributes', $atts );

	    rpress_get_template_part( 'fooditem/fooditems' );
	}
}