<?php
/**
 * AJAX Functions
 *
 * Process the front-end AJAX actions.
 *
 * @package     RPRESS
 * @subpackage  Functions/AJAX
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Checks whether AJAX is enabled.
 *
 * This will be deprecated soon in favor of rpress_is_ajax_disabled()
 *
 * @since 1.0
 * @return bool True when RPRESS AJAX is enabled (for the cart), false otherwise.
 */
function rpress_is_ajax_enabled() {
	$retval = ! rpress_is_ajax_disabled();
	return apply_filters( 'rpress_is_ajax_enabled', $retval );
}

/**
 * Checks whether AJAX is disabled.
 *
 * @since  1.0.0
 * @since 1.0 Setting to disable AJAX was removed
 * @return bool True when RPRESS AJAX is disabled (for the cart), false otherwise.
 */
function rpress_is_ajax_disabled() {
	return apply_filters( 'rpress_is_ajax_disabled', false );
}

/**
 * Check if AJAX works as expected
 *
 * @since  1.0.0
 * @return bool True if AJAX works, false otherwise
 */
function rpress_test_ajax_works() {

	// Check if the Airplane Mode plugin is installed
	if ( class_exists( 'Airplane_Mode_Core' ) ) {

		$airplane = Airplane_Mode_Core::getInstance();

		if ( method_exists( $airplane, 'enabled' ) ) {

			if ( $airplane->enabled() ) {
				return true;
			}

		} else {

			if ( $airplane->check_status() == 'on' ) {
				return true;
			}
		}
	}

	add_filter( 'block_local_requests', '__return_false' );

	if ( get_transient( '_rpress_ajax_works' ) ) {
		return true;
	}

	$params = array(
		'sslverify'  => false,
		'timeout'    => 30,
		'body'       => array(
			'action' => 'rpress_test_ajax'
		)
	);

	$ajax  = wp_remote_post( rpress_get_ajax_url(), $params );
	$works = true;

	if ( is_wp_error( $ajax ) ) {

		$works = false;

	} else {

		if( empty( $ajax['response'] ) ) {
			$works = false;
		}

		if( empty( $ajax['response']['code'] ) || 200 !== (int) $ajax['response']['code'] ) {
			$works = false;
		}

		if( empty( $ajax['response']['message'] ) || 'OK' !== $ajax['response']['message'] ) {
			$works = false;
		}

		if( ! isset( $ajax['body'] ) || 0 !== (int) $ajax['body'] ) {
			$works = false;
		}

	}

	if ( $works ) {
		set_transient( '_rpress_ajax_works', '1', DAY_IN_SECONDS );
	}

	return $works;
}

/**
 * Get AJAX URL
 *
 * @since 1.0
 * @return string URL to the AJAX file to call during AJAX requests.
*/
function rpress_get_ajax_url() {
	$scheme = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';

	$current_url = rpress_get_current_page_url();
	$ajax_url    = admin_url( 'admin-ajax.php', $scheme );

	if ( preg_match( '/^https/', $current_url ) && ! preg_match( '/^https/', $ajax_url ) ) {
		$ajax_url = preg_replace( '/^http/', 'https', $ajax_url );
	}

	return apply_filters( 'rpress_ajax_url', $ajax_url );
}

/**
 * Removes item from cart via AJAX.
 *
 * @since 1.0
 * @return void
 */
function rpress_ajax_remove_from_cart() {
	if ( isset( $_POST['cart_item'] ) ) {

		rpress_remove_from_cart( $_POST['cart_item'] );

		$return = array(
			'removed'       => 1,
			'subtotal'      => html_entity_decode( rpress_currency_filter( rpress_format_amount( rpress_get_cart_subtotal() ) ), ENT_COMPAT, 'UTF-8' ),
			'total'         => html_entity_decode( rpress_currency_filter( rpress_format_amount( rpress_get_cart_total() ) ), ENT_COMPAT, 'UTF-8' ),
			'cart_quantity' => html_entity_decode( rpress_get_cart_quantity() ),
		);

		if ( rpress_use_taxes() ) {
			$cart_tax = (float) rpress_get_cart_tax();
			$return['tax'] = html_entity_decode( rpress_currency_filter( rpress_format_amount( $cart_tax ) ), ENT_COMPAT, 'UTF-8' );
		}

		echo json_encode( $return );

	}
	rpress_die();
}
add_action( 'wp_ajax_rpress_remove_from_cart', 'rpress_ajax_remove_from_cart' );
add_action( 'wp_ajax_nopriv_rpress_remove_from_cart', 'rpress_ajax_remove_from_cart' );

/**
 * Adds item to the cart via AJAX.
 *
 * @since 1.0
 * @return void
 */
