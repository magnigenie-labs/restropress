<?php
/**
 * Food Item Object
 *
 * @package     RPRESS
 * @subpackage  Classes/RPRESS
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.2
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * RPRESS_Fooditem Class
 *
 * @since  1.0.0
 */
class RPRESS_Fooditem {

	/**
	 * The fooditem ID
	 *
	 * @since  1.0.0
	 */
	public $ID = 0;

	/**
	 * The fooditem price
	 *
	 * @since  1.0.0
	 */
	private $price;

	/**
	 * The fooditem prices, if Variable Prices are enabled
	 *
	 * @since  1.0.0
	 */
	private $prices;

	/**
	 * The fooditem files
	 *
	 * @since  1.0.0
	 */
	private $files;

	/**
	 * The fooditem's file fooditem limit
	 *
	 * @since  1.0.0
	 */
	private $file_fooditem_limit;

	/**
	 * The fooditem type, default or bundle
	 *
	 * @since  1.0.0
	 */
	private $type;

	/**
	 * The bundled fooditems, if this is a bundle type
	 *
	 * @since  1.0.0
	 */
	private $bundled_fooditems;

	/**
	 * The fooditem's sale count
	 *
	 * @since  1.0.0
	 */
	private $sales;

	/**
	 * The fooditem's total earnings
	 *
	 * @since  1.0.0
	 */
	private $earnings;

	/**
	 * The fooditem's notes
	 *
	 * @since  1.0.0
	 */
	private $notes;

	/**
	 * The fooditem sku
	 *
	 * @since  1.0.0
	 */
	private $sku;

	/**
	 * The fooditem's purchase button behavior
	 *
	 * @since  1.0.0
	 */
	private $button_behavior;

	/**
	 * Declare the default properties in WP_Post as we can't extend it
	 * Anything we've declared above has been removed.
	 */
	public $post_author = 0;
	public $post_date = '0000-00-00 00:00:00';
	public $post_date_gmt = '0000-00-00 00:00:00';
	public $post_content = '';
	public $post_title = '';
	public $post_excerpt = '';
	public $post_status = 'publish';
	public $comment_status = 'open';
	public $ping_status = 'open';
	public $post_password = '';
	public $post_name = '';
	public $to_ping = '';
	public $pinged = '';
	public $post_modified = '0000-00-00 00:00:00';
	public $post_modified_gmt = '0000-00-00 00:00:00';
	public $post_content_filtered = '';
	public $post_parent = 0;
	public $guid = '';
	public $menu_order = 0;
	public $post_mime_type = '';
	public $comment_count = 0;
	public $filter;

	/**
	 * Get things going
	 *
	 * @since  1.0.0
	 */
	public function __construct( $_id = false, $_args = array() ) {

		$fooditem = WP_Post::get_instance( $_id );

		return $this->setup_fooditem( $fooditem );

	}

	/**
	 * Given the fooditem data, let's set the variables
	 *
	 * @since  1.0.0.6
	 * @param  WP_Post $fooditem The WP_Post object for fooditem.
	 * @return bool             If the setup was successful or not
	 */
	private function setup_fooditem( $fooditem ) {

		if( ! is_object( $fooditem ) ) {
			return false;
		}

		if( ! $fooditem instanceof WP_Post ) {
			return false;
		}

		if( 'fooditem' !== $fooditem->post_type ) {
			return false;
		}

		foreach ( $fooditem as $key => $value ) {

			switch ( $key ) {

				default:
					$this->$key = $value;
					break;

			}

		}

		return true;

	}

	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since  1.0.0
	 */
	public function __get( $key ) {

		if( method_exists( $this, 'get_' . $key ) ) {

			return call_user_func( array( $this, 'get_' . $key ) );

		} else {

			return new WP_Error( 'rpress-fooditem-invalid-property', sprintf( __( 'Can\'t get property %s', 'restropress' ), $key ) );

		}

	}

