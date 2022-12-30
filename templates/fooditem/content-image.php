<?php

$fooditems_overlay = rpress_get_option( 'enable_food_image_popup', false );
$image_placeholder = rpress_get_option( 'enable_image_placeholder', false );

if ( has_post_thumbnail( $post->ID ) ):

  $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID, 'full' ), 'single-post-thumbnail' ); ?>

  <div class="rpress-thumbnail-holder rpress-bg rpress-icon-bg">
    <?php if ( $image_placeholder == 1 || has_post_thumbnail( $post->ID ) ) : ?>

      <?php echo rpress_get_fooditem_icon(); ?>

    <?php endif; ?>

    <?php if( $fooditems_overlay == 1 ) : ?>
      <a href="<?php echo esc_url( $image[0] ); ?>" class="rpress-thumbnail-popup">
          <?php echo get_the_post_thumbnail( get_the_ID(), 'thumbnail' ); ?>
      </a>
    <?php else: ?>
      <?php echo get_the_post_thumbnail( get_the_ID(), 'thumbnail' ); ?>
    <?php endif; ?>
  </div>

<?php elseif( $image_placeholder == 1 ) : ?>

    <?php $image_src = RP_PLUGIN_URL . 'assets/svg/no_image.png'; ?>
    <div class="rpress-thumbnail-holder rpress-default-bg rpress-icon-bg">
      <?php echo rpress_get_fooditem_icon(); ?>
        <img src="<?php echo esc_url( $image_src ); ?>" alt=""/>
    </div>

<?php endif; ?>
