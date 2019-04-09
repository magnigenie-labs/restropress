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
 * in a function hooked to the 'rpress_email_tags' action
 *
 * @package     RPRESS
 * @subpackage  Emails
 * @copyright   Copyright (c) 2014, Magnigenie
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
			'description' => __( 'A list of fooditem purchased', 'restropress' ),
			'function'    => 'rpress_email_tag_fooditem_list'
		),
		array(
			'tag'         => 'name',
			'description' => __( "The buyer's first name", 'restropress' ),
			'function'    => 'rpress_email_tag_first_name'
		),
		array(
			'tag'         => 'fullname',
			'description' => __( "The buyer's full name, first and last", 'restropress' ),
			'function'    => 'rpress_email_tag_fullname'
		),
		array(
			'tag'         => 'username',
			'description' => __( "The buyer's user name on the site, if they registered an account", 'restropress' ),
			'function'    => 'rpress_email_tag_username'
		),
		array(
			'tag'         => 'user_email',
			'description' => __( "The buyer's email address", 'restropress' ),
			'function'    => 'rpress_email_tag_user_email'
		),
		array(
			'tag'         => 'billing_address',
			'description' => __( 'The buyer\'s billing address', 'restropress' ),
			'function'    => 'rpress_email_tag_billing_address'
		),
		array(
			'tag'         => 'date',
			'description' => __( 'The date of the purchase', 'restropress' ),
			'function'    => 'rpress_email_tag_date'
		),
		array(
			'tag'         => 'subtotal',
			'description' => __( 'The price of the purchase before taxes', 'restropress' ),
			'function'    => 'rpress_email_tag_subtotal'
		),
		array(
			'tag'         => 'tax',
			'description' => __( 'The taxed amount of the purchase', 'restropress' ),
			'function'    => 'rpress_email_tag_tax'
		),
		array(
			'tag'         => 'price',
			'description' => __( 'The total price of the purchase', 'restropress' ),
			'function'    => 'rpress_email_tag_price'
		),
		array(
			'tag'         => 'payment_id',
			'description' => __( 'The unique ID number for this purchase', 'restropress' ),
			'function'    => 'rpress_email_tag_payment_id'
		),
		array(
			'tag'         => 'receipt_id',
			'description' => __( 'The unique ID number for this purchase receipt', 'restropress' ),
			'function'    => 'rpress_email_tag_receipt_id'
		),
		array(
			'tag'         => 'payment_method',
			'description' => __( 'The method of payment used for this purchase', 'restropress' ),
			'function'    => 'rpress_email_tag_payment_method'
		),
		array(
			'tag'         => 'sitename',
			'description' => __( 'Your site name', 'restropress' ),
			'function'    => 'rpress_email_tag_sitename'
		),
		array(
			'tag'         => 'receipt_link',
			'description' => __( 'Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly.', 'restropress' ),
			'function'    => 'rpress_email_tag_receipt_link'
		),
		array(
			'tag'         => 'discount_codes',
			'description' => __( 'Adds a list of any discount codes applied to this purchase', 'restropress' ),
			'function'    => 'rpress_email_tag_discount_codes'
		),
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

	$payment_data  = rpress_get_payment_meta( $payment_id );
	$fooditem_list = '<ul>';
	$cart_items    = rpress_get_payment_meta_cart_details( $payment_id );
	$email         = rpress_get_payment_user_email( $payment_id );

	if ( $cart_items ) {
		$show_names = apply_filters( 'rpress_email_show_names', true );

		foreach ( $cart_items as $item ) {

			if ( rpress_use_skus() ) {
				$sku = rpress_get_fooditem_sku( $item['id'] );
			}

			$price_id = rpress_get_cart_item_price_id( $item );

			if ( $show_names ) {

				$title = get_the_title( $item['id'] );

				if ( ! empty( $sku ) ) {
					$title .= "&nbsp;&ndash;&nbsp;" . __( 'SKU', 'restropress' ) . ': ' . $sku;
				}

				if ( $price_id !== false ) {
					$title .= "&nbsp;&ndash;&nbsp;" . rpress_get_price_option_name( $item['id'], $price_id );
				}

				$fooditem_list .= '<li>' . apply_filters( 'rpress_email_receipt_fooditem_title', $title, $item, $price_id, $payment_id ) . '<br/>';
				$fooditem_list .= '<ul>';
			}

			$files = rpress_get_fooditem_files( $item['id'], $price_id );

			if ( $files ) {
				foreach ( $files as $filekey => $file ) {
					$fooditem_list .= '<li>';
					$file_url = rpress_get_fooditem_file_url( $payment_data['key'], $email, $filekey, $item['id'], $price_id );
					$fooditem_list .= '<a href="' . esc_url( $file_url ) . '">' . rpress_get_file_name( $file ) . '</a>';
					$fooditem_list .= '</li>';
				}
			}
			elseif ( rpress_is_bundled_product( $item['id'] ) ) {

				$bundled_products = rpress_get_bundled_products( $item['id'] );

				foreach ( $bundled_products as $bundle_item ) {

					$fooditem_list .= '<li class="rpress_bundled_product"><strong>' . get_the_title( $bundle_item ) . '</strong></li>';

					$files = rpress_get_fooditem_files( $bundle_item );

					foreach ( $files as $filekey => $file ) {
						$fooditem_list .= '<li>';
						$file_url = rpress_get_fooditem_file_url( $payment_data['key'], $email, $filekey, $bundle_item, $price_id );
						$fooditem_list .= '<a href="' . esc_url( $file_url ) . '">' . $file['name'] . '</a>';
						$fooditem_list .= '</li>';
					}
				}
			}

			if ( $show_names ) {
				$fooditem_list .= '</ul>';
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
 * Email template tag: file_urls
 * A plain-text list of fooditem URLs for each fooditem purchased
 *
 * @param int $payment_id
 *
 * @return string $file_urls
 */
function rpress_email_tag_file_urls( $payment_id ) {

	$payment_data = rpress_get_payment_meta( $payment_id );
	$file_urls    = '';
	$cart_items   = rpress_get_payment_meta_cart_details( $payment_id );
	$email        = rpress_get_payment_user_email( $payment_id );

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

			$bundled_products = rpress_get_bundled_products( $item['id'] );

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
	$payment_data = rpress_get_payment_meta( $payment_id );
	$email_name   = rpress_get_email_names( $payment_data['user_info'] );
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
	$payment_data = rpress_get_payment_meta( $payment_id );
	$email_name   = rpress_get_email_names( $payment_data['user_info'] );
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
	$payment_data = rpress_get_payment_meta( $payment_id );
	$email_name   = rpress_get_email_names( $payment_data['user_info'] );
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
	return rpress_get_payment_user_email( $payment_id );
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
	$payment_data = rpress_get_payment_meta( $payment_id );
	return date_i18n( get_option( 'date_format' ), strtotime( $payment_data['date'] ) );
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
	$subtotal = rpress_currency_filter( rpress_format_amount( rpress_get_payment_subtotal( $payment_id ) ) );
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
	$tax = rpress_currency_filter( rpress_format_amount( rpress_get_payment_tax( $payment_id ) ) );
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
	$price = rpress_currency_filter( rpress_format_amount( rpress_get_payment_amount( $payment_id ) ) );
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
	return rpress_get_payment_number( $payment_id );
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
	return rpress_get_payment_key( $payment_id );
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
	return rpress_get_gateway_checkout_label( rpress_get_payment_gateway( $payment_id ) );
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
	return get_bloginfo( 'name' );
}

/**
 * Email template tag: receipt_link
 * Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly
 *
 * @param $int payment_id
 *
 * @return string receipt_link
 */
function rpress_email_tag_receipt_link( $payment_id ) {
	return sprintf( __( '%1$sView it in your browser.%2$s', 'restropress' ), '<a href="' . add_query_arg( array( 'payment_key' => rpress_get_payment_key( $payment_id ), 'rpress_action' => 'view_receipt' ), home_url() ) . '">', '</a>' );
}

/**
 * Email template tag: discount_codes
 * Adds a list of any discount codes applied to this purchase
 *
 * @param $int payment_id
 * @since  1.0.0
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