<?php
/**
 * Class for Print Receipts
 *
 * @package     RPRESS
 * @subpackage  Logging
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 2.8.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * RPRESS_Print_Receipts Class
 *
 * Class that adds print receipts option to the site.
 *
 */

class RPRESS_Print_Receipts {

  /**
  *
  * Class Constructor
  *
  */
  public function __construct() {

    add_action( 'rpress_after_order_title', array( $this, 'add_meta_box' ), 10, 1 );

    add_action( 'wp_ajax_rp_print_payment_data',  array( $this, 'rp_print_payment_data' ) );

    if ( is_admin() ) {
      
      add_filter( 'rpress_payments_table_columns', array( $this, 'add_print_column' ) );
      add_filter( 'rpress_payments_table_column', array( $this, 'get_print_action'), 10, 3);
    }
  }

  /**
   * Get printer paper size
   *
   * @since 1.1
   * @return array
   */
  public static function paper_sizes() {
    
    $paper_sizes = array(
      ''          => __( 'Select Paper Size', 'restropress' ),
      '56.9mm' => __( '57mm x 38mm (Mobile/Small CC Terminals)', 'restropress' ),
      '57.0mm' => __( '57mm x 40mm (Mobile/Small CC Terminals)', 'restropress' ),
      '57.1mm' => __( '57mm x 50mm (Mobile/Small CC Terminals)', 'restropress' ),
      '79.9mm' => __( '80mm x 60mm (Thermal Receipt Printers)', 'restropress' ),
      '80.0mm' => __( '80mm x 70mm (Thermal Receipt Printers)', 'restropress' ),
      '80.1mm' => __( '80mm x 80mm (Thermal Receipt Printers)', 'restropress' ),
    );

    return apply_filters( 'rpress_receipt_paper_sizes', $paper_sizes );
  }

  /**
   * Get printer settings from the database
   *
   * @return mixed
   */
  protected static function get_settings() {
    
    $print_settings = get_option( 'rpress_settings', array() );
    return apply_filters( 'rpress_print_settings_fields', $print_settings );
  }

  /**
   * Add print text in the columns
   *
   * @param array $columns columns
   *
   * @return array
   */
  public function add_print_column( $columns ) {
    
    $new_columns = ( is_array( $columns ) ) ? $columns : array();

    $get_settings = self::get_settings();

    if( isset( $get_settings['enable_printing'] ) )
      $new_columns['print'] = __( 'Print', 'restropress' );
    
    return $new_columns;
  }

