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
				<?php _e( 'Extending the Possibilities', 'restropress' ); ?>
			</h2>
			<div class="about-text"><?php _e('RestroPress has some basic features for food ordering system. If you want more exciting premium features then we have some addons to boost your restropress powered ordering system.', 'restropress');?></div>
			
			
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

	if( ! $items ) {
		$items = rpress_fetch_items();
	}

	$rpress_installed_addons = rpress_installed_addons();

	$data = '';
	if( is_array($items) && !empty($items) ) {
		$data = '<div class="restropress-addons-all">';
		
		foreach( $items as $key => $item ) {

			$class = 'inactive';

			$class_name = trim($item->class_name);

			if( class_exists($class_name) ) {
				$class = 'installed';
			}

			$updated_class = '';
			$deactive_class = 'hide';

			if( get_option($item->license_string.'_status') == 'valid' ) {
				$updated_class = 'rpress-updated';
				$deactive_class = 'show';
			}


			$item_link = isset($item->link) ? $item->link : '';
			ob_start();
			?>
			<div class="row restropress-addon-item <?php echo $class; ?>">
				<!-- Addons Image Starts Here -->
				<div class="rp-col-xs-12 rp-col-sm-6 rp-col-md-5 rp-col-lg-5 restropress-addon-img-wrap">
					<img alt="<?php echo $item->title; ?>" src="<?php echo $item->product_image; ?>">
				</div>
				<!-- Addons Image Ends Here -->

				<!-- Addons Price and Details Starts Here -->
				<div class="rp-col-xs-12 rp-col-sm-6 rp-col-md-5 rp-col-lg-5 restropress-addon-img-wrap">
					<div class="inside">
						<h3><?php echo $item->title; ?></h3>
						<small class="rpress-addon-item-pricing"><?php echo __('from', 'restropress'). ' ' . $item->price_range; ?></small>

						<!-- Addons price wrap starts here -->
						<div class="restropress-btn-group rpress-purchase-section">
							<span class="button-secondary"><?php echo $item->price_range; ?></span>
							<a class="button button-medium button-primary " target="_blank" href="<?php echo $item_link . '?utm_source=plugin&utm_medium=addon_page&utm_campaign=promote_addon' ?>" ><?php echo __('Details and Buy', 'restropress')?></a>
						</div>
						<!-- Addons price wrap ends here -->

						<div class="restropress-installed-wrap">

						<!-- Addons Installed Starts Here -->
						<div class="restropress-btn-group rpress-installed-section pull-left">
							<button class="button button-medium button-primary"><?php echo __('Installed', 'restropress'); ?></button>
						</div>
						<!-- Addons Installed Ends Here -->

						<!-- Addon Details Starts Here -->
						<div class="restropress-btn-group rpress-addon-details-section pull-right">
							<a class="button button-medium button-primary " target="_blank" href="<?php echo $item_link . '?utm_source=plugin&utm_medium=addon_page&utm_campaign=promote_addon' ?>" ><?php echo __('Addon Details', 'restropress')?></a>
						</div>
						<!-- Addon Details Ends Here -->

						</div>

						<div class="rpress-purchased-wrap">
							<span><?php echo $item->short_content; ?></span>
							
							<div class="rpress-license-wrapper <?php echo $updated_class; ?>">
								<input type="hidden" class="rpress_license_string" name="rpress_license" value="<?php echo $item->license_string; ?>">
								<input type="text" data-license-key="" placeholder="<?php echo __('Enter your license key here'); ?>" data-item-name="<?php echo $item->title; ?>" data-item-id="<?php echo $item->id; ?>" class="rpress-license-field pull-left" name="rpress-license">
								<button data-action="activate_addon_license" class="button button-medium button-primary pull-right rpress-validate-license"><?php echo __('Activate License', 'restropress'); ?></button>
								<div class="clear"></div>
								
							</div><!-- .rpress-license-wrapper-->

							<!-- License Deactivate Starts Here -->
							<div class="clear"></div>
							<div class="rpress-license-deactivate-wrapper <?php echo $deactive_class; ?>">
								<button data-action="deactivate_addon_license" class="button  pull-left rpress-deactivate-license"><?php echo __('Deactivate License', 'restropress'); ?></button>
							</div>
							<!-- License Deactiave Ends Here -->

						</div>

					</div>
				</div>
				<!-- Addons Price and Details Ends Here -->
			</div>
			
			<?php
		}
	}
	echo ob_get_clean();
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
		$items = '<div class="error"><p>' . __( 'There was an error retrieving the extensions list from the server. Please try again later.', 'restropress' ) . '</div>';
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
	return $plugin_titles;
}