	/**
	 * Creates a fooditem
	 *
	 * @since  1.0.0.6
	 * @param  array  $data Array of attributes for a fooditem
	 * @return mixed  false if data isn't passed and class not instantiated for creation
	 */
	public function create( $data = array() ) {

		if ( $this->id != 0 ) {
			return false;
		}

		$defaults = array(
			'post_type'   => 'fooditem',
			'post_status' => 'draft',
			'post_title'  => __( 'New Product', 'restropress' )
		);

		$args = wp_parse_args( $data, $defaults );

		/**
		 * Fired before a fooditem is created
		 *
		 * @param array $args The post object arguments used for creation.
		 */
		do_action( 'rpress_fooditem_pre_create', $args );

		$id = wp_insert_post( $args, true );

		$fooditem = WP_Post::get_instance( $id );

		/**
		 * Fired after a fooditem is created
		 *
		 * @param int   $id   The post ID of the created item.
		 * @param array $args The post object arguments used for creation.
		 */
		do_action( 'rpress_fooditem_post_create', $id, $args );

		return $this->setup_fooditem( $fooditem );

	}

	/**
	 * Retrieve the ID
	 *
	 * @since  1.0.0
	 * @return int ID of the fooditem
	 */
	public function get_ID() {

		return $this->ID;

	}

	/**
	 * Retrieve the fooditem name
	 *
	 * @since 1.0
	 * @return string Name of the fooditem
	 */
	public function get_name() {
		return get_the_title( $this->ID );
	}

	/**
	 * Retrieve the price
	 *
	 * @since  1.0.0
	 * @return float Price of the fooditem
	 */
	public function get_price() {

		if ( ! isset( $this->price ) ) {

			$this->price = get_post_meta( $this->ID, 'rpress_price', true );

			if ( $this->price ) {

				$this->price = rpress_sanitize_amount( $this->price );

			} else {

				$this->price = 0;

			}

		}

		/**
		 * Override the fooditem price.
		 *
		 * @since  1.0.0
		 *
		 * @param string $price The fooditem price(s).
		 * @param string|int $id The fooditems ID.
		 */
		return apply_filters( 'rpress_get_fooditem_price', $this->price, $this->ID );

	}

	/**
	 * Retrieve the variable prices
	 *
	 * @since  1.0.0
	 * @return array List of the variable prices
	 */
	public function get_prices() {

		$this->prices = array();

		if( true === $this->has_variable_prices() ) {

			if ( empty( $this->prices ) ) {
				$this->prices = get_post_meta( $this->ID, 'rpress_variable_prices', true );
			}

		}

		/**
		 * Override variable prices
		 *
		 * @since  1.0.0
		 *
		 * @param array $prices The array of variables prices.
		 * @param int|string The ID of the fooditem.
		 */
		return apply_filters( 'rpress_get_variable_prices', $this->prices, $this->ID );

	}

	/**
	 * Determine if single price mode is enabled or disabled
	 *
	 * @since  1.0.0
	 * @return bool True if fooditem is in single price mode, false otherwise
	 */
	public function is_single_price_mode() {

		$ret = get_post_meta( $this->ID, '_rpress_price_options_mode', true );

		/**
		 * Override the price mode for a fooditem when checking if is in single price mode.
		 *
		 * @since 1.0
		 *
		 * @param bool $ret Is fooditem in single price mode?
		 * @param int|string The ID of the fooditem.
		 */
		return (bool) apply_filters( 'rpress_single_price_option_mode', $ret, $this->ID );

	}

	/**
	 * Determine if the fooditem has variable prices enabled
	 *
	 * @since  1.0.0
	 * @return bool True when the fooditem has variable pricing enabled, false otherwise
	 */
	public function has_variable_prices() {

		$ret = get_post_meta( $this->ID, '_variable_pricing', true );

		/**
		 * Override whether the fooditem has variables prices.
		 *
		 * @since 1.0
		 *
		 * @param bool $ret Does fooditem have variable prices?
		 * @param int|string The ID of the fooditem.
		 */
		return (bool) apply_filters( 'rpress_has_variable_prices', $ret, $this->ID );

	}

	/**
	 * Retrieve the file fooditems
	 *
	 * @since  1.0.0
	 * @param integer $variable_price_id
	 * @return array List of fooditem files
	 */
	public function get_files( $variable_price_id = null ) {
		if( ! isset( $this->files ) ) {

			$this->files = array();

			// Bundled products are not allowed to have files
			if( $this->is_bundled_fooditem() ) {
				return $this->files;
			}

			$fooditem_files = get_post_meta( $this->ID, 'rpress_fooditem_files', true );

			if ( $fooditem_files ) {


				if ( ! is_null( $variable_price_id ) && $this->has_variable_prices() ) {

					foreach ( $fooditem_files as $key => $file_info ) {

						if ( isset( $file_info['condition'] ) ) {

							if ( $file_info['condition'] == $variable_price_id || 'all' === $file_info['condition'] ) {

								$this->files[ $key ] = $file_info;

							}

						}

					}

				} else {

					$this->files = $fooditem_files;

				}

			}

		}

		return apply_filters( 'rpress_fooditem_files', $this->files, $this->ID, $variable_price_id );

	}

