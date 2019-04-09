<?php
/**
 * Plugin Compatibility
 *
 * Functions for compatibility with other plugins.
 *
 * @package     RPRESS
 * @subpackage  Functions/Compatibility
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Disables admin sorting of Post Types Order
 *
 * When sorting fooditems by price, earnings, sales, date, or name,
 * we need to remove the posts_orderby that Post Types Order imposes
 *
 * @since  1.0.0
 * @return void
 */
function rpress_remove_post_types_order() {
	remove_filter( 'posts_orderby', 'CPTOrderPosts' );
}
add_action( 'load-edit.php', 'rpress_remove_post_types_order' );

/**
 * Disables opengraph tags on the checkout page
 *
 * There is a bizarre conflict that makes the checkout errors not get displayed
 * when the Jetpack opengraph tags are displayed
 *
 * @since 1.0.0.1
 * @return bool
 */
function rpress_disable_jetpack_og_on_checkout() {
	if ( rpress_is_checkout() ) {
		remove_action( 'wp_head', 'jetpack_og_tags' );
	}
}
add_action( 'template_redirect', 'rpress_disable_jetpack_og_on_checkout' );

/**
 * Checks if a caching plugin is active
 *
 * @since 1.0
 * @return bool $caching True if caching plugin is enabled, false otherwise
 */
function rpress_is_caching_plugin_active() {
	$caching = ( function_exists( 'wpsupercache_site_admin' ) || defined( 'W3TC' ) || function_exists( 'rocket_init' ) );
	return apply_filters( 'rpress_is_caching_plugin_active', $caching );
}

/**
 * Adds a ?nocache option for the checkout page
 *
 * This ensures the checkout page remains uncached when plugins like WP Super Cache are activated
 *
 * @since 1.0
 * @param array $settings Misc Settings
 * @return array $settings Updated Misc Settings
 */
function rpress_append_no_cache_param( $settings ) {
	if ( ! rpress_is_caching_plugin_active() )
		return $settings;

	$settings[] = array(
		'id' => 'no_cache_checkout',
		'name' => __('No Caching on Checkout?','restropress' ),
		'desc' => __('Check this box in order to append a ?nocache parameter to the checkout URL to prevent caching plugins from caching the page.','restropress' ),
		'type' => 'checkbox'
	);

	return $settings;
}
add_filter( 'rpress_settings_misc', 'rpress_append_no_cache_param', -1 );

/**
 * Show the correct language on the [fooditems] shortcode if qTranslate is active
 *
 * @since  1.0.0
 * @param string $content 
 * @return string $content 
 */
function rpress_qtranslate_content( $content ) {
	if( defined( 'QT_LANGUAGE' ) )
		$content = qtrans_useCurrentLanguageIfNotFoundShowAvailable( $content );
	return $content;
}
add_filter( 'rpress_fooditems_content', 'rpress_qtranslate_content' );
add_filter( 'rpress_fooditems_excerpt', 'rpress_qtranslate_content' );

/**
 * Prevents qTranslate from redirecting to language-specific URL when fooditeming purchased files
 *
 * @since  1.0.0
 * @param string       $target Target URL
 * @return string|bool $target Target URL. False if redirect is disabled
 */
function rpress_qtranslate_prevent_redirect( $target ) {

	if( strpos( $target, 'rpressfile' ) ) {
		$target = false;
		global $q_config;
		$q_config['url_mode'] = '';
	}

	return $target;
}
add_filter( 'qtranslate_language_detect_redirect', 'rpress_qtranslate_prevent_redirect' );

/**
 * Disable the WooCommerce 'Un-force SSL when leaving checkout' option on RPRESS checkout
 * to prevent redirect loops
 *
 * @since  1.0.0
 * @return void
 */
function rpress_disable_woo_ssl_on_checkout() {
	if( rpress_is_checkout() && rpress_is_ssl_enforced() ) {
		remove_action( 'template_redirect', array( 'WC_HTTPS', 'unforce_https_template_redirect' ) );
	}
}
add_action( 'template_redirect', 'rpress_disable_woo_ssl_on_checkout', 9 );

/**
 * Disables the mandrill_nl2br filter while sending RPRESS emails
 *
 * @since  1.0.0
 * @return void
 */
function rpress_disable_mandrill_nl2br() {
	add_filter( 'mandrill_nl2br', '__return_false' );
}
add_action( 'rpress_email_send_before', 'rpress_disable_mandrill_nl2br');

/**
 * Prevents the Purchase Confirmation screen from being detected as a 404 error in the 404 Redirected plugin
 *
 * @since 1.0.0
 * @return void
 */
function rpress_disable_404_redirected_redirect() {

	if( ! defined( 'WBZ404_VERSION' ) ) {
		return;
	}

	if( rpress_is_success_page() ) {
		remove_action( 'template_redirect', 'wbz404_process404', 10 );
	}
}
add_action( 'template_redirect', 'rpress_disable_404_redirected_redirect', 9 );

/**
 * Adds 'rpress' to the list of Say What aliases after moving to WordPress.org language packs
 *
 * @since 1.0.0
 * @param  array $aliases Say What domain aliases
 * @return array          Say What domain alises with 'rpress' added
 */
function rpress_say_what_domain_aliases( $aliases ) {
	$aliases['restropress'][] = 'rpress';

	return $aliases;
}
add_filter( 'say_what_domain_aliases', 'rpress_say_what_domain_aliases', 10, 1 );

/**
 * Removes the Really Simple SSL mixed content filter during file fooditems to avoid
 * errors with chunked file delivery
 *
 * @see https://github.com/rlankhorst/really-simple-ssl/issues/30
 *
 * @since 1.0.10
 * @return void
 */
function rpress_rsssl_remove_mixed_content_filter() {
	if ( class_exists( 'REALLY_SIMPLE_SSL' ) && did_action( 'rpress_process_verified_fooditem' ) ) {
		remove_action( 'init', array( RSSSL()->rsssl_mixed_content_fixer, 'start_buffer' ) );
		remove_action( 'shutdown', array( RSSSL()->rsssl_mixed_content_fixer, 'end_buffer' ) );
	}
}
add_action( 'plugins_loaded', 'rpress_rsssl_remove_mixed_content_filter', 999 );