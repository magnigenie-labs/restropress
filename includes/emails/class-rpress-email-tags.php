<?php
/**
 * RestroPress API for creating Email template tags
 *
 * Email tags are wrapped in { }
 *
 * A few examples:
 *
 * {fooditem_list}
 * {name}
 * {sitename}
 *
 *
 * To replace tags in content, use: rpress_do_email_tags( $content, payment_id );
 *
 * To add tags, use: rpress_add_email_tag( $tag, $description, $func ). Be sure to wrap rpress_add_email_tag()
 * in a function hooked to the 'rpress_add_email_tags' action
 *
 * @package     RPRESS
 * @subpackage  Emails
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since  1.0.0
 * @author      RestroPress
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class RPRESS_Email_Template_Tags {

	/**
	 * Container for storing all tags
	 *
	 * @since  1.0.0
	 */
	private $tags;

	/**
	 * Payment ID
	 *
	 * @since  1.0.0
	 */
	private $payment_id;

	/**
	 * Add an email tag
	 *
	 * @since  1.0.0
	 *
	 * @param string   $tag  Email tag to be replace in email
	 * @param callable $func Hook to run when email tag is found
	 */
	public function add( $tag, $description, $func ) {
		if ( is_callable( $func ) ) {
			$this->tags[$tag] = array(
				'tag'         => $tag,
				'description' => $description,
				'func'        => $func
			);
		}
	}

	/**
	 * Remove an email tag
	 *
	 * @since  1.0.0
	 *
	 * @param string $tag Email tag to remove hook from
	 */
	public function remove( $tag ) {
		unset( $this->tags[$tag] );
	}

	/**
	 * Check if $tag is a registered email tag
	 *
	 * @since  1.0.0
	 *
	 * @param string $tag Email tag that will be searched
	 *
	 * @return bool
	 */
	public function email_tag_exists( $tag ) {
		return array_key_exists( $tag, $this->tags );
	}

	/**
	 * Returns a list of all email tags
	 *
	 * @since  1.0.0
	 *
	 * @return array
	 */
	public function get_tags() {
		return $this->tags;
	}

	/**
	 * Search content for email tags and filter email tags through their hooks
	 *
	 * @param string $content Content to search for email tags
	 * @param int $payment_id The payment id
	 *
	 * @since  1.0.0
	 *
	 * @return string Content with email tags filtered out.
	 */
	public function do_tags( $content, $payment_id ) {

		// Check if there is atleast one tag added
		if ( empty( $this->tags ) || ! is_array( $this->tags ) ) {
			return $content;
		}

		$this->payment_id = $payment_id;

		$new_content = preg_replace_callback( "/{([A-z0-9\-\_]+)}/s", array( $this, 'do_tag' ), $content );

		$this->payment_id = null;

		return $new_content;
	}

	/**
	 * Do a specific tag, this function should not be used. Please use rpress_do_email_tags instead.
	 *
	 * @since  1.0.0
	 *
	 * @param $m message
	 *
	 * @return mixed
	 */
	public function do_tag( $m ) {

		// Get tag
		$tag = $m[1];

		// Return tag if tag not set
		if ( ! $this->email_tag_exists( $tag ) ) {
			return $m[0];
		}

		return call_user_func( $this->tags[$tag]['func'], $this->payment_id, $tag );
	}

}

/**
 * Add an email tag
 *
 * @since  1.0.0
 *
 * @param string   $tag  Email tag to be replace in email
 * @param callable $func Hook to run when email tag is found
 */
function rpress_add_email_tag( $tag, $description, $func ) {
	RPRESS()->email_tags->add( $tag, $description, $func );
}

/**
 * Remove an email tag
 *
 * @since  1.0.0
 *
 * @param string $tag Email tag to remove hook from
 */
function rpress_remove_email_tag( $tag ) {
	RPRESS()->email_tags->remove( $tag );
}

/**
 * Check if $tag is a registered email tag
 *
 * @since  1.0.0
 *
 * @param string $tag Email tag that will be searched
 *
 * @return bool
 */
function rpress_email_tag_exists( $tag ) {
	return RPRESS()->email_tags->email_tag_exists( $tag );
}

/**
 * Get all email tags
 *
 * @since  1.0.0
 *
 * @return array
 */
function rpress_get_email_tags() {
	return RPRESS()->email_tags->get_tags();
}

