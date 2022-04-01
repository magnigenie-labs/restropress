<?php
/**
 * Contextual Help
 *
 * @package     RPRESS
 * @subpackage  Admin/RestroPress
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since  1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Adds the Contextual Help for the main RestroPress page
 *
 * @since 1.0
 * @return void
 */
function rpress_fooditems_contextual_help() {

	$screen = get_current_screen();

	if ( $screen->id != 'fooditem' )
		return;

	$screen->add_help_tab( array(
		'id'	    => 'rpress-fooditem-prices',
		'title'	    => sprintf( __( '%s Prices', 'restropress' ), rpress_get_label_singular() ),
		'content'	=>
			'<p>' . __( '<strong>Enable variable pricing</strong> - By enabling variable pricing, multiple fooditem options and prices can be configured.', 'restropress' ) . '</p>' .

			'<p>' . __( '<strong>Enable multi-option purchases</strong> - By enabling multi-option purchases customers can add multiple variable price items to their cart at once.', 'restropress' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'rpress-product-notes',
		'title'	    => sprintf( __( '%s Notes', 'restropress' ), rpress_get_label_singular() ),
		'content'	=> '<p>' . __( 'Special notes or instructions for the product. These notes will be added to the purchase receipt, and additionally may be used by some extensions or themes on the frontend.', 'restropress' ) . '</p>'
	) );

	$colors = array(
		'gray', 'pink', 'blue', 'green', 'teal', 'black', 'dark gray', 'orange', 'purple', 'slate'
	);

	$screen->add_help_tab( array(
		'id'	    => 'rpress-purchase-shortcode',
		'title'	    => __( 'Purchase Shortcode', 'restropress' ),
		'content'	=>
			'<p>' . __( '<strong>Purchase Shortcode</strong> - If the automatic output of the purchase button has been disabled via the RestroPress Configuration box, a shortcode can be used to output the button or link.', 'restropress' ) . '</p>' .
			'<p><code>[purchase_link id="#" price="1" text="Add to Cart" color="blue"]</code></p>' .
			'<ul>
				<li><strong>id</strong> - ' . __( 'The ID of a specific fooditem to purchase.', 'restropress' ) . '</li>
				<li><strong>price</strong> - ' . __( 'Whether to show the price on the purchase button. 1 to show the price, 0 to disable it.', 'restropress' ) . '</li>
				<li><strong>text</strong> - ' . __( 'The text to be displayed on the button or link.', 'restropress' ) . '</li>
				<li><strong>style</strong> - ' . __( '<em>button</em> | <em>text</em> - The style of the purchase link.', 'restropress' ) . '</li>
				<li><strong>color</strong> - <em>' . implode( '</em> | <em>', $colors ) . '</em></li>
				<li><strong>class</strong> - ' . __( 'One or more custom CSS classes you want applied to the button.', 'restropress' ) . '</li>
			</ul>' .
			'<p>' . sprintf( __( 'For more information, see <a href="%s">using Shortcodes</a> on the WordPress.org Codex or <a href="%s">RestroPress Documentation</a>', 'restropress' ), 'https://codex.wordpress.org/Shortcode', 'http://docs.restropress.com/article/229-purchaselink' ) . '</p>'
	) );

	/**
	 * Fires off in the RPRESS RestroPress Contextual Help Screen
	 *
	 * @since 1.0
	 * @param object $screen The current admin screen
	 */
	do_action( 'rpress_fooditems_contextual_help', $screen );
}
add_action( 'load-post.php', 'rpress_fooditems_contextual_help' );
add_action( 'load-post-new.php', 'rpress_fooditems_contextual_help' );
