<?php
/**
 * Edit Discount Page
 *
 * @package     RPRESS
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! isset( $_GET['discount'] ) || ! is_numeric( $_GET['discount'] ) ) {
	wp_die( __( 'Something went wrong.', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 400 ) );
}

$discount_id       = absint( $_GET['discount'] );
$discount          = rpress_get_discount( $discount_id );
$product_reqs      = rpress_get_discount_product_reqs( $discount_id );
$excluded_products = rpress_get_discount_excluded_products( $discount_id );
$category_reqs     = rpress_get_discount_category_reqs( $discount_id );
$excluded_category = rpress_get_discount_excluded_category( $discount_id );
$condition         = rpress_get_discount_product_condition( $discount_id );
$single_use        = rpress_discount_is_single_use( $discount_id );
$flat_display      = rpress_get_discount_type( $discount_id ) == 'flat' ? '' : ' style="display:none;"';
$percent_display   = rpress_get_discount_type( $discount_id ) == 'percent' ? '' : ' style="display:none;"';
$condition_display = empty( $product_reqs ) ? ' style="display:none;"' : '';
?>
<h2><?php esc_html_e( 'Edit Discount', 'restropress' ); ?></h2>

<?php if ( isset( $_GET['rpress_discount_updated'] ) ) : ?>
	<div id="message" class="updated">
		<p><strong><?php esc_html_e( 'Discount code updated.', 'restropress' ); ?></strong></p>

		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=rpress-discounts' ) ); ?>"><?php esc_html_e( '&larr; Back to Discounts', 'restropress' ); ?></a></p>
	</div>
<?php endif; ?>

<form id="rpress-edit-discount" action="" method="post">
	<?php do_action( 'rpress_edit_discount_form_top', $discount_id, $discount ); ?>
	<table class="form-table">
		<tbody>
			<?php do_action( 'rpress_edit_discount_form_before_name', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-name"><?php esc_html_e( 'Name', 'restropress' ); ?></label>
				</th>
				<td>
					<input name="name" required="required" id="rpress-name" type="text" value="<?php echo esc_attr( stripslashes( $discount->post_title ) ); ?>" />
					<p class="description"><?php esc_html_e( 'The name of this discount', 'restropress' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'rpress_edit_discount_form_before_code', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-code"><?php esc_html_e( 'Code', 'restropress' ); ?></label>
				</th>
				<td>
					<input type="text" required="required" id="rpress-code" name="code" value="<?php echo esc_attr( rpress_get_discount_code( $discount_id ) ); ?>" pattern="[a-zA-Z0-9-_]+" />
					<p class="description"><?php esc_html_e( 'Enter a code for this discount, such as 10PERCENT. Only alphanumeric characters are allowed.', 'restropress' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'rpress_edit_discount_form_before_type', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-type"><?php esc_html_e( 'Type', 'restropress' ); ?></label>
				</th>
				<td>
					<select name="type" id="rpress-type">
						<option value="percent" <?php selected( rpress_get_discount_type( $discount_id ), 'percent' ); ?>><?php esc_html_e( 'Percentage', 'restropress' ); ?></option>
						<option value="flat"<?php selected( rpress_get_discount_type( $discount_id ), 'flat' ); ?>><?php esc_html_e( 'Flat amount', 'restropress' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'The kind of discount to apply for this discount.', 'restropress' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'rpress_edit_discount_form_before_amount', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-amount"><?php esc_html_e( 'Amount', 'restropress' ); ?></label>
				</th>
				<td>
					<input type="text" class="rpress-price-field" required="required" id="rpress-amount" name="amount" value="<?php echo esc_attr( rpress_get_discount_amount( $discount_id ) ); ?>" />
					<p class="description rpress-amount-description flat"<?php echo esc_attr( $flat_display ); ?>><?php printf( __( 'Enter the discount amount in %s', 'restropress' ), rpress_get_currency() ); ?></p>
					<p class="description rpress-amount-description percent"<?php echo esc_attr( $percent_display ); ?>><?php esc_html_e( 'Enter the discount percentage. 10 = 10%', 'restropress' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'rpress_edit_discount_form_before_products', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-products"><?php printf( __( '%s Requirements', 'restropress' ), rpress_get_label_singular() ); ?></label>
				</th>
				<td>
					<p>
						<?php echo RPRESS()->html->product_dropdown( array(
							'name'        => 'products[]',
							'id'          => 'products',
							'selected'    => $product_reqs,
							'multiple'    => true,
							'chosen'      => true,
							'placeholder' => sprintf( __( 'Select one or more %s', 'restropress' ), rpress_get_label_plural() )
						) ); ?><br/>
					</p>
					<div id="rpress-discount-product-conditions"<?php echo esc_attr( $condition_display ); ?>>
						<p>
							<select id="rpress-product-condition" name="product_condition">
								<option value="all"<?php selected( 'all', $condition ); ?>><?php printf( __( 'Cart must contain all selected %s', 'restropress' ), rpress_get_label_plural() ); ?></option>
								<option value="any"<?php selected( 'any', $condition ); ?>><?php printf( __( 'Cart needs one or more of the selected %s', 'restropress' ), rpress_get_label_plural() ); ?></option>
							</select>
						</p>
						<p>
							<label>
								<input type="radio" class="tog" name="not_global" value="0"<?php checked( false, rpress_is_discount_not_global( $discount_id ) ); ?>/>
								<?php esc_html_e( 'Apply discount to entire purchase.', 'restropress' ); ?>
							</label><br/>
							<label>
								<input type="radio" class="tog" name="not_global" value="1"<?php checked( true, rpress_is_discount_not_global( $discount_id ) ); ?>/>
								<?php printf( __( 'Apply discount only to selected %s.', 'restropress' ), rpress_get_label_plural() ); ?>
							</label>
						</p>
					</div>
					<p class="description"><?php printf( __( 'Select %s relevant to this discount. If left blank, this discount can be used on any product.', 'restropress' ), rpress_get_label_plural() ); ?></p>
				</td>
			</tr>
			<?php do_action( 'rpress_edit_discount_form_before_excluded_products', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-excluded-products"><?php printf( __( 'Excluded %s', 'restropress' ), rpress_get_label_plural() ); ?></label>
				</th>
				<td>
					<?php echo RPRESS()->html->product_dropdown( array(
						'name'        => 'excluded-products[]',
						'id'          => 'excluded-products',
						'selected'    => $excluded_products,
						'multiple'    => true,
						'chosen'      => true,
						'placeholder' => sprintf( __( 'Select one or more %s', 'restropress' ), rpress_get_label_plural() )
					) ); ?><br/>
					<p class="description"><?php printf( __( '%s that this discount code cannot be applied to.', 'restropress' ), rpress_get_label_plural() ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-included-categories"><?php printf( __( 'Included categories', 'restropress' ), rpress_get_label_plural() ); ?></label>
				</th>
				<td>
					<?php echo RPRESS()->html->food_categories_dropdown( array(
						'name'        => 'categories[]',
						'id'          => 'categories',
						'selected'    =>$category_reqs,
						'multiple'    => true,
						'chosen'      => true,
						'placeholder' => sprintf( __( 'Select one or more categories', 'restropress' ), rpress_get_label_plural() ),
					) ); ?><br/>
					<p class="description"><?php printf( __( 'Categories that this discount code to be applied to.', 'restropress' ), rpress_get_label_plural() ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-excluded-categories"><?php printf( __( 'Excluded categories', 'restropress' ), rpress_get_label_plural() ); ?></label>
				</th>
				<td>
					<?php echo RPRESS()->html->food_categories_dropdown( array(
						'name'        => 'excluded-categories[]',
						'id'          => 'excluded-categories',
						'selected'    => $excluded_category,
						'multiple'    => true,
						'chosen'      => true,
						'placeholder' => sprintf( __( 'Select one or more categories', 'restropress' ), rpress_get_label_plural() ),
					) ); ?><br/>
					<p class="description"><?php printf( __( 'Categories that this discount code cannot to be applied to.', 'restropress' ), rpress_get_label_plural() ); ?></p>
				</td>
			</tr>
			<?php do_action( 'rpress_edit_discount_form_before_start', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-start"><?php esc_html_e( 'Start date', 'restropress' ); ?></label>
				</th>
				<td>
					<input name="start" id="rpress-start" type="text" value="<?php echo esc_attr( rpress_get_discount_start_date( $discount_id ) ); ?>"  class="rpress_datepicker"/>
					<p class="description"><?php esc_html_e( 'Enter the start date for this discount code in the format of mm/dd/yyyy. For no start date, leave blank. If entered, the discount can only be used after or on this date.', 'restropress' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'rpress_edit_discount_form_before_expiration', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-expiration"><?php esc_html_e( 'Expiration date', 'restropress' ); ?></label>
				</th>
				<td>
					<input name="expiration" id="rpress-expiration" type="text" value="<?php echo esc_attr( rpress_get_discount_expiration( $discount_id ) ); ?>"  class="rpress_datepicker"/>
					<p class="description"><?php esc_html_e( 'Enter the expiration date for this discount code in the format of mm/dd/yyyy. For no expiration, leave blank', 'restropress' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'rpress_edit_discount_form_before_max_uses', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-max-uses"><?php esc_html_e( 'Max Uses', 'restropress' ); ?></label>
				</th>
				<td>
					<input type="text" id="rpress-max-uses" name="max" value="<?php echo esc_attr( rpress_get_discount_max_uses( $discount_id ) ); ?>" style="width: 40px;"/>
					<p class="description"><?php esc_html_e( 'The maximum number of times this discount can be used. Leave blank for unlimited.', 'restropress' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'rpress_edit_discount_form_before_min_cart_amount', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-min-cart-amount"><?php esc_html_e( 'Minimum Amount', 'restropress' ); ?></label>
				</th>
				<td>
					<input type="text" id="rpress-min-cart-amount" name="min_price" value="<?php echo esc_attr( rpress_get_discount_min_price( $discount_id ) ); ?>" style="width: 40px;"/>
					<p class="description"><?php esc_html_e( 'The minimum amount that must be purchased before this discount can be used. Leave blank for no minimum.', 'restropress' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'rpress_edit_discount_form_before_status', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-status"><?php esc_html_e( 'Status', 'restropress' ); ?></label>
				</th>
				<td>
					<select name="status" id="rpress-status">
						<option value="active" <?php selected( $discount->post_status, 'active' ); ?>><?php esc_html_e( 'Active', 'restropress' ); ?></option>
						<option value="inactive"<?php selected( $discount->post_status, 'inactive' ); ?>><?php esc_html_e( 'Inactive', 'restropress' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'The status of this discount code.', 'restropress' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'rpress_edit_discount_form_before_use_once', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-use-once"><?php esc_html_e( 'Use Once Per Customer', 'restropress' ); ?></label>
				</th>
				<td>
					<input type="checkbox" id="rpress-use-once" name="use_once" value="1"<?php checked( true, $single_use ); ?>/>
					<span class="description"><?php esc_html_e( 'Limit this discount to a single-use per customer?', 'restropress' ); ?></span>
				</td>
			</tr>
		</tbody>
	</table>
	<?php do_action( 'rpress_edit_discount_form_bottom', $discount_id, $discount ); ?>
	<p class="submit">
		<input type="hidden" name="rpress-action" value="edit_discount"/>
		<input type="hidden" name="discount-id" value="<?php echo absint( $_GET['discount'] ); ?>"/>
		<input type="hidden" name="rpress-redirect" value="<?php echo esc_url( admin_url( 'admin.php?page=rpress-discounts&rpress-action=edit_discount&discount=' . $discount_id ) ); ?>"/>
		<input type="hidden" name="rpress-discount-nonce" value="<?php echo wp_create_nonce( 'rpress_discount_nonce' ); ?>"/>
		<input type="submit" value="<?php esc_html_e( 'Update Discount Code', 'restropress' ); ?>" class="button-primary"/>
	</p>
</form>