/**
 * Get a formatted HTML list of all available email tags
 *
 * @since  1.0.0
 *
 * @return string
 */
function rpress_get_emails_tags_list() {
	// The list
	$list = '';

	// Get all tags
	$email_tags = rpress_get_email_tags();

	// Check
	if ( count( $email_tags ) > 0 ) {

		// Loop
		foreach ( $email_tags as $email_tag ) {

			// Add email tag to list
			$list .= '{' . $email_tag['tag'] . '} - ' . $email_tag['description'] . '<br/>';

		}

	}

	// Return the list
	return $list;
}

/**
 * Search content for email tags and filter email tags through their hooks
 *
 * @param string $content Content to search for email tags
 * @param int $payment_id The payment id
 *
 * @since  1.0.0
 *
 * @return string Content with email tags filtered out.
 */
function rpress_do_email_tags( $content, $payment_id ) {

	// Replace all tags
	$content = RPRESS()->email_tags->do_tags( $content, $payment_id );

	// Maintaining backwards compatibility
	$content = apply_filters( 'rpress_email_template_tags', $content, rpress_get_payment_meta( $payment_id ), $payment_id );

	// Return content
	return $content;
}

/**
 * Load email tags
 *
 * @since  1.0.0
 */
function rpress_load_email_tags() {
	do_action( 'rpress_add_email_tags' );
}
add_action( 'init', 'rpress_load_email_tags', -999 );

/**
 * Add default RPRESS email template tags
 *
 * @since  1.0.0
 */
function rpress_setup_email_tags() {

	// Setup default tags array
	$email_tags = array(
		array(
			'tag'         => 'fooditem_list',
			'description' => __( 'A list of fooditem purchased', 'restro-press' ),
			'function'    => 'text/html' == RPRESS()->emails->get_content_type() ? 'rpress_email_tag_fooditem_list' : 'rpress_email_tag_fooditem_list_plain'
		),
		array(
			'tag'         => 'name',
			'description' => __( "The buyer's first name", 'restro-press' ),
			'function'    => 'rpress_email_tag_first_name'
		),
		array(
			'tag'         => 'fullname',
			'description' => __( "The buyer's full name, first and last", 'restro-press' ),
			'function'    => 'rpress_email_tag_fullname'
		),
		array(
			'tag'         => 'username',
			'description' => __( "The buyer's user name on the site, if they registered an account", 'restro-press' ),
			'function'    => 'rpress_email_tag_username'
		),
		array(
			'tag'         => 'user_email',
			'description' => __( "The buyer's email address", 'restro-press' ),
			'function'    => 'rpress_email_tag_user_email'
		),
		array(
			'tag'         => 'billing_address',
			'description' => __( 'The buyer\'s billing address', 'restro-press' ),
			'function'    => 'rpress_email_tag_billing_address'
		),
		array(
			'tag'         => 'date',
			'description' => __( 'The date of the purchase', 'restro-press' ),
			'function'    => 'rpress_email_tag_date'
		),
		array(
			'tag'         => 'subtotal',
			'description' => __( 'The price of the purchase before taxes', 'restro-press' ),
			'function'    => 'rpress_email_tag_subtotal'
		),
		array(
			'tag'         => 'tax',
			'description' => __( 'The taxed amount of the purchase', 'restro-press' ),
			'function'    => 'rpress_email_tag_tax'
		),
		array(
			'tag'         => 'price',
			'description' => __( 'The total price of the purchase', 'restro-press' ),
			'function'    => 'rpress_email_tag_price'
		),
		array(
			'tag'         => 'payment_id',
			'description' => __( 'The unique ID number for this purchase', 'restro-press' ),
			'function'    => 'rpress_email_tag_payment_id'
		),
		array(
			'tag'         => 'receipt_id',
			'description' => __( 'The unique ID number for this purchase receipt', 'restro-press' ),
			'function'    => 'rpress_email_tag_receipt_id'
		),
		array(
			'tag'         => 'payment_method',
			'description' => __( 'The method of payment used for this purchase', 'restro-press' ),
			'function'    => 'rpress_email_tag_payment_method'
		),
		array(
			'tag'         => 'sitename',
			'description' => __( 'Your site name', 'restro-press' ),
			'function'    => 'rpress_email_tag_sitename'
		),
		array(
			'tag'         => 'receipt_link',
			'description' => __( 'Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly.', 'restro-press' ),
			'function'    => 'rpress_email_tag_receipt_link'
		),
		array(
			'tag'         => 'discount_codes',
			'description' => __( 'Adds a list of any discount codes applied to this purchase', 'restro-press' ),
			'function'    => 'rpress_email_tag_discount_codes'
		),
		array(
			'tag'         => 'ip_address',
			'description' => __( 'The buyer\'s IP Address', 'restro-press' ),
			'function'    => 'rpress_email_tag_ip_address'
		)
	);

	// Apply rpress_email_tags filter
	$email_tags = apply_filters( 'rpress_email_tags', $email_tags );

	// Add email tags
	foreach ( $email_tags as $email_tag ) {
		rpress_add_email_tag( $email_tag['tag'], $email_tag['description'], $email_tag['function'] );
	}

}
add_action( 'rpress_add_email_tags', 'rpress_setup_email_tags' );

