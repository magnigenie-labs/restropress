<?php
/**
 * Shortcodes
 *
 * @package     RPRESS
 * @subpackage  Shortcodes
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Purchase Link Shortcode
 *
 * Retrieves a fooditem and displays the purchase form.
 *
 * @since 1.0
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string Fully formatted purchase link
 */
function rpress_fooditem_shortcode( $atts, $content = null ) {
  global $post;

  $post_id = is_object( $post ) ? $post->ID : 0;

  $atts = shortcode_atts( array(
    'id'          => $post_id,
    'price_id'      => isset( $atts['price_id'] ) ? $atts['price_id'] : false,
    'sku'     => '',
    'price'         => '1',
    'direct'        => '0',
    'text'          => '',
    'style'         => rpress_get_option( 'button_style', 'button' ),
    'color'         => rpress_get_option( 'checkout_color', 'red' ),
    'class'         => 'rpress-submit',
    'form_id'       => ''
  ),
  $atts, 'purchase_link' );

  // Override text only if not provided / empty
  if ( ! $atts['text'] ) {
    if( $atts['direct'] == '1' || $atts['direct'] == 'true' ) {
      $atts['text'] = rpress_get_option( 'buy_now_text', __( 'Buy Now', 'restropress' ) );
    } else {
      $atts['text'] = rpress_get_option( 'add_to_cart_text', __( 'Purchase', 'restropress' ) );
    }
  }

  // Override color if color == inherit
  if( isset( $atts['color'] ) ) {
    $atts['color'] = ( $atts['color'] == 'inherit' ) ? '' : $atts['color'];
  }

  if( ! empty( $atts['sku'] ) ) {

    $fooditem = rpress_get_fooditem_by( 'sku', $atts['sku'] );

    if ( $fooditem ) {
      $atts['fooditem_id'] = $fooditem->ID;
    }

  } elseif( isset( $atts['id'] ) ) {

    // Rpress_get_purchase_link() expects the ID to be fooditem_id since v1.3
    $atts['fooditem_id'] = $atts['id'];

    $fooditem = rpress_get_fooditem( $atts['fooditem_id'] );

  }

  if ( $fooditem ) {
    return rpress_get_purchase_link( $atts );
  }
}
add_shortcode( 'purchase_link', 'rpress_fooditem_shortcode' );

/**
 * Item History Shortcode
 *
 * Displays a user's fooditem history.
 *
 * @since 1.0
 * @return string
 */
function rpress_fooditem_history() {
  if ( is_user_logged_in() ) {
    ob_start();

    if( ! rpress_user_pending_verification() ) {

      rpress_get_template_part( 'history', 'fooditems' );

    } else {

      rpress_get_template_part( 'account', 'pending' );

    }

    return ob_get_clean();
  }
}
add_shortcode( 'fooditem_history', 'rpress_fooditem_history' );

/**
 * Order History Shortcode
 *
 * Displays a user's order history.
 *
 * @since 1.0
 * @return string
 */
function rpress_order_history() {
  ob_start();

  if( ! rpress_user_pending_verification() ) {

    rpress_get_template_part( 'history', 'purchases' );

  } else {

    rpress_get_template_part( 'account', 'pending' );

  }

  return ob_get_clean();
}
add_shortcode( 'order_history', 'rpress_order_history' );

/**
 * Checkout Form Shortcode
 *
 * Show the checkout form.
 *
 * @since 1.0
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string
 */
function rpress_checkout_form_shortcode( $atts, $content = null ) {
  return rpress_checkout_form();
}
add_shortcode( 'fooditem_checkout', 'rpress_checkout_form_shortcode' );

/**
 * Item Cart Shortcode
 *
 * Show the shopping cart.
 *
 * @since 1.0
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string
 */
function rpress_cart_shortcode( $atts, $content = null ) {
  return rpress_shopping_cart();
}
add_shortcode( 'fooditem_cart', 'rpress_cart_shortcode' );

