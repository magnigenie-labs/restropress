<?php $item_prop = rpress_add_schema_microdata() ? ' itemprop="description"' : ''; ?>
<div<?php echo $item_prop; ?> class="rpress_fooditem_full_content">
	<?php echo apply_filters( 'rpress_fooditems_content', get_post_field( 'post_content', get_the_ID() ) ); ?>
</div>
