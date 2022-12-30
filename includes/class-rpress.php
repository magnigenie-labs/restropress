<?php
/**
 * RestroPress setup
 *
 * @package RestroPress
 * @since   2.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main RestroPress Class.
 *
 * @class RestroPress
 */

final class RestroPress {

  /**
   * RestroPress version.
   *
   * @var string
   */
  public $version = '2.9.5';

	/**
   * The single instance of the class.
   *
   * @var RestroPress
   * @since  1.0
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
	 * Cloning is forbidden.
	 *
	 * @since 2.1
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'restropress' ), '2.6.1' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 2.1
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'restropress' ), '2.6.1' );
	}


	/**
	 * RestroPress Constructor.
	 */
	public function __construct() {
		$this->define_constants();
	}


	/**
	 * Define RPRESS Constants.
	 */
	private function define_constants() {
		$this->define( 'RP_VERSION', $this->version );
		$this->define( 'RP_PLUGIN_DIR', plugin_dir_path( RP_PLUGIN_FILE ) );
		$this->define( 'RP_PLUGIN_URL', plugin_dir_url( RP_PLUGIN_FILE ) );
		$this->define( 'CAL_GREGORIAN', 1 );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	* What type of request is this?
	*
	* @param  string $type admin, ajax, cron or frontend.
	* @return bool
	*/
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
		    	return is_admin();
		  	case 'ajax':
		    	return defined( 'DOING_AJAX' );
		  	case 'cron':
		    	return defined( 'DOING_CRON' );
		  	case 'frontend':
		    	return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {

		global $rpress_options;

		require_once RP_PLUGIN_DIR . 'includes/admin/settings/register-settings.php';

		$rpress_options = rpress_get_settings();

		require_once RP_PLUGIN_DIR . 'includes/rp-actions.php';

		if( file_exists( RP_PLUGIN_DIR . 'includes/deprecated-functions.php' ) ) {
			require_once RP_PLUGIN_DIR . 'includes/deprecated-functions.php';
		}
		include_once RP_PLUGIN_DIR . 'includes/class-rpress-category-sorting.php';
		require_once RP_PLUGIN_DIR . 'includes/rp-ajax-functions.php';
		include_once RP_PLUGIN_DIR . 'includes/class-rpress-ajax.php';
		require_once RP_PLUGIN_DIR . 'includes/template-functions.php';
		require_once RP_PLUGIN_DIR . 'includes/template-actions.php';
		require_once RP_PLUGIN_DIR . 'includes/checkout/template.php';
		require_once RP_PLUGIN_DIR . 'includes/checkout/functions.php';
		require_once RP_PLUGIN_DIR . 'includes/cart/class-rpress-cart.php';
		require_once RP_PLUGIN_DIR . 'includes/cart/functions.php';
		require_once RP_PLUGIN_DIR . 'includes/cart/template.php';
		require_once RP_PLUGIN_DIR . 'includes/cart/actions.php';
		require_once RP_PLUGIN_DIR . 'includes/class-rpress-db.php';
		require_once RP_PLUGIN_DIR . 'includes/class-rpress-db-customers.php';
		require_once RP_PLUGIN_DIR . 'includes/class-rpress-db-customer-meta.php';
		require_once RP_PLUGIN_DIR . 'includes/class-rpress-customer-query.php';
		require_once RP_PLUGIN_DIR . 'includes/class-rpress-customer.php';
		require_once RP_PLUGIN_DIR . 'includes/class-rpress-print-receipts.php';
		require_once RP_PLUGIN_DIR . 'includes/class-rpress-license-handler.php';

		require_once RP_PLUGIN_DIR . 'includes/class-rpress-discount.php';
		require_once RP_PLUGIN_DIR . 'includes/class-rpress-fooditem.php';
		require_once RP_PLUGIN_DIR . 'includes/class-rpress-cache-helper.php';

		require_once RP_PLUGIN_DIR . 'includes/class-rpress-cron.php';
		require_once RP_PLUGIN_DIR . 'includes/class-rpress-fees.php';
		require_once RP_PLUGIN_DIR . 'includes/class-rpress-html-elements.php';

		require_once RP_PLUGIN_DIR . 'includes/class-rpress-logging.php';
		require_once RP_PLUGIN_DIR . 'includes/class-rpress-session.php';
		require_once RP_PLUGIN_DIR . 'includes/class-rpress-stats.php';
		require_once RP_PLUGIN_DIR . 'includes/class-rpress-roles.php';
		require_once RP_PLUGIN_DIR . 'includes/country-functions.php';
		require_once RP_PLUGIN_DIR . 'includes/formatting.php';
		require_once RP_PLUGIN_DIR . 'includes/rp-core-functions.php';
		require_once RP_PLUGIN_DIR . 'includes/gateways/actions.php';
		require_once RP_PLUGIN_DIR . 'includes/gateways/functions.php';

		if ( version_compare( phpversion(), 5.3, '>' ) ) {
			require_once RP_PLUGIN_DIR . 'includes/gateways/amazon-payments.php';
		}

		require_once RP_PLUGIN_DIR . 'includes/gateways/paypal-standard.php';
		require_once RP_PLUGIN_DIR . 'includes/gateways/manual.php';

		//Add frontend discount functionality
		require_once RP_PLUGIN_DIR . 'includes/discount-functions.php';

		require_once RP_PLUGIN_DIR . 'includes/admin/orders/actions.php';
		require_once RP_PLUGIN_DIR . 'includes/payments/functions.php';
		require_once RP_PLUGIN_DIR . 'includes/payments/actions.php';
		require_once RP_PLUGIN_DIR . 'includes/payments/class-payment-stats.php';
		require_once RP_PLUGIN_DIR . 'includes/payments/class-payments-query.php';
		require_once RP_PLUGIN_DIR . 'includes/payments/class-rpress-payment.php';
		require_once RP_PLUGIN_DIR . 'includes/fooditem-functions.php';
		require_once RP_PLUGIN_DIR . 'includes/post-types.php';
		require_once RP_PLUGIN_DIR . 'includes/plugin-compatibility.php';
		require_once RP_PLUGIN_DIR . 'includes/emails/class-rpress-emails.php';
		require_once RP_PLUGIN_DIR . 'includes/emails/class-rpress-email-tags.php';
    	require_once RP_PLUGIN_DIR . 'includes/emails/email-tags.php';
		require_once RP_PLUGIN_DIR . 'includes/emails/functions.php';
		require_once RP_PLUGIN_DIR . 'includes/emails/template.php';
		require_once RP_PLUGIN_DIR . 'includes/emails/actions.php';
		require_once RP_PLUGIN_DIR . 'includes/error-tracking.php';
		require_once RP_PLUGIN_DIR . 'includes/user-functions.php';
		require_once RP_PLUGIN_DIR . 'includes/query-filters.php';
		require_once RP_PLUGIN_DIR . 'includes/tax-functions.php';
		require_once RP_PLUGIN_DIR . 'includes/process-purchase.php';
		require_once RP_PLUGIN_DIR . 'includes/login-register.php';
		// Must be loaded on frontend to ensure cron runs
		require_once RP_PLUGIN_DIR . 'includes/admin/tracking.php';
		require_once RP_PLUGIN_DIR . 'includes/privacy-functions.php';
		require_once RP_PLUGIN_DIR . 'includes/shortcodes.php';

		/**
		 * Migrating 3.0 Features to 2.x
		 *
		 * @since 2.4.2
		 */
		include_once RP_PLUGIN_DIR . 'includes/class-rpress-shortcodes.php';
		include_once RP_PLUGIN_DIR . 'includes/shortcodes/class-shortcode-fooditems.php';

		if ( $this->is_request( 'admin' ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {

			/**
			* Migrating 3.0 Features to 2.x
			*
			* @since 2.4.2
			*/
			include_once RP_PLUGIN_DIR . 'includes/admin/includes-rp-admin.php';

			require_once RP_PLUGIN_DIR . 'includes/admin/add-ons.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/admin-actions.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/class-rpress-notices.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/admin-pages.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/dashboard-widgets.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/fooditems/dashboard-columns.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/customers/customers.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/customers/customer-functions.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/customers/customer-actions.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/fooditems/metabox.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/fooditems/contextual-help.php';

			// Add admin discount codes
			require_once RP_PLUGIN_DIR . 'includes/admin/discounts/discount-actions.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/discounts/discount-codes.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/import/import-actions.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/import/import-functions.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/payments/actions.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/payments/payments-history.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/payments/contextual-help.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/reporting/contextual-help.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/reporting/export/export-functions.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/reporting/reports.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/reporting/class-rpress-graph.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/reporting/class-rpress-pie-graph.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/reporting/graphing.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/settings/display-settings.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/settings/contextual-help.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/tools.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/plugins.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/upgrades/upgrades.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/class-rpress-heartbeat.php';
			require_once RP_PLUGIN_DIR . 'includes/admin/tools/tools-actions.php';
		}

		if ( $this->is_request( 'frontend' ) ) {
			$this->frontend_includes();
		}

		require_once RP_PLUGIN_DIR . 'includes/class-rpress-register-meta.php';
		require_once RP_PLUGIN_DIR . 'includes/install.php';
	}

	/**
	* Include required frontend files.
	*/
	public function frontend_includes() {
		include_once RP_PLUGIN_DIR . 'includes/class-rpress-frontend-scripts.php';
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'restropress', false, dirname( plugin_basename( RP_PLUGIN_FILE ) ). '/languages/' );
	}
}
