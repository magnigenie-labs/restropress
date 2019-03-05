<?php
/**
 * Plugin Name: RestroPress
 * Plugin URI: http://restropress.magnigenie.com
 * Description: RestroPress is a restaurant food ordering system for WordPress
 * Author: Magnigenie
 * Author URI: https://magnigenie.com
 * Version: 1.0.7
 * Text Domain: restro-press
 * Domain Path: languages
 *

 * @package RPRESS
 * @category Core
 * @author kshirod.patel@gmail.com
 * @version 1.0.7
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'RestroPress' ) ) :

/**
 * Main RestroPress Class.
 *
 * @since 1.0
 */
final class RestroPress {
	/** Singleton *************************************************************/

	/**
	 * @var RestroPress The one true RestroPress
	 * @since  1.0.0
	 */
	private static $instance;

	/**
	 * RPRESS Roles Object.
	 *
	 * @var object|RPRESS_Roles
	 * @since 1.0
	 */
	public $roles;

	/**
	 * RPRESS Cart Fees Object.
	 *
	 * @var object|RPRESS_Fees
	 * @since 1.0
	 */
	public $fees;

	/**
	 * RPRESS HTML Session Object.
	 *
	 * This holds cart items, purchase sessions, and anything else stored in the session.
	 *
	 * @var object|RPRESS_Session
	 * @since 1.0
	 */
	public $session;

	/**
	 * RPRESS HTML Element Helper Object.
	 *
	 * @var object|RPRESS_HTML_Elements
	 * @since 1.0
	 */
	public $html;

	/**
	 * RPRESS Emails Object.
	 *
	 * @var object|RPRESS_Emails
	 * @since  1.0.0
	 */
	public $emails;

	/**
	 * RPRESS Email Template Tags Object.
	 *
	 * @var object|RPRESS_Email_Template_Tags
	 * @since  1.0.0
	 */
	public $email_tags;

	/**
	 * RPRESS Customers DB Object.
	 *
	 * @var object|RPRESS_DB_Customers
	 * @since  1.0.0
	 */
	public $customers;

	/**
	 * RPRESS Customer meta DB Object.
	 *
	 * @var object|RPRESS_DB_Customer_Meta
	 * @since 1.0.0
	 */
	public $customer_meta;

	/**
	 * RPRESS Cart Object
	 *
	 * @var object|RPRESS_Cart
	 * @since 1.0
	 */
	public $cart;

