<?php
/**
 * Metabox Functions
 *
 * @package     RPRESS
 * @subpackage  Admin/RestroPress
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register all the meta boxes for the food items custom post type
 *
 * @since 3.0
 * @return void
 */

class RP_FoodItem_Meta_Boxes {

  public static function init() {
    add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
    add_action( 'save_post', array( __CLASS__, 'save_meta_boxes' ), 1, 2 );
  }

  public static function add_meta_boxes() {
    $screen    = get_current_screen();
    $screen_id = $screen ? $screen->id : '';
    
    add_meta_box( 'rpress-fooditem-data', __( 'Food Item Data', 'restropress' ), array( __CLASS__, 'metabox_output' ), 'fooditem', 'normal', 'high' );
  }

  public static function metabox_output( $post ) {
    global $thepostid, $fooditem_object,$rpress_sku;

    $thepostid     = $post->ID;
    $fooditem_object = new RPRESS_Fooditem( $thepostid );
    $rpress_sku    = new RPRESS_Fooditem( $thepostid );
    wp_nonce_field( 'restropress_save_data', 'restropress_meta_nonce' );

    include 'views/html-fooditem-data-panel.php';
  }


  /**
   * Show tab content/settings.
   */
  private static function output_tabs() {
    global $post, $thepostid, $fooditem_object;

    include 'views/html-fooditem-data-general.php';
    include 'views/html-fooditem-data-category.php';
    include 'views/html-fooditem-data-addons.php';
  }

  /**
   * Return array of tabs to show.
   *
   * @return array
   */
  private static function get_fooditem_data_tabs() {
    $tabs = apply_filters(
      'rpress_fooditem_data_tabs',
      array(
        'general'        => array(
          'label'    => __( 'General', 'restropress' ),
          'target'   => 'general_fooditem_data',
          'class'    => array(),
          'icon'     => 'icon-general',
          'priority' => 10,
        ),
        'category'      => array(
          'label'    => __( 'Category', 'restropress' ),
          'target'   => 'category_fooditem_data',
          'class'    => array(),
          'icon'     => 'icon-category',
          'priority' => 20,
        ),
        'addons'       => array(
          'label'    => __( 'Addons', 'restropress' ),
          'target'   => 'addons_fooditem_data',
          'class'    => array(),
          'icon'     => 'icon-addon',
          'priority' => 30,
        )
      )
    );
    // Sort tabs based on priority.
    uasort( $tabs, array( __CLASS__, 'fooditem_data_tabs_sort' ) );

    return $tabs;
  }

  public static function metabox_fields() {
    $fields = array(
      'rpress_food_type',
      'rpress_price',
      '_variable_pricing',
      'rpress_variable_price_label',
      'rpress_variable_prices',
      'addons'
    );

    return apply_filters( 'rpress_metabox_fields_save', $fields );

  }

  public static function save_meta_boxes( $post_id, $post ) {
    // $post_id and $post are required
    if ( empty( $post_id ) || empty( $post ) || $post->post_type != 'fooditem' ) {
      return;
    }

    // Dont' save meta boxes for revisions or autosaves.
    if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
      return;
    }

