<?php
/**
 * Custom Functions
 *
 * @package     RPRESS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Create Addon Item taxonomy.
 *
 * @since       1.0
 * @param       null
 * @return      void
 */
function rpress_set_custom_taxonomies() {

	$addon_item_label = array(
		'name'              => _x( 'Addon Item', 'taxonomy general name', 'restro-press' ),
		'singular_name'     => _x( 'Addon item', 'taxonomy singular name', 'restro-press' ),
		'search_items'      => __( 'Search Addon Item', 'restro-press' ),
		'all_items'         => __( 'All Addon Item', 'restro-press' ),
		'parent_item'       => __( 'Parent Addon Item', 'textdomain' ),
		'parent_item_colon' => __( 'Parent Addon Item:', 'textdomain' ),
		'edit_item'         => __( 'Edit Addon Item', 'restro-press' ),
		'update_item'       => __( 'Update Addon item', 'restro-press' ),
		'add_new_item'      => __( 'Add New Addon Item', 'restro-press' ),
		'new_item_name'     => __( 'New Addon Item', 'restro-press' ),
		'menu_name'         => __( 'Addon Item', 'restro-press' ),
	);


	$food_category_label = array(
		'name'              => _x( 'Food Category', 'taxonomy general name', 'restro-press' ),
		'singular_name'     => _x( 'Food Category', 'taxonomy singular name', 'restro-press' ),
		'search_items'      => __( 'Search Food Category', 'restro-press' ),
		'all_items'         => __( 'All Food Category', 'restro-press' ),
		'parent_item'       => __( 'Parent Food Category', 'textdomain' ),
		'parent_item_colon' => __( 'Parent Food Category:', 'textdomain' ),
		'edit_item'         => __( 'Edit Food Category', 'restro-press' ),
		'update_item'       => __( 'Update Food Category', 'restro-press' ),
		'add_new_item'      => __( 'Add New Food Category', 'restro-press' ),
		'new_item_name'     => __( 'New Food Category', 'restro-press' ),
		'menu_name'         => __( 'Food Category', 'restro-press' ),
	);

	$food_item_args = array(
		'hierarchical'      => true,
		'labels'            => $food_category_label,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'food-category' ),
	);

	register_taxonomy( 'food-category', array( 'fooditem' ), $food_item_args );

  //Register taxonomy for food category
	register_taxonomy_for_object_type( 'food-category', 'fooditem' );

}
add_action( 'init', 'rpress_set_custom_taxonomies' );

function rpress_enque_scripts() {

	//Add fancybox style
	wp_enqueue_style( 'rpress-fancybox-stylesheet', plugins_url( 'assets/css/jquery.fancybox.css', RPRESS_PLUGIN_FILE ));

	//Add fancybox script
	wp_enqueue_script( 'rpress-fancybox', plugins_url( 'assets/js/jquery.fancybox.js', RPRESS_PLUGIN_FILE ) , array( 'jquery' ), '1.0.1', true );

	//Add Sticky bar
	wp_enqueue_script('rpress-sticky-sidebar', plugins_url( 'assets/js/rpress-sticky-sidebar.js', RPRESS_PLUGIN_FILE ), array( 'jquery' ), '1.0.1', true );

	wp_enqueue_script('rpress-google-js', 'https://maps.googleapis.com/maps/api/js?&key='.rpress_get_option('map_api_key').'&libraries=places', array(), '', true);

	//Add Google Map js 
	if( rpress_get_option('enable_google_map_api') 
		&& rpress_is_checkout() && rpress_get_option('map_api_key') !== '' ) :

		wp_enqueue_script('rpress-google-js', 'https://maps.googleapis.com/maps/api/js?&key='.rpress_get_option('map_api_key').'&libraries=places', array(), '', true);
	endif;

	wp_enqueue_style( 'rpress-datepicker-stylesheet', plugins_url( 'assets/css/rpress-datepicker.css', RPRESS_PLUGIN_FILE ));

	wp_enqueue_script('rpress-datepicker', plugins_url( 'assets/js/rpress-datepicker.js', RPRESS_PLUGIN_FILE ), array( 'jquery' ), '1.0.1', true );

	//Add custom js script
	wp_enqueue_script('rpress-custom', plugins_url( 'assets/js/rpress-custom.js', RPRESS_PLUGIN_FILE ), array( 'jquery', 'rpress-sticky-sidebar', 'rpress-datepicker' ), '1.0.1', true );

	// Add custom css
	wp_enqueue_style( 'rpress-custom-stylesheet', plugins_url( 'assets/css/rpress-custom.css', RPRESS_PLUGIN_FILE ));

	// Timepicker css
  wp_register_style( 'rpress-timepicker', plugins_url( 'assets/css/jquery.timepicker.css', RPRESS_PLUGIN_FILE ));
  wp_enqueue_style( 'rpress-timepicker' );

  // Timepicker js
  wp_register_script( 'rpress-timepicker-script', plugins_url( 'assets/js/jquery.timepicker.js', RPRESS_PLUGIN_FILE ), '1.0.1', true);
  wp_enqueue_script( 'rpress-timepicker-script' );

  $fooditem_popup_enable = rpress_get_option( 'enable_food_image_popup', false );

  wp_localize_script( 'rpress-custom', 'RpressVars', array(
  	'wait_text' 		=> __( 'Please Wait', 'restro-press' ),
  	'add_to_cart' 		=> __( 'Add To Cart', 'restro-press' ),
  	'added_into_cart' 	=> __( 'Added Into Cart', 'restro-press' ),
  	'estimated_tax'		=> __( 'Estimated Tax', 'restro-press'),
  	'total_text'		=> __( 'Subtotal', 'restro-press'),
  	'google_api'			=> rpress_get_option('map_api_key'),
  	'enable_google_autocomplete' => rpress_get_option('enable_google_map_api'),
  	'is_checkout_page' => rpress_is_checkout(),
  	'store_closed'		=> __('Store is closed', 'restro-press'),
  	'delivery_closed' => __('Delivery is closed', 'restro-press'),
  	'enable_fooditem_popup' => $fooditem_popup_enable,
  ));
}
add_action( 'wp_enqueue_scripts',  'rpress_enque_scripts' );

