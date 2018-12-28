<?php
/**
 * Tools
 *
 * These are functions used for displaying RPRESS tools such as the import/export system.
 *
 * @package     RPRESS
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Tools
 *
 * Shows the tools panel which contains RPRESS-specific tools including the
 * built-in import/export system.
 *
 * @since 1.0
 * @author      RestroPress
 * @return      void
 */
function rpress_tools_page() {
	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
?>
	<div class="wrap">
		<h2><?php _e( 'RestroPress Tools', 'restro-press' ); ?></h2>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( rpress_get_tools_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'tab' => $tab_id
				) );

				$tab_url = remove_query_arg( array(
					'rpress-message'
				), $tab_url );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';
				echo '<a href="' . esc_url( $tab_url ) . '" class="nav-tab' . $active . '">' . esc_html( $tab_name ) . '</a>';

			}
			?>
		</h2>
		<div class="metabox-holder">
			<?php
			do_action( 'rpress_tools_tab_' . $active_tab );
			?>
		</div><!-- .metabox-holder -->
	</div><!-- .wrap -->
<?php
}


/**
 * Retrieve tools tabs
 *
 * @since       2.0
 * @return      array
 */
function rpress_get_tools_tabs() {

	$tabs                  = array();
	$tabs['general']       = __( 'General', 'restro-press' );

	if( count( rpress_get_beta_enabled_extensions() ) > 0 ) {
		$tabs['betas'] = __( 'Beta Versions', 'restro-press' );
	}

	$tabs['system_info']   = __( 'System Info', 'restro-press' );

	if( rpress_is_debug_mode() ) {
		$tabs['debug_log'] = __( 'Debug Log', 'restro-press' );
	}

	$tabs['import_export'] = __( 'Import/Export', 'restro-press' );

	return apply_filters( 'rpress_tools_tabs', $tabs );
}


/**
 * Display the ban emails tab
 *
 * @since       2.0
 * @return      void
 */