/**
 * Login Shortcode
 *
 * Shows a login form allowing users to users to log in. This function simply
 * calls the rpress_login_form function to display the login form.
 *
 * @since 1.0
 * @param array $atts Shortcode attributes
 * @param string $content
 * @uses rpress_login_form()
 * @return string
 */
function rpress_login_form_shortcode( $atts, $content = null ) {
  $redirect = '';

  extract( shortcode_atts( array(
      'redirect' => $redirect
    ), $atts, 'rpress_login' )
  );

  if ( empty( $redirect ) ) {
    $login_redirect_page = rpress_get_option( 'login_redirect_page', '' );

    if ( ! empty( $login_redirect_page ) ) {
      $redirect = get_permalink( $login_redirect_page );
    }
  }

  if ( empty( $redirect ) ) {
    $order_history = rpress_get_option( 'order_history_page', 0 );

    if ( ! empty( $order_history ) ) {
      $redirect = get_permalink( $order_history );
    }
  }

  if ( empty( $redirect ) ) {
    $redirect = home_url();
  }

  return rpress_login_form( $redirect );
}
add_shortcode( 'rpress_login', 'rpress_login_form_shortcode' );

/**
 * Register Shortcode
 *
 * Shows a registration form allowing users to users to register for the site
 *
 * @since  1.0.0
 * @param array $atts Shortcode attributes
 * @param string $content
 * @uses rpress_register_form()
 * @return string
 */
function rpress_register_form_shortcode( $atts, $content = null ) {
  $redirect         = home_url();
  $order_history = rpress_get_option( 'order_history_page', 0 );

  if ( ! empty( $order_history ) ) {
    $redirect = get_permalink( $order_history );
  }

  extract( shortcode_atts( array(
      'redirect' => $redirect
    ), $atts, 'rpress_register' )
  );
  return rpress_register_form( $redirect );
}
add_shortcode( 'rpress_register', 'rpress_register_form_shortcode' );

/**
 * Discounts shortcode
 *
 * Displays a list of all the active discounts. The active discounts can be configured
 * from the Discount Codes admin screen.
 *
 * @since 1.0
 * @param array $atts Shortcode attributes
 * @param string $content
 * @uses rpress_get_discounts()
 * @return string $discounts_lists List of all the active discount codes
 */
function rpress_discounts_shortcode( $atts, $content = null ) {
  $discounts = rpress_get_discounts();

  $discounts_list = '<ul id="rpress_discounts_list">';

  if ( ! empty( $discounts ) && rpress_has_active_discounts() ) {

    foreach ( $discounts as $discount ) {

      if ( rpress_is_discount_active( $discount->ID ) ) {

        $discounts_list .= '<li class="rpress_discount">';

          $discounts_list .= '<span class="rpress_discount_name">' . rpress_get_discount_code( $discount->ID ) . '</span>';
          $discounts_list .= '<span class="rpress_discount_separator"> - </span>';
          $discounts_list .= '<span class="rpress_discount_amount">' . rpress_format_discount_rate( rpress_get_discount_type( $discount->ID ), rpress_get_discount_amount( $discount->ID ) ) . '</span>';

        $discounts_list .= '</li>';

      }

    }

  } else {
    $discounts_list .= '<li class="rpress_discount">' . __( 'No discounts found', 'restropress' ) . '</li>';
  }

  $discounts_list .= '</ul>';

  return $discounts_list;
}
add_shortcode( 'fooditem_discounts', 'rpress_discounts_shortcode' );

/**
 * Purchase Collection Shortcode
 *
 * Displays a collection purchase link for adding all items in a taxonomy term
 * to the cart.
 *
 * @since 1.0.0
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string
 */
function rpress_purchase_collection_shortcode( $atts, $content = null ) {
  extract( shortcode_atts( array(
      'taxonomy'  => '',
      'terms'   => '',
      'text'    => __('Purchase All Items','restropress' ),
      'style'     => rpress_get_option( 'button_style', 'button' ),
      'color'     => rpress_get_option( 'checkout_color', 'red' ),
      'class'   => 'rpress-submit'
    ), $atts, 'purchase_collection' )
  );

  $button_display = implode( ' ', array( $style, $color, $class ) );

  return '<a href="' . esc_url( add_query_arg( array( 'rpress_action' => 'purchase_collection', 'taxonomy' => $taxonomy, 'terms' => $terms ) ) ) . '" class="' . $button_display . '">' . $text . '</a>';
}
add_shortcode( 'purchase_collection', 'rpress_purchase_collection_shortcode' );

