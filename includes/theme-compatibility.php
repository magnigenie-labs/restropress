<?php
/**
 * Theme Compatibility
 *
 * Functions for compatibility with specific themes.
 *
 * @package     RPRESS
 * @subpackage  Functions/Compatibility
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Remove the "fooditem" post class from single food item pages
 *
 * The Responsive theme applies special styling the .fooditem class resulting in really terrible display.
 *
 * @since 1.0.0
 * @param array $classes Post classes
 * @param string $class
 * @param int $post_id Post ID
 * @return array
 */
function rpress_responsive_fooditem_post_class( $classes = array(), $class = '', $post_id = 0 ) {
	if (
		! is_singular( 'fooditem' ) &&
		! is_post_type_archive( 'fooditem' ) &&
		! is_tax( 'addon_category' ) &&
		! is_tax( 'fooditem_tag' )
	)
		return $classes;

	if ( ( $key = array_search( 'fooditem', $classes ) ) )
		unset( $classes[ $key ] );

	return $classes;
}
add_filter( 'post_class', 'rpress_responsive_fooditem_post_class', 999, 3 );