function rpress_ajax_add_to_cart() {
	if ( isset( $_POST['fooditem_id'] ) && isset( $_POST['fooditem_price'] ) ) {
		$itemQty = !empty($_POST['fooditem_qty']) ? $_POST['fooditem_qty'] : 1;
		$to_add = array();

		$get_all_items = $_POST['post_data'];

		$items = '';

		$options['id'] = $_POST['fooditem_id'];
		$options['quantity'] = $itemQty;
		$options['instruction'] = !empty($_POST['special_instruction']) ? $_POST['special_instruction'] : '';

		if( is_array($get_all_items) && !empty($get_all_items) ) {
			foreach( $get_all_items as $key => $get_all_item ) {
				$item_qty = explode('|', $get_all_item['value']);


				if( is_array($item_qty) && !empty($item_qty) ) {

					$addon_item_like = isset($item_qty[3]) ? $item_qty[3] : 'checkbox';

					if( $addon_item_like == 'radio' ) {
						$addon_item_name = get_term_by('id', $item_qty[0], 'addon_category');
						$addon_item_name = $addon_item_name->name;
						$options['addon_items'][$key]['addon_item_name'] = $addon_item_name;

					}
					else {
						$options['addon_items'][$key]['addon_item_name'] = $get_all_item['name'];
					}

					$options['addon_items'][$key]['addon_id'] = isset($item_qty[0]) ? $item_qty[0] : '';
					$options['addon_items'][$key]['price'] = isset($item_qty[2]) ? $item_qty[2] : '';
					$options['addon_items'][$key]['quantity'] = isset($item_qty[1]) ? $item_qty[1] : '';
				}
			}
		}

		$key = rpress_add_to_cart( $_POST['fooditem_id'], $options );
		

		$item = array(
			'id'      => $_POST['fooditem_id'],
			'options' => $options
		);

		$item   = apply_filters( 'rpress_ajax_pre_cart_item_template', $item );

		$items .= rpress_get_cart_item_template( $key, $item, true, $data_key = '' );


		$return = array(
			'subtotal'      => html_entity_decode( rpress_currency_filter( rpress_format_amount( rpress_get_cart_subtotal() ) ), ENT_COMPAT, 'UTF-8' ),
			'total'         => html_entity_decode( rpress_currency_filter( rpress_format_amount( rpress_get_cart_total() ) ), ENT_COMPAT, 'UTF-8' ),
			'cart_item'     => $items,
			'cart_key'      => $key,
			'cart_quantity' => html_entity_decode( rpress_get_cart_quantity() )
		);

		if ( rpress_use_taxes() ) {
			$cart_tax = (float) rpress_get_cart_tax();
			$return['tax'] = html_entity_decode( rpress_currency_filter( rpress_format_amount( $cart_tax ) ), ENT_COMPAT, 'UTF-8' );
		}

		if( apply_delivery_fee() ) :
			$return['subtotal'] = rpress_currency_filter( rpress_format_amount( rpress_get_cart_subtotal() ) );
			$return['delivery_fee'] = rpress_get_delivery_price();
		endif;

		echo json_encode( $return );
	}
	rpress_die();
}
add_action( 'wp_ajax_rpress_add_to_cart', 'rpress_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_rpress_add_to_cart', 'rpress_ajax_add_to_cart' );


function get_delivery_steps( $fooditem_id ) {
	ob_start();
	rpress_get_template_part( 'rpress', 'delivery-steps' );
	$data = ob_get_clean();
	$data = str_replace( '{FoodID}', $fooditem_id, $data );
	return $data;
}


function rpress_show_delivery_options() {
	//Get store status
	$food_item_id = isset($_POST['fooditem_id']) ? $_POST['fooditem_id'] : '';
	$get_addons = get_delivery_steps( $food_item_id );

	$response = array(
		'html' => $get_addons,
		'html_title' => apply_filters('rpress_delivery_options_title', __('Your Order Settings', 'restropress') ),
	);

	wp_send_json_success($response);
	rpress_die();
}
add_action( 'wp_ajax_rpress_show_delivery_options', 'rpress_show_delivery_options' );
add_action( 'wp_ajax_nopriv_rpress_show_delivery_options', 'rpress_show_delivery_options' );

/**
 * Gets lists of products in the popup
 *
 * @since  1.0.0
 * @param void
 * @return html
*/
function rpress_show_products() {
	$food_item_id = isset( $_POST['fooditem_id'] ) ? $_POST['fooditem_id'] : '';
	$price = isset( $_POST['price'] ) ? $_POST['price'] : '';

	if( empty( $food_item_id ) )
		return;

	$food_title = get_the_title( $food_item_id );

	if( !empty( $food_item_id ) ) {
		$terms = getFooditemCategoryById( $food_item_id );
		$get_formatted_cats = getFormattedCats( $terms );
		ob_start();
		rpress_get_template_part( 'rpress', 'show-products' );

		$data = ob_get_clean();
		$data = str_replace( '{Food_Title}', $food_title, $data );
		$data = str_replace( '{Food_ID}', $food_item_id, $data );
		$data = str_replace( '{Food_Price}', $price, $data );
		$data = str_replace( '{Formatted_Cats}', $get_formatted_cats, $data );
	}

	$response = array(
		'html' => $data,
		'html_title' => apply_filters( 'rpress_modal_title' , $food_title),
	);

	wp_send_json_success($response);

	rpress_die();
}

add_action('wp_ajax_rpress_show_products', 'rpress_show_products');
add_action( 'wp_ajax_nopriv_rpress_show_products', 'rpress_show_products');


