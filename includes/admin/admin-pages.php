<?php
/**
 * Admin Pages
 *
 * @package     RPRESS
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the admin submenu pages under the RestroPress menu and assigns their
 * links to global variables
 *
 * @since 1.0.0
 * @global $rpress_discounts_page
 * @global $rpress_payments_page
 * @global $rpress_customers_page
 * @global $rpress_settings_page
 * @global $rpress_reports_page
 * @global $rpress_add_ons_page
 * @global $rpress_settings_export
 * @global $rpress_upgrades_screen
 * @return void
 */
function rpress_add_options_link() {
	global $rpress_discounts_page, $rpress_payments_page, $rpress_settings_page, $rpress_reports_page, $rpress_add_ons_page, $rpress_settings_export, $rpress_upgrades_screen, $rpress_tools_page, $rpress_delivery_page, $rpress_customers_page;

	$rpress_payment         = get_post_type_object( 'rpress_payment' );

	$customer_view_role     = apply_filters( 'rpress_view_customers_role', 'view_shop_reports' );

	$rpress_payments_page      = add_submenu_page( 'edit.php?post_type=fooditem', $rpress_payment->labels->name, $rpress_payment->labels->menu_name, 'edit_shop_payments', 'rpress-payment-history', 'rpress_payment_history_page' );
	$rpress_customers_page     = add_submenu_page( 'edit.php?post_type=fooditem', __( 'Customers', 'restro-press' ), __( 'Customers', 'restro-press' ), $customer_view_role, 'rpress-customers', 'rpress_customers_page' );
	$rpress_discounts_page     = add_submenu_page( 'edit.php?post_type=fooditem', __( 'Discount Codes', 'restro-press' ), __( 'Discount Codes', 'restro-press' ), 'manage_shop_discounts', 'rpress-discounts', 'rpress_discounts_page' );
	$rpress_reports_page       = add_submenu_page( 'edit.php?post_type=fooditem', __( 'Earnings and Sales Reports', 'restro-press' ), __( 'Reports', 'restro-press' ), 'view_shop_reports', 'rpress-reports', 'rpress_reports_page' );
	$rpress_settings_page      = add_submenu_page( 'edit.php?post_type=fooditem', __( 'RestroPress Settings', 'restro-press' ), __( 'Settings', 'restro-press' ), 'manage_shop_settings', 'rpress-settings', 'rpress_options_page' );
	$rpress_add_ons_page       = add_submenu_page( 'edit.php?post_type=fooditem', __( 'RestroPress Addons', 'restro-press' ), '<span style="color:#f39c12;">' . __( 'Addons', 'restro-press' ) . '</span>', 'manage_shop_settings', 'rpress-addons', 'rpress_add_ons_page' );
	$rpress_tools_page         = add_submenu_page( 'edit.php?post_type=fooditem', __( 'RestroPress Info and Tools', 'restro-press' ), __( 'Tools', 'restro-press' ), 'manage_shop_settings', 'rpress-tools', 'rpress_tools_page' );
	$rpress_upgrades_screen    = add_submenu_page( null, __( 'RPRESS Upgrades', 'restro-press' ), __( 'RPRESS Upgrades', 'restro-press' ), 'manage_shop_settings', 'rpress-upgrades', 'rpress_upgrades_screen' );

}
add_action( 'admin_menu', 'rpress_add_options_link', 10 );

/**
 *  Determines whether the current admin page is a specific RPRESS admin page.
 *
 *  Only works after the `wp_loaded` hook, & most effective
 *  starting on `admin_menu` hook. Failure to pass in $view will match all views of $main_page.
 *  Failure to pass in $main_page will return true if on any RPRESS page
 *
 *  @since 1.0.0
 *
 *  @param string $page Optional. Main page's slug
 *  @param string $view Optional. Page view ( ex: `edit` or `delete` )
 *  @return bool True if RPRESS admin page we're looking for or an RPRESS page or if $page is empty, any RPRESS page
 */