add_action( 'admin_enqueue_scripts', 'rpress_admin_scripts' );

function rpress_admin_scripts() {
  wp_register_style( 'rpress-timepicker', plugins_url( 'assets/css/jquery.timepicker.css', RPRESS_PLUGIN_FILE ));
  wp_enqueue_style( 'rpress-timepicker' );

  wp_register_script( 'rpress-timepicker-script', plugins_url( 'assets/js/jquery.timepicker.js', RPRESS_PLUGIN_FILE ), '1.0.1', true);
  wp_enqueue_script( 'rpress-timepicker-script' );
}

function rpress_prefix_enqueue() { 
	if( rpress_get_option('allow_using_style') == 1 ) {
		// js
		wp_register_script('prefix_bootstrap', plugins_url( 'assets/js/rpress-bootstrap.js', RPRESS_PLUGIN_FILE ), '1.0.1', true);
  	wp_enqueue_script('prefix_bootstrap');

  	// css
  	wp_register_style('prefix_bootstrap', plugins_url( 'assets/css/rpress-bootstrap.css', RPRESS_PLUGIN_FILE ));
  	wp_enqueue_style('prefix_bootstrap');
	}
}


add_action( 'wp_enqueue_scripts',  'rpress_prefix_enqueue' );

add_action( 'admin_enqueue_scripts', 'load_admin_scripts' );

function load_admin_scripts() {
	//Add admin custom js script
	wp_enqueue_script('admin-rpress-script', plugins_url( 'assets/js/admin-custom.js', RPRESS_PLUGIN_FILE ), array( 'jquery' ), '1.0.1', true );
}

function addon_category_taxonomy_custom_fields($tag) {
	// Check for existing taxonomy meta for the term you're editing  
    $t_id = $tag->term_id; // Get the ID of the term you're editing  
    $term_meta = get_option( "taxonomy_term_$t_id" ); // Do the check  
?>  
  
<tr class="form-field">  
	<th scope="row" valign="top">  
  	<label for="price_id"><?php _e('Price'); ?></label>  
  </th>  
  <td>  
  	<input type="number" step=".01" name="term_meta[price]" id="term_meta[price]" size="25" style="width:15%;" value="<?php echo $term_meta['price'] ? $term_meta['price'] : ''; ?>"><br />  
    <span class="description"><?php _e('Price for this addon item'); ?></span>  
  </td>  
</tr>  

<tr class="form-field">  
	<th scope="row" valign="top">  
  	<label for="enable_quantity"><?php _e('Enable Quantity'); ?></label>  
  </th>  
  <td>
  	<input type="hidden" value="0" name="term_meta[enable_quantity]">
  	<input type="checkbox" <?php echo (!empty($term_meta['enable_quantity']) ? ' checked="checked" ' : ''); ?> value="1" name="term_meta[enable_quantity]" />
  	<br />  
    <span class="description"><?php _e('Show quantity for this?'); ?></span>  
  </td>  
</tr> 
<?php
}

/**
 * Update taxonomy meta data
 *
 * @since       1.0
 * @param       string | term_id
 * @return      update meta data
 */
function save_addon_category_custom_fields( $term_id ) {
	if( isset( $_POST['term_meta'] ) ) {  
  	$t_id = $term_id;  
    $term_meta = get_option( "taxonomy_term_$t_id" );  
    $cat_keys = array_keys( $_POST['term_meta'] );  
    foreach ( $cat_keys as $key ){  
    	if( isset( $_POST['term_meta'][$key] ) ){  
      	$term_meta[$key] = $_POST['term_meta'][$key];  
      }  
    }
    //save the option array  
    update_option( "taxonomy_term_$t_id", $term_meta );  
  }  
}

// Add the fields to the "addon_category" taxonomy, using our callback function  
add_action( 'addon_category_edit_form_fields', 'addon_category_taxonomy_custom_fields', 10, 2 ); 

// Save the changes made on the "addon_category" taxonomy, using our callback function  
add_action( 'edited_addon_category', 'save_addon_category_custom_fields', 10, 2 );

