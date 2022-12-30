<div class="rpress-title-holder">

  <?php $item_prop = rpress_add_schema_microdata() ? ' itemprop="name"' : ''; ?>
  <?php $image_placeholder = rpress_get_option( 'enable_image_placeholder', false ); ?>

  <h3<?php echo esc_attr( $item_prop ); ?> class="rpress_fooditem_title">
    <span class="food-title" itemprop="url">
      <?php if ( ! has_post_thumbnail( $post->ID ) ) : ?>

        <?php echo rpress_get_fooditem_icon(); ?>

      <?php endif; ?>
      <?php the_title();?>
    </span>
  </h3>

  <?php $excerpt_length = apply_filters( 'excerpt_length', 40 ); ?>

  <?php $item_prop = rpress_add_schema_microdata() ? ' itemprop="description"' : ''; ?>
  <div class="description-tag-wrap">
      <?php if ( has_excerpt() ) : ?>

        <div <?php echo esc_attr( $item_prop ); ?> class="rpress_fooditem_excerpt">
          <?php $description = get_post_field( 'post_excerpt', get_the_ID() ); ?>
          <?php echo apply_filters( 'rpress_fooditems_excerpt', wp_trim_words( $description, $excerpt_length ), $description ); ?>
        </div>

      <?php elseif ( get_the_content() ) : ?>

        <div <?php echo esc_attr( $item_prop ); ?> class="rpress_fooditem_excerpt">
          <?php $description = get_post_field( 'post_content', get_the_ID() ); ?>
          <?php echo apply_filters( 'rpress_fooditems_content', wp_trim_words( $description, $excerpt_length ), $description ); ?>
        </div>

      <?php endif; ?>

      <?php

      $disable_category = rpress_get_option( 'disable_category_menu', false );

      $option_view_food_items  = rpress_get_option( 'option_view_food_items' );
      //Grid view design issue fixed
      if( $disable_category && $option_view_food_items == "grid_view" ) :
       ?>

      <?php $enable_tags = rpress_get_option( 'enable_tags_display', false ); ?>

      <?php if( $enable_tags ) : ?>

        <?php
        $terms = get_the_terms( get_the_id(), 'fooditem_tag' );
        if( $terms ) {
          echo '<div class="rpress_fooditem_tags">';
          foreach ($terms as $key => $term) {
            echo '<span class="fooditem_tag '.$term->slug.'">'.$term->name.'</span>';
          }
          echo '</div>';
        }
        ?>

      <?php endif; ?>

    <?php endif; ?>

  </div>

  <div class="rpress-price-holder rpress-grid-view-holder">
  <span class="price">
    <?php

    global $post;
    global $rpress_options;

    $price = get_post_meta( $post->ID,'rpress_price', true ) ;
    $variable_pricing = rpress_has_variable_prices( $post->ID );

    if ( $variable_pricing ) {
      echo  rpress_price_range( $post->ID );
    } else {
      echo rpress_currency_filter( rpress_format_amount( $price ) );
    }

    ?>
  </span>
    <div class="rpress_fooditem_buy_button">
      <?php echo rpress_get_purchase_link( array( 'fooditem_id' => get_the_ID() ) ); ?>
    </div>
  </div>

  <?php

  //Grid view design issue fixed
  if( $disable_category && $option_view_food_items == "list_view" || $disable_category == 0 ) :
   ?>

  <?php $enable_tags = rpress_get_option( 'enable_tags_display', false ); ?>

  <?php if( $enable_tags ) : ?>

    <?php
    $terms = get_the_terms( get_the_id(), 'fooditem_tag' );
    if( $terms ) {
      echo '<div class="rpress_fooditem_tags">';
      foreach ($terms as $key => $term) {
        echo '<span class="fooditem_tag '.$term->slug.'">'.$term->name.'</span>';
      }
      echo '</div>';
    }
    ?>

    <?php endif; ?>

  <?php endif ?>

</div>
