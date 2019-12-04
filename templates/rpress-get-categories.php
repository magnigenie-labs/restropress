<?php
$color = rpress_get_option( 'checkout_color', 'red' );
$taxonomy_name = 'food-category';

$get_all_items = get_terms( array( 'taxonomy' => $taxonomy_name, 'hide_empty' => true ) );
ob_start();
?>
<div class="rp-col-lg-2 rp-col-md-2 rp-col-sm-3 rp-col-xs-12 sticky-sidebar cat-lists">
  <div class="rpress-filter-toggle">
    <span class="rpress-filter-toggle-text">
      <?php echo __('Categories By', 'restropress'); ?>
    </span>
  </div>
  
  <div class="rpress-filter-wrapper">
    <div class="rpress-categories-menu">
      <?php do_action('rpress_before_category_list'); ?>
      <?php
        if( is_array($get_all_items) && !empty($get_all_items) ) :
      ?>
      <ul class="rpress-category-lists">
        <?php 
        foreach ($get_all_items as $key => $get_all_item) : ?>
          <li class="rpress-category-item ">
            <a href="#<?php echo $get_all_item->slug; ?>" data-id="<?php echo $get_all_item->term_id; ?>" class="rpress-category-link  nav-scroller-item <?php echo $color; ?>"><?php echo $get_all_item->name; ?></a>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php
      endif;
      ?>
      <?php do_action('rpress_after_category_list'); ?>
    </div>
  </div>
</div>
<?php
echo ob_get_clean();