/**
 * Get Cart Items By Key
 *
 * @since       1.0
 * @param       int | key
 * @return      array | cart items array
 */
function getCartItemsByKey($key) {
	$cart_items_arr = array();
	if( $key !== '' ) {
		$cart_items = rpress_get_cart_contents();
		if( is_array( $cart_items ) && !empty($cart_items) ) {
			$items_in_cart = $cart_items[$key];
			if( is_array($items_in_cart) ) {
				if( isset($items_in_cart['addon_items']) ) {
					$cart_items_arr = $items_in_cart['addon_items'];
				}
			}
		}
	}
	return $cart_items_arr;
}
/**
 * Get Cart Items Price 
 *
 * @since       1.0
 * @param       int | key
 * @return      int | total price for cart
 */
function getCartItemsByPrice($key) {
	$cart_items_price = array();
	if( $key !== '' ) {
		$cart_items = rpress_get_cart_contents();
		if( is_array($cart_items) && !empty($cart_items) ) {
			$items_in_cart = $cart_items[$key];
			if( is_array($items_in_cart) ) {
				$item_price = rpress_get_fooditem_price( $items_in_cart['id'] );
				if( $items_in_cart['quantity'] > 0 ) {
					$item_price = $item_price * $items_in_cart['quantity'];
				}
				array_push($cart_items_price, $item_price);
				if( isset( $items_in_cart['addon_items'] ) ) {
					foreach( $items_in_cart['addon_items'] as $key => $item_list ) {
						array_push($cart_items_price, $item_list['price']);
					}
				}
			}
		}
	}

	$cart_item_total = array_sum($cart_items_price);
	return $cart_item_total;
}

/**
 * Get food item quantity in the cart by key
 *
 * @since       1.0
 * @param       int | cart_key
 * @return      array | cart items array
 */
function rpress_get_item_qty_by_key( $cart_key ) {
	if( $cart_key !== '' ) {
		$cart_items = rpress_get_cart_contents();
		//print_r($cart_items);
		$cart_items = $cart_items[$cart_key];
		return $cart_items['quantity'];
	}
}

add_filter( 'rpress_food_cats_before', 'food_cats_before_wrap' );

if( !function_exists('food_cats_before_wrap') ) {
	function food_cats_before_wrap() {
		$html = '<div class="rpress-section col-lg-12 col-md-12 col-sm-12 col-xs-12" >';
		echo $html;
	}
}

add_filter( 'rpress_food_cats_after', 'food_cats_after_wrap' );

if( !function_exists('food_cats_after_wrap') ) {
	function food_cats_after_wrap() {
		$html = '</div>';
		return $html;
	}
}

add_filter( 'rpress_food_list_items_before', 'food_item_list_before' );
if( !function_exists('food_item_list_before') ) {
	function food_item_list_before() {
		$html = '<div class="rpress_fooditems_list col-lg-7 col-md-7 col-sm-9 col-xs-12">';
		echo $html;
	}
}

add_filter('rpress_food_list_items_after', 'food_item_list_after' );
if( !function_exists('food_item_list_after') ) {
	function food_item_list_after() {
		$html = '</div>';
		echo $html;
	}
}


add_filter( 'rpress_food_cats', 'rpress_get_food_cats' );

if( ! function_exists( 'rpress_get_food_cats' ) ) {
	function rpress_get_food_cats(){

		$taxonomy_name = 'food-category';

		$get_all_items = get_terms( array(
    	'taxonomy' => $taxonomy_name,
    	'hide_empty' => true,
		) );

		$html = '';

		$html .= '<div class="col-lg-2 col-md-2 col-sm-3 col-xs-12 sticky-sidebar cat-lists">';

		//filter toggle for mobile
		$html .= '<div class="rpress-filter-toggle">';
		$html .= '<span class="rpress-filter-toggle-text">'.__('Categories By', 'rpress').'</span>';
		$html .= '</div>';

		//filter wrapper starts here
		$html .= '<div class="rpress-filter-wrapper">';
		$html .= '<div class="rpress-categories-menu">';
		$html .= '<h6>'.__('Categories', 'restro-press').'</h6>';

		if( is_array($get_all_items) && !empty($get_all_items) ) :
			$html .= '<ul class="rpress-category-lists">';
			foreach ($get_all_items as $key => $get_all_item) :
				$html .= '<li class="rpress-category-item "><a href="javascript:void(0)" data-id="'.$get_all_item->term_id.'" class="rpress-category-link  nav-scroller-item  ">'.$get_all_item->name.'</a></li>';
			endforeach;

			$html .= '</ul>';
		endif;

		$html .= '</div>';
		$html .= '</div>';
		//filter wrapper ends here

		$html .= '</div>';
		return $html;
	}
}

add_filter('rpress_fooditems_search', 'rpress_implement_search');
if( !function_exists('rpress_implement_search') ) {
	function rpress_implement_search() {
		$search = '';
		$search .= '<div class="rpress-search-wrap rpress-live-search">';
		$search .= '<input id="rpress-food-search" type="text" placeholder="'.__('Search Food Item', 'restro-press').'">';
		$search .= '</div>';
		echo $search;
	}
}



