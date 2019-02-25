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
		<hr class="wp-header-end">
		<!-- RestroPress Addons Starts Here-->
		<div class="rpress-about-body">
			<h2>
				<?php _e( 'Extending the Possibilities', 'restro-press' ); ?>
			</h2>
			<div class="about-text"><?php _e('RestroPress has some basic features for food ordering system. If you want more exciting premium features then we have some addons to boost your restropress powered ordering system.', 'restro-press');?></div>
			
			
		</div>
		<!-- RestroPress Addons Ends Here -->
		<div class="rpress-add-ons-view-wrapper">
			<?php echo rpress_add_ons_get_feed(); ?>
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
function rpress_add_ons_get_feed() {
	$items = get_transient( 'restropress_add_ons_feed' );
	if( !$items ) {
		$items = rpress_fetch_items();
	}

	$rpress_installed_addons = rpress_installed_addons();

	$data = '';
	if( is_array($items) && !empty($items) ) {
		$data = '<div class="restropress-addons-all">';
		
		foreach( $items as $key => $item ) {

			$class = '';
			
			if( in_array($item->title, $rpress_installed_addons) ) {
				$class = 'purchased';
			}

			$item_link = isset($item->link) ? $item->link : '';
			
			$data .= '<div class="row restropress-addon-item '.$class.' ">';

			//Addons Image Starts Here
			$data .= '<div class="col-xs-12 col-sm-6 col-md-5 col-lg-5 restropress-addon-img-wrap">';
			$data .= '<img alt="'.$item->title.'" src="'.$item->product_image.'">';
			$data .= '</div>';
			//Addons Image Ends Here

			//Addons Price and Details Starts Here
			$data .= '<div class="col-xs-12 col-sm-6 col-md-5 col-lg-5 restropress-addon-img-wrap">';
			$data .= '<div class="inside">';
			$data .= '<h3>'.$item->title.'</h3>';
			$data .= '<small class="rpress-addon-item-pricing">'.__('from', 'restro-press'). ' ' . $item->price_range . '</small>';
				
			//Addons price wrap starts here
			$data .= '<div class="restropress-btn-group">';
			$data .= '<span class="button-secondary">'.$item->price_range.'</span>';
			$data .= '<a class="button button-medium button-primary " target="_blank" href="'.$item_link.'?utm_source=plugin&utm_medium=addon_page&utm_campaign=promote_addon">'.__('Details and Buy', 'restro-press').'</a>';
			$data .= '</div>';
			//Addons price wrap ends here

			$data .= '<div class="rpress-purchased-wrap">';
			$data .= '<span>'.$item->short_content.'</span>';

			$data .= '<div class="rpress-license-wrapper">';
			$data .= '<button class="button button-medium button-primary">'.__('Activate License').'</button>';
			$data .= '</div>';

			$data .= '</div>';
			//$data .= '<button class="button button-medium button-primary">'.__('Already Purchased? Validate License', 'restro-press').'</button>';

			$data .= '</div>';
			$data .= '</div>';
			//addons Price and Details Ends Here

			$data .= '</div>';
		}
			$data .= '</div>';
		}
		else {
			echo $items;
		}
	
	return $data;
}

function rpress_fetch_items() {
	$url = 'https://www.restropress.com/wp-json/restropress-server/';
	$version = '1.0';
	$remote_url = $url . 'v' . $version;

	$feed = wp_remote_get( esc_url_raw( $remote_url ), array( 'sslverify' => false ) );
	$items = array();

	if ( ! is_wp_error( $feed ) ) {
		if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
			$items = wp_remote_retrieve_body( $feed );
			$items = json_decode($items);
			set_transient( 'restropress_add_ons_feed', $items, 3600 );
		}
	} else {
		$items = '<div class="error"><p>' . __( 'There was an error retrieving the extensions list from the server. Please try again later.', 'restro-press' ) . '</div>';
	}
	return $items;
}

function rpress_installed_addons() {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$all_plugins = get_plugins();
	$plugin_titles = array();

	if( is_array($all_plugins) ) {
		foreach( $all_plugins as $key => $get_plugin ) {
			array_push($plugin_titles, $get_plugin['Name']);
		}
	}
	print_r($plugin_titles);
	return $plugin_titles;
}