/**
 * RestroPress Shortcode
 *
 * arguments specified when using the shortcode. A list of the arguments
 * can be found from the RPRESS Dccumentation. The shortcode will take all the
 * parameters and display the fooditems queried in a valid HTML <div> tags.
 *
 * @since 1.0.0
 * @internal Incomplete shortcode
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string $display Output generated from the fooditems queried
 */
function rpress_fooditems_tabs( $atts, $content = null ) {

  // Tabs filter to hook custom tabs
  $tabs = apply_filters( 'rpress_product_tabs', array() );

  if ( ! empty( $tabs ) ) : ?>
    <nav>
      <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <?php foreach ( $tabs as $key => $tab ) :
          $active_class = ( esc_attr( $key ) == 'menu') ? 'active' : '';
          ?>
          <a class="nav-item nav-link  <?php echo $active_class; ?>" id="nav-<?php echo esc_attr( $key ); ?>-tab" data-toggle="tab" href="#nav-<?php echo esc_attr( $key ); ?>" role="tab" aria-controls="nav-<?php echo esc_attr( $key ); ?>" aria-selected="true"><?php echo apply_filters( 'rpress_product_' . $key . '_tab_title', esc_html( $tab['title'] ), $key ); ?></a>
        <?php endforeach; ?>
      </div>
    </nav>

    <div class="tab-content" id="nav-tabContent">
      <?php foreach ( $tabs as $key => $tab ) :
        $active_class = ( esc_attr( $key ) == 'menu') ? 'active in' : '';
        ?>
        <div class="tab-pane row fade show <?php echo $active_class; ?>" id="nav-<?php echo esc_attr( $key ); ?>" role="tabpanel" aria-labelledby="nav-<?php echo esc_attr( $key ); ?>-tab">
          <div class="rp-col-md-12 <?php echo esc_attr( $key ); ?>">
            <?php if ( isset( $tab['callback'] ) ) { call_user_func( $tab['callback'], $key, $tab ); } ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
<?php endif;

}

add_shortcode( 'rpress_fooditems', 'rpress_fooditems_tabs' );

add_shortcode( 'fooditems', 'rpress_fooditems_query' );

