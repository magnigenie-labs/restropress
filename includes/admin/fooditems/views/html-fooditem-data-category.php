<?php
/**
 * Food Item category data panel.
 *
 * @package RestroPress/Admin
 */

defined( 'ABSPATH' ) || exit;
$categories = rpress_get_categories( array( 'hide_empty' => false ) );
$food_categories = $fooditem_object->get_food_categories();

?>
<div id="category_fooditem_data" class="panel restropress_options_panel hidden">
	<div class="rp-metabox-container">
		<div class="toolbar toolbar-top">
			<span class="rp-toolbar-title">
				<?php esc_html_e( 'Food Category', 'restropress' ); ?>
			</span>
		</div>
		<div class="options_group rp-category">
			<div class="rp-metabox">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label><?php esc_html_e( 'Select Category', 'restropress' ); ?></label>
							</th>
							<td class="rp-select-category">
								<select name="food_categories[]" class="rp-category-select rp-select2" multiple="multiple">
									<?php foreach ( $categories as $category ) {
										echo '<option ' . rp_selected( $category->term_id, $food_categories ) . ' value="' . $category->term_id .'">' .$category->name .'</option>';
									}
									?>
								</select> <?php echo rp_help_tip( __( 'Select the food categories you would like to assign to this food item. This will be used for the filtering on the food items list page.', 'restropress' ) ); ?>
							</td>
						</tr>
						<tr class="rp-add-category hidden">
							<th scope="row"></th>
							<td>
								<input type="text" class="rp-input" name="rp_category" id="rp-category-name" placeholder="<?php esc_html_e( 'Enter Category Name', 'restropress' ); ?>">
								<select name="_parent_category" id="rp-parent-category" class="rp-input rp-select2">
									<option value="">
										<?php esc_html_e( 'Parent Category', 'restropress' ); ?>
									</option>
									<?php foreach ( $categories as $category ) {
										echo '<option value="' . $category->term_id .'">' .$category->name .'</option>';
									}
									?>
								</select>
								<button type="button" class="button add-category alignright">
									<?php esc_html_e( 'Save Changes', 'restropress' ); ?>
								</button>
							</td>
						</tr>
						<tr>
							<th scope="row"></th>
							<td class="alignright">
								<button type="button" class="button button-primary rp_add_category">
									<?php esc_html_e( ' + Add New Category', 'restropress' ); ?>
								</button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php do_action( 'rpress_fooditem_categories' ); ?>
		</div>
	</div>
	<?php do_action( 'rpress_fooditem_options_category_data' ); ?>
</div>