	/**
	 * Retrieve the file fooditem limit
	 *
	 * @since  1.0.0
	 * @return int Number of fooditem limit
	 */
	public function get_file_fooditem_limit() {

		if( ! isset( $this->file_fooditem_limit ) ) {

			$ret    = 0;
			$limit  = get_post_meta( $this->ID, '_rpress_fooditem_limit', true );
			$global = rpress_get_option( 'file_fooditem_limit', 0 );

			if ( ! empty( $limit ) || ( is_numeric( $limit ) && (int)$limit == 0 ) ) {

				//specific limit
				$ret = absint( $limit );

			} else {

				// Global limit
				$ret = strlen( $limit ) == 0  || $global ? $global : 0;

			}

			$this->file_fooditem_limit = $ret;

		}

		return absint( apply_filters( 'rpress_file_fooditem_limit', $this->file_fooditem_limit, $this->ID ) );

	}

	/**
	 * Retrieve the price option that has access to the specified file
	 *
	 * @since  1.0.0
	 * @return int|string
	 */
	public function get_file_price_condition( $file_key = 0 ) {

		$files    = $this->get_files();
		$condition = isset( $files[ $file_key ]['condition']) ? $files[ $file_key ]['condition'] : 'all';

		return apply_filters( 'rpress_get_file_price_condition', $condition, $this->ID, $files );

	}

	/**
	 * Retrieve the fooditem type, default or bundle
	 *
	 * @since  1.0.0
	 * @return string Type of fooditem, either 'default' or 'bundle'
	 */
	public function get_type() {

		if( ! isset( $this->type ) ) {

			$this->type = get_post_meta( $this->ID, '_rpress_product_type', true );

			if( empty( $this->type ) ) {
				$this->type = 'default';
			}

		}

		return apply_filters( 'rpress_get_fooditem_type', $this->type, $this->ID );

	}

	/**
	 * Determine if this is a bundled fooditem
	 *
	 * @since  1.0.0
	 * @return bool True when fooditem is a bundle, false otherwise
	 */
	public function is_bundled_fooditem() {
		return 'bundle' === $this->get_type();
	}

	/**
	 * Retrieves the Food Item IDs that are bundled with this
	 *
	 * @since  1.0.0
	 * @return array List of bundled fooditems
	 */
	public function get_bundled_fooditems() {

		if( ! isset( $this->bundled_fooditems ) ) {

			$this->bundled_fooditems = (array) get_post_meta( $this->ID, '_rpress_bundled_products', true );

		}

		return (array) apply_filters( 'rpress_get_bundled_products', array_filter( $this->bundled_fooditems ), $this->ID );

	}

	/**
	 * Retrieve the Product IDs that are bundled with this based on the variable pricing ID passed
	 *
	 * @since 1.0
	 * @param int $price_id Variable pricing ID
	 * @return array List of bundled fooditems
	 */
	public function get_variable_priced_bundled_fooditems( $price_id = null ) {
		if ( null == $price_id ) {
			return $this->get_bundled_fooditems();
		}

		$fooditems         = array();
		$bundled_fooditems = $this->get_bundled_fooditems();
		$price_assignments = $this->get_bundle_pricing_variations();

		if ( ! $price_assignments ) {
			return $bundled_fooditems;
		}

		$price_assignments = $price_assignments[0];
		$price_assignments = array_values( $price_assignments );

		foreach ( $price_assignments as $key => $value ) {
			if ( $value == $price_id || $value == 'all' ) {
				$fooditems[] = $bundled_fooditems[ $key ];
			}
		}

		return $fooditems;
	}

