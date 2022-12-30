<?php
/**
 * Food Items Page
 *
 * This template can be overridden by copying it to yourtheme/restropress/fooditem/fooditems.php.
 *
 * @package RestroPress/Templates
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="rpress-section rp-col-lg-12 rp-col-md-12 rp-col-sm-12 rp-col-xs-12">

  <?php

  $shortcode_atts = RP_Shortcode_Fooditems::$atts;
  $category_ids = $all_terms = $query = [];

  if ( $shortcode_atts['category'] || $shortcode_atts['category_menu'] ) {

    if ( $shortcode_atts['category'] ) {
      $categories = explode( ',', $shortcode_atts['category'] );
    }

    if ( $shortcode_atts['category_menu'] ) {
      $categories = explode( ',', $shortcode_atts['category_menu'] );
    }

    foreach( $categories as $category ) {

      $is_id = is_int( $category ) && ! empty( $category );

      if ( $is_id ) {

        $term_id = $category;

      }
      else {

        $term = get_term_by( 'slug', $category, 'food-category' );

        if( ! $term ) {
          continue;
        }

        $term_id = $term->term_id;

        }

        $category_ids[] = $term_id;
      }
    }

    $category_params = array(
      'orderby'         => !empty( $shortcode_atts['cat_orderby'] ) ? $shortcode_atts['cat_orderby'] : '',
      'order'           => !empty( $shortcode_atts['cat_order'] ) ? $shortcode_atts['cat_order'] : '' ,
      'ids'             => $category_ids,
      'category_menu'   => !empty( $shortcode_atts['category_menu'] ) ? true : false,
    );

    do_action( 'rpress_get_food_categories' );

    do_action( 'rp_get_categories', $category_params );
    ?>
    <?php
    $disable_category = rpress_get_option( 'disable_category_menu', false );

    $option_view_food_items  = rpress_get_option( 'option_view_food_items' );
    //Grid view design issue fixed
    if( $disable_category && $option_view_food_items == "grid_view" ) :

    ?>

  <div class="rpress_fooditems_list rp-col-lg-8 rp-col-md-12 rp-col-sm-12 rp-col-xs-12">
    <?php do_action( 'before_fooditems_list' );

    $get_categories = rpress_get_categories( $category_params );

    if ( !empty( $shortcode_atts['category_menu'] ) ) {
      $get_categories = rpress_get_child_cats( $category_ids );
    }

    $all_terms = array();

    if( is_array( $get_categories ) && !empty( $get_categories ) ) {
      $all_terms = wp_list_pluck( $get_categories, 'slug' );
    }

    if ( is_array( $all_terms ) && !empty( $all_terms ) ) :

      foreach ( $all_terms as $term_slug ) :

        $prepared_query = RP_Shortcode_Fooditems::query($term_slug);
        $atts           = RP_Shortcode_Fooditems::$atts;

        // Allow the query to be manipulated by other plugins
        $query = apply_filters( 'rpress_fooditems_query', $prepared_query, $atts );

        $fooditems = new WP_Query( $query );

        do_action( 'rpress_fooditems_list_before', $atts );

        if ( $fooditems->have_posts() ) :

          $i = 1;

          do_action( 'rpress_fooditems_list_top', $atts, $fooditems );
          $curr_cat_var = '';

          while ( $fooditems->have_posts() ) : $fooditems->the_post();

            $id = get_the_ID();

            do_action( 'rpress_fooditems_category_title', $term_slug, $id, $curr_cat_var );

            do_action( 'rpress_fooditem_shortcode_item', $atts, $i );

            $i++;

          endwhile;

          wp_reset_postdata();

          do_action( 'rpress_fooditems_list_bottom', $atts );

          wp_reset_query();

        endif;

      endforeach;

      else:

        /* translators: %s: post singular name */
        printf( _x( 'No %s found', 'rpress post type name', 'restropress' ), rp_get_label_plural() );

      endif;

      ?>
    </div>

  <?php else: ?>

  <div class="rpress_fooditems_list rp-col-lg-6 rp-col-md-12 rp-col-sm-12 rp-col-xs-12">
    <?php do_action( 'before_fooditems_list' );

    $get_categories = rpress_get_categories( $category_params );

    if ( !empty( $shortcode_atts['category_menu'] ) ) {
      $get_categories = rpress_get_child_cats( $category_ids );
    }

    $all_terms = array();

    if( is_array( $get_categories ) && !empty( $get_categories ) ) {
      $all_terms = wp_list_pluck( $get_categories, 'slug' );
    }

    if ( is_array( $all_terms ) && !empty( $all_terms ) ) :

      foreach ( $all_terms as $term_slug ) :

        $prepared_query = RP_Shortcode_Fooditems::query($term_slug);
        $atts           = RP_Shortcode_Fooditems::$atts;

        // Allow the query to be manipulated by other plugins
        $query = apply_filters( 'rpress_fooditems_query', $prepared_query, $atts );

        $fooditems = new WP_Query( $query );

        do_action( 'rpress_fooditems_list_before', $atts );

        if ( $fooditems->have_posts() ) :

          $i = 1;

          do_action( 'rpress_fooditems_list_top', $atts, $fooditems );
          $curr_cat_var = '';

          while ( $fooditems->have_posts() ) : $fooditems->the_post();

            $id = get_the_ID();

            do_action( 'rpress_fooditems_category_title', $term_slug, $id, $curr_cat_var );

            do_action( 'rpress_fooditem_shortcode_item', $atts, $i );

            $i++;

          endwhile;

          wp_reset_postdata();

          do_action( 'rpress_fooditems_list_bottom', $atts );

          wp_reset_query();

        endif;

      endforeach;

      else:

        /* translators: %s: post singular name */
        printf( _x( 'No %s found', 'rpress post type name', 'restropress' ), rp_get_label_plural() );

      endif;

      ?>

  </div>

  <?php endif; ?>

  <?php if( !empty( $atts ) && !empty( $fooditems ) ) : ?>

    <?php do_action( 'rpress_fooditems_list_after', $atts, $fooditems ); ?>

  <?php endif; ?>

  <?php do_action( 'rpress_get_cart' ); ?>

</div>