<?php

global $curr_cat_var;
global $fooditem_term_slug;
global $rpress_fooditem_id;

$class = ($curr_cat_var == $fooditem_term_slug )? 'rpress-same-cat' : 'rpress-different-cat';
$curr_cat_var = $fooditem_term_slug;
$food_category = get_term_by( 'slug', $fooditem_term_slug, 'food-category' );

if( $class == 'rpress-different-cat' ) : ?>

<div id="menu-category-<?php echo esc_attr( $food_category->term_id ); ?>" class="rpress-element-title" id="<?php echo esc_attr( $rpress_fooditem_id ); ?>" data-term-id="<?php echo esc_attr( $food_category->term_id ); ?>">
  <div class="menu-category-wrap" data-cat-id="<?php echo esc_attr( $fooditem_term_slug ); ?>">
    <div class="menu-category-wrap" data-cat-id="<?php echo esc_attr( $fooditem_term_slug ); ?>">
      <h5 class="rpress-cat rpress-different-cat"><?php echo wp_kses_post( $food_category->name ); ?></h5>
        <?php if( !empty( $food_category->description ) ) : ?>
          <span><?php echo wp_kses_post( $food_category->description ); ?></span>
        <?php endif; ?>
    </div>
  </div>
</div>

<?php endif; ?>