function rpress_fooditems_query( $atts, $content = null ) {

add_shortcode( 'rpress_fooditems', 'rpress_fooditems_tabs' );

  $color = rpress_get_option( 'checkout_color', 'red' );
  
  $get_all_items = get_terms( array(
    'taxonomy' => 'food-category',
    'hide_empty' => true,
  ));

  $all_terms = $query = array();

  if( is_array( $get_all_items ) && !empty( $get_all_items ) ) {
    $all_terms = wp_list_pluck( $get_all_items, 'slug' );
  }

  ob_start();

  rpress_get_template_part( 'rpress', 'before-fooditem' );

  //Get food category items
  do_action( 'rpress_get_food_categories' );

  rpress_get_template_part( 'rpress', 'before-fooditem-container' );

  //Restropress search form
  echo apply_filters( 'rpress_search_form', rpress_search_form() );

  $fooditems = '';

  $atts = shortcode_atts( array(
    'category'         => '',
    'exclude_category' => '',
    'tags'             => '',
    'exclude_tags'     => '',
    'author'           => false,
    'relation'         => 'OR',
    'number'           => 9,
    'price'            => 'no',
    'excerpt'          => 'yes',
    'full_content'     => 'no',
    'buy_button'       => 'yes',
    'columns'          => 3,
    'thumbnails'       => 'true',
    'orderby'          => 'post_date',
    'order'            => 'DESC',
    'ids'              => '',
    'class'            => '',
    'pagination'       => 'true',
  ), $atts, 'fooditems' );

  if( is_array( $all_terms ) && !empty( $all_terms ) ) :

    foreach( $all_terms as $term_slug ) {

      $atts = shortcode_atts( array(
        'category'         => '',
        'exclude_category' => '',
        'tags'             => '',
        'exclude_tags'     => '',
        'author'           => false,
        'relation'         => 'OR',
        'number'           => 9,
        'price'            => 'no',
        'excerpt'          => 'yes',
        'full_content'     => 'no',
        'buy_button'       => 'yes',
        'columns'          => 3,
        'thumbnails'       => 'true',
        'title'            => 'true',
        'orderby'          => 'post_date',
        'order'            => 'DESC',
        'ids'              => '',
        'posts_per_page'   => -1,
        'class'            => '',
        'pagination'       => 'true',
      ), $atts, 'fooditems' );

      $query = array(
        'post_type'      => 'fooditem',
        'orderby'        => $atts['orderby'],
        'order'          => $atts['order']
      );

      $query['tax_query'][] = array(
        'taxonomy' => 'food-category',
        'field'    => 'slug',
        'terms'    => $term_slug,
      );

      if ( $atts['category'] ) {

        $categories = explode( ',', $atts['category'] );

        $terms_arr = array();

        foreach( $categories as $category ) {

          $t_id  = (int) $category;
          $is_id = is_int( $t_id ) && ! empty( $t_id );

          if( $is_id ) {

            $term_id = $category;

          } else {

            $term = get_term_by( 'slug', $category, 'food-category' );

            if( ! $term ) {
              continue;
            }

            $term_id = $term->term_id;

          }

          array_push($terms_arr, $term_id);

        }

        $query['tax_query'][] = array(
          'taxonomy' => 'food-category',
          'field'    => 'term_id',
          'terms'    => $terms_arr,
        );

      }

      if ( $atts['exclude_category'] ) {

        $categories = explode( ',', $atts['exclude_category'] );

        foreach( $categories as $category ) {

          $t_id  = (int) $category;
          $is_id = is_int( $t_id ) && ! empty( $t_id );

          if( $is_id ) {

            $term_id = $category;

          } else {

            $term = get_term_by( 'slug', $category, 'food-category' );

            if( ! $term ) {
              continue;
            }

            $term_id = $term->term_id;
          }

          $query['tax_query'][] = array(
            'taxonomy' => 'food-category',
            'field'    => 'term_id',
            'terms'    => $term_id,
            'operator' => 'NOT IN'
          );
        }

      }

      if( ! empty( $atts['ids'] ) )
        $query['post__in'] = explode( ',', $atts['ids'] );

      if ( get_query_var( 'paged' ) )
        $query['paged'] = get_query_var('paged');
      else if ( get_query_var( 'page' ) )
        $query['paged'] = get_query_var( 'page' );
      else
        $query['paged'] = 1;

      // Allow the query to be manipulated by other plugins
      $query = apply_filters( 'rpress_fooditems_query', $query, $atts );

      $fooditems = new WP_Query( $query );

      do_action( 'rpress_fooditems_list_before', $atts );

      if ( $fooditems->have_posts() ) :

        $i = 1;
        $columns_class   = array( 'rpress_fooditem_columns_' . $atts['columns'] );
        $custom_classes  = array_filter( explode( ',', $atts['class'] ) );
        $wrapper_classes = array_unique( array_merge( $columns_class, $custom_classes ) );
        $wrapper_classes = implode( ' ', $wrapper_classes );

        do_action( 'rpress_fooditems_list_top', $atts, $fooditems ); 
        
        $var = '';

        while ( $fooditems->have_posts() ) : $fooditems->the_post();
          
          $id = get_the_ID();
          $post_terms = wp_get_post_terms($id, 'food-category');

            do_action( 'rpress_fooditems_category_title',  $id, $var );

            do_action( 'rpress_fooditem_shortcode_item', $atts, $i );

            ?>

            <?php $i++; endwhile; ?>

            <?php wp_reset_postdata(); ?>
            <?php do_action( 'rpress_fooditems_list_bottom', $atts ); ?>

          <?php


          wp_reset_query();
      endif;


    }

    

  else:

    printf( _x( 'No %s found', 'rpress post type name', 'restropress' ), rpress_get_label_plural() );
  endif;


  rpress_get_template_part( 'rpress', 'after-fooditem-container' );

  do_action( 'rpress_fooditems_list_after', $atts, $fooditems );

  do_action( 'rpress_get_cart' );

  rpress_get_template_part( 'rpress', 'after-fooditem' );

  $display = ob_get_clean();

  return apply_filters( 'fooditems_shortcode', $display, $atts, $atts['buy_button'], $atts['columns'], '', $fooditems, $atts['excerpt'], $atts['full_content'], $atts['price'], $atts['thumbnails'], $query );
}

