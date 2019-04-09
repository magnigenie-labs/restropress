<?php
/**
 * Scripts
 *
 * @package     RPRESS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Load Scripts
 *
 * Enqueues the required scripts.
 *
 * @since 1.0
 * @global $post
 * @return void
 */
function rpress_load_scripts() {
	global $post;

	$js_dir = RPRESS_PLUGIN_URL . 'assets/js/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Get position in cart of current fooditem
	if ( isset( $post->ID ) ) {
		$position = rpress_get_item_position_in_cart( $post->ID );
	}

	$has_purchase_links = false;
	if ( ( ! empty( $post->post_content ) && ( has_shortcode( $post->post_content, 'purchase_link' ) || has_shortcode( $post->post_content, 'fooditems' ) ) ) || is_post_type_archive( 'fooditem' ) ) {
		$has_purchase_links = true;
	}

	$in_footer = rpress_scripts_in_footer();

	if ( rpress_is_checkout() ) {
		if ( rpress_is_cc_verify_enabled() ) {
			wp_register_script( 'creditCardValidator', $js_dir . 'jquery.creditCardValidator' . $suffix . '.js', array( 'jquery' ), RPRESS_VERSION, $in_footer );

			// Registered so gateways can enqueue it when they support the space formatting. wp_enqueue_script( 'jQuery.payment' );
			wp_register_script( 'jQuery.payment', $js_dir . 'jquery.payment.min.js', array( 'jquery' ), RPRESS_VERSION, $in_footer );

			wp_enqueue_script( 'creditCardValidator' );
		}

		wp_register_script( 'rpress-checkout-global', $js_dir . 'rpress-checkout-global' . $suffix . '.js', array( 'jquery' ), RPRESS_VERSION, $in_footer );
		wp_enqueue_script( 'rpress-checkout-global' );

		wp_localize_script( 'rpress-checkout-global', 'rpress_global_vars', apply_filters( 'rpress_global_checkout_script_vars', array(
			'ajaxurl'               => rpress_get_ajax_url(),
			'checkout_nonce'        => wp_create_nonce( 'rpress_checkout_nonce' ),
			'checkout_error_anchor' => '#rpress_purchase_submit',
			'currency_sign'         => rpress_currency_filter(''),
			'currency_pos'          => rpress_get_option( 'currency_position', 'before' ),
			'decimal_separator'     => rpress_get_option( 'decimal_separator', '.' ),
			'thousands_separator'   => rpress_get_option( 'thousands_separator', ',' ),
			'no_gateway'            => __( 'Please select a payment method', 'restropress' ),
			'no_discount'           => __( 'Please enter a discount code', 'restropress' ), // Blank discount code message
			'enter_discount'        => __( 'Enter discount', 'restropress' ),
			'discount_applied'      => __( 'Discount Applied', 'restropress' ), // Discount verified message
			'no_email'              => __( 'Please enter an email address before applying a discount code', 'restropress' ),
			'no_username'           => __( 'Please enter a username before applying a discount code', 'restropress' ),
			'purchase_loading'      => __( 'Please Wait...', 'restropress' ),
			'complete_purchase'     => rpress_get_checkout_button_purchase_label(),
			'taxes_enabled'         => rpress_use_taxes() ? '1' : '0',
			'rpress_version'           => RPRESS_VERSION
		) ) );
	}


	// Get Delivery Cutoff Hours 
	$cutof_hours = Rpress_Delivery_Cut_Hours();
	$cutoff_start_time = '';
	$cutof_end_time = '';
	if( is_array($cutof_hours) ) {
		$cutoff_start_time = isset($cutof_hours['cutoff_start_time']) ? $cutof_hours['cutoff_start_time'] : '';
		$cutof_end_time = isset($cutof_hours['cutoff_end_time']) ? $cutof_hours['cutoff_end_time'] : '';
	}

	$cutoff_hours = array();
	$open_hours = array();
	$get_holiday_message = '';
	if( class_exists('RestroPress_Store_Timing') ) {
		$store_timings = new RestroPress_Store_Timing();
		$cutoff_hours = $store_timings->get_all_cutoff_hours();
		$open_hours = $store_timings->get_all_open_hours();
		$get_holiday_message = $store_timings->get_holiday_message();
	}
	
	

	// Load AJAX scripts, if enabled
	if ( ! rpress_is_ajax_disabled() ) {
		wp_register_script( 'rpress-ajax', $js_dir . 'rpress-ajax.js', array( 'jquery' ), RPRESS_VERSION, $in_footer );
		wp_enqueue_script( 'rpress-ajax' );

		wp_localize_script( 'rpress-ajax', 'rpress_scripts', apply_filters( 'rpress_ajax_script_vars', array(
			'ajaxurl'                 => rpress_get_ajax_url(),
			'position_in_cart'        => isset( $position ) ? $position : -1,
			'has_purchase_links'      => $has_purchase_links,
			'already_in_cart_message' => __('You have already added this item to your cart','restropress' ), // Item already in the cart message
			'empty_cart_message'      => __('Your cart is empty','restropress' ), // Item already in the cart message
			'loading'                 => __('Loading','restropress' ) , // General loading message
			'select_option'           => __('Please select an option','restropress' ) , // Variable pricing error with multi-purchase option enabled
			'is_checkout'             => rpress_is_checkout() ? '1' : '0',
			'default_gateway'         => rpress_get_default_gateway(),
			'redirect_to_checkout'    => ( rpress_straight_to_checkout() || rpress_is_checkout() ) ? '1' : '0',
			'checkout_page'           => rpress_get_checkout_uri(),
			'permalinks'              => get_option( 'permalink_structure' ) ? '1' : '0',
			'quantities_enabled'      => rpress_item_quantities_enabled(),
			'taxes_enabled'           => rpress_use_taxes() ? '1' : '0', // Adding here for widget, but leaving in checkout vars for backcompat
			'open_hours'          	  => rpress_get_option('open_time'),
			'close_hours'          	  => rpress_get_option('close_time'),
			'please_wait'							=> __('Please Wait', 'restropress'),
			'rpress_holidays' 				=> rpress_get_holidays_lists(),
			'rpress_pre_order_until' 	=> rpress_show_preorder_until(),
			'rpress_cutoff_starts'		=> $cutoff_start_time,
			'rpress_cutoff_ends'			=> $cutof_end_time,
			'cutoff_hours'						=> $cutoff_hours,
			'store_open_hours'				=> $open_hours,
			'holiday_message'					=> $get_holiday_message,
			'add_to_cart'							=> __('Add To Cart', 'restropress'),
			'update_cart'							=> __('Update Cart', 'restropress'),
			'button_color'   					=> rpress_get_option( 'checkout_color', 'red' ),
			'check_delivery_fee_enabled'		=> check_delivery_fee_enabled()
		) ) );
	}
}
add_action( 'wp_enqueue_scripts', 'rpress_load_scripts' );

