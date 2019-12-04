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
    'name'              => _x( 'Addon Item', 'taxonomy general name', 'restropress' ),
    'singular_name'     => _x( 'Addon item', 'taxonomy singular name', 'restropress' ),
    'search_items'      => __( 'Search Addon Item', 'restropress' ),
    'all_items'         => __( 'All Addon Item', 'restropress' ),
    'parent_item'       => __( 'Parent Addon Item', 'textdomain' ),
    'parent_item_colon' => __( 'Parent Addon Item:', 'textdomain' ),
    'edit_item'         => __( 'Edit Addon Item', 'restropress' ),
    'update_item'       => __( 'Update Addon item', 'restropress' ),
    'add_new_item'      => __( 'Add New Addon Item', 'restropress' ),
    'new_item_name'     => __( 'New Addon Item', 'restropress' ),
    'menu_name'         => __( 'Addon Item', 'restropress' ),
  );


  $food_category_label = array(
    'name'              => _x( 'Food Category', 'taxonomy general name', 'restropress' ),
    'singular_name'     => _x( 'Food Category', 'taxonomy singular name', 'restropress' ),
    'search_items'      => __( 'Search Food Category', 'restropress' ),
    'all_items'         => __( 'All Food Category', 'restropress' ),
    'parent_item'       => __( 'Parent Food Category', 'textdomain' ),
    'parent_item_colon' => __( 'Parent Food Category:', 'textdomain' ),
    'edit_item'         => __( 'Edit Food Category', 'restropress' ),
    'update_item'       => __( 'Update Food Category', 'restropress' ),
    'add_new_item'      => __( 'Add New Food Category', 'restropress' ),
    'new_item_name'     => __( 'New Food Category', 'restropress' ),
    'menu_name'         => __( 'Food Category', 'restropress' ),
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


function addon_category_taxonomy_custom_fields($tag) {
  $t_id = $tag->term_id; 
  $term_meta = get_option( "taxonomy_term_$t_id" ); 
  $use_addon_like =  isset($term_meta['use_it_like']) ? $term_meta['use_it_like'] : 'checkbox';
?>
<?php if( $tag->parent != 0 ): ?>
<tr class="form-field">
  <th scope="row" valign="top">
    <label for="price_id"><?php _e('Price'); ?></label>
  </th>
  <td>
    <input type="number" step=".01" name="term_meta[price]" id="term_meta[price]" size="25" style="width:15%;" value="<?php echo $term_meta['price'] ? $term_meta['price'] : ''; ?>"><br />
    <span class="description"><?php _e('Price for this addon item'); ?></span>
  </td>
</tr>
<?php endif; ?>

<?php if( $tag->parent == 0 ): ?>
<tr class="form-field">
  <th scope="row" valign="top">
    <label for="use_it_as">
      <?php _e('Addon item selection type', 'restropress'); ?></label>
  </th>
  <td>
    <div class="use-it-like-wrap">
      <label for="use_like_radio">
        <input id="use_like_radio" type="radio" value="radio" name="term_meta[use_it_like]" <?php checked($use_addon_like, 'radio'); ?> >
          <?php _e('Single item', 'restropress'); ?>
      </label>
      <br/><br/>
      <label for="use_like_checkbox">
        <input id="use_like_checkbox" type="radio" value="checkbox" name="term_meta[use_it_like]" <?php checked($use_addon_like, 'checkbox'); ?> >
          <?php _e('Multiple Items', 'restropress'); ?>
      </label>
    </div>
  </td>
</tr>
<?php endif; ?>

<?php
}

/**
 * Update taxonomy meta data
 *
 * @since       1.0
 * @param       int | term_id
 * @return      update meta data
 */
function save_addon_category_custom_fields( $term_id ) {
  if( isset( $_POST['term_meta'] ) ) {
    $t_id = $term_id;
    $term_meta = get_option( "taxonomy_term_$t_id" );
    $cat_keys = array_keys( $_POST['term_meta'] );

    if( is_array( $cat_keys ) && !empty( $cat_keys ) ) {
      foreach ( $cat_keys as $key ){
        if( isset( $_POST['term_meta'][$key] ) ){
          $term_meta[$key] = $_POST['term_meta'][$key];
        }
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
function getCartItemsByKey( $key ) {
  $cart_items_arr = array();
  if( $key !== '' ) {
    $cart_items = rpress_get_cart_contents();
    if( is_array( $cart_items ) && !empty( $cart_items ) ) {
      $items_in_cart = $cart_items[$key];
      if( is_array( $items_in_cart ) ) {
        if( isset( $items_in_cart['addon_items'] ) ) {
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
function getCartItemsByPrice( $key ) {
  $cart_items_price = array();
  
  if( $key !== '' ) {
    $cart_items = rpress_get_cart_contents();
    
    if( is_array( $cart_items ) && !empty( $cart_items ) ) {
      $items_in_cart = $cart_items[$key];
      if( is_array( $items_in_cart ) ) {
        $item_price = rpress_get_fooditem_price( $items_in_cart['id'] );
        
        if( $items_in_cart['quantity'] > 0 ) {
          $item_price = $item_price * $items_in_cart['quantity'];
        }
        array_push( $cart_items_price, $item_price );
        
        if( isset( $items_in_cart['addon_items'] ) ) {
          foreach( $items_in_cart['addon_items'] as $key => $item_list ) {
            array_push( $cart_items_price, $item_list['price'] );
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
    $cart_items = $cart_items[$cart_key];
    return $cart_items['quantity'];
  }
}

add_action( 'wp_footer', 'rpress_popup' );
if( !function_exists('rpress_popup') ) {
  function rpress_popup() {
    rpress_get_template_part( 'rpress', 'popup' );
  }
}

add_action( 'rpress_get_food_categories', 'rpress_get_food_cats' );

if ( ! function_exists( 'rpress_get_food_cats' ) ) {
  function rpress_get_food_cats(){
    rpress_get_template_part('rpress', 'get-categories');
  }
}

if ( ! function_exists( 'rpress_search_form' ) ) {
  function rpress_search_form() {
    $search  = '<div class="rpress-search-wrap rpress-live-search">';
    $search .= '<input id="rpress-food-search" type="text" placeholder="'.__('Search Food Item', 'restropress').'">';
        $search .= '</div>';
    return $search;
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

  return apply_filters( 'rpress_sepcial_instruction', $instruction );
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

add_action( 'rpress_get_cart', 'rpress_get_cart_items' );

function rpress_get_cart_items() {
  ob_start();
  ?>
  <div class="rp-col-lg-3 rp-col-md-3 rp-col-sm-12 rp-col-xs-12 pull-right rpress-sidebar-cart item-cart sticky-sidebar">
    <div class="rpress-sidebar-cart-wrap">
      <?php echo rpress_shopping_cart(); ?>
    </div>
  </div>
  <?php
  $data = ob_get_contents();
  ob_get_clean();
  echo $data;
}


/**
 * Get formatted array of food item details
 *
 * @since       1.0.2
 * @param       array | Food items
 * @param       int | cart key by default blank
 * @return      array | Outputs the array of food items with formatted values in the key value
 */
function getFormattedCatsList( $terms, $cart_key = '' ) {
    $parent_ids = $child_ids =  $list_array = $child_arr = array();
    $html = '';
    
    if( $terms ) {
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
 * Save order type in session
 *
 * @since       1.0.4
 * @param       string | Delivery Type
 * @param           string | Delivery Time
 * @return      array  | Session array for delivery type and delivery time
 */
function rpress_checkout_delivery_type( $delivery_type, $delivery_time ) {

  $_COOKIE['deliveryMethod'] = $delivery_type;
  $_COOKIE['deliveryTime']  = $delivery_time;
}


/**
 * Show delivery options in the cart
 *
 * @since       1.0.2
 * @param       void
 * @return      string | Outputs the html for the delivery options with texts
 */
function get_delivery_options( $changeble ) {
  $color = rpress_get_option( 'checkout_color', 'red' );
  ob_start();
  ?>
  <div class="delivery-wrap">
    <div class="delivery-opts">
      <?php if ( isset( $_COOKIE['deliveryMethod'] ) 
      && $_COOKIE['deliveryMethod'] !== '' ) : ?>
      <span class="delMethod">
        <?php echo $_COOKIE['deliveryMethod']; ?></span>
        <?php if( isset($_COOKIE['deliveryTime'])
          && $_COOKIE['deliveryTime'] !== '' ) : ?>
          <span class="delTime"> 
            <?php esc_html_e( 'at', 'restropress' ); ?> 
            <?php echo $_COOKIE['deliveryTime']; ?>    
          </span>
        <?php endif; ?>
      <?php endif; ?>
    </div>
    <?php
      if( $changeble && isset( $_COOKIE['deliveryMethod'] ) && $_COOKIE['deliveryMethod'] !== '' ) :
      ?>
      <span class="delivery-change <?php echo $color; ?>"><?php esc_html_e('Change?', 'restropress'); ?></span>
      <?php
      endif;
     ?>
  </div>
  <?php
  $data = ob_get_contents();
  ob_get_clean();
  return $data;
}


function rpress_get_delivery_price() {
  $delivery_fee_settings = get_option( 'rpress_delivery_fee', array() );
  $free_delivery_above = isset($delivery_fee_settings['free_delivery_above']) ? $delivery_fee_settings['free_delivery_above'] : 0;

  $cart_subtotal = rpress_get_cart_subtotal();

  if( isset( $_COOKIE['rpress_delivery_price'] ) ) {
    ob_start();
        
    if( $cart_subtotal < $free_delivery_above ) {
      echo rpress_currency_filter( rpress_format_amount( $_COOKIE['rpress_delivery_price'] ) );
    }

    return ob_get_clean();
  }
}


function rpress_display_checkout_fields() {
  $enable_phone = rpress_get_option( 'enable_phone' );
  $enable_flat = rpress_get_option( 'enable_door_flat' );
  $enable_landmark = rpress_get_option( 'enable_landmark' );
  $google_map_opts = rpress_get_option( 'enable_google_map_api' );
  $delivery_method = isset( $_COOKIE['deliveryMethod'] ) ? $_COOKIE['deliveryMethod'] : '';
  
  if($enable_phone): ?>
    <p id="rpress-phone-wrap">
      <label class="rpress-label" for="rpress-phone"><?php _e('Phone Number', 'restropress'); ?><span class="rpress-required-indicator">*</span></label>
      <span class="rpress-description">
        <?php _e('Enter your phone number so we can get in touch with you.', 'restropress'); ?>
      </span>
      <input class="rpress-input" type="text" name="rpress_phone" id="rpress-phone" placeholder="Phone Number" />
    </p>
  <?php endif; ?>

  <?php if( $google_map_opts ) :  ?>

    <p id="rpress-google-address">
      <label class="rpress-address" for="rpress-address"><?php _e('Address', 'restropress') ?></label>
      <span class="rpress-description">
          <?php _e('Enter Your Address', 'restropress'); ?>
      </span>
      <input class="rpress-input autocomplete" id="autocomplete" name="address" placeholder="Enter your address"
            type="text"/>
    </p>

    <p id="rpress-street-address">
      <label class="rpress-street-address" for="rpress-street-address"><?php _e('Street Address', 'restropress') ?></label>
      <input class="rpress-input rpress-street-number" type="text" name="route" id="route"  />
    </p>

    <p id="rpress-city">
      <label class="rpress-city" for="rpress-city"><?php _e('City', 'restropress') ?></label>
      <input class="rpress-input rpress-street-number" autocomplete="off" type="text" name="locality" id="locality"  />
    </p>

    <p id="rpress-state">
      <label class="rpress-state" for="rpress-state"><?php _e('State', 'restropress') ?></label>
      <input class="rpress-input rpress-street-number" autocomplete="off" type="text" name="administrative_area_level_1" id="administrative_area_level_1"  />
    </p>

    <p id="rpress-zip">
      <label class="rpress-zip" for="rpress-zip"><?php _e('Zip code', 'restropress') ?></label>
      <input class="rpress-input rpress-zip" autocomplete="off" type="text" name="postal_code" id="postal_code"  />
    </p>

    <p id="rpress-country">
      <label class="rpress-country" for="rpress-country"><?php _e('Country', 'restropress') ?></label>
      <input class="rpress-input rpress-country" autocomplete="off" type="text" name="country" id="country"  />
      <input type="hidden" id="rpress_geo_address" name="rpress_geo_address" value="">
    </p>

  <?php endif; ?>

  <?php 
  if( $enable_flat ) : 
    if( $delivery_method !== 'pickup') : ?>
    <p id="rpress-door-flat">
      <label class="rpress-flat" for="rpress-flat"><?php _e('Door/Flat No.', 'restropress'); ?><span class="rpress-required-indicator">*</span></label>
        <span class="rpress-description">
          <?php _e('Enter your Door/Flat number', 'restropress'); ?>
        </span>
        <input class="rpress-input" type="text" name="rpress_door_flat" id="rpress-door-flat" placeholder="Door/Flat Number" />
    </p>
    <?php endif; ?>
  <?php endif; ?>

  <?php if($enable_landmark): ?>
    <?php if( $delivery_method !== 'pickup') : ?>
    <p id="rpress-landmark">
    <label class="rpress-landmark" for="rpress-landmark"><?php _e('Land Mark', 'restropress') ?><span class="rpress-required-indicator">*</span></label>
    <span class="rpress-description">
        <?php _e('Enter Landmark Near By You', 'restropress'); ?>
    </span>
    <input class="rpress-input" type="text" name="rpress_landmark" id="rpress-landmark" placeholder="Landmark" />
    </p>
    <?php endif; ?>
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
  $enable_phone = rpress_get_option( 'enable_phone' );
  $enable_flat = rpress_get_option( 'enable_door_flat' );
  $enable_landmark = rpress_get_option( 'enable_landmark' );
  $delivery_method = isset( $_COOKIE['deliveryMethod'] ) ? $_COOKIE['deliveryMethod'] : '';

  if( $enable_phone ) :
    $required_fields['rpress_phone'] = array(
      'error_id'      => 'invalid_phone',
      'error_message' =>  __('Please enter a valid Phone number', 'restropress')
      );
  endif;

  if( $enable_flat ) :
    if( $delivery_method !== 'pickup' ) :
      $required_fields['rpress_door_flat'] = array(
        'error_id'          => 'invalid_door_flat',
        'error_message' => __('Please enter your door flat', 'restropress')
      );
    endif;
  endif;

  if( $enable_landmark ):
    if( $delivery_method !== 'pickup' ) :
      $required_fields['rpress_landmark'] = array(
        'error_id'          => 'invalid_landmark',
        'error_message' => __('Please enter landmark', 'restropress')
      );
    endif;
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
          <strong><?php echo __('Phone:', 'restropress'); ?> </strong>
          <?php echo $phone; ?>
        </div>
      <?php endif; ?>

      <?php if( $flat ) : ?>
        <div style="margin-bottom:10px;">
          <strong><?php echo __('Flat:', 'restropress'); ?> </strong>
            <?php echo $flat; ?>
        </div>
      <?php endif; ?>

      <?php if( $landmark) : ?>
        <div style="margin-bottom:10px;">
          <strong><?php echo __('Landmark:', 'restropress'); ?> </strong>
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
  rpress_add_email_tag( 'service_type', 'Service Type', 'rpress_email_tag_service_type' );
  rpress_add_email_tag( 'service_time', 'Service Time', 'rpress_email_tag_service_time' );
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
 * The {service_type} email tag
 */
function rpress_email_tag_service_type( $payment_id ) {
    $payment_data = rpress_get_payment_meta( $payment_id );
    return $payment_data['delivery_type'];
}

/**
 * The {service_time} email tag
 */
function rpress_email_tag_service_time( $payment_id ) {
    $payment_data = rpress_get_payment_meta( $payment_id );
    return $payment_data['delivery_time'];
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

    $deivery_type = !empty( $delivery_type ) ? ucfirst( $delivery_type ) : '-';
    return $deivery_type;
  }
}


/**
 * Get holidays list and disable the dates from calendar
 *
 * @since       1.0.6
 * @return      array | Holiday lists
 */

function rpress_get_holidays_lists() {
  add_filter( 'rp_get_holidays_lists' , function( $data ) {
    print_r($data);
  },10,1);
}


function apply_delivery_fee() {
  $cond = false;
  $delivery_settings = get_option( 'rpress_delivery_fee', array() );

  if( isset( $delivery_settings['enable'] )
  && isset( $delivery_settings['free_delivery_above'] ) ) {

    //Get Cart Subtotal
    $subtotal = rpress_get_cart_subtotal();

    if( $subtotal < $delivery_settings['free_delivery_above'] ) {
      $cond = true;
    }
  }
  return $cond;
}

function get_delivery_fee() {
  if( isset( $_COOKIE['rpress_delivery_price'] ) && $_COOKIE['rpress_delivery_price'] !== '' ) {
    $delivery_fee = $_COOKIE['rpress_delivery_price'];
    return apply_filters( 'rpress_delivery_fee', $delivery_fee );
  }
}


/* Remove View Link From Food Items */
add_filter('post_row_actions','rpress_remove_view_link', 10, 2);

function rpress_remove_view_link($actions, $post){
  if ($post->post_type =="fooditem"){
    unset($actions['view']);
  }
  return $actions;
}

/* Remove View Link From Food Addon Category */
add_filter('addon_category_row_actions','rpress_remove_tax_view_link', 10, 2);

function rpress_remove_tax_view_link($actions, $taxonomy) {
    if( $taxonomy->taxonomy == 'addon_category' ) {
        unset($actions['view']);
    }
    return $actions;
}


/* Remove View Link From Food Category */
add_filter('food-category_row_actions','rpress_remove_food_cat_view_link', 10, 2);

function rpress_remove_food_cat_view_link($actions, $taxonomy) {
  if( $taxonomy->taxonomy == 'food-category' ) {
    unset($actions['view']);
  }
  return $actions;
}


/* Function to check delivery fee addon is enabled so that it would init google map js on popup */
function check_delivery_fee_enabled() {
  $delivery_settings = get_option( 'rpress_delivery_fee', array() );

  $delivery_fee_enable =  isset( $delivery_settings['enable'] ) ? $delivery_settings['enable'] : '';

  $delivery_fee_enable = $delivery_fee_enable ? true : false;

  return apply_filters( 'rpress_delivery_fee_enable', $delivery_fee_enable );
}

function rp_get_store_timings() {
  $current_time = current_time('h:ia');
  $open_time = !empty( rpress_get_option( 'open_time' ) ) ? rpress_get_option( 'open_time' ) : '9:00am';
  
  $close_time = !empty( rpress_get_option( 'close_time' ) ) ? rpress_get_option( 'close_time' ) : '11:30pm';
  $store_times = range( strtotime( $open_time ), strtotime( $close_time ), 30*60 );
  return $store_times;
}

function rp_get_current_time() {
  $current_time = '';
  $timezone = get_option( 'timezone_string' );
  if( !empty( $timezone ) ) {
    $tz = new DateTimeZone( $timezone );
    $dt = new DateTime( "now", $tz );
    $current_time = $dt->format("H:i:s");
  }
  return $current_time;
}