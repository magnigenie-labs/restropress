<?php
/**
 * Thickbox
 *
 * @package     RPRESS
 * @subpackage  Admin
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Adds an "Insert Download" button above the TinyMCE Editor on add/edit screens.
 *
 * @since 1.0
 * @return string "Insert Download" Button
 */
function rpress_media_button() {
	global $pagenow, $typenow;
	$output = '';

	/** Only run in post/page creation and edit screens */
	if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) && $typenow != 'fooditem' ) {

		$img = '<span class="wp-media-buttons-icon dashicons dashicons-fooditem" id="rpress-media-button"></span>';
		$output = '<a href="#TB_inline?width=640&inlineId=choose-fooditem" class="thickbox button rpress-thickbox" style="padding-left: .4em;">' . $img . sprintf( __( 'Insert %s', 'restropress' ), strtolower( rpress_get_label_singular() ) ) . '</a>';

	}

	echo wp_kses_data( $output );
}
add_action( 'media_buttons', 'rpress_media_button', 11 );

/**
 * Admin Footer For Thickbox
 *
 * Prints the footer code needed for the Insert Download
 * TinyMCE button.
 *
 * @since 1.0
 * @global $pagenow
 * @global $typenow
 * @return void
 */
function rpress_admin_footer_for_thickbox() {
	global $pagenow, $typenow;

	// Only run in post/page creation and edit screens
	if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) && $typenow != 'fooditem' ) { ?>
		<script type="text/javascript">
			function insertDownload() {
				var id = jQuery('#products').val(),
					direct = jQuery('#select-rpress-direct').val(),
					style = jQuery('#select-rpress-style').val(),
					color = jQuery('#select-rpress-color').is(':visible') ? jQuery('#select-rpress-color').val() : '',
					text = jQuery('#rpress-text').val() || '<?php esc_html_e( "Purchase", "restro-press" ); ?>';

				// Return early if no fooditem is selected
				if ('' === id) {
					alert('<?php esc_html_e( "You must choose a fooditem", "restro-press" ); ?>');
					return;
				}

				if( '2' == direct ) {
					direct = ' direct="true"';
				} else {
					direct = '';
				}

				// Send the shortcode to the editor
				window.send_to_editor('[purchase_link id="' + id + '" style="' + style + '" color="' + color + '" text="' + text + '"' + direct +']');
			}
			jQuery(document).ready(function ($) {
				$('#select-rpress-style').change(function () {
					if ($(this).val() === 'button') {
						$('#rpress-color-choice').slideDown();
					} else {
						$('#rpress-color-choice').slideUp();
					}
				});
			});
		</script>

		<div id="choose-fooditem" style="display: none;">
			<div class="wrap" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
				<p><?php echo sprintf( __( 'Use the form below to insert the shortcode for purchasing a %s', 'restropress' ), rpress_get_label_singular() ); ?></p>
				<div>
					<?php echo RPRESS()->html->product_dropdown( array( 'chosen' => true )); ?>
				</div>
				<?php if( rpress_shop_supports_buy_now() ) : ?>
					<div>
						<select id="select-rpress-direct" style="clear: both; display: block; margin-bottom: 1em; margin-top: 1em;">
							<option value="0"><?php esc_html_e( 'Choose the button behavior', 'restropress' ); ?></option>
							<option value="1"><?php esc_html_e( 'Add to Cart', 'restropress' ); ?></option>
							<option value="2"><?php esc_html_e( 'Direct Purchase Link', 'restropress' ); ?></option>
						</select>
					</div>
				<?php endif; ?>
				<div>
					<select id="select-rpress-style" style="clear: both; display: block; margin-bottom: 1em; margin-top: 1em;">
						<option value=""><?php esc_html_e( 'Choose a style', 'restropress' ); ?></option>
						<?php
							$styles = array( 'button', 'text link' );
							foreach ( $styles as $style ) {
								echo '<option value="' . $style . '">' . $style . '</option>';
							}
						?>
					</select>
				</div>
				<?php
				$colors = rpress_get_button_colors();
				if( $colors ) { ?>
				<div id="rpress-color-choice" style="display: none;">
					<select id="select-rpress-color" style="clear: both; display: block; margin-bottom: 1em;">
						<option value=""><?php esc_html_e('Choose a button color','restropress' ); ?></option>
						<?php
							foreach ( $colors as $key => $color ) {
								echo '<option value="' . str_replace( ' ', '_', $key ) . '">' . $color['label'] . '</option>';
							}
						?>
					</select>
				</div>
				<?php } ?>
				<div>
					<input type="text" class="regular-text" id="rpress-text" value="" placeholder="<?php esc_html_e( 'Link text . . .', 'restropress' ); ?>"/>
				</div>
				<p class="submit">
					<input type="button" id="rpress-insert-fooditem" class="button-primary" value="<?php echo sprintf( __( 'Insert %s', 'restropress' ), rpress_get_label_singular() ); ?>" onclick="insertDownload();" />
					<a id="rpress-cancel-fooditem-insert" class="button-secondary" onclick="tb_remove();"><?php esc_html_e( 'Cancel', 'restropress' ); ?></a>
				</p>
			</div>
		</div>
	<?php
	}
}
add_action( 'admin_footer', 'rpress_admin_footer_for_thickbox' );
