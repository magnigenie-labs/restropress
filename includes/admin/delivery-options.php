<?php
/**
 * Delivery Options
 *
 * These are functions used for displaying delivery options.
 *
 * @package     RPRESS
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function rpress_delivery_options() {
	?>
	<div class="wrap">
		<h2><?php _e( 'RestroPress Delivery Options', 'restropress' ); ?></h2>
		<div class="inside">
			<p><?php _e( 'Setup Delivery options here', 'restropress' ); ?></p>
			<form method="post" id="delivery-options" action="<?php echo admin_url( 'edit.php?post_type=fooditem&page=rpress-delivery-options' ); ?>">
				<label for="delivery">Delivery
					<input type="checkbox" value="yes" id="delivery" name="rpress_delivery[]">
				</label>
				<label  for="pickup">Pickup
					<input type="checkbox" value="yes" id="pickup" name="rpress_delivery[]">
				</label>
				
				<div>
					<?php submit_button( __( 'Save', 'restropress' ), 'secondary', 'submit', false ); ?>
				</div>
				
			</form>
		</div>
		
	</div>
	<?php
}