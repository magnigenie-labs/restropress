<?php
/**
 * Food Item Addons data panel.
 *
 * @package RestroPress/Admin
 */

defined( 'ABSPATH' ) || exit;

$count    = !empty( $current ) ? $current : time();
$post_id  = get_the_ID();
$addons   = get_post_meta( $post_id, '_addon_items', true );
  
$variation_label = '';

if ( is_array( $addons ) && !empty( $addons ) ) :

  if( ! is_null( $post_id ) && rpress_has_variable_prices( $post_id ) ) {
    $variation_label = get_post_meta( $post_id, 'rpress_variable_price_label', true );
    $variation_label = !empty( $variation_label ) ? $variation_label : __( 'Variation', 'restropress' );
  }

  foreach( $addons as $key => $addon_item ) :
    if( ! isset( $addon_item['category'] ) )
      continue;

    $addon_id = $addon_item['category'];
    $addon_type  = get_term_meta( $addon_id, '_type', true );

    if( isset( $addon_item['is_required'] ) && $addon_item['is_required'] == 'yes' ) {
      $is_required = 'checked';
    } else {
      $is_required = '';
    }

    ?>

    <!-- Addon category form starts -->
    <div class="rp-addon rp-metabox" >

      <h3>
        <a href="#" class="remove_row delete"><span class="dashicons dashicons-remove"></span></a>
        <strong><?php esc_html_e( 'Select Addon', 'restropress' ); ?></strong>
      </h3>

      <div class="rp-metabox-content" >
        <div class="addon-category">
          <select name="addons[<?php echo sanitize_key( $key ); ?>][category]" class=" rp-input rp-addon-lists " data-row-id="<?php echo esc_attr( $key ); ?>">

            <?php if ( $addon_id == '' ) : ?>
              <option value="">
                <?php esc_html_e( 'Select Addon Category', 'restropress' ); ?>
              </option>
            <?php endif; ?>

            <?php
              foreach ( $addon_categories as $category ) {
                echo '<option data-name="'. esc_attr( $category->name ) .'" '.selected( $addon_item['category'], $category->term_id, false ).' value="' . esc_attr( $category->term_id ) .'">' . esc_html( $category->name ) .'</option>';
              }
            ?>
          </select>
          <button type="button" class="button load-addon" data-item-id=<?php echo isset( $post_id )? esc_attr( $post_id ):''; ?>>
            <?php esc_html_e( 'Add', 'restropress' ); ?>
          </button>
          <label class="input_max_allowed">
            <?php esc_html_e( 'Max Selections?', 'restropress' ); ?>
            <input type="number" name="addons[<?php echo sanitize_key( $key ); ?>][max_addons]" value="<?php echo isset( $addon_item['max_addons'] ) ? esc_attr( $addon_item['max_addons'] ) : ''; ?>" />
          </label>
          <label class="cb_required">
            <input type="checkbox" name="addons[<?php echo sanitize_key( $key ) ; ?>][is_required]" value="yes" <?php echo esc_html( $is_required ); ?> />
            <?php esc_html_e( 'Required?', 'restropress' ); ?>
            <span> | </span>
          </label>
        </div>
        <div class="addon-items">

          <?php
          $get_addons = rpress_get_addons( $addon_id );
          if ( !empty( $addon_id ) && is_array( $get_addons ) && !empty( $get_addons ) ) : ?>

            <table class="rp-addon-items" data-addon_type="<?php echo $addon_type ?>">
              <thead>
                <tr>
                  <th class="select_addon">
                    <strong>
                      <input type="checkbox" class="rp-select-all">
                      <?php esc_html_e( 'Enable', 'restropress' ); ?>
                    </strong>
                  </th>
                  <th class="addon_name">
                    <strong>
                      <?php esc_html_e( 'Addon Name', 'restropress' ); ?>
                    </strong>
                  </th>
                  <th class="variation_name">
                    <strong>
                      <?php echo esc_html( $variation_label ); ?>
                    </strong>
                  </th>
                  <th class="addon_price">
                    <strong>
                      <?php esc_html_e( 'Price', 'restropress' ); ?>
                    </strong>
                  </th>
                  <th class="default_addon">
                    <strong>
                      <?php esc_html_e( 'Default', 'restropress' ); ?>
                    </strong>
                  </th>
                </tr>
              </thead>
              <tbody>

              <?php foreach( $get_addons as $get_addon ) :

                $addon_item_id = $get_addon->term_id;
                $addon_item_name = $get_addon->name;
                $addon_slug = $get_addon->slug;
                $addon_price = rpress_get_addon_data( $addon_item_id, '_price' );
             
                $addon_price = ! empty( $addon_price ) ? $addon_price : '';

                $selected = '';
                $req_selected = '';
                $default_selected = '';

                if ( isset( $addon_item['items'] ) ) {
                  if ( in_array( $addon_item_id, $addon_item['items'] ) ) {
                    $selected = 'checked';
                  }
                }

                if ( isset( $addon_item['required'] ) ) {
                  if ( in_array( $addon_item_id, $addon_item['required'] ) ) {
                    $req_selected = 'checked';
                  }
                }

                if ( isset( $addon_item['default'] ) ) {
                  if ( in_array( $addon_item_id, $addon_item['default'] ) ) {
                    $default_selected = 'checked';
                  }
                }

                if( rpress_has_variable_prices( $post_id ) ) {
                
                  $count = 1;
                  foreach ( rpress_get_variable_prices( $post_id ) as $price) {
                    
                    $addon_price = !empty( $addon_item['prices'] ) && !empty( $addon_item['prices'][$addon_item_id][sanitize_key( $price['name'] )] ) ? $addon_item['prices'][$addon_item_id][ sanitize_key( $price['name'] )] : $addon_price;
                    
                    if ( isset( $addon_item['default'] ) ) {
                      
                  if ( in_array( $addon_item_id .'|'. $price['name'], $addon_item['default'] ) ) {
                    $default_var_selected = 'checked';
                  }else{
                    $default_var_selected ='';
                  }
                }else{
                  $default_var_selected ='';
                }
                    ?>

                    <tr class="rp-child-addon">
                      <?php if( $count == 1 ) { ?>
                        <td class="rp-addon-select td_checkbox"><input type="checkbox" value="<?php echo esc_attr( $addon_item_id ); ?>" id="<?php echo esc_attr( $addon_slug ); ?>" name="addons[<?php echo sanitize_key( $key ); ?>][items][]" class="rp-checkbox" <?php echo esc_html( $selected ); ?> /></td>
                      <?php } else { ?>
                        <td class="td_checkbox">&nbsp;</td>
                      <?php } ?>
                      <td class="add_label"><label for="<?php echo esc_attr( $addon_slug ) ; ?>"><?php echo (esc_html( $addon_item_name )); ?></label></td>
                      <td class="variation_label"><label for="<?php echo esc_attr($price['name'] ); ?>"><?php echo esc_html( $price['name'] ); ?></label></td>
                      <td class="addon_price"><input class="addon-custom-price" type="text" placeholder="0.00" value="<?php echo esc_attr( $addon_price ); ?>" name="addons[<?php echo sanitize_key( $key ); ?>][prices][<?php echo sanitize_key( $addon_item_id ); ?>][<?php echo sanitize_key( $price['name'] ); ?>]"></td>
                      
                      <td class="td_checkbox"><input type="checkbox" data-variation_name="<?php echo esc_attr( $price['name'] ); ?>" value="<?php echo esc_attr( $addon_item_id . '|' . $price['name']); ?>" id="<?php echo esc_attr( $addon_slug ); ?>" name="addons[<?php echo sanitize_key( $key ); ?>][default][]"class="rps-checkbox" <?php echo esc_html( $default_var_selected ); ?> /></td>
                    </tr>

                  <?php $count++; } ?>

                <?php } else {

                  $addon_price = ( isset( $addon_item['prices'][$addon_item_id] ) && !is_array( $addon_item['prices'][$addon_item_id] ) ) ? $addon_item['prices'][$addon_item_id] : $addon_price;

                  ?>

                  <tr class="rp-child-addon">
                    <td class="rp-addon-select td_checkbox"><input type="checkbox" value="<?php echo esc_attr( $addon_item_id ); ?>" id="<?php echo esc_attr( $addon_slug ); ?>" name="addons[<?php echo sanitize_key( $key ); ?>][items][]" class="rp-checkbox" <?php echo esc_html( $selected ); ?> /></td>
                    <td class="add_label"><label for="<?php echo esc_attr( $addon_slug ); ?>"><?php echo esc_html( $addon_item_name ); ?></label></td>
                    <td class="variation_label">&nbsp;</td>
                    <td class="addon_price"><input class="addon-custom-price" type="text" placeholder="0.00" value="<?php echo esc_attr( $addon_price ); ?>" name="addons[<?php echo sanitize_key( $key ); ?>][prices][<?php echo sanitize_key( $addon_item_id ); ?>]"></td>
                    
                    <td class="tds_checkbox"><input type="checkbox" value="<?php echo esc_attr( $addon_item_id ); ?>" id="<?php echo esc_attr( $addon_slug ); ?>" name="addons[<?php echo sanitize_key( $key ); ?>][default][]" class="rp-checkbox" <?php echo esc_attr( $default_selected ); ?> /></td>
                    
                  </tr>

                <?php } ?>
              <?php endforeach; ?>
              </tbody>
            </table>

          <?php else : ?>
            <div class="rp-addon-msg">
              <?php esc_html_e( 'Please select a addon category first!', 'restropress' ); ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <!-- Addon category form ends -->

  <?php endforeach; ?>