    // Check the nonce.
    if ( empty( $_POST['restropress_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['restropress_meta_nonce'] ) ) , 'restropress_save_data' ) ) {
      return;
    }

    // Check the post being saved == the $post_id to prevent triggering this call for other save_post events.
    if ( empty( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== $post_id ) {
      return;
    }

    // Check user has permission to edit.
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
      return;
    }

    // Get custom fields to save for the metabox
    $fields = self::metabox_fields();

    foreach( $fields as $field ) {
      if( $field != 'addons' && ! empty( $_POST[$field]  ) ) {
        $value = is_array( $_POST[$field] ) ? rpress_sanitize_array( $_POST[$field] ) : sanitize_text_field( $_POST[$field] );
        $value = apply_filters( 'rpress_metabox_save_' . $field, $value );
        update_post_meta( $post_id, $field, $value );
      } else {
        delete_post_meta( $post_id, $field );
      }
    }
    //save sku fields 
     if( isset( $_POST['rpress_sku'] ) ) {
        $sku = !empty( $_POST['rpress_sku'] ) ? sanitize_text_field( $_POST['rpress_sku'] ) : null ;
        update_post_meta(  $post_id, 'rpress_sku', $sku );
    }
    // Set the lowest price as the product price so that we can use it on frontend display.
    if( rpress_has_variable_prices() ) {
      $lowest = rpress_get_lowest_price_option( $post_id );
      update_post_meta( $post_id, 'rpress_price', $lowest );
    }

    // Save categories for the food item.
    if( !empty( $_POST['food_categories'] ) && count ( $_POST['food_categories'] ) > 0 ) {
      $food_categories = rpress_sanitize_array( $_POST['food_categories'] );
      wp_set_post_terms( $post_id, $food_categories, 'food-category'  );
    }

    // Save addons for the food item.
    if ( ! empty( $_POST['addons'] ) && count ( $_POST['addons'] ) > 0 ) {

      $addons = isset( $_POST['addons'] ) && !empty( $_POST['addons'] ) ? rpress_sanitize_array( $_POST['addons'] ) : null;

      $addon_terms = array();
      $addon_to_save = array();
      
      foreach( $addons as $addon ) {
        if ( ! empty( $addon['category'] ) ) {
          $addon_to_save[$addon['category']] = $addon;
          $addon_terms[] = $addon['category'];
          if ( isset( $addon['items'] ) ) {
            foreach( $addon['items'] as $item ) {
              $addon_terms[] = $item;
            }
          }
        }
      }

      $addon_terms = array_unique( $addon_terms );
      $product_terms = wp_get_post_terms( $post_id,  'addon_category', array( 'fields' => 'ids' ) );

      if( ! is_wp_error( $product_terms ) ) {
        $terms_to_remove = array_diff( $product_terms, $addon_terms );
        wp_remove_object_terms( $post_id, $terms_to_remove, 'addon_category' );
      }

      wp_set_post_terms( $post_id, $addon_terms, 'addon_category', true );
      update_post_meta( $post_id, '_addon_items', $addon_to_save );

    } else {

      $product_terms = wp_get_post_terms( $post_id,  'addon_category', array( 'fields' => 'ids' ) );
      if( !is_wp_error( $product_terms ) ) {
        wp_remove_object_terms( $post_id, $product_terms, 'addon_category' );
      }
      update_post_meta( $post_id, '_addon_items', '' );
    }

    // Save Addon Category
    if ( isset( $_POST['addon_category'] ) && !empty( $_POST['addon_category'] ) && count( $_POST['addon_category'] ) > 0 ) {

      $addon_data = array();

      $addon_categories = isset( $_POST['addon_category'] ) && !empty( $_POST['addon_category'] )  ? rpress_sanitize_array( $_POST['addon_category'] ) : array();

      foreach( $addon_categories as $key => $addon_cat ) {

        $name = !empty( $addon_cat['name'] ) ? $addon_cat['name'] : '';
        $type = !empty( $addon_cat['type'] ) ? $addon_cat['type'] : 'multiple';

        $term_data = wp_insert_term( $name, 'addon_category', array( 'parent' => 0, 'slug' => sanitize_title( $name ) ) );

        if ( !is_wp_error( $term_data ) ) {

          if ( !empty( $term_data[ 'term_id' ] ) ) {

            $term_id = $term_data[ 'term_id' ];

            update_term_meta( $term_id, '_type',  $type );

            wp_set_post_terms( $post_id, $term_id, 'addon_category', true );

            $addon_data[$key]['category'] = $term_id;

            if ( !empty( $addon_cat['addon_name'] ) && count( $addon_cat['addon_name'] ) > 0 ) {

              foreach( $addon_cat['addon_name'] as $k => $child_addon ) {

                $term_name = !empty( $child_addon ) ? $child_addon : '';
                $term_price = !empty( $addon_cat['addon_price'][$k] ) ? $addon_cat['addon_price'][$k] : '';

                if ( !empty( $term_name ) ) {
                  $child_terms = wp_insert_term( $term_name, 'addon_category', array( 'parent' => $term_id, 'slug' => sanitize_title( $term_name ) ) );
                }

                if ( !empty( $term_price ) && !empty( $child_terms['term_id'] ) ) {
                  update_term_meta( $child_terms['term_id'], '_price', $term_price );
                  wp_set_post_terms( $post_id, $child_terms['term_id'], 'addon_category', true );
                  $addon_data[$key]['items'][] = $child_terms['term_id'];
                }
              }
            }
          }
        }
      }
      self::update_addon_items( $post_id, $addon_data );

    }

    // Hook to allow users to save any custom fields.
    do_action( 'rpress_save_fooditem', $post_id, $post );
  }


  /**
   * Update food addon items
   *
   * @since 3.0
   * @param int $post_id FoodItem id.
   * @param int $addon_category addon category.
   * @param int $addon_items addon category items
   *
   * @return bool
   */
  public static function update_addon_items( $post_id, $addon_data ) {

    if ( empty( $post_id )  ) {
      return;
    }

    $get_addon_items = get_post_meta( $post_id, '_addon_items', true );

    if ( is_array( $get_addon_items )
      && !empty( $get_addon_items ) ) {
      foreach( $addon_data as $addon_list ) {
        $get_addon_items[] = $addon_list;
      }
    }
    else {
      $get_addon_items = $addon_data;
    }

    update_post_meta( $post_id, '_addon_items', $get_addon_items );
  }


  /**
   * Callback to sort fooditem data tabs on priority.
   *
   * @since 3.0
   * @param int $a First item.
   * @param int $b Second item.
   *
   * @return bool
   */
  private static function fooditem_data_tabs_sort( $a, $b ) {
    if ( ! isset( $a['priority'], $b['priority'] ) ) {
      return -1;
    }

    if ( $a['priority'] === $b['priority'] ) {
      return 0;
    }

    return $a['priority'] < $b['priority'] ? -1 : 1;
  }

}

RP_FoodItem_Meta_Boxes::init();