add_shortcode( 'rpress_items', 'get_rpress_fooditems' );

/**
 * Price Shortcode
 *
 * Shows the price of a fooditem.
 *
 * @since 1.0.3
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string
 */
function rpress_fooditem_price_shortcode( $atts, $content = null ) {
  extract( shortcode_atts( array(
      'id' => NULL,
      'price_id' => false,
    ), $atts, 'rpress_price' )
  );

  if ( is_null( $id ) ) {
    $id = get_the_ID();
  }

  return rpress_price( $id, false, $price_id );
}
add_shortcode( 'rpress_price', 'rpress_fooditem_price_shortcode' );

/**
 * Receipt Shortcode
 *
 * Shows an order receipt.
 *
 * @since  1.0.0
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string
 */
function rpress_receipt_shortcode( $atts, $content = null ) {
  global $rpress_receipt_args;

  $rpress_receipt_args = shortcode_atts( array(
    'error'           => __( 'Sorry, trouble retrieving payment receipt.', 'restropress' ),
    'price'           => true,
    'discount'        => true,
    'products'        => true,
    'date'            => true,
    'notes'           => true,
    'payment_key'     => false,
    'payment_method'  => true,
    'payment_id'      => true
  ), $atts, 'rpress_receipt' );

  $session = rpress_get_purchase_session();
  if ( isset( $_GET['payment_key'] ) ) {
    $payment_key = urldecode( $_GET['payment_key'] );
  } else if ( $session ) {
    $payment_key = $session['purchase_key'];
  } elseif ( $rpress_receipt_args['payment_key'] ) {
    $payment_key = $rpress_receipt_args['payment_key'];
  }

  // No key found
  if ( ! isset( $payment_key ) ) {
    return '<p class="rpress-alert rpress-alert-error">' . $rpress_receipt_args['error'] . '</p>';
  }

  $payment_id    = rpress_get_purchase_id_by_key( $payment_key );
  $user_can_view = rpress_can_view_receipt( $payment_key );

  // Key was provided, but user is logged out. Offer them the ability to login and view the receipt
  if ( ! $user_can_view && ! empty( $payment_key ) && ! is_user_logged_in() && ! rpress_is_guest_payment( $payment_id ) ) {
    global $rpress_login_redirect;
    $rpress_login_redirect = rpress_get_current_page_url();

    ob_start();

    echo '<p class="rpress-alert rpress-alert-warn">' . __( 'You must be logged in to view this payment receipt.', 'restropress' ) . '</p>';
    rpress_get_template_part( 'shortcode', 'login' );

    $login_form = ob_get_clean();

    return $login_form;
  }

  $user_can_view = apply_filters( 'rpress_user_can_view_receipt', $user_can_view, $rpress_receipt_args );

  // If this was a guest checkout and the purchase session is empty, output a relevant error message
  if ( empty( $session ) && ! is_user_logged_in() && ! $user_can_view ) {
    return '<p class="rpress-alert rpress-alert-error">' . apply_filters( 'rpress_receipt_guest_error_message', __( 'Receipt could not be retrieved, your purchase session has expired.', 'restropress' ) ) . '</p>';
  }

  /*
   * Check if the user has permission to view the receipt
   *
   * If user is logged in, user ID is compared to user ID of ID stored in payment meta
   *
   * Or if user is logged out and purchase was made as a guest, the purchase session is checked for
   *
   * Or if user is logged in and the user can view sensitive shop data
   *
   */


  if ( ! $user_can_view ) {
    return '<p class="rpress-alert rpress-alert-error">' . $rpress_receipt_args['error'] . '</p>';
  }

  ob_start();

  rpress_get_template_part( 'shortcode', 'receipt' );

  $display = ob_get_clean();

  return $display;
}
add_shortcode( 'rpress_receipt', 'rpress_receipt_shortcode' );