/**
 * Email template tag: fooditem_list
 * A list of fooditem purchased
 *
 * @param int $payment_id
 *
 * @return string fooditem_list
 */
function rpress_email_tag_fooditem_list( $payment_id ) {
	$payment = new RPRESS_Payment( $payment_id );

	$payment_data  = $payment->get_meta();
	$fooditem_list = '<ul>';
	$cart_items    = $payment->cart_details;
	$email         = $payment->email;

	if ( $cart_items ) {
		$show_names = apply_filters( 'rpress_email_show_names', true );
		$show_links = apply_filters( 'rpress_email_show_links', true );

		foreach ( $cart_items as $item ) {

			if ( rpress_use_skus() ) {
				$sku = rpress_get_fooditem_sku( $item['id'] );
			}

			if ( rpress_item_quantities_enabled() ) {
				$quantity = $item['quantity'];
			}

			$price_id = rpress_get_cart_item_price_id( $item );
			if ( $show_names ) {

				$title = '<strong>' . get_the_title( $item['id'] ) . '</strong>';

				if ( ! empty( $quantity ) && $quantity > 1 ) {
					$title .= "&nbsp;&ndash;&nbsp;" . __( 'Quantity', 'restro-press' ) . ': ' . $quantity;
				}

				if ( ! empty( $sku ) ) {
					$title .= "&nbsp;&ndash;&nbsp;" . __( 'SKU', 'restro-press' ) . ': ' . $sku;
				}

				if( ! empty( $price_id ) && 0 !== $price_id ){
					$title .= "&nbsp;&ndash;&nbsp;" . rpress_get_price_option_name( $item['id'], $price_id, $payment_id );
				}

				$fooditem_list .= '<li>' . apply_filters( 'rpress_email_receipt_fooditem_title', $title, $item, $price_id, $payment_id ) . '<br/>';
			}

			$files = rpress_get_fooditem_files( $item['id'], $price_id );

			if ( ! empty( $files ) ) {

				foreach ( $files as $filekey => $file ) {

					if ( $show_links ) {
						$fooditem_list .= '<div>';
							$file_url = rpress_get_fooditem_file_url( $payment_data['key'], $email, $filekey, $item['id'], $price_id );
							$fooditem_list .= '<a href="' . esc_url_raw( $file_url ) . '">' . rpress_get_file_name( $file ) . '</a>';
							$fooditem_list .= '</div>';
					} else {
						$fooditem_list .= '<div>';
							$fooditem_list .= rpress_get_file_name( $file );
						$fooditem_list .= '</div>';
					}

				}

			} elseif ( rpress_is_bundled_product( $item['id'] ) ) {

				$bundled_products = apply_filters( 'rpress_email_tag_bundled_products', rpress_get_bundled_products( $item['id'], $price_id ), $item, $payment_id, 'fooditem_list' );

				foreach ( $bundled_products as $bundle_item ) {

					$fooditem_list .= '<div class="rpress_bundled_product"><strong>' . get_the_title( $bundle_item ) . '</strong></div>';

					$fooditem_files = rpress_get_fooditem_files( rpress_get_bundle_item_id( $bundle_item ), rpress_get_bundle_item_price_id( $bundle_item ) );

					foreach ( $fooditem_files as $filekey => $file ) {
						if ( $show_links ) {
							$fooditem_list .= '<div>';
							$file_url = rpress_get_fooditem_file_url( $payment_data['key'], $email, $filekey, $bundle_item, $price_id );
							$fooditem_list .= '<a href="' . esc_url( $file_url ) . '">' . rpress_get_file_name( $file ) . '</a>';
							$fooditem_list .= '</div>';
						} else {
							$fooditem_list .= '<div>';
							$fooditem_list .= rpress_get_file_name( $file );
							$fooditem_list .= '</div>';
						}
					}
				}

			} else {

				$no_fooditems_message = apply_filters( 'rpress_receipt_no_files_found_text', __( 'No fooditemable files found.', 'restro-press' ), $item['id'] );
				$no_fooditems_message = apply_filters( 'rpress_email_receipt_no_fooditems_message', $no_fooditems_message, $item['id'], $price_id, $payment_id );

				if ( ! empty( $no_fooditems_message ) ){
					$fooditem_list .= '<div>';
						$fooditem_list .= $no_fooditems_message;
					$fooditem_list .= '</div>';
				}
			}


			if ( '' != rpress_get_product_notes( $item['id'] ) ) {
				$fooditem_list .= ' &mdash; <small>' . rpress_get_product_notes( $item['id'] ) . '</small>';
			}


			if ( $show_names ) {
				$fooditem_list .= '</li>';
			}
		}
	}
	$fooditem_list .= '</ul>';

	return $fooditem_list;
}

