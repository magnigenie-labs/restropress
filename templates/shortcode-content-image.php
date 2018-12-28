<?php if ( has_post_thumbnail( $post->ID ) ): ?>
	<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID, 'full' ), 'single-post-thumbnail' ); ?>
  <div class="rpress-thumbnail-holder rpress-bg" style="background-image: url(<?php echo $image[0]; ?>);">
  </div>
<?php else :  ?> 
	<?php 
		$image_src = plugins_url() . '/restropress/assets/svg/no_image.png';
  ?> 
  <div class="rpress-thumbnail-holder rpress-default-bg" style="background-image: url(<?php echo $image_src; ?>)">
  </div>
<?php endif; ?>