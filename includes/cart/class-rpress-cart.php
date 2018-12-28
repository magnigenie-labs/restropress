<?php
/**
 * Cart Object
 *
 * @package     RPRESS
 * @subpackage  Classes/Cart
 * @copyright   Copyright (c) 2016, Sunny Ratilal
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * RPRESS_Cart Class
 *
 * @since 1.0
 */
class RPRESS_Cart {
	/**
	 * Cart contents
	 *
	 * @var array
	 * @since 1.0
	 */
	public $contents = array();

	/**
	 * Details of the cart contents
	 *
	 * @var array
	 * @since 1.0
	 */
	public $details = array();

	/**
	 * Cart Quantity
	 *
	 * @var int
	 * @since 1.0
	 */
	public $quantity = 0;

	/**
	 * Subtotal
	 *
	 * @var float
	 * @since 1.0
	 */
	public $subtotal = 0.00;

	/**
	 * Total
	 *
	 * @var float
	 * @since 1.0
	 */
	public $total = 0.00;

	/**
	 * Fees
	 *
	 * @var array
	 * @since 1.0
	 */
	public $fees = array();

	/**
	 * Tax
	 *
	 * @var float
	 * @since 1.0
	 */
	public $tax = 0.00;

	/**
	 * Purchase Session
	 *
	 * @var array
	 * @since 1.0
	 */
	public $session;

	/**
	 * Discount codes
	 *
	 * @var array
	 * @since 1.0
	 */
	public $discounts = array();

	/**
	 * Cart saving
	 *
	 * @var bool
	 * @since 1.0
	 */
	public $saving;

	/**
	 * Saved cart
	 *
	 * @var array
	 * @since 1.0
	 */
	public $saved;