/**
 * Register Styles
 *
 * Checks the styles option and hooks the required filter.
 *
 * @since 1.0
 * @return void
 */
function rpress_register_styles() {
	if ( rpress_get_option( 'disable_styles', false ) ) {
		return;
	}

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	$file          = 'rpress' . $suffix . '.css';
	$templates_dir = rpress_get_theme_template_dir_name();

	$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
	$child_theme_style_sheet_2  = trailingslashit( get_stylesheet_directory() ) . $templates_dir . 'rpress.css';
	$parent_theme_style_sheet   = trailingslashit( get_template_directory()   ) . $templates_dir . $file;
	$parent_theme_style_sheet_2 = trailingslashit( get_template_directory()   ) . $templates_dir . 'rpress.css';
	$rpress_plugin_style_sheet     = trailingslashit( rpress_get_templates_dir()    ) . $file;

	// Look in the child theme directory first, followed by the parent theme, followed by the RPRESS core templates directory
	// Also look for the min version first, followed by non minified version, even if SCRIPT_DEBUG is not enabled.
	// This allows users to copy just rpress.css to their theme
	if ( file_exists( $child_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $child_theme_style_sheet_2 ) ) ) ) {
		if( ! empty( $nonmin ) ) {
			$url = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . 'rpress.css';
		} else {
			$url = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . $file;
		}
	} elseif ( file_exists( $parent_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $parent_theme_style_sheet_2 ) ) ) ) {
		if( ! empty( $nonmin ) ) {
			$url = trailingslashit( get_template_directory_uri() ) . $templates_dir . 'rpress.css';
		} else {
			$url = trailingslashit( get_template_directory_uri() ) . $templates_dir . $file;
		}
	} elseif ( file_exists( $rpress_plugin_style_sheet ) || file_exists( $rpress_plugin_style_sheet ) ) {
		$url = trailingslashit( rpress_get_templates_url() ) . $file;
	}

	wp_register_style( 'rpress-styles', $url, array(), RPRESS_VERSION, 'all' );
	wp_enqueue_style( 'rpress-styles' );
}
add_action( 'wp_enqueue_scripts', 'rpress_register_styles' );

