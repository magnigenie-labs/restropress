<?php
/**
 * Misc Functions
 *
 * @package     RPRESS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get Cart Items By Key
 *
 * @since       1.0
 * @param       int | key
 * @return      array | cart items array
 */
function rpress_get_cart_items_by_key( $key ) {
  $cart_items_arr = array();
  if( $key !== '' ) {
    $cart_items = rpress_get_cart_contents();
    if( is_array( $cart_items ) && ! empty( $cart_items ) ) {
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
function rpress_get_cart_item_by_price( $key ) {
  $cart_items_price = array();

  if( $key !== '' ) {
    $cart_items = rpress_get_cart_contents();

    if( is_array( $cart_items ) && ! empty( $cart_items ) ) {
      $items_in_cart = $cart_items[$key];
      if( is_array( $items_in_cart ) ) {
        $item_price = rpress_get_fooditem_price( $items_in_cart['id'] );

        if( $items_in_cart['quantity'] > 0 ) {
          $item_price = $item_price * $items_in_cart['quantity'];
        }
        array_push( $cart_items_price, $item_price );

        if( isset( $items_in_cart['addon_items'] ) && is_array( $items_in_cart['addon_items'] ) ) {
          foreach( $items_in_cart['addon_items'] as $item_list ) {
            array_push( $cart_items_price, $item_list['price'] );
          }
        }

      }
    }
  }

  $cart_item_total = array_sum( $cart_items_price );
  return $cart_item_total;
}

function addon_category_taxonomy_custom_fields($tag) {

  $t_id        = $tag->term_id;
  $addon_type  = get_term_meta( $t_id, '_type', true );
  $addon_type  = empty( $addon_type ) ? 'multiple' : $addon_type;
  $addon_price = ! empty( get_term_meta( $t_id, '_price', true ) ) ? rpress_sanitize_amount( get_term_meta( $t_id, '_price', true ) ): '';
?>
<?php if( $tag->parent != 0 ): ?>
<tr class="form-field">
  <th scope="row" valign="top">
    <label for="price_id"><?php esc_html_e( 'Price', 'restropress' ); ?></label>
  </th>
  <td>
    <input type="number" min="0" step=".01" name="addon_meta[price]" id="addon_meta[price]" size="25" style="width:15%;" value="<?php echo $addon_price; ?>"><br />
    <span class="description"><?php esc_html_e( 'Price for this addon item', 'restropress' ); ?></span>
  </td>
</tr>
<?php endif; ?>

<?php if( $tag->parent == 0 ): ?>
<tr class="form-field">
  <th scope="row" valign="top">
    <label for="addon-type">
      <?php esc_html_e( 'Addon item selection type', 'restropress' ); ?></label>
  </th>
  <td>
    <div class="addon-type-wrap">
      <label for="addon-type-single">
        <input id="addon-type-single" type="radio" value="single" name="addon_meta[type]" <?php checked( $addon_type, 'single' ); ?> >
          <?php esc_html_e( 'Single item', 'restropress' ); ?>
      </label>
      <br/><br/>
      <label for="addon-type-multiple">
        <input id="addon-type-multiple" type="radio" value="multiple" name="addon_meta[type]" <?php checked( $addon_type, 'multiple' ); ?> >
          <?php esc_html_e( 'Multiple Items', 'restropress' ); ?>
      </label>
    </div>
  </td>
</tr>
<?php endif; ?>

<?php
}

/**
 * Update addon metadata
 *
 * @since       1.0
 * @param       int | term_id
 * @return      null
 */
function save_addon_category_custom_fields( $term_id ) {
  if( isset( $_POST['addon_meta'] ) ) {

    if( ! empty( $_POST['addon_meta']['type'] ) )
      update_term_meta( $term_id, '_type', sanitize_text_field( $_POST['addon_meta']['type'] ) );
    
    if( ! empty( $_POST['addon_meta']['price'] ) )
      update_term_meta( $term_id, '_price', sanitize_text_field( $_POST['addon_meta']['price'] ) );
  }
}

// Add the fields to the "addon_category" taxonomy, using our callback function
add_action( 'addon_category_edit_form_fields', 'addon_category_taxonomy_custom_fields', 10, 2 );

// Save the changes made on the "addon_category" taxonomy, using our callback function
add_action( 'edited_addon_category', 'save_addon_category_custom_fields', 10, 2 );

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

if( ! function_exists( 'rpress_popup' ) ) {
  function rpress_popup() {
    rpress_get_template_part( 'rpress', 'popup' );
  }
}


add_action( 'rp_get_categories', 'get_fooditems_categories' );

if ( ! function_exists( 'get_fooditems_categories' ) ) {
  function get_fooditems_categories( $params ){
    global $data;
    $data = $params;
    rpress_get_template_part( 'rpress', 'get-categories' );
  }
}

if ( ! function_exists( 'rpress_search_form' ) ) {
  function rpress_search_form() {
    ?>
    <div class="rpress-search-wrap rpress-live-search">
      <input id="rpress-food-search" type="text" placeholder="<?php esc_html_e( 'Search Item', 'restropress' ); ?>">
    </div>
    <?php
  }
}

add_action( 'before_fooditems_list', 'rpress_search_form' );

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

  if( is_array( $items ) ) {
    if( isset( $items['options'] ) ) {
      $instruction = $items['options']['instruction'];
    } else {
      if( isset( $items['instruction'] ) ) {
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
  $instruction = '';
  if( $cart_key !== '' ) {
    $cart_items = rpress_get_cart_contents();
    $cart_items = $cart_items[ $cart_key ];
    if( isset( $cart_items['instruction'] ) ) {
      $instruction = ! empty( $cart_items['instruction'] ) ? $cart_items['instruction'] : '';
    }
  }
  return $instruction;
}

/**
 * Show delivery options in the cart
 *
 * @since       1.0.2
 * @param       void
 * @return      string | Outputs the html for the delivery options with texts
 */
function get_delivery_options( $changeble ) {

  $service_date = isset( $_COOKIE['delivery_date'] ) ? sanitize_text_field( $_COOKIE['delivery_date'] ) : '';
  ob_start();
  ?>
  <div class="delivery-wrap">
    <div class="delivery-opts">
      <?php if ( ! empty( $_COOKIE['service_type'] ) ) : ?>
        <span class="delMethod"><?php echo rpress_service_label( sanitize_text_field( $_COOKIE['service_type'] ) ) . ', ' . $service_date; ?></span><?php if( ! empty( $_COOKIE['service_time'] ) ) : ?><span class="delTime"><?php printf( __( ', %s', 'restropress' ), sanitize_text_field( $_COOKIE['service_time_text'] ) ); ?></span><?php endif; ?>
      <?php endif; ?>
    </div>
    <?php if( $changeble && ! empty( $_COOKIE['service_type'] ) ) : ?>
      <a href="#" class="delivery-change">
        <span class="rp-ajax-toggle-text">
          <?php esc_html_e( 'Change?', 'restropress' ); ?>
        </span>
      </a>
    <?php endif; ?>
  </div>
  <?php
  $data = ob_get_contents();
  ob_get_clean();
  return $data;
}

/**
 * Stores delivery address meta
 *
 * @since       1.0.3
 * @param       array | Delivery address meta array
 * @return      array | Custom data with delivery address meta array
 */
function rpress_store_custom_fields( $delivery_address_meta ) {
  $delivery_address_meta['address']   = ! empty( $_POST['rpress_street_address'] ) ? sanitize_text_field( $_POST['rpress_street_address'] ) : '';
  $delivery_address_meta['flat']      = ! empty( $_POST['rpress_apt_suite'] ) ? sanitize_text_field( $_POST['rpress_apt_suite'] ) : '';
  $delivery_address_meta['city']      = ! empty( $_POST['rpress_city'] ) ? sanitize_text_field( $_POST['rpress_city'] ) : '';
  $delivery_address_meta['postcode']  = ! empty( $_POST['rpress_postcode'] ) ? sanitize_text_field( $_POST['rpress_postcode'] ) : '';
  return $delivery_address_meta;
}
add_filter( 'rpress_delivery_address_meta', 'rpress_store_custom_fields' );


/**
* Add order note to the order
*/
add_filter( 'rpress_order_note_meta', 'rpress_order_note_fields' );
function rpress_order_note_fields( $order_note ) {
  $order_note = isset( $_POST['rpress_order_note'] ) ? sanitize_text_field( $_POST['rpress_order_note'] ) : '';
  return $order_note;
}

/**
* Add phone number to payment meta
*/
add_filter( 'rpress_payment_meta', 'rpress_add_phone' );
function rpress_add_phone( $payment_meta ) {
  if( ! empty( $_POST['rpress_phone'] ) )
    $payment_meta['phone']  = sanitize_text_field( $_POST['rpress_phone'] );
  return $payment_meta;
}

/**
 * Get Service type
 *
 * @since       1.0.4
 * @param       Int | Payment_id
 * @return      string | Service type string
 */
function rpress_get_service_type( $payment_id ) {
  if( $payment_id  ) {
    $service_type = get_post_meta( $payment_id, '_rpress_delivery_type', true );
    return strtolower( $service_type );
  }
}

/* Remove View Link From Food Items */
add_filter( 'post_row_actions','rpress_remove_view_link', 10, 2 );

function rpress_remove_view_link( $actions, $post ){
  if ( $post->post_type =="fooditem" ){
    unset( $actions['view'] );
  }
  return $actions;
}

/* Remove View Link From Food Addon Category */
add_filter( 'addon_category_row_actions','rpress_remove_tax_view_link', 10, 2 );

function rpress_remove_tax_view_link( $actions, $taxonomy ) {
    if( $taxonomy->taxonomy == 'addon_category' ) {
        unset( $actions['view'] );
    }
    return $actions;
}

/* Remove View Link From Food Category */
add_filter( 'food-category_row_actions','rpress_remove_food_cat_view_link', 10, 2 );

function rpress_remove_food_cat_view_link( $actions, $taxonomy ) {
  if( $taxonomy->taxonomy == 'food-category' ) {
    unset( $actions['view'] );
  }
  return $actions;
}

/**
 * Get store timings for the store
 *
 * @since       1.0.0
 * @return      array | store timings
 */
function rp_get_store_timings( $hide_past_time = true, $service_type = null) {
  $current_time = current_time( 'timestamp' );
  $prep_time    = ! empty( rpress_get_option( 'prep_time' ) ) ? rpress_get_option( 'prep_time' ) : 30;
  $open_time    = ! empty( rpress_get_option( 'open_time' ) ) ? rpress_get_option( 'open_time' ) : '9:00am';
  $close_time   = ! empty( rpress_get_option( 'close_time' ) ) ? rpress_get_option( 'close_time' ) : '11:30pm';
  $time_interval = apply_filters( 'rp_store_time_interval', '30', $service_type );
  $time_interval = $time_interval * 60;
  $prep_time  = $prep_time * 60;
  $open_time  = strtotime( date_i18n( 'Y-m-d' ) . ' ' . $open_time );
  $close_time = strtotime( date_i18n( 'Y-m-d' ) . ' ' . $close_time );
  $time_today = apply_filters( 'rpress_timing_for_today', true );
  $store_times = range( $open_time, $close_time, $time_interval );
  //If not today then return normal time
  if( !$time_today ) return $store_times;
  //Add prep time to current time to determine the time to display for the dropdown
  if( $prep_time > 0 ) {
    $current_time = $current_time + $prep_time;
  }
  //Store timings for today.
  $store_timings = [];
  foreach( $store_times as $store_time ) {
    if( $hide_past_time ) {
      if( $store_time > $current_time ) {
        $store_timings[] = $store_time;
      }
    } else {
      $store_timings[] = $store_time;
    }
  }
  return $store_timings;
}
/**
 * Get current time
 *
 * @since       1.0.0
 * @return      string | current time
 */
function rp_get_current_time() {
  $current_time = '';
  $timezone = get_option( 'timezone_string' );
  if( ! empty( $timezone ) ) {
    $tz = new DateTimeZone( $timezone );
    $dt = new DateTime( "now", $tz );
    $current_time = $dt->format( "H:i:s" );
  }
  return $current_time;
}

/**
 * Get current date
 *
 * @since       1.0.0
 * @return      string | current date
 */
function rp_current_date( $format = '' ) {
  $date_format  = empty( $format ) ? get_option( 'date_format' ) : $format;
  $date_i18n = date_i18n( $date_format );
  return apply_filters( 'rpress_current_date', $date_i18n );
}

/**
 * Get local date from date string
 *
 * @since       1.0.0
 * @return      string | localized date based on date string
 */
function rpress_local_date( $date ) {
  $date_format = apply_filters( 'rpress_date_format', get_option( 'date_format', true ) );
  $timestamp  = strtotime( $date );
  $local_date = empty( get_option( 'timezone_string' ) ) ? date_i18n( $date_format, $timestamp ) : wp_date( $date_format, $timestamp );
  return apply_filters( 'rpress_local_date', $local_date, $date );
}

/**
 * Get list of categories
 *
 * @since 2.2.4
 * @return array of categories
 */
function rpress_get_categories( $params = array() ) {

  if( ! empty( $params['ids'] ) ) {
    $params['include'] = $params['ids'];
    $params['orderby'] = 'include';
  }

  unset( $params['ids'] );

  $defaults = array(
    'taxonomy'    => 'food-category',
    'hide_empty'  => true,
    'orderby'     => 'name',
    'order'       => 'ASC',
  );
  $term_args = wp_parse_args( $params, $defaults );
  $term_args = apply_filters( 'rpress_get_categories', $term_args );
  $get_all_items = get_terms( $term_args );

  return $get_all_items;
}

function rpress_get_service_types() {
  $service_types = array(
    'delivery'  => __( 'Delivery', 'restropress' ),
    'pickup'    => __( 'Pickup', 'restropress' )
  );
  return apply_filters( 'rpress_service_type', $service_types );
}

/**
* Get Store service hours
* @since 3.0
* @param string $service_type Select service type
* @param bool $current_time_aware if current_time_aware is set true then it would show the next time from now otherwise it would show the default store timings
* @return store time
*/
function rp_get_store_service_hours( $service_type, $current_time_aware = true, $selected_time = null ,$asap_option = null ) {
  if ( empty( $service_type ) ) {
    return;
  }

  $time_format = get_option( 'time_format', true );
  $time_format = apply_filters( 'rp_store_time_format', $time_format );

  $current_time = ! empty( rp_get_current_time() ) ? rp_get_current_time() : date( $time_format );
  $store_times = rp_get_store_timings( false, $service_type );
  $asap_option = rpress_get_option('enable_asap_option', '');
  if ( $service_type == 'delivery' ) {
    $store_timings = apply_filters( 'rpress_store_delivery_timings', $store_times );
  } else {
    $store_timings = apply_filters( 'rpress_store_pickup_timings', $store_times );
  }

  $store_timings_for_today = apply_filters( 'rpress_timing_for_today', true );

  if( is_array( $store_timings ) ) {

    foreach( $store_timings as $key => $time ) {

      // Bring both curent time and Selected time to Admin Time Format
      $store_time = date( $time_format, $time );
      $selected_time = date( $time_format, strtotime( $selected_time ) );
      $asap_option = rpress_get_option('enable_asap_option', '');
      if ( $store_timings_for_today ) {

        // Remove any extra space in Current Time and Selected Time
        $timing_slug = str_replace( ' ', '', $store_time );
        $selected_time = str_replace( ' ', '', $selected_time );

        if( $current_time_aware ) {

          if ( strtotime( $store_time ) > strtotime( $current_time ) ) { ?>

            <option <?php selected( $selected_time, $timing_slug, $asap_option ); ?> value='<?php echo ( $asap_option && $key == 0 ) ? __( 'ASAP' , 'restropress' ) : $store_time; ?>'>
              <?php echo ( $asap_option && $key == 0 ) ? __( 'ASAP' , 'restropress' ) : $store_time; ?>
            </option>

          <?php }

        } else { ?>

          <option <?php selected( $selected_time, $timing_slug, $asap_option ); ?> value='<?php echo ( $asap_option && $key == 0 ) ? __( 'ASAP' , 'restropress' ) : $store_time; ?>'>
              <?php echo ( $asap_option && $key == 0 ) ? __( 'ASAP' , 'restropress' ) : $store_time; ?>
            </option>

        <?php }
      }
    }
  }
}

/**
 * Get list of categories/subcategories
 *
 * @since 2.3
 * @return array of Get list of categories/subcategories
 */
function rpress_get_child_cats( $category ) {
  $taxonomy_name = 'food-category';
  $parent_term = $category[0];
  $get_child_terms = get_terms( $taxonomy_name,
      ['child_of'=> $parent_term ] );

  if ( empty( $get_child_terms ) ) {
    $parent_terms = array(
      'taxonomy'    => $taxonomy_name,
      'hide_empty'  => true,
      'include'     => $category,
    );

    $get_child_terms = get_terms( $parent_terms );
  }
  return $get_child_terms;
}

add_filter( 'post_updated_messages', 'rpress_fooditem_update_messages' );
function rpress_fooditem_update_messages( $messages ) {
  global $post, $post_ID;

  $post_types = get_post_types( array( 'show_ui' => true, '_builtin' => false ), 'objects' );

  foreach( $post_types as $post_type => $post_object ) {
    if ( $post_type == 'fooditem' ) {
      $messages[$post_type] = array(
        0  => '', // Unused. Messages start at index 1.
        1  => sprintf( __( '%s updated.' ), $post_object->labels->singular_name ),
        2  => __( 'Custom field updated.' ),
        3  => __( 'Custom field deleted.' ),
        4  => sprintf( __( '%s updated.' ), $post_object->labels->singular_name ),
        5  => isset( $_GET['revision']) ? sprintf( __( '%s restored to revision from %s' ), $post_object->labels->singular_name, wp_post_revision_title( (int) sanitize_text_field( $_GET['revision'] ), false ) ) : false,
        6  => sprintf( __( '%s published.' ), $post_object->labels->singular_name ),
        7  => sprintf( __( '%s saved.' ), $post_object->labels->singular_name ),
        8  => sprintf( __( '%s submitted'), $post_object->labels->singular_name),
        9  => sprintf( __( '%s scheduled for: <strong>%1$s</strong>'), $post_object->labels->singular_name, date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), $post_object->labels->singular_name ),
        10 => sprintf( __( '%s draft updated.'), $post_object->labels->singular_name ),
        );
    }
  }

  return $messages;

}

/**
 * Return the html selected attribute if stringified $value is found in array of stringified $options
 * or if stringified $value is the same as scalar stringified $options.
 *
 * @param string|int       $value   Value to find within options.
 * @param string|int|array $options Options to go through when looking for value.
 * @return string
 */
function rp_selected( $value, $options ) {
  if ( is_array( $options ) ) {
    $options = array_map( 'strval', $options );
    return selected( in_array( (string) $value, $options, true ), true, false );
  }
  return selected( $value, $options, false );
}


/**
 * Return the currently selected service type
 *
 * @since       2.5
 * @param       string | type
 * @return      string | Currently selected service type
 */
function rpress_selected_service( $type = '' ) {
  $service_type = isset( $_COOKIE['service_type'] ) ? sanitize_text_field( $_COOKIE['service_type'] ) : '';
  //Return service type label when $type is label
  if( $type == 'label' )
    $service_type = rpress_service_label( $service_type );

  return $service_type;
}

/**
 * Return the service type label based on the service slug.
 *
 * @since       2.5
 * @param       string | service type
 * @return      string | Service type label
 */
function rpress_service_label( $service ) {
  $service_types = array(
    'delivery'  => __( 'Delivery', 'restropress' ),
    'pickup'    => __( 'Pickup', 'restropress' ),
  );
  //Allow to filter the service types.
  $service_types = apply_filters( 'rpress_service_types', $service_types );

  //Check for the service key in the service types and return the service type label
  if( array_key_exists( $service, $service_types ) )
    $service = $service_types[$service];

  return $service;
}

/**
 * Save order type in session
 *
 * @since       1.0.4
 * @param       string | Delivery Type
 * @param           string | Delivery Time
 * @return      array  | Session array for delivery type and delivery time
 */
function rpress_checkout_delivery_type( $service_type, $service_time ) {

  $_COOKIE['service_type'] = $service_type;
  $_COOKIE['service_time'] = $service_time;
}

/**
 * Validates the cart before checkout
 *
 * @since       2.5
 * @param       void
 * @return      array | Respose as success/error
 */
function rpress_pre_validate_order(){

  $service_type   = ! empty( $_COOKIE['service_type'] ) ? sanitize_text_field( $_COOKIE['service_type'] )  : '';
  $service_time   = ! empty( $_COOKIE['service_time'] ) ? sanitize_text_field( $_COOKIE['service_time'] ) : '';
  $service_date   = ! empty( $_COOKIE['service_date'] ) ? sanitize_text_field( $_COOKIE['service_date'] ) : current_time( 'Y-m-d' );
  $prep_time      = rpress_get_option( 'prep_time', 0 );
  $prep_time      = $prep_time * 60;
  $current_time   = current_time( 'timestamp' );

  if( $prep_time > 0 ) {
    $current_time = $current_time + $prep_time;
  }

  $service_time = strtotime( $service_date . ' ' . $service_time );

  // Check minimum order
  $enable_minimum_order = rpress_get_option( 'allow_minimum_order' );
  $minimum_order_price_delivery = rpress_get_option( 'minimum_order_price' );
  $minimum_order_price_delivery = floatval( $minimum_order_price_delivery );
  $minimum_order_price_pickup = rpress_get_option( 'minimum_order_price_pickup' );
  $minimum_order_price_pickup = floatval( $minimum_order_price_pickup );
  $allow_asap_delivery = rpress_get_option( 'enable_asap_option','' );

  if( $enable_minimum_order && $service_type == 'delivery' && rpress_get_cart_subtotal() < $minimum_order_price_delivery ) {
    $minimum_price_error = rpress_get_option( 'minimum_order_error' );
    $minimum_order_formatted = rpress_currency_filter( rpress_format_amount( $minimum_order_price_delivery ) );
    $minimum_price_error = str_replace( '{min_order_price}', $minimum_order_formatted, $minimum_price_error );
    $response = array( 'status' => 'error', 'minimum_price' => $minimum_order_price, 'error_msg' =>  $minimum_price_error  );
  } else if( $enable_minimum_order && $service_type == 'pickup' && rpress_get_cart_subtotal() < $minimum_order_price_pickup ) {
    $minimum_price_error_pickup = rpress_get_option( 'minimum_order_error_pickup' );
    $minimum_order_formatted = rpress_currency_filter( rpress_format_amount( $minimum_order_price_pickup ) );
    $minimum_price_error_pickup = str_replace( '{min_order_price}', $minimum_order_formatted, $minimum_price_error_pickup );
    $response = array( 'status' => 'error', 'minimum_price' => $minimum_order_price_pickup, 'error_msg' =>  $minimum_price_error_pickup  );
   } 

    else if( $current_time > $service_time && ! empty( sanitize_text_field( $_COOKIE['service_time'] ) ) && $current_time == $allow_asap_delivery ){
    $time_error = __( 'Please select a different time slot.', 'restropress' );
    $response = array(
      'status' => 'error',
      'error_msg' =>  $time_error
    );
  } 
    else {
    $response = array( 'status' => 'success' );
  }
  return $response;
}

/**
 * Is Test Mode
 *
 * @since 1.0
 * @return bool $ret True if test mode is enabled, false otherwise
 */
function rpress_is_test_mode() {
  $ret = rpress_get_option( 'test_mode', false );
  return (bool) apply_filters( 'rpress_is_test_mode', $ret );
}

/**
 * Is Debug Mode
 *
 * @since 1.0
 * @return bool $ret True if debug mode is enabled, false otherwise
 */
function rpress_is_debug_mode() {
  $ret = rpress_get_option( 'debug_mode', false );
  if( defined( 'RPRESS_DEBUG_MODE' ) && RPRESS_DEBUG_MODE ) {
    $ret = true;
  }
  return (bool) apply_filters( 'rpress_is_debug_mode', $ret );
}

/**
 * Checks if Guest checkout is enabled
 *
 * @since 1.0
 * @return bool $ret True if guest checkout is enabled, false otherwise
 */
function rpress_no_guest_checkout() {
  $login_method = rpress_get_option( 'login_method', 'login_guest' );
  $ret = $login_method == 'login_only' ? true : false;
  return (bool) apply_filters( 'rpress_no_guest_checkout', $ret );
}

/**
 * Redirect to checkout immediately after adding items to the cart?
 *
 * @since 1.0.0
 * @return bool $ret True is redirect is enabled, false otherwise
 */
function rpress_straight_to_checkout() {
  $ret = rpress_get_option( 'redirect_on_add', false );
  return (bool) apply_filters( 'rpress_straight_to_checkout', $ret );
}

/**
 * Verify credit card numbers live?
 *
 * @since  1.0.0
 * @return bool $ret True is verify credit cards is live
 */
function rpress_is_cc_verify_enabled() {
  $ret = true;

  /*
   * Enable if use a single gateway other than PayPal or Manual. We have to assume it accepts credit cards
   * Enable if using more than one gateway if they aren't both PayPal and manual, again assuming credit card usage
   */

  $gateways = rpress_get_enabled_payment_gateways();

  if ( count( $gateways ) == 1 && ! isset( $gateways['paypal'] ) && ! isset( $gateways['manual'] ) ) {
    $ret = true;
  } else if ( count( $gateways ) == 1 ) {
    $ret = false;
  } else if ( count( $gateways ) == 2 && isset( $gateways['paypal'] ) && isset( $gateways['manual'] ) ) {
    $ret = false;
  }

  return (bool) apply_filters( 'rpress_verify_credit_cards', $ret );
}

/**
 * Check if the current page is a RestroPress Page or not
 */
function is_restropress_page() {

  global $post;

  $rp_page = false;
  $menu_page = rpress_get_option( 'food_items_page', '' );

  if ( is_object( $post ) ) {
    if ( $post->ID == $menu_page ) {
      $rp_page = true;
    } else if ( has_shortcode( $post->post_content, 'fooditems' ) ) {
      $rp_page = true;
    } else if ( has_shortcode( $post->post_content, 'fooditem_checkout' ) ) {
      $rp_page = true;
    } else if ( has_shortcode( $post->post_content, 'fooditem_cart' ) ) {
      $rp_page = true;
    } else if ( has_shortcode( $post->post_content, 'order_history' ) ) {
      $rp_page = true;
    } else if ( has_shortcode( $post->post_content, 'rpress_receipt' ) ) {
      $rp_page = true;
    }
  }

  return apply_filters( 'is_a_restropress_page', $rp_page );
}

/**
 * Is Odd
 *
 * Checks whether an integer is odd.
 *
 * @since 1.0
 * @param int     $int The integer to check
 * @return bool Is the integer odd?
 */
function rpress_is_odd( $int ) {
  return (bool) ( $int & 1 );
}

/**
 * Get File Extension
 *
 * Returns the file extension of a filename.
 *
 * @since 1.0
 *
 * @param unknown $str File name
 *
 * @return mixed File extension
 */
function rpress_get_file_extension( $str ) {
  $parts = explode( '.', $str );
  return end( $parts );
}

/**
 * Checks if the string (filename) provided is an image URL
 *
 * @since 1.0
 * @param string  $str Filename
 * @return bool Whether or not the filename is an image
 */
function rpress_string_is_image_url( $str ) {
  $ext = rpress_get_file_extension( $str );

  switch ( strtolower( $ext ) ) {
    case 'jpg';
      $return = true;
      break;
    case 'png';
      $return = true;
      break;
    case 'gif';
      $return = true;
      break;
    default:
      $return = false;
      break;
  }

  return (bool) apply_filters( 'rpress_string_is_image', $return, $str );
}

/**
 * Get User IP
 *
 * Returns the IP address of the current visitor
 *
 * @since 1.0
 * @return string $ip User's IP address
 */
function rpress_get_ip() {

  $ip = '127.0.0.1';

  if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
    //check ip from share internet
    $ip = $_SERVER['HTTP_CLIENT_IP'];
  } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
    //to check ip is pass from proxy
    // can include more than 1 ip, first is the public one
    $ip = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
    $ip = trim($ip[0]);
  } elseif( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
    $ip = $_SERVER['REMOTE_ADDR'];
  }

  // Fix potential CSV returned from $_SERVER variables
  $ip_array = explode( ',', $ip );
  $ip_array = array_map( 'trim', $ip_array );

  return apply_filters( 'rpress_get_ip', $ip_array[0] );
}


/**
 * Get user host
 *
 * Returns the webhost this site is using if possible
 *
 * @since  1.0.0
 * @return mixed string $host if detected, false otherwise
 */
function rpress_get_host() {
  $host = false;

  if( defined( 'WPE_APIKEY' ) ) {
    $host = 'WP Engine';
  } elseif( defined( 'PAGELYBIN' ) ) {
    $host = 'Pagely';
  } elseif( DB_HOST == 'localhost:/tmp/mysql5.sock' ) {
    $host = 'ICDSoft';
  } elseif( DB_HOST == 'mysqlv5' ) {
    $host = 'NetworkSolutions';
  } elseif( strpos( DB_HOST, 'ipagemysql.com' ) !== false ) {
    $host = 'iPage';
  } elseif( strpos( DB_HOST, 'ipowermysql.com' ) !== false ) {
    $host = 'IPower';
  } elseif( strpos( DB_HOST, '.gridserver.com' ) !== false ) {
    $host = 'MediaTemple Grid';
  } elseif( strpos( DB_HOST, '.pair.com' ) !== false ) {
    $host = 'pair Networks';
  } elseif( strpos( DB_HOST, '.stabletransit.com' ) !== false ) {
    $host = 'Rackspace Cloud';
  } elseif( strpos( DB_HOST, '.sysfix.eu' ) !== false ) {
    $host = 'SysFix.eu Power Hosting';
  } elseif( strpos( $_SERVER['SERVER_NAME'], 'Flywheel' ) !== false ) {
    $host = 'Flywheel';
  } else {
    // Adding a general fallback for data gathering
    $host = 'DBH: ' . DB_HOST . ', SRV: ' . $_SERVER['SERVER_NAME'];
  }

  return $host;
}


/**
 * Check site host
 *
 * @since  1.0.0
 * @param $host The host to check
 * @return bool true if host matches, false if not
 */
function rpress_is_host( $host = false ) {

  $return = false;

  if( $host ) {
    $host = str_replace( ' ', '', strtolower( $host ) );

    switch( $host ) {
      case 'wpengine':
        if( defined( 'WPE_APIKEY' ) )
          $return = true;
        break;
      case 'pagely':
        if( defined( 'PAGELYBIN' ) )
          $return = true;
        break;
      case 'icdsoft':
        if( DB_HOST == 'localhost:/tmp/mysql5.sock' )
          $return = true;
        break;
      case 'networksolutions':
        if( DB_HOST == 'mysqlv5' )
          $return = true;
        break;
      case 'ipage':
        if( strpos( DB_HOST, 'ipagemysql.com' ) !== false )
          $return = true;
        break;
      case 'ipower':
        if( strpos( DB_HOST, 'ipowermysql.com' ) !== false )
          $return = true;
        break;
      case 'mediatemplegrid':
        if( strpos( DB_HOST, '.gridserver.com' ) !== false )
          $return = true;
        break;
      case 'pairnetworks':
        if( strpos( DB_HOST, '.pair.com' ) !== false )
          $return = true;
        break;
      case 'rackspacecloud':
        if( strpos( DB_HOST, '.stabletransit.com' ) !== false )
          $return = true;
        break;
      case 'sysfix.eu':
      case 'sysfix.eupowerhosting':
        if( strpos( DB_HOST, '.sysfix.eu' ) !== false )
          $return = true;
        break;
      case 'flywheel':
        if( strpos( $_SERVER['SERVER_NAME'], 'Flywheel' ) !== false )
          $return = true;
        break;
      default:
        $return = false;
    }
  }

  return $return;
}


/**
 * Get Currencies
 *
 * @since 1.0
 * @return array $currencies A list of the available currencies
 */
function rpress_get_currencies() {
  $currencies = array(
    'USD'  => __( 'US Dollars (&#36;)', 'restropress' ),
    'EUR'  => __( 'Euros (&euro;)', 'restropress' ),
    'GBP'  => __( 'Pound Sterling (&pound;)', 'restropress' ),
    'AUD'  => __( 'Australian Dollars (&#36;)', 'restropress' ),
    'BRL'  => __( 'Brazilian Real (R&#36;)', 'restropress' ),
    'CAD'  => __( 'Canadian Dollars (&#36;)', 'restropress' ),
    'CZK'  => __( 'Czech Koruna (Kč)', 'restropress' ),
    'DKK'  => __( 'Danish Krone (kr)', 'restropress' ),
    'HKD'  => __( 'Hong Kong Dollar (&#36;)', 'restropress' ),
    'HUF'  => __( 'Hungarian Forint (Ft)', 'restropress' ),
    'ILS'  => __( 'Israeli Shekel (₪)', 'restropress' ),
    'JPY'  => __( 'Japanese Yen (&yen;)', 'restropress' ),
    'MYR'  => __( 'Malaysian Ringgits (RM)', 'restropress' ),
    'MXN'  => __( 'Mexican Peso (&#36;)', 'restropress' ),
    'NZD'  => __( 'New Zealand Dollar (&#36;)', 'restropress' ),
    'NOK'  => __( 'Norwegian Krone (kr)', 'restropress' ),
    'PKR'  => __( 'Pakistani Rupee (Rs)', 'restropress' ),
    'PHP'  => __( 'Philippine Pesos (₱)', 'restropress' ),
    'PLN'  => __( 'Polish Zloty (zł)', 'restropress' ),
    'SGD'  => __( 'Singapore Dollar (&#36;)', 'restropress' ),
    'SEK'  => __( 'Swedish Krona (kr)', 'restropress' ),
    'CHF'  => __( 'Swiss Franc', 'restropress' ),
    'TWD'  => __( 'Taiwan New Dollars ($)', 'restropress' ),
    'THB'  => __( 'Thai Baht (฿)', 'restropress' ),
    'INR'  => __( 'Indian Rupee (₹)', 'restropress' ),
    'TRY'  => __( 'Turkish Lira (₺)', 'restropress' ),
    'RIAL' => __( 'Iranian Rial (﷼)', 'restropress' ),
    'RUB'  => __( 'Russian Rubles (₽)', 'restropress' ),
    'AOA'  => __( 'Angolan Kwanza (Kz)', 'restropress' ),
    'NGN'  => __( 'Nigerian Naira (&#8358;)', 'restropress' ),
    'AED'  => __( 'UAE Dirham (د.إ)', 'restropress' ),
    'AFN'  => __( 'Afghani (؋)', 'restropress' ),
    'AMD'  => __( 'Netherlands Antillean Guilder (֏)', 'restropress' ),
    'VND'  => __( 'Vietnamese dong (₫)', 'restropress' ),
    'CNY'  => __( 'Renminbi (¥)', 'restropress' ),
    'KRW'  => __( 'South Korean won (₩)', 'restropress' ),
    'BDT'  => __( 'Bangladeshi taka (৳)', 'restropress' ),
    'NPR'  => __( 'Nepalese rupee (Rs)', 'restropress' ),
    'AZN'  => __( 'Azerbaijani manat (₽)', 'restropress'),




  );

  return apply_filters( 'rpress_currencies', $currencies );
}

/**
 * Get the store's set currency
 *
 * @since 1.0
 * @return string The currency code
 */
function rpress_get_currency() {
  $currency = rpress_get_option( 'currency', 'USD' );
  return apply_filters( 'rpress_currency', $currency );
}

/**
 * Given a currency determine the symbol to use. If no currency given, site default is used.
 * If no symbol is determine, the currency string is returned.
 *
 * @since 1.0
 * @param  string $currency The currency string
 * @return string           The symbol to use for the currency
 */
function rpress_currency_symbol( $currency = '' ) {
  if ( empty( $currency ) ) {
    $currency = rpress_get_currency();
  }

  switch ( $currency ) :
    case "GBP" :
      $symbol = '£';
      break;
    case "BRL" :
      $symbol = 'R$';
      break;
    case "EUR" :
      $symbol = '€';
      break;
      case "INR" :
          $symbol = '₹';
          break;
    case "USD" :
    case "AUD" :
    case "NZD" :
    case "CAD" :
    case "HKD" :
    case "MXN" :
    case "SGD" :
    case "TWD" :
      $symbol = '$';
      break;
    case "JPY" :
    case "CNY" :
      $symbol = '¥';
      break;
    case "AOA" :
      $symbol = 'Kz';
      break;
    case "NGN" :
        $symbol = '₦';
        break;
    case "CZK" :
        $symbol = 'Kč';
        break;
    case "HUF" :
        $symbol = 'Ft';
        break;
    case "ILS" :
        $symbol = '₪';
        break;
    case "MYR" :
        $symbol = 'RM';
        break;
    case "NOK" :
    case "SEK" :
        $symbol = 'kr';
        break;
    case "PKR" :
    case "NPR" :
        $symbol = 'Rs';
        break;
    case "PHP" :
        $symbol = '₱';
        break;
    case "PLN" :
        $symbol = 'zł';
        break;
    case "THB" :
        $symbol = '฿';
        break;
    case "RIAL" :
        $symbol = '﷼';
        break;
    case "RUB" :
    case "AZN" :
        $symbol = '₽';
        break;
    case "AED" :
        $symbol = 'د.إ';
        break;
    case "AFN" :
        $symbol = '؋';
        break;
    case "AMD" :
        $symbol = '֏';
        break;
    case "VND" :
        $symbol = '₫';
        break;
    case "KRW" :
        $symbol = '₩';
        break;
    case "BDT" :
        $symbol = '৳';
        break;
    case "TRY" :
        $symbol = '₺';
        break;
    default :
      $symbol = $currency;
      break;
  endswitch;

  return apply_filters( 'rpress_currency_symbol', $symbol, $currency );
}

/**
 * Get the name of a currency
 *
 * @since  1.0.0
 * @param  string $code The currency code
 * @return string The currency's name
 */
function rpress_get_currency_name( $code = 'USD' ) {
  $currencies = rpress_get_currencies();
  $name       = isset( $currencies[ $code ] ) ? $currencies[ $code ] : $code;
  return apply_filters( 'rpress_currency_name', $name );
}

/**
 * Month Num To Name
 *
 * Takes a month number and returns the name three letter name of it.
 *
 * @since 1.0
 *
 * @param integer $n
 * @return string Short month name
 */
function rpress_month_num_to_name( $n ) {
  $timestamp = mktime( 0, 0, 0, $n, 1, 2005 );

  return date_i18n( "M", $timestamp );
}

/**
 * Get PHP Arg Separator Output
 *
 * @since 1.0
 * @return string Arg separator output
 */
function rpress_get_php_arg_separator_output() {
  return ini_get( 'arg_separator.output' );
}

/**
 * Get the current page URL
 *
 * @since 1.0
 * @param  bool   $nocache  If we should bust cache on the returned URL
 * @return string $page_url Current page URL
 */
function rpress_get_current_page_url( $nocache = false ) {

  global $wp;

  if( get_option( 'permalink_structure' ) ) {

    $base = trailingslashit( home_url( $wp->request ) );

  } else {

    $base = add_query_arg( $wp->query_string, '', trailingslashit( home_url( $wp->request ) ) );
    $base = remove_query_arg( array( 'post_type', 'name' ), $base );

  }

  $scheme = is_ssl() ? 'https' : 'http';
  $uri    = set_url_scheme( $base, $scheme );

  if ( is_front_page() ) {
    $uri = home_url( '/' );
  } elseif ( rpress_is_checkout() ) {
    $uri = rpress_get_checkout_uri();
  }

  $uri = apply_filters( 'rpress_get_current_page_url', $uri );

  if ( $nocache ) {
    $uri = rpress_add_cache_busting( $uri );
  }

  return $uri;
}

/**
 * Adds the 'nocache' parameter to the provided URL
 *
 * @since  1.0.0
 * @param  string $url The URL being requested
 * @return string      The URL with cache busting added or not
 */
function rpress_add_cache_busting( $url = '' ) {

  $no_cache_checkout = rpress_get_option( 'no_cache_checkout', false );

  if ( rpress_is_caching_plugin_active() || ( rpress_is_checkout() && $no_cache_checkout ) ) {
    $url = add_query_arg( 'nocache', 'true', $url );
  }

  return $url;
}

/**
 * Marks a function as deprecated and informs when it has been used.
 *
 * There is a hook rpress_deprecated_function_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * This function is to be used in every function that is deprecated.
 *
 * @uses do_action() Calls 'rpress_deprecated_function_run' and passes the function name, what to use instead,
 *   and the version the function was deprecated in.
 * @uses apply_filters() Calls 'rpress_deprecated_function_trigger_error' and expects boolean value of true to do
 *   trigger or false to not trigger error.
 *
 * @param string  $function    The function that was called
 * @param string  $version     The version of RestroPress that deprecated the function
 * @param string  $replacement Optional. The function that should have been called
 * @param array   $backtrace   Optional. Contains stack backtrace of deprecated function
 */
function _rpress_deprecated_function( $function, $version, $replacement = null, $backtrace = null ) {
  do_action( 'rpress_deprecated_function_run', $function, $replacement, $version );

  $show_errors = current_user_can( 'manage_options' );

  // Allow plugin to filter the output error trigger
  if ( WP_DEBUG && apply_filters( 'rpress_deprecated_function_trigger_error', $show_errors ) ) {
    if ( ! is_null( $replacement ) ) {
      trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since RestroPress version %2$s! Use %3$s instead.', 'restropress' ), $function, $version, $replacement ) );
      trigger_error(  print_r( $backtrace, 1 ) ); // Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
      // Alternatively we could dump this to a file.
    } else {
      trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since RestroPress version %2$s with no alternative available.', 'restropress' ), $function, $version ) );
      trigger_error( print_r( $backtrace, 1 ) );// Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
      // Alternatively we could dump this to a file.
    }
  }
}

/**
 * Marks an argument in a function deprecated and informs when it's been used
 *
 * There is a hook rpress_deprecated_argument_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * This function is to be used in every function that has an argument being deprecated.
 *
 * @uses do_action() Calls 'rpress_deprecated_argument_run' and passes the argument, function name, what to use instead,
 *   and the version the function was deprecated in.
 * @uses apply_filters() Calls 'rpress_deprecated_argument_trigger_error' and expects boolean value of true to do
 *   trigger or false to not trigger error.
 *
 * @param string  $argument    The arguemnt that is being deprecated
 * @param string  $function    The function that was called
 * @param string  $version     The version of WordPress that deprecated the function
 * @param string  $replacement Optional. The function that should have been called
 * @param array   $backtrace   Optional. Contains stack backtrace of deprecated function
 */
function _rpress_deprected_argument( $argument, $function, $version, $replacement = null, $backtrace = null ) {
  do_action( 'rpress_deprecated_argument_run', $argument, $function, $replacement, $version );

  $show_errors = current_user_can( 'manage_options' );

  // Allow plugin to filter the output error trigger
  if ( WP_DEBUG && apply_filters( 'rpress_deprecated_argument_trigger_error', $show_errors ) ) {
    if ( ! is_null( $replacement ) ) {
      trigger_error( sprintf( __( 'The %1$s argument of %2$s is <strong>deprecated</strong> since RestroPress version %3$s! Please use %4$s instead.', 'restropress' ), $argument, $function, $version, $replacement ) );
      trigger_error(  print_r( $backtrace, 1 ) ); // Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
      // Alternatively we could dump this to a file.
    } else {
      trigger_error( sprintf( __( 'The %1$s argument of %2$s is <strong>deprecated</strong> since RestroPress version %3$s with no alternative available.', 'restropress' ), $argument, $function, $version ) );
      trigger_error( print_r( $backtrace, 1 ) );// Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
      // Alternatively we could dump this to a file.
    }
  }
}

/**
 * Checks whether function is disabled.
 *
 * @since 1.0.5
 *
 * @param string  $function Name of the function.
 * @return bool Whether or not function is disabled.
 */
function rpress_is_func_disabled( $function ) {
  $disabled = explode( ',',  ini_get( 'disable_functions' ) );

  return in_array( $function, $disabled );
}

/**
 * RPRESS Let To Num
 *
 * Does Size Conversions
 *
 * @since  1.0.0
 * @usedby rpress_settings()
 * @author Chris Christoff
 *
 * @param unknown $v
 * @return int
 */
function rpress_let_to_num( $v ) {
  $l   = substr( $v, -1 );
  $ret = substr( $v, 0, -1 );

  switch ( strtoupper( $l ) ) {
    case 'P': // fall-through
    case 'T': // fall-through
    case 'G': // fall-through
    case 'M': // fall-through
    case 'K': // fall-through
      $ret *= 1024;
      break;
    default:
      break;
  }

  return (int) $ret;
}

/**
 * Retrieve the URL of the symlink directory
 *
 * @since 1.0
 * @return string $url URL of the symlink directory
 */
function rpress_get_symlink_url() {
  $wp_upload_dir = wp_upload_dir();
  wp_mkdir_p( $wp_upload_dir['basedir'] . '/rpress/symlinks' );
  $url = $wp_upload_dir['baseurl'] . '/rpress/symlinks';

  return apply_filters( 'rpress_get_symlink_url', $url );
}

/**
 * Retrieve the absolute path to the symlink directory
 *
 * @since 1.0
 * @return string $path Absolute path to the symlink directory
 */
function rpress_get_symlink_dir() {
  $wp_upload_dir = wp_upload_dir();
  wp_mkdir_p( $wp_upload_dir['basedir'] . '/rpress/symlinks' );
  $path = $wp_upload_dir['basedir'] . '/rpress/symlinks';

  return apply_filters( 'rpress_get_symlink_dir', $path );
}

/**
 * Retrieve the absolute path to the file upload directory without the trailing slash
 *
 * @since 1.0
 * @return string $path Absolute path to the RPRESS upload directory
 */
function rpress_get_upload_dir() {
  $wp_upload_dir = wp_upload_dir();
  wp_mkdir_p( $wp_upload_dir['basedir'] . '/rpress' );
  $path = $wp_upload_dir['basedir'] . '/rpress';

  return apply_filters( 'rpress_get_upload_dir', $path );
}

/**
 * Delete symbolic links after they have been used
 *
 * This function is only intended to be used by WordPress cron.
 *
 * @since 1.0
 * @return void
 */
function rpress_cleanup_file_symlinks() {

  // Bail if not in WordPress cron
  if ( ! rpress_doing_cron() ) {
    return;
  }

  $path = rpress_get_symlink_dir();
  $dir = opendir( $path );

  while ( ( $file = readdir( $dir ) ) !== false ) {
    if ( $file == '.' || $file == '..' )
      continue;

    $transient = get_transient( md5( $file ) );
    if ( $transient === false )
      @unlink( $path . '/' . $file );
  }
}
add_action( 'rpress_cleanup_file_symlinks', 'rpress_cleanup_file_symlinks' );

/**
 * Checks if SKUs are enabled
 *
 * @since  1.0.0
 * @author Daniel J Griffiths
 * @return bool $ret True if SKUs are enabled, false otherwise
 */
function rpress_use_skus() {
  $ret = rpress_get_option( 'enable_skus', false );
  return (bool) apply_filters( 'rpress_use_skus', $ret );
}

/**
 * Retrieve timezone
 *
 * @since  1.0.0
 * @return string $timezone The timezone ID
 */
function rpress_get_timezone_id() {

  // if site timezone string exists, return it
  if ( $timezone = get_option( 'timezone_string' ) )
    return $timezone;

  // get UTC offset, if it isn't set return UTC
  if ( ! ( $utc_offset = 3600 * get_option( 'gmt_offset', 0 ) ) )
    return 'UTC';

  // attempt to guess the timezone string from the UTC offset
  $timezone = timezone_name_from_abbr( '', $utc_offset );

  // last try, guess timezone string manually
  if ( $timezone === false ) {

    $is_dst = date( 'I' );

    foreach ( timezone_abbreviations_list() as $abbr ) {
      foreach ( $abbr as $city ) {
        if ( $city['dst'] == $is_dst &&  $city['offset'] == $utc_offset )
          return $city['timezone_id'];
      }
    }
  }

  // fallback
  return 'UTC';
}

/**
 * Given an object or array of objects, convert them to arrays
 *
 * @since 1.0
 * @internal Updated in 2.6
 * @param    object|array $object An object or an array of objects
 * @return   array                An array or array of arrays, converted from the provided object(s)
 */
function rpress_object_to_array( $object = array() ) {

  if ( empty( $object ) || ( ! is_object( $object ) && ! is_array( $object ) ) ) {
    return $object;
  }

  if ( is_array( $object ) ) {
    $return = array();
    foreach ( $object as $item ) {
      if ( $object instanceof RPRESS_Payment ) {
        $return[] = $object->array_convert();
      } else {
        $return[] = rpress_object_to_array( $item );
      }

    }
  } else {
    if ( $object instanceof RPRESS_Payment ) {
      $return = $object->array_convert();
    } else {
      $return = get_object_vars( $object );

      // Now look at the items that came back and convert any nested objects to arrays
      foreach ( $return as $key => $value ) {
        $value = ( is_array( $value ) || is_object( $value ) ) ? rpress_object_to_array( $value ) : $value;
        $return[ $key ] = $value;
      }
    }
  }

  return $return;
}

/**
 * Set Upload Directory
 *
 * Sets the upload dir to rpress. This function is called from
 * rpress_change_fooditems_upload_dir()
 *
 * @since 1.0
 * @return array Upload directory information
 */
function rpress_set_upload_dir( $upload ) {

  // Override the year / month being based on the post publication date, if year/month organization is enabled
  if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
    // Generate the yearly and monthly dirs
    $time = current_time( 'mysql' );
    $y = substr( $time, 0, 4 );
    $m = substr( $time, 5, 2 );
    $upload['subdir'] = "/$y/$m";
  }

  $upload['subdir'] = '/rpress' . $upload['subdir'];
  $upload['path']   = $upload['basedir'] . $upload['subdir'];
  $upload['url']    = $upload['baseurl'] . $upload['subdir'];
  return $upload;
}

/**
 * Check if the upgrade routine has been run for a specific action
 *
 * @since  1.0.0
 * @param  string $upgrade_action The upgrade action to check completion for
 * @return bool                   If the action has been added to the copmleted actions array
 */
function rpress_has_upgrade_completed( $upgrade_action = '' ) {

  if ( empty( $upgrade_action ) ) {
    return false;
  }

  $completed_upgrades = rpress_get_completed_upgrades();

  return in_array( $upgrade_action, $completed_upgrades );

}

/**
 * Get's the array of completed upgrade actions
 *
 * @since  1.0.0
 * @return array The array of completed upgrades
 */
function rpress_get_completed_upgrades() {

  $completed_upgrades = get_option( 'rpress_completed_upgrades' );

  if ( false === $completed_upgrades ) {
    $completed_upgrades = array();
  }

  return $completed_upgrades;

}


if ( ! function_exists( 'cal_days_in_month' ) ) {
  // Fallback in case the calendar extension is not loaded in PHP
  // Only supports Gregorian calendar
  function cal_days_in_month( $calendar, $month, $year ) {
    return date( 't', mktime( 0, 0, 0, $month, 1, $year ) );
  }
}


if ( ! function_exists( 'hash_equals' ) ) :
/**
 * Compare two strings in constant time.
 *
 * This function was added in PHP 5.6.
 * It can leak the length of a string.
 *
 * @since 1.0
 *
 * @param string $a Expected string.
 * @param string $b Actual string.
 * @return bool Whether strings are equal.
 */
function hash_equals( $a, $b ) {
  $a_length = strlen( $a );
  if ( $a_length !== strlen( $b ) ) {
    return false;
  }
  $result = 0;

  // Do not attempt to "optimize" this.
  for ( $i = 0; $i < $a_length; $i++ ) {
    $result |= ord( $a[ $i ] ) ^ ord( $b[ $i ] );
  }

  return $result === 0;
}
endif;

if ( ! function_exists( 'getallheaders' ) ) :

  /**
   * Retrieve all headers
   *
   * Ensure getallheaders function exists in the case we're using nginx
   *
   * @since 1.0
   * @return array
   */
  function getallheaders() {
    $headers = array();
    foreach ( $_SERVER as $name => $value ) {
      if ( substr( $name, 0, 5 ) == 'HTTP_' ) {
        $headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value;
      }
    }
    return $headers;
  }

endif;

/**
 * Determines the receipt visibility status
 *
 * @return bool Whether the receipt is visible or not.
 */
function rpress_can_view_receipt( $payment_key = '' ) {

  $return = false;

  if ( empty( $payment_key ) ) {
    return $return;
  }

  global $rpress_receipt_args;

  $rpress_receipt_args['id'] = rpress_get_purchase_id_by_key( $payment_key );

  $user_id = (int) rpress_get_payment_user_id( $rpress_receipt_args['id'] );

  $payment_meta = rpress_get_payment_meta( $rpress_receipt_args['id'] );

  if ( is_user_logged_in() ) {
    if ( $user_id === (int) get_current_user_id() ) {
      $return = true;
    } elseif ( wp_get_current_user()->user_email === rpress_get_payment_user_email( $rpress_receipt_args['id'] ) ) {
      $return = true;
    } elseif ( current_user_can( 'view_shop_sensitive_data' ) ) {
      $return = true;
    }
  }

  $session = rpress_get_purchase_session();
  if ( ! empty( $session ) && ! is_user_logged_in() ) {
    if ( $session['purchase_key'] === $payment_meta['key'] ) {
      $return = true;
    }
  }

  return (bool) apply_filters( 'rpress_can_view_receipt', $return, $payment_key );
}

/**
 * Given a Payment ID, generate a link to IP address provider (ipinfo.io)
 *
 * @since 1.0
 * @param  int    $payment_id The Payment ID
 * @return string A link to the IP details provider
 */
function rpress_payment_get_ip_address_url( $payment_id ) {

  $payment = new RPRESS_Payment( $payment_id );

  $base_url = 'https://ipinfo.io/';
  $provider_url = '<a href="' . esc_url( $base_url ) . esc_attr( $payment->ip ) . '" target="_blank">' . esc_attr( $payment->ip ) . '</a>';

  return apply_filters( 'rpress_payment_get_ip_address_url', $provider_url, $payment->ip, $payment_id );

}

/**
 * Abstraction for WordPress cron checking, to avoid code duplication.
 *
 * In future versions of RPRESS, this function will be changed to only refer to
 * RPRESS specific cron related jobs. You probably won't want to use it until then.
 *
 * @since 1.0
 *
 * @return boolean
 */
function rpress_doing_cron() {

  // Bail if not doing WordPress cron (>4.8.0)
  if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
    return true;

  // Bail if not doing WordPress cron (<4.8.0)
  } elseif ( defined( 'DOING_CRON' ) && ( true === DOING_CRON ) ) {
    return true;
  }

  // Default to false
  return false;
}