if ( ! function_exists( 'rpress_product_menu_tab' ) ) {

	/**
	 * Output the rpress menu tab content.
	 */
	function rpress_product_menu_tab() {
		 echo do_shortcode('[rpress_items]');
	}
}

/**
 * Get special instruction for food items
 *
 * @since       1.0
 * @param       array | food items
 * @return      string | Special instruction string
 */
function get_special_instruction( $items ) {
	$instruction = '';
	if( is_array($items) ) {
		if( isset($items['options']) ) {
			$instruction = $items['options']['instruction'];
		}
		else {
			if( isset($items['instruction']) ) {
				$instruction = $items['instruction'];
			}
		}
	}
	return $instruction;
}

/**
 * Get instruction in the cart by key
 *
 * @since       1.0
 * @param       int | cart_key
 * @return      string | Special instruction string
 */
function rpress_get_instruction_by_key( $cart_key ) {
	if( $cart_key !== '' ) {
		$cart_items = rpress_get_cart_contents();
		$cart_items = $cart_items[$cart_key];
		$instruction = '';
		if( isset($cart_items['instruction']) ) {
			$instruction = !empty($cart_items['instruction']) ? $cart_items['instruction'] : '';
		}
	}
	return $instruction;
}

add_action('rpress_fooditems_list_after', 'rpress_get_cart_items');

function rpress_get_cart_items() {
	$html = '<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 pull-right rpress-sidebar-cart item-cart sticky-sidebar">';
	$html .= '<div class="rpress-sidebar-cart-wrap">';
	$html .= do_shortcode('[fooditem_cart]');
	$html .= '</div>';
	$html .= '</div>';
	echo $html;
}


/**
 * Get formatted array of food item details 
 *
 * @since       1.0.2
 * @param       array | Food items 
 * @param       int | cart key by default blank
 * @return      array | Outputs the array of food items with formatted values in the key value
 */
function getFormattedCatsList($terms, $cart_key = '') {
	$parent_ids = array();
	$child_ids = array();
	$list_array = array();
	$child_arr = array();

	$html = '';

	if($terms) {
  	foreach( $terms as $term ) {
    	if( $term->parent == 0 ) {
    		$parent_id = $term->term_id;
    		array_push( $parent_ids, $parent_id);
    	}
    	else {
    		$child_id = $term->term_id;;
    		array_push( $child_ids, $child_id );
    	}
    }
  }
  	
	if( is_array( $parent_ids ) && !empty($parent_ids) ) {
  	foreach( $parent_ids as $parent_id ) {
    	$term_data = get_term_by('id', $parent_id, 'addon_category');
    	$children = get_term_children( $term_data->term_id, 'addon_category' );

    	if( is_array($children) && !empty($children) ) {
    		
    		foreach( $children as $key => $children_data ) {
    			if( in_array($children_data, $child_ids) ) {
    				array_push( $child_arr, $children_data);

    				if( is_array($child_arr) && !empty($child_arr) ) {
    					foreach( $child_arr as $data => $child_arr_list ) {
    						$term_data = get_term_by('id', $child_arr_list, 'addon_category');
    						$t_id = $child_arr_list;
    						$term_meta = get_option( "taxonomy_term_$t_id" );
    						$term_price = !empty($term_meta['price']) ? $term_meta['price'] : '';
    						$term_quantity = !empty($term_meta['enable_quantity']) ? $term_meta['enable_quantity'] : '';

    						$list_array[$data]['id'] = $term_data->term_id;
    						$list_array[$data]['name'] = $term_data->name;
    						$list_array[$data]['price'] = html_entity_decode( rpress_currency_filter( rpress_format_amount( $term_price ) ), ENT_COMPAT, 'UTF-8' );
    						$list_array[$data]['price'] =  $term_price;
    						$list_array[$data]['slug'] = $term_data->slug;
    					}
    				}		
    			}
    		}
    	}
    }
	}
	return $list_array;
}


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
	
	//Check store timing is enabled or not
	if( class_exists('RestroPress_Store_Timing') ) {
		rpress_checkout_delivery_type($delivery_opt, $delivery_time);

		$store_timings = rpress_get_store_timing();
		//check store timings
		if( isset($store_timings['enable']) ) {
			$delivery_option = isset($_POST['deliveryOpt']) ? $_POST['deliveryOpt'] : '';

			$store_timings_response = RestroPress_Store_Timing::check_store_timing($delivery_option);
			$store_timings_response = json_decode($store_timings_response);

			//Check store is closed
			if( $store_timings_response->store_status == 'closed' ) {
				$response = array( 'status' => 'error', 'store_status' => 'closed' );
			}

			if( $store_timings_response->store_status == 'opened' && $delivery_option == 'delivery' ) {
				if( $store_timings_response->delivery_status == 'delivery closed' ) {
					$response = array( 'status' => 'error', 'delivery_status' => 'delivery_closed' );
				}
				else {
					$response = array( 'status' => 'success' );
				}
			}

			if( $store_timings_response->store_status == 'opened' 
				&& $delivery_option == 'pickup' ) {
				$response = array( 'status' => 'success' );
			}
		}
		else {
			$response = array( 'status' => 'success' );
		}
		
	}
	else {
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
				rpress_checkout_delivery_type($delivery_opt, $delivery_time);
				$response = array( 'status' => 'success' );
			endif;

		else :
			//Save session vars
			rpress_checkout_delivery_type($delivery_opt, $delivery_time);
			$response = array( 'status' => 'success' );
		endif;

	}
	
	echo json_encode($response);

	exit;
}	