/**
 * Load Admin Scripts
 *
 * Enqueues the required admin scripts.
 *
 * @since 1.0
 * @global $post
 * @param string $hook Page hook
 * @return void
 */
function rpress_load_admin_scripts( $hook ) {

	// if ( ! apply_filters( 'rpress_load_admin_scripts', rpress_is_admin_page(), $hook ) ) {
	// 	return;
	// }

	global $post;

	$js_dir  = RPRESS_PLUGIN_URL . 'assets/js/';
	$css_dir = RPRESS_PLUGIN_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	//$suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	$suffix  = '';
	// These have to be global
	wp_register_style( 'jquery-chosen', $css_dir . 'chosen' . $suffix . '.css', array(), RPRESS_VERSION );
	wp_enqueue_style( 'jquery-chosen' );

	wp_register_script( 'jquery-chosen', $js_dir . 'chosen.jquery' . $suffix . '.js', array( 'jquery' ), RPRESS_VERSION );
	wp_enqueue_script( 'jquery-chosen' );

	wp_enqueue_script( 'jquery-form' );

	$admin_deps = array();

	if ( ! rpress_is_admin_page( $hook, 'edit' ) && ! rpress_is_admin_page( $hook, 'new' ) ) {
		$admin_deps = array( 'jquery', 'jquery-form', 'inline-edit-post' );
	} else {
		$admin_deps = array( 'jquery', 'jquery-form' );
	}

	wp_register_script( 'rpress-admin-scripts', $js_dir . 'admin-scripts' . $suffix . '.js', $admin_deps, RPRESS_VERSION, false );

	wp_enqueue_script( 'rpress-admin-scripts' );

	wp_localize_script( 'rpress-admin-scripts', 'rpress_vars', array(
		'post_id'                     => isset( $post->ID ) ? $post->ID : null,
		'rpress_version'                 => RPRESS_VERSION,
		'add_new_fooditem'            => __( 'Add New Food Item', 'restropress' ),
		'use_this_file'               => __( 'Use This File', 'restropress' ),
		'quick_edit_warning'          => __( 'Sorry, not available for variable priced products.', 'restropress' ),
		'delete_payment'              => __( 'Are you sure you wish to delete this payment?', 'restropress' ),
		'delete_payment_note'         => __( 'Are you sure you wish to delete this note?', 'restropress' ),
		'delete_tax_rate'             => __( 'Are you sure you wish to delete this tax rate?', 'restropress' ),
		'resend_receipt'              => __( 'Are you sure you wish to resend the purchase receipt?', 'restropress' ),
		'disconnect_customer'         => __( 'Are you sure you wish to disconnect the WordPress user from this customer record?', 'restropress' ),
		'copy_fooditem_link_text'     => __( 'Copy these links to your clipboard and give them to your customer', 'restropress' ),
		'delete_payment_fooditem'     => sprintf( __( 'Are you sure you wish to delete this %s?', 'restropress' ), rpress_get_label_singular() ),
		'one_price_min'               => __( 'You must have at least one price', 'restropress' ),
		'one_field_min'               => __( 'You must have at least one field', 'restropress' ),
		'one_fooditem_min'            => __( 'Payments must contain at least one item', 'restropress' ),
		'one_option'                  => sprintf( __( 'Choose a %s', 'restropress' ), rpress_get_label_singular() ),
		'one_or_more_option'          => sprintf( __( 'Choose one or more %s', 'restropress' ), rpress_get_label_plural() ),
		'numeric_item_price'          => __( 'Item price must be numeric', 'restropress' ),
		'numeric_item_tax'            => __( 'Item tax must be numeric', 'restropress' ),
		'numeric_quantity'            => __( 'Quantity must be numeric', 'restropress' ),
		'currency'                    => rpress_get_currency(),
		'currency_sign'               => rpress_currency_filter( '' ),
		'currency_pos'                => rpress_get_option( 'currency_position', 'before' ),
		'currency_decimals'           => rpress_currency_decimal_filter(),
		'decimal_separator'           => rpress_get_option( 'decimal_separator', '.' ),
		'thousands_separator'         => rpress_get_option( 'thousands_separator', ',' ),
		'new_media_ui'                => apply_filters( 'rpress_use_35_media_ui', 1 ),
		'remove_text'                 => __( 'Remove', 'restropress' ),
		'type_to_search'              => sprintf( __( 'Type to search %s', 'restropress' ), rpress_get_label_plural() ),
		'quantities_enabled'          => rpress_item_quantities_enabled(),
		'batch_export_no_class'       => __( 'You must choose a method.', 'restropress' ),
		'batch_export_no_reqs'        => __( 'Required fields not completed.', 'restropress' ),
		'reset_stats_warn'            => __( 'Are you sure you want to reset your store? This process is <strong><em>not reversible</em></strong>. Please be sure you have a recent backup.', 'restropress' ),
		'unsupported_browser'         => __( 'We are sorry but your browser is not compatible with this kind of file upload. Please upgrade your browser.', 'restropress' ),
		'show_advanced_settings'      => __( 'Show advanced settings', 'restropress' ),
		'hide_advanced_settings'      => __( 'Hide advanced settings', 'restropress' ),
		'is_admin'					  => is_admin(),
		'desktop_notification_duration'	=> rpress_get_option('notification_duration') ,
		'enable_order_notification'  =>  rpress_get_option('enable_order_notification'),   
	));

	/*
	 * This bit of JavaScript is to facilitate #2704, in order to not break backwards compatibility with the old Variable Price Rows
	 * while we transition to an entire new markup. They should not be relied on for long-term usage.
	 *
	 */
	wp_register_script( 'rpress-admin-scripts-compatibility', $js_dir . 'admin-backwards-compatibility' . $suffix . '.js', array( 'jquery', 'rpress-admin-scripts' ), RPRESS_VERSION );
	wp_localize_script( 'rpress-admin-scripts-compatibility', 'rpress_backcompat_vars', array(
		'purchase_limit_settings'     => __( 'Purchase Limit Settings', 'restropress' ),
		'simple_shipping_settings'    => __( 'Simple Shipping Settings', 'restropress' ),
		'software_licensing_settings' => __( 'Software Licensing Settings', 'restropress' ),
		'recurring_payments_settings' => __( 'Recurring Payments Settings', 'restropress' ),
	) );

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );

	wp_register_style( 'colorbox', $css_dir . 'colorbox' . $suffix . '.css', array(), '1.3.20' );
	wp_enqueue_style( 'colorbox' );

	wp_register_script( 'colorbox', $js_dir . 'jquery.colorbox-min.js', array( 'jquery' ), '1.3.20' );
	wp_enqueue_script( 'colorbox' );

	//call for media manager
	wp_enqueue_media();

	wp_register_script( 'jquery-flot', $js_dir . 'jquery.flot' . $suffix . '.js' );
	wp_enqueue_script( 'jquery-flot' );

	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'jquery-ui-tooltip' );

	$ui_style = ( 'classic' == get_user_option( 'admin_color' ) ) ? 'classic' : 'fresh';
	wp_register_style( 'jquery-ui-css', $css_dir . 'jquery-ui-' . $ui_style . $suffix . '.css' );
	wp_enqueue_style( 'jquery-ui-css' );

	wp_enqueue_script( 'media-upload' );
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_style( 'thickbox' );

	wp_register_style( 'rpress-admin', $css_dir . 'rpress-admin' . $suffix . '.css', array(), RPRESS_VERSION );
	wp_enqueue_style( 'rpress-admin' );
}
add_action( 'admin_enqueue_scripts', 'rpress_load_admin_scripts', 100 );

