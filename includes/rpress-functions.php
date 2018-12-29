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

	//Add custom js script
	wp_enqueue_script('rpress-custom', plugins_url( 'assets/js/rpress-custom.js', RPRESS_PLUGIN_FILE ), array( 'jquery', 'rpress-sticky-sidebar' ), '1.0.1', true );

	// Add custom css
	wp_enqueue_style( 'rpress-custom-stylesheet', plugins_url( 'assets/css/rpress-custom.css', RPRESS_PLUGIN_FILE ));

	// Timepicker css
  wp_register_style( 'rpress-timepicker', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css' );
  wp_enqueue_style( 'rpress-timepicker' );

  // Timepicker js
  wp_register_script( 'rpress-timepicker-script', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js' );
  wp_enqueue_script( 'rpress-timepicker-script' );

  $fooditem_popup_enable = rpress_get_option( 'enable_food_image_popup', false );

  wp_localize_script( 'rpress-custom', 'RpressVars', array(
  	'wait_text' 		=> __( 'Please Wait', 'restro-press' ),
  	'add_to_cart' 		=> __( 'Add To Cart', 'restro-press' ),
  	'added_into_cart' 	=> __( 'Added Into Cart', 'restro-press' ),
  	'estimated_tax'		=> __( 'Estimated Tax', 'restro-press'),
  	'total_text'		=> __( 'Subtotal', 'restro-press'),
  	'enable_fooditem_popup' => $fooditem_popup_enable,
  ));
}
add_action( 'wp_enqueue_scripts',  'rpress_enque_scripts' );

add_action( 'admin_enqueue_scripts', 'rpress_admin_scripts' );

function rpress_admin_scripts() {
	wp_register_style( 'rpress-timepicker', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css' );
  wp_enqueue_style( 'rpress-timepicker' );

  wp_register_script( 'rpress-timepicker-script', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js' );
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
function rpress_update_delivery_options() {
	$delivery_opt = isset($_POST['deliveryOpt']) ? $_POST['deliveryOpt'] : '';

  $delivery_time = isset($_POST['deliveryTime']) ? $_POST['deliveryTime'] : '';

	if( session_id() == '' || !isset($_SESSION) ) {
  	// session isn't started
    session_start();
	}

	$_SESSION['delivery_type'] = $delivery_opt;
  $_SESSION['delivery_time'] = $delivery_time;

	exit;
}	

add_action('wp_ajax_rpress_update_delivery_options', 'rpress_update_delivery_options');
add_action('wp_ajax_nopriv_rpress_update_delivery_options', 'rpress_update_delivery_options');


/**
 * Show delivery options in the cart 
 *
 * @since       1.0.2
 * @param       void
 * @return      string | Outputs the html for the delivery options with texts
 */
function get_delivery_options() {
	if( rpress_get_option('enable_delivery') == 1 || rpress_get_option('enable_pickup') == 1 ) {

		$html = '';
		$html .= '<h3 class="delivery-options-heading">'. __( 'Delivery Options', 'restro-press' ).'</h3>';
		$html .='<div class="delivery-wrap">';
		$html .='<div class="delivery-opts">';
		
		if( rpress_get_option('enable_delivery') == 1 ) :
			
			$html .='<input class="deli" id="delivery" type="radio" checked="checked" value="delivery" name="delivery_opt"><label for="delivery">'.__( 'Delivery', 'restro-press' ).'</label>'
	  			;
		endif;

		if( rpress_get_option('enable_pickup') == 1 ) : 
			
			$html .='<input class="pick" id="pickup" type="radio" value="pickup" name="delivery_opt"><label for="pickup">'.__( 'Pickup', 'restro-press' ).'</label>'
	  			;
		endif;
		$html .='</div>';
		
		$html .='<div class="rpress-open-hrs">';
		$html .= '<h3 class="delivery-options-heading">'.__( 'Select Delivery / Pickup Time', 'restro-press' ).'</h3>';
		$html .='<input type="text" id="rpress-allowed-hours" name="rpress_allowed_hours">';
		$html .='</div>';
		$html .='</div>';
	}

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
?>
	<p id="rpress-phone-wrap">
  	<label class="rpress-label" for="rpress-phone"><?php _e('Phone Number', 'restro-press'); ?></label>
    <span class="rpress-description">
    	<?php _e('Enter your phone number so we can get in touch with you.', 'restro-press'); ?>
    </span>
    <input class="rpress-input" type="text" name="rpress_phone" id="rpress-phone" placeholder="Phone Number" />
    </p>

    <p id="rpress-door-flat">
  	<label class="rpress-flat" for="rpress-flat"><?php _e('Door/Flat No.', 'restro-press'); ?></label>
    <span class="rpress-description">
    	<?php _e('Enter your Door/Flat number', 'restro-press'); ?> 
    </span>
    <input class="rpress-input" type="text" name="rpress_door_flat" id="rpress-door-flat" placeholder="Door/Flat Number" />
    </p>

    <p id="rpress-landmark">
  	<label class="rpress-landmark" for="rpress-landmark"><?php _e('Land Mark', 'restro-press') ?></label>
    <span class="rpress-description">
    	<?php _e('Enter Landmark Near By You', 'restro-press'); ?> 
    </span>
    <input class="rpress-input" type="text" name="rpress_landmark" id="rpress-landmark" placeholder="Landmark" />
    </p>

    <p id="rpress-google-address">
  	<label class="rpress-google-address" for="rpress-google-address"><?php _e('Address', 'restro-press') ?></label>
    <span class="rpress-description">
    	<?php _e('Enter Your Address', 'restro-press'); ?> 
    </span>
    <input class="rpress-input" type="text" name="rpress_address" id="rpress-google-address" placeholder="Address" />
    </p>
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
	$required_fields['rpress_phone'] = array(
		'error_id' 			=> 'invalid_phone',
		'error_message' =>  __('Please enter a valid Phone number', 'restro-press')
	);

  $required_fields['rpress_door_flat'] = array(
  	'error_id' 			=> 'invalid_door_flat',
    'error_message' => __('Please enter your door flat', 'restro-press')
  );

  $required_fields['rpress_landmark'] = array(
  	'error_id' 			=> 'invalid_landmark',
    'error_message' => __('Please enter landmark', 'restro-press')
  );

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

	if( did_action( 'rpress_purchase' ) ) {
		$payment_meta['phone'] = isset( $_POST['rpress_phone'] ) ? sanitize_text_field( $_POST['rpress_phone'] ) : '';
	}

	if( did_action( 'rpress_purchase' ) ) {
		$payment_meta['flat'] = isset( $_POST['rpress_door_flat'] ) ? sanitize_text_field( $_POST['rpress_door_flat'] ) : '';
	}

	if( did_action( 'rpress_purchase' ) ) {
		$payment_meta['landmark'] = isset( $_POST['rpress_landmark'] ) ? sanitize_text_field( $_POST['rpress_landmark'] ) : '';
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
   		<div style="margin-top:10px; margin-bottom:10px;">
    		<strong><?php echo __('Phone:', 'restro-press'); ?> </strong>
    		<?php echo $phone; ?>
    	</div>

    	<div style="margin-bottom:10px;">
    		<strong><?php echo __('Flat:', 'restro-press'); ?> </strong>
    		<?php echo $flat; ?>
    	</div>
    		
    	<div style="margin-bottom:10px;">
    		<strong><?php echo __('Landmark:', 'restro-press'); ?> </strong>
    		 <?php echo $landmark; ?>
    	</div>
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