	/**
	 * Retrieve the fooditem notes
	 *
	 * @since  1.0.0
	 * @return string Note related to the fooditem
	 */
	public function get_notes() {

		if( ! isset( $this->notes ) ) {

			$this->notes = get_post_meta( $this->ID, 'rpress_product_notes', true );

		}

		return (string) apply_filters( 'rpress_product_notes', $this->notes, $this->ID );

	}

	/**
	 * Retrieve the fooditem sku
	 *
	 * @since  1.0.0
	 * @return string SKU of the fooditem
	 */
	public function get_sku() {

		if( ! isset( $this->sku ) ) {

			$this->sku = get_post_meta( $this->ID, 'rpress_sku', true );

			if ( empty( $this->sku ) ) {
				$this->sku = '-';
			}

		}

		return apply_filters( 'rpress_get_fooditem_sku', $this->sku, $this->ID );

	}

	/**
	 * Retrieve the purchase button behavior
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_button_behavior() {

		if( ! isset( $this->button_behavior ) ) {

			$this->button_behavior = get_post_meta( $this->ID, '_rpress_button_behavior', true );

			if( empty( $this->button_behavior ) || ! rpress_shop_supports_buy_now() ) {

				$this->button_behavior = 'add_to_cart';

			}

		}

		return apply_filters( 'rpress_get_fooditem_button_behavior', $this->button_behavior, $this->ID );

	}

	/**
	 * Retrieve the sale count for the fooditem
	 *
	 * @since  1.0.0
	 * @return int Number of times this has been purchased
	 */
	public function get_sales() {

		if( ! isset( $this->sales ) ) {

			if ( '' == get_post_meta( $this->ID, '_rpress_fooditem_sales', true ) ) {
				add_post_meta( $this->ID, '_rpress_fooditem_sales', 0 );
			}

			$this->sales = get_post_meta( $this->ID, '_rpress_fooditem_sales', true );

			// Never let sales be less than zero
			$this->sales = max( $this->sales, 0 );

		}

		return $this->sales;

	}

	/**
	 * Increment the sale count by one
	 *
	 * @since  1.0.0
	 * @param int $quantity The quantity to increase the sales by
	 * @return int New number of total sales
	 */
	public function increase_sales( $quantity = 1 ) {

		$quantity    = absint( $quantity );
		$total_sales = $this->get_sales() + $quantity;

		if ( $this->update_meta( '_rpress_fooditem_sales', $total_sales ) ) {

			$this->sales = $total_sales;

			do_action( 'rpress_fooditem_increase_sales', $this->ID, $this->sales, $this );

			return $this->sales;

		}

		return false;
	}

	/**
	 * Decrement the sale count by one
	 *
	 * @since  1.0.0
	 * @param int $quantity The quantity to decrease by
	 * @return int New number of total sales
	 */
	public function decrease_sales( $quantity = 1 ) {

		// Only decrease if not already zero
		if ( $this->get_sales() > 0 ) {

			$quantity    = absint( $quantity );
			$total_sales = $this->get_sales() - $quantity;

			if ( $this->update_meta( '_rpress_fooditem_sales', $total_sales ) ) {

				$this->sales = $total_sales;

				do_action( 'rpress_fooditem_decrease_sales', $this->ID, $this->sales, $this );

				return $this->sales;

			}

		}

		return false;

	}

	/**
	 * Retrieve the total earnings for the fooditem
	 *
	 * @since  1.0.0
	 * @return float Total fooditem earnings
	 */
	public function get_earnings() {

		if ( ! isset( $this->earnings ) ) {

			if ( '' == get_post_meta( $this->ID, '_rpress_fooditem_earnings', true ) ) {
				add_post_meta( $this->ID, '_rpress_fooditem_earnings', 0 );
			}

			$this->earnings = get_post_meta( $this->ID, '_rpress_fooditem_earnings', true );

			// Never let earnings be less than zero
			$this->earnings = max( $this->earnings, 0 );

		}

		return $this->earnings;

	}

	/**
	 * Increase the earnings by the given amount
	 *
	 * @since  1.0.0
	 * @param int|float $amount Amount to increase the earnings by
	 * @return float New number of total earnings
	 */
	public function increase_earnings( $amount = 0 ) {

		$current_earnings = $this->get_earnings();
		$new_amount = apply_filters( 'rpress_fooditem_increase_earnings_amount', $current_earnings + (float) $amount, $current_earnings, $amount, $this );

		if ( $this->update_meta( '_rpress_fooditem_earnings', $new_amount ) ) {

			$this->earnings = $new_amount;

			do_action( 'rpress_fooditem_increase_earnings', $this->ID, $this->earnings, $this );

			return $this->earnings;

		}

		return false;

	}