/**
 * Admin RestroPress Icon
 *
 * Echoes the CSS for the fooditems post type icon.
 *
 * @since 1.0
 * @since 1.0.0.11 Removed globals and CSS for custom icon
 * @return void
*/
function rpress_admin_fooditems_icon() {

	$images_url      = RPRESS_PLUGIN_URL . 'assets/images/';
	$menu_icon       = '\f316';
	$icon_cpt_url    = $images_url . 'rpress-cpt.png';
	$icon_cpt_2x_url = $images_url . 'rpress-cpt-2x.png';
	?>
	<style type="text/css" media="screen">
		#dashboard_right_now .fooditem-count:before {
			content: '<?php echo $menu_icon; ?>';
		}
		#icon-edit.icon32-posts-fooditem {
			background: url(<?php echo $icon_cpt_url; ?>) -7px -5px no-repeat;
		}
		@media
		only screen and (-webkit-min-device-pixel-ratio: 1.5),
		only screen and (   min--moz-device-pixel-ratio: 1.5),
		only screen and (     -o-min-device-pixel-ratio: 3/2),
		only screen and (        min-device-pixel-ratio: 1.5),
		only screen and (        		 min-resolution: 1.5dppx) {
			#icon-edit.icon32-posts-fooditem {
				background: url(<?php echo $icon_cpt_2x_url; ?>) no-repeat -7px -5px !important;
				background-size: 55px 45px !important;
			}
		}
	</style>
	<?php
}
add_action( 'admin_head','rpress_admin_fooditems_icon' );


