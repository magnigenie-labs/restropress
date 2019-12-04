<?php
/**
 * Email Template
 *
 * @package     RPRESS
 * @subpackage  Emails
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Gets all the email templates that have been registerd. The list is extendable
 * and more templates can be added.
 *
 * As of 2.0, this is simply a wrapper to RPRESS_Email_Templates->get_templates()
 *
 * @since 1.0
 * @return array $templates All the registered email templates
 */
function rpress_get_email_templates() {
	$templates = new RPRESS_Emails;
	return $templates->get_templates();
}

/**
 * Email Template Tags
 *
 * @since 1.0
 *
 * @param string $message Message with the template tags
 * @param array $payment_data Payment Data
 * @param int $payment_id Payment ID
 * @param bool $admin_notice Whether or not this is a notification email
 *
 * @return string $message Fully formatted message
 */
function rpress_email_template_tags( $message, $payment_data, $payment_id, $admin_notice = false ) {
	return rpress_do_email_tags( $message, $payment_id );
}

/**
 * Email Preview Template Tags
 *
 * @since 1.0
 * @param string $message Email message with template tags
 * @return string $message Fully formatted message
 */
function rpress_email_preview_template_tags( $message ) {
	$fooditem_list = '<ul>';
	$fooditem_list .= '<li>' . __( 'Sample Product Title', 'restropress' ) . '<br />';
	$fooditem_list .= '<div>';
	$fooditem_list .= '<a href="#">' . __( 'Sample Food Item Name', 'restropress' ) . '</a> - <small>' . __( 'Optional notes about this fooditem.', 'restropress' ) . '</small>';
	$fooditem_list .= '</div>';
	$fooditem_list .= '</li>';
	$fooditem_list .= '</ul>';

	$file_urls = esc_html( trailingslashit( get_site_url() ) . 'test.zip?test=key&key=123' );

	$price = rpress_currency_filter( rpress_format_amount( 10.50 ) );

	$gateway = rpress_get_gateway_admin_label( rpress_get_default_gateway() );

	$receipt_id = strtolower( md5( uniqid() ) );

	$notes = __( 'These are some sample notes added to a product.', 'restropress' );

	$tax = rpress_currency_filter( rpress_format_amount( 1.00 ) );

	$sub_total = rpress_currency_filter( rpress_format_amount( 9.50 ) );

	$payment_id = rand(1, 100);

	$user = wp_get_current_user();

	$message = str_replace( '{fooditem_list}', $fooditem_list, $message );
	$message = str_replace( '{name}', $user->display_name, $message );
	$message = str_replace( '{fullname}', $user->display_name, $message );
 	$message = str_replace( '{username}', $user->user_login, $message );
	$message = str_replace( '{date}', date( get_option( 'date_format' ), current_time( 'timestamp' ) ), $message );
	$message = str_replace( '{subtotal}', $sub_total, $message );
	$message = str_replace( '{tax}', $tax, $message );
	$message = str_replace( '{price}', $price, $message );
	$message = str_replace( '{receipt_id}', $receipt_id, $message );
	$message = str_replace( '{payment_method}', $gateway, $message );
	$message = str_replace( '{sitename}', get_bloginfo( 'name' ), $message );
	$message = str_replace( '{product_notes}', $notes, $message );
	$message = str_replace( '{payment_id}', $payment_id, $message );
	$message = str_replace( '{receipt_link}', rpress_email_tag_receipt_link( $payment_id ), $message );

	$message = apply_filters( 'rpress_email_preview_template_tags', $message );

	return apply_filters( 'rpress_email_template_wpautop', true ) ? wpautop( $message ) : $message;
}

/**
 * Email Template Preview
 *
 * @access private
 * @since 1.0
 */
function rpress_email_template_preview() {
	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	ob_start();
	?>
	<a href="<?php echo esc_url( add_query_arg( array( 'rpress_action' => 'preview_email' ), home_url() ) ); ?>" class="button-secondary" target="_blank"><?php _e( 'Preview Order Receipt', 'restropress' ); ?></a>
	<a href="<?php echo wp_nonce_url( add_query_arg( array( 'rpress_action' => 'send_test_email' ) ), 'rpress-test-email' ); ?>" class="button-secondary"><?php _e( 'Send Test Email', 'restropress' ); ?></a>
	<?php
	echo ob_get_clean();
}
add_action( 'rpress_purchase_receipt_email_settings', 'rpress_email_template_preview' );

/**
 * Displays the email preview
 *
 * @since  1.0.0
 * @return void
 */
function rpress_display_email_template_preview() {

	if( empty( $_GET['rpress_action'] ) ) {
		return;
	}

	if( 'preview_email' !== $_GET['rpress_action'] ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}


	RPRESS()->emails->heading = rpress_email_preview_template_tags( rpress_get_option( 'purchase_heading', __( 'Purchase Receipt', 'restropress' ) ) );

	echo RPRESS()->emails->build_email( rpress_email_preview_template_tags( rpress_get_email_body_content( 0, array() ) ) );

	exit;

}
add_action( 'template_redirect', 'rpress_display_email_template_preview' );