/**
 * Updates cart items through ajax
 *
 * @since  1.0.0
 * @param void
 * @return json_object | cart items
*/
function rpress_ajax_update_cart_items() {
	if( isset( $_POST['fooditem_cartkey'] ) ) {
		$cart_key = ( $_POST['fooditem_cartkey'] !== '' ) ? intval( $_POST['fooditem_cartkey'] ) : '';
		$item_qty = !empty( $_POST['fooditem_Qty'] ) ? $_POST['fooditem_Qty'] : 1;

		rpress_remove_from_cart( $cart_key );

		$options['id'] = $_POST['fooditem_id'];
		$options['quantity'] = $item_qty;
		$options['instruction'] = !empty($_POST['special_instruction']) ? $_POST['special_instruction'] : '';
		$get_all_items = $_POST['post_data'];

		if( is_array($get_all_items) ) {
			foreach( $get_all_items as $key => $get_all_item ) {
				if( $get_all_item['name'] !== 'quantity' ) {
					$item_qty = explode('|', $get_all_item['value']);

					if( is_array($item_qty) && !empty($item_qty) ) {

						$addon_item_like = isset($item_qty[3]) ? $item_qty[3] : 'checkbox';

						if( $addon_item_like == 'radio' ) {
							$addon_item_name = get_term_by('id', $item_qty[0], 'addon_category');
							$addon_item_name = $addon_item_name->name;
							$options['addon_items'][$key]['addon_item_name'] = $addon_item_name;
						}
						else {
							$options['addon_items'][$key]['addon_item_name'] = $get_all_item['name'];
						}

						$options['addon_items'][$key]['addon_id'] = isset($item_qty[0]) ? $item_qty[0] : '';
						$options['addon_items'][$key]['price'] = isset($item_qty[2]) ? $item_qty[2] : '';
						$options['addon_items'][$key]['quantity'] = isset($item_qty[1]) ? $item_qty[1] : '';
					}
				}
			}
		}

		$item_key = rpress_add_to_cart( $_POST['fooditem_id'], $options );

		$item = array(
			'id'      => $_POST['fooditem_id'],
			'options' => $options
		);

		$item   = apply_filters( 'rpress_ajax_pre_cart_item_template', $item );
		$get_cart_items = rpress_get_cart_contents();

		$items .= rpress_get_cart_item_template( $item_key, $item, true, $data_key = '' );

		$return = array(
			'subtotal'      => html_entity_decode( rpress_currency_filter( rpress_format_amount( rpress_get_cart_subtotal() ) ), ENT_COMPAT, 'UTF-8' ),
			'total'         => html_entity_decode( rpress_currency_filter( rpress_format_amount( rpress_get_cart_total() ) ), ENT_COMPAT, 'UTF-8' ),
			'cart_item'     => $items,
			'cart_key'  		=> $cart_key,
			'cart_quantity' => html_entity_decode( rpress_get_cart_quantity() )
		);

		if ( rpress_use_taxes() ) {
			$cart_tax = (float) rpress_get_cart_tax();
			$return['tax'] = html_entity_decode( rpress_currency_filter( rpress_format_amount( $cart_tax ) ), ENT_COMPAT, 'UTF-8' );
		}

		if( apply_delivery_fee() ) :
			$return['subtotal'] = rpress_currency_filter( rpress_format_amount( rpress_get_cart_subtotal() ) );
			$return['delivery_fee'] = rpress_get_delivery_price();
		endif;

		echo json_encode( $return );
	}
	rpress_die();
}

add_action( 'wp_ajax_rpress_update_cart_items', 'rpress_ajax_update_cart_items' );
add_action( 'wp_ajax_nopriv_rpress_update_cart_items', 'rpress_ajax_update_cart_items' );


/**
 * Makes the cart clear by ajax
 * Delete Cookie for delivery time, delivery method, order date, delivery price, delivery location
 *
 * @since  1.0.0
 * @param void
 * @return json
*/
function rpress_clear_cart_items() {
	rpress_empty_cart();
  
  if ( isset( $_COOKIE['deliveryTime'] ) ) :
    unset( $_COOKIE['deliveryTime'] );
    setcookie( "deliveryTime", "", time() - 300,"/" );
  endif;

  if ( isset( $_COOKIE['deliveryMethod'] ) ) :
    unset( $_COOKIE['deliveryMethod'] );
    setcookie( "deliveryMethod", "", time() - 300,"/" );
  endif;

  if ( isset( $_COOKIE['DeliveryDate'] ) ) :
    unset( $_COOKIE['DeliveryDate'] );
    setcookie( "DeliveryDate", "", time() - 300,"/" );
  endif;

  if ( isset( $_COOKIE['rpress_delivery_price'] ) ) :
    unset( $_COOKIE['rpress_delivery_price'] );
    setcookie( "rpress_delivery_price", "", time() - 300,"/" );
  endif;

  if ( isset( $_COOKIE['rpress_delivery_location'] ) ) :
    unset( $_COOKIE['rpress_delivery_location'] );
    setcookie( "rpress_delivery_location", "", time() - 300,"/" );
  endif;

  if ( isset( $_COOKIE['rpress_delivery_location_pos'] ) ) :
    unset( $_COOKIE['rpress_delivery_location_pos'] );
    setcookie( "rpress_delivery_location_pos", "", time() - 300,"/" );
  endif;

	$return['status'] = 'success';
	$return['response'] = '<li class="cart_item empty"><span class="rpress_empty_cart">'.apply_filters( 'rpress_empty_cart_message', '<span class="rpress_empty_cart">' . __( 'CHOOSE AN ITEM FROM THE MENU TO GET STARTED.', 'restropress' ) . '</span>' ).'</span></li>';
	echo json_encode( $return );
	rpress_die();
}
add_action( 'wp_ajax_rpress_clear_cart', 'rpress_clear_cart_items' );
add_action( 'wp_ajax_nopriv_rpress_clear_cart', 'rpress_clear_cart_items' );


