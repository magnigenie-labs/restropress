<?php
/*
* Sidebar category template
*
*/

global $data;

ob_start();

if ( $data['category_menu'] ) {
  $get_all_items = rpress_get_child_cats( $data['ids'] );
} else {
  $get_all_items = rpress_get_categories( $data );
}
$disable_category = rpress_get_option( 'disable_category_menu', false );
if( !$disable_category ) :
?>

<div class="rp-col-lg-2 rp-col-md-2 rp-col-sm-3 rp-col-xs-12 sticky-sidebar cat-lists">
  <div class="rpress-filter-wrapper">
    <div class="rpress-categories-menu">
    <?php do_action('rpress_before_category_list'); ?>
    <?php
    if ( is_array( $get_all_items ) && !empty( $get_all_items ) ) :
    ?>
      <ul class="rpress-category-lists">
      <?php
      foreach ( $get_all_items as $key => $get_all_item ) : ?>
        <li class="rpress-category-item ">
          <a href="#<?php echo esc_html( $get_all_item->slug ); ?>" data-id="<?php echo esc_attr( $get_all_item->term_id ); ?>" class="rpress-category-link nav-scroller-item"><?php echo wp_kses_post( $get_all_item->name ); ?></a>
        </li>
      <?php endforeach; ?>
      </ul>
    <?php
    endif;
    ?>
    <?php do_action( 'rpress_after_category_list' ); ?>
    </div>
  </div>
</div>
<div class="rp-mb-cat-ft-btn"><span class="rp-mb-cat-txt"><i class="fa fa-cutlery" aria-hidden="true"></i><?php esc_html_e( 'Menu', 'restropress' ); ?></span></div>

<?php
endif;
echo ob_get_clean();