function rpress_is_admin_page( $passed_page = '', $passed_view = '' ) {

	global $pagenow, $typenow;

	$found      = false;
	$post_type  = isset( $_GET['post_type'] )  ? strtolower( $_GET['post_type'] )  : false;
	$action     = isset( $_GET['action'] )     ? strtolower( $_GET['action'] )     : false;
	$taxonomy   = isset( $_GET['taxonomy'] )   ? strtolower( $_GET['taxonomy'] )   : false;
	$page       = isset( $_GET['page'] )       ? strtolower( $_GET['page'] )       : false;
	$view       = isset( $_GET['view'] )       ? strtolower( $_GET['view'] )       : false;
	$rpress_action = isset( $_GET['rpress-action'] ) ? strtolower( $_GET['rpress-action'] ) : false;
	$tab        = isset( $_GET['tab'] )        ? strtolower( $_GET['tab'] )        : false;

	switch ( $passed_page ) {
		case 'fooditem':
			switch ( $passed_view ) {
				case 'list-table':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'post.php' ) {
						$found = true;
					}
					break;
				case 'new':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'post-new.php' ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) || 'fooditem' === $post_type || ( 'post-new.php' == $pagenow && 'fooditem' === $post_type ) ) {
						$found = true;
					}
					break;
			}
			break;
		case 'categories':
			switch ( $passed_view ) {
				case 'list-table':
				case 'new':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit-tags.php' && 'edit' !== $action && 'addon_category' === $taxonomy ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit-tags.php' && 'edit' === $action && 'addon_category' === $taxonomy ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit-tags.php' && 'addon_category' === $taxonomy ) {
						$found = true;
					}
					break;
			}
			break;
		case 'tags':
			switch ( $passed_view ) {
				case 'list-table':
				case 'new':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit-tags.php' && 'edit' !== $action && 'fooditem_tax' === $taxonomy ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit-tags.php' && 'edit' === $action && 'fooditem_tax' === $taxonomy ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit-tags.php' && 'fooditem_tax' === $taxonomy ) {
						$found = true;
					}
					break;
			}
			break;
		case 'payments':
			switch ( $passed_view ) {
				case 'list-table':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-payment-history' === $page && false === $view  ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-payment-history' === $page && 'view-order-details' === $view ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-payment-history' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'discounts':
			switch ( $passed_view ) {
				case 'list-table':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-discounts' === $page && false === $rpress_action ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-discounts' === $page && 'edit_discount' === $rpress_action ) {
						$found = true;
					}
					break;
				case 'new':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-discounts' === $page && 'add_discount' === $rpress_action ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-discounts' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'reports':
			switch ( $passed_view ) {
				// If you want to do something like enqueue a script on a particular report's duration, look at $_GET[ 'range' ]
				case 'earnings':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-reports' === $page && ( 'earnings' === $view || '-1' === $view || false === $view ) ) {
						$found = true;
					}
					break;
				case 'fooditems':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-reports' === $page && 'fooditems' === $view ) {
						$found = true;
					}
					break;
				case 'customers':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-reports' === $page && 'customers' === $view ) {
						$found = true;
					}
					break;
				case 'gateways':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-reports' === $page && 'gateways' === $view ) {
						$found = true;
					}
					break;
				case 'taxes':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-reports' === $page && 'taxes' === $view ) {
						$found = true;
					}
					break;
				case 'export':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-reports' === $page && 'export' === $view ) {
						$found = true;
					}
					break;
				case 'logs':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-reports' === $page && 'logs' === $view ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-reports' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'settings':
			switch ( $passed_view ) {
				case 'general':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-settings' === $page && ( 'genera' === $tab || false === $tab ) ) {
						$found = true;
					}
					break;
				case 'gateways':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-settings' === $page && 'gateways' === $tab ) {
						$found = true;
					}
					break;
				case 'emails':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-settings' === $page && 'emails' === $tab ) {
						$found = true;
					}
					break;
				case 'styles':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-settings' === $page && 'styles' === $tab ) {
						$found = true;
					}
					break;
				case 'taxes':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-settings' === $page && 'taxes' === $tab ) {
						$found = true;
					}
					break;
				case 'extensions':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-settings' === $page && 'extensions' === $tab ) {
						$found = true;
					}
					break;
				case 'licenses':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-settings' === $page && 'licenses' === $tab ) {
						$found = true;
					}
					break;
				case 'misc':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-settings' === $page && 'misc' === $tab ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-settings' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'tools':
			switch ( $passed_view ) {
				case 'general':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-tools' === $page && ( 'general' === $tab || false === $tab ) ) {
						$found = true;
					}
					break;
				case 'system_info':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-tools' === $page && 'system_info' === $tab ) {
						$found = true;
					}
					break;
				case 'import_export':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-tools' === $page && 'import_export' === $tab ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-tools' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'addons':
			if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-addons' === $page ) {
				$found = true;
			}
			break;
		case 'customers':
			switch ( $passed_view ) {
				case 'list-table':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-customers' === $page && false === $view ) {
						$found = true;
					}
					break;
				case 'overview':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-customers' === $page && 'overview' === $view ) {
						$found = true;
					}
					break;
				case 'notes':
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-customers' === $page && 'notes' === $view ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-customers' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'reports':
			if ( ( 'fooditem' == $typenow || 'fooditem' === $post_type ) && $pagenow == 'edit.php' && 'rpress-reports' === $page ) {
				$found = true;
			}
			break;
		default:
			global $rpress_discounts_page, $rpress_payments_page, $rpress_settings_page, $rpress_reports_page, $rpress_system_info_page, $rpress_settings_export, $rpress_upgrades_screen, $rpress_customers_page, $rpress_add_ons_page, $rpress_reports_page;
			$admin_pages = apply_filters( 'rpress_admin_pages', array( $rpress_discounts_page, $rpress_payments_page, $rpress_settings_page, $rpress_add_ons_page, $rpress_reports_page, $rpress_system_info_page,  $rpress_settings_export, $rpress_customers_page, $rpress_reports_page ) );
			if ( 'fooditem' == $typenow || 'index.php' == $pagenow || 'post-new.php' == $pagenow || 'post.php' == $pagenow ) {
				$found = true;
				if( 'rpress-upgrades' === $page ) {
					$found = false;
				}
			} elseif ( in_array( $pagenow, $admin_pages ) ) {
				$found = true;
			}
			break;
	}

	return (bool) apply_filters( 'rpress_is_admin_page', $found, $page, $view, $passed_page, $passed_view );
}