/**
 * Edits the food items in the cart through ajax
 *
 * @since  1.0.0
 * @param void
 * @return html
*/
function rpress_ajax_edit_food_item() {
	$cart_key = ( $_POST['cartitem_id'] !== '' ) ? $_POST['cartitem_id'] : '';
	$food_item_id = !empty( $_POST['fooditem_id'] ) ? $_POST['fooditem_id'] : '';
	$fooditem_name = !empty( $_POST['fooditem_name'] ) ? $_POST['fooditem_name'] : '';
	$fooditem_price = !empty( $_POST['fooditem_price'] ) ? $_POST['fooditem_price'] : '';


	if( !empty( $food_item_id ) ) {
		$cart_contents = rpress_get_cart_contents();
		$terms = getFooditemCategoryById($food_item_id);
		$get_formatted_cats = getFormattedCats($terms, $cart_key);
		$item_qty = rpress_get_item_qty_by_key($cart_key);
		$special_instruction = rpress_get_instruction_by_key($cart_key);

		ob_start();
		rpress_get_template_part( 'rpress', 'edit-product' );

		$data = ob_get_clean();
		$data = str_replace( '{FoodName}', $fooditem_name, $data );
		$data = str_replace( '{FormattedCats}', $get_formatted_cats, $data );
		$data = str_replace( '{ItemQty}', $item_qty, $data );
		$data = str_replace( '{CartKey}', $cart_key, $data );
		$data = str_replace( '{FoodItemId}', $food_item_id, $data );
		$data = str_replace( '{FoodItemPrice}', $fooditem_price, $data );
		$data = str_replace( '{SpecialInstruction}', $special_instruction, $data );
	}

	$response = array(
		'html' 			=> $data,
		'title_html' => $fooditem_name
	);

	wp_send_json_success($response);
	rpress_die();
}

add_action( 'wp_ajax_rpress_edit_food_item', 'rpress_ajax_edit_food_item' );
add_action( 'wp_ajax_nopriv_rpress_edit_food_item', 'rpress_ajax_edit_food_item' );

function getFormattedCats( $terms, $cart_key = '' ) {
	if( $terms ) {
		$parent_ids = array();
		$child_ids = array();

    foreach( $terms as $term ) {
    	if( $term->parent == 0 ) {
    		$parent_id = $term->term_id;
    		array_push( $parent_ids, $parent_id );
    	}
    	else {
    		$child_id = $term->term_id;;
    		array_push( $child_ids, $child_id );
    	}
    }
  }

  $html = '';

  $cart_items = array();
  if( $cart_key !== '' ) {
  	$cart_contents = rpress_get_cart_contents();
  	$cart_contents = $cart_contents[$cart_key];
  	if( is_array($cart_contents) && !empty($cart_contents) ) {
  		foreach( $cart_contents as $cart_content ) {
  			foreach( $cart_content as $key => $val ) {
  				array_push($cart_items, $val['addon_item_name'] );
  			}
  		}
  	}
  }


  if( is_array( $parent_ids ) && !empty( $parent_ids ) ) {
  	foreach( $parent_ids as $parent_id ) {
    	$term_data = get_term_by( 'id', $parent_id, 'addon_category' );
    	$parent_addon_name = $term_data->name;
    	$parent_addon_slug = $term_data->slug;

    	$html .= '<h6 class="rpress-addon-category">'.$parent_addon_name.'</h6>';

    	$children = get_term_children( $term_data->term_id, 'addon_category' );

			$parent_meta = get_option( "taxonomy_term_$parent_id" );
			$use_addon_like = !empty( $parent_meta['use_it_like'] ) ? $parent_meta['use_it_like'] : 'checkbox';

    	if( is_array( $children ) && !empty( $children ) ) {
    		foreach( $children as $children_data ) {
    			if( in_array( $children_data, $child_ids ) ) {
    				$term_data = get_term_by( 'id', $children_data, 'addon_category' );
    				$t_id = $children_data;
    				$term_meta = get_option( "taxonomy_term_$t_id" );
    				$term_price = !empty( $term_meta['price'] ) ? $term_meta['price'] : '';

    				$html .= '<div class="food-item-list">';

    				$name = ( $use_addon_like == 'radio' ) ? $parent_addon_name : $term_data->name;

    				$class = ( $use_addon_like == 'radio' ) ? 'radio-container' : 'checkbox-container';

    				$html .= '<label for="'.$term_data->slug.'" class="'.$class.'">';

    				if( is_array($cart_items) ) {

    					if( in_array( $term_data->name, $cart_items ) ) {
    						$html .= '<input data-type="'.$use_addon_like.'" type='.$use_addon_like.' id="cbtest" checked name="'.$name.'" value="'.$term_data->term_id.'|1|'.$term_price.'|'.$use_addon_like.'" id="'.$term_data->slug.'"><span>'.$term_data->name.'</span>';
    					}
    					else {
    						$html .= '<input type='.$use_addon_like.' data-type="'.$use_addon_like.'" name="'.$name.'" value="'.$term_data->term_id.'|1|'.$term_price.'|'.$use_addon_like.'" id="'.$term_data->slug.'"><span>'.$term_data->name.'</span>';
    					}
    				}

    				$html .= '</label>';

    				$html .= '<span class="cat_price">'.rpress_currency_filter( rpress_format_amount( $term_price ) ).'</span>';
    				$html .= '</div>';
    			}
    		}
    	}
    }
  }
  return $html;
}


/**
 * Gets food items by category id
 *
 * @since  	1.0.0
 * @param 	int
 * @return 	array | food items array
*/
function getFooditemCategoryById($post_id) {
	if( !empty( $post_id ) ) {
		$food_terms = wp_get_post_terms( $post_id, 'addon_category' );
		return $food_terms;
	}
}


/**
 * Gets the cart's subtotal via AJAX.
 *
 * @since 1.0
 * @return void
 */
