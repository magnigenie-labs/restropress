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
					$options['addon_items'][$key]['addon_item_name'] = $get_all_item['name'];
					$options['addon_items'][$key]['addon_id'] = $item_qty[0];
					$options['addon_items'][$key]['price'] = $item_qty[2];
					$options['addon_items'][$key]['quantity'] = $item_qty[1];
				}
			}
		}

		$key = rpress_add_to_cart( $_POST['fooditem_id'], $options, $itemQty );

		$options_price_array = array();
		if( isset($options['addon_items']) && is_array($options['addon_items']) ) {
			foreach( $options['addon_items'] as  $val ) {
				if( $val['price'] !== '' ) {
					array_push($options_price_array, $val['price']);
				}
			}
		}

		$options_price = array_sum($options_price_array);

			$item = array(
				'id'      => $_POST['fooditem_id'],
				'options' => $options
			);

			$item   = apply_filters( 'rpress_ajax_pre_cart_item_template', $item );
			//$items .= html_entity_decode( rpress_get_cart_item_template( $key, $item, true ), ENT_COMPAT, 'UTF-8' );
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

		echo json_encode( $return );
	}
	rpress_die();
}
add_action( 'wp_ajax_rpress_add_to_cart', 'rpress_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_rpress_add_to_cart', 'rpress_ajax_add_to_cart' );

/**
 * Gets lists of products in the popup through ajax
 *
 * @since  1.0.0
 * @param void
 * @return html
*/
function rpress_ajax_show_product() {

	$food_item_id = $_POST['fooditem_id'];
	$food_title = get_the_title($food_item_id);
	$price = $_POST['fooditem_price'];

	$html = '';

	if( !empty($food_item_id) ) {

		//get food category by id
		$terms = getFooditemCategoryById($food_item_id);
		$get_formatted_cats = getFormattedCats($terms);
		$html .= '<div class="fancybox-main">';
		$html .= '<div class="fancybox-first">';
		$html .= '<div class="view-food-item-wrap">';
		$html .= '<div class="row"><div class="col-md-12"><h1 class="text-center">'.$food_title.'</h1></div></div>';
		
		$html .= '<form id="fooditem-details" class="row">';
		
		$html .= $get_formatted_cats;
		$html .= '</form>';
		$html .= '<div class="col-md-12 md-4-top special-margin">';
		$html .= '<a href="#" class="special-instructions-link">'.__('Special Instructions?', 'restro-press').'</a>';
		$html .= '<textarea placeholder="'.__('Add Instructions...', 'restro-press').'" class="col-md-12 special-instructions " name="special_instruction"></textarea>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="rpress-popup-actions edit-rpress-popup-actions  col-md-12">';
		$html .= '<div class="row row-top">';
		$html .= '<div class="col-md-6 btn-count">';
		$html .= '<div class="col-md-3 col-xs-3 col-sm-2"><input type="button" value="-" class="qtyminus qtyminus-style qtyminus-style-edit" field="quantity"/></div>';
		$html .= '<div class="col-md-4 col-xs-4  col-sm-4 md-4-mar-lft"><input type="text" name="quantity" value="1" class="qty qty-style"></div>';
		$html .= '<div class="col-md-2 col-sm-2 col-xs-3 plus-symb"><input type="button" value="+" class="qtyplus col-md-3 qtyplus-style qtyplus-style-edit" field="quantity"/></div>';
		$html .='</div>';
		$html .='</div>';
		$html .= '<a data-item-qty="1" data-item-id="'.$food_item_id.'" data-item-price="'.$price.'" class="center submit-fooditem-button text-center inline col-md-6">Add To Cart</a>';
		$html .= '</div>';
		$html .= '</div>';
	}
	
	echo json_encode( $html );
	rpress_die();
}
add_action( 'wp_ajax_rpress_show_product', 'rpress_ajax_show_product' );
add_action( 'wp_ajax_nopriv_rpress_show_product', 'rpress_ajax_show_product' );

/**
 * Updates cart items through ajax
 *
 * @since  1.0.0
 * @param void
 * @return json_object | cart items
*/
function rpress_ajax_update_cart_items() {
	if( isset($_POST['fooditem_cartkey']) ) {
		$cart_key = ($_POST['fooditem_cartkey'] !== '') ? $_POST['fooditem_cartkey'] : '';
		$cart_key = intval($cart_key);
		$item_qty = !empty($_POST['fooditem_Qty']) ? $_POST['fooditem_Qty'] : 1;
		
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
						$options['addon_items'][$key]['addon_item_name'] = $get_all_item['name'];
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

		echo json_encode( $return );
	}
	rpress_die();
}

add_action( 'wp_ajax_rpress_update_cart_items', 'rpress_ajax_update_cart_items' );
add_action( 'wp_ajax_nopriv_rpress_update_cart_items', 'rpress_ajax_update_cart_items' );