/**
 * Display a RestroPress help tip.
 *
 * @since  3.0
 *
 * @param  string $tip        Help tip text.
 * @param  bool   $allow_html Allow sanitized HTML if true or escape.
 * @return string
 */
function rp_help_tip( $tip, $allow_html = false ) {
  if ( $allow_html ) {
    $tip = rpress_sanitize_tooltip( $tip );
  } else {
    $tip = esc_attr( $tip );
  }

  return '<span class="restropress-help-tip" data-tip="' . $tip . '"></span>';
}

/**
 * Is pickup/delivery time enabled
 *
 * @since 1.0
 * @return bool $ret True if test mode is enabled, false otherwise
 */
function rpress_is_service_enabled( $service ) {
  return (bool) apply_filters( 'rpress_is_service_enabled', true, $service );
}

function rpress_fooditem_available( $fooditem_id ) {
  return (bool) apply_filters( 'rpress_is_orderable', true, $fooditem_id );
}

/** Get Singular Label
 *  @since 2.0.7
 *
 *  @param bool $lowercase
 *  @return string $defaults['singular'] Singular label
 */
function rp_get_label_singular( $lowercase = false ) {
  $defaults = rp_get_default_labels();
  return ( $lowercase ) ? strtolower( $defaults['singular'] ) : $defaults['singular'];
}

