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
      wp_register_script( 'rpress-orders', RP_PLUGIN_URL . 'assets/js/admin/rp-orders.js', array( 'jquery', 'rp-backbone-modal' ), RP_VERSION );
      wp_register_script( 'jquery-tata-toast', RP_PLUGIN_URL . 'assets/js/rp-tata.js', array( 'jquery' ), RP_VERSION );
      wp_register_script( 'rp-admin', RP_PLUGIN_URL . 'assets/js/admin/rp-admin.js', $admin_deps, RP_VERSION );
      wp_register_script( 'jquery-chosen', RP_PLUGIN_URL . 'assets/js/jquery-chosen/chosen.jquery' . $suffix . '.js', array( 'jquery' ), RP_VERSION );

      wp_enqueue_script( 'jquery-chosen' );
      wp_enqueue_script( 'jquery-form' );
      wp_enqueue_script( 'jquery-ui-datepicker' );
      wp_enqueue_script( 'jquery-ui-dialog' );
      wp_enqueue_script( 'jquery-ui-tooltip' );
      wp_enqueue_script( 'select2' );
      wp_enqueue_script( 'media-upload' );
      wp_enqueue_script( 'thickbox' );

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

      if ( $screen_id == 'restropress_page_rpress-payment-history' ) {

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
      // $suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
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
  }

endif;

return new RP_Admin_Assets();