/**
 * Email Template Body
 *
 * @since 1.0
 * @param int $payment_id Payment ID
 * @param array $payment_data Payment Data
 * @return string $email_body Body of the email
 */
function rpress_get_email_body_content( $payment_id = 0, $payment_data = array() ) {
	$default_email_body = __( "Dear", "restro-press" ) . " {name},\n\n";
	$default_email_body .= __( "Thank you for your order. Here are the list of items that you have ordered", "restro-press" ) . "\n\n";
	$default_email_body .= "{fooditem_list}\n\n";
	$default_email_body .= "{sitename}";

	$email = rpress_get_option( 'purchase_receipt', false );
	$email = $email ? stripslashes( $email ) : $default_email_body;

	$email_body = apply_filters( 'rpress_email_template_wpautop', true ) ? wpautop( $email ) : $email;

	$email_body = apply_filters( 'rpress_purchase_receipt_' . RPRESS()->emails->get_template(), $email_body, $payment_id, $payment_data );

	return apply_filters( 'rpress_purchase_receipt', $email_body, $payment_id, $payment_data );
}

/**
 * Order Notification Template Body
 *
 * @since  1.0.0
 * @author RestroPress
 * @param int $payment_id Payment ID
 * @param array $payment_data Payment Data
 * @return string $email_body Body of the email
 */
function rpress_get_order_notification_body_content( $payment_id = 0, $payment_data = array() ) {
	$payment = rpress_get_payment( $payment_id );

	if( $payment->user_id > 0 ) {
		$user_data = get_userdata( $payment->user_id );
		$name = $user_data->display_name;
	} elseif( ! empty( $payment->first_name ) && ! empty( $payment->last_name ) ) {
		$name = $payment->first_name . ' ' . $payment->last_name;
	} else {
		$name = $payment->email;
	}

	ob_start();

	set_query_var( 'rpress_email_fooditems', $payment->fooditems );

	rpress_get_template_part( 'email', 'foodlist' );

	$fooditem_list = ob_get_clean();

	$gateway = rpress_get_gateway_admin_label( $payment->gateway );

	$default_email_body = __( 'Hello', 'restropress' ) . "\n\n" . __( 'A new order has been received', 'restropress' ) . ".\n\n";
	$default_email_body .= sprintf( __( '%s ordered:', 'restropress' ), rpress_get_label_plural() ) . "\n\n";
	$default_email_body .= $fooditem_list . "\n\n";
	$default_email_body .= __( 'Ordered by: ', 'restropress' ) . " " . html_entity_decode( $name, ENT_COMPAT, 'UTF-8' ) . "\n";
	$default_email_body .= __( 'Amount: ', 'restropress' ) . " " . html_entity_decode( rpress_currency_filter( rpress_format_amount( $payment->total ) ), ENT_COMPAT, 'UTF-8' ) . "\n";
	$default_email_body .= __( 'Payment Method: ', 'restropress' ) . " " . $gateway . "\n\n";
	$default_email_body .= __( 'Thank you', 'restropress' );

	$message = rpress_get_option( 'order_notification', false );
	$message   = $message ? stripslashes( $message ) : $default_email_body;

	$email_body = rpress_do_email_tags( $message, $payment_id );

	$email_body = apply_filters( 'rpress_email_template_wpautop', true ) ? wpautop( $email_body ) : $email_body;

	return apply_filters( 'rpress_sale_notification', $email_body, $payment_id, $payment_data );
}

/**
 * Render Receipt in the Browser
 *
 * A link is added to the Purchase Receipt to view the email in the browser and
 * this function renders the Purchase Receipt in the browser. It overrides the
 * Purchase Receipt template and provides its only styling.
 *
 * @since 1.0
 * @author RestroPress
 */
function rpress_render_receipt_in_browser() {
	if ( ! isset( $_GET['payment_key'] ) )
		wp_die( __( 'Missing order key.', 'restropress' ), __( 'Error', 'restropress' ) );

	$key = urlencode( $_GET['payment_key'] );

	ob_start();
	//Disallows caching of the page
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache"); // HTTP/1.0
	header("Expires: Sat, 23 Oct 1977 05:00:00 PST"); // Date in the past
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php _e( 'Receipt', 'restropress' ); ?></title>
		<meta charset="utf-8" />
		<meta name="robots" content="noindex, nofollow" />
		<?php wp_head(); ?>
	</head>
<body class="<?php echo apply_filters('rpress_receipt_page_body_class', 'rpress_receipt_page' ); ?>">
	<div id="rpress_receipt_wrapper">
		<?php do_action( 'rpress_render_receipt_in_browser_before' ); ?>
		<?php echo do_shortcode('[rpress_receipt payment_key='. $key .']'); ?>
		<?php do_action( 'rpress_render_receipt_in_browser_after' ); ?>
	</div>
<?php wp_footer(); ?>
</body>
</html>
<?php
	echo ob_get_clean();
	die();
}
add_action( 'rpress_view_receipt', 'rpress_render_receipt_in_browser' );