function rpress_ajax_get_subtotal() {
	echo rpress_currency_filter( rpress_get_cart_subtotal() );
	rpress_die();
}

add_action( 'wp_ajax_rpress_get_subtotal', 'rpress_ajax_get_subtotal' );
add_action( 'wp_ajax_nopriv_rpress_get_subtotal', 'rpress_ajax_get_subtotal' );

/**
 * Validates the supplied discount sent via AJAX.
 *
 * @since 1.0
 * @return void
 */
function rpress_ajax_apply_discount() {
	if ( isset( $_POST['code'] ) ) {

		$discount_code = sanitize_text_field( $_POST['code'] );

		$return = array(
			'msg'  => '',
			'code' => $discount_code
		);

		$user = '';

		if ( is_user_logged_in() ) {
			$user = get_current_user_id();
		} else {
			parse_str( $_POST['form'], $form );
			if ( ! empty( $form['rpress_email'] ) ) {
				$user = urldecode( $form['rpress_email'] );
			}
		}

		if ( rpress_is_discount_valid( $discount_code, $user ) ) {
			$discount  = rpress_get_discount_by_code( $discount_code );
			$amount    = rpress_format_discount_rate( rpress_get_discount_type( $discount->ID ), rpress_get_discount_amount( $discount->ID ) );
			$discounts = rpress_set_cart_discount( $discount_code );
			$total     = rpress_get_cart_total( $discounts );
			$return = array(
				'msg'         => 'valid',
				'amount'      => $amount,
				'total_plain' => $total,
				'total'       => html_entity_decode( rpress_currency_filter( rpress_format_amount( $total ) ), ENT_COMPAT, 'UTF-8' ),
				'code'        => $discount_code,
				'html'        => rpress_get_cart_discounts_html( $discounts )
			);
		} else {
			$errors = rpress_get_errors();
			$return['msg']  = $errors['rpress-discount-error'];
			rpress_unset_error( 'rpress-discount-error' );
		}

		// Allow for custom discount code handling
		$return = apply_filters( 'rpress_ajax_discount_response', $return );

		echo json_encode($return);
	}
	rpress_die();
}
add_action( 'wp_ajax_rpress_apply_discount', 'rpress_ajax_apply_discount' );
add_action( 'wp_ajax_nopriv_rpress_apply_discount', 'rpress_ajax_apply_discount' );

/**
 * Validates the supplied discount sent via AJAX.
 *
 * @since 1.0
 * @return void
 */
function rpress_ajax_update_cart_item_quantity() {
	if ( ! empty( $_POST['quantity'] ) && ! empty( $_POST['fooditem_id'] ) ) {

		$fooditem_id = absint( $_POST['fooditem_id'] );
		$quantity    = absint( $_POST['quantity'] );
		$options     = json_decode( stripslashes( $_POST['options'] ), true );

		RPRESS()->cart->set_item_quantity( $fooditem_id, $quantity, $options );

		$return = array(
			'fooditem_id' => $fooditem_id,
			'quantity'    => RPRESS()->cart->get_item_quantity( $fooditem_id, $options, $quantity ),
			'subtotal'    => html_entity_decode( rpress_currency_filter( rpress_format_amount( RPRESS()->cart->get_subtotal() ) ), ENT_COMPAT, 'UTF-8' ),
			'taxes'       => html_entity_decode( rpress_currency_filter( rpress_format_amount( RPRESS()->cart->get_tax() ) ), ENT_COMPAT, 'UTF-8' ),
			'total'       => html_entity_decode( rpress_currency_filter( rpress_format_amount( RPRESS()->cart->get_total() ) ), ENT_COMPAT, 'UTF-8' )
		);

		// Allow for custom cart item quantity handling
		$return = apply_filters( 'rpress_ajax_cart_item_quantity_response', $return );

		echo json_encode($return);
	}
	rpress_die();
}
add_action( 'wp_ajax_rpress_update_quantity', 'rpress_ajax_update_cart_item_quantity' );
add_action( 'wp_ajax_nopriv_rpress_update_quantity', 'rpress_ajax_update_cart_item_quantity' );

/**
 * Removes a discount code from the cart via ajax
 *
 * @since  1.0.0
 * @return void
 */
function rpress_ajax_remove_discount() {
	if ( isset( $_POST['code'] ) ) {

		rpress_unset_cart_discount( urldecode( $_POST['code'] ) );

		$total = rpress_get_cart_total();

		$return = array(
			'total'     => html_entity_decode( rpress_currency_filter( rpress_format_amount( $total ) ), ENT_COMPAT, 'UTF-8' ),
			'code'      => $_POST['code'],
			'discounts' => rpress_get_cart_discounts(),
			'html'      => rpress_get_cart_discounts_html()
		);

		echo json_encode( $return );
	}
	rpress_die();
}
add_action( 'wp_ajax_rpress_remove_discount', 'rpress_ajax_remove_discount' );
add_action( 'wp_ajax_nopriv_rpress_remove_discount', 'rpress_ajax_remove_discount' );

/**
 * Loads Checkout Login Fields the via AJAX
 *
 * @since 1.0
 * @return void
 */
function rpress_load_checkout_login_fields() {
	do_action( 'rpress_purchase_form_login_fields' );
	rpress_die();
}
add_action('wp_ajax_nopriv_checkout_login', 'rpress_load_checkout_login_fields');

/**
 * Load Checkout Register Fields via AJAX
 *
 * @since 1.0
 * @return void
*/
function rpress_load_checkout_register_fields() {
	do_action( 'rpress_purchase_form_register_fields' );
	rpress_die();
}
add_action('wp_ajax_nopriv_checkout_register', 'rpress_load_checkout_register_fields');

