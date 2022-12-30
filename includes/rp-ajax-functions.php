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
 * Get food items list .
 *
 * @since 1.0
 * @return void
 */
function get_fooditem_lists( $fooditem_id, $cart_key = '') {
  $addons = get_post_meta( $fooditem_id, '_addon_items', true );

  $chosen_addons = array();
  $price_id = 0;
  $addon_ids = $child_ids = array();

  if( $addons ) {
    foreach ($addons as $addon ) {

      if( ! empty( $addon['category'] ) ) {
        array_push($addon_ids, $addon['category'] );
      }
      if( is_array( $addon['items'] ) ) {
        $child_ids = array_merge( $child_ids, $addon['items'] );
      }
    }
  }

  if( $cart_key !== '' ) {

    $cart_contents = rpress_get_cart_contents();
    $cart_contents = $cart_contents[$cart_key];
    $price_id      = isset( $cart_contents['price_id'] ) ? $cart_contents['price_id'] : 0;

    if( ! empty( $cart_contents['addon_items'] ) ) {
      foreach( $cart_contents['addon_items'] as $key => $val ) {
        array_push( $chosen_addons, $val['addon_id'] );
      }
    }
  }

  ob_start();

  if ( ! empty( $fooditem_id ) && rpress_has_variable_prices( $fooditem_id ) ) {

    $prices = rpress_get_variable_prices( $fooditem_id );



    if ( is_array( $prices ) && ! empty( $prices ) ) {

      $variable_price_label = get_post_meta( $fooditem_id, 'rpress_variable_price_label', true );
      $variable_price_label = ! empty( $variable_price_label ) ? $variable_price_label : esc_html( 'Price Options', 'restropress' );
      $variable_price_heading = apply_filters( 'rp_variable_price_heading', $variable_price_label ); ?>

      <h6><?php echo esc_html( $variable_price_heading ); ?></h6>

      <div class="rp-variable-price-wrapper">

      <?php
      foreach( $prices as $k => $price ) {
        
        $price_option = $price['name'];
        $is_first = ( $k == $price_id ) ? 'checked' : '';
        $price_option_slug = sanitize_title( $price['name'] );
        $price_option_amount = rpress_currency_filter( rpress_format_amount( $price['amount'] ) ); ?>

        <div class="food-item-list active">
          <label for="<?php echo esc_attr( $price_option_slug ); ?>" class="radio-container">
            <input type="radio" name="price_options" id="<?php echo esc_attr( $price_option_slug ); ?>" data-value="<?php echo esc_attr( $price_option_slug . '|1|' . $price['amount'] . '|radio' ); ?>" value="<?php echo esc_attr( $k ); ?>" <?php echo esc_attr( $is_first ); ?> class="rp-variable-price-option " />
            <span><?php echo esc_html( $price_option ); ?></span>
            <span class="control__indicator"></span>
          </label>
          <span class="cat_price"><?php echo esc_html( $price_option_amount ); ?></span>
        </div>
      <?php } ?>
      </div>
    <?php }
  }

  if( isset( $addon_ids ) && is_array( $addon_ids ) && ! empty( $addon_ids ) ) { ?>

    <div class="rp-addons-data-wrapper">

      <?php
      foreach( $addon_ids as $parent ) {
        $addon_items = get_term_by( 'id', $parent, 'addon_category' );
        $addon_name = $addon_items->name;
        $addon_slug = $addon_items->slug;
        $addon_id = $addon_items->term_id;

        $is_required = isset( $addons[ $parent ]['is_required'] ) ? $addons[ $parent ]['is_required'] : 'no';
        $max_addons = isset( $addons[ $parent ]['max_addons'] ) ? $addons[ $parent ]['max_addons'] : 0;
        $is_default = isset( $addons[ $parent ]['default'] ) ? $addons[ $parent ]['default'] : array();
     
        if( ! empty( $is_default ) )
          $chosen_addons = array_merge( $chosen_addons, $is_default );
        
        ?>

        <div class="addons-wrapper addons-wrapper-<?php echo esc_attr( $parent ); ?>" data-id="<?php echo esc_attr( $parent ); ?>">

          <h6 class="rpress-addon-category">
            <?php echo esc_html( $addon_name ); ?>
            <?php if( $is_required == 'yes' ) : ?>
              <span class="rp-addon-required">
                <?php esc_html_e( 'Required', 'restropress' ); ?>
              </span>
            <?php endif; ?>
            <?php if( ! empty( $max_addons ) ) : ?>
              <span class="rp-max-addon">
                <?php echo sprintf( __( 'Maximum %s allowed', 'restropress' ), $max_addons ); ?>
              </span>
            <?php endif; ?>
          </h6>
          <input type="hidden" name="is_required" class="addon_is_required" value="<?php echo esc_attr( $is_required ); ?>" />
          <input type="hidden" name="max_limit" class="addon_max_limit" value="<?php echo esc_attr( $max_addons ); ?>" />
          <?php

          $addon_category_args = array( 'taxonomy' => 'addon_category', 'parent' => $addon_items->term_id, 'include' => $child_ids );
          $child_addons = get_terms( apply_filters( 'rp_addon_category', $addon_category_args ) );

          if ( $child_addons ) {
            $child_addons = wp_list_pluck( $child_addons, 'term_id' );
          }

          if( is_array( $child_addons ) && ! empty( $child_addons ) ) {

            foreach( $child_addons as  $child_addon ) {
      
              $classes = [];

              $extra_classes = apply_filters( 'rp_food_item_list_extra_classes', $classes, $child_addon );
              $item_classes = implode( ' ', $extra_classes );

              $child_data = get_term_by( 'id', $child_addon, 'addon_category' );
              $child_addon_slug = $child_data->slug;
              $child_addon_name = $child_data->name;
              $child_addon_id   = $child_data->term_id;
              $addon_type       = rpress_get_addon_data( $parent, '_type' );
              $use_addon_like   = $addon_type == 'single' ? 'radio' : 'checkbox';
              $child_addon_type_name = ( $use_addon_like == 'radio' ) ? $addon_name : $child_addon_name;
              $child_addon_quantity = isset( $_GET['addon_quantity_final'] ) ? $_GET['addon_quantity_final'] : 0;

               if ( is_array( $chosen_addons ) ) :
                if ( is_array( $prices ) && ! empty( $prices ) ) :

                  foreach( $prices as $p_id => $price ) :
                    $get_addon_price  = rpress_dynamic_addon_price( $fooditem_id, $child_data->term_id, $child_data->parent, $p_id );
                    
                    ?>

                    <div class="food-item-list list_<?php echo esc_attr( $p_id ); ?> <?php if( $p_id == $price_id ) { echo 'active'; } ?> <?php echo esc_attr( $item_classes ); ?>">
                      <label for="<?php echo esc_attr( $child_addon_slug ); ?>_<?php echo esc_attr( $p_id ); ?>" class="<?php echo esc_attr( $use_addon_like ); ?>-container">
                        <?php 
                          $is_selected = in_array( $child_addon_id .'|'. $price['name'], $chosen_addons ) ?  'checked' : '';
                        ?>
                        <input class="<?php echo esc_attr( $is_selected );?>" data-type="<?php echo esc_attr( $use_addon_like );?>" type="<?php echo esc_attr( $use_addon_like ); ?>" name="<?php echo esc_attr( $child_addon_type_name ); ?>" id="<?php echo esc_attr( $child_addon_slug ); ?>_<?php echo esc_attr( $p_id ); ?>" value="<?php echo esc_attr( $child_addon . '|1|' . $get_addon_price . '|' . $use_addon_like ); ?>" <?php echo esc_attr( $is_selected );?> data-addon-checked = "<?php echo esc_attr( $is_selected );?>"  >
                        <span><?php echo esc_attr( $child_addon_name ); ?></span>
                        <span class="control__indicator"></span>
                      </label>

                      <?php if( $get_addon_price >= 0 ) : ?>
                      <span class="cat_price total-addon-price">&nbsp;+&nbsp;<?php echo rpress_currency_filter( rpress_format_amount( $get_addon_price ) ); ?></span> <?php

                      endif; ?>
                    <span class="cat_price"><?php apply_filters( 'rp_food_addon_column_extra_html',$child_addon_id, $get_addon_price, $addon_id ,$child_addon_slug ); ?></span>

                    </div> <?php
                  endforeach;

                else:
                  
                  $get_addon_price  = rpress_dynamic_addon_price( $fooditem_id, $child_data->term_id, $child_data->parent );

                  ?>
                  <div class="food-item-list active <?php echo esc_attr( $item_classes ); ?>">
                    <label for="<?php echo esc_attr( $child_addon_slug ); ?>" class="<?php echo esc_attr( $use_addon_like ); ?>-container">
                      <?php $is_selected = in_array( $child_addon_id, $chosen_addons ) ?  'checked' : '';
                     ?>

                      <input data-type="<?php echo esc_attr( $use_addon_like );?>" type="<?php echo esc_attr( $use_addon_like ); ?>" name="<?php echo esc_attr( $child_addon_type_name ); ?>" id="<?php echo esc_attr( $child_addon_slug ); ?>" value="<?php echo esc_attr( $child_addon . '|1|' . $get_addon_price . '|' . $use_addon_like ); ?>" <?php echo esc_attr( $is_selected );?> >
                    
                      <span><?php echo esc_attr( $child_addon_name ); ?></span>
                      <span class="control__indicator"></span>
                    </label>
                    
                    <?php if( $get_addon_price >= 0 ) : ?>
                    <span class="cat_price total-addon-price">&nbsp;+&nbsp;<?php echo rpress_currency_filter( rpress_format_amount( $get_addon_price ) ); ?></span> 
                    <span class="cat_price"><?php apply_filters( 'rp_food_addon_column_extra_html',$child_addon_id, $get_addon_price, $addon_id ,$child_addon_slug ); ?></span>
                    <?php endif; ?>
                    
                     
                  </div> <?php
                endif;
              endif;
            }

          }

          ?>
        </div>
      <?php } ?>
    </div>
  <?php }
  return ob_get_clean();
}