	/**
	 * Has discount?
	 *
	 * @var bool
	 * @since 1.0
	 */
	public $has_discounts = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'setup_cart' ), 1 );
	}

	/**
	 * Sets up cart components
	 *
	 * @since 1.0
	 * @access private
	 * @return void
	 */
	public function setup_cart() {
		$this->get_contents_from_session();
		$this->get_contents();
		$this->get_contents_details();
		$this->get_all_fees();
		$this->get_discounts_from_session();
		$this->get_quantity();
	}

	/**
	 * Populate the cart with the data stored in the session
	 *
	 * @since 1.0
	 * @return void
	 */
	public function get_contents_from_session() {
		$cart = RPRESS()->session->get( 'rpress_cart' );
		$this->contents = $cart;
		do_action( 'rpress_cart_contents_loaded_from_session', $this );
	}

	/**
	 * Populate the discounts with the data stored in the session.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function get_discounts_from_session() {
		$discounts = RPRESS()->session->get( 'cart_discounts' );
		$this->discounts = $discounts;

		do_action( 'rpress_cart_discounts_loaded_from_session', $this );
	}

	/**
	 * Get cart contents
	 *
	 * @since 1.0
	 * @return array List of cart contents.
	 */
	public function get_contents() {
		if ( ! did_action( 'rpress_cart_contents_loaded_from_session' ) ) {
			$this->get_contents_from_session();
		}


		$cart = is_array( $this->contents ) && ! empty( $this->contents ) ? array_values( $this->contents ) : array();
		$cart_count = count( $cart );

		if( is_array( $cart ) && !empty( $cart ) ) {
			foreach ( $cart as $key => $item ) {
				if( isset($item['id']) && !empty($item['id']) ) {
					$fooditem = new RPRESS_Fooditem( $item['id'] );


					// If the item is not a fooditem or it's status has changed since it was added to the cart.
					if ( empty( $fooditem->ID ) || ! $fooditem->can_purchase() ) {
						unset( $cart[ $key ] );
					}
				}
				
			}
		}
		

		// We've removed items, reset the cart session
		if ( count( $cart ) < $cart_count ) {
			$this->contents = $cart;
			$this->update_cart();
		}

		$this->contents = apply_filters( 'rpress_cart_contents', $cart );

		do_action( 'rpress_cart_contents_loaded' );


		return $this->contents;
	}

	/**
	 * Get cart contents details
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_contents_details() {
		global $rpress_is_last_cart_item, $rpress_flat_discount_total;

		//print_r($this->contents);


		if ( empty( $this->contents ) ) {
			return array();
		}

		$details = array();
		$length  = count( $this->contents ) - 1;


		foreach ( $this->contents as $key => $item ) {
			if( $key >= $length ) {
				$rpress_is_last_cart_item = true;
			}

			if( empty($item['id']) )
				return;


			$item['quantity'] = rpress_item_quantities_enabled() ? absint( $item['quantity'] ) : 1;
			$item['quantity'] = max( 1, $item['quantity'] ); // Force quantity to 1

			$options = isset( $item['options'] ) ? $item['options'] : array();

			$price_id = isset( $options['price_id'] ) ? $options['price_id'] : null;

			$item_price = $this->get_item_price( $item['id'], $options );
			$discount   = $this->get_item_discount_amount( $item );
			$discount   = apply_filters( 'rpress_get_cart_content_details_item_discount_amount', $discount, $item );
			$quantity   = $this->get_item_quantity( $item['id'], $options );
			$fees       = $this->get_fees( 'fee', $item['id'], $price_id );
			$subtotal   = floatval( $item_price ) * $quantity;

			// Subtotal for tax calculation must exclude fees that are greater than 0. See $this->get_tax_on_fees()
			$subtotal_for_tax = $subtotal;

			foreach ( $fees as $fee ) {

				$fee_amount = (float) $fee['amount'];
				$subtotal  += $fee_amount;

				if( $fee_amount > 0 ) {
					continue;
				}

				$subtotal_for_tax += $fee_amount;
			}

			$tax = $this->get_item_tax( $item['id'], $options, $subtotal_for_tax - $discount );

			if ( rpress_prices_include_tax() ) {
				$subtotal -= round( $tax, rpress_currency_decimal_filter() );
			}

			$total = $subtotal - $discount + $tax;

			if ( $total < 0 ) {
				$total = 0;
			}

			$details[ $key ]  = array(
				'name'        => get_the_title( $item['id'] ),
				'id'          => $item['id'],
				'item_number' => $item,
				'item_price'  => round( $item_price, rpress_currency_decimal_filter() ),
				'quantity'    => $quantity,
				'discount'    => round( $discount, rpress_currency_decimal_filter() ),
				'subtotal'    => round( $subtotal, rpress_currency_decimal_filter() ),
				'tax'         => round( $tax, rpress_currency_decimal_filter() ),
				'fees'        => $fees,
				'price'       => round( $total, rpress_currency_decimal_filter() )
			);

			if ( $rpress_is_last_cart_item ) {
				$rpress_is_last_cart_item   = false;
				$rpress_flat_discount_total = 0.00;
			}
		}


		$this->details = $details;

		return $this->details;
	}

	/**
	 * Get Discounts.
	 *
	 * @since 1.0
	 * @return array $discounts The active discount codes
	 */
	public function get_discounts() {
		$this->get_discounts_from_session();
		$this->discounts = ! empty( $this->discounts ) ? explode( '|', $this->discounts ) : array();
		return $this->discounts;
	}

	/**
	 * Update Cart
	 *
	 * @since 1.0
	 * @return void
	 */
	public function update_cart() {
		RPRESS()->session->set( 'rpress_cart', $this->contents );
	}

	/**
	 * Checks if any discounts have been applied to the cart
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_discounts() {
		if ( null !== $this->has_discounts ) {
			return $this->has_discounts;
		}

		$has_discounts = false;

		$discounts = $this->get_discounts();
		if ( ! empty( $discounts ) ) {
			$has_discounts = true;
		}

		$this->has_discounts = apply_filters( 'rpress_cart_has_discounts', $has_discounts );

		return $this->has_discounts;
	}

	/**
	 * Get quantity
	 *
	 * @since 1.0
	 * @return int
	 */
	public function get_quantity() {
		$total_quantity = 0;

		$contents = $this->get_contents();
		if ( ! empty( $contents ) ) {
			$total_quantity = absint( count($contents) );
		}

		$this->quantity = apply_filters( 'rpress_get_cart_quantity', $total_quantity, $this->contents );
		return $this->quantity;
	}

	/**
	 * Checks if the cart is empty
	 *
	 * @since 1.0
	 * @return boolean
	 */
	public function is_empty() {
		return 0 === sizeof( $this->contents );
	}

	/**
	 * Add to cart
	 *
	 * As of RPRESS 2.7, items can only be added to the cart when the object passed extends RPRESS_Cart_Item
	 *
	 * @since 1.0
	 * @return array $cart Updated cart object
	 */
	public function add( $fooditem_id, $options = array() ) {
		$fooditem = new RPRESS_Fooditem( $fooditem_id );

		if ( empty( $fooditem->ID ) ) {
			return; // Not a fooditem product
		}

		if ( ! $fooditem->can_purchase() ) {
			return; // Do not allow draft/pending to be purchased if can't edit. Fixes #1056
		}

		do_action( 'rpress_pre_add_to_cart', $fooditem_id, $options );

		$this->contents = apply_filters( 'rpress_pre_add_to_cart_contents', $this->contents );

		$quantity = 1;


		// If the price IDs are a string and is a coma separated list, make it an array (allows custom add to cart URLs)
		if ( isset( $options['price_id'] ) && ! is_array( $options['price_id'] ) && false !== strpos( $options['price_id'], ',' ) ) {
			$options['price_id'] = explode( ',', $options['price_id'] );
		}

		$items = array();


		$items[] = array(
				'id'       => $fooditem_id,
				'quantity' => $quantity,
			);

		$this->contents[] = $options;

		unset( $item );


		$this->update_cart();

		do_action( 'rpress_post_add_to_cart', $fooditem_id, $options, $items );

		// Clear all the checkout errors, if any
		rpress_clear_errors();
		

		return count( $this->contents ) - 1;
	}

	/**
	 * Remove from cart
	 *
	 * @since 1.0
	 *
	 * @param int $key Cart key to remove. This key is the numerical index of the item contained within the cart array.
 	 * @return array Updated cart contents
	 */
	public function remove( $key ) {
		$cart = $this->get_contents();


		do_action( 'rpress_pre_remove_from_cart', $key );

		if ( ! is_array( $cart ) ) {
			return true; // Empty cart
		} else {
			$item_id = isset( $cart[ $key ]['id'] ) ? $cart[ $key ]['id'] : null;
			unset( $cart[ $key ] );
		}

		$this->contents = $cart;
		$this->update_cart();

		do_action( 'rpress_post_remove_from_cart', $key, $item_id );

		rpress_clear_errors();

		return $this->contents;
	}

	/**
	 * Generate the URL to remove an item from the cart.
	 *
	 * @since 1.0
	 *
	 * @param int $cart_key Cart item key
 	 * @return string $remove_url URL to remove the cart item
	 */
	public function remove_item_url( $cart_key ) {
		global $wp_query;

		if ( defined( 'DOING_AJAX' ) ) {
			$current_page = rpress_get_checkout_uri();
		} else {
			$current_page = rpress_get_current_page_url();
		}

		$remove_url = rpress_add_cache_busting( add_query_arg( array( 'cart_item' => $cart_key, 'rpress_action' => 'remove' ), $current_page ) );

		return apply_filters( 'rpress_remove_item_url', $remove_url );
	}

	/**
	 * Generate the URL to remove a fee from the cart.
	 *
	 * @since 1.0
	 *
	 * @param int $fee_id Fee ID.
	 * @return string $remove_url URL to remove the cart item
	 */
	public function remove_fee_url( $fee_id = '' ) {
		global $post;

		if ( defined('DOING_AJAX') ) {
			$current_page = rpress_get_checkout_uri();
		} else {
			$current_page = rpress_get_current_page_url();
		}

		$remove_url = add_query_arg( array( 'fee' => $fee_id, 'rpress_action' => 'remove_fee', 'nocache' => 'true' ), $current_page );

		return apply_filters( 'rpress_remove_fee_url', $remove_url );
	}

	/**
	 * Empty the cart
	 *
	 * @since 1.0
	 * @return void
	 */
	public function empty_cart() {
		// Remove cart contents.
		RPRESS()->session->set( 'rpress_cart', NULL );

		// Remove all cart fees.
		RPRESS()->session->set( 'rpress_cart_fees', NULL );

		// Remove any resuming payments.
		RPRESS()->session->set( 'rpress_resume_payment', NULL );

		// Remove any active discounts
		$this->remove_all_discounts();
		$this->contents = array();

		do_action( 'rpress_empty_cart' );
	}

	/**
	 * Remove discount from the cart
	 *
	 * @since 1.0
	 * @return array Discount codes
	 */
	public function remove_discount( $code = '' ) {
		if ( empty( $code ) ) {
			return;
		}

		if ( $this->discounts ) {
			$key = array_search( $code, $this->discounts );

			if ( false !== $key ) {
				unset( $this->discounts[ $key ] );
			}

			$this->discounts = implode( '|', array_values( $this->discounts ) );

			// update the active discounts
			RPRESS()->session->set( 'cart_discounts', $this->discounts );
		}

		do_action( 'rpress_cart_discount_removed', $code, $this->discounts );
		do_action( 'rpress_cart_discounts_updated', $this->discounts );

		return $this->discounts;
	}

	/**
	 * Remove all discount codes
	 *
	 * @since 1.0
	 * @return void
	 */
	public function remove_all_discounts() {
		RPRESS()->session->set( 'cart_discounts', null );
		do_action( 'rpress_cart_discounts_removed' );
	}

	/**
	 * Get the discounted amount on a price
	 *
	 * @since 1.0
	 *
	 * @param array       $item     Cart item.
	 * @param bool|string $discount False to use the cart discounts or a string to check with a discount code.
	 * @return float The discounted amount
	 */
	public function get_item_discount_amount( $item = array(), $discount = false ) {
		global $rpress_is_last_cart_item, $rpress_flat_discount_total;

		// If we're not meeting the requirements of the $item array, return or set them
		if ( empty( $item ) || empty( $item['id'] ) ) {
			return 0;
		}

		// Quantity is a requirement of the cart options array to determine the discounted price
		if ( empty( $item['quantity'] ) ) {
			return 0;
		}

		if ( ! isset( $item['options'] ) ) {
			$item['options'] = array();
		}

		$amount           = 0;
		$price            = $this->get_item_price( $item['id'], $item['options'] );
		$discounted_price = $price;

		$discounts = false === $discount ? $this->get_discounts() : array( $discount );

		if ( ! empty( $discounts ) ) {
			foreach ( $discounts as $discount ) {
				$code_id = rpress_get_discount_id_by_code( $discount );

				// Check discount exists
				if( ! $code_id ) {
					continue;
				}

				$reqs              = rpress_get_discount_product_reqs( $code_id );
				$excluded_products = rpress_get_discount_excluded_products( $code_id );

				// Make sure requirements are set and that this discount shouldn't apply to the whole cart
				if ( ! empty( $reqs ) && rpress_is_discount_not_global( $code_id ) ) {
					// This is a product(s) specific discount
					foreach ( $reqs as $fooditem_id ) {
						if ( $fooditem_id == $item['id'] && ! in_array( $item['id'], $excluded_products ) ) {
							$discounted_price -= $price - rpress_get_discounted_amount( $discount, $price );
						}
					}
				} else {
					// This is a global cart discount
					if( ! in_array( $item['id'], $excluded_products ) ) {
						if( 'flat' === rpress_get_discount_type( $code_id ) ) {
							/* *
							 * In order to correctly record individual item amounts, global flat rate discounts
							 * are distributed across all cart items. The discount amount is divided by the number
							 * of items in the cart and then a portion is evenly applied to each cart item
							 */
							$items_subtotal    = 0.00;
							$cart_items        = $this->get_contents();
							foreach ( $cart_items as $cart_item ) {
								if ( ! in_array( $cart_item['id'], $excluded_products ) ) {
									$item_price      = $this->get_item_price( $cart_item['id'], $cart_item['options'] );
									$items_subtotal += $item_price * $cart_item['quantity'];
								}
							}

							$subtotal_percent  = ( ( $price * $item['quantity'] ) / $items_subtotal );
							$code_amount       = rpress_get_discount_amount( $code_id );
							$discounted_amount = $code_amount * $subtotal_percent;
							$discounted_price -= $discounted_amount;

							$rpress_flat_discount_total += round( $discounted_amount, rpress_currency_decimal_filter() );

							if ( $rpress_is_last_cart_item && $rpress_flat_discount_total < $code_amount ) {
								$adjustment = $code_amount - $rpress_flat_discount_total;
								$discounted_price -= $adjustment;
							}
						} else {
							$discounted_price -= $price - rpress_get_discounted_amount( $discount, $price );
						}
					}
				}

				if ( $discounted_price < 0 ) {
					$discounted_price = 0;
				}
			}

			$amount = round( ( $price - apply_filters( 'rpress_get_cart_item_discounted_amount', $discounted_price, $discounts, $item, $price ) ), rpress_currency_decimal_filter() );

			if ( 'flat' !== rpress_get_discount_type( $code_id ) ) {
				$amount = $amount * $item['quantity'];
			}
		}

		return $amount;
	}

	/**
	 * Shows the fully formatted cart discount
	 *
	 * @since 1.0
	 *
	 * @param bool $echo Echo?
	 * @return string $amount Fully formatted cart discount
	 */
	public function display_cart_discount( $echo = false ) {
		$discounts = $this->get_discounts();

		if ( empty( $discounts ) ) {
			return false;
		}

		$discount_id  = rpress_get_discount_id_by_code( $discounts[0] );
		$amount       = rpress_format_discount_rate( rpress_get_discount_type( $discount_id ), rpress_get_discount_amount( $discount_id ) );

		if ( $echo ) {
			echo $amount;
		}

		return $amount;
	}

	/**
	 * Checks to see if an item is in the cart.
	 *
	 * @since 1.0
	 *
	 * @param int   $fooditem_id Download ID of the item to check.
 	 * @param array $options
	 * @return bool
	 */
	public function is_item_in_cart( $fooditem_id = 0, $options = array() ) {
		$cart = $this->get_contents();

		$ret = false;

		if ( is_array( $cart ) ) {
			foreach ( $cart as $item ) {
				if ( $item['id'] == $fooditem_id ) {
					if ( isset( $options['price_id'] ) && isset( $item['options']['price_id'] ) ) {
						if ( $options['price_id'] == $item['options']['price_id'] ) {
							$ret = true;
							break;
						}
					} else {
						$ret = true;
						break;
					}
				}
			}
		}

		return (bool) apply_filters( 'rpress_item_in_cart', $ret, $fooditem_id, $options );
	}

	/**
	 * Get the position of an item in the cart
	 *
	 * @since 1.0
	 *
	 * @param int   $fooditem_id Download ID of the item to check.
 	 * @param array $options
	 * @return mixed int|false
	 */
	public function get_item_position( $fooditem_id = 0, $options = array() ) {
		$cart = $this->get_contents();

		if ( ! is_array( $cart ) ) {
			return false;
		} else {
			foreach ( $cart as $position => $item ) {
				if( !isset($item['id']) )
					return;
				if ( $item['id'] == $fooditem_id ) {
					if ( isset( $options['price_id'] ) && isset( $item['options']['price_id'] ) ) {
						if ( (int) $options['price_id'] == (int) $item['options']['price_id'] ) {
							return $position;
						}
					} else {
						return $position;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Get the quantity of an item in the cart.
	 *
	 * @since 1.0
	 *
	 * @param int   $fooditem_id Download ID of the item
 	 * @param array $options
	 * @return int Numerical index of the position of the item in the cart
	 */
	public function get_item_quantity( $fooditem_id = 0, $options = array() ) {
		$key = $this->get_item_position( $fooditem_id, $options );
		$quantity = isset( $this->contents[ $key ]['quantity'] ) ? $this->contents[ $key ]['quantity'] : 1;

		if ( $quantity < 1 ) {
			$quantity = 1;
		}

		return absint( apply_filters( 'rpress_get_cart_item_quantity', $quantity, $fooditem_id, $options ) );
	}

	/**
	 * Set the quantity of an item in the cart.
	 *
	 * @since 1.0
	 *
	 * @param int   $fooditem_id Download ID of the item
	 * @param int   $quantity    Updated quantity of the item
 	 * @param array $options
	 * @return array $contents Updated cart object.
	 */
	public function set_item_quantity( $fooditem_id = 0, $quantity = 1, $options = array() ) {
		$key  = $this->get_item_position( $fooditem_id, $options );

		if ( false === $key ) {
			return $this->contents;
		}

		if ( $quantity < 1 ) {
			$quantity = 1;
		}

		//$quantity = 10;

		$this->contents[ $key ]['quantity'] = $quantity;
		$this->update_cart();

		do_action( 'rpress_after_set_cart_item_quantity', $fooditem_id, $quantity, $options, $this->contents );

		return $this->contents;
	}

	/**
	 * Cart Item Price.
	 *
	 * @since 1.0
	 *
	 * @param int   $item_id Download (cart item) ID number
 	 * @param array $options Optional parameters, used for defining variable prices
 	 * @return string Fully formatted price
	 */
	public function item_price( $item_id = 0, $options = array() ) {
		$price = $this->get_item_price( $item_id, $options );
		$label = '';

		$price_id = isset( $options['price_id'] ) ? $options['price_id'] : false;

		if ( ! rpress_is_free_fooditem( $item_id, $price_id ) && ! rpress_fooditem_is_tax_exclusive( $item_id ) ) {
			if ( rpress_prices_show_tax_on_checkout() && ! rpress_prices_include_tax() ) {
				$price += rpress_get_cart_item_tax( $item_id, $options, $price );
			}

			if ( ! rpress_prices_show_tax_on_checkout() && rpress_prices_include_tax() ) {
				$price -= rpress_get_cart_item_tax( $item_id, $options, $price );
			}

			if ( rpress_display_tax_rate() ) {
				$label = '&nbsp;&ndash;&nbsp;';

				if ( rpress_prices_show_tax_on_checkout() ) {
					$label .= sprintf( __( 'includes %s tax', 'restro-press' ), rpress_get_formatted_tax_rate() );
				} else {
					$label .= sprintf( __( 'excludes %s tax', 'restro-press' ), rpress_get_formatted_tax_rate() );
				}

				$label = apply_filters( 'rpress_cart_item_tax_description', $label, $item_id, $options );
			}
		}

		$price = rpress_currency_filter( rpress_format_amount( $price ) );

		return apply_filters( 'rpress_cart_item_price_label', $price . $label, $item_id, $options );
	}

	/**
	 * Gets the price of the cart item. Always exclusive of taxes.
 	 *
 	 * Do not use this for getting the final price (with taxes and discounts) of an item.
 	 * Use rpress_get_cart_item_final_price()
	 *
	 * @since 1.0
	 *
	 * @param  int        $fooditem_id               Download ID for the cart item
	 * @param  array      $options                   Optional parameters, used for defining variable prices
 	 * @param  bool       $remove_tax_from_inclusive Remove the tax amount from tax inclusive priced products.
 	 * @return float|bool Price for this item
	 */
	public function get_item_price( $fooditem_id = 0, $options = array(), $remove_tax_from_inclusive = false ) {
		$price = 0;
		$variable_prices = rpress_has_variable_prices( $fooditem_id );

		if ( $variable_prices ) {
			$prices = rpress_get_variable_prices( $fooditem_id );

			if ( $prices ) {
				if ( ! empty( $options ) ) {
					$price = isset( $prices[ $options['price_id'] ] ) ? $prices[ $options['price_id'] ]['amount'] : false;
				} else {
					$price = false;
				}
			}
		}

		if ( ! $variable_prices || false === $price ) {
			// Get the standard Download price if not using variable prices
			$price = rpress_get_fooditem_price( $fooditem_id );
		}

		if ( $remove_tax_from_inclusive && rpress_prices_include_tax() ) {
			$price -= $this->get_item_tax( $fooditem_id, $options, $price );
		}

		return apply_filters( 'rpress_cart_item_price', $price, $fooditem_id, $options );
	}

	/**
	 * Final Price of Item in Cart (incl. discounts and taxes)
	 *
	 * @since 1.0
	 *
	 * @param int $item_key Cart item key
 	 * @return float Final price for the item
	 */
	public function get_item_final_price( $item_key = 0 ) {
		$final_price = $this->details[ $item_key ]['price'];

		return apply_filters( 'rpress_cart_item_final_price', $final_price, $item_key );
	}

	/**
	 * Calculate the tax for an item in the cart.
	 *
	 * @since 1.0
	 *
	 * @param array $fooditem_id Download ID
	 * @param array $options     Cart item options
	 * @param float $subtotal    Cart item subtotal
	 * @return float Tax amount
	 */
	public function get_item_tax( $fooditem_id = 0, $options = array(), $subtotal = '' ) {
		$tax = 0;

		if ( ! rpress_fooditem_is_tax_exclusive( $fooditem_id ) ) {
			$country = ! empty( $_POST['billing_country'] ) ? $_POST['billing_country'] : false;
			$state   = ! empty( $_POST['card_state'] )      ? $_POST['card_state']      : false;

			$tax = rpress_calculate_tax( $subtotal, $country, $state );
		}

		$tax = max( $tax, 0 );

		return apply_filters( 'rpress_get_cart_item_tax', $tax, $fooditem_id, $options, $subtotal );
	}

	/**
	 * Get Cart Fees
	 *
	 * @since 1.0
	 * @return array Cart fees
	 */
	public function get_fees( $type = 'all', $fooditem_id = 0, $price_id = null ) {
		return RPRESS()->fees->get_fees( $type, $fooditem_id, $price_id );
	}

	/**
	 * Get All Cart Fees.
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_all_fees() {
		$this->fees = RPRESS()->fees->get_fees( 'all' );
		return $this->fees;
	}

	/**
	 * Get Cart Items Subtotal.
	 *
	 * @since 1.0
	 *
	 * @param array $items Cart items array
 	 * @return float items subtotal
	 */
	public function get_items_subtotal( $items ) {
		$subtotal = 0.00;

		if ( is_array( $items ) && ! empty( $items ) ) {
			$prices = wp_list_pluck( $items, 'subtotal' );

			if ( is_array( $prices ) ) {
				$subtotal = array_sum( $prices );
			} else {
				$subtotal = 0.00;
			}

			if ( $subtotal < 0 ) {
				$subtotal = 0.00;
			}
		}

		$this->subtotal = apply_filters( 'rpress_get_cart_items_subtotal', $subtotal );

		return $this->subtotal;
	}

	/**
	 * Get Discountable Subtotal.
	 *
	 * @since 1.0
	 * @return float Total discountable amount before taxes
	 */
	public function get_discountable_subtotal( $code_id ) {
		$cart_items = $this->get_contents_details();
		$items      = array();

		$excluded_products = rpress_get_discount_excluded_products( $code_id );

		if ( $cart_items ) {
			foreach( $cart_items as $item ) {
				if ( ! in_array( $item['id'], $excluded_products ) ) {
					$items[] =  $item;
				}
			}
		}

		$subtotal = $this->get_items_subtotal( $items );

		return apply_filters( 'rpress_get_cart_discountable_subtotal', $subtotal );
	}

	/**
	 * Get Discounted Amount.
	 *
	 * @since 1.0
	 *
	 * @param bool $discounts Discount codes
	 * @return float|mixed|void Total discounted amount
	 */
	public function get_discounted_amount( $discounts = false ) {
		$amount = 0.00;
		$items  = $this->get_contents_details();

		if ( $items ) {
			$discounts = wp_list_pluck( $items, 'discount' );

			if ( is_array( $discounts ) ) {
				$discounts = array_map( 'floatval', $discounts );
				$amount    = array_sum( $discounts );
			}
		}

		return apply_filters( 'rpress_get_cart_discounted_amount', $amount );
	}

	/**
	 * Get Cart Subtotal.
	 *
	 * Gets the total price amount in the cart before taxes and before any discounts.
	 *
	 * @since 1.0
	 *
	 * @return float Total amount before taxes
	 */
	public function get_subtotal() {
		$items    = $this->get_contents_details();
		$subtotal = $this->get_items_subtotal( $items );
		$options_price = (float) $this->get_options_amount();

		$subtotal = $subtotal + $options_price;
		return apply_filters( 'rpress_get_cart_subtotal', $subtotal );
	}

	/**
	 * Subtotal (before taxes).
	 *
	 * @since 1.0
	 * @return float Total amount before taxes fully formatted
	 */
	public function subtotal() {
		return esc_html( rpress_currency_filter( rpress_format_amount( rpress_get_cart_subtotal() ) ) );
	}

	/**
	*
	* @since 1.0
	* @return Get the product options price as total
	*/

	public function get_options_amount() {
		$amount = 0.00;
		$items  = $this->get_contents_details();
		$options_arr = array();
		if( is_array($items) ) {
			foreach( $items as $key => $item ) {
				foreach( $item['item_number'] as $k => $v ) {
					if( is_array($v) ) {
						foreach( $v as $data => $data_val ) {
							if( isset($data_val['price']) ) {
								array_push($options_arr, $data_val['price']);
							}
						}
					}
				}
			}
		}

		$amount = array_sum($options_arr);

		return $amount;
	}

	/**
	 * Get Total Cart Amount.
	 *
	 * @since 1.0
	 *
	 * @param bool $discounts Array of discounts to apply (needed during AJAX calls)
	 * @return float Cart amount
	 */
	public function get_total( $discounts = false ) {
		$subtotal     = (float) $this->get_subtotal();
		$discounts    = (float) $this->get_discounted_amount();
		$fees         = (float) $this->get_total_fees();
		$cart_tax     = (float) $this->get_tax();
		$total_wo_tax = $subtotal - $discounts + $fees;
		$total        = $subtotal - $discounts + $cart_tax + $fees ;

		if ( $total < 0 || ! $total_wo_tax > 0 ) {
			$total = 0.00;
		}

		$this->total = (float) apply_filters( 'rpress_get_cart_total', $total );

		return $this->total;
	}

	/**
	 * Fully Formatted Total Cart Amount.
	 *
	 * @since 1.0
	 *
	 * @param bool $echo
	 * @return mixed|string|void
	 */
	public function total( $echo ) {
		$total = apply_filters( 'rpress_cart_total', rpress_currency_filter( rpress_format_amount( $this->get_total() ) ) );

		if ( ! $echo ) {
			return $total;
		}

		echo $total;
	}

	/**
	 * Get Cart Fee Total
	 *
	 * @since 1.0
	 * @return double
	 */
	public function get_total_fees() {
		$fee_total = 0.00;

		foreach ( $this->get_fees() as $fee ) {

			// Since fees affect cart item totals, we need to not count them towards the cart total if there is an association.
			if ( ! empty( $fee['fooditem_id'] ) ) {
				continue;
			}

			$fee_total += $fee['amount'];
		}

		return apply_filters( 'rpress_get_fee_total', $fee_total, $this->fees );
	}

	/**
	 * Get the price ID for an item in the cart.
	 *
	 * @since 1.0
	 *
	 * @param array $item Item details
	 * @return string $price_id Price ID
	 */
	public function get_item_price_id( $item = array() ) {
		if ( isset( $item['item_number'] ) ) {
			$price_id = isset( $item['item_number']['options']['price_id'] ) ? $item['item_number']['options']['price_id'] : null;
		} else {
			$price_id = isset( $item['options']['price_id'] ) ? $item['options']['price_id'] : null;
		}

		return $price_id;
	}

	/**
	 * Get the price name for an item in the cart.
	 *
	 * @since 1.0
	 *
	 * @param array $item Item details
	 * @return string $name Price name
	 */
	public function get_item_price_name( $item = array() ) {
		$price_id = (int) $this->get_item_price_id( $item );
		$prices   = rpress_get_variable_prices( $item['id'] );
		$name     = ! empty( $prices[ $price_id ] ) ? $prices[ $price_id ]['name'] : '';

		return apply_filters( 'rpress_get_cart_item_price_name', $name, $item['id'], $price_id, $item );
	}

	/**
	 * Get the name of an item in the cart.
	 *
	 * @since 1.0
	 *
	 * @param array $item Item details
	 * @return string $name Item name
	 */
	public function get_item_name( $item = array() ) {
		$item_title = get_the_title( $item['id'] );

		if ( empty( $item_title ) ) {
			$item_title = $item['id'];
		}

		if ( rpress_has_variable_prices( $item['id'] ) && false !== rpress_get_cart_item_price_id( $item ) ) {
			$item_title .= ' - ' . rpress_get_cart_item_price_name( $item );
		}

		return apply_filters( 'rpress_get_cart_item_name', $item_title, $item['id'], $item );
	}

	/**
	 * Get all applicable tax for the items in the cart
	 *
	 * @since 1.0
	 * @return float Total tax amount
	 */
	public function get_tax() {
		$cart_tax     = 0;
		$items        = $this->get_contents_details();

		if ( $items ) {

			$taxes = wp_list_pluck( $items, 'tax' );

			if ( is_array( $taxes ) ) {
				$cart_tax = array_sum( $taxes );
			}
		}
		$cart_tax += $this->get_tax_on_fees();

		$subtotal = $this->get_subtotal();
		if ( empty( $subtotal ) ) {
			$cart_tax = 0;
		}

		$cart_tax = apply_filters( 'rpress_get_cart_tax', rpress_sanitize_amount( $cart_tax ) );

		return $cart_tax;
	}

	/**
	 * Gets the total tax amount for the cart contents in a fully formatted way
	 *
	 * @since 1.0
	 *
	 * @param boolean $echo Decides if the result should be returned or not
	 * @return string Total tax amount
	 */
	public function tax( $echo = false ) {
		$cart_tax = $this->get_tax();
		$cart_tax = rpress_currency_filter( rpress_format_amount( $cart_tax ) );

		$tax = max( $cart_tax, 0 );
		$tax = apply_filters( 'rpress_cart_tax', $cart_tax );

		if ( ! $echo ) {
			return $tax;
		} else {
			echo $tax;
		}
	}

	/**
	 * Get tax applicable for fees.
	 *
	 * @since 1.0
	 * @return float Total taxable amount for fees
	 */
	public function get_tax_on_fees() {
		$tax  = 0;
		$fees = rpress_get_cart_fees();

		if ( $fees ) {
			foreach ( $fees as $fee_id => $fee ) {
				if ( ! empty( $fee['no_tax'] ) || $fee['amount'] < 0 ) {
					continue;
				}

				/**
				 * Fees (at this time) must be exclusive of tax
				 */
				add_filter( 'rpress_prices_include_tax', '__return_false' );
				$tax += rpress_calculate_tax( $fee['amount'] );
				remove_filter( 'rpress_prices_include_tax', '__return_false' );
			}
		}

		return apply_filters( 'rpress_get_cart_fee_tax', $tax );
	}

	/**
	 * Is Cart Saving Enabled?
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function is_saving_enabled() {
		return rpress_get_option( 'enable_cart_saving', false );
	}

	/**
	 * Checks if the cart has been saved
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function is_saved() {
		if ( ! $this->is_saving_enabled() ) {
			return false;
		}

		$saved_cart = get_user_meta( get_current_user_id(), 'rpress_saved_cart', true );

		if ( is_user_logged_in() ) {
			if ( ! $saved_cart ) {
				return false;
			}

			if ( $saved_cart === RPRESS()->session->get( 'rpress_cart' ) ) {
				return false;
			}

			return true;
		} else {
			if ( ! isset( $_COOKIE['rpress_saved_cart'] ) ) {
				return false;
			}

			if ( json_decode( stripslashes( $_COOKIE['rpress_saved_cart'] ), true ) === RPRESS()->session->get( 'rpress_cart' ) ) {
				return false;
			}

			return true;
		}
	}

	/**
	 * Save Cart
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function save() {
		if ( ! $this->is_saving_enabled() ) {
			return false;
		}

		$user_id  = get_current_user_id();
		$cart     = RPRESS()->session->get( 'rpress_cart' );
		$token    = rpress_generate_cart_token();
		$messages = RPRESS()->session->get( 'rpress_cart_messages' );

		if ( is_user_logged_in() ) {
			update_user_meta( $user_id, 'rpress_saved_cart', $cart,  false );
			update_user_meta( $user_id, 'rpress_cart_token', $token, false );
		} else {
			$cart = json_encode( $cart );
			setcookie( 'rpress_saved_cart', $cart,  time() + 3600 * 24 * 7, COOKIEPATH, COOKIE_DOMAIN );
			setcookie( 'rpress_cart_token', $token, time() + 3600 * 24 * 7, COOKIEPATH, COOKIE_DOMAIN );
		}

		$messages = RPRESS()->session->get( 'rpress_cart_messages' );

		if ( ! $messages ) {
			$messages = array();
		}

		$messages['rpress_cart_save_successful'] = sprintf(
			'<strong>%1$s</strong>: %2$s',
			__( 'Success', 'restro-press' ),
			__( 'Cart saved successfully. You can restore your cart using this URL:', 'restro-press' ) . ' ' . '<a href="' .  rpress_get_checkout_uri() . '?rpress_action=restore_cart&rpress_cart_token=' . $token . '">' .  rpress_get_checkout_uri() . '?rpress_action=restore_cart&rpress_cart_token=' . $token . '</a>'
		);

		RPRESS()->session->set( 'rpress_cart_messages', $messages );

		if ( $cart ) {
			return true;
		}

		return false;
	}

	/**
	 * Restore Cart
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function restore() {
		if ( ! $this->is_saving_enabled() ) {
			return false;
		}

		$user_id    = get_current_user_id();
		$saved_cart = get_user_meta( $user_id, 'rpress_saved_cart', true );
		$token      = $this->get_token();

		if ( is_user_logged_in() && $saved_cart ) {
			$messages = RPRESS()->session->get( 'rpress_cart_messages' );

			if ( ! $messages ) {
				$messages = array();
			}

			if ( isset( $_GET['rpress_cart_token'] ) && ! hash_equals( $_GET['rpress_cart_token'], $token ) ) {
				$messages['rpress_cart_restoration_failed'] = sprintf( '<strong>%1$s</strong>: %2$s', __( 'Error', 'restro-press' ), __( 'Cart restoration failed. Invalid token.', 'restro-press' ) );
				RPRESS()->session->set( 'rpress_cart_messages', $messages );
			}

			delete_user_meta( $user_id, 'rpress_saved_cart' );
			delete_user_meta( $user_id, 'rpress_cart_token' );

			if ( isset( $_GET['rpress_cart_token'] ) && $_GET['rpress_cart_token'] != $token ) {
				return new WP_Error( 'invalid_cart_token', __( 'The cart cannot be restored. Invalid token.', 'restro-press' ) );
			}
		} elseif ( ! is_user_logged_in() && isset( $_COOKIE['rpress_saved_cart'] ) && $token ) {
			$saved_cart = $_COOKIE['rpress_saved_cart'];

			if ( ! hash_equals( $_GET['rpress_cart_token'], $token ) ) {
				$messages['rpress_cart_restoration_failed'] = sprintf( '<strong>%1$s</strong>: %2$s', __( 'Error', 'restro-press' ), __( 'Cart restoration failed. Invalid token.', 'restro-press' ) );
				RPRESS()->session->set( 'rpress_cart_messages', $messages );

				return new WP_Error( 'invalid_cart_token', __( 'The cart cannot be restored. Invalid token.', 'restro-press' ) );
			}

			$saved_cart = json_decode( stripslashes( $saved_cart ), true );

			setcookie( 'rpress_saved_cart', '', time()-3600, COOKIEPATH, COOKIE_DOMAIN );
			setcookie( 'rpress_cart_token', '', time()-3600, COOKIEPATH, COOKIE_DOMAIN );
		}

		$messages['rpress_cart_restoration_successful'] = sprintf( '<strong>%1$s</strong>: %2$s', __( 'Success', 'restro-press' ), __( 'Cart restored successfully.', 'restro-press' ) );
		RPRESS()->session->set( 'rpress_cart', $saved_cart );
		RPRESS()->session->set( 'rpress_cart_messages', $messages );

		// @e also have to set this instance to what the session is.
		$this->contents = $saved_cart;

		return true;
	}

	/**
	 * Retrieve a saved cart token. Used in validating saved carts
	 *
	 * @since 1.0
	 * @return int
	 */
	public function get_token() {
		$user_id = get_current_user_id();

		if ( is_user_logged_in() ) {
			$token = get_user_meta( $user_id, 'rpress_cart_token', true );
		} else {
			$token = isset( $_COOKIE['rpress_cart_token'] ) ? $_COOKIE['rpress_cart_token'] : false;
		}

		return apply_filters( 'rpress_get_cart_token', $token, $user_id );
	}

	/**
	 * Generate URL token to restore the cart via a URL
	 *
	 * @since 1.0
	 * @return int
	 */
	public function generate_token() {
		return apply_filters( 'rpress_generate_cart_token', md5( mt_rand() . time() ) );
	}
}
