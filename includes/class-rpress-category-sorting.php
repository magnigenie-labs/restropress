<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Food Category Sorting
*/
class RP_Food_Category_Sorting {

    /**
     * Initializes the object instance
     */
    public function __construct() {
        
        add_action( 'init', array( $this, 'front_end_order_terms' ), 20 );
        add_action( 'admin_head', array( $this, 'admin_sort_categories' ) );
        add_action( 'wp_ajax_rp_update_category_order', array( $this, 'update_category_order' ) );
        add_action( 'wp_ajax_rp_get_category_order', array( $this, 'rp_get_category_order' ) );
    }
    /**
     * Functionalities and Includes to enable Category
     * Sorting
     *
     * @since 1.0
     * @return void
     */
    public function admin_sort_categories() {

        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : '';

        if ( ! isset( $_GET['orderby'] ) && ! empty( $screen ) && ! empty( $screen->base ) && $screen->base === 'edit-tags' && $screen->taxonomy ==='food-category' ) {

            $this->enqueue_assets();
            $this->set_default_term_order( 'food-category' );
            $this->custom_help_tab();
            add_filter( 'terms_clauses', array( $this, 'set_tax_order' ), 10, 3 );
        }
    }

    /**
     * Enqueueing assests for drag and drop sorting
     *
     * @since 1.0.0
     * @return void
     */
    public function enqueue_assets() {

        wp_enqueue_style( 'rp-category-drag-drop', RP_PLUGIN_URL . "assets/css/rp-category-sorting.css", array(), '', 'all' );
        wp_enqueue_script( 'rp-category-drag-drop', RP_PLUGIN_URL . "assets/js/rp-category-sorting.js", array( 'jquery-ui-core', 'jquery-ui-sortable' ), '', true );
        
        wp_localize_script(
            'rp-category-drag-drop',
            'rp_pro_sorting_data',
            array(
                'preloader_url'    => esc_url( admin_url( 'images/wpspin_light.gif' ) ),
                'term_order_nonce' => wp_create_nonce( 'term_order_nonce' ),
                'paged'            => isset( $_GET['paged'] ) ? absint( wp_unslash( $_GET['paged'] ) ) : 0,
                'per_page_id'      => "edit_food-category_per_page",
            )
        );
    }

    /**
     * Setting a default term order if the drag and drop
     * is done  yet
     *
     * @since 1.0.0
     * @return void
     */
    public function set_default_term_order( $tax_slug ) {

        $terms = get_terms( $tax_slug, array( 'hide_empty' => false ) );
        $order = $this->get_max_taxonomy_order( $tax_slug );
        foreach ( $terms as $term ) {
            if ( ! get_term_meta( $term->term_id, 'tax_position', true ) ) {
                update_term_meta( $term->term_id, 'tax_position', $order );
                $order++;

            }
        }
    }

    /**
     * undocumented function
     *
     * @return void
     * @author 
     **/
    function rp_get_category_order() {
       $food_cat_terms =  get_terms( ['taxonomy' => 'food-category',
            'orderby' => 'name',
            'order' => 'ASC' ] );
       $return_array = [];
       foreach ( $food_cat_terms as $term_key => $food_cat_term ) {
          $return_array[$food_cat_term->term_id] = (int) get_term_meta( $food_cat_term->term_id, 'tax_position', true  );
       }
       wp_send_json_success( $return_array );
       wp_die();
    }

    /**
     * Get the maximum tax_position for categories.
     * This will be applied to terms that don't have
     * a tax position.
     *
     * @since 1.0.0
     */
    private function get_max_taxonomy_order( $tax_slug ) {
        global $wpdb;
        $max_term_order = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT MAX( CAST( tm.meta_value AS UNSIGNED ) )
                FROM $wpdb->terms t
                JOIN $wpdb->term_taxonomy tt ON t.term_id = tt.term_id AND tt.taxonomy = '%s'
                JOIN $wpdb->termmeta tm ON tm.term_id = t.term_id WHERE tm.meta_key = 'tax_position'",
                $tax_slug
            )
        );
        $max_term_order = is_array( $max_term_order ) ? current( $max_term_order ) : 0;
        return (int) $max_term_order === 0 || empty( $max_term_order ) ? 1 : (int) $max_term_order + 1;
    }

    /**
     * Create custom help tab to make users understand
     * about how this feature works.
     *
     * @since 1.0.0
     * @return void
     */
    public function custom_help_tab() {

        $screen = get_current_screen();
        $screen->add_help_tab(
            array(
                'id'      => 'rp_sorting_help_tab',
                'title'   => __( 'Category Ordering', 'rp-pro-starter' ),
                'content' => '<p>' . __( 'To reposition a category in the list, simply drag & drop it into the desired position. Each time you reposition a category, the data will update in the database and on the front end of your site.', 'rp-pro-starter' ) . '</p>',
            )
        );
    }

    /**
     * Re-Order the taxonomies based on the tax_position value.
     *
     * @param array $pieces     Array of SQL query clauses.
     * @param array $taxonomies Array of taxonomy names.
     * @param array $args       Array of term query args.
     */
    public function set_tax_order( $pieces, $taxonomies, $args ) {

        foreach ( $taxonomies as $taxonomy ) {
            global $wpdb;

            $join_statement = " LEFT JOIN $wpdb->termmeta AS term_meta ON t.term_id = term_meta.term_id AND term_meta.meta_key = 'tax_position'";

            if ( ! $this->does_substring_exist( $pieces['join'], $join_statement ) ) {
                $pieces['join'] .= $join_statement;
            }
            $pieces['orderby'] = 'ORDER BY CAST( term_meta.meta_value AS UNSIGNED )';
        }
        return $pieces;
    }

    /**
     * Check if a substring exists inside a string.
     *
     * @param string $string    The main string (haystack) we're searching in.
     * @param string $substring The substring we're searching for.
     *
     * @return bool True if substring exists, else false.
     */
    protected function does_substring_exist( $string, $substring ) {
        return strstr( $string, $substring ) !== false;
    }

    /**
     * Ajax callback function to update new sorting order
     *
     * @since 1.0.0
     */
    public function update_category_order() {

        if ( ! check_ajax_referer( 'term_order_nonce', 'term_order_nonce', false ) ) {
            wp_send_json_error();
        }

        $taxonomy_ordering_data = filter_var_array( wp_unslash( $_POST['taxonomy_ordering_data'] ), FILTER_SANITIZE_NUMBER_INT );
        $base_index = filter_var( wp_unslash( $_POST['base_index'] ), FILTER_SANITIZE_NUMBER_INT ) ;


        foreach ( $taxonomy_ordering_data as $order_data ) {
            // Due to the way WordPress shows parent categories on multiple pages, we need to check if the parent category's position should be updated.
            // If the category's current position is less than the base index (i.e. the category shouldn't be on this page), then don't update it.
            if ( $base_index > 0 ) {
                $current_position = get_term_meta( $order_data['term_id'], 'tax_position', true );
                if ( (int) $current_position < (int) $base_index ) {
                    continue;
                }
            }

            update_term_meta( $order_data['term_id'], 'tax_position', ( (int) $order_data['order'] + (int) $base_index ) );

        }

        wp_send_json_success();
    }

    /**
     * Sort categories on Frontend as per new sorting order
     *
     * @since 1.0.0
     */
     public function front_end_order_terms() {
        if ( ! is_admin() ) {
            add_filter( 'terms_clauses', array( $this, 'set_tax_order' ), 10, 3 );
        }
     }
  
}

new RP_Food_Category_Sorting();