<?php else : ?>

  <!-- Addon category form starts -->
  <div class="rp-addon rp-metabox">
    <h3>
      <a href="#" class="remove_row delete"><span class="dashicons dashicons-remove"></span></a>
      <strong><?php esc_html_e( 'Select Addon', 'restropress' ); ?></strong>
    </h3>
    <div class="rp-metabox-content">
      <div class="addon-category">
        <select name="addons[<?php echo sanitize_key( $count ); ?>][category]" class="rp-input rp-addon-items-list" data-row-id="<?php echo esc_attr( $count ); ?>">
          <option value="">
            <?php esc_html_e( 'Select Addon', 'restropress' ); ?>
          </option>
          <?php
            foreach ( $addon_categories as $category ) :
              echo '<option value="' . esc_attr( $category->term_id ) .'">' . esc_html( $category->name ).'</option>';
            endforeach;
          ?>
        </select>
        <button type="button" class="button load-addon" data-item-id=<?php echo isset( $item_id )? esc_attr( $item_id ): esc_attr( $post_id ); ?>>
          <?php esc_html_e( 'Add', 'restropress' ); ?>
        </button>
        <label class="input_max_allowed">
          <?php esc_html_e( 'Max Selections?', 'restropress' ); ?>
          <input type="number" name="addons[<?php echo sanitize_key( $count ) ; ?>][max_addons]" value="" />
        </label>
        <label class="cb_required">
          <input type="checkbox" name="addons[<?php echo sanitize_key( $count ); ?>][is_required]" value="yes" />Is Required?
          <span> | </span>
        </label>
      </div>
      <div class="addon-items">
        <div class="rp-addon-msg">
          <?php esc_html_e( 'Please select a addon category first!', 'restropress' ); ?>
        </div>
      </div>
    </div>
  </div>
  <!-- Addon category form ends -->

<?php endif; ?>
