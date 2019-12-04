<?php
//global $rpress_fooditem_id, $rpress_post_terms;

global $var;
$rpress_fooditem_id = get_the_ID();
$rpress_post_terms = wp_get_post_terms(get_the_ID(), 'food-category');
$current_food_cat = @$get_food_cat;
$get_food_cat = $rpress_post_terms[0]->name;
$get_food_id = $rpress_post_terms[0]->term_taxonomy_id;
$get_description = $rpress_post_terms[0]->description;
$get_food_cat_slug = $rpress_post_terms[0]->slug;
$color = rpress_get_option( 'checkout_color', 'red' );

$class = ($var == $get_food_cat )? 'rpress-same-cat' : 'rpress-different-cat';

$var = $get_food_cat;

if( $class == 'rpress-different-cat' ) :
?>
<div id="menu-category-<?php echo $get_food_id; ?>" class="rpress-element-title" id="<?php echo $rpress_fooditem_id; ?>" data-term-id="<?php echo $get_food_id; ?>">
  <div class="menu-category-wrap" data-cat-id="<?php echo $get_food_cat_slug; ?>">
    <div class="menu-category-wrap" data-cat-id="<?php echo $get_food_cat_slug; ?>">
      <h5 class="rpress-cat rpress-different-cat <?php echo $color;?>"><?php echo $get_food_cat; ?></h5>
        <?php if( !empty($get_description) ) : ?>
          <span><?php echo $get_description; ?></span>
        <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>