  /**
   * Check whether print action should be available with selected order
   *
   * @since 1.0.0
   * @param int $payment_id
   *
   * @return bool
   */
  public function check_print_action_available( $payment_id ) {
    
    $current_status = rpress_get_order_status( $payment_id );
    $get_settings = self::get_settings();

    $print_status = isset( $get_settings['order_print_status'] ) ? $get_settings['order_print_status'] : array();
    
    if ( isset( $get_settings['enable_printing'] ) && array_key_exists( $current_status, $print_status ) ) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Add print actions with this column
   *
   * @param string $value output string with matching column
   * @param int $payment_id payment id to be checked
   * @param string $column_name column name
   *
   * @return array
   */
  public function get_print_action( $value, $payment_id, $column_name ) {
    if ( 'print' === $column_name ) {
      if ( $this->check_print_action_available( $payment_id ) ) {
        $value = '<div style="display: none;" class="print-display-area" id="print-display-area-'.$payment_id.'"></div><button type="button" data-payment-id="'.$payment_id.'" class="button rp_print_now">'.apply_filters( 'rpress_print_text', __( '<span class="dashicons dashicons-media-document"></span>', 'restropress' ) ).'</button>';
      }
    }
    return $value;
  }

  /**
   * Add print actions on payment details page
   *
   * @param int $payment_id 
   */
  public function add_meta_box( $payment_id ) {

    if ( $this->check_print_action_available( $payment_id ) ) :

      echo '<div style="display: none;" class="print-display-area" id="print-display-area-'.$payment_id.'"></div><button type="button" data-payment-id="'.$payment_id.'" class="button rp_print_now">'.apply_filters( 'rpress_edit_print_text', __( 'Print', 'restropress' ) ).'</button>';
      
    endif;
  }

  /**
   * Generating complete print screen HTML
   * All the content will be later replaced with shortcodes
   * availble in the template file
   *
   */
  public function rp_print_payment_data() {
    
    if( ! current_user_can( 'edit_shop_payments', $_GET['payment_id'] ) ) {
        wp_die( esc_html__( 'You do not have permission to update this order', 'restropress' ), esc_html__( 'Error', 'restropress' ), array( 'response' => 403 ) );
    }
    
    $payment_id = isset( $_GET['payment_id'] ) ? absint( $_GET['payment_id'] ) : '';
    
    if( empty( $payment_id ) ) return;
    
    $payment    = new RPRESS_Payment( $payment_id );

    $payment_meta   = $payment->get_meta();
    $address_meta = get_post_meta( $payment_id,'_rpress_delivery_address',true );
    
    ob_start();
    rpress_get_template_part( 'receipt/print' ,'receipt' );
    $receipt = ob_get_clean();

    $print_settings = self::get_settings();

    // Paper Size
    $paper_size = isset( $print_settings['paper_size'] ) ?$print_settings['paper_size']:'80mm';

    // Selected Font
    $printing_font = isset( $print_settings['order_printing_font'] ) ?$print_settings['order_printing_font']:'Arial, Helvetica, sans-serif';

    // Store Logo / Name
    $image_path = isset( $print_settings['store_logo'] ) ? $print_settings['store_logo'] : '';
    if( $image_path != '' ) {
      $image_type = pathinfo( $image_path, PATHINFO_EXTENSION );
      $image_data = file_get_contents( $image_path );
      $base64_img = 'data:image/'.$image_type.';base64,'.base64_encode( $image_data );

      $store_logo = '<img style="height: 75px; width:auto; margin: 0px auto 10px; display:block;" src="'.$base64_img.'">';
    } else {
      $store_logo = get_bloginfo( 'name' );
    }

    // Customer Info (Email, Phone, Email)
    $customer_name = $payment_meta['user_info']['first_name'] . ' ' .  $payment_meta['user_info']['last_name'];
    $customer_mail = $payment_meta['email'];
    $customer_phone = ! empty( $payment_meta['phone'] ) ? $payment_meta['phone']:'';

    // Service Type & Time
    $timezone = get_option( 'timezone_string' );
    date_default_timezone_set( $timezone );

    $service_type = $payment->get_meta( '_rpress_delivery_type' );
    $service_time = $payment->get_meta( '_rpress_delivery_time' );
    $service_date = $payment->get_meta( '_rpress_delivery_date' );

    $service_type = ! empty( $service_type ) ? ucfirst($service_type) : '';
    $service_time = ! empty( $service_time ) ? $service_time : '';
    $service_date = ! empty( $service_date ) && $service_date != 'undefined' ? date_i18n( get_option( 'date_format' ), strtotime( $service_date ) ) : '';

    // Payment Type
    $gateway          = $payment->gateway;
    $get_payment_type = rpress_get_gateway_admin_label( $gateway );
    $payment_type     = ! empty( $get_payment_type )?'<p><b>'.apply_filters( 'rpress_receipt_payment_type_text', __( 'Payment Type: ', 'restropress' ) ).'</b> '.$get_payment_type.'</p>':'';

    // Address to be shown 
    $address_string = ''; 
    if( ! empty( $service_type ) && $service_type == 'Delivery' ) :

      $address_string.= '<p>'.apply_filters( 'rpress_receipt_payment_address_text', __( 'Address: ', 'restropress' ) ).'<b>';

      $address_array = array();

      if( ! empty( $address_meta['address'] ) ) {
        array_push( $address_array, $address_meta['address'] );
      }

      if( ! empty( $address_meta['flat'] ) ) {
        array_push( $address_array, $address_meta['flat'] );
      }

      if( ! empty( $address_meta['city'] ) ) {
        array_push( $address_array, $address_meta['city'] );
      }

      if( ! empty( $address_meta['postcode'] ) ) {
        array_push( $address_array, $address_meta['postcode'] );
      }

      $address_from_array = implode( ', ', $address_array );

      $address_string.= $address_from_array;
      $address_string.= '</b></p>';

    endif;

    // Items List
    $receipt_content = $this->render_payment_order_details();

    // Order Note
    $payment_note = get_post_meta( $payment_id, '_rpress_order_note', true );
    $payment_note = ! empty( $payment_note )?'<p> '.apply_filters( 'rpress_receipt_payment_note_text', __( 'Instructions: ', 'restropress' ) ).'<b> '.$payment_note.'</b></p>':'';

    // Footer Notes
    $footer_note = isset( $print_settings['footer_area_content'] ) ?$print_settings['footer_area_content']:'';
    $complm_note = isset( $print_settings['complementary_close'] ) ?$print_settings['complementary_close']:'';

    $search = array( 
      '{rpp_choosen_font}',
      '{rpp_paper_size}',
      '{rpp_store_logo}', 
      '{rpp_order_id}',
      '{rpp_customer_name}',
      '{rpp_customer_phone}',
      '{rpp_customer_email}',
      '{rpp_order_type}',
      '{rpp_order_time}',
      '{rpp_order_date}',
      '{rpp_order_location}',
      '{rpp_order_payment_type}',
      '{rpp_order_note}',
      '{rpp_order_items}',
      '{footer_note}',
      '{footer_complementary}',
    );

    $replace = array(
      $printing_font,
      $paper_size,
      $store_logo,
      $payment_id,
      $customer_name,
      $customer_phone,
      $customer_mail,
      $service_type,
      $service_time,
      $service_date,
      $address_string,
      $payment_type,
      $payment_note,
      $receipt_content,
      $footer_note,
      $complm_note
    );

    $content = str_replace( $search, $replace, $receipt );

    // Output the content
    ob_start();
    echo $content;
    wp_die();
  }

  /**
   * Get the HTML part of the receipt to print
   *
   * @since 1.0.0
   * @param int $payment_id Payment ID 
   *
   */
  public function render_payment_order_details() {

    ob_start();
    rpress_get_template_part( 'receipt/print', 'receipt-content' );
    $payment_receipt = ob_get_clean();
    return $payment_receipt;
  }

}
$print_receipts = new RPRESS_Print_Receipts();