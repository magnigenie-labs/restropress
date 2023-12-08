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
	public static function query($term_slug) {
		$atts = RP_Shortcode_Fooditems::$atts;
	
		// Split the combined term slug into individual term slugs
		$term_slugs = explode(',', $term_slug);
		$term_slugs = array_map('trim', $term_slugs); // Remove whitespace around terms
	
		// Remove excluded category slugs from $term_slugs
		if (!empty($atts['category_exclude'])) {
			$exclude_terms = explode(',', $atts['category_exclude']);
			$exclude_terms = array_map('trim', $exclude_terms); // Remove whitespace around terms
	
			$term_slugs = array_diff($term_slugs, $exclude_terms);
		}
	
		$query = array(
			'post_type'      => 'fooditem',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => $atts['fooditem_orderby'],
			'order'          => $atts['fooditem_order']
		);
	
		// Add the modified $term_slugs to the tax query
		$query['tax_query'][] = array(
			'taxonomy' => 'food-category',
			'field'    => 'slug',
			'terms'    => $term_slugs,
		);
	
		if (!empty($atts['ids'])) {
			$query['post__in'] = explode(',', $atts['ids']);
		}
	
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
		
		$exclude_terms = "";
		if(!empty($atts['category_exclude'])) {
			$exclude_terms = $atts['category_exclude'];
		}

		$atts = shortcode_atts( array(
        'category'          => '',
        'category_menu'     => '',
        'fooditem_orderby'  => 'title',
        'fooditem_order'    => 'ASC',
        'relation'          => 'OR',
        'cat_orderby'       => 'include',
        'cat_order'         => 'ASC',
		'category_exclude'  => $exclude_terms,
	    ), $atts, 'fooditems' );

	    RP_Shortcode_Fooditems::$atts = apply_filters( 'rpress_set_fooditems_attributes', $atts );

	    rpress_get_template_part( 'fooditem/fooditems' );
	}
}