	/**
	 * Main RestroPress Instance.
	 *
	 * Insures that only one instance of RestroPress exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since  1.0.0
	 * @static
	 * @staticvar array $instance
	 * @uses RestroPress::setup_constants() Setup the constants needed.
	 * @uses RestroPress::includes() Include the required files.
	 * @uses RestroPress::load_textdomain() load the language files.
	 * @see RPRESS()
	 * @return object|RestroPress The one true RestroPress
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof RestroPress ) ) {
			self::$instance = new RestroPress;
			self::$instance->setup_constants();

			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

			self::$instance->includes();
			self::$instance->roles         = new RPRESS_Roles();
			self::$instance->fees          = new RPRESS_Fees();
			self::$instance->session       = new RPRESS_Session();
			self::$instance->html          = new RPRESS_HTML_Elements();
			self::$instance->emails        = new RPRESS_Emails();
			self::$instance->email_tags    = new RPRESS_Email_Template_Tags();
			self::$instance->customers     = new RPRESS_DB_Customers();
			self::$instance->customer_meta = new RPRESS_DB_Customer_Meta();
			self::$instance->payment_stats = new RPRESS_Payment_Stats();
			self::$instance->cart          = new RPRESS_Cart();
		}

		return self::$instance;
	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'restro-press' ), '1.6' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'restro-press' ), '1.6' );
	}

	/**
	 * Setup plugin constants.
	 *
	 * @access private
	 * @since  1.0.0
	 * @return void
	 */
	private function setup_constants() {

		// Plugin version.
		if ( ! defined( 'RPRESS_VERSION' ) ) {
			define( 'RPRESS_VERSION', '1.0.4' );
		}

		// Plugin Folder Path.
		if ( ! defined( 'RPRESS_PLUGIN_DIR' ) ) {
			define( 'RPRESS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'RPRESS_PLUGIN_URL' ) ) {
			define( 'RPRESS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File.
		if ( ! defined( 'RPRESS_PLUGIN_FILE' ) ) {
			define( 'RPRESS_PLUGIN_FILE', __FILE__ );
		}


		// Make sure CAL_GREGORIAN is defined.
		if ( ! defined( 'CAL_GREGORIAN' ) ) {
			define( 'CAL_GREGORIAN', 1 );
		}
	}

	/**
	 * Include required files.
	 *
	 * @access private
	 * @since  1.0.0
	 * @return void
	 */
	private function includes() {
		global $rpress_options;

		require_once RPRESS_PLUGIN_DIR . 'includes/admin/settings/register-settings.php';
		$rpress_options = rpress_get_settings();

		require_once RPRESS_PLUGIN_DIR . 'includes/actions.php';
		
		if( file_exists( RPRESS_PLUGIN_DIR . 'includes/deprecated-functions.php' ) ) {
			require_once RPRESS_PLUGIN_DIR . 'includes/deprecated-functions.php';
		}
		require_once RPRESS_PLUGIN_DIR . 'includes/ajax-functions.php';

		
		require_once RPRESS_PLUGIN_DIR . 'includes/template-functions.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/template-actions.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/checkout/template.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/checkout/functions.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/cart/class-rpress-cart.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/cart/functions.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/cart/template.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/cart/actions.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/class-rpress-db.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/class-rpress-db-customers.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/class-rpress-db-customer-meta.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/class-rpress-customer-query.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/class-rpress-customer.php';

		// Discount Class
		require_once RPRESS_PLUGIN_DIR . 'includes/class-rpress-discount.php';
		
		require_once RPRESS_PLUGIN_DIR . 'includes/class-rpress-fooditem.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/class-rpress-cache-helper.php';
		
		require_once RPRESS_PLUGIN_DIR . 'includes/class-rpress-cron.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/class-rpress-fees.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/class-rpress-html-elements.php';
		
		require_once RPRESS_PLUGIN_DIR . 'includes/class-rpress-logging.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/class-rpress-session.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/class-rpress-stats.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/class-rpress-roles.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/country-functions.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/formatting.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/misc-functions.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/mime-types.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/gateways/actions.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/gateways/functions.php';

		if ( version_compare( phpversion(), 5.3, '>' ) ) {
			require_once RPRESS_PLUGIN_DIR . 'includes/gateways/amazon-payments.php';
		}

		require_once RPRESS_PLUGIN_DIR . 'includes/gateways/paypal-standard.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/gateways/manual.php';

		
		//Add frontend discount functionality
		require_once RPRESS_PLUGIN_DIR . 'includes/discount-functions.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/payments/functions.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/payments/actions.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/payments/class-payment-stats.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/payments/class-payments-query.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/payments/class-rpress-payment.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/fooditem-functions.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/scripts.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/post-types.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/plugin-compatibility.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/emails/class-rpress-emails.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/emails/class-rpress-email-tags.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/emails/functions.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/emails/template.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/emails/actions.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/error-tracking.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/user-functions.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/query-filters.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/tax-functions.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/process-purchase.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/login-register.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/shortcodes.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/admin/tracking.php'; // Must be loaded on frontend to ensure cron runs
		require_once RPRESS_PLUGIN_DIR . 'includes/privacy-functions.php';

		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/add-ons.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/admin-actions.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/class-rpress-notices.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/admin-pages.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/dashboard-widgets.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/upload-functions.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/fooditems/dashboard-columns.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/customers/customers.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/customers/customer-functions.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/customers/customer-actions.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/fooditems/metabox.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/fooditems/contextual-help.php';

			//Add admin discount codes
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/discounts/discount-actions.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/discounts/discount-codes.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/import/import-actions.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/import/import-functions.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/payments/actions.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/payments/payments-history.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/payments/contextual-help.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/reporting/contextual-help.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/reporting/export/export-functions.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/reporting/reports.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/reporting/class-rpress-graph.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/reporting/class-rpress-pie-graph.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/reporting/graphing.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/settings/display-settings.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/settings/contextual-help.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/tools.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/delivery-options.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/plugins.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/upgrades/upgrades.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/class-rpress-heartbeat.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/admin/tools/tools-actions.php';

		} else {
			require_once RPRESS_PLUGIN_DIR . 'includes/process-fooditem.php';
			require_once RPRESS_PLUGIN_DIR . 'includes/theme-compatibility.php';
		}

		require_once RPRESS_PLUGIN_DIR . 'includes/class-rpress-register-meta.php';
		require_once RPRESS_PLUGIN_DIR . 'includes/install.php';

		require_once RPRESS_PLUGIN_DIR . 'includes/rpress-functions.php';

		//Addon License Functions
		require_once RPRESS_PLUGIN_DIR . 'includes/rpress-license.php';
	}

	/**
	 * Loads the plugin language files.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'restro-press', false, dirname( plugin_basename( __FILE__ ) ). '/languages/' );
	}

}

endif; // End if class_exists check.


/**
 * The main function for that returns RestroPress
 *
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $rpress = RPRESS(); ?>
 *
 * @since  1.0.0
 * @return object|RestroPress The one true RestroPress Instance.
 */
function RPRESS() {
	return RestroPress::instance();
}

//Get RestroPress Running.
RPRESS();