add_action('wp_ajax_rpress_proceed_checkout', 'rpress_proceed_checkout');
add_action('wp_ajax_nopriv_rpress_proceed_checkout', 'rpress_proceed_checkout');


/**
 * Save order type in session 
 *
 * @since       1.0.4
 * @param       string | Delivery Type
 * @param 			string | Delivery Time
 * @return      array  | Session array for delivery type and delivery time
 */
function rpress_checkout_delivery_type($delivery_type, $delivery_time) {

	$_COOKIE['deliveryMethod'] = $delivery_type;
  	$_COOKIE['deliveryTime'] = $delivery_time;
}


/**
 * Show delivery options in the cart 
 *
 * @since       1.0.2
 * @param       void
 * @return      string | Outputs the html for the delivery options with texts
 */
function get_delivery_options() {
	$html = '';
	$html .='<div class="delivery-wrap">';
	$html .='<div class="delivery-opts">';
	if( isset($_COOKIE['deliveryMethod']) && $_COOKIE['deliveryMethod'] !== '' ) {
		$html .= '<span>'.strtoupper($_COOKIE['deliveryMethod']).'</span>';
		if( isset($_COOKIE['deliveryTime']) 
		&& $_COOKIE['deliveryTime'] !== '' ) {
			$html .= '<span> at '.$_COOKIE['deliveryTime'].'</span>';
		}
	}
	$html .='</div>';
	$html .='</div>';
		
	return $html;

}

add_action( 'rpress_insert_payment', 'rpress_show_admin_notification', 10, 2 );




/**
 * Show notification to admin 
 *
 * @since       1.0.3
 * @param       int | Payment_id
 * @param 			obj | Payment Data
 * @return      boolean
 */
function rpress_show_admin_notification($payment_id, $payment_data) {

	$url_order = admin_url('post.php?post=' . absint($payment_id) . '&action=edit');

	$customer_email = isset($payment_data['user_email']) ? $payment_data['user_email'] : '';
	$username = '';

	if( !empty($customer_email) ) {
		$customer_user = get_user_by('email', $customer_email);
		if( $customer_user ) {
			$username = !empty($customer_user->ID) ? get_user_meta($customer_user->ID, 'nickname', true) : '';
		}
	}

	$placeholder = array(
                	"{order_id}" => $payment_id,
                  "{order_total}" => $payment_data['price'],
                  "{username}" => $username,
                );

	$description = strtr(rpress_get_option('notification_body'), $placeholder);
	$notifications['description'] = $description;
	$notifications['url'] = $url_order;
	$notification_processed = $notifications;

	register_notification($notification_processed);
	
}

add_action('wp_ajax_rpress_display_order_notifications', 'rpress_display_order_notifications');

/**
 * Show order notification
 *
 * @since       1.0.3
 * @param       void
 * @return      json | user notification json object
 */
function rpress_display_order_notifications() {
	$user_notifications = array();
            
	if ( current_user_can('manage_options') ) {
  	$current_user = wp_get_current_user();
    $id_current_user = $current_user->ID;
                
    $notifications = get_notifications_by_user($current_user);

    foreach ($notifications as $notification) {
    	$user_notified = (array)unserialize($notification->notified_users);
      $url = $notification->url;

      if (!in_array($id_current_user, $user_notified)) {
      	$noti = (array)unserialize($notification->data);
        $noti['url'] = $url;
        array_push($user_notifications, $noti);
        //Updated array user notified
        array_push($user_notified, $id_current_user);
        update_notification($notification->id, serialize($user_notified));
        continue;
      }
    }
  }
 	wp_send_json($user_notifications);

	exit;
}

function create_order_notification_table() {
	global $wpdb;
	$table_name = $wpdb->prefix.'rpress_order_notification';
	$version = '1.0';

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$sql = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		notification varchar(255) NOT NULL,
		data longtext,
		user_roles_to_notify longtext,
		notified_users longtext,
		date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

	dbDelta( $sql );

	update_option( $table_name . '_db_version', $version );
}

/**
 * Register notification system
 *
 * @since       1.0.3
 * @param       string | type
 * @return      void
 */
function register_notification($type) {
	global $wpdb;
	$table_name = $wpdb->prefix.'rpress_order_notification';
  $key_notification = uniqid();
  $type_notificacion = "placed";
  $data_notification = array();
  $data_notification['title'] = rpress_get_option('notification_title');
	$data_notification['description'] = $type['description'];
	
	$role_user_notification = "NULL";
	$url_notificated = $type['url'];
	$user_notificated = array();
  $insert_query = "INSERT INTO ". $table_name ." (`notification`, `data`, `user_roles_to_notify`,`notified_users`) VALUES ('" . $type_notificacion . "', '" . serialize($data_notification) . "' , '" . serialize($role_user_notification) . "' , '" . serialize($user_notificated) . "')";
  $wpdb->query( $insert_query );
}