/**
 * Email template tag: fooditem_list
 * A list of fooditem purchased in plaintext
 *
 * @since 1.0
 * @param int $payment_id
 *
 * @return string fooditem_list
 */
function rpress_email_tag_fooditem_list_plain( $payment_id ) {
	$payment = new RPRESS_Payment( $payment_id );

	$payment_data  = $payment->get_meta();
	$cart_items    = $payment->cart_details;
	$email         = $payment->email;
	$fooditem_list = '';

	if ( $cart_items ) {
		$show_names = apply_filters( 'rpress_email_show_names', true );
		$show_links = apply_filters( 'rpress_email_show_links', true );

		foreach ( $cart_items as $item ) {

			if ( rpress_use_skus() ) {
				$sku = rpress_get_fooditem_sku( $item['id'] );
			}

			if ( rpress_item_quantities_enabled() ) {
				$quantity = $item['quantity'];
			}

			$price_id = rpress_get_cart_item_price_id( $item );
			if ( $show_names ) {

				$title = get_the_title( $item['id'] );

				if ( ! empty( $quantity ) && $quantity > 1 ) {
					$title .= __( 'Quantity', 'restro-press' ) . ': ' . $quantity;
				}

				if ( ! empty( $sku ) ) {
					$title .= __( 'SKU', 'restro-press' ) . ': ' . $sku;
				}

				if ( $price_id !== null ) {
					$title .= rpress_get_price_option_name( $item['id'], $price_id, $payment_id );
				}

				$fooditem_list .= "\n";

				$fooditem_list .= apply_filters( 'rpress_email_receipt_fooditem_title', $title, $item, $price_id, $payment_id )  . "\n";
			}

			$files = rpress_get_fooditem_files( $item['id'], $price_id );

			if ( ! empty( $files ) ) {

				foreach ( $files as $filekey => $file ) {
					if( $show_links ) {
						$fooditem_list .= "\n";
						$file_url = rpress_get_fooditem_file_url( $payment_data['key'], $email, $filekey, $item['id'], $price_id );
						$fooditem_list .= rpress_get_file_name( $file ) . ': ' . $file_url . "\n";
					} else {
						$fooditem_list .= "\n";
						$fooditem_list .= rpress_get_file_name( $file ) . "\n";
					}
				}

			} elseif ( rpress_is_bundled_product( $item['id'] ) ) {

				$bundled_products = apply_filters( 'rpress_email_tag_bundled_products', rpress_get_bundled_products( $item['id'] ), $item, $payment_id, 'fooditem_list' );

				foreach ( $bundled_products as $bundle_item ) {

					$fooditem_list .= '<div class="rpress_bundled_product"><strong>' . get_the_title( $bundle_item ) . '</strong></div>';

					$files = rpress_get_fooditem_files( $bundle_item );

					foreach ( $files as $filekey => $file ) {
						if( $show_links ) {
							$file_url = rpress_get_fooditem_file_url( $payment_data['key'], $email, $filekey, $bundle_item, $price_id );
							$fooditem_list .= rpress_get_file_name( $file ) . ': ' . $file_url . "\n";
						} else {
							$fooditem_list .= rpress_get_file_name( $file ) . "\n";
						}
					}
				}
			}


			if ( '' != rpress_get_product_notes( $item['id'] ) ) {
				$fooditem_list .= "\n";
				$fooditem_list .= rpress_get_product_notes( $item['id'] ) . "\n";
			}
		}
	}

	return $fooditem_list;
}

