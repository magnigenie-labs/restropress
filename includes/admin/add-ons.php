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
function rpress_extensions_page() {
	ob_start(); ?>
	<div class="wrap" id="rpress-add-ons">
		<hr class="wp-header-end">
		<!-- RestroPress Addons Starts Here-->
		<div class="rpress-about-body">
			<h2>
				<?php esc_html_e( 'Extending the Possibilities', 'restropress' ); ?>
			</h2>
			<div class="about-text"><?php esc_html_e('RestroPress has some basic features for food ordering system. If you want more exciting premium features then we have some addons to boost your restropress powered ordering system.', 'restropress');?></div>
		</div>
		<div class="rpress-plugin-filter">
			<div>
			<?php 
				$base  = admin_url('admin.php?page=rpress-extensions'); 
				$current        = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ): '';

				 echo sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( 'status', $base ), $current === 'all' || $current == '' ? ' class="current"' : '', __('All', 'restropress') ) .'  |  '; echo sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'active', $base ), $current === 'active' ? ' class="current"' : '', __('active', 'restropress')  );
				?> 
			</div>
		</div>
		<div class="rpress-search-view-wrapper">

	 	    <div style="position: right; float:right; padding-bottom: 5px;" class="rpress-search-wrap rpress-live-search">
	 	      <input id="rpress-plugin-search" type="text" placeholder="<?php esc_html_e( 'Search plugins', 'restropress' ); ?>">
	 	    </div>
			 	    
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

	$data = '';
	if( is_array( $items ) && !empty( $items ) ) {
		$data = '<div class="restropress-addons-all">';

		foreach( $items as $key => $item ) {

			$class = 'inactive';

			$class_name = trim( $item->class_name );		

			if ( isset( $_GET['status'] ) && isset( $_GET['status'] ) == 'active') {
				if ( !class_exists( $class_name ) ) {
					continue;
				}
			}

			if( class_exists( $class_name ) ) {

				$class = 'installed';
			}

			$updated_class = '';
			$deactive_class = 'hide';

			if( get_option( $item->text_domain . '_license_status' ) == 'valid' ) {
				$updated_class = 'rpress-updated';
				$deactive_class = 'show';
			}

			$item_link = isset( $item->link ) ? $item->link : '';
			ob_start();
			?>
			<div class="rp-col-xs-12 rp-col-sm-4 rp-col-md-4 rp-col-lg-4 restropress-addon-item <?php echo esc_attr( $class ); ?>">
				<!-- Addons Inner Wrap Starts Here -->
				<div class="rp-addin-item-inner-wrap">
					<h3 class="rpress-addon-title" ><?php echo esc_html( $item->title ); ?></h3>
					<!-- Addons Image Starts Here -->
					<div class="restropress-addon-img-wrap">
						<img alt="<?php echo esc_attr( $item->title ); ?>" src="<?php echo esc_url( $item->product_image ); ?>">
					</div>
					<div class="rp-addon-main-wrap">
						<!-- Addons Image Ends Here -->
						<div class="rp-addon-info">
							<span><?php echo esc_html( $item->short_content ); ?></span>
						</div>

						<div class="rpress-purchased-wrap">

							<div class="rpress-license-wrapper <?php echo esc_attr( $updated_class ); ?>">
								<input type="hidden" class="rpress_license_string" name="rpress_license" value="<?php echo esc_attr( $item->text_domain . '_license' ); ?>">
								<input type="text" data-license-key="" placeholder="<?php esc_html_e('Enter your license key here'); ?>" data-item-name="<?php echo esc_attr( $item->title ); ?>" data-item-id="<?php echo esc_attr( $item->id ); ?>" class="rpress-license-field pull-left" name="rpress-license">
								<button data-action="rpress_activate_addon_license" class="button button-medium button-primary pull-right rpress-validate-license"><?php esc_html_e('Activate License', 'restropress'); ?></button>
								<div class="clear"></div>

							</div><!-- .rpress-license-wrapper-->

							<!-- License Deactivate Starts Here -->
							<div class="clear"></div>
							<div class="rpress-license-deactivate-wrapper <?php echo esc_attr( $deactive_class ); ?>">
								<div class="rp-license-deactivate-inner">
									<button data-action="rpress_deactivate_addon_license" class="button  pull-left rpress-deactivate-license"><?php esc_html_e('Deactivate', 'restropress'); ?></button>
									<small class="rpress-addon-item-pricing"><?php esc_html_e('From ', 'restropress') . rpress_currency_filter( rpress_format_amount( $item->price_range ) ); ?></small>
								</div>
							</div>
							<div class="rpress-license-default-wrapper <?php echo esc_attr( $deactive_class ); ?>">
								<div class="restropress-btn-group rpress-addon-details-section pull-left">
								<a class="button button-medium button-primary " target="_blank" href="<?php echo esc_attr( $item_link . '?utm_source=plugin&utm_medium=addon_page&utm_campaign=promote_addon' ); ?>" ><?php esc_html_e('View Details', 'restropress')?></a>
								<small class="rpress-addon-item-pricing"><?php esc_html_e('From ', 'restropress') . rpress_currency_filter( rpress_format_amount( $item->price_range ) ); ?></small>
								</div>
							</div>
							<!-- License Deactiave Ends Here -->

						</div>
					</div>

				</div>
				<!-- Addons Inner Wrap Ends Here -->
			</div>

			<?php
		}
	} else { ?>
		<div class="restropress-addons-all">
			<span><?php esc_html_e( 'Something went wrong. Please try after sometime..', 'restropress' ); ?>
			</span>
		</div>;
	<?php }
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