/**
 * Profile Editor Shortcode
 *
 * Outputs the RPRESS Profile Editor to allow users to amend their details from the
 * front-end. This function uses the RPRESS templating system allowing users to
 * override the default profile editor template. The profile editor template is located
 * under templates/profile-editor.php, however, it can be altered by creating a
 * file called profile-editor.php in the rpress_template directory in your active theme's
 * folder. Please visit the RPRESS Documentation for more information on how the
 * templating system is used.
 *
 * @since  1.0.0
 *
 * @author RestroPress
 *
 * @param      $atts Shortcode attributes
 * @param null $content
 * @return string Output generated from the profile editor
 */
function rpress_profile_editor_shortcode( $atts, $content = null ) {
  ob_start();

  if( ! rpress_user_pending_verification() ) {

    rpress_get_template_part( 'shortcode', 'profile-editor' );

  } else {

    rpress_get_template_part( 'account', 'pending' );

  }

  $display = ob_get_clean();

  return $display;
}
add_shortcode( 'rpress_profile_editor', 'rpress_profile_editor_shortcode' );

/**
 * Process Profile Updater Form
 *
 * Processes the profile updater form by updating the necessary fields
 *
 * @since  1.0.0
 * @author RestroPress
 * @param array $data Data sent from the profile editor
 * @return void
 */
function rpress_process_profile_editor_updates( $data ) {
  // Profile field change request
  if ( empty( $_POST['rpress_profile_editor_submit'] ) && !is_user_logged_in() ) {
    return false;
  }

  // Pending users can't edit their profile
  if ( rpress_user_pending_verification() ) {
    return false;
  }

  // Nonce security
  if ( ! wp_verify_nonce( $data['rpress_profile_editor_nonce'], 'rpress-profile-editor-nonce' ) ) {
    return false;
  }

  $user_id       = get_current_user_id();
  $old_user_data = get_userdata( $user_id );

  $display_name = isset( $data['rpress_display_name'] )    ? sanitize_text_field( $data['rpress_display_name'] )    : $old_user_data->display_name;
  $first_name   = isset( $data['rpress_first_name'] )      ? sanitize_text_field( $data['rpress_first_name'] )      : $old_user_data->first_name;
  $last_name    = isset( $data['rpress_last_name'] )       ? sanitize_text_field( $data['rpress_last_name'] )       : $old_user_data->last_name;
  $email        = isset( $data['rpress_email'] )           ? sanitize_email( $data['rpress_email'] )                : $old_user_data->user_email;
  $line1        = isset( $data['rpress_address_line1'] )   ? sanitize_text_field( $data['rpress_address_line1'] )   : '';
  $line2        = isset( $data['rpress_address_line2'] )   ? sanitize_text_field( $data['rpress_address_line2'] )   : '';
  $city         = isset( $data['rpress_address_city'] )    ? sanitize_text_field( $data['rpress_address_city'] )    : '';
  $state        = isset( $data['rpress_address_state'] )   ? sanitize_text_field( $data['rpress_address_state'] )   : '';
  $zip          = isset( $data['rpress_address_zip'] )     ? sanitize_text_field( $data['rpress_address_zip'] )     : '';
  $country      = isset( $data['rpress_address_country'] ) ? sanitize_text_field( $data['rpress_address_country'] ) : '';

  $userdata = array(
    'ID'           => $user_id,
    'first_name'   => $first_name,
    'last_name'    => $last_name,
    'display_name' => $display_name,
    'user_email'   => $email
  );


  $address = array(
    'line1'    => $line1,
    'line2'    => $line2,
    'city'     => $city,
    'state'    => $state,
    'zip'      => $zip,
    'country'  => $country
  );

  do_action( 'rpress_pre_update_user_profile', $user_id, $userdata );

  // New password
  if ( ! empty( $data['rpress_new_user_pass1'] ) ) {
    if ( $data['rpress_new_user_pass1'] !== $data['rpress_new_user_pass2'] ) {
      rpress_set_error( 'password_mismatch', __( 'The passwords you entered do not match. Please try again.', 'restropress' ) );
    } else {
      $userdata['user_pass'] = $data['rpress_new_user_pass1'];
    }
  }

  // Make sure the new email doesn't belong to another user
  if( $email != $old_user_data->user_email ) {
    // Make sure the new email is valid
    if( ! is_email( $email ) ) {
      rpress_set_error( 'email_invalid', __( 'The email you entered is invalid. Please enter a valid email.', 'restropress' ) );
    }

    // Make sure the new email doesn't belong to another user
    if( email_exists( $email ) ) {
      rpress_set_error( 'email_exists', __( 'The email you entered belongs to another user. Please use another.', 'restropress' ) );
    }
  }

  // Check for errors
  $errors = rpress_get_errors();

  if( $errors ) {
    // Send back to the profile editor if there are errors
    wp_redirect( $data['rpress_redirect'] );
    rpress_die();
  }

  // Update the user
  $meta    = update_user_meta( $user_id, '_rpress_user_address', $address );
  $updated = wp_update_user( $userdata );

  // Possibly update the customer
  $customer    = new RPRESS_Customer( $user_id, true );
  if ( $customer->email === $email || ( is_array( $customer->emails ) && in_array( $email, $customer->emails ) ) ) {
    $customer->set_primary_email( $email );
  };

  if ( $customer->id > 0 ) {
    $update_args = array(
      'name'  => $first_name . ' ' . $last_name,
    );

    $customer->update( $update_args );
  }

  if ( $updated ) {
    do_action( 'rpress_user_profile_updated', $user_id, $userdata );
    wp_redirect( add_query_arg( 'updated', 'true', $data['rpress_redirect'] ) );
    rpress_die();
  }
}
add_action( 'rpress_edit_user_profile', 'rpress_process_profile_editor_updates' );