function rpress_tools_banned_emails_display() {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	do_action( 'rpress_tools_banned_emails_before' );
?>
	<div class="postbox">
		<h3><span><?php _e( 'Banned Emails', 'restro-press' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Emails placed in the box below will not be allowed to make purchases.', 'restro-press' ); ?></p>
			<form method="post" action="<?php echo admin_url( 'edit.php?post_type=fooditem&page=rpress-tools&tab=general' ); ?>">
				<p>
					<textarea name="banned_emails" rows="10" class="large-text"><?php echo implode( "\n", rpress_get_banned_emails() ); ?></textarea>
					<span class="description"><?php _e( 'Enter emails and/or domains (starting with "@") and/or TLDs (starting with ".") to disallow, one per line.', 'restro-press' ); ?></span>
				</p>
				<p>
					<input type="hidden" name="rpress_action" value="save_banned_emails" />
					<?php wp_nonce_field( 'rpress_banned_emails_nonce', 'rpress_banned_emails_nonce' ); ?>
					<?php submit_button( __( 'Save', 'restro-press' ), 'secondary', 'submit', false ); ?>
				</p>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->
<?php
	do_action( 'rpress_tools_banned_emails_after' );
	do_action( 'rpress_tools_after' );
}
add_action( 'rpress_tools_tab_general', 'rpress_tools_banned_emails_display' );


/**
 * Display the recount stats
 *
 * @since 1.0
 * @return      void
 */
function rpress_tools_recount_stats_display() {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	do_action( 'rpress_tools_recount_stats_before' );
?>
	<div class="postbox">
		<h3><span><?php _e( 'Recount Stats', 'restro-press' ); ?></span></h3>
		<div class="inside recount-stats-controls">
			<p><?php _e( 'Use these tools to recount / reset store stats.', 'restro-press' ); ?></p>
			<form method="post" id="rpress-tools-recount-form" class="rpress-export-form rpress-import-export-form">
				<span>

					<?php wp_nonce_field( 'rpress_ajax_export', 'rpress_ajax_export' ); ?>

					<select name="rpress-export-class" id="recount-stats-type">
						<option value="0" selected="selected" disabled="disabled"><?php _e( 'Please select an option', 'restro-press' ); ?></option>
						<option data-type="recount-store" value="RPRESS_Tools_Recount_Store_Earnings"><?php _e( 'Recount Store Earnings and Sales', 'restro-press' ); ?></option>
						<option data-type="recount-fooditem" value="RPRESS_Tools_Recount_Download_Stats"><?php printf( __( 'Recount Earnings and Sales for a %s', 'restro-press' ), rpress_get_label_singular( true ) ); ?></option>
						<option data-type="recount-all" value="RPRESS_Tools_Recount_All_Stats"><?php printf( __( 'Recount Earnings and Sales for All %s', 'restro-press' ), rpress_get_label_plural( true ) ); ?></option>
						<option data-type="recount-customer-stats" value="RPRESS_Tools_Recount_Customer_Stats"><?php _e( 'Recount Customer Stats', 'restro-press' ); ?></option>
						<?php do_action( 'rpress_recount_tool_options' ); ?>
						<option data-type="reset-stats" value="RPRESS_Tools_Reset_Stats"><?php _e( 'Reset Store', 'restro-press' ); ?></option>
					</select>

					<span id="tools-product-dropdown" style="display: none">
						<?php
							$args = array(
								'name'   => 'fooditem_id',
								'number' => -1,
								'chosen' => true,
							);
							echo RPRESS()->html->product_dropdown( $args );
						?>
					</span>

					<input type="submit" id="recount-stats-submit" value="<?php _e( 'Submit', 'restro-press' ); ?>" class="button-secondary"/>

					<br />

					<span class="rpress-recount-stats-descriptions">
						<span id="recount-store"><?php _e( 'Recalculates the total store earnings and sales.', 'restro-press' ); ?></span>
						<span id="recount-fooditem"><?php printf( __( 'Recalculates the earnings and sales stats for a specific %s.', 'restro-press' ), rpress_get_label_singular( true ) ); ?></span>
						<span id="recount-all"><?php printf( __( 'Recalculates the earnings and sales stats for all %s.', 'restro-press' ), rpress_get_label_plural( true ) ); ?></span>
						<span id="recount-customer-stats"><?php _e( 'Recalculates the lifetime value and purchase counts for all customers.', 'restro-press' ); ?></span>
						<?php do_action( 'rpress_recount_tool_descriptions' ); ?>
						<span id="reset-stats"><?php _e( '<strong>Deletes</strong> all payment records, customers, and related log entries.', 'restro-press' ); ?></span>
					</span>

					<span class="spinner"></span>

				</span>
			</form>
			<?php do_action( 'rpress_tools_recount_forms' ); ?>
		</div><!-- .inside -->
	</div><!-- .postbox -->
<?php
	do_action( 'rpress_tools_recount_stats_after' );
}
add_action( 'rpress_tools_tab_general', 'rpress_tools_recount_stats_display' );

/**
 * Display the clear upgrades tab
 *
 * @since       2.3.5
 * @return      void
 */
function rpress_tools_clear_doing_upgrade_display() {

	if( ! current_user_can( 'manage_shop_settings' ) || false === get_option( 'rpress_doing_upgrade' ) ) {
		return;
	}

	do_action( 'rpress_tools_clear_doing_upgrade_before' );
?>
	<div class="postbox">
		<h3><span><?php _e( 'Clear Incomplete Upgrade Notice', 'restro-press' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Sometimes a database upgrade notice may not be cleared after an upgrade is completed due to conflicts with other extensions or other minor issues.', 'restro-press' ); ?></p>
			<p><?php _e( 'If you\'re certain these upgrades have been completed, you can clear these upgrade notices by clicking the button below. If you have any questions about this, please contact the RestroPress support team and we\'ll be happy to help.', 'restro-press' ); ?></p>
			<form method="post" action="<?php echo admin_url( 'edit.php?post_type=fooditem&page=rpress-tools&tab=general' ); ?>">
				<p>
					<input type="hidden" name="rpress_action" value="clear_doing_upgrade" />
					<?php wp_nonce_field( 'rpress_clear_upgrades_nonce', 'rpress_clear_upgrades_nonce' ); ?>
					<?php submit_button( __( 'Clear Incomplete Upgrade Notice', 'restro-press' ), 'secondary', 'submit', false ); ?>
				</p>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->
<?php
	do_action( 'rpress_tools_clear_doing_upgrade_after' );
}
add_action( 'rpress_tools_tab_general', 'rpress_tools_clear_doing_upgrade_display' );



/**
 * Display beta opt-ins
 *
 * @since 1.01
 * @return      void
 */
function rpress_tools_betas_display() {
	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	$has_beta = rpress_get_beta_enabled_extensions();

	do_action( 'rpress_tools_betas_before' );
	?>

	<div class="postbox rpress-beta-support">
		<h3><span><?php _e( 'Enable Beta Versions', 'restro-press' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Checking any of the below checkboxes will opt you in to receive pre-release update notifications. You can opt-out at any time. Pre-release updates do not install automatically, you will still have the opportunity to ignore update notifications.', 'restro-press' ); ?></p>
			<form method="post" action="<?php echo admin_url( 'edit.php?post_type=fooditem&page=rpress-tools&tab=betas' ); ?>">
				<table class="form-table rpress-beta-support">
					<tbody>
						<?php foreach( $has_beta as $slug => $product ) : ?>
							<tr>
								<?php $checked = rpress_extension_has_beta_support( $slug ); ?>
								<th scope="row"><?php echo esc_html( $product ); ?></th>
								<td>
									<input type="checkbox" name="enabled_betas[<?php echo esc_attr( $slug ); ?>]" id="enabled_betas[<?php echo esc_attr( $slug ); ?>]"<?php echo checked( $checked, true, false ); ?> value="1" />
									<label for="enabled_betas[<?php echo esc_attr( $slug ); ?>]"><?php printf( __( 'Get updates for pre-release versions of %s', 'restro-press' ), $product ); ?></label>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<input type="hidden" name="rpress_action" value="save_enabled_betas" />
				<?php wp_nonce_field( 'rpress_save_betas_nonce', 'rpress_save_betas_nonce' ); ?>
				<?php submit_button( __( 'Save', 'restro-press' ), 'secondary', 'submit', false ); ?>
			</form>
		</div>
	</div>

	<?php
	do_action( 'rpress_tools_betas_after' );
}
add_action( 'rpress_tools_tab_betas', 'rpress_tools_betas_display' );


/**
 * Return an array of all extensions with beta support
 *
 * Extensions should be added as 'extension-slug' => 'Extension Name'
 *
 * @since 1.01
 * @return      array $extensions The array of extensions
 */
function rpress_get_beta_enabled_extensions() {
	return apply_filters( 'rpress_beta_enabled_extensions', array() );
}


/**
 * Check if a given extensions has beta support enabled
 *
 * @since 1.01
 * @param       string $slug The slug of the extension to check
 * @return      bool True if enabled, false otherwise
 */
function rpress_extension_has_beta_support( $slug ) {
	$enabled_betas = rpress_get_option( 'enabled_betas', array() );
	$return        = false;

	if( array_key_exists( $slug, $enabled_betas ) ) {
		$return = true;
	}

	return $return;
}


/**
 * Save enabled betas
 *
 * @since 1.01
 * @return      void
 */
function rpress_tools_enabled_betas_save() {
	if( ! wp_verify_nonce( $_POST['rpress_save_betas_nonce'], 'rpress_save_betas_nonce' ) ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if( ! empty( $_POST['enabled_betas'] ) ) {
		$enabled_betas = array_filter( array_map( 'rpress_tools_enabled_betas_sanitize_value', $_POST['enabled_betas'] ) );
		rpress_update_option( 'enabled_betas', $enabled_betas );
	} else {
		rpress_delete_option( 'enabled_betas' );
	}
}
add_action( 'rpress_save_enabled_betas', 'rpress_tools_enabled_betas_save' );

/**
 * Sanitize the supported beta values by making them booleans
 *
 * @since 1.0.0.11
 * @param mixed $value The value being sent in, determining if beta support is enabled.
 *
 * @return bool
 */
function rpress_tools_enabled_betas_sanitize_value( $value ) {
	return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
}


/**
 * Save banned emails
 *
 * @since       2.0
 * @return      void
 */
function rpress_tools_banned_emails_save() {

	if( ! wp_verify_nonce( $_POST['rpress_banned_emails_nonce'], 'rpress_banned_emails_nonce' ) ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if( ! empty( $_POST['banned_emails'] ) ) {

		// Sanitize the input
		$emails = array_map( 'trim', explode( "\n", $_POST['banned_emails'] ) );
		$emails = array_unique( $emails );
		$emails = array_map( 'sanitize_text_field', $emails );

		foreach( $emails as $id => $email ) {
			if( ! is_email( $email ) && $email[0] != '@' && $email[0] != '.' ) {
				unset( $emails[$id] );
			}
		}
	} else {
		$emails = '';
	}

	rpress_update_option( 'banned_emails', $emails );
}
add_action( 'rpress_save_banned_emails', 'rpress_tools_banned_emails_save' );

/**
 * Execute upgrade notice clear
 *
 * @since       2.3.5
 * @return      void
 */
function rpress_tools_clear_upgrade_notice() {
	if( ! wp_verify_nonce( $_POST['rpress_clear_upgrades_nonce'], 'rpress_clear_upgrades_nonce' ) ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	delete_option( 'rpress_doing_upgrade' );
}
add_action( 'rpress_clear_doing_upgrade', 'rpress_tools_clear_upgrade_notice' );


/**
 * Display the tools import/export tab
 *
 * @since       2.0
 * @return      void
 */
function rpress_tools_import_export_display() {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	do_action( 'rpress_tools_import_export_before' );
?>

	<div class="postbox rpress-import-payment-history">
		<h3><span><?php _e( 'Import Payment History', 'restro-press' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Import a CSV file of payment records.', 'restro-press' ); ?></p>
			<form id="rpress-import-payments" class="rpress-import-form rpress-import-export-form" action="<?php echo esc_url( add_query_arg( 'rpress_action', 'upload_import_file', admin_url() ) ); ?>" method="post" enctype="multipart/form-data">

				<div class="rpress-import-file-wrap">
					<?php wp_nonce_field( 'rpress_ajax_import', 'rpress_ajax_import' ); ?>
					<input type="hidden" name="rpress-import-class" value="RPRESS_Batch_Payments_Import"/>
					<p>
						<input name="rpress-import-file" id="rpress-payments-import-file" type="file" />
					</p>
					<span>
						<input type="submit" value="<?php _e( 'Import CSV', 'restro-press' ); ?>" class="button-secondary"/>
						<span class="spinner"></span>
					</span>
				</div>

				<div class="rpress-import-options" id="rpress-import-payments-options" style="display:none;">

					<p>
						<?php
						printf(
							__( 'Each column loaded from the CSV needs to be mapped to a payment field. Select the column that should be mapped to each field below. Any columns not needed can be ignored. See <a href="%s" target="_blank">this guide</a> for assistance with importing payment records.', 'restro-press' ),
							'http://docs.fooditems.com/category/1337-importexport'
						);
						?>
					</p>

					<table class="widefat rpress_repeatable_table striped" width="100%" cellpadding="0" cellspacing="0">
						<thead>
							<tr>
								<th><strong><?php _e( 'Payment Field', 'restro-press' ); ?></strong></th>
								<th><strong><?php _e( 'CSV Column', 'restro-press' ); ?></strong></th>
								<th><strong><?php _e( 'Data Preview', 'restro-press' ); ?></strong></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><?php _e( 'Currency Code', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[currency]" class="rpress-import-csv-column" data-field="Currency">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Email', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[email]" class="rpress-import-csv-column" data-field="Email">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'First Name', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[first_name]" class="rpress-import-csv-column" data-field="First Name">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Last Name', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[last_name]" class="rpress-import-csv-column" data-field="Last Name">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Customer ID', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[customer_id]" class="rpress-import-csv-column" data-field="Customer ID">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Discount Code(s)', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[discounts]" class="rpress-import-csv-column" data-field="Discount Code">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'IP Address', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[ip]" class="rpress-import-csv-column" data-field="IP Address">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Mode (Live|Test)', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[mode]" class="rpress-import-csv-column" data-field="Mode (Live|Test)">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Parent Payment ID', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[parent_payment_id]" class="rpress-import-csv-column" data-field="">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Payment Method', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[gateway]" class="rpress-import-csv-column" data-field="Payment Method">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Payment Number', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[number]" class="rpress-import-csv-column" data-field="Payment Number">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Date', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[date]" class="rpress-import-csv-column" data-field="Date">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Purchase Key', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[key]" class="rpress-import-csv-column" data-field="Purchase Key">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Purchased Product(s)', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[fooditems]" class="rpress-import-csv-column" data-field="Products (Raw)">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Status', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[status]" class="rpress-import-csv-column" data-field="Status">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Subtotal', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[subtotal]" class="rpress-import-csv-column" data-field="">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Tax', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[tax]" class="rpress-import-csv-column" data-field="Tax ($)">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Total', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[total]" class="rpress-import-csv-column" data-field="Amount ($)">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Transaction ID', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[transaction_id]" class="rpress-import-csv-column" data-field="Transaction ID">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'User', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[user_id]" class="rpress-import-csv-column" data-field="User">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Address Line 1', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[line1]" class="rpress-import-csv-column" data-field="Address">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Address Line 2', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[line2]" class="rpress-import-csv-column" data-field="Address (Line 2)">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'City', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[city]" class="rpress-import-csv-column" data-field="City">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'State / Province', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[state]" class="rpress-import-csv-column" data-field="State">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Zip / Postal Code', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[zip]" class="rpress-import-csv-column" data-field="Zip / Postal Code">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
							<tr>
								<td><?php _e( 'Country', 'restro-press' ); ?></td>
								<td>
									<select name="rpress-import-field[country]" class="rpress-import-csv-column" data-field="Country">
										<option value=""><?php _e( '- Ignore this field -', 'restro-press' ); ?></option>
									</select>
								</td>
								<td class="rpress-import-preview-field"><?php _e( '- select field to preview data -', 'restro-press' ); ?></td>
							</tr>
						</tbody>
					</table>
					<p class="submit">
						<button class="rpress-import-proceed button-primary"><?php _e( 'Process Import', 'restro-press' ); ?></button>
					</p>
				</div>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->

	
	<div class="postbox">
		<h3><span><?php _e( 'Export Settings', 'restro-press' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Export the RestroPress settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'restro-press' ); ?></p>
			<p><?php printf( __( 'To export shop data (purchases, customers, etc), visit the <a href="%s">Reports</a> page.', 'restro-press' ), admin_url( 'edit.php?post_type=fooditem&page=rpress-reports&tab=export' ) ); ?></p>
			<form method="post" action="<?php echo admin_url( 'edit.php?post_type=fooditem&page=rpress-tools&tab=import_export' ); ?>">
				<p><input type="hidden" name="rpress_action" value="export_settings" /></p>
				<p>
					<?php wp_nonce_field( 'rpress_export_nonce', 'rpress_export_nonce' ); ?>
					<?php submit_button( __( 'Export', 'restro-press' ), 'secondary', 'submit', false ); ?>
				</p>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->

	<div class="postbox">
		<h3><span><?php _e( 'Import Settings', 'restro-press' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Import the RestroPress settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'restro-press' ); ?></p>
			<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'edit.php?post_type=fooditem&page=rpress-tools&tab=import_export' ); ?>">
				<p>
					<input type="file" name="import_file"/>
				</p>
				<p>
					<input type="hidden" name="rpress_action" value="import_settings" />
					<?php wp_nonce_field( 'rpress_import_nonce', 'rpress_import_nonce' ); ?>
					<?php submit_button( __( 'Import', 'restro-press' ), 'secondary', 'submit', false ); ?>
				</p>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->
<?php
	do_action( 'rpress_tools_import_export_after' );
}
add_action( 'rpress_tools_tab_import_export', 'rpress_tools_import_export_display' );


/**
 * Process a settings export that generates a .json file of the shop settings
 *
 * @since 1.0
 * @return      void
 */
function rpress_tools_import_export_process_export() {

	if( empty( $_POST['rpress_export_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['rpress_export_nonce'], 'rpress_export_nonce' ) )
		return;

	if( ! current_user_can( 'manage_shop_settings' ) )
		return;

	$rpress_settings  = get_option( 'rpress_settings' );
	$rpress_tax_rates = get_option( 'rpress_tax_rates' );
	$settings = array(
		'rpress_settings'  => $rpress_settings,
		'rpress_tax_rates' => $rpress_tax_rates,
	);

	ignore_user_abort( true );

	if ( ! rpress_is_func_disabled( 'set_time_limit' ) )
		set_time_limit( 0 );

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . apply_filters( 'rpress_settings_export_filename', 'rpress-settings-export-' . date( 'm-d-Y' ) ) . '.json' );
	header( "Expires: 0" );

	echo json_encode( $settings );
	exit;
}
add_action( 'rpress_export_settings', 'rpress_tools_import_export_process_export' );


/**
 * Process a settings import from a json file
 *
 * @since  1.0.0
 * @return void
 */
function rpress_tools_import_export_process_import() {

	if( empty( $_POST['rpress_import_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['rpress_import_nonce'], 'rpress_import_nonce' ) )
		return;

	if( ! current_user_can( 'manage_shop_settings' ) )
		return;

	if( rpress_get_file_extension( $_FILES['import_file']['name'] ) != 'json' ) {
		wp_die( __( 'Please upload a valid .json file', 'restro-press' ), __( 'Error', 'restro-press' ), array( 'response' => 400 ) );
	}

	$import_file = $_FILES['import_file']['tmp_name'];

	if( empty( $import_file ) ) {
		wp_die( __( 'Please upload a file to import', 'restro-press' ), __( 'Error', 'restro-press' ), array( 'response' => 400 ) );
	}

	// Retrieve the settings from the file and convert the json object to an array
	$settings = rpress_object_to_array( json_decode( file_get_contents( $import_file ) ) );

	if ( ! isset( $settings['rpress_settings'] ) ) {

		// Process a settings export from a pre 2.8 version of RPRESS
		update_option( 'rpress_settings', $settings );

	} else {

		// Update the settings from a 2.8+ export file
		$rpress_settings  = $settings['rpress_settings'];
		update_option( 'rpress_settings', $rpress_settings );

		$rpress_tax_rates = $settings['rpress_tax_rates'];
		update_option( 'rpress_tax_rates', $rpress_tax_rates );

	}



	wp_safe_redirect( admin_url( 'edit.php?post_type=fooditem&page=rpress-tools&rpress-message=settings-imported' ) ); exit;

}
add_action( 'rpress_import_settings', 'rpress_tools_import_export_process_import' );


/**
 * Display the debug log tab
 *
 * @since 1.0.7
 * @return      void
 */
function rpress_tools_debug_log_display() {

	global $rpress_logs;

	if( ! current_user_can( 'manage_shop_settings' ) || ! rpress_is_debug_mode() ) {
		return;
	}

?>
	<div class="postbox">
		<h3><span><?php esc_html_e( 'Debug Log', 'restro-press' ); ?></span></h3>
		<div class="inside">
			<form id="rpress-debug-log" method="post">
				<textarea readonly="readonly" class="large-text" rows="15" name="rpress-debug-log-contents"><?php echo esc_textarea( $rpress_logs->get_file_contents() ); ?></textarea>
				<p class="submit">
					<input type="hidden" name="rpress_action" value="submit_debug_log" />
					<?php
					submit_button( __( 'Download Debug Log File', 'restro-press' ), 'primary', 'rpress-fooditem-debug-log', false );
					submit_button( __( 'Clear Log', 'restro-press' ), 'secondary rpress-inline-button', 'rpress-clear-debug-log', false );
					submit_button( __( 'Copy Entire Log', 'restro-press' ), 'secondary rpress-inline-button', 'rpress-copy-debug-log', false, array( 'onclick' => "this.form['rpress-debug-log-contents'].focus();this.form['rpress-debug-log-contents'].select();document.execCommand('copy');return false;" ) );
					?>
				</p>
				<?php wp_nonce_field( 'rpress-debug-log-action' ); ?>
			</form>
			<p><?php _e( 'Log file', 'restro-press' ); ?>: <code><?php echo $rpress_logs->get_log_file_path(); ?></code></p>
		</div><!-- .inside -->
	</div><!-- .postbox -->
<?php
}
add_action( 'rpress_tools_tab_debug_log', 'rpress_tools_debug_log_display' );

/**
 * Handles submit actions for the debug log.
 *
 * @since 1.0
 */
function rpress_handle_submit_debug_log() {

	global $rpress_logs;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	check_admin_referer( 'rpress-debug-log-action' );

	if ( isset( $_REQUEST['rpress-fooditem-debug-log'] ) ) {
		nocache_headers();

		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="rpress-debug-log.txt"' );

		echo wp_strip_all_tags( $_REQUEST['rpress-debug-log-contents'] );
		exit;

	} elseif ( isset( $_REQUEST['rpress-clear-debug-log'] ) ) {

		// Clear the debug log.
		$rpress_logs->clear_log_file();

		wp_safe_redirect( admin_url( 'edit.php?post_type=fooditem&page=rpress-tools&tab=debug_log' ) );
		exit;

	}
}
add_action( 'rpress_submit_debug_log', 'rpress_handle_submit_debug_log' );

/**
 * Display the system info tab
 *
 * @since       2.0
 * @return      void
 */
function rpress_tools_sysinfo_display() {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

?>
	<form action="<?php echo esc_url( admin_url( 'edit.php?post_type=fooditem&page=rpress-tools&tab=system_info' ) ); ?>" method="post" dir="ltr">
		<textarea readonly="readonly" onclick="this.focus(); this.select()" id="system-info-textarea" name="rpress-sysinfo"><?php echo rpress_tools_sysinfo_get(); ?></textarea>
		<p class="submit">
			<input type="hidden" name="rpress-action" value="fooditem_sysinfo" />
			<?php submit_button( 'Download System Info File', 'primary', 'rpress-fooditem-sysinfo', false ); ?>
		</p>
	</form>
<?php
}
add_action( 'rpress_tools_tab_system_info', 'rpress_tools_sysinfo_display' );


/**
 * Get system info
 *
 * @since       2.0
 * @global      object $wpdb Used to query the database using the WordPress Database API
 * @return      string $return A string containing the info to output
 */
function rpress_tools_sysinfo_get() {
	global $wpdb;

	if( !class_exists( 'Browser' ) )
		require_once RPRESS_PLUGIN_DIR . 'includes/libraries/browser.php';

	$browser = new Browser();

	// Get theme info
	$theme_data   = wp_get_theme();
	$theme        = $theme_data->Name . ' ' . $theme_data->Version;
	$parent_theme = $theme_data->Template;
	if ( ! empty( $parent_theme ) ) {
		$parent_theme_data = wp_get_theme( $parent_theme );
		$parent_theme      = $parent_theme_data->Name . ' ' . $parent_theme_data->Version;
	}

	// Try to identify the hosting provider
	$host = rpress_get_host();

	$return  = '### Begin System Info ###' . "\n\n";

	// Start with the basics...
	$return .= '-- Site Info' . "\n\n";
	$return .= 'Site URL:                 ' . site_url() . "\n";
	$return .= 'Home URL:                 ' . home_url() . "\n";
	$return .= 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n";

	$return  = apply_filters( 'rpress_sysinfo_after_site_info', $return );

	// Can we determine the site's host?
	if( $host ) {
		$return .= "\n" . '-- Hosting Provider' . "\n\n";
		$return .= 'Host:                     ' . $host . "\n";

		$return  = apply_filters( 'rpress_sysinfo_after_host_info', $return );
	}

	// The local users' browser information, handled by the Browser class
	$return .= "\n" . '-- User Browser' . "\n\n";
	$return .= $browser;

	$return  = apply_filters( 'rpress_sysinfo_after_user_browser', $return );

	$locale = get_locale();

	// WordPress configuration
	$return .= "\n" . '-- WordPress Configuration' . "\n\n";
	$return .= 'Version:                  ' . get_bloginfo( 'version' ) . "\n";
	$return .= 'Language:                 ' . ( !empty( $locale ) ? $locale : 'en_US' ) . "\n";
	$return .= 'Permalink Structure:      ' . ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . "\n";
	$return .= 'Active Theme:             ' . $theme . "\n";
	if ( $parent_theme !== $theme ) {
		$return .= 'Parent Theme:             ' . $parent_theme . "\n";
	}
	$return .= 'Show On Front:            ' . get_option( 'show_on_front' ) . "\n";

	// Only show page specs if frontpage is set to 'page'
	if( get_option( 'show_on_front' ) == 'page' ) {
		$front_page_id = get_option( 'page_on_front' );
		$blog_page_id = get_option( 'page_for_posts' );

		$return .= 'Page On Front:            ' . ( $front_page_id != 0 ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : 'Unset' ) . "\n";
		$return .= 'Page For Posts:           ' . ( $blog_page_id != 0 ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' ) . "\n";
	}

	$return .= 'ABSPATH:                  ' . ABSPATH . "\n";

	// Make sure wp_remote_post() is working
	$request['cmd'] = '_notify-validate';

	$params = array(
		'sslverify'     => false,
		'timeout'       => 60,
		'user-agent'    => 'RPRESS/' . RPRESS_VERSION,
		'body'          => $request
	);

	$response = wp_remote_post( 'https://www.paypal.com/cgi-bin/webscr', $params );

	if( !is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
		$WP_REMOTE_POST = 'wp_remote_post() works';
	} else {
		$WP_REMOTE_POST = 'wp_remote_post() does not work';
	}

	$return .= 'Remote Post:              ' . $WP_REMOTE_POST . "\n";
	$return .= 'Table Prefix:             ' . 'Length: ' . strlen( $wpdb->prefix ) . '   Status: ' . ( strlen( $wpdb->prefix ) > 16 ? 'ERROR: Too long' : 'Acceptable' ) . "\n";
	//$return .= 'Admin AJAX:               ' . ( rpress_test_ajax_works() ? 'Accessible' : 'Inaccessible' ) . "\n";
	$return .= 'WP_DEBUG:                 ' . ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n";
	$return .= 'Memory Limit:             ' . WP_MEMORY_LIMIT . "\n";
	$return .= 'Registered Post Stati:    ' . implode( ', ', get_post_stati() ) . "\n";

	$return  = apply_filters( 'rpress_sysinfo_after_wordpress_config', $return );

	// RPRESS configuration
	$return .= "\n" . '-- RPRESS Configuration' . "\n\n";
	$return .= 'Version:                  ' . RPRESS_VERSION . "\n";
	$return .= 'Upgraded From:            ' . get_option( 'rpress_version_upgraded_from', 'None' ) . "\n";
	$return .= 'Test Mode:                ' . ( rpress_is_test_mode() ? "Enabled\n" : "Disabled\n" );
	$return .= 'AJAX:                     ' . ( ! rpress_is_ajax_disabled() ? "Enabled\n" : "Disabled\n" );
	$return .= 'Guest Checkout:           ' . ( rpress_no_guest_checkout() ? "Disabled\n" : "Enabled\n" );
	$return .= 'Symlinks:                 ' . ( apply_filters( 'rpress_symlink_file_fooditems', rpress_get_option( 'symlink_file_fooditems', false ) ) && function_exists( 'symlink' ) ? "Enabled\n" : "Disabled\n" );
	$return .= 'Download Method:          ' . ucfirst( rpress_get_file_fooditem_method() ) . "\n";
	$return .= 'Currency Code:            ' . rpress_get_currency() . "\n";
	$return .= 'Currency Position:        ' . rpress_get_option( 'currency_position', 'before' ) . "\n";
	$return .= 'Decimal Separator:        ' . rpress_get_option( 'decimal_separator', '.' ) . "\n";
	$return .= 'Thousands Separator:      ' . rpress_get_option( 'thousands_separator', ',' ) . "\n";
	$return .= 'Upgrades Completed:       ' . implode( ',', rpress_get_completed_upgrades() ) . "\n";
	$return .= 'Download Link Expiration: ' . rpress_get_option( 'fooditem_link_expiration' ) . " hour(s)\n";

	$return  = apply_filters( 'rpress_sysinfo_after_rpress_config', $return );

	// RPRESS pages
	$purchase_page = rpress_get_option( 'purchase_page', '' );
	$success_page  = rpress_get_option( 'success_page', '' );
	$failure_page  = rpress_get_option( 'failure_page', '' );

	$return .= "\n" . '-- RPRESS Page Configuration' . "\n\n";
	$return .= 'Checkout:                 ' . ( !empty( $purchase_page ) ? "Valid\n" : "Invalid\n" );
	$return .= 'Checkout Page:            ' . ( !empty( $purchase_page ) ? get_permalink( $purchase_page ) . "\n" : "Unset\n" );
	$return .= 'Success Page:             ' . ( !empty( $success_page ) ? get_permalink( $success_page ) . "\n" : "Unset\n" );
	$return .= 'Failure Page:             ' . ( !empty( $failure_page ) ? get_permalink( $failure_page ) . "\n" : "Unset\n" );
	$return .= 'RestroPress Slug:           ' . ( defined( 'RPRESS_SLUG' ) ? '/' . RPRESS_SLUG . "\n" : "/fooditems\n" );

	$return  = apply_filters( 'rpress_sysinfo_after_rpress_pages', $return );

	// RPRESS gateways
	$return .= "\n" . '-- RPRESS Gateway Configuration' . "\n\n";

	$active_gateways = rpress_get_enabled_payment_gateways();
	if( $active_gateways ) {
		$default_gateway_is_active = rpress_is_gateway_active( rpress_get_default_gateway() );
		if( $default_gateway_is_active ) {
			$default_gateway = rpress_get_default_gateway();
			$default_gateway = $active_gateways[$default_gateway]['admin_label'];
		} else {
			$default_gateway = 'Test Payment';
		}

		$gateways        = array();
		foreach( $active_gateways as $gateway ) {
			$gateways[] = $gateway['admin_label'];
		}

		$return .= 'Enabled Gateways:         ' . implode( ', ', $gateways ) . "\n";
		$return .= 'Default Gateway:          ' . $default_gateway . "\n";
	} else {
		$return .= 'Enabled Gateways:         None' . "\n";
	}

	$return  = apply_filters( 'rpress_sysinfo_after_rpress_gateways', $return );


	// RPRESS Taxes
	$return .= "\n" . '-- RPRESS Tax Configuration' . "\n\n";
	$return .= 'Taxes:                    ' . ( rpress_use_taxes() ? "Enabled\n" : "Disabled\n" );
	$return .= 'Tax Rate:                 ' . rpress_get_tax_rate() * 100 . "\n";
	$return .= 'Display On Checkout:      ' . ( rpress_get_option( 'checkout_include_tax', false ) ? "Displayed\n" : "Not Displayed\n" );
	$return .= 'Prices Include Tax:       ' . ( rpress_prices_include_tax() ? "Yes\n" : "No\n" );

	$rates = rpress_get_tax_rates();
	if( !empty( $rates ) ) {
		$return .= 'Country / State Rates:    ' . "\n";
		foreach( $rates as $rate ) {
			$return .= '                          Country: ' . $rate['country'] . ', State: ' . $rate['state'] . ', Rate: ' . $rate['rate'] . "\n";
		}
	}

	$return  = apply_filters( 'rpress_sysinfo_after_rpress_taxes', $return );

	// RPRESS Templates
	$dir = get_stylesheet_directory() . '/rpress_templates/*';
	if( is_dir( $dir ) && ( count( glob( "$dir/*" ) ) !== 0 ) ) {
		$return .= "\n" . '-- RPRESS Template Overrides' . "\n\n";

		foreach( glob( $dir ) as $file ) {
			$return .= 'Filename:                 ' . basename( $file ) . "\n";
		}

		$return  = apply_filters( 'rpress_sysinfo_after_rpress_templates', $return );
	}

	// Get plugins that have an update
	$updates = get_plugin_updates();

	// Must-use plugins
	// NOTE: MU plugins can't show updates!
	$muplugins = get_mu_plugins();
	if( count( $muplugins ) > 0 ) {
		$return .= "\n" . '-- Must-Use Plugins' . "\n\n";

		foreach( $muplugins as $plugin => $plugin_data ) {
			$return .= $plugin_data['Name'] . ': ' . $plugin_data['Version'] . "\n";
		}

		$return = apply_filters( 'rpress_sysinfo_after_wordpress_mu_plugins', $return );
	}

	// WordPress active plugins
	$return .= "\n" . '-- WordPress Active Plugins' . "\n\n";

	$plugins = get_plugins();
	$active_plugins = get_option( 'active_plugins', array() );

	foreach( $plugins as $plugin_path => $plugin ) {
		if( !in_array( $plugin_path, $active_plugins ) )
			continue;

		$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
		$return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
	}

	$return  = apply_filters( 'rpress_sysinfo_after_wordpress_plugins', $return );

	// WordPress inactive plugins
	$return .= "\n" . '-- WordPress Inactive Plugins' . "\n\n";

	foreach( $plugins as $plugin_path => $plugin ) {
		if( in_array( $plugin_path, $active_plugins ) )
			continue;

		$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
		$return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
	}

	$return  = apply_filters( 'rpress_sysinfo_after_wordpress_plugins_inactive', $return );

	if( is_multisite() ) {
		// WordPress Multisite active plugins
		$return .= "\n" . '-- Network Active Plugins' . "\n\n";

		$plugins = wp_get_active_network_plugins();
		$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

		foreach( $plugins as $plugin_path ) {
			$plugin_base = plugin_basename( $plugin_path );

			if( !array_key_exists( $plugin_base, $active_plugins ) )
				continue;

			$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
			$plugin  = get_plugin_data( $plugin_path );
			$return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
		}

		$return  = apply_filters( 'rpress_sysinfo_after_wordpress_ms_plugins', $return );
	}

	// Server configuration (really just versioning)
	$return .= "\n" . '-- Webserver Configuration' . "\n\n";
	$return .= 'PHP Version:              ' . PHP_VERSION . "\n";
	$return .= 'MySQL Version:            ' . $wpdb->db_version() . "\n";
	$return .= 'Webserver Info:           ' . $_SERVER['SERVER_SOFTWARE'] . "\n";

	$return  = apply_filters( 'rpress_sysinfo_after_webserver_config', $return );

	// PHP configs... now we're getting to the important stuff
	$return .= "\n" . '-- PHP Configuration' . "\n\n";
	$return .= 'Memory Limit:             ' . ini_get( 'memory_limit' ) . "\n";
	$return .= 'Upload Max Size:          ' . ini_get( 'upload_max_filesize' ) . "\n";
	$return .= 'Post Max Size:            ' . ini_get( 'post_max_size' ) . "\n";
	$return .= 'Upload Max Filesize:      ' . ini_get( 'upload_max_filesize' ) . "\n";
	$return .= 'Time Limit:               ' . ini_get( 'max_execution_time' ) . "\n";
	$return .= 'Max Input Vars:           ' . ini_get( 'max_input_vars' ) . "\n";
	$return .= 'Display Errors:           ' . ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . "\n";
	$return .= 'PHP Arg Separator:        ' . rpress_get_php_arg_separator_output() . "\n";

	$return  = apply_filters( 'rpress_sysinfo_after_php_config', $return );

	// PHP extensions and such
	$return .= "\n" . '-- PHP Extensions' . "\n\n";
	$return .= 'cURL:                     ' . ( function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported' ) . "\n";
	$return .= 'fsockopen:                ' . ( function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported' ) . "\n";
	$return .= 'SOAP Client:              ' . ( class_exists( 'SoapClient' ) ? 'Installed' : 'Not Installed' ) . "\n";
	$return .= 'Suhosin:                  ' . ( extension_loaded( 'suhosin' ) ? 'Installed' : 'Not Installed' ) . "\n";

	$return  = apply_filters( 'rpress_sysinfo_after_php_ext', $return );

	// Session stuff
	$return .= "\n" . '-- Session Configuration' . "\n\n";
	$return .= 'RPRESS Use Sessions:         ' . ( defined( 'RPRESS_USE_PHP_SESSIONS' ) && RPRESS_USE_PHP_SESSIONS ? 'Enforced' : ( RPRESS()->session->use_php_sessions() ? 'Enabled' : 'Disabled' ) ) . "\n";
	$return .= 'Session:                  ' . ( isset( $_SESSION ) ? 'Enabled' : 'Disabled' ) . "\n";

	// The rest of this is only relevant is session is enabled
	if( isset( $_SESSION ) ) {
		$return .= 'Session Name:             ' . esc_html( ini_get( 'session.name' ) ) . "\n";
		$return .= 'Cookie Path:              ' . esc_html( ini_get( 'session.cookie_path' ) ) . "\n";
		$return .= 'Save Path:                ' . esc_html( ini_get( 'session.save_path' ) ) . "\n";
		$return .= 'Use Cookies:              ' . ( ini_get( 'session.use_cookies' ) ? 'On' : 'Off' ) . "\n";
		$return .= 'Use Only Cookies:         ' . ( ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off' ) . "\n";
	}

	$return  = apply_filters( 'rpress_sysinfo_after_session_config', $return );

	$return .= "\n" . '### End System Info ###';

	return $return;
}


/**
 * Generates a System Info fooditem file
 *
 * @since       2.0
 * @return      void
 */
function rpress_tools_sysinfo_fooditem() {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	nocache_headers();

	header( 'Content-Type: text/plain' );
	header( 'Content-Disposition: attachment; filename="rpress-system-info.txt"' );

	echo wp_strip_all_tags( $_POST['rpress-sysinfo'] );
	rpress_die();
}
add_action( 'rpress_fooditem_sysinfo', 'rpress_tools_sysinfo_fooditem' );
