<?php
/**
 * Admin Add-ons
 *
 * @package     RPRESS
 * @subpackage  Admin/Add-ons
 * @copyright   Copyright (c) 2019, 
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add-ons Page
 *
 * Renders the add-ons page content.
 *
 * @since 1.0
 * @return void
 */
function rpress_add_ons_page() {
	ob_start(); ?>
	<div class="wrap" id="rpress-add-ons">
		<h1 class="wp-heading-inline">
			<?php echo rpress_get_label_plural(); ?>
		</h1>
		<hr class="wp-header-end">
		<!-- RestroPress Addons Starts Here-->
		<div class="rpress-about-body">
			<h2>
				<?php _e( 'Extending the Possibilities', 'restro-press' ); ?>
			</h2>
			<div class="about-text"><?php _e('Even though RestroPress has a lot of built-in features, it is impossible to make everyone happy. This is why we have lots of addons to boost your restropress powsered ordering system.', 'restro-press');?></div>
			
			<a href="https://www.restropress.com/" target="_blank" class="button button-large button-primary"><?php _e('Browse Add Ons', 'restro-press'); ?></a>
		</div>
		<!-- RestroPress Addons Ends Here -->
		<div class="rpress-add-ons-view-wrapper">
			
		</div>
		
	</div>
	<?php
	echo ob_get_clean();
}

/**
 * Add-ons Get Feed
 *
 * Gets the add-ons page feed.
 *
 * @since 1.0
 * @return void
 */
function rpress_add_ons_get_feed( $tab = 'popular' ) {
	$cache = get_transient( 'restropress_add_ons_feed_' . $tab );

	$cache = '';

	if ( false === $cache ) {
		$url = 'https://restropress.com/?feed=addons';

		if ( 'popular' !== $tab ) {
			$url = add_query_arg( array( 'display' => $tab ), $url );
		}

		$feed = wp_remote_get( esc_url_raw( $url ), array( 'sslverify' => false ) );

		if ( ! is_wp_error( $feed ) ) {
			if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
				$cache = wp_remote_retrieve_body( $feed );
				set_transient( 'restropress_add_ons_feed_' . $tab, $cache, 3600 );
			}
		} else {
			$cache = '<div class="error"><p>' . __( 'There was an error retrieving the extensions list from the server. Please try again later.', 'restro-press' ) . '</div>';
		}
	}

	if ( isset( $_GET['view'] ) && 'integrations' === $_GET['view'] ) {
		// Set a new campaign for tracking purposes
		//$cache = str_replace( 'RPRESSAddonsPage', 'RPRESSIntegrationsPage', $cache );
	}

	return $cache;
}