/**
 * Get Download Title via AJAX
 *
 * @since 1.0
 * @since 1.0.0 Restrict to just the fooditem post type
 * @return void
 */
function rpress_ajax_get_fooditem_title() {
	if ( isset( $_POST['fooditem_id'] ) ) {
		$post_id   = absint( $_POST['fooditem_id'] );
		$post_type = get_post_type( $post_id );
		$title     = 'fail';
		if ( 'fooditem' === $post_type ) {
			$post_title = get_the_title( $_POST['fooditem_id'] );
			if ( $post_title ) {
				echo $title = $post_title;
			}
		}

		echo $title;
	}
	rpress_die();
}
add_action( 'wp_ajax_rpress_get_fooditem_title', 'rpress_ajax_get_fooditem_title' );
add_action( 'wp_ajax_nopriv_rpress_get_fooditem_title', 'rpress_ajax_get_fooditem_title' );

/**
 * Recalculate cart taxes
 *
 * @since  1.0.0
 * @return void
 */
function rpress_ajax_recalculate_taxes() {
	if ( ! rpress_get_cart_contents() ) {
		return false;
	}

	if ( empty( $_POST['billing_country'] ) ) {
		$_POST['billing_country'] = rpress_get_shop_country();
	}

	ob_start();
	rpress_checkout_cart();
	$cart     = ob_get_clean();
	$response = array(
		'html'         => $cart,
		'tax_raw'      => rpress_get_cart_tax(),
		'tax'          => html_entity_decode( rpress_cart_tax( false ), ENT_COMPAT, 'UTF-8' ),
		'tax_rate_raw' => rpress_get_tax_rate(),
		'tax_rate'     => html_entity_decode( rpress_get_formatted_tax_rate(), ENT_COMPAT, 'UTF-8' ),
		'total'        => html_entity_decode( rpress_cart_total( false ), ENT_COMPAT, 'UTF-8' ),
		'total_raw'    => rpress_get_cart_total(),
	);

	echo json_encode( $response );

	rpress_die();
}
add_action( 'wp_ajax_rpress_recalculate_taxes', 'rpress_ajax_recalculate_taxes' );
add_action( 'wp_ajax_nopriv_rpress_recalculate_taxes', 'rpress_ajax_recalculate_taxes' );

/**
 * Retrieve a states drop down
 *
 * @since  1.0.0
 * @return void
 */
function rpress_ajax_get_states_field() {
	if( empty( $_POST['country'] ) ) {
		$_POST['country'] = rpress_get_shop_country();
	}
	$states = rpress_get_shop_states( $_POST['country'] );

	if( ! empty( $states ) ) {

		$args = array(
			'name'    => $_POST['field_name'],
			'id'      => $_POST['field_name'],
			'class'   => $_POST['field_name'] . '  rpress-select',
			'options' => $states,
			'show_option_all'  => false,
			'show_option_none' => false
		);

		$response = RPRESS()->html->select( $args );

	} else {

		$response = 'nostates';
	}

	echo $response;

	rpress_die();
}
add_action( 'wp_ajax_rpress_get_shop_states', 'rpress_ajax_get_states_field' );
add_action( 'wp_ajax_nopriv_rpress_get_shop_states', 'rpress_ajax_get_states_field' );

/**
 * Retrieve a states drop down
 *
 * @since  1.0.0
 * @return void
 */
