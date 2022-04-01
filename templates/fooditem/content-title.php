<div class="rpress-title-holder">

  <?php $item_prop = rpress_add_schema_microdata() ? ' itemprop="name"' : ''; ?>

  <h3<?php echo esc_attr( $item_prop ); ?> class="rpress_fooditem_title">
    <span class="food-title" itemprop="url">
      <?php echo rpress_get_fooditem_icon(); ?>
      <?php the_title();?>
    </span>
  </h3>

  <?php $excerpt_length = apply_filters( 'excerpt_length', 40 ); ?>

  <?php $item_prop = rpress_add_schema_microdata() ? ' itemprop="description"' : ''; ?>

  <?php if ( has_excerpt() ) : ?>

    <div<?php echo esc_attr( $item_prop ); ?> class="rpress_fooditem_excerpt">
      <?php $description = get_post_field( 'post_excerpt', get_the_ID() ); ?>
      <?php echo apply_filters( 'rpress_fooditems_excerpt', wp_trim_words( $description, $excerpt_length ), $description ); ?>
    </div>

  <?php elseif ( get_the_content() ) : ?>

    <div<?php echo esc_attr( $item_prop ); ?> class="rpress_fooditem_excerpt">
      <?php $description = get_post_field( 'post_content', get_the_ID() ); ?>
      <?php echo apply_filters( 'rpress_fooditems_content', wp_trim_words( $description, $excerpt_length ), $description ); ?>
    </div>

  <?php endif; ?>

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

</div>