/**
 * Get Plural Label
 *
 * @since 1.0
 * @return string $defaults['plural'] Plural label
 */
function rp_get_label_plural( $lowercase = false ) {
  $defaults = rp_get_default_labels();
  return ( $lowercase ) ? strtolower( $defaults['plural'] ) : $defaults['plural'];
}

/**
 * Get Default Labels
 *
 * @since 1.0
 * @return array $defaults Default labels
 */
function rp_get_default_labels() {
  $defaults = array(
     'singular' => __( 'Food Item', 'restropress' ),
     'plural'   => __( 'Food Items','restropress' )
  );
  return apply_filters( 'rp_default_fooditems_name', $defaults );
}

/**
 * Get food type icon from id
 *
 * @since 2.7.2
 * @return string image source for the icon
 */
function rpress_get_fooditem_icon( $id = '' ) {
  if( empty( $id ) ) $id = get_the_ID();
  $food_type = get_post_meta( $id, 'rpress_food_type', true );
  $icon_url = apply_filters( 'rpress_food_type_icon', RP_PLUGIN_URL . 'assets/images/' . $food_type . '.png' );

  if( ! empty( $food_type ) && ! empty( $icon_url ) )
    return '<img src="' . $icon_url . '" class="rpress-food-type-icon">';
}