/**
 * Makes the cart clear by ajax
 *
 * @since  1.0.0
 * @param void
 * @return json
*/
function rpress_clear_cart_items() {
	rpress_empty_cart();
	$return['status'] = 'success';
	$return['response'] = '<li class="cart_item empty"><span class="rpress_empty_cart">'.apply_filters( 'rpress_empty_cart_message', '<span class="rpress_empty_cart">' . __( 'Your cart is empty.', 'restro-press' ) . '</span>' ).'</span></li>';
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
	$cart_key = ($_POST['cartitem_id'] !== '') ? $_POST['cartitem_id'] : '';
	$food_item_id = !empty($_POST['fooditem_id']) ? $_POST['fooditem_id'] : '';
	$fooditem_name = !empty($_POST['fooditem_name']) ? $_POST['fooditem_name'] : '';
	$fooditem_price = !empty($_POST['fooditem_price']) ? $_POST['fooditem_price'] : '';


	if( !empty($food_item_id) ) {
		$cart_contents = rpress_get_cart_contents();
		$terms = getFooditemCategoryById($food_item_id);
		$get_formatted_cats = getFormattedCats($terms, $cart_key);
		$item_qty = rpress_get_item_qty_by_key($cart_key);
		$special_instruction = rpress_get_instruction_by_key($cart_key);

		$html .= '<div class="fancybox-content pointer">';
		$html .= '<div class="view-food-item-wrap">';
		$html .= '<div class="row"><div class="col-md-12"><h1 class="text-center">'.$fooditem_name.'</h1></div></div>';

		$html .= '<form id="fooditem-update-details" class="row">';
		$html .= $get_formatted_cats;
		$html .= '</form>';
		$html .= '<div class="col-md-12 md-12-top special-inst">';
		$html .= '<a href="#" class="special-instructions-link">'.__('Special Instructions?', 'restro-press').'</a>';
		$class = !empty($special_instruction) ? '' : 'hide';
		
		$html .= '<textarea placeholder="Add Instructions..." class="col-md-12 special-instructions '.$class.' " name="special_instruction">'.$special_instruction.'</textarea>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="row row-top update-bottom">';
		$html .= '<div class="col-md-6 qty-button">';
		$html .= '<div class="col-md-3 col-xs-3 col-sm-3 left"><input type="button" value="-" class="qtyminus" field="quantity" style="font-weight: bold;" /></div>';
		$html .= '<div class="col-md-4 col-xs-4 col-sm-3 cent"><input type="text" name="quantity" value="'.$item_qty.'" class="qty" style="margin-bottom: 0px !important"/></div>';
		$html .= '<div class="col-md-4 col-xs-3 col-sm-3 right"><input type="button" value="+" class="qtyplus col-md-3 qty_plus_font" field="quantity" style="font-weight: bold;" /></div>';
		$html .='</div>';
		
		$html .= '<div class="rpress-popup-actions  edit-pop-up-custom-button">';
	

		$html .= '<a data-item-qty="'.$item_qty.'" data-cart-key="'.$cart_key.'" data-item-id="'.$food_item_id.'" data-item-price="'.$fooditem_price.'" class="center update-fooditem-button inline">Update Cart</a>';
		$html .= '</div>';
		$html .='</div>';
		$html .= '</div>';
		

	}
	echo json_encode( $html );
	rpress_die();
}

add_action( 'wp_ajax_rpress_edit_food_item', 'rpress_ajax_edit_food_item' );
add_action( 'wp_ajax_nopriv_rpress_edit_food_item', 'rpress_ajax_edit_food_item' );

function getFormattedCats($terms, $cart_key = '') {
	if($terms) {
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


  if( is_array( $parent_ids ) && !empty($parent_ids) ) {
  	foreach( $parent_ids as $parent_id ) {
    	$term_data = get_term_by('id', $parent_id, 'addon_category');
    	$html .= '<h6 class="rpress-addon-category">'.$term_data->name.'</h6>';

    	$children = get_term_children( $term_data->term_id, 'addon_category' );

    	
    	if( is_array($children) && !empty($children) ) {
    		foreach( $children as $children_data ) {
    			if( in_array($children_data, $child_ids) ) {
    				$term_data = get_term_by('id', $children_data, 'addon_category');
    				$t_id = $children_data;
    				$term_meta = get_option( "taxonomy_term_$t_id" );
    				$term_price = !empty($term_meta['price']) ? $term_meta['price'] : '';
    				$term_quantity = !empty($term_meta['enable_quantity']) ? $term_meta['enable_quantity'] : '';

    				$html .= '<div class="food-item-list">';
    				$html .= '<label for="'.$term_data->slug.'">';
    				

    				if( is_array($cart_items) ) {
    					if( in_array($term_data->name, $cart_items) ) {
    						$html .= '<input type="checkbox" id="cbtest" checked name="'.$term_data->name.'" value="'.$term_data->term_id.'|1|'.$term_price.'" id="'.$term_data->slug.'"><span>'.$term_data->name.'</span>';
    					}
    					else {
    						$html .= '<input type="checkbox" name="'.$term_data->name.'" value="'.$term_data->term_id.'|1|'.$term_price.'" id="'.$term_data->slug.'"><span>'.$term_data->name.'</span>';
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
	if( !empty($post_id) ) {
		$taxonomy = 'addon_category';
		$food_terms = wp_get_post_terms( $post_id, $taxonomy);
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
			'name' => __( 'No results found', 'restro-press' )
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
			'name' => __( 'No results found', 'restro-press' )
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
			'name' => __( 'No users found', 'restro-press' )
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
			$user_list .= '<li>' . __( 'No users found', 'restro-press' ) . '</li>';
		}
		$user_list .= '</ul>';

		echo json_encode( array( 'results' => $user_list ) );

	}
	die();
}
add_action( 'wp_ajax_rpress_search_users', 'rpress_ajax_search_users' );