/**
 * Get user notification
 *
 * @since       1.0.3
 * @param       object | User object
 * @return      array | an array of results
 */
function get_notifications_by_user($user) {
	global $wpdb;
	$table = $wpdb->prefix.'rpress_order_notification';
  $user_id = $user->ID;
  $datetime = 'NOW() - INTERVAL 15 MINUTE' ;

  $where = "WHERE (notified_users NOT LIKE '%i:$user_id;%') AND (date >= '%\"$datetime\"%')";


  $results = $wpdb->get_results( "SELECT * FROM $table $where" );

  return $results;
}

/**
 * Update order push notification
 *
 * @since       1.0.3
 * @param       int | id
 * @param       int | user_notified
 * @return      void
 */
function update_notification($id,$user_notified) {
	global $wpdb;
	$table_name = $wpdb->prefix.'rpress_order_notification';
  $wpdb->query("UPDATE ". $table_name ." SET `notified_users` = '".$user_notified."' WHERE `id` = '".$id."'");
}


function rpress_display_checkout_fields() {
	$enable_phone = rpress_get_option('enable_phone');
	$enable_flat = rpress_get_option('enable_door_flat');
	$enable_landmark = rpress_get_option('enable_landmark');
	$google_map_opts = rpress_get_option('enable_google_map_api');
?>

	<?php if($enable_phone): ?>
		<p id="rpress-phone-wrap">
  		<label class="rpress-label" for="rpress-phone"><?php _e('Phone Number', 'restro-press'); ?></label>
    	<span class="rpress-description">
    		<?php _e('Enter your phone number so we can get in touch with you.', 'restro-press'); ?>
    	</span>
    	<input class="rpress-input" type="text" name="rpress_phone" id="rpress-phone" placeholder="Phone Number" />
    </p>
   <?php endif; ?>

  <?php if($google_map_opts) :  ?>
  	
  	<p id="rpress-google-address">
  		<label class="rpress-address" for="rpress-address"><?php _e('Address', 'restro-press') ?></label>
    	<span class="rpress-description">
    		<?php _e('Enter Your Address', 'restro-press'); ?> 
    	</span>
    	<input class="rpress-input autocomplete" id="autocomplete" name="address" placeholder="Enter your address"
              type="text"/>
  	</p>

  	<p id="rpress-street-address">
  		<label class="rpress-street-address" for="rpress-street-address"><?php _e('Street Address', 'restro-press') ?></label>
    	<input class="rpress-input rpress-street-number" type="text" name="route" id="route"  />
  	</p>

  	<p id="rpress-city">
  		<label class="rpress-city" for="rpress-city"><?php _e('City', 'restro-press') ?></label>
    	<input class="rpress-input rpress-street-number" autocomplete="off" type="text" name="locality" id="locality"  />
  	</p>

  	<p id="rpress-state">
  		<label class="rpress-state" for="rpress-state"><?php _e('State', 'restro-press') ?></label>
    	<input class="rpress-input rpress-street-number" autocomplete="off" type="text" name="administrative_area_level_1" id="administrative_area_level_1"  />
  	</p>

  	<p id="rpress-zip">
  		<label class="rpress-zip" for="rpress-zip"><?php _e('Zip code', 'restro-press') ?></label>
    	<input class="rpress-input rpress-zip" autocomplete="off" type="text" name="postal_code" id="postal_code"  />
  	</p>

  	<p id="rpress-country">
  		<label class="rpress-country" for="rpress-country"><?php _e('Country', 'restro-press') ?></label>
    	<input class="rpress-input rpress-country" autocomplete="off" type="text" name="country" id="country"  />
    	<input type="hidden" id="rpress_geo_address" name="rpress_geo_address" value="">
  	</p>

  <?php endif; ?>
  

  <?php if($enable_flat) : ?>
    <p id="rpress-door-flat">
  		<label class="rpress-flat" for="rpress-flat"><?php _e('Door/Flat No.', 'restro-press'); ?></label>
    	<span class="rpress-description">
    		<?php _e('Enter your Door/Flat number', 'restro-press'); ?> 
    	</span>
    	<input class="rpress-input" type="text" name="rpress_door_flat" id="rpress-door-flat" placeholder="Door/Flat Number" />
    </p>
  <?php endif; ?>

  <?php if($enable_landmark): ?>
    <p id="rpress-landmark">
  	<label class="rpress-landmark" for="rpress-landmark"><?php _e('Land Mark', 'restro-press') ?></label>
    <span class="rpress-description">
    	<?php _e('Enter Landmark Near By You', 'restro-press'); ?> 
    </span>
    <input class="rpress-input" type="text" name="rpress_landmark" id="rpress-landmark" placeholder="Landmark" />
    </p>
  <?php endif; ?>

  <?php
}
add_action( 'rpress_purchase_form_user_info_fields', 'rpress_display_checkout_fields' );

/**
 * Make checkout fields required
 *
 * @since       1.0.3
 * @param       array | An array of required fields
 * @return      array | An array of fields
 */