/**
 * Get defalut checkout fields
 *
 * @since 2.8
 * @return array
 */
function rp_get_checkout_fields() {

  $customer  = RPRESS()->session->get( 'customer' );
  $customer  = wp_parse_args( $customer, array( 'delivery_address' => array(
    'address'   => '',
    'flat'      => '',
    'city'      => '',
    'postcode'  => '',
  ) ) );

  $customer['delivery_address'] = array_map( 'sanitize_text_field', $customer['delivery_address'] );

  if( is_user_logged_in() ) {

    $user_address = get_user_meta( get_current_user_id(), '_rpress_user_delivery_address', true );

    foreach( $customer['delivery_address'] as $key => $field ) {

      if ( empty( $field ) && ! empty( $user_address[ $key ] ) ) {
        $customer['delivery_address'][ $key ] = $user_address[ $key ];
      } else {
        $customer['delivery_address'][ $key ] = '';
      }
    }
  }

  $customer['delivery_address'] = apply_filters( 'rpress_customer_delivery_address', $customer['delivery_address'] );

  $checkout_fields = array(
    'rpress_street_address' => array(
      'id' => 'rpress-street-address',
      'title' => __( 'Street Address', 'restropress' ),
      'is_required' => true,
      'is_hidden' => false,
      'name' => 'rpress_street_address',
      'placeholder' => __( 'Street Address', 'restropress' ),
      'value' => $customer['delivery_address']['address'],
    ),
    'rpress_apt_suite' => array(
      'id' => 'rpress-apt-suite',
      'title' => __( 'Apartment, suite, unit etc. (optional)', 'restropress' ),
      'is_required' => false,
      'is_hidden' => false,
      'name' => 'rpress_apt_suite',
      'placeholder' => __( 'Apartment, suite, unit etc. (optional)', 'restropress' ),
      'value' => $customer['delivery_address']['flat'],
    ),
    'rpress_city' => array(
      'id' => 'rpress_city',
      'title' => __( 'Town / City', 'restropress' ),
      'is_required' => true,
      'is_hidden' => false,
      'name' => 'rpress_city',
      'placeholder' => __( 'Town / City', 'restropress' ),
      'value' => $customer['delivery_address']['city'],
    ),
    'rpress_postcode' => array(
      'id' => 'rpress-postcode',
      'title' => __( 'Postcode / ZIP', 'restropress' ),
      'is_required' => true,
      'is_hidden' => false,
      'name' => 'rpress_postcode',
      'placeholder' => __( 'Postcode / ZIP', 'restropress' ),
      'value' => $customer['delivery_address']['postcode'],
    ),
  );

  return apply_filters( 'rpress_checkout_fields', $checkout_fields, $customer['delivery_address'] );
}

/**
 *  Get order count based on order status
 *  @since 2.8.4
 *
 *  @param string $status
 *  @return int $order_count 
 */
function rp_get_order_count( $status = 'pending' ) {
  global $wpdb;
  
  $query = $wpdb->prepare( "SELECT count(*) as count
  FROM {$wpdb->postmeta}
  WHERE `meta_key` = '_order_status'
  AND `meta_value` = '%s'
  GROUP BY meta_value", $status );

  $order_count = $wpdb->get_var( $query );

  return apply_filters( 'rpress_order_count', $order_count, $status );
}