function rpress_ajax_fooditem_search() {
	global $wpdb;

	$search   = esc_sql( sanitize_text_field( $_GET['s'] ) );
	$excludes = ( isset( $_GET['current_id'] ) ? (array) $_GET['current_id'] : array() );

	$no_bundles = isset( $_GET['no_bundles'] ) ? filter_var( $_GET['no_bundles'], FILTER_VALIDATE_BOOLEAN ) : false;
	if( true === $no_bundles ) {
		$bundles  = $wpdb->get_results( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_rpress_product_type' AND meta_value = 'bundle';", ARRAY_A );
		$bundles  = wp_list_pluck( $bundles, 'post_id' );
		$excludes = array_merge( $excludes, $bundles );
	}

	$variations = isset( $_GET['variations'] ) ? filter_var( $_GET['variations'], FILTER_VALIDATE_BOOLEAN ) : false;

	$excludes = array_unique( array_map( 'absint', $excludes ) );
	$exclude  = implode( ",", $excludes );

	$results = array();

	// Setup the SELECT statement
	$select = "SELECT ID,post_title FROM $wpdb->posts ";

	// Setup the WHERE clause
	$where = "WHERE `post_type` = 'fooditem' and `post_title` LIKE '%s' ";

	// If we have items to exclude, exclude them
	if( ! empty( $exclude ) ) {
		$where .= "AND `ID` NOT IN (" . $exclude . ") ";
	}

	if ( ! current_user_can( 'edit_products' ) ) {
		$status = apply_filters( 'rpress_product_dropdown_status_nopriv', array( 'publish' ) );
	} else {
		$status = apply_filters( 'rpress_product_dropdown_status', array( 'publish', 'draft', 'private', 'future' ) );
	}

	if ( is_array( $status ) && ! empty( $status ) ) {

		$status     = array_map( 'sanitize_text_field', $status );
		$status_in  = "'" . join( "', '", $status ) . "'";
		$where     .= "AND `post_status` IN ({$status_in}) ";

	} else {

		$where .= "AND `post_status` = `publish` ";

	}

	// Limit the result sets
	$limit = "LIMIT 50";

	$sql = $select . $where . $limit;

	$prepared_statement = $wpdb->prepare( $sql, '%' . $search . '%' );

	$items = $wpdb->get_results( $prepared_statement );

	if( $items ) {

		foreach( $items as $item ) {

			$results[] = array(
				'id'   => $item->ID,
				'name' => $item->post_title
			);

			if ( $variations && rpress_has_variable_prices( $item->ID ) ) {
				$prices = rpress_get_variable_prices( $item->ID );

				foreach ( $prices as $key => $value ) {
					$name   = ! empty( $value['name'] )   ? $value['name']   : '';
					$amount = ! empty( $value['amount'] ) ? $value['amount'] : '';
					$index  = ! empty( $value['index'] )  ? $value['index']  : $key;

					if ( $name && $index ) {
						$results[] = array(
							'id'   => $item->ID . '_' . $key,
							'name' => esc_html( $item->post_title . ': ' . $name ),
						);
					}
				}
			}
		}

	} else {

		$results[] = array(
			'id'   => 0,
			'name' => __( 'No results found', 'restropress' )
		);

	}

	echo json_encode( $results );

	rpress_die();
}
add_action( 'wp_ajax_rpress_fooditem_search', 'rpress_ajax_fooditem_search' );
add_action( 'wp_ajax_nopriv_rpress_fooditem_search', 'rpress_ajax_fooditem_search' );

/**
 * Search the customers database via AJAX
 *
 * @since  1.0.0
 * @return void
 */
function rpress_ajax_customer_search() {
	global $wpdb;

	$search  = esc_sql( sanitize_text_field( $_GET['s'] ) );
	$results = array();
	$customer_view_role = apply_filters( 'rpress_view_customers_role', 'view_shop_reports' );
	if ( ! current_user_can( $customer_view_role ) ) {
		$customers = array();
	} else {
		$select = "SELECT id, name, email FROM {$wpdb->prefix}rpress_customers ";
		if ( is_numeric( $search ) ) {
			$where = "WHERE `id` LIKE '%$search%' OR `user_id` LIKE '%$search%' ";
		} else {
			$where = "WHERE `name` LIKE '%$search%' OR `email` LIKE '%$search%' ";
		}
		$limit = "LIMIT 50";

		$customers = $wpdb->get_results( $select . $where . $limit );
	}

	if( $customers ) {

		foreach( $customers as $customer ) {

			$results[] = array(
				'id'   => $customer->id,
				'name' => $customer->name . '(' .  $customer->email . ')'
			);
		}

	} else {

		$customers[] = array(
			'id'   => 0,
			'name' => __( 'No results found', 'restropress' )
		);

	}

	echo json_encode( $results );

	rpress_die();
}
add_action( 'wp_ajax_rpress_customer_search', 'rpress_ajax_customer_search' );

/**
 * Search the users database via AJAX
 *
 * @since 1.0.0.9
 * @return void
 */
function rpress_ajax_user_search() {
	global $wpdb;

	$search         = esc_sql( sanitize_text_field( $_GET['s'] ) );
	$results        = array();
	$user_view_role = apply_filters( 'rpress_view_users_role', 'view_shop_reports' );

	if ( ! current_user_can( $user_view_role ) ) {
		$results = array();
	} else {
		$user_args = array(
			'search' => '*' . esc_attr( $search ) . '*',
			'number' => 50,
		);

		$users = get_users( $user_args );
	}

	if ( $users ) {

		foreach( $users as $user ) {

			$results[] = array(
				'id'   => $user->ID,
				'name' => $user->display_name,
			);
		}

	} else {

		$results[] = array(
			'id'   => 0,
			'name' => __( 'No users found', 'restropress' )
		);

	}

	echo json_encode( $results );

	rpress_die();
}
add_action( 'wp_ajax_rpress_user_search', 'rpress_ajax_user_search' );

/**
 * Check for Download Price Variations via AJAX (this function can only be used
 * in WordPress Admin). This function is used for the Edit Payment screen when fooditems
 * are added to the purchase. When each fooditem is chosen, an AJAX call is fired
 * to this function which will check if variable prices exist for that fooditem.
 * If they do, it will output a dropdown of all the variable prices available for
 * that fooditem.
 *
 * @author RestroPress
 * @since 1.0
 * @return void
 */
function rpress_check_for_fooditem_price_variations() {
	if( ! current_user_can( 'edit_products' ) ) {
		die( '-1' );
	}

	$fooditem_id = intval( $_POST['fooditem_id'] );
	$fooditem    = get_post( $fooditem_id );


	if( 'fooditem' != $fooditem->post_type ) {
		die( '-2' );
	}

	echo rpress_get_fooditem_price($fooditem_id);

	rpress_die();
}
add_action( 'wp_ajax_rpress_check_for_fooditem_price_variations', 'rpress_check_for_fooditem_price_variations' );


/**
 * Searches for users via ajax and returns a list of results
 *
 * @since  1.0.0
 * @return void
 */
function rpress_ajax_search_users() {

	if( current_user_can( 'manage_shop_settings' ) ) {

		$search_query = trim( $_POST['user_name'] );
		$exclude      = trim( $_POST['exclude'] );

		$get_users_args = array(
			'number' => 9999,
			'search' => $search_query . '*'
		);

		if ( ! empty( $exclude ) ) {
			$exclude_array = explode( ',', $exclude );
			$get_users_args['exclude'] = $exclude_array;
		}

		$get_users_args = apply_filters( 'rpress_search_users_args', $get_users_args );

		$found_users = apply_filters( 'rpress_ajax_found_users', get_users( $get_users_args ), $search_query );

		$user_list = '<ul>';
		if( $found_users ) {
			foreach( $found_users as $user ) {
				$user_list .= '<li><a href="#" data-userid="' . esc_attr( $user->ID ) . '" data-login="' . esc_attr( $user->user_login ) . '">' . esc_html( $user->user_login ) . '</a></li>';
			}
		} else {
			$user_list .= '<li>' . __( 'No users found', 'restropress' ) . '</li>';
		}
		$user_list .= '</ul>';

		echo json_encode( array( 'results' => $user_list ) );

	}
	die();
}
add_action( 'wp_ajax_rpress_search_users', 'rpress_ajax_search_users' );

/**
 * Update delivery options when user procedds to checkout
 *
 * @since       1.0.2
 * @param       void
 * @return      array | Session array for selected delivery system
 */
function rpress_proceed_checkout() {

  $delivery_opt = isset($_POST['deliveryOpt']) ? $_POST['deliveryOpt'] : '';

  $delivery_time = isset($_POST['deliveryTime']) ? $_POST['deliveryTime'] : '';

  //Check minimum order
  $enable_minimum_order = rpress_get_option('allow_minimum_order');

  if( $enable_minimum_order ) :
    $minimum_order_price = rpress_get_option('minimum_order_price');
    $minimum_price_error = rpress_get_option('minimum_order_error');

    $minimum_order_formatted = rpress_currency_filter( rpress_format_amount( $minimum_order_price ) );
    $minimum_price_error = str_replace('{min_order_price}', $minimum_order_formatted, $minimum_price_error);

    if( rpress_get_cart_total() < $minimum_order_price ) :
      $response = array( 'status' => 'error', 'minimum_price' => $minimum_order_price, 'minimum_price_error' =>  $minimum_price_error  );
    else :
      //Save session vars
      rpress_checkout_delivery_type( $delivery_opt, $delivery_time );
      $response = array( 'status' => 'success' );
    endif;

    else :
      //Save session vars
      rpress_checkout_delivery_type( $delivery_opt, $delivery_time );
      $response = array( 'status' => 'success' );
    endif;
    echo json_encode($response);
    exit;
}

add_action('wp_ajax_rpress_proceed_checkout', 'rpress_proceed_checkout');
add_action('wp_ajax_nopriv_rpress_proceed_checkout', 'rpress_proceed_checkout');


/**
 * Check for new orders and send notification
 *
 * @since       2.0.1
 * @param       void
 * @return      json | user notification json object
 */
function rpress_check_new_orders() {
  $last_order = get_option( 'rp_last_order_id' );
  $order      = rpress_get_payments( array( 'number' => 1 ) );
    
  if( is_array( $order ) && $order[0]->ID != $last_order ) {
    $placeholder = array( '{order_id}' => $payment_id );

    $body = strtr( rpress_get_option( 'notification_body' ) , $placeholder );

    $notification = array(
      'title' => rpress_get_option( 'notification_title' ),
      'body'  => $body,
      'icon'  => rpress_get_option( 'notification_icon' ),
      'sound' => rpress_get_option( 'notification_sound' ),
      'url'   => admin_url( 'edit.php?post_type=fooditem&page=rpress-payment-history&view=view-order-details&id=' . $order[0]->ID )
    );
    update_option( 'rp_last_order_id', $order[0]->ID  );
    wp_send_json( $notification );
  }
  echo 0;
  wp_die();
}
add_action( 'wp_ajax_rpress_check_new_orders', 'rpress_check_new_orders' );

/**
 * Get Addon items in the admin
 *
 * @since       1.0.6
 * @param       blank
 * @return      html | addon items html options
 */
function rpress_get_admin_addon_items() {
  $html = '';

  $item_id = isset($_POST['fooditem_id']) ? $_POST['fooditem_id'] : '';
  
  if( $item_id ) {
    $terms = getFooditemCategoryById($item_id);
    
    if( is_array($terms) ) {
      $parent_ids = array();
      $child_ids = array();

      foreach( $terms as $term ) {
        if( $term->parent == 0 ) {
          $parent_id = $term->term_id;
          array_push($parent_ids, $parent_id);
        }
        else {
          $child_id = $term->term_id;;
          array_push( $child_ids, $child_id );
        }
      }
    }

    if( is_array( $parent_ids ) && !empty( $parent_ids ) ) {

      $html .= '<select class="addon-items-list" name="rpress-payment-details-fooditems[0][addon_items][]">';

      foreach( $parent_ids as $parent_id ) {
        $term_data = get_term_by('id', $parent_id, 'addon_category');
        $children = get_term_children( $term_data->term_id, 'addon_category' );

        if( is_array( $children ) && !empty( $children ) ) {
          foreach( $children as $children_data ) {
            if( in_array( $children_data, $child_ids ) ) {
              $term_data = get_term_by('id', $children_data, 'addon_category');
              $t_id = $children_data;
              $term_meta = get_option( "taxonomy_term_$t_id" );
              $term_price = !empty($term_meta['price']) ? $term_meta['price'] : '';
              $html .= '<option value="'.$term_data->slug.'">'.$term_data->name.'('.rpress_currency_filter( rpress_format_amount( $term_price ) ).')</option>';
            }
          }
        }
      }
      $html .= '</select>';
    }
  echo $html;
  }
  exit;
}

add_action('wp_ajax_rpress_get_admin_addon_items', 'rpress_get_admin_addon_items');