/**
 * Process the 'remove' URL on the profile editor when customers wish to remove an email address
 *
 * @since  1.0.0
 * @return void
 */
function rpress_process_profile_editor_remove_email() {
  if ( ! is_user_logged_in() ) {
    return false;
  }

  // Pending users can't edit their profile
  if ( rpress_user_pending_verification() ) {
    return false;
  }

  // Nonce security
  if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'rpress-remove-customer-email' ) ) {
    return false;
  }

  if ( empty( $_GET['email'] ) || ! is_email( $_GET['email'] ) ) {
    return false;
  }

  $customer = new RPRESS_Customer( get_current_user_id(), true );
  if ( $customer->remove_email( $_GET['email'] ) ) {

    $url = add_query_arg( 'updated', true, $_GET['redirect'] );

    $user          = wp_get_current_user();
    $user_login    = ! empty( $user->user_login ) ? $user->user_login : 'RPRESSBot';
    $customer_note = sprintf( __( 'Email address %s removed by %s', 'restropress' ), sanitize_email( $_GET['email'] ), $user_login );
    $customer->add_note( $customer_note );

  } else {
    rpress_set_error( 'profile-remove-email-failure', __( 'Error removing email address from profile. Please try again later.', 'restropress' ) );
    $url = $_GET['redirect'];
  }

  wp_safe_redirect( $url );
  exit;
}
add_action( 'rpress_profile-remove-email', 'rpress_process_profile_editor_remove_email' );

/**
 * Delivery Options Shortcode
 *
 * Displays a user's order delivery options.
 *
 * @since 1.0
 * @return string
 */
function rpress_delivery_shortcode($atts, $content = null) {
  ob_start();

  rpress_get_template_part( 'shortcode', 'delivery' );

  $display = ob_get_clean();

  return $display;
}
add_shortcode( 'rpress_delivery', 'rpress_delivery_shortcode' );
