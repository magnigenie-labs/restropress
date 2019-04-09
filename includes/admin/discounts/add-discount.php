<?php
/**
 * Add Discount Page
 *
 * @package     RPRESS
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<h2><?php _e( 'Add New Discount', 'restropress' ); ?></h2>

<?php if ( isset( $_GET['rpress_discount_added'] ) ) : ?>
	<div id="message" class="updated">
		<p><strong><?php _e( 'Discount code created.', 'restropress' ); ?></strong></p>

		<p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=fooditem&page=rpress-discounts' ) ); ?>"><?php _e( '&larr; Back to Discounts', 'restropress' ); ?></a></p>
	</div>
<?php endif; ?>

<form id="rpress-add-discount" action="" method="POST">
	<?php do_action( 'rpress_add_discount_form_top' ); ?>
	<table class="form-table">
		<tbody>
			<?php do_action( 'rpress_add_discount_form_before_name' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-name"><?php _e( 'Name', 'restropress' ); ?></label>
				</th>
				<td>
					<input name="name" required="required" id="rpress-name" type="text" value="" />
					<p class="description"><?php _e( 'Enter the name of this discount.', 'restropress' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'rpress_discount_form_before_code' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-code"><?php _e( 'Code', 'restropress' ); ?></label>
				</th>
				<td>
					<input type="text" required="required" id="rpress-code" name="code" value="" pattern="[a-zA-Z0-9-_]+" />
					<p class="description"><?php _e( 'Enter a code for this discount, such as <span class="rpress-discount-demo"style="background:#FFF; padding: 2px 8px;" > 10PERCENT</span>. Only alphanumeric characters are allowed.', 'restropress' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'rpress_add_discount_form_before_type' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-type"><?php _e( 'Type', 'restropress' ); ?></label>
				</th>
				<td>
					<select name="type" id="rpress-type">
						<option value="percent"><?php _e( 'Percentage', 'restropress' ); ?></option>
						<option value="flat"><?php _e( 'Flat amount', 'restropress' ); ?></option>
					</select>
					<p class="description"><?php _e( 'The kind of discount to apply for this discount.', 'restropress' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'rpress_add_discount_form_before_amount' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-amount"><?php _e( 'Amount', 'restropress' ); ?></label>
				</th>
				<td>
					<input type="text" required="required" class="rpress-price-field" id="rpress-amount" name="amount" value="" />
					<p class="description rpress-amount-description flat-discount" style="display:none;"><?php printf( __( 'Enter the discount amount in %s', 'restropress' ), rpress_get_currency() ); ?></p>
					<p class="description rpress-amount-description percent-discount"><?php _e( 'Enter the discount percentage. 10 = 10%', 'restropress' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'rpress_add_discount_form_before_products' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-products"><?php printf( __( '%s Requirements', 'restropress' ), rpress_get_label_singular() ); ?></label>
				</th>
				<td>
					<p>
						<?php echo RPRESS()->html->product_dropdown( array(
							'name'        => 'products[]',
							'id'          => 'products',
							'multiple'    => true,
							'chosen'      => true,
							'placeholder' => sprintf( __( 'Select one or more %s', 'restropress' ), rpress_get_label_plural() ),
						) ); ?><br/>
					</p>
					<div id="rpress-discount-product-conditions" style="display:none;">
						<p>
							<select id="rpress-product-condition" name="product_condition">
								<option value="all"><?php printf( __( 'Cart must contain all selected %s', 'restropress' ), rpress_get_label_plural() ); ?></option>
								<option value="any"><?php printf( __( 'Cart needs one or more of the selected %s', 'restropress' ), rpress_get_label_plural() ); ?></option>
							</select>
						</p>
						<p>
							<label>
								<input type="radio" class="tog" name="not_global" value="0" checked="checked"/>
								<?php _e( 'Apply discount to entire purchase.', 'restropress' ); ?>
							</label><br/>
							<label>
								<input type="radio" class="tog" name="not_global" value="1"/>
								<?php printf( __( 'Apply discount only to selected %s.', 'restropress' ), rpress_get_label_plural() ); ?>
							</label>
						</p>
					</div>
					<p class="description"><?php printf( __( 'Select %s relevant to this discount. If left blank, this discount can be used on any product.', 'restropress' ), rpress_get_label_plural() ); ?></p>
				</td>
			</tr>
			<?php do_action( 'rpress_add_discount_form_before_excluded_products' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-excluded-products"><?php printf( __( 'Excluded %s', 'restropress' ), rpress_get_label_plural() ); ?></label>
				</th>
				<td>
					<?php echo RPRESS()->html->product_dropdown( array(
						'name'        => 'excluded-products[]',
						'id'          => 'excluded-products',
						'selected'    => array(),
						'multiple'    => true,
						'chosen'      => true,
						'placeholder' => sprintf( __( 'Select one or more %s', 'restropress' ), rpress_get_label_plural() ),
					) ); ?><br/>
					<p class="description"><?php printf( __( '%s that this discount code cannot be applied to.', 'restropress' ), rpress_get_label_plural() ); ?></p>
				</td>
			</tr>
			<?php do_action( 'rpress_add_discount_form_before_start' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-start"><?php _e( 'Start date', 'restropress' ); ?></label>
				</th>
				<td>
					<input name="start" id="rpress-start" type="text" value="" class="rpress_datepicker"/>
					<p class="description"><?php _e( 'Enter the start date for this discount code in the format of mm/dd/yyyy. For no start date, leave blank. If entered, the discount can only be used after or on this date.', 'restropress' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'rpress_add_discount_form_before_expiration' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-expiration"><?php _e( 'Expiration date', 'restropress' ); ?></label>
				</th>
				<td>
					<input name="expiration" id="rpress-expiration" type="text" class="rpress_datepicker"/>
					<p class="description"><?php _e( 'Enter the expiration date for this discount code in the format of mm/dd/yyyy. For no expiration, leave blank.', 'restropress' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'rpress_add_discount_form_before_min_cart_amount' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-min-cart-amount"><?php _e( 'Minimum Amount', 'restropress' ); ?></label>
				</th>
				<td>
					<input type="text" id="rpress-min-cart-amount" name="min_price" value="" />
					<p class="description"><?php _e( 'The minimum dollar amount that must be in the cart before this discount can be used. Leave blank for no minimum.', 'restropress' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'rpress_add_discount_form_before_max_uses' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-max-uses"><?php _e( 'Max Uses', 'restropress' ); ?></label>
				</th>
				<td>
					<input type="text" id="rpress-max-uses" name="max" value="" />
					<p class="description"><?php _e( 'The maximum number of times this discount can be used. Leave blank for unlimited.', 'restropress' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'rpress_add_discount_form_before_use_once' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="rpress-use-once"><?php _e( 'Use Once Per Customer', 'restropress' ); ?></label>
				</th>
				<td>
					<input type="checkbox" id="rpress-use-once" name="use_once" value="1"/>
					<span class="description"><?php _e( 'Limit this discount to a single-use per customer?', 'restropress' ); ?></span>
				</td>
			</tr>
		</tbody>
	</table>
	<?php do_action( 'rpress_add_discount_form_bottom' ); ?>
	<p class="submit">
		<input type="hidden" name="rpress-action" value="add_discount"/>
		<input type="hidden" name="rpress-redirect" value="<?php echo esc_url( admin_url( 'edit.php?post_type=fooditem&page=rpress-discounts' ) ); ?>"/>
		<input type="hidden" name="rpress-discount-nonce" value="<?php echo wp_create_nonce( 'rpress_discount_nonce' ); ?>"/>
		<input type="submit" value="<?php _e( 'Add Discount Code', 'restropress' ); ?>" class="button-primary"/>
	</p>
</form>