/**
 * Load head styles
 *
 * Ensures fooditem styling is still shown correctly if a theme is using the CSS template file
 *
 * @since  1.0.0
 * @global $post
 * @return void
 */
function rpress_load_head_styles() {

	global $post;

	if ( rpress_get_option( 'disable_styles', false ) || ! is_object( $post ) ) {
		return;
	}

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	$file          = 'rpress' . $suffix . '.css';
	$templates_dir = rpress_get_theme_template_dir_name();

	$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
	$child_theme_style_sheet_2  = trailingslashit( get_stylesheet_directory() ) . $templates_dir . 'rpress.css';
	$parent_theme_style_sheet   = trailingslashit( get_template_directory()   ) . $templates_dir . $file;
	$parent_theme_style_sheet_2 = trailingslashit( get_template_directory()   ) . $templates_dir . 'rpress.css';

	$has_css_template = false;

	if ( has_shortcode( $post->post_content, 'fooditems' ) &&
		file_exists( $child_theme_style_sheet ) ||
		file_exists( $child_theme_style_sheet_2 ) ||
		file_exists( $parent_theme_style_sheet ) ||
		file_exists( $parent_theme_style_sheet_2 )
	) {
		$has_css_template = apply_filters( 'rpress_load_head_styles', true );
	}

	if ( ! $has_css_template ) {
		return;
	}

	?>
	<style>.rpress_fooditem{float:left;}.rpress_fooditem_columns_1 .rpress_fooditem{width: 100%;}.rpress_fooditem_columns_2 .rpress_fooditem{width:50%;}.rpress_fooditem_columns_0 .rpress_fooditem,.rpress_fooditem_columns_3 .rpress_fooditem{width:33%;}.rpress_fooditem_columns_4 .rpress_fooditem{width:25%;}.rpress_fooditem_columns_5 .rpress_fooditem{width:20%;}.rpress_fooditem_columns_6 .rpress_fooditem{width:16.6%;}</style>
	<?php
}
add_action( 'wp_head', 'rpress_load_head_styles' );

/**
 * Determine if the frontend scripts should be loaded in the footer or header (default: footer)
 *
 * @since 1.0.0.6
 * @return mixed
 */
function rpress_scripts_in_footer() {
	return apply_filters( 'rpress_load_scripts_in_footer', true );
}