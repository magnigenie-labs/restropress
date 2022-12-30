<?php
/**
 * Food Item create new addon category html.
 *
 * @package RestroPress/Admin
 */

defined( 'ABSPATH' ) || exit;

$row = isset( $_POST['i'] ) ?  absint( $_POST['i'] ) : 0;

?>

<!-- Create new addon form starts -->
<div class="rp-addon rp-metabox create-new-addon">
	<h3>
		<a href="#" class="remove_row delete">Remove</a>
		<div class="tips sort" data-tip="<?php esc_html_e( 'Drag Drop to reorder the addon categories.', 'restropress' );?>"></div>
		<strong class="addon_category_name">
			<?php esc_html_e( 'Create New Addon Category', 'restropress' ); ?>
		</strong>
	</h3>
	<div class="rp-metabox-content">

    <div class="rp-metabox-content-wrapper">
      <div class="rp-col-6 addon-category">
        <table class="form-table">
          <thead>
            <tr>
              <th scope="row">
                <?php esc_html_e( 'Addon Category:', 'default' ); ?>
              </th>
              <th scope="row">
                <?php esc_html_e( 'Type:', 'default' ); ?>
              </th>
            </tr>
          </thead>
          <tbody>
            <td>
              <input type="text" name="addon_category[<?php echo sanitize_key( $row ) ; ?>][name]" id="" class="rp-input addon-category-name" placeholder="<?php esc_html_e( 'Addon Category Name', 'restropress' ); ?>">
            </td>
            <td>
              <select name="addon_category[<?php echo sanitize_key( $row ) ; ?>][type]" class="rp-input addon-category-type">
                <?php
                  foreach ( $addon_types as $k => $type ) {
                    echo '<option value="' . esc_attr( $k ).'">' .esc_html( $type ) .'</option>';
                  }
                ?>
              </select>
            </td>
          </tbody>
        </table>
      </div>

      <div class="rp-col-6 addon-items">
        <table class="form-table">
          <thead>
            <tr>
              <th scope="row">
                <?php esc_html_e( 'Addon Items:', 'restropress' ); ?>
              </th>
              <th scope="row" class="addon-price-symbol">
                <?php echo sprintf( __( 'Price (%s)', 'restropress' ), rpress_currency_symbol() ); ?>
              </th>
              <th scope="row">&nbsp</th>
            </tr>
          </thead>
          <tbody>
            <tr class="addon-items-row">
              <td>
                <input type="text" name="addon_category[<?php echo $row; ?>][addon_name][]" class="rp-input" placeholder="<?php esc_html_e( 'Addon Item Name', 'restropress' ); ?>">
              </td>
              <td>
                <input type="text" name="addon_category[<?php echo $row; ?>][addon_price][]" class="rp-input rp-addon-price" placeholder="9.99">
              </td>
              <td>
                <span class="remove rp-addon-cat">
                  <span class="dashicons dashicons-dismiss"></span>
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="clear"></div>
    </div>

		<div class="toolbar-bottom toolbar">
      <button type="button" class="button button-primary add-new-addon alignright add-addon-multiple-item"> + <?php esc_html_e( 'Add New', 'restropress' ); ?></button>
    </div>
	</div>
</div>
<!-- Create new addon form ends -->