	/**
	 * Decrease the earnings by the given amount
	 *
	 * @since  1.0.0
	 * @param int|float $amount Number to decrease earning with
	 * @return float New number of total earnings
	 */
	public function decrease_earnings( $amount ) {

		// Only decrease if greater than zero
		if ( $this->get_earnings() > 0 ) {

			$current_earnings = $this->get_earnings();
			$new_amount = apply_filters( 'rpress_fooditem_decrease_earnings_amount', $current_earnings - (float) $amount, $current_earnings, $amount, $this );

			if ( $this->update_meta( '_rpress_fooditem_earnings', $new_amount ) ) {

				$this->earnings = $new_amount;

				do_action( 'rpress_fooditem_decrease_earnings', $this->ID, $this->earnings, $this );

				return $this->earnings;

			}

		}

		return false;

	}

	/**
	 * Determine if the fooditem is free or if the given price ID is free
	 *
	 * @since  1.0.0
	 * @param bool $price_id ID of variation if needed
	 * @return bool True when the fooditem is free, false otherwise
	 */
	public function is_free( $price_id = false ) {

		$is_free = false;
		$variable_pricing = rpress_has_variable_prices( $this->ID );

		if ( $variable_pricing && ! is_null( $price_id ) && $price_id !== false ) {

			$price = rpress_get_price_option_amount( $this->ID, $price_id );

		} elseif ( $variable_pricing && $price_id === false ) {

			$lowest_price  = (float) rpress_get_lowest_price_option( $this->ID );
			$highest_price = (float) rpress_get_highest_price_option( $this->ID );

			if ( $lowest_price === 0.00 && $highest_price === 0.00 ) {
				$price = 0;
			}

		} elseif( ! $variable_pricing ) {

			$price = get_post_meta( $this->ID, 'rpress_price', true );

		}

		if( isset( $price ) && (float) $price == 0 ) {
			$is_free = true;
		}

		return (bool) apply_filters( 'rpress_is_free_fooditem', $is_free, $this->ID, $price_id );

	}

	/**
	 * Is quantity input disabled on this product?
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function quantities_disabled() {

		$ret = (bool) get_post_meta( $this->ID, '_rpress_quantities_disabled', true );
		return apply_filters( 'rpress_fooditem_quantity_disabled', $ret, $this->ID );

	}

	/**
	 * Updates a single meta entry for the fooditem
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  string $meta_key   The meta_key to update
	 * @param  string|array|object $meta_value The value to put into the meta
	 * @return bool             The result of the update query
	 */
	private function update_meta( $meta_key = '', $meta_value = '' ) {

		global $wpdb;

		if ( empty( $meta_key ) || empty( $meta_value ) ) {
			return false;
		}

		// Make sure if it needs to be serialized, we do
		$meta_value = maybe_serialize( $meta_value );

		if ( is_numeric( $meta_value ) ) {
			$value_type = is_float( $meta_value ) ? '%f' : '%d';
		} else {
			$value_type = "'%s'";
		}

		$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = $value_type WHERE post_id = $this->ID AND meta_key = '%s'", $meta_value, $meta_key );

		if ( $wpdb->query( $sql ) ) {

			clean_post_cache( $this->ID );
			return true;

		}

		return false;
	}

	/**
	 * Checks if the fooditem can be purchased
	 *
	 * NOTE: Currently only checks on rpress_get_cart_contents() and rpress_add_to_cart()
	 *
	 * @since  1.0.0.4
	 * @return bool If the current user can purcahse the fooditem ID
	 */
	public function can_purchase() {
		$can_purchase = true;

		if ( ! current_user_can( 'edit_post', $this->ID ) && $this->post_status != 'publish' ) {
			$can_purchase = false;
		}

		return (bool) apply_filters( 'rpress_can_purchase_fooditem', $can_purchase, $this );
	}

	/**
	 * Get pricing variations for bundled items
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_bundle_pricing_variations() {
		return get_post_meta( $this->ID, '_rpress_bundled_products_conditions' );
	}

}
