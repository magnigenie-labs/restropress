<?php
/**
 * Load assets
 *
 * @package RestroPress/Admin
 * @since 2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'RP_Admin_Assets', false ) ) :

  /**
   * RP_Admin_Assets Class.
   */
  class RP_Admin_Assets {

    /**
     * Hook in tabs.
     */
    public function __construct() {
      add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
      add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
      add_action( 'admin_enqueue_scripts', array( $this, 'register_styles' ), 100 );
      add_action( 'admin_head', array( $this, 'admin_icons' ) );
      add_action( 'wp_ajax_selected_filter', array( $this, 'selected_filter' ) );
      add_action( 'wp_ajax_nopriv_selected_filter',array( $this, 'selected_filter' ) );
      add_action( 'wp_ajax_rpress_do_ajax_export', array($this, 'rpress_do_ajax_export' ) );
      add_action( 'wp_ajax_order_graph_filter', array( $this, 'order_graph_filter' ) );
      add_action( 'wp_ajax_nopriv_order_graph_filter', array( $this, 'order_graph_filter') );
      add_action( 'wp_ajax_revenue_graph_filter', array( $this, 'revenue_graph_filter' ) );
      add_action( 'wp_ajax_nopriv_revenue_graph_filter',array( $this, 'revenue_graph_filter' ) );
      add_action( 'wp_ajax_customers_data_filter', array( $this, 'customers_data_filter') );
      add_action( 'wp_ajax_nopriv_customers_data_filter', array( $this, 'customers_data_filter') );
    }

    /**
     * Enqueue styles.
     */
    public function admin_styles() {

      global $wp_scripts;

      $screen    = get_current_screen();
      $screen_id = $screen ? $screen->id : '';
      $suffix       = '';

      // Register admin styles.
      wp_register_style( 'rpress_admin_icon_styles', RP_PLUGIN_URL . '/assets/css/admin-icons.css', array(), RP_VERSION );
      wp_register_style( 'rpress_admin_styles', RP_PLUGIN_URL . 'assets/css/admin.css', array('select2'), RP_VERSION );
      wp_register_style( 'select2', RP_PLUGIN_URL . 'assets/css/select2.min.css', array(), RP_VERSION );
      wp_register_style( 'toast', RP_PLUGIN_URL . '/assets/css/jquery.toast.css', array(), RP_VERSION );
      wp_register_style( 'timepicker', RP_PLUGIN_URL . 'assets/css/jquery.timepicker.css', array(), RP_VERSION );
      wp_register_style( 'jquery-chosen', RP_PLUGIN_URL .'assets/css/chosen.min.css', array(), RP_VERSION );
      wp_register_style( 'backbone-modal', RP_PLUGIN_URL .'assets/css/rpress-backbone-modal.css', array(), RP_VERSION );

      $ui_style = ( 'classic' == get_user_option( 'admin_color' ) ) ? 'classic' : 'fresh';
      wp_register_style( 'jquery-ui-css', RP_PLUGIN_URL . 'assets/css/jquery-ui-'. $ui_style . '.min.css' );

      wp_enqueue_style( 'jquery-ui-css' );
      wp_enqueue_style( 'timepicker' );
      wp_enqueue_style( 'rpress_admin_styles' );
      wp_enqueue_style( 'jquery-chosen' );
      wp_enqueue_style( 'wp-color-picker' );
      wp_enqueue_style( 'toast' );
      wp_enqueue_style( 'thickbox' );
      wp_enqueue_style( 'backbone-modal' );

      // Sitewide Admin Icons.
      wp_enqueue_style( 'rpress_admin_icon_styles' );

    }

    /**
     * Enqueue scripts.
     */
    public function admin_scripts() {

      global $wp_query, $post;

      $screen       = get_current_screen();
      $screen_id    = $screen ? $screen->id : '';
      $rp_screen_id = sanitize_title( __( 'RestroPress', 'restropress' ) );
      $suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
      $admin_deps   = array( 'jquery', 'jquery-tata-toast', 'timepicker', 'jquery-form', 'inline-edit-post', 'jquery-ui-tooltip' );

      wp_register_script( 'jquery-tiptip', RP_PLUGIN_URL . 'assets/js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ), RP_VERSION, true );
      wp_register_script( 'select2', RP_PLUGIN_URL . 'assets/js/select2/select2' . $suffix . '.js', array( 'jquery' ), RP_VERSION, true );
      wp_register_script( 'jquery-blockui', RP_PLUGIN_URL . 'assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), RP_VERSION, true );
      wp_register_script( 'rp-backbone-modal', RP_PLUGIN_URL . 'assets/js/admin/backbone-modal.js', array( 'underscore', 'backbone', 'wp-util' ), RP_VERSION );
      wp_register_script( 'timepicker', RP_PLUGIN_URL . 'assets/js/timepicker/jquery.timepicker.js', array( 'jquery' ), RP_VERSION );
      wp_register_script( 'rp-admin-meta-boxes', RP_PLUGIN_URL . 'assets/js/admin/meta-boxes.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'select2', 'jquery-tiptip', 'jquery-blockui' ), RP_VERSION );
      wp_register_script( 'rpress-orders', RP_PLUGIN_URL . 'assets/js/admin/rp-orders.js', array( 'jquery', 'rp-backbone-modal' ), RP_VERSION, true );
      wp_register_script( 'jquery-tata-toast', RP_PLUGIN_URL . 'assets/js/rp-tata.js', array( 'jquery' ), RP_VERSION );
      wp_register_script( 'rp-admin', RP_PLUGIN_URL . 'assets/js/admin/rp-admin.js', $admin_deps, RP_VERSION );
      wp_register_script( 'jquery-chosen', RP_PLUGIN_URL . 'assets/js/jquery-chosen/chosen.jquery' . $suffix . '.js', array( 'jquery' ), RP_VERSION );
      wp_register_script( 'admin-dashboard', RP_PLUGIN_URL . 'assets/js/admin/admin-dashboard.js', array( 'jquery','rp-backbone-modal' ), RP_VERSION, true );
      wp_register_script('moment-js', 'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js', array('jquery'), null, true);
      wp_register_script('daterangepicker-js', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', array('jquery', 'moment-js'), null, true);
      wp_register_style('daterangepicker-css', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css');
      wp_register_script( 'admin-dashboard-chart', 'https://cdn.canvasjs.com/canvasjs.min.js', array( 'jquery' ), RP_VERSION, true );


      wp_enqueue_script( 'jquery-chosen' );
      wp_enqueue_script( 'jquery-form' );
      wp_enqueue_script( 'jquery-ui-datepicker' );
      wp_enqueue_script( 'jquery-ui-dialog' );
      wp_enqueue_script( 'jquery-ui-tooltip' );
      wp_enqueue_script( 'select2' );
      wp_enqueue_script( 'media-upload' );
      wp_enqueue_script( 'thickbox' );
      wp_enqueue_script('moment-js');
      wp_enqueue_script('daterangepicker-js');
      wp_enqueue_style('daterangepicker-css');
      wp_enqueue_script( 'admin-dashboard' );
      wp_enqueue_script( 'admin-dashboard-chart' );
       

      $is_custom_cordinates_enabled = !empty( rpress_get_option( 'use_custom_latlng' ) ) ? 'yes' : 'no';
      $admin_params = array(
        'ajaxurl'                     => rpress_get_ajax_url(),
        'please_wait'                 => esc_html( 'Please Waitsss', 'restropress' ),
        'success'                     => esc_html( 'Success', 'restropress' ),
        'error'                       => esc_html( 'Error', 'restropress' ),
        'information'                 => esc_html( 'Information', 'restropress' ),
        'license_success'             => esc_html( 'Congrats, your license successfully activated!', 'restropress' ),
        'license_error'               => esc_html( 'Invalid License Key', 'restropress' ),
        'license_activate'            => esc_html( 'Activate License', 'restropress' ),
        'license_deactivated'         => esc_html( 'Your license has been deactivated', 'restropress' ),
        'deactivate_license'          => esc_html( 'Deactivate', 'restropress' ),
        'empty_license'               => esc_html( 'Please enter valid license key', 'restropress' ),
        'update_order_nonce'          => wp_create_nonce( 'update-order' ),
        'use_custom_cordinates'       => $is_custom_cordinates_enabled,
        'post_id'                     => isset( $post->ID ) ? $post->ID : null,
        'rpress_version'              => RP_VERSION,
        'add_new_fooditem'            => __( 'Add New Food Item', 'restropress' ),
        'use_this_file'               => __( 'Use This File', 'restropress' ),
        'quick_edit_warning'          => __( 'Sorry, not available for variable priced products.', 'restropress' ),
        'delete_payment'              => __( 'Are you sure you wish to delete this payment?', 'restropress' ),
        'delete_payment_note'         => __( 'Are you sure you wish to delete this note?', 'restropress' ),
        'delete_tax_rate'             => __( 'Are you sure you wish to delete this tax rate?', 'restropress' ),
        'resend_receipt'              => __( 'Are you sure you wish to resend the purchase receipt?', 'restropress' ),
        'disconnect_customer'         => __( 'Are you sure you wish to disconnect the WordPress user from this customer record?', 'restropress' ),
        'copy_fooditem_link_text'     => __( 'Copy these links to your clipboard and give them to your customer', 'restropress' ),
        'delete_payment_fooditem'     => sprintf( __( 'Are you sure you wish to delete this %s?', 'restropress' ), rp_get_label_singular() ), /* translators: %s: singular payment */
        'one_price_min'               => __( 'You must have at least one price', 'restropress' ),
        'one_field_min'               => __( 'You must have at least one field', 'restropress' ),
        'one_fooditem_min'            => __( 'Payments must contain at least one item', 'restropress' ),
        'one_option'                  => sprintf( __( 'Choose a %s', 'restropress' ), rp_get_label_singular() ), /* translators: %s: singular label */
        'one_or_more_option'          => sprintf( /* translators: %s: singular label */ __( 'Choose one or more %s', 'restropress' ), rp_get_label_plural() ),
        'numeric_item_price'          => __( 'Item price must be numeric', 'restropress' ),
        'numeric_item_tax'            => __( 'Item tax must be numeric', 'restropress' ),
        'numeric_quantity'            => __( 'Quantity must be numeric', 'restropress' ),
        'currency'                    => rpress_get_currency(),
        'currency_sign'               => rpress_currency_filter( '' ),
        'currency_pos'                => rpress_get_option( 'currency_position', 'before' ),
        'currency_decimals'           => rpress_currency_decimal_filter(),
        'decimal_separator'           => rpress_get_option( 'decimal_separator', '.' ),
        'thousands_separator'         => rpress_get_option( 'thousands_separator', ',' ),
        'new_media_ui'                => apply_filters( 'rpress_use_35_media_ui', 1 ),
        'remove_text'                 => __( 'Remove', 'restropress' ),
        'type_to_search'              => __( 'Type to search', 'restropress' ),
        'quantities_enabled'          => rpress_item_quantities_enabled(),
        'batch_export_no_class'       => __( 'You must choose a method.', 'restropress' ),
        'batch_export_no_reqs'        => __( 'Required fields not completed.', 'restropress' ),
        'reset_stats_warn'            => __( 'Are you sure you want to reset your store? This process is <strong><em>not reversible</em></strong>. Please be sure you have a recent backup.', 'restropress' ),
        'unsupported_browser'         => __( 'We are sorry but your browser is not compatible with this kind of file upload. Please upgrade your browser.', 'restropress' ),
        'show_advanced_settings'      => __( 'Show advanced settings', 'restropress' ),
        'hide_advanced_settings'      => __( 'Hide advanced settings', 'restropress' ),
        'is_admin'                    => is_admin(),
        'notification_duration'       => rpress_get_option( 'notification_duration' ) ,
        'enable_order_notification'   => rpress_get_option( 'enable_order_notification' ),
        'loopsound'                   => rpress_get_option( 'notification_sound_loop' ),
        'load_admin_addon_nonce'      => wp_create_nonce( 'load-admin-addon' ),
        'preview_nonce' => wp_create_nonce( 'rpress-preview-order' ),
        'order_nonce'   => wp_create_nonce( 'rpress-order' ),
      );

      wp_localize_script( 'rp-admin', 'rpress_vars',
        $admin_params
      );
      wp_register_script( 'rpress-admin-scripts-compatibility', RP_PLUGIN_URL . '/assets/js/admin/admin-backwards-compatibility' . $suffix . '.js', array( 'jquery', 'rp-admin' ), RP_VERSION );
      wp_localize_script( 'rpress-admin-scripts-compatibility', 'rpress_backcompat_vars', array(
          'purchase_limit_settings'     => __( 'Purchase Limit Settings', 'restropress' ),
          'simple_shipping_settings'    => __( 'Simple Shipping Settings', 'restropress' ),
          'software_licensing_settings' => __( 'Software Licensing Settings', 'restropress' ),
          'recurring_payments_settings' => __( 'Recurring Payments Settings', 'restropress' ),
      ) );

      wp_enqueue_script( 'wp-color-picker' );

      //call for media manager
      wp_enqueue_media();

      wp_register_script( 'jquery-flot', RP_PLUGIN_URL . '/assets/js/jquery-flot/jquery.flot' . $suffix . '.js' );
      wp_enqueue_script( 'jquery-flot' );

      // Meta boxes.
      if ( in_array( $screen_id, array( 'fooditem', 'edit-fooditem' ) ) ){

        wp_register_script( 'rp-admin-fooditem-meta-boxes', RP_PLUGIN_URL . 'assets/js/admin/meta-boxes-fooditem.js', array( 'rp-admin-meta-boxes' ), RP_VERSION );
        wp_enqueue_script( 'rp-admin-fooditem-meta-boxes' );

        $params = array(
          'post_id'               => isset( $post->ID ) ? $post->ID : '',
          'ajax_url'              => admin_url( 'admin-ajax.php' ),
          'add_price_nonce'       => wp_create_nonce( 'add-price' ),
          'add_category_nonce'    => wp_create_nonce( 'add-category' ),
          'add_addon_nonce'       => wp_create_nonce( 'add-addon' ),
          'load_addon_nonce'      => wp_create_nonce( 'load-addon' ),
          'delete_pricing'        => esc_js( __( 'Are you sure you want to remove this?', 'restropress' ) ),
          'delete_new_category'   => esc_js( __( 'Are you sure to delete this category?', 'restropress' ) ),
          'select_addon_category' => esc_js( __( 'Please select addon category first.', 'restropress' ) ),
          'addon_category_already_selected' => esc_js( __( 'Addon category already selected.', 'restropress' ) ),
        );
        wp_localize_script( 'rp-admin-fooditem-meta-boxes', 'fooditem_meta_boxes', $params );
      }

      if ( $screen_id == 'restropress_page_rpress-payment-history' || $screen_id == 'restropress_page_rpress-dashboard' ) {

        wp_enqueue_script( 'rpress-orders' );

        wp_localize_script(
          'rpress-orders',
          'rp_orders_params',
          array(
            'ajax_url'      => admin_url( 'admin-ajax.php' ),
            'preview_nonce' => wp_create_nonce( 'rpress-preview-order' ),
            'order_nonce'   => wp_create_nonce( 'rpress-order' ),
          )
        );
        
      }
      
      wp_enqueue_script( 'rp-admin' );

    }
    /**
    * RestroPress Admin dashboard  revenue graph filter
    *
    *  return revenue data according filter .
    *
    * @since 1.0
    * @return void
    */
    public function revenue_graph_filter(){
      $filter_type = isset( $_POST['select_filter'] ) ? $_POST['select_filter'] : '';
      $SalesByDate = [];
      if( $filter_type == 'yearly') {
        $SalesByDate = $this->get_revenue_report( $filter_type );
      }
      elseif( $filter_type === 'monthly') {
         $SalesByDate = $this->get_revenue_report( $filter_type );
      }
      elseif( $filter_type == 'weekly') {

        $SalesByDate = $this->get_revenue_report( $filter_type );
      }

      wp_send_json( $SalesByDate ); 

    }
    
    public function get_revenue_report( $filter_type ) {

      $SalesByDate          = [];
      $key                  = "";
      $currentMonth         ='';
      $currentYear          = '';
      $first_day_for_filter = '';
      $last_day_for_filter  = '';

      if( $filter_type == 'monthly' ) {
        $key                  = 'd';
        $currentMonth         = date('m');
        $currentYear          = date('Y');
        $first_day_for_filter = date( 'Y-m-01', strtotime( "$currentYear-$currentMonth-01" ) );
        $last_day_for_filter  = date( 'Y-m-t', strtotime( "$currentYear-$currentMonth-01" ) );
      }
      elseif( $filter_type == 'weekly'  ) {
        $key                  = 'd';
        $first_day_for_filter      = date( 'Y-m-d', strtotime( 'this week monday' ) );
        $last_day_for_filter        = date( 'Y-m-d', strtotime( 'this week sunday' ) );

      }
      elseif( $filter_type == 'yearly' ) {
        $key                    = 'm';
        $currentMonth           = date( 'm' );
        $currentYear            = date('Y');
        $first_day_for_filter   = date( 'Y-01-01', strtotime( "$currentYear-01-01" ) );
        $last_day_for_filter    = date( 'Y-12-31', strtotime( "$currentYear-12-31" ) );

      }
     
      
      $args = array(
        'post_type'      => 'rpress_payment',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'date_query'     => array(
          'after'     => $first_day_for_filter,
          'before'    => $last_day_for_filter,
          'inclusive' => true,
        ),
      );
      
      $query = new WP_Query( $args );
      if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
          $query->the_post(); 
          $post_id = get_the_ID();
          $payment = new RPRESS_Payment( $post_id ); 
          $amount  = $payment->total;  
          $deliveryDate = get_post_meta( $post_id, '_rpress_delivery_date', true);
          $day          = date( $key, strtotime( $deliveryDate ) );
          if (  !isset( $SalesByDate[ $day ] ) ) $SalesByDate[ $day ] = 0;
          $SalesByDate[$day] += $amount;
        }        
        wp_reset_postdata();
      }
      return  $SalesByDate;    
  
    }

    /**
     * Register Required admin style
     * Taken from scripts.php from RP 2.5
     *
     * @since 1.0
     * @global $post
     * @param string $hook Page hook
     * @return void
     */
    public function register_styles() {

      global $post;

      $js_dir  = RP_PLUGIN_URL . 'assets/js/';
      $css_dir = RP_PLUGIN_URL . 'assets/css/';

      // Use minified libraries if SCRIPT_DEBUG is turned off
      $suffix = '';

      wp_register_style( 'rpress-admin', $css_dir . 'rpress-admin' . $suffix . '.css', array(), RP_VERSION);
      wp_enqueue_style('rpress-admin');

      if ( isset( $_GET['section'] ) && $_GET['section'] == 'order_notifications' ) {
        wp_add_inline_style( 'rpress-admin', 'input#submit { visibility: hidden; }' );
      }

      if ( isset( $_GET['section'] )
        && $_GET['section'] == 'order_notifications'
        && isset( $_GET['rpress_order_status'] )
        && !empty( $_GET['rpress_order_status'] )
      ) {
        $css = 'input#submit { visibility: visible; }';
        $css .= 'table.rpress_emails.widefat { display: none; }';
        $css .= 'p.order_notification_desc{ display: none; }';
        wp_add_inline_style( 'rpress-admin', $css );
      }

      wp_register_style('admin-icons', $css_dir . 'admin-icons' . $suffix . '.css', array(), RP_VERSION);
      wp_enqueue_style('admin-icons');
    }

    /**
    * RestroPress Admin Food Items Icons
    *
    * Echoes the CSS for the fooditems post type icon.
    *
    * @since 1.0
    * @return void
    */
    public function admin_icons() {

      $svg_images_url = esc_url( RP_PLUGIN_URL . 'assets/svg/restropress-icon.svg' );

      ?>

      <style type="text/css" media="screen">
        #dashboard_right_now .fooditem-count:before {
          background-image: url(<?php echo esc_url( $svg_images_url ); ?>);
          content: '';
          width: 20px;
          height: 20px;
          background-repeat: no-repeat;
          filter: grayscale(1);
          background-size: 80%;
          -webkit-background-size: 80%;
          -moz-background-size: 80%;
        }

        #icon-edit.icon32-posts-fooditem {
          background-image: url(<?php echo esc_url( $svg_images_url ); ?>);
          content: '';
          width: 20px;
          height: 20px;
          background-repeat: no-repeat;
          filter: grayscale(1);
          background-size: 80%;
          -webkit-background-size: 80%;
          -moz-background-size: 80%;
        }

        @media
        only screen and (-webkit-min-device-pixel-ratio: 1.5),
        only screen and (   min--moz-device-pixel-ratio: 1.5),
        only screen and (     -o-min-device-pixel-ratio: 3/2),
        only screen and (        min-device-pixel-ratio: 1.5),
        only screen and (            min-resolution: 1.5dppx) {
          #icon-edit.icon32-posts-fooditem {
            background-image: url(<?php echo esc_url( $svg_images_url ); ?>);
            content: '';
            width: 20px;
            height: 20px;
            background-repeat: no-repeat;
            filter: grayscale(1);
            background-size: 80%;
            -webkit-background-size: 80%;
            -moz-background-size: 80%;
          }
        }
      </style>

    <?php }

    public function selected_filter() {

      global $wpdb;
    
      $pdate = $_POST['date'];

      $order_count      = 0;
      $customer_count   = 0;
      $total_refund     = 0;
      $total_sales      = 0;
      $percentage_change = 0;

      if ( $pdate == 'this_year' ) {

        $start_of_this_year = date( 'Y-01-01' );
        $end_of_this_year   = date( 'Y-12-31' );
        $last_year_start    = date( 'Y-01-01', strtotime( '-1 year' ) );
        $last_year_end      = date( 'Y-12-t', strtotime( '-1 year' ) );

        $order_count        = $this->get_this_year_order_count( $start_of_this_year, $end_of_this_year );
        $orders_received_last_year = $this->get_this_year_order_count( $last_year_start, $last_year_end );

        if ( $orders_received_last_year != 0 ) {

          $percentage_change = ( ( $order_count - $orders_received_last_year )  / $orders_received_last_year ) * 100;

        }

        $table_end      = $wpdb->prefix . 'rpress_customers';

        $result         = $this->get_this_year_customer_counts( $table_end, $start_of_this_year, $end_of_this_year, $last_year_start, $last_year_end );

        $total_refund   = $this->calculate_this_year_refunds( $start_of_this_year, $end_of_this_year, $last_year_start, $last_year_end );

        $total_sales    = $this->calculate_this_year_sales( $start_of_this_year, $end_of_this_year, $last_year_start, $last_year_end );
        
      }
    
      if  ( $pdate == 'today' ) {

        $date           = date( 'Y-m-d' );
        $yesterday_date = date( "Y-m-d", strtotime( "-1 day" ) );
        
        $order_count                = $this->get_today_order_count( $date );
        $orders_received_yesterday  = $this->get_today_order_count( $yesterday_date );

        if ( $orders_received_yesterday != 0) {

          $percentage_change = ( ( $order_count - $orders_received_yesterday )  / $orders_received_yesterday ) * 100;

        }

        $table_end  = $wpdb->prefix . 'rpress_customers';

        $result     = $this->get_today_customer_counts( $table_end, $date, $yesterday_date );
    
        $total_refund = $this->calculate_today_refund( $date, $yesterday_date );

        $total_sales  = $this->calculate_today_sales( $date, $yesterday_date );
          
      }

      if  ( $pdate == 'yesterday' ) {

        $date           = date( 'Y-m-d', strtotime( '-1 day' ) );
        $previous_date  = date( "Y-m-d", strtotime( "-2 day" ) );

        $order_count              = $this->get_yesterday_order_count( $date );
        $orders_received_previous = $this->get_yesterday_order_count( $previous_date );
        
        if ( $orders_received_previous != 0 ) {

          $percentage_change  = ( ( $order_count - $orders_received_previous )  / $orders_received_previous ) * 100;

        }
      
        $table_end  = $wpdb->prefix . 'rpress_customers';
        $result     = $this->get_yesterday_customer_counts( $table_end, $date, $previous_date );

        $total_refund = $this->calculate_yesterday_refund( $date, $previous_date );

        $total_sales = $this->calculate_yesterday_sales( $date, $previous_date );

      }

      if( $pdate == 'last_week' ) {

        $start_of_last_week     = date( 'Y-m-d', strtotime( 'last week monday' ) );
        $end_of_last_week       = date( 'Y-m-d', strtotime( 'last week sunday' ) );
        $previous_week_start    = date( 'Y-m-d', strtotime( 'monday 1 weeks ago' ) );
        $previous_week_end      = date( 'Y-m-d', strtotime( 'sunday 1 week ago' ) );
      
        $order_count        = $this->get_last_week_order_count( $start_of_last_week, $end_of_last_week );
        $orders_received_previous_week = $this->get_last_week_order_count( $previous_week_start, $previous_week_end );
      
        if ( $orders_received_previous_week != 0 ) {

          $percentage_change = ( ( $order_count - $orders_received_previous_week ) / $orders_received_previous_week ) * 100;

        } 

        $table_end    = $wpdb->prefix . 'rpress_customers';
        $result       = $this->get_last_week_customer_counts( $table_end, $start_of_last_week, $end_of_last_week, $previous_week_start, $previous_week_end );

        $total_refund = $this->calculate_last_weekly_refunds( $start_of_last_week, $end_of_last_week, $previous_week_start, $previous_week_end );

        $total_sales  = $this->calculate_last_weekly_sales( $start_of_last_week, $end_of_last_week, $previous_week_start, $previous_week_end );

         
      }

      if  ( $pdate == 'last_month' ) {

        $start_of_last_month    = date( 'Y-m-01', strtotime( 'last month' ) );
        $end_of_last_month      = date( 'Y-m-t', strtotime( 'last month' ) );
        $previous_month_start   = date( 'Y-m-01', strtotime( '-2 months' ) );
        $previous_month_end     = date( 'Y-m-t', strtotime( '-2 month' ) );

        $order_count                    = $this->get_last_month_order_count( $start_of_last_month, $end_of_last_month );
        $orders_received_previous_month = $this->get_last_month_order_count( $previous_month_start, $previous_month_end );
        
        if ( $orders_received_previous_month != 0 ) {

          $percentage_change = ( ( $order_count - $orders_received_previous_month ) / $orders_received_previous_month ) * 100;

        }

        $table_end    = $wpdb->prefix . 'rpress_customers';

        $result       = $this->get_last_month_customer_counts( $table_end, $start_of_last_month, $end_of_last_month, $previous_month_start, $previous_month_end );

        $total_refund = $this->calculate_last_month_refunds( $start_of_last_month, $end_of_last_month, $previous_month_start, $previous_month_end );

        $total_sales  = $this->calculate_last_month_sales( $start_of_last_month, $end_of_last_month, $previous_month_start, $previous_month_end );

      }

      if ( $pdate == 'last_year' ) {

        $start_of_last_year     = date( 'Y-01-01', strtotime( '-1 year') );
        $end_of_last_year       = date( 'Y-12-31', strtotime( '-1 year') );
        $two_years_ago_start    = date('Y-01-01', strtotime( '-2 years' ) );
        $two_years_ago_end      = date( 'Y-12-31', strtotime( '-2 years') );

        $order_count            = $this->get_last_year_order_count( $start_of_last_year, $end_of_last_year );
        $orders_received_two_years_ago = $this->get_last_year_order_count( $two_years_ago_start, $two_years_ago_end );

        if ( $orders_received_two_years_ago != 0 ) {

          $percentage_change = ( ( abs( $order_count - $orders_received_two_years_ago ) ) / $orders_received_two_years_ago) * 100;

        }

        $table_end      = $wpdb->prefix . 'rpress_customers';

        $result         = $this->get_last_month_customer_counts( $table_end, $start_of_last_year, $end_of_last_year, $two_years_ago_start, $two_years_ago_end );

        $total_refund   = $this->calculate_last_year_refunds( $start_of_last_year, $end_of_last_year, $two_years_ago_start, $two_years_ago_end );

        $total_sales    = $this->calculate_last_year_sales( $start_of_last_year, $end_of_last_year, $two_years_ago_start, $two_years_ago_end );
         
      }

      if ( $pdate == 'custom') {
        $startDate  = $_POST['startDate'];
        $endDate    = $_POST['endDate'];

        $order_count   = $this->get_custom_order_count( $startDate, $endDate );

        $table_end     = $wpdb->prefix . 'rpress_customers';

        $result        = $this->get_custom_customer_counts( $table_end, $startDate, $endDate );

        $total_refund = $this->calculate_custom_refunds( $startDate, $endDate );

        $total_sales  = $this->calculate_custom_sales( $startDate, $endDate );

      }

      $data = array(
        'order_count'             =>  $order_count,
        'customer_count'          =>  $result['customer_count'],    
        'total_refund'            =>  $total_refund['total_refund'],
        'total_sales'             =>  $total_sales['total_sales'],
        'order_percentage'        =>  number_format( $percentage_change, 2 ),
        'customer_percentage'     =>  $result['percentage_change_customer'],
        'refund_percentage'       =>  $total_refund['total_refund_percentage'],
        'sales_percentage'        =>  $total_sales['total_sales_percentage'],
      );
      wp_send_json( $data );
    
      wp_die();
    }

    public function get_today_order_count( $date ) {
      global $wpdb;
      $query = $wpdb->prepare( "SELECT count(*) as count
          FROM {$wpdb->postmeta}
          WHERE `meta_key` = '_rpress_delivery_date'
          AND `meta_value` = %s
          GROUP BY meta_value", $date );
  
      $total_order_count = $wpdb->get_var( $query );
      return $total_order_count ? $total_order_count : 0;
    }
    
    public function get_this_year_order_count( $start_date, $end_date ) {
      global $wpdb;
      $query = $wpdb->prepare( "SELECT count(*) as count
          FROM {$wpdb->postmeta}
          WHERE `meta_key` = '_rpress_delivery_date'
          AND `meta_value` BETWEEN %s AND %s", $start_date, $end_date );
  
      $total_order_count = $wpdb->get_var( $query );
      return $total_order_count ? $total_order_count : 0;
    }

    public function get_yesterday_order_count( $date ) {
      global $wpdb;
      $query = $wpdb->prepare( "SELECT count(*) as count
          FROM {$wpdb->postmeta}
          WHERE `meta_key` = '_rpress_delivery_date'
          AND `meta_value` = %s
          GROUP BY meta_value", $date );
  
      $total_order_count = $wpdb->get_var( $query );
      return $total_order_count ? $total_order_count : 0;
    }

    public function get_last_week_order_count( $start_date, $end_date ) {
      global $wpdb;
      $query = $wpdb->prepare( "SELECT count(*) as count
          FROM {$wpdb->postmeta}
          WHERE `meta_key` = '_rpress_delivery_date'
          AND `meta_value` BETWEEN %s AND %s",
         $start_date, $end_date );
  
      $total_order_count = $wpdb->get_var( $query );
      return $total_order_count ? $total_order_count : 0;
    }

    public function get_last_month_order_count( $start_date, $end_date ) {
      global $wpdb;
      $query = $wpdb->prepare( "SELECT count(*) as count
          FROM {$wpdb->postmeta}
          WHERE `meta_key` = '_rpress_delivery_date'
          AND `meta_value` BETWEEN %s AND %s", $start_date, $end_date );
  
      $total_order_count = $wpdb->get_var($query);
      return $total_order_count ? $total_order_count : 0;
    }

    public function get_last_year_order_count( $start_date, $end_date ) {
      global $wpdb;
      $query = $wpdb->prepare( "SELECT count(*) as count
          FROM {$wpdb->postmeta}
          WHERE `meta_key` = '_rpress_delivery_date'
          AND `meta_value` BETWEEN %s AND %s", $start_date, $end_date );
  
      $total_order_count = $wpdb->get_var( $query );
      return $total_order_count ? $total_order_count : 0;
      
    }

    public function get_custom_order_count( $range_of_start_date, $range_of_end_date ) {
      global $wpdb;
      $query = $wpdb->prepare( "SELECT count(*) as count
          FROM {$wpdb->postmeta}
          WHERE `meta_key` = '_rpress_delivery_date'
          AND `meta_value` BETWEEN %s AND %s",
         $range_of_start_date, $range_of_end_date );
  
      $total_order_count = $wpdb->get_var( $query );
      return $total_order_count ? $total_order_count : 0;
    }

    public function get_today_customer_counts( $table_name, $today_date, $yesterday_date ) {
      global $wpdb;
  
       
      $query_today = $wpdb->prepare("
          SELECT COUNT(*) 
          FROM $table_name 
          WHERE DATE(date_created) LIKE %s", 
          array( "$today_date%" )
      );
  
      $customer_count = $wpdb->get_var( $query_today );
  
     
      $query_yesterday = $wpdb->prepare("
          SELECT COUNT(*) 
          FROM $table_name 
          WHERE DATE(date_created) LIKE %s", 
          array( "$yesterday_date%" )
      );
  
      $customer_count_yesterday = $wpdb->get_var( $query_yesterday );

      $percentage_change_customer = 0;
      if ( $customer_count_yesterday != 0 ) {
        $percentage_change_customer = (  ( $customer_count - $customer_count_yesterday )  / $customer_count_yesterday ) * 100;
      } 
  
      return array(
          'customer_count'              => $customer_count,
          'customer_count_yesterday'    => $customer_count_yesterday,
          'percentage_change_customer'  => number_format( $percentage_change_customer, 2 )
      );

    }

    public function get_yesterday_customer_counts( $table_name, $yesterday_date, $two_day_previous_date ) {
      global $wpdb;
  
      $query_yesterday = $wpdb->prepare("
          SELECT COUNT(*) 
          FROM $table_name 
          WHERE DATE(date_created) LIKE %s", 
          array( "$yesterday_date%" )
      );
  
      $customer_count = $wpdb->get_var( $query_yesterday );
  
      $query_two_days_ago = $wpdb->prepare("
          SELECT COUNT(*) 
          FROM $table_name 
          WHERE DATE(date_created) LIKE %s", 
          array( "$two_day_previous_date%" )
      );
  
      $customer_count_two_days_ago = $wpdb->get_var( $query_two_days_ago );
  
      
      $percentage_change_customer = 0;
      if ( $customer_count_two_days_ago != 0 ) {
        $percentage_change_customer = ( ( $customer_count - $customer_count_two_days_ago )  / $customer_count_two_days_ago ) * 100;
      }

      return array(
          'customer_count'              => $customer_count,
          'customer_count_two_days_ago' => $customer_count_two_days_ago,
          'percentage_change_customer'  => number_format( $percentage_change_customer, 2 )
      );

    }

    public function get_last_week_customer_counts( $table_name, $last_week_start, $last_week_end, $two_weeks_ago_start, $two_weeks_ago_end ) {
      global $wpdb;

      $query_last_week = $wpdb->prepare("
          SELECT COUNT(*) 
          FROM $table_name 
          WHERE DATE(date_created) BETWEEN %s AND %s", 
          array( $last_week_start, $last_week_end )
      );
  
      $customer_count = $wpdb->get_var( $query_last_week );

      $query_two_weeks_ago = $wpdb->prepare("
          SELECT COUNT(*) 
          FROM $table_name 
          WHERE DATE(date_created) BETWEEN %s AND %s", 
          array( $two_weeks_ago_start, $two_weeks_ago_end )
      );
  
      $customer_count_two_weeks_ago = $wpdb->get_var( $query_two_weeks_ago );
  
      
      $percentage_change_customer = 0;
      if ( $customer_count_two_weeks_ago != 0 ) {

        $percentage_change_customer = ( ( $customer_count - $customer_count_two_weeks_ago )  / $customer_count_two_weeks_ago ) * 100;
          
      }
  
      return array(
          'customer_count'                => $customer_count,
          'customer_count_two_weeks_ago'  => $customer_count_two_weeks_ago,
          'percentage_change_customer'    => number_format( $percentage_change_customer, 2 )
      );

    }

    public function get_last_month_customer_counts( $table_name, $last_month_start, $last_month_end, $two_month_ago_start, $two_month_ago_end ) {
      global $wpdb;

      $query_last_week = $wpdb->prepare("
          SELECT COUNT(*) 
          FROM $table_name 
          WHERE DATE(date_created) BETWEEN %s AND %s", 
          array( $last_month_start, $last_month_end )
      );
  
      $customer_count = $wpdb->get_var( $query_last_week );

      $query_two_month_ago = $wpdb->prepare("
          SELECT COUNT(*) 
          FROM $table_name 
          WHERE DATE(date_created) BETWEEN %s AND %s", 
          array( $two_month_ago_start, $two_month_ago_end )
      );
  
      $customer_count_two_months_ago = $wpdb->get_var( $query_two_month_ago );
  
      
      $percentage_change_customer = 0;
      if ( $customer_count_two_months_ago != 0 ) {

        $percentage_change_customer = ( ( $customer_count - $customer_count_two_months_ago ) / $customer_count_two_months_ago ) * 100;
      } 
  
      return array(
          'customer_count'                => $customer_count,
          'customer_count_two_months_ago' => $customer_count_two_months_ago,
          'percentage_change_customer'    => number_format( $percentage_change_customer, 2 )
      );

    }

    public function get_last_year_customer_counts( $table_name, $last_year_start, $last_year_end, $two_year_ago_start, $two_year_ago_end ) {
      global $wpdb;

      $query_last_week = $wpdb->prepare("
          SELECT COUNT(*) 
          FROM $table_name 
          WHERE DATE(date_created) BETWEEN %s AND %s", 
          array( $last_year_start, $last_year_end )
      );
  
      $customer_count = $wpdb->get_var( $query_last_week );

      $query_two_weeks_ago = $wpdb->prepare("
          SELECT COUNT(*) 
          FROM $table_name 
          WHERE DATE(date_created) BETWEEN %s AND %s", 
          array( $two_year_ago_start, $two_year_ago_end )
      );
  
      $customer_count_two_year_ago = $wpdb->get_var( $query_two_weeks_ago );
  
      
      $percentage_change_customer = 0;
      if ( $customer_count_two_year_ago != 0 ) {
        $percentage_change_customer = ( ( $customer_count - $customer_count_two_year_ago ) / $customer_count_two_year_ago ) * 100;   
      }
  
      return array(
          'customer_count'              => $customer_count,
          'customer_count_two_year_ago' => $customer_count_two_year_ago,
          'percentage_change_customer'  => number_format( $percentage_change_customer, 2 )
      );
    }

    public function get_this_year_customer_counts( $table_name, $this_year_start, $this_year_end, $start_of_last_year, $end_of_last_year ) {
      global $wpdb;

      $query_last_week = $wpdb->prepare("
          SELECT COUNT(*) 
          FROM $table_name 
          WHERE DATE(date_created) BETWEEN %s AND %s", 
          array( $this_year_start, $this_year_end )
      );
  
      $customer_count = $wpdb->get_var( $query_last_week );

      $query_two_weeks_ago = $wpdb->prepare("
          SELECT COUNT(*) 
          FROM $table_name 
          WHERE DATE(date_created) BETWEEN %s AND %s", 
          array( $start_of_last_year, $end_of_last_year )
      );
  
      $customer_count_last_year = $wpdb->get_var( $query_two_weeks_ago );
  
      
      $percentage_change_customer = 0;
      if ( $customer_count_last_year != 0 ) {
        $percentage_change_customer = ( ( $customer_count - $customer_count_last_year ) / $customer_count_last_year ) * 100;
      } 
  
      return array(
          'customer_count'              => $customer_count,
          'customer_count_last_year'    => $customer_count_last_year,
          'percentage_change_customer'  => number_format( $percentage_change_customer, 2 )
      );

    }

    public function get_custom_customer_counts( $table_name, $range_of_start_date, $range_of_end_date ) {
      global $wpdb;

      $query_custom_range = $wpdb->prepare("
          SELECT COUNT(*) 
          FROM $table_name 
          WHERE DATE(date_created) BETWEEN %s AND %s", 
          array( $range_of_start_date, $range_of_end_date )
      );
  
      $customer_count = $wpdb->get_var( $query_custom_range );
      $percentage_change_customer = 0;
       
      return array(
          'customer_count'                => $customer_count,
          'percentage_change_customer'    => number_format( $percentage_change_customer, 2 )
      );

    }

    public function calculate_today_refund( $today_date, $yesterday_date ) {
  
      $args_today = array(
        'post_type'      => 'rpress_payment',
        'post_status'    => 'refunded',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_rpress_delivery_date',
                'value'   => $today_date,
                'compare' => '=' // Exact match for today's date
            )
        )
      );

    
      $args_yesterday = array(
          'post_type'      => 'rpress_payment',
          'post_status'    => 'refunded',
          'posts_per_page' => -1,
          'meta_query'     => array(
              array(
                  'key'     => '_rpress_delivery_date',
                  'value'   => $yesterday_date,
                  'compare' => '=' // Exact match for yesterday's date
              )
          )
      );

      $query_today      = new WP_Query( $args_today );
      $query_yesterday  = new WP_Query( $args_yesterday );

      $total_refund = 0;
      $total_refund_yesterday = 0;

      if ( $query_today->have_posts() ) {
          while ( $query_today->have_posts() ) {
              $query_today->the_post();
              $post_id        = get_the_ID();
              $payment        = new RPRESS_Payment($post_id);
              $amount         = $payment->total;
              $total_refund  += $amount;
          }
          wp_reset_postdata();
      }

      
      if ( $query_yesterday->have_posts() ) {
          while ( $query_yesterday->have_posts() ) {
              $query_yesterday->the_post();
              $post_id          = get_the_ID();
              $payment          = new RPRESS_Payment( $post_id );
              $amount           = $payment->total;
              $total_refund_yesterday += $amount;
          }
          wp_reset_postdata();
      }
      
      $total_refund_percentage = 0;
      if ( $total_refund_yesterday != 0 ) {
          $total_refund_percentage = ( ( $total_refund - $total_refund_yesterday ) / $total_refund_yesterday ) * 100;
      }

      return array(
          'total_refund'            => $total_refund,
          'total_refund_yesterday'  => $total_refund_yesterday,
          'total_refund_percentage' => number_format( $total_refund_percentage, 2 )
      );

    }

    public function calculate_yesterday_refund( $yesterday_date, $two_days_ago_date ) {
  
      $args_yesterday = array(
        'post_type'      => 'rpress_payment',
        'post_status'    => 'refunded',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_rpress_delivery_date',
                'value'   => $yesterday_date,
                'compare' => '=' // Exact match for today's date
            )
        )
      );

    
      $args_two_days_ago = array(
          'post_type'      => 'rpress_payment',
          'post_status'    => 'refunded',
          'posts_per_page' => -1,
          'meta_query'     => array(
              array(
                  'key'     => '_rpress_delivery_date',
                  'value'   => $two_days_ago_date,
                  'compare' => '=' // Exact match for yesterday's date
              )
          )
      );

      $query_yesterday      = new WP_Query( $args_yesterday );
      $query_two_days_ago  = new WP_Query( $args_two_days_ago );

      $total_refund = 0;
      $total_refund_two_days_ago = 0;

      if ( $query_yesterday->have_posts() ) {
          while ( $query_yesterday->have_posts() ) {
              $query_yesterday->the_post();
              $post_id = get_the_ID();
              $payment = new RPRESS_Payment( $post_id );
              $amount = $payment->total;
              $total_refund += $amount;
          }
          wp_reset_postdata();
      }

      
      if ( $query_two_days_ago->have_posts() ) {
          while ( $query_two_days_ago->have_posts() ) {
              $query_two_days_ago->the_post();
              $post_id          = get_the_ID();
              $payment          = new RPRESS_Payment( $post_id );
              $amount           = $payment->total;
              $total_refund_two_days_ago += $amount;
          }
          wp_reset_postdata();
      }
      
      $total_refund_percentage = 0;
      if ( $total_refund_two_days_ago != 0 ) {
          $total_refund_percentage = ( ( $total_refund - $total_refund_two_days_ago ) / $total_refund_two_days_ago ) * 100;
      }

      return array(
          'total_refund' => $total_refund,
          'total_refund_two_days_ago' => $total_refund_two_days_ago,
          'total_refund_percentage' => number_format( $total_refund_percentage, 2 )
      );
    }

    public function calculate_last_weekly_refunds( $start_date_last_week, $end_date_last_week, $previous_date_week_start, $previous_date_week_end ) {

      $args_last_week = array(
        'post_type'      => 'rpress_payment',
        'post_status'    => 'refunded',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_rpress_delivery_date',
                'value'   => array( $start_date_last_week, $end_date_last_week ),
                'compare' => 'BETWEEN',
                'type'    => 'DATE'
            )
        )
      );

      $query_last_week = new WP_Query( $args_last_week );

      $total_refund = 0;

      if ( $query_last_week->have_posts()) {
          while ( $query_last_week->have_posts() ) {
              $query_last_week->the_post();
              $post_id = get_the_ID();
              $payment = new RPRESS_Payment( $post_id );
              $amount = $payment->total;
              $total_refund += $amount;
          }
          wp_reset_postdata();
      }

      
      $args_previous_week = array(
          'post_type'      => 'rpress_payment',
          'post_status'    => 'refunded',
          'posts_per_page' => -1,
          'meta_query'     => array(
              array(
                  'key'     => '_rpress_delivery_date',
                  'value'   => array( $previous_date_week_start, $previous_date_week_end ),
                  'compare' => 'BETWEEN',
                  'type'    => 'DATE'
              )
          )
      );

      $query_previous_week = new WP_Query( $args_previous_week );

      $total_refund_previous_week = 0;

      if ( $query_previous_week->have_posts() ) {
          while ( $query_previous_week->have_posts() ) {
              $query_previous_week->the_post();
              $post_id = get_the_ID();
              $payment = new RPRESS_Payment($post_id);
              $amount = $payment->total;
              $total_refund_previous_week += $amount;
          }
          wp_reset_postdata();
      }

       
      $total_refund_percentage = 0;
      if ( $total_refund_previous_week != 0 ) {
          $total_refund_percentage = ( ( $total_refund - $total_refund_previous_week ) / $total_refund_previous_week) * 100;
      }

      
      $data = array(
          'total_refund'                => $total_refund,
          'total_refund_previous_week'  => $total_refund_previous_week,
          'total_refund_percentage'     => number_format( $total_refund_percentage, 2 )
      );

      return $data;
    }

    public function calculate_last_month_refunds( $start_date_last_month, $end_date_last_month, $previous_date_month_start, $previous_date_month_end ) {

      $args_last_month = array(
        'post_type'      => 'rpress_payment',
        'post_status'    => 'refunded',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_rpress_delivery_date',
                'value'   => array( $start_date_last_month, $end_date_last_month ),
                'compare' => 'BETWEEN',
                'type'    => 'DATE'
            )
        )
      );

      $query_last_month = new WP_Query( $args_last_month );

      $total_refund = 0;

      if ( $query_last_month->have_posts()) {
          while ( $query_last_month->have_posts() ) {
              $query_last_month->the_post();
              $post_id = get_the_ID();
              $payment = new RPRESS_Payment( $post_id );
              $amount = $payment->total;
              $total_refund += $amount;
          }
          wp_reset_postdata();
      }

      
      $args_previous_month = array(
          'post_type'      => 'rpress_payment',
          'post_status'    => 'refunded',
          'posts_per_page' => -1,
          'meta_query'     => array(
              array(
                  'key'     => '_rpress_delivery_date',
                  'value'   => array( $previous_date_month_start, $previous_date_month_end ),
                  'compare' => 'BETWEEN',
                  'type'    => 'DATE'
              )
          )
      );

      $query_previous_month = new WP_Query( $args_previous_month );

      $total_refund_previous_month = 0;

      if ( $query_previous_month->have_posts() ) {
          while ( $query_previous_month->have_posts() ) {
              $query_previous_month->the_post();
              $post_id = get_the_ID();
              $payment = new RPRESS_Payment($post_id);
              $amount = $payment->total;
              $total_refund_previous_month += $amount;
          }
          wp_reset_postdata();
      }

       
      $total_refund_percentage = 0;
      if ( $total_refund_previous_month != 0 ) {
          $total_refund_percentage = ( ( $total_refund - $total_refund_previous_month ) / $total_refund_previous_month) * 100;
      }

      
      $data = array(
          'total_refund'                  => $total_refund,
          'total_refund_previous_month'   => $total_refund_previous_month,
          'total_refund_percentage'       => number_format( $total_refund_percentage, 2 )
      );

      return $data;
    }

    public function calculate_last_year_refunds( $start_date_last_year, $end_date_last_year, $previous_date_year_start, $previous_date_year_end ) {

      $args_last_year = array(
        'post_type'      => 'rpress_payment',
        'post_status'    => 'refunded',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_rpress_delivery_date',
                'value'   => array( $start_date_last_year, $end_date_last_year ),
                'compare' => 'BETWEEN',
                'type'    => 'DATE'
            )
        )
      );

      $query_last_year = new WP_Query( $args_last_year );

      $total_refund = 0;

      if ( $query_last_year->have_posts()) {
          while ( $query_last_year->have_posts() ) {
              $query_last_year->the_post();
              $post_id = get_the_ID();
              $payment = new RPRESS_Payment( $post_id );
              $amount = $payment->total;
              $total_refund += $amount;
          }
          wp_reset_postdata();
      }

      
      $args_previous_year = array(
          'post_type'      => 'rpress_payment',
          'post_status'    => 'refunded',
          'posts_per_page' => -1,
          'meta_query'     => array(
              array(
                  'key'     => '_rpress_delivery_date',
                  'value'   => array( $previous_date_year_start, $previous_date_year_end ),
                  'compare' => 'BETWEEN',
                  'type'    => 'DATE'
              )
          )
      );

      $query_previous_month = new WP_Query( $args_previous_year );

      $total_refund_previous_year = 0;

      if ( $query_previous_month->have_posts() ) {
          while ( $query_previous_month->have_posts() ) {
              $query_previous_month->the_post();
              $post_id = get_the_ID();
              $payment = new RPRESS_Payment($post_id);
              $amount = $payment->total;
              $total_refund_previous_year += $amount;
          }
          wp_reset_postdata();
      }

       
      $total_refund_percentage = 0;
      if ( $total_refund_previous_year != 0 ) {
          $total_refund_percentage = ( ( $total_refund - $total_refund_previous_year ) / $total_refund_previous_year) * 100;
      }

      
      $data = array(
          'total_refund'                  => $total_refund,
          'total_refund_previous_year'    => $total_refund_previous_year,
          'total_refund_percentage'       => number_format( $total_refund_percentage, 2 )
      );

      return $data;
    }

    public function calculate_this_year_refunds( $start_date_this_year, $end_date_this_year, $last_year_date_start, $last_year_date_end ) {

      $args_this_year = array(
        'post_type'      => 'rpress_payment',
        'post_status'    => 'refunded',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_rpress_delivery_date',
                'value'   => array( $start_date_this_year, $end_date_this_year ),
                'compare' => 'BETWEEN',
                'type'    => 'DATE'
            )
        )
      );

      $query_this_year = new WP_Query( $args_this_year );

      $total_refund = 0;

      if ( $query_this_year->have_posts()) {
          while ( $query_this_year->have_posts() ) {
              $query_this_year->the_post();
              $post_id = get_the_ID();
              $payment = new RPRESS_Payment( $post_id );
              $amount = $payment->total;
              $total_refund += $amount;
          }
          wp_reset_postdata();
      }

      
      $args_previous_year = array(
          'post_type'      => 'rpress_payment',
          'post_status'    => 'refunded',
          'posts_per_page' => -1,
          'meta_query'     => array(
              array(
                  'key'     => '_rpress_delivery_date',
                  'value'   => array( $last_year_date_start, $last_year_date_end ),
                  'compare' => 'BETWEEN',
                  'type'    => 'DATE'
              )
          )
      );

      $query_previous_year = new WP_Query( $args_previous_year );

      $total_refund_previous_year = 0;

      if ( $query_previous_year->have_posts() ) {
          while ( $query_previous_year->have_posts() ) {
              $query_previous_year->the_post();
              $post_id = get_the_ID();
              $payment = new RPRESS_Payment($post_id);
              $amount = $payment->total;
              $total_refund_previous_year += $amount;
          }
          wp_reset_postdata();
      }

       
      $total_refund_percentage = 0;
      if ( $total_refund_previous_year != 0 ) {
          $total_refund_percentage = ( ( $total_refund - $total_refund_previous_year ) / $total_refund_previous_year) * 100;
      }

      
      $data = array(
          'total_refund'                  => $total_refund,
          'total_refund_previous_year'    => $total_refund_previous_year,
          'total_refund_percentage'       => number_format( $total_refund_percentage, 2 )
      );

      return $data;
    }

    public function calculate_custom_refunds( $range_of_start_date, $range_of_end_date ) {

      $args_custom = array(
        'post_type'      => 'rpress_payment',
        'post_status'    => 'refunded',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_rpress_delivery_date',
                'value'   => array( $range_of_start_date, $range_of_end_date ),
                'compare' => 'BETWEEN',
                'type'    => 'DATE'
            )
        )
      );

      $query_custom_range = new WP_Query( $args_custom );

      $total_refund = 0;
      $total_refund_percentage =0;
      if ( $query_custom_range->have_posts()) {
          while ( $query_custom_range->have_posts() ) {
              $query_custom_range->the_post();
              $post_id = get_the_ID();
              $payment = new RPRESS_Payment( $post_id );
              $amount = $payment->total;
              $total_refund += $amount;
          }
          wp_reset_postdata();
      }
       
      $data = array(
          'total_refund'                  => $total_refund,
          'total_refund_percentage'       => number_format( $total_refund_percentage, 2 )
      );

      return $data;
    }

    public function calculate_today_sales( $today_date, $yesterday_date ) {
  
      $args_today = array(
        'post_type'      => 'rpress_payment',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_rpress_delivery_date',
                'value'   => $today_date,
                'compare' => '=' // Exact match for today's date
            )
        )
      );

    
      $args_yesterday = array(
          'post_type'      => 'rpress_payment',
          'post_status'    => 'publish',
          'posts_per_page' => -1,
          'meta_query'     => array(
              array(
                  'key'     => '_rpress_delivery_date',
                  'value'   => $yesterday_date,
                  'compare' => '=' // Exact match for yesterday's date
              )
          )
      );

      $query_today      = new WP_Query( $args_today );
      $query_yesterday  = new WP_Query( $args_yesterday );

      $total_today_sales = 0;
      $total_sales_yesterday = 0;

      if ( $query_today->have_posts() ) {
          while ( $query_today->have_posts() ) {
              $query_today->the_post();
              $post_id = get_the_ID();
              $payment = new RPRESS_Payment( $post_id );
              $amount = $payment->total;
              $total_today_sales += $amount;
          }
          wp_reset_postdata();
      }

      
      if ( $query_yesterday->have_posts() ) {
          while ( $query_yesterday->have_posts() ) {
              $query_yesterday->the_post();
              $post_id          = get_the_ID();
              $payment          = new RPRESS_Payment( $post_id );
              $amount           = $payment->total;
              $total_sales_yesterday += $amount;
          }
          wp_reset_postdata();
      }
      
      $total_sales_percentage = 0;
      if ( $total_sales_yesterday != 0 ) {
          $total_sales_percentage = ( ( $total_today_sales - $total_sales_yesterday ) / $total_sales_yesterday ) * 100;
      }

      return array(
          'total_sales'             => $total_today_sales,
          'total_sales_yesterday'   => $total_sales_yesterday,
          'total_sales_percentage'  => number_format( $total_sales_percentage, 2 )
      );
    }

    public function calculate_yesterday_sales( $yesterday_date, $two_days_ago_date ) {
  
      $args_yesterday = array(
        'post_type'      => 'rpress_payment',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_rpress_delivery_date',
                'value'   => $yesterday_date,
                'compare' => '=' // Exact match for today's date
            )
        )
      );

    
      $args_two_days_ago = array(
          'post_type'      => 'rpress_payment',
          'post_status'    => 'publish',
          'posts_per_page' => -1,
          'meta_query'     => array(
              array(
                  'key'     => '_rpress_delivery_date',
                  'value'   => $two_days_ago_date,
                  'compare' => '=' // Exact match for yesterday's date
              )
          )
      );

      $query_yesterday      = new WP_Query( $args_yesterday );
      $query_two_days_ago  = new WP_Query( $args_two_days_ago );

      $total_yesterday_sales = 0;
      $total_sales_two_days_ago = 0;

      if ( $query_yesterday->have_posts() ) {
          while ( $query_yesterday->have_posts() ) {
              $query_yesterday->the_post();
              $post_id  = get_the_ID();
              $payment  = new RPRESS_Payment( $post_id );
              $amount   = $payment->total;
              $total_yesterday_sales += $amount;
          }
          wp_reset_postdata();
      }

      
      if ( $query_two_days_ago->have_posts() ) {
          while ( $query_two_days_ago->have_posts() ) {
              $query_two_days_ago->the_post();
              $post_id          = get_the_ID();
              $payment          = new RPRESS_Payment( $post_id );
              $amount           = $payment->total;
              $total_sales_two_days_ago += $amount;
          }
          wp_reset_postdata();
      }
      
      $total_sales_percentage = 0;
      if ( $total_sales_two_days_ago != 0 ) {
          $total_sales_percentage = ( ( $total_yesterday_sales - $total_sales_two_days_ago ) / $total_sales_two_days_ago ) * 100;
      }

      return array(
          'total_sales'               => $total_yesterday_sales,
          'total_sales_two_days_ago'  => $total_sales_two_days_ago,
          'total_sales_percentage'    => number_format( $total_sales_percentage, 2 )
      );
    }

    public function calculate_last_weekly_sales( $start_date_last_week, $end_date_last_week, $previous_date_week_start, $previous_date_week_end ) {

      $args_last_week = array(
        'post_type'      => 'rpress_payment',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_rpress_delivery_date',
                'value'   => array( $start_date_last_week, $end_date_last_week ),
                'compare' => 'BETWEEN',
                'type'    => 'DATE'
            )
        )
      );

      $query_last_week = new WP_Query( $args_last_week );

      $total_sales_last_week = 0;

      if ( $query_last_week->have_posts()) {
          while ( $query_last_week->have_posts() ) {
              $query_last_week->the_post();
              $post_id          = get_the_ID();
              $payment          = new RPRESS_Payment( $post_id );
              $amount           = $payment->total;
              $total_sales_last_week += $amount;
          }
          wp_reset_postdata();
      }

      
      $args_previous_week = array(
          'post_type'      => 'rpress_payment',
          'post_status'    => 'publish',
          'posts_per_page' => -1,
          'meta_query'     => array(
              array(
                  'key'     => '_rpress_delivery_date',
                  'value'   => array( $previous_date_week_start, $previous_date_week_end ),
                  'compare' => 'BETWEEN',
                  'type'    => 'DATE'
              )
          )
      );

      $query_previous_week = new WP_Query( $args_previous_week );

      $total_sales_previous_week = 0;

      if ( $query_previous_week->have_posts() ) {
          while ( $query_previous_week->have_posts() ) {
              $query_previous_week->the_post();
              $post_id = get_the_ID();
              $payment = new RPRESS_Payment($post_id);
              $amount = $payment->total;
              $total_sales_previous_week += $amount;
          }
          wp_reset_postdata();
      }

       
      $total_sales_percentage = 0;
      if ( $total_sales_previous_week != 0 ) {
          $total_sales_percentage = ( ( $total_sales_last_week - $total_sales_previous_week ) / $total_sales_previous_week) * 100;
      }

      
      $data = array(
          'total_sales'                 => $total_sales_last_week,
          'total_sales_previous_week'   => $total_sales_previous_week,
          'total_sales_percentage'      => number_format( $total_sales_percentage, 2 )
      );

      return $data;
    }

    public function calculate_last_month_sales( $start_date_last_month, $end_date_last_month, $previous_date_month_start, $previous_date_month_end ) {

      $args_last_month = array(
        'post_type'      => 'rpress_payment',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_rpress_delivery_date',
                'value'   => array( $start_date_last_month, $end_date_last_month ),
                'compare' => 'BETWEEN',
                'type'    => 'DATE'
            )
        )
      );

      $query_last_month = new WP_Query( $args_last_month );

      $total_last_month_sales = 0;

      if ( $query_last_month->have_posts()) {
          while ( $query_last_month->have_posts() ) {
              $query_last_month->the_post();
              $post_id            = get_the_ID();
              $payment            = new RPRESS_Payment( $post_id );
              $amount             = $payment->total;
              $total_last_month_sales += $amount;
          }
          wp_reset_postdata();
      }

      
      $args_previous_month = array(
          'post_type'      => 'rpress_payment',
          'post_status'    => 'publish',
          'posts_per_page' => -1,
          'meta_query'     => array(
              array(
                  'key'     => '_rpress_delivery_date',
                  'value'   => array( $previous_date_month_start, $previous_date_month_end ),
                  'compare' => 'BETWEEN',
                  'type'    => 'DATE'
              )
          )
      );

      $query_previous_month = new WP_Query( $args_previous_month );

      $total_sales_previous_month = 0;

      if ( $query_previous_month->have_posts() ) {
          while ( $query_previous_month->have_posts() ) {
              $query_previous_month->the_post();
              $post_id        = get_the_ID();
              $payment        = new RPRESS_Payment($post_id);
              $amount         = $payment->total;
              $total_sales_previous_month += $amount;
          }
          wp_reset_postdata();
      }

       
      $total_sales_percentage = 0;
      if ( $total_sales_previous_month != 0 ) {
          $total_sales_percentage = ( ( $total_last_month_sales - $total_sales_previous_month ) / $total_sales_previous_month) * 100;
      }

      
      $data = array(
          'total_sales'                   => $total_last_month_sales,
          'total_sales_previous_month'    => $total_sales_previous_month,
          'total_sales_percentage'        => number_format( $total_sales_percentage, 2 )
      );

      return $data;
    }

    public function calculate_last_year_sales( $start_date_last_year, $end_date_last_year, $previous_date_year_start, $previous_date_year_end ) {

      $args_last_year = array(
        'post_type'      => 'rpress_payment',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_rpress_delivery_date',
                'value'   => array( $start_date_last_year, $end_date_last_year ),
                'compare' => 'BETWEEN',
                'type'    => 'DATE'
            )
        )
      );

      $query_last_year = new WP_Query( $args_last_year );

      $total_last_year_sales = 0;

      if ( $query_last_year->have_posts()) {
          while ( $query_last_year->have_posts() ) {
              $query_last_year->the_post();
              $post_id        = get_the_ID();
              $payment        = new RPRESS_Payment( $post_id );
              $amount         = $payment->total;
              $total_last_year_sales += $amount;
          }
          wp_reset_postdata();
      }

      
      $args_previous_year = array(
          'post_type'      => 'rpress_payment',
          'post_status'    => 'publish',
          'posts_per_page' => -1,
          'meta_query'     => array(
              array(
                  'key'     => '_rpress_delivery_date',
                  'value'   => array( $previous_date_year_start, $previous_date_year_end ),
                  'compare' => 'BETWEEN',
                  'type'    => 'DATE'
              )
          )
      );

      $query_previous_month = new WP_Query( $args_previous_year );

      $total_sales_previous_year = 0;

      if ( $query_previous_month->have_posts() ) {
          while ( $query_previous_month->have_posts() ) {
              $query_previous_month->the_post();
              $post_id = get_the_ID();
              $payment = new RPRESS_Payment($post_id);
              $amount = $payment->total;
              $total_sales_previous_year += $amount;
          }
          wp_reset_postdata();
      }

       
      $total_sales_percentage = 0;
      if ( $total_sales_previous_year != 0 ) {
          $total_sales_percentage = ( ( $total_last_year_sales - $total_sales_previous_year ) / $total_sales_previous_year) * 100;
      }

      
      $data = array(
          'total_sales'                   => $total_last_year_sales,
          'total_sales_previous_year'     => $total_sales_previous_year,
          'total_sales_percentage'        => number_format( $total_sales_percentage, 2 )
      );

      return $data;
    }

    public function calculate_this_year_sales( $start_date_this_year, $end_date_this_year, $last_year_date_start, $last_year_date_end ) {

      $args_this_year = array(
        'post_type'      => 'rpress_payment',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_rpress_delivery_date',
                'value'   => array( $start_date_this_year, $end_date_this_year ),
                'compare' => 'BETWEEN',
                'type'    => 'DATE'
            )
        )
      );

      $query_this_year = new WP_Query( $args_this_year );

      $total_this_year_sales = 0;

      if ( $query_this_year->have_posts()) {
          while ( $query_this_year->have_posts() ) {
              $query_this_year->the_post();
              $post_id                = get_the_ID();
              $payment                = new RPRESS_Payment( $post_id );
              $amount                 = $payment->total;
              $total_this_year_sales += $amount;
          }
          wp_reset_postdata();
      }

      
      $args_previous_year = array(
          'post_type'      => 'rpress_payment',
          'post_status'    => 'publish',
          'posts_per_page' => -1,
          'meta_query'     => array(
              array(
                  'key'     => '_rpress_delivery_date',
                  'value'   => array( $last_year_date_start, $last_year_date_end ),
                  'compare' => 'BETWEEN',
                  'type'    => 'DATE'
              )
          )
      );

      $query_previous_year = new WP_Query( $args_previous_year );

      $total_sales_previous_year = 0;

      if ( $query_previous_year->have_posts() ) {
          while ( $query_previous_year->have_posts() ) {
              $query_previous_year->the_post();
              $post_id = get_the_ID();
              $payment = new RPRESS_Payment($post_id);
              $amount = $payment->total;
              $total_sales_previous_year += $amount;
          }
          wp_reset_postdata();
      }

       
      $total_sales_percentage = 0;
      if ( $total_sales_previous_year != 0 ) {
          $total_sales_percentage = ( ( $total_this_year_sales - $total_sales_previous_year ) / $total_sales_previous_year) * 100;
      }

      
      $data = array(
          'total_sales'                   => $total_this_year_sales,
          'total_sales_previous_year'     => $total_sales_previous_year,
          'total_sales_percentage'        => number_format( $total_sales_percentage, 2 )
      );

      return $data;
    }

    public function calculate_custom_sales( $range_of_start_date, $range_of_end_date ) {

      $args_custom = array(
        'post_type'      => 'rpress_payment',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_rpress_delivery_date',
                'value'   => array( $range_of_start_date, $range_of_end_date ),
                'compare' => 'BETWEEN',
                'type'    => 'DATE'
            )
        )
      );

      $query_custom_range = new WP_Query( $args_custom );

      $total_range_sales = 0;
      $total_sales_percentage =0;

      if ( $query_custom_range->have_posts()) {
          while( $query_custom_range->have_posts() ) {
              $query_custom_range->the_post();
              $post_id                = get_the_ID();
              $payment                = new RPRESS_Payment( $post_id );
              $amount                 = $payment->total;
              $total_range_sales     += $amount;
          }
          wp_reset_postdata();
      }

      
      $data = array(
          'total_sales'                   => $total_range_sales,
          'total_sales_percentage'        => number_format( $total_sales_percentage, 2 )
      );

      return $data;
    }

    public function rpress_do_ajax_export() {

      require_once RP_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export.php';
    
      parse_str( $_POST['form'], $form );
    
      $_REQUEST = $form = (array) $form;
    
    
      if( ! wp_verify_nonce( $_REQUEST['rpress_ajax_export'], 'rpress_ajax_export' ) ) {
        die( '-2' );
      }
    
      do_action( 'rpress_batch_export_class_include', $form['rpress-export-class'] );
    
      $step     = absint( $_POST['step'] );
      $class    = sanitize_text_field( $form['rpress-export-class'] );
      $export   = new $class( $step );
      
      if( ! $export->can_export() ) {
        die( '-1' );
      }
    
      if ( ! $export->is_writable ) {
        echo json_encode( array( 'error' => true, 'message' => __( 'Export location or file not writable', 'restropress' ) ) ); exit;
      }
    
      $export->set_properties( $_REQUEST );
      
      // Added in 2.5 to allow a bulk processor to pre-fetch some data to speed up the remaining steps and cache data
      $export->pre_fetch();
    
      $ret = $export->process_step( $step );
    
      $percentage = $export->get_percentage_complete();
    
      if( $ret ) {
    
        $step += 1;
        echo json_encode( array( 'step' => $step, 'percentage' => $percentage ) ); exit;
    
      } elseif ( true === $export->is_empty ) {
    
        echo json_encode( array( 'error' => true, 'message' => __( 'No data found for export parameters', 'restropress' ) ) ); exit;
    
      } elseif ( true === $export->done && true === $export->is_void ) {
    
        $message = ! empty( $export->message ) ? $export->message : __( 'Batch Processing Complete', 'restropress' );
        echo json_encode( array( 'success' => true, 'message' => $message ) ); exit;
    
      } else {
    
        $args = array_merge( $form, array(
          'step'       => $step,
          'class'      => $class,
          'nonce'      => wp_create_nonce( 'rpress-batch-export' ),
          'rpress_action' => 'fooditem_batch_export',
        ) );
    
        $fooditem_url = add_query_arg( $args, admin_url() );
    
        echo json_encode( array( 'step' => 'done', 'url' => $fooditem_url ) ); exit;
    
      }
    }

    public function order_graph_filter() {
      $filter_type = isset( $_POST[ 'select_filter' ] ) ? $_POST[ 'select_filter' ] : '';
      $SalesByDate = [];
      if (  $filter_type === 'monthly' || $filter_type === 'weekly' || $filter_type === 'yearly'  ) {

        $SalesByDate  = $this->get_order_report( $filter_type  );

    }
      wp_send_json(  $SalesByDate );
    }
    public function get_order_report( $filter_type ) {

        $SalesByDate            = [];
        $key                    = '';
        $currentMonth           = '';
        $first_day_for_filter   = '';
        $last_day_for_filter    = '';

        if ( $filter_type == 'monthly' ) {

          $key                = 'd';
          $first_day_of_month = date( 'Y-m-01' );
          $last_day_of_month  = date( 'Y-m-t' );

        } elseif ( $filter_type == 'weekly' ) {

          $key                  = 'd';
          $currentDate          = date( 'Y-m-d' );
          $previousSixDays      = date( 'Y-m-d', strtotime( '-7 days', strtotime( $currentDate ) ) );
          $first_day_for_filter = $previousSixDays;
          $last_day_for_filter  = $currentDate;

        } elseif ( $filter_type == 'yearly' ) {
          $key            = 'm';
          $currentYear    = date('Y');
          $first_day_for_filter   = date("$currentYear-01-01");
          $last_day_for_filter    = date("$currentYear-12-t");
        }
        $args = array(
            'post_type'       => 'rpress_payment',
            'posts_per_page'  => -1,
            'date_query'      => array(
                'after'     => $first_day_for_filter,
                'before'    => $last_day_for_filter,
                'inclusive' => true,
            ),
        );
        $query = new WP_Query( $args );
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id      = get_the_ID();
                $payment      = new RPRESS_Payment( $post_id );
                $deliveryDate = get_post_meta( $post_id, '_rpress_delivery_date', true );
                $day = date( $key, strtotime( $deliveryDate ) );
                if ( !isset( $SalesByDate[ $day ] ) ) {
                    $SalesByDate[ $day ] = 0;
                }
                $SalesByDate[ $day ] += 1;
            }
          wp_reset_postdata();
        }
        return $SalesByDate;
    }
    public function customers_data_filter(){
      global $wpdb;

      $customer = $_POST['selected_option'];
        if( $customer == 'yearly' ) {
          $start_of_this_year = date( 'Y-01-01' );
          $end_of_this_year   = date( 'Y-12-31' );
          $last_year_start    = date('Y-m-01');
          $last_year_end      = date('Y-m-t');
          $table_name         = $wpdb->prefix . 'rpress_customers';
          $result             = $this->get_this_year_customers_data( $table_name, $start_of_this_year, $end_of_this_year, $last_year_start, $last_year_end );
        }
        if ( $customer == 'monthly') {
          $start_of_this_month = date('Y-m-01');
          $end_of_this_month = date('Y-m-t');
          $start_of_last_month    = date('Y-m-d', strtotime('-6 days'));
          $end_of_last_month      = date('Y-m-d');
          $table_name             = $wpdb->prefix . 'rpress_customers';
          $result                 = $this->get_this_month_customer_counts( $table_name, $start_of_this_month, $end_of_this_month, $start_of_last_month, $end_of_last_month );
        }
        if(  $customer == 'weekly') {
          $this_week_start      = date( 'Y-m-d',  strtotime('-1 days') );
          $this_week_end        = date( 'Y-m-d' );
          $start_of_last_week   = date('Y-m-d', strtotime('-1 days'));
          $end_of_last_week     = date('Y-m-d');
          $table_name           = $wpdb->prefix . 'rpress_customers';
          $result               = $this->get_this_week_customer_counts( $table_name, $this_week_start, $this_week_end, $start_of_last_week, $end_of_last_week );
        }
        
        $data = array(
          'customer_count'          =>  $result['customer_count'],
          'customer_percentage'     =>  $result['percentage_change_customer'],
          'customer_count_last'     => $result['customer_count_last'],  
        );
        wp_send_json( $data );
    }
    public function get_this_year_customers_data( $table_name, $this_year_start, $this_year_end, $start_of_last_year, $end_of_last_year ) {
      global $wpdb;

      $query_last_week = $wpdb->prepare("
          SELECT COUNT(*) 
          FROM $table_name 
          WHERE DATE(date_created) BETWEEN %s AND %s", 
          array( $this_year_start, $this_year_end )
      );

      $customer_count = $wpdb->get_var( $query_last_week );

      $query_two_weeks_ago = $wpdb->prepare("
          SELECT COUNT(*) 
          FROM $table_name 
          WHERE DATE(date_created) BETWEEN %s AND %s", 
          array( $start_of_last_year, $end_of_last_year )
      );

      $customer_count_last_year = $wpdb->get_var( $query_two_weeks_ago );

      
      $percentage_change_customer = 0;
      if ( $customer_count_last_year != 0 ) {
        $percentage_change_customer = ( ( $customer_count - $customer_count_last_year ) / $customer_count_last_year ) * 100;
      } 
      
      return array(
        'customer_count'              => $customer_count,
        'customer_count_last'    => $customer_count_last_year,
        'percentage_change_customer'  => number_format( $percentage_change_customer, 2 )
      );

    }

    public function get_this_month_customer_counts( $table_name, $start_of_this_month, $end_of_this_month, $last_month_start, $last_month_end ) {
      global $wpdb;

      $query_this_week = $wpdb->prepare("
          SELECT COUNT(*) 
          FROM $table_name 
          WHERE DATE(date_created) BETWEEN %s AND %s", 
          array( $start_of_this_month, $end_of_this_month )
      );
  
      $customer_count = $wpdb->get_var( $query_this_week );

      $query_last_month_ago = $wpdb->prepare("
          SELECT COUNT(*) 
          FROM $table_name 
          WHERE DATE(date_created) BETWEEN %s AND %s", 
          array( $last_month_start, $last_month_end )
      );
  
      $customer_count_last_months_ago = $wpdb->get_var( $query_last_month_ago );
  
      
      $percentage_change_customer = 0;
      if ( $customer_count_last_months_ago != 0 ) {

        $percentage_change_customer = ( ( $customer_count - $customer_count_last_months_ago ) / $customer_count_last_months_ago ) * 100;
      } 
  
      return array(
          'customer_count'                  => $customer_count,
          'customer_count_last'  => $customer_count_last_months_ago,
          'percentage_change_customer'      => number_format( $percentage_change_customer, 2 )
      );

    }

    public function get_this_week_customer_counts( $table_name, $start_of_this_week, $end_of_this_week, $last_week_start, $last_week_end ) {
      global $wpdb;

      $query_this_week = $wpdb->prepare("
          SELECT COUNT(*) 
          FROM $table_name 
          WHERE DATE(date_created) BETWEEN %s AND %s", 
          array( $start_of_this_week, $end_of_this_week )
      );
  
      $customer_count = $wpdb->get_var( $query_this_week );

      $query_last_weeks_ago = $wpdb->prepare("
          SELECT COUNT(*) 
          FROM $table_name 
          WHERE DATE(date_created) BETWEEN %s AND %s", 
          array( $last_week_start, $last_week_end )
      );
  
      $customer_count_last_weeks_ago = $wpdb->get_var( $query_last_weeks_ago );
  
      
      $percentage_change_customer = 0;
      if ( $customer_count_last_weeks_ago != 0 ) {

        $percentage_change_customer = ( ( $customer_count - $customer_count_last_weeks_ago )  / $customer_count_last_weeks_ago ) * 100;
          
      }
  
      return array(
          'customer_count'                => $customer_count,
          'customer_count_last'  => $customer_count_last_weeks_ago,
          'percentage_change_customer'  => number_format( $percentage_change_customer, 2 ) . '%'
);
    }
    
    
  }

endif;

return new RP_Admin_Assets();