function rpress_required_checkout_fields( $required_fields ) {
	$enable_phone = rpress_get_option('enable_phone');
	$enable_flat = rpress_get_option('enable_door_flat');
	$enable_landmark = rpress_get_option('enable_landmark');

	if( $enable_phone ) :
		$required_fields['rpress_phone'] = array(
			'error_id' 			=> 'invalid_phone',
			'error_message' =>  __('Please enter a valid Phone number', 'restro-press')
		);
	endif;

	if( $enable_flat ) :
  	$required_fields['rpress_door_flat'] = array(
  		'error_id' 			=> 'invalid_door_flat',
    	'error_message' => __('Please enter your door flat', 'restro-press')
  	);
  endif;

  if( $enable_landmark ):
  	$required_fields['rpress_landmark'] = array(
  		'error_id' 			=> 'invalid_landmark',
    	'error_message' => __('Please enter landmark', 'restro-press')
  	);
  endif;



  return $required_fields;
}
add_filter( 'rpress_purchase_form_required_fields', 'rpress_required_checkout_fields' );


/**
 * Stores custom data in payment fields
 *
 * @since       1.0.3
 * @param       array | Payment meta array
 * @return      array | Custom data with payment meta array
 */
function rpress_store_custom_fields( $payment_meta ) {

	// if( did_action( 'rpress_purchase' ) ) {
	// 	$payment_meta['phone'] = isset( $_POST['rpress_phone'] ) ? sanitize_text_field( $_POST['rpress_phone'] ) : '';
	// }

	// if( did_action( 'rpress_purchase' ) ) {
	// 	$payment_meta['flat'] = isset( $_POST['rpress_door_flat'] ) ? sanitize_text_field( $_POST['rpress_door_flat'] ) : '';
	// }

	// if( did_action( 'rpress_purchase' ) ) {
	// 	$payment_meta['landmark'] = isset( $_POST['rpress_landmark'] ) ? sanitize_text_field( $_POST['rpress_landmark'] ) : '';
	// }

	if( did_action( 'rpress_purchase' ) ) {
		$payment_meta['phone'] = isset( $_POST['rpress_phone'] ) ? sanitize_text_field( $_POST['rpress_phone'] ) : '';

		$payment_meta['flat'] = isset( $_POST['rpress_door_flat'] ) ? sanitize_text_field( $_POST['rpress_door_flat'] ) : '';

		$payment_meta['landmark'] = isset( $_POST['rpress_landmark'] ) ? sanitize_text_field( $_POST['rpress_landmark'] ) : '';

		$payment_meta['address'] = isset( $_POST['address'] ) ? sanitize_text_field( $_POST['address'] ) : '';

		$payment_meta['route'] = isset( $_POST['route'] ) ? sanitize_text_field( $_POST['route'] ) : '';

		$payment_meta['city'] = isset( $_POST['locality'] ) ? sanitize_text_field( $_POST['locality'] ) : '';

		$payment_meta['state'] = isset( $_POST['administrative_area_level_1'] ) ? sanitize_text_field( $_POST['administrative_area_level_1'] ) : '';

		$payment_meta['zip'] = isset( $_POST['postal_code'] ) ? sanitize_text_field( $_POST['postal_code'] ) : '';

		$payment_meta['country'] = isset( $_POST['country'] ) ? sanitize_text_field( $_POST['country'] ) : '';

		$payment_meta['latlng'] = isset( $_POST['rpress_geo_address'] ) ? sanitize_text_field( $_POST['rpress_geo_address'] ) : '';
	}

	

	return $payment_meta;
}
add_filter( 'rpress_payment_meta', 'rpress_store_custom_fields');


/**
 * Add the phone number to the "View Order Details" page
 * Add the flat number to the "View Order Details" page
 * Add the landmark to the "View Order Details" page
 */
function rpress_view_order_details( $payment_meta, $user_info ) {
	$phone = isset( $payment_meta['phone'] ) ? $payment_meta['phone'] : 'none';
	$flat = isset( $payment_meta['flat'] ) ? $payment_meta['flat'] : 'none';
	$landmark = isset( $payment_meta['landmark'] ) ? $payment_meta['landmark'] : 'none';
	
?>
	<div class="column-container">
  	<div class="column">
  		<?php if( $phone ) : ?>
   			<div style="margin-top:10px; margin-bottom:10px;">
    			<strong><?php echo __('Phone:', 'restro-press'); ?> </strong>
    			<?php echo $phone; ?>
    		</div>
    	<?php endif; ?>

    	<?php if( $flat ) : ?>
    		<div style="margin-bottom:10px;">
    			<strong><?php echo __('Flat:', 'restro-press'); ?> </strong>
    			<?php echo $flat; ?>
    		</div>
    	<?php endif; ?>

    	<?php if( $landmark) : ?>
    		<div style="margin-bottom:10px;">
    			<strong><?php echo __('Landmark:', 'restro-press'); ?> </strong>
    		 	<?php echo $landmark; ?>
    		</div>
    	<?php endif; ?>

  	</div>
  </div>
<?php
}
add_action( 'rpress_payment_personal_details_list', 'rpress_view_order_details', 10, 2 );