/**
 * Get addon items for a specific Fooditem
 *
 * @since 1.0
 * @return void
 */
function rpress_addon_items_by_fooditem( $fooditem_id ) {

  if ( empty( $fooditem_id ) ) {
    return;
  }

  $addons = get_post_meta( $fooditem_id, '_addon_items', true );
  $addon_ids = $child_ids = array();

  if( $addons ) {
    foreach ($addons as $addon ) {
      if( ! empty( $addon['category'] ) ) {
        array_push($addon_ids, $addon['category'] );
      }
      if( is_array( $addon['items'] ) ) {
        $child_ids = array_merge( $child_ids, $addon['items'] );
      }
    }
  }

  if( is_array( $addon_ids ) && ! empty( $addon_ids ) ) {

    foreach( $addon_ids as $parent ) {

      $addon_items = get_term_by( 'id', $parent, 'addon_category' );
      $addon_category_args = array( 'taxonomy' => 'addon_category', 'parent' => $addon_items->term_id );
      $child_addons = get_terms( apply_filters( 'rp_addon_category', $addon_category_args ) );

      if ( $child_addons ) {
        $child_addons = wp_list_pluck( $child_addons, 'term_id' );
      }

      if( is_array( $child_addons ) && ! empty( $child_addons ) ) {

        foreach( $child_addons as $child_addon ) {
          $child_data = get_term_by( 'id', $child_addon, 'addon_category' );
          $child_addon_name = $child_data->name;
          $child_addon_price = rpress_get_addon_data( $child_data->term_id, '_price' );
          $addon_price = html_entity_decode( rpress_currency_filter( rpress_format_amount( $child_addon_price ) ) );
          ?>
          <option data-price="<?php echo esc_attr( $addon_price ); ?>" data-id="$child_addon" value="<?php echo esc_attr( $child_addon_name . '|' . $child_addon . '|' . $child_addon_price . '|1' ); ?> "><?php echo esc_html( $child_addon_name . ' (' . $addon_price . ') ' ); ?> </option>
          <?php
        }
      }
    }
  }
}