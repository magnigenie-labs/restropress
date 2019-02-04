<?php 
	$fooditems_overlay = rpress_get_option( 'enable_food_image_popup', false );
	
	if ( has_post_thumbnail( $post->ID ) ): 
		
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID, 'full' ), 'single-post-thumbnail' ); ?>

  	<div class="rpress-thumbnail-holder rpress-bg" style="background-image: url(<?php echo $image[0]; ?>);">
  	
  		<?php if( $fooditems_overlay == 1 ) : ?>
  		<a rel="gallery" class="rpress-fancybox" href="<?php echo $image[0]; ?>"><img src="<?php echo $image[0]; ?>" alt=""/></a>
  		<?php endif; ?>
  	</div>

	<?php else :  ?>

	<?php 
		$image_src = plugins_url('restropress/assets/svg/no_image.png');
  ?> 
  <div class="rpress-thumbnail-holder rpress-default-bg" style="background-image: url(<?php echo $image_src; ?>)">
  	
  	<?php if( $fooditems_overlay == 1 ) : ?>
  		<a rel="gallery" class="rpress-fancybox" href="<?php echo $image_src; ?>"><img src="<?php echo $image_src;?>" alt=""/></a>
  	<?php endif; ?>

  </div>
  
<?php endif; ?>