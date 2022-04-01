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
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'RP_Admin_Menus', false ) ) {
	return new RP_Admin_Menus();
}


/**
 * RP_Admin_Menus Class.
 */
class RP_Admin_Menus {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		// Add menus.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'menu_order_count' ) );
		
		//Custom menu ordering
		add_filter( 'custom_menu_order', '__return_true' );
		add_filter( 'menu_order', array( $this, 'menu_order' ) );
	}

	/**
	 * Add menu items.
	 */
	public function admin_menu() {
		global $menu;

		$menu[] = array( '', 'read', 'separator-restropress', '', 'wp-menu-separator restropress' );

		$rpress_payment 	= get_post_type_object( 'rpress_payment' );
		$customer_view_role = apply_filters( 'rpress_view_customers_role', 'view_shop_reports' );

		add_menu_page( __( 'RestroPress', 'restropress' ), __( 'RestroPress', 'restropress' ), 'manage_shop_settings', 'restropress', null, null, '55.5' );

		add_submenu_page( 'restropress', $rpress_payment->labels->name, $rpress_payment->labels->menu_name, 'edit_shop_payments', 'rpress-payment-history', 'rpress_payment_history_page', null , null );

		add_submenu_page( 'restropress', __( 'Customers', 'restropress' ), __( 'Customers', 'restropress' ), $customer_view_role, 'rpress-customers', 'rpress_customers_page', null, null );

		add_submenu_page( 'restropress', __( 'Discount Codes', 'restropress' ), __( 'Discount Codes', 'restropress' ), 'manage_shop_discounts', 'rpress-discounts', 'rpress_discounts_page' );

		add_submenu_page( 'restropress', __( 'Earnings and Sales Reports', 'restropress' ), __( 'Reports', 'restropress' ), 'view_shop_reports', 'rpress-reports', 'rpress_reports_page' );

		add_submenu_page( 'restropress', __( 'RestroPress Settings', 'restropress' ), __( 'Settings', 'restropress' ), 'manage_shop_settings', 'rpress-settings', 'rpress_options_page' );

		add_submenu_page( 'restropress', __( 'RestroPress Info and Tools', 'restropress' ), __( 'Tools', 'restropress' ), 'manage_shop_settings', 'rpress-tools', 'rpress_tools_page' );

		add_submenu_page( 'restropress', __( 'RestroPress Extensions', 'restropress' ), '<span style="color:#f39c12;">' . __( 'Extensions', 'restropress' ) . '</span>', 'manage_shop_settings', 'rpress-extensions', 'rpress_extensions_page' );

		// Remove the additional restropress menu
		remove_submenu_page( 'restropress', 'restropress' );

	}

	/**
	 * Adds the order pending count to the menu.
	 */
	public function menu_order_count() {
		global $submenu;

		if ( isset( $submenu['restropress'] ) ) {
			// Remove 'RestroPress' sub menu item.
			unset( $submenu['restropress'][0] );

			// Add count if user has access.
			if ( apply_filters( 'rpress_include_pending_order_count_in_menu', true ) && current_user_can( 'edit_shop_payments' ) ) {
				$order_count = apply_filters( 'rpress_menu_order_count', rp_get_order_count( 'pending' ) );

				if ( $order_count ) {
					foreach ( $submenu['restropress'] as $key => $menu_item ) {
						if ( 0 === strpos( $menu_item[0], _x( 'Orders', 'Admin menu name', 'restropress' ) ) ) {
							$submenu['restropress'][ $key ][0] .= ' <span class="awaiting-mod update-plugins count-' . esc_attr( $order_count ) . '"><span class="processing-count">' . number_format_i18n( $order_count ) . '</span></span>';
							break;
						}
					}
				}
			}
		}
	}

	/**
	 * Reorder the RestroPress menu items in admin.
	 *
	 * @param int $menu_order Menu order.
	 * @return array
	 */
	public function menu_order( $menu_order ) {

		// Initialize our custom order array.
		$rpress_menu_order = array();

		// Get the index of our custom separator.
		$rpress_separator = array_search( 'separator-restropress', $menu_order, true );

		// Get index of fooditem menu.
		$rpress_fooditems = array_search( 'edit.php?post_type=fooditem', $menu_order, true );

		//Remove the custom separator and fooditems menu so that we can re-order them
		unset( $menu_order[ $rpress_separator ] );
		unset( $menu_order[ $rpress_fooditems ] );

		// Loop through menu order and do some rearranging.
		foreach ( $menu_order as $index => $item ) {

			if ( 'restropress' === $item ) {
				$rpress_menu_order[] = 'separator-restropress';
				$rpress_menu_order[] = $item;
				$rpress_menu_order[] = 'edit.php?post_type=fooditem';
			} elseif ( ! in_array( $item, array( 'separator-restropress' ), true ) ) {
				$rpress_menu_order[] = $item;
			}
		}
		// Return order.
		return $rpress_menu_order;
	}
}

return new RP_Admin_Menus();