/**
 * Add a {phone} tag for use in either the purchase receipt email or admin notification emails
 * Add a {flat} tag for use in either the purchase receipt email or admin notification emails
 * Add a {landmark} tag for use in either the purchase receipt email or admin notification emails
 */
function checkout_rpress_add_email_tag() {
	rpress_add_email_tag( 'phone', 'Customer\'s phone number', 'rpress_email_tag_phone' );
	rpress_add_email_tag( 'flat', 'Customer\'s flat number', 'rpress_email_tag_flat' );
	rpress_add_email_tag( 'landmark', 'Customer\'s landmark number', 'rpress_email_tag_landmark' );
}
add_action( 'rpress_add_email_tags', 'checkout_rpress_add_email_tag' );

/**
 * The {phone} email tag
 */
function rpress_email_tag_phone( $payment_id ) {
	$payment_data = rpress_get_payment_meta( $payment_id );
	return $payment_data['phone'];
}

/**
 * The {flat} email tag
 */
function rpress_email_tag_flat( $payment_id ) {
	$payment_data = rpress_get_payment_meta( $payment_id );
	return $payment_data['flat'];
}

/**
 * The {landmark} email tag
 */
function rpress_email_tag_landmark( $payment_id ) {
	$payment_data = rpress_get_payment_meta( $payment_id );
	return $payment_data['landmark'];
}

/**
 * Get order by statemeny by taxonomy
 *
 * @since       1.0.2
 * @param       string | order by
 * @return      string | order by string passed
 */
function edit_posts_orderby($orderby_statement) {
	$orderby_statement = " term_taxonomy_id ASC ";
  return $orderby_statement;
}

/**
 * Get Delivery type
 *
 * @since       1.0.4
 * @param       Int | Payment_id
 * @return      string | Delivery type string
 */
function rpress_get_delivery_type( $payment_id ) {
	if( $payment_id  ) {
		$delivery_type = get_post_meta( $payment_id, '_rpress_delivery_type', true );
		if( $delivery_type ) {
			return ucfirst($delivery_type);
		}
		else {
			return '-';
		}
	}
}

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

		if( is_array( $parent_ids ) && !empty($parent_ids) ) {
			
			$html .= '<select class="addon-items-list" name="rpress-payment-details-fooditems[0][addon_items][]">';

			foreach( $parent_ids as $parent_id ) {
				$term_data = get_term_by('id', $parent_id, 'addon_category');
				$children = get_term_children( $term_data->term_id, 'addon_category' );

				if( is_array($children) && !empty($children) ) {
					foreach( $children as $children_data ) {
						if( in_array($children_data, $child_ids) ) {
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


/**
 * Get holidays list and disable the dates from calendar
 *
 * @since       1.0.6
 * @param       blank
 * @return      array | Holiday lists
 */
function rpress_get_holidays_lists() {
	$holidays_arr = array();
	if( class_exists('RestroPress_Store_Timing') ) {
		$store_timings = get_option('rpress_store_timing');
		if( isset($store_timings['enable']) 
			&& isset($store_timings['pre_order']) ) {
       if( isset($store_timings['holiday']) ) {
         $holidays = $store_timings['holiday'];
         if( is_array($holidays) ) {
           foreach( $holidays as $key => $holiday ) {
             $holiday_list = date('Y-n-d', strtotime($holiday));
             array_push($holidays_arr, $holiday_list);
           }
         }
       }
     }
	}
	return $holidays_arr;
}


/**
 * Get Preorder ranges and hides the dates which are in max
 *
 * @since       1.0.6
 * @param       blank
 * @return      string | Max range date
 */
function rpress_show_preorder_until() {
	$pre_order_date = '';
	if( class_exists('RestroPress_Store_Timing') ) {
		$store_timings = get_option('rpress_store_timing');

		if( isset($store_timings['enable']) 
			&& isset($store_timings['pre_order']) ) {
			$pre_order_range = isset($store_timings['pre_order_range']) ? $store_timings['pre_order_range'] : '';

		  $get_timezone = get_option('timezone_string');

  		if( $get_timezone !== '' ) {
  			date_default_timezone_set($get_timezone);
  		}

			if( $pre_order_range !== '' ) {
				$current_date = date('Y-m-d');
				$pre_order_date = date('Y-m-d', strtotime($current_date . ' + '.$pre_order_range.' days'));
			}
		}
	}
	return $pre_order_date;
}

/**
 * Get Cutoff Delivery time
 *
 * @since       1.0.6
 * @param       blank
 * @return      array | Cutoff Hours For Today Delivery
 */
function Rpress_Delivery_Cut_Hours() {
	$get_timezone = get_option('timezone_string');

  if( !empty($get_timezone) ) {
  	date_default_timezone_set($get_timezone);
  }

  $current_day = date("w");

	if( class_exists('RestroPress_Store_Timing') ) {
		$store_hours = new RestroPress_Store_Timing();
		
		if( method_exists($store_hours, 'rpress_check_delivery_hours') ) {
			$cutoff_hours = $store_hours->rpress_check_delivery_hours($current_day);
			return $cutoff_hours;
		}
	}

}