/**
 * Email template tag: file_urls
 * A plain-text list of fooditem URLs for each fooditem purchased
 *
 * @param int $payment_id
 *
 * @return string $file_urls
 */
function rpress_email_tag_file_urls( $payment_id ) {
	$payment = new RPRESS_Payment( $payment_id );

	$payment_data = $payment->get_meta();
	$file_urls    = '';
	$cart_items   = $payment->cart_details;
	$email        = $payment->email;

	foreach ( $cart_items as $item ) {

		$price_id = rpress_get_cart_item_price_id( $item );
		$files    = rpress_get_fooditem_files( $item['id'], $price_id );

		if ( $files ) {
			foreach ( $files as $filekey => $file ) {
				$file_url = rpress_get_fooditem_file_url( $payment_data['key'], $email, $filekey, $item['id'], $price_id );

				$file_urls .= esc_html( $file_url ) . '<br/>';
			}
		}
		elseif ( rpress_is_bundled_product( $item['id'] ) ) {

			$bundled_products = apply_filters( 'rpress_email_tag_bundled_products', rpress_get_bundled_products( $item['id'] ), $item, $payment_id, 'file_urls' );

			foreach ( $bundled_products as $bundle_item ) {

				$files = rpress_get_fooditem_files( $bundle_item );
				foreach ( $files as $filekey => $file ) {
					$file_url = rpress_get_fooditem_file_url( $payment_data['key'], $email, $filekey, $bundle_item, $price_id );
					$file_urls .= esc_html( $file_url ) . '<br/>';
				}

			}
		}

	}

	return $file_urls;
}

/**
 * Email template tag: name
 * The buyer's first name
 *
 * @param int $payment_id
 *
 * @return string name
 */
function rpress_email_tag_first_name( $payment_id ) {
	$payment   = new RPRESS_Payment( $payment_id );
	$user_info = $payment->user_info;

	if( empty( $user_info) ) {
		return '';
	}

	$email_name   = rpress_get_email_names( $user_info, $payment );

	return $email_name['name'];
}

/**
 * Email template tag: fullname
 * The buyer's full name, first and last
 *
 * @param int $payment_id
 *
 * @return string fullname
 */
function rpress_email_tag_fullname( $payment_id ) {
	$payment   = new RPRESS_Payment( $payment_id );
	$user_info = $payment->user_info;

	if( empty( $user_info ) ) {
		return '';
	}

	$email_name   = rpress_get_email_names( $user_info, $payment );
	return $email_name['fullname'];
}

/**
 * Email template tag: username
 * The buyer's user name on the site, if they registered an account
 *
 * @param int $payment_id
 *
 * @return string username
 */
function rpress_email_tag_username( $payment_id ) {
	$payment   = new RPRESS_Payment( $payment_id );
	$user_info = $payment->user_info;

	if( empty( $user_info ) ) {
		return '';
	}

	$email_name   = rpress_get_email_names( $user_info, $payment );
	return $email_name['username'];
}

/**
 * Email template tag: user_email
 * The buyer's email address
 *
 * @param int $payment_id
 *
 * @return string user_email
 */
function rpress_email_tag_user_email( $payment_id ) {
	$payment = new RPRESS_Payment( $payment_id );

	return $payment->email;
}

/**
 * Email template tag: billing_address
 * The buyer's billing address
 *
 * @param int $payment_id
 *
 * @return string billing_address
 */
function rpress_email_tag_billing_address( $payment_id ) {

	$user_info    = rpress_get_payment_meta_user_info( $payment_id );
	$user_address = ! empty( $user_info['address'] ) ? $user_info['address'] : array( 'line1' => '', 'line2' => '', 'city' => '', 'country' => '', 'state' => '', 'zip' => '' );

	$return = $user_address['line1'] . "\n";
	if( ! empty( $user_address['line2'] ) ) {
		$return .= $user_address['line2'] . "\n";
	}
	$return .= $user_address['city'] . ' ' . $user_address['zip'] . ' ' . $user_address['state'] . "\n";
	$return .= $user_address['country'];

	return $return;
}

/**
 * Email template tag: date
 * Date of purchase
 *
 * @param int $payment_id
 *
 * @return string date
 */
function rpress_email_tag_date( $payment_id ) {
	$payment = new RPRESS_Payment( $payment_id );
	return date_i18n( get_option( 'date_format' ), strtotime( $payment->date ) );
}

/**
 * Email template tag: subtotal
 * Price of purchase before taxes
 *
 * @param int $payment_id
 *
 * @return string subtotal
 */
function rpress_email_tag_subtotal( $payment_id ) {
	$payment  = new RPRESS_Payment( $payment_id );
	$subtotal = rpress_currency_filter( rpress_format_amount( $payment->subtotal ), $payment->currency );
	return html_entity_decode( $subtotal, ENT_COMPAT, 'UTF-8' );
}

/**
 * Email template tag: tax
 * The taxed amount of the purchase
 *
 * @param int $payment_id
 *
 * @return string tax
 */
function rpress_email_tag_tax( $payment_id ) {
	$payment = new RPRESS_Payment( $payment_id );
	$tax     = rpress_currency_filter( rpress_format_amount( $payment->tax ), $payment->currency );
	return html_entity_decode( $tax, ENT_COMPAT, 'UTF-8' );
}

/**
 * Email template tag: price
 * The total price of the purchase
 *
 * @param int $payment_id
 *
 * @return string price
 */
function rpress_email_tag_price( $payment_id ) {
	$payment = new RPRESS_Payment( $payment_id );
	$price   = rpress_currency_filter( rpress_format_amount( $payment->total ), $payment->currency );
	return html_entity_decode( $price, ENT_COMPAT, 'UTF-8' );
}

/**
 * Email template tag: payment_id
 * The unique ID number for this purchase
 *
 * @param int $payment_id
 *
 * @return int payment_id
 */
function rpress_email_tag_payment_id( $payment_id ) {
	$payment = new RPRESS_Payment( $payment_id );
	return $payment->number;
}

/**
 * Email template tag: receipt_id
 * The unique ID number for this purchase receipt
 *
 * @param int $payment_id
 *
 * @return string receipt_id
 */
function rpress_email_tag_receipt_id( $payment_id ) {
	$payment = new RPRESS_Payment( $payment_id );
	return $payment->key;
}

/**
 * Email template tag: payment_method
 * The method of payment used for this purchase
 *
 * @param int $payment_id
 *
 * @return string gateway
 */
function rpress_email_tag_payment_method( $payment_id ) {
	$payment = new RPRESS_Payment( $payment_id );
	return rpress_get_gateway_checkout_label( $payment->gateway );
}

/**
 * Email template tag: sitename
 * Your site name
 *
 * @param int $payment_id
 *
 * @return string sitename
 */
function rpress_email_tag_sitename( $payment_id ) {
	return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
}

/**
 * Email template tag: receipt_link
 * Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly
 *
 * @param $payment_id int
 *
 * @return string receipt_link
 */
function rpress_email_tag_receipt_link( $payment_id ) {
	$receipt_url = esc_url( add_query_arg( array(
		'payment_key' => rpress_get_payment_key( $payment_id ),
		'rpress_action'  => 'view_receipt'
	), home_url() ) );
	$formatted   = sprintf( __( '%1$sView it in your browser %2$s', 'restro-press' ), '<a href="' . $receipt_url . '">', '&raquo;</a>' );

	if ( rpress_get_option( 'email_template' ) !== 'none' ) {
		return $formatted;
	} else {
		return $receipt_url;
	}
}

/**
 * Email template tag: discount_codes
 * Adds a list of any discount codes applied to this purchase
 *
 * @since 1.0.0
 * @param int $payment_id
 * @return string $discount_codes
 */
function rpress_email_tag_discount_codes( $payment_id ) {
	$user_info = rpress_get_payment_meta_user_info( $payment_id );

	$discount_codes = '';

	if( isset( $user_info['discount'] ) && $user_info['discount'] !== 'none' ) {
		$discount_codes = $user_info['discount'];
	}

	return $discount_codes;
}

/**
 * Email template tag: IP address
 * IP address of the customer
 *
 * @since  1.0.0
 * @param int $payment_id
 * @return string IP address
 */
function rpress_email_tag_ip_address( $payment_id ) {
	$payment = new RPRESS_Payment( $payment_